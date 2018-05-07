<?php
namespace User\Controller;
use Think\Controller;
class ShopcarController extends Controller {

    function __construct() {
		//继承父类
		parent::__construct();
	    //判断登录
		$user_id=session("user_id");
		if(__ACTION=="notify"){//未登录
        
		}else{
            if($user_id==""){//未登录
                header("Location:".U('index/index'));
                exit();
            }    
        }

    }
    //空方法，防止报错
    public function _empty(){
        //$this->index();
        dd('非法操作！');
    }
    
    
    //商品详情
    public function xiangqing(){
        $agcy_goods_id=I('get.agcy_goods_id')?I('get.agcy_goods_id'):0;
        $user_id=session('user_id');
        $agcygoods=M('agcygoods')
                    ->where('agcy_goods_id='.$agcy_goods_id.' and agcy_goods_state=1 and agcy_goods_states=2')
                    ->find();
              
        if($agcygoods && $agcy_goods_id){
            $image=explode('|',$agcygoods['agcy_goods_images']);
            $agcygoods['agcy_goods_images']=$image;  
            $agcygoods['agcy_goods_spec']=explode(',',$agcygoods['agcy_goods_spec']); 
            $this->assign('agcygoods',$agcygoods);
            $this->assign('agcy_goods_price',$agcygoods['agcy_goods_price']);
            //求购物车的数量
            $count=M('car')->where("user_id=".$user_id)->field('SUM(goods_num) as num')->select();
            $this->assign('count',$count[0]['num']?$count[0]['num']:0); 
            $this->display();
            
        }else{
            jump_success('此商品不存在！！！',U('index/index'));
        }
       
    }
    
    //购物车
    public function shoppingcart(){
        $user_id=session('user_id');
        $cart=M('car')
            ->join('left join ec_agcy on ec_agcy.agcy_id=ec_car.agcy_id')
            ->where('ec_car.user_id='.$user_id)
            ->group('ec_car.agcy_id')
            ->field('ec_car.*,ec_agcy.agcy_name')
            ->order('ec_car.agcy_id desc')
            ->select();
        $listcar=array();    
        foreach($cart as $k=>$v){
            $listcar[$k]['agcy_name']=$v['agcy_name'];
            $data=M('car')
                    ->join('left join ec_agcy on ec_agcy.agcy_id=ec_car.agcy_id')
                    ->join('left join ec_agcygoods on ec_agcygoods.agcy_goods_id=ec_car.goods_id')
                    ->where('ec_car.agcy_id='.$v['agcy_id'].' and ec_car.user_id='.$user_id)
                    ->field('ec_car.*,ec_agcy.agcy_name as agcyname,ec_agcygoods.agcy_goods_name as goodsname,ec_agcygoods.agcy_goods_images as images,ec_agcygoods.agcy_goods_spec,ec_agcygoods.agcy_goods_repertory')
                    ->select();
            foreach($data as $kk=>$vv){
                $image=explode('|',$vv['images']);
                $data[$kk]['images']=$image[0];
                //获取该商品库存信息
                $sp=explode(',',$vv['agcy_goods_spec']);//规格
                $repertory=explode(',',$vv['agcy_goods_repertory']);//库存
                $data[$kk]['kucun']=0;
                foreach($sp as $key=>$value){
                    if($vv['goods_spec']==$value){
                       $data[$kk]['kucun']=$repertory[$key]; 
                    }
                     
                }
            }        
            
            $listcar[$k]['child']=$data;
        }
        //dd($listcar);
        $this->assign('listcar',$listcar);  
        $this->assign('count',sizeof($listcar));  
        $this->display();
    }
  
    //加入购物车
    public function addcar(){
       $user_id=session('user_id');
       $info=I('post.');
       $info['user_id']=$user_id;
       //查找是否添加过商品
       $car=M("car")->where("user_id=".$user_id." and goods_id=".$info['goods_id']." and goods_spec='".$info['goods_spec']."'")->find();
       if($car){//添加过的商品//$a=M('car')->where('car_id='.$car['car_id'])->setInc('goods_num',$info['goods_num']);
            $this->ajaxReturn(array('status'=>2,'msg'=>"该商品已添加过，请前去购物车结算"));    

       }else{
           $goods=M('agcygoods')->where('agcy_goods_id='.$info['goods_id'])->find();
           $spec=explode(',',$goods['agcy_goods_spec']);
           $kucun=explode(',',$goods['agcy_goods_repertory']);
           foreach($spec as $k=>$v){
               if($v==$info['goods_spec']){
                   $cc=$kucun[$k];
               }
           }
            if($cc>0){
                $flag=M('car')->add($info);//加购物车
            }else{
                $this->ajaxReturn(array('status'=>-1,'msg'=>"库存不足")); 
            }
            
            if($flag){
                //求购物车的数量
                $count=M('car')->where("user_id=".$user_id)->field('SUM(goods_num) as num')->select();
                $this->ajaxReturn(array('status'=>1,'msg'=>"加入购物车成功",'count'=>$count[0]['num']));    
            }else{
                $this->ajaxReturn(array('status'=>0,'msg'=>"加入购物车失败"));    
            }
       }
               
             
    }
    //更新购物车
    public function updatacar(){
        $car_id=I('get.car_id');
        $num=I('get.num');
        M("car")->where("car_id=".$car_id)->save(array('goods_num'=>$num));
        $this->ajaxReturn(array('status'=>1,'msg'=>"success"));    
        
    }
    //删除购物车
    public function delcar(){
        $car_id=I('get.car_id');
        $flag=M("car")->where("car_id=".$car_id)->delete();
        if($flag){
            $this->ajaxReturn(array('status'=>1,'msg'=>"success"));  
        }else{
            $this->ajaxReturn(array('status'=>0,'msg'=>"error"));  
        }
         
    }
    //显示购物车数量
    public function carnum() {
        $user_id = session("user_id");
        $count = M("car")
                ->where("user_id=" . $user_id)
                ->sum("goods_num");
        echo $count;
    }
    
    //结算
    public function jiesuan(){
        $user_id=session('user_id');
        $state=I('get.state');//1：立即购买，2：购物车，3：我的订单
        $count=0;
        if(intval($state)==1){//立即购买
            $agcy_id=I('get.agcy_id');
            $agcy_goods_id=I('get.agcy_goods_id');
            $spec=I('get.spec');
            $price=floatval(I('get.price'));
            $num=intval(I('get.num'));
            $goods=M('agcygoods')
                    ->where('agcy_goods_id='.$agcy_goods_id.' and agcy_goods_state=1 and agcy_goods_states=2 ')
                    ->field('agcy_goods_name,agcy_goods_images')
                    ->find();
            if($goods){
                $count=$num;
                $image=explode('|',$goods['agcy_goods_images']);
                $goods['agcy_goods_images']=$image[0];
                $sum=round(floatval($price*$num),2);
                $this->assign('spec',$spec);
                $this->assign('price',$price);
                $this->assign('num',$num);
                $this->assign('agcy_goods_id',$agcy_goods_id);
                $this->assign('goods',$goods);
            }else{
                jump_success('此商品不存在！！！',U('shopcar/xiangqing',array('agcy_goods_id'=>$agcy_goods_id)));
            }        
            
        }else if(intval($state)==2){//2购物车
           $car_ids=I('get.car_ids');//
           $nums=I('get.nums');
           $car_id=explode(',',$car_ids);
           $num=explode(',',$nums);
           $sum=0;
           $goods=array();
           foreach($car_id as $k=>$v){
                //更新购物车数量
                M("car")->where("car_id=".$v)->save(array('goods_num'=>intval($num[$k])));
                //获取购物车信息
                $car=M("car")
                    ->where("car_id=".$v)
                    ->join('left join ec_agcygoods on ec_agcygoods.agcy_goods_id=ec_car.goods_id')
                    ->field('ec_car.*,ec_agcygoods.agcy_goods_name as gname,ec_agcygoods.agcy_goods_images as gimages')
                    ->find();
                $images=explode('|',$car['gimages']);
                $car['gimages']=$images[0];//处理图片
                //求商品价格
                $sum+=floatval($car['goods_price']*intval($num[$k]));   
                $goods[$k]=$car;
                //商品总数                
                $count+=$num[$k];    
           }
           $agcy_id=$goods[0]['agcy_id'];//店铺id
           //$count=sizeof($goods);
           $this->assign('goods',$goods);
           $this->assign('car_ids',$car_ids);
        }else if(intval($state)==3){//我的订单,团购，秒杀
            $order_id=I('get.order_id');
            $order=M('orderinfo')->where('order_id='.$order_id." and order_state=1 ")->find();
            $agcy_id=$order['agcy_id'];
            $goods=M('orderlist')
                    ->join('left join ec_agcygoods on ec_agcygoods.agcy_goods_id=ec_orderlist.goods_id')
                    ->where('order_id='.$order_id)
                    ->field('ec_orderlist.*,ec_agcygoods.agcy_goods_name as gname,ec_agcygoods.agcy_goods_images as gimages')
                    ->select();
            foreach($goods as $k=>$v){
                $images=explode('|',$v['gimages']);
                $goods[$k]['gimages']=$images[0];//处理图片
                
            }        
            $sum=$order['order_money']; 
            //商品总数
            $counts=M('orderlist')->where('order_id='.$order_id)->field('SUM(goods_num) as num')->select();
            $count=$counts[0]['num']; 
            $this->assign('goods',$goods);
            $this->assign('order_id',$order_id);
        }else{
            jump_success('非法操作',U('index/index')); 
        }
        if(!$agcy_id){
            jump_success('经销商不存在',U('index/index'));
        }
        //经销商信息
        $agcy=M('agcy')
                ->where('agcy_id='.$agcy_id.' and agcy_state=1 ')
                ->field('agcy_id,agcy_name,agcy_address,agcy_manager,agcy_tel')
                ->find();
        if($agcy){
            $this->assign('agcy',$agcy); 
        }else{
            jump_success('经销商不存在或者已被禁用',U('index/index'));
        }
        $this->assign('state',$state);
        $this->assign('count',$count);
        $this->assign('sum',$sum);
        $this->display();
    }
    
    
    //支付
    public function zhifu(){
        $user_id=session('user_id');
        $state=intval(I('get.state'));
        $notes=filterEmoji(I('get.notes'),2);
        $type=intval(I('get.type'));
        if($state==1){
            //立即购买
            $agcy_id=I('get.agcy_id');
            $spec=I('get.spec');
            $num=intval(I('get.num'));
            $agcy_goods_id=I('get.agcy_goods_id');
            //$price=I('get.price'); 用规格获取价格 库存
            $goodsinfo=M('agcygoods')
                        ->where('agcy_goods_id='.$agcy_goods_id)
                        ->field('agcy_goods_price,agcy_goods_spec,agcy_goods_repertory')
                        ->find();
            $sp=explode(',',$goodsinfo['agcy_goods_spec']);//规格
            $pri=explode(',',$goodsinfo['agcy_goods_price']);//价格
            $repertory=explode(',',$goodsinfo['agcy_goods_repertory']);//库存
            foreach($sp as $k=>$v){
                if($spec==$v){
                   $price=floatval($pri[$k]);
                   $kucun=$repertory[$k]; 
                }
            }
            if(!$price){
               jump_success('此商品规格与对应价格有误！',U('shopcar/xiangqing',array('agcy_goods_id'=>$agcy_goods_id)));
            }
            $sum=round(floatval($price*$num),2);
            if($kucun>=$num){
                //经销商信息
                $agcy=M('agcy')
                    ->where('agcy_id='.$agcy_id.' and agcy_state=1 ')
                    ->field('agcy_id,agcy_name,agcy_address,agcy_manager,agcy_tel')
                    ->find();
                $info['user_id']=$user_id;
                $info['agcy_id']=$agcy_id;
                $info['order_money']=$sum;
                $info['order_state']=1;
                $info['order_type']=1;//1 微信
                $info['order_addtime']=time();
                $info['addr_name']=$agcy['agcy_manager'];
                $info['addr_phone']=$agcy['agcy_tel'];
                $info['addr_address']=$agcy['agcy_address'];
                $info['order_remarks']=filterEmoji($notes,2);
                $info['order_distribution']=1;//自提
                $info['order_number']=time().rand(10000,99999);//自提
                $order_id=M('orderinfo')->add($info); //orderinfo表添加数据
                //orderlist 表添加数据
                M('orderlist')->add(array('goods_id'=>$agcy_goods_id,'goods_num'=>$num,'goods_price'=>$price,'order_id'=>$order_id,'goods_spec'=>$spec));
                
                $this->paytypes($order_id,$type);
            }else{
                //echo "<script>alert('此商品库存不足，购买不了');location.href='".U("shopcar/xiangqing",array('agcy_goods_id'=>$agcy_goods_id))."'</script>";
                jump_success('商品库存不足，购买不了',U('shopcar/xiangqing',array('agcy_goods_id'=>$agcy_goods_id)));
                exit();   
            }
        }else if($state==2){//2购物车
            $car_ids=I('get.car_ids');
            $car_id=explode(',',$car_ids);
            $sum=0;
            $agcy_id=0;
            //判断库存
            foreach($car_id as $k=>$v){
                //商品库存
                $car=M("car")
                    ->join('left join ec_agcygoods on ec_agcygoods.agcy_goods_id=ec_car.goods_id')
                    ->field('ec_car.*,ec_agcygoods.agcy_goods_spec,ec_agcygoods.agcy_goods_repertory')
                    ->where("car_id=".$v)
                    ->find();
                $sp=explode(',',$car['agcy_goods_spec']);//规格
                $repertory=explode(',',$car['agcy_goods_repertory']);//库存
                $kucun=0;
                foreach($sp as $k=>$v){
                    if($car['goods_spec']==$v){
                       $kucun=$repertory[$k]; 
                    } 
                }
                if($kucun<$car['goods_num']){
                    //echo "<script>alert('商品库存不足，购买不了');location.href='".U("shopcar/xiangqing",array('agcy_goods_id'=>$car['goods_id']))."'</script>";
                    jump_success('商品库存不足，购买不了',U('shopcar/xiangqing',array('agcy_goods_id'=>$car['goods_id'])));
                    exit(); 
                }
            }
            //存orderinfo表
            $data=array('user_id'=>$user_id,
                        'order_state'=>1,
                        'order_type'=>1,
                        'order_addtime'=>time(),
                        'order_number'=>time().rand(10000,99999),
                        'order_remarks'=>$notes,
                        'order_distribution'=>1
            );
            $order_id=M('orderinfo')->add($data);
            //存orderlist表
            foreach($car_id as $k=>$v){
                //获取购物车信息
                $car=M("car")->where("car_id=".$v) ->find();
                $agcy_id=$car['agcy_id'];
                M('orderlist')->add(array('goods_id'=>$car['goods_id'],'goods_num'=>$car['goods_num'],'goods_price'=>$car['goods_price'],'goods_spec'=>$car['goods_spec'],'order_id'=>$order_id));
                //求商品价格
                $sum+=round(floatval($car['goods_price']*intval($car['goods_num'])),2);
                
                //清除购物车
                M('car')->where("car_id=".$v)->delete();
            }
            //经销商信息
            $agcy=M('agcy')
                        ->where('agcy_id='.$agcy_id.' and agcy_state=1 ')
                        ->field('agcy_id,agcy_name,agcy_address,agcy_manager,agcy_tel')
                        ->find();
            //orderinfo表更新
            $flag=M('orderinfo')->where('order_id='.$order_id )->save(array('agcy_id'=>$agcy_id,'order_money'=>$sum,'addr_name'=>$agcy['agcy_manager'],'addr_phone'=>$agcy['agcy_tel'],'addr_address'=>$agcy['agcy_address']));
            $this->paytypes($order_id,$type);//跳支付
        }else if($state==3){//我的订单,团购，秒杀
            $order_id=I('get.order_id');
            $list=M("orderlist")
                ->join("left join ec_agcygoods on ec_agcygoods.agcy_goods_id=ec_orderlist.goods_id")
                ->where('order_id='.$order_id)
                ->field('ec_orderlist.*,ec_agcygoods.agcy_goods_spec,ec_agcygoods.agcy_goods_repertory')
                ->select();
            //判断库存
            foreach($list as $k=>$v){
                $sp=explode(',',$v['agcy_goods_spec']);//规格
                $repertory=explode(',',$v['agcy_goods_repertory']);//库存
                $kucun=0;
                foreach($sp as $k1=>$v1){
                    if($v['goods_spec']==$v1){
                       $kucun=$repertory[$k1]; 
                    }
                }
                if($kucun<$v['goods_num']){
                    //echo "<script>alert('商品库存不足，购买不了');location.href='".U("shopcar/xiangqing",array('agcy_goods_id'=>$v['goods_id']))."'</script>";
                    jump_success('库存不足',U('shopcar/xiangqing',array('agcy_goods_id'=>$v['goods_id'])));
                    //exit(); 
                }
            }
            $this->paytypes($order_id,$type);//跳支付
            
        }
        
    }
    
    //支付方式
    public function paytypes($order_id,$type){
        if($type==1){
           //微信支付
           //重定向
            $this->redirect('shopcar/weixinpay', array('order_id' => $order_id), 0, '页面跳转中...');
        }else if($type==2){//银联支付
            dd('暂未开发');
        }else{
            //echo "<script>alert('请选择支付方式');location.href='".U("index/index")."'</script>";
            jump_success('请选择支付方式',U('index/index'));
            //exit(); 
        }
    }
    
    
    //支付开始============
	//微信支付
    public function weixinpay() {
		header("Content-type: text/html; charset=utf-8");
        $order_id = I('get.order_id');
        //根据订单号查询所需的订单编号，订单金额
		$orderlist=M('Orderinfo')->where('order_id='.$order_id)->field('order_money,order_number,order_id')->find();
		$out_trade_no=$orderlist['order_number'];//订单编号
		$amount=$orderlist['order_money'];//订单金额
		$body="订单".$out_trade_no."下单成功";
        session("weixinpay_orderid", $order_id); 
        session("weixinpay_out_trade_no", $out_trade_no);
        //引入WxpayAPI
        vendor('WxpayAPI.WxPayJsApiPay');
        //①、获取用户openid
        $jsApi = new \JsApiPay();
        $openId = $jsApi->GetOpenid();

        //②、统一下单
        //获取客户订单号，微信下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($body); //商品描述
        $input->SetAttach($order_id); //附加数据-订单id
        $input->SetOut_trade_no($out_trade_no); //商户系统内部订单号，要求32个字符内、且在同一个商户号下唯一
        //$input->SetTotal_fee(1); //订单总金额，单位为分
        $input->SetTotal_fee($amount * 100); //订单总金额，单位为分
        $input->SetTime_start(date("YmdHis")); //交易起始时间
        $input->SetTime_expire(date("YmdHis", time() + 600)); //交易结束时间
        $input->SetGoods_tag($body); //商品标记
        $input->SetNotify_url(WEB_HOST . 'index.php/User/shopcar/notify'); //通知地址
        $input->SetTrade_type("JSAPI"); //交易类型 取值如下：JSAPI，NATIVE，APP等
        $input->SetOpenid($openId);
        $order = \WxPayApi::unifiedOrder($input);

        $jsApiParameters = $jsApi->GetJsApiParameters($order);
        //获取共享收货地址js函数参数
        //$editAddress = $jsApi->GetEditAddressParameters();
        $this->assign('jsApiParameters', $jsApiParameters);
        $token = md5($order_id . "weur2938423");
        $this->assign('SuccessUrl', U('shopcar/SuccessUrl', array('order_id' => $order_id, "token" => $token))); //支付成功跳转页面
        $this->assign('FailUrl', U('shopcar/FailUrl', array("order_id" => $order_id,))); //支付失败跳转页面
        //echo $jsApiParameters;
        $this->display();
    }

    //支付成功页面
    public function SuccessUrl() {
		header("Content-type: text/html; charset=utf-8");
        $order_id = I("get.order_id");
        $token = I("get.token");
        $user_id = session("user_id");
        if ($token != md5($order_id . "weur2938423")) {
			//echo "<script>alert('非法操作，参数错误');location.href='".U("index/index")."'</script>";
            jump_success('非法操作，参数错误',U('index/index'));
			//exit();
        }
        //引入WxpayAPI
        vendor('WxpayAPI.WxPayJsApiPay');
        vendor('WxpayAPI.WxPayNotify');
        //使用通用通知接口
        $notify = new \PayNotify();
        $flag = $notify->Queryorder_out_trade_no(session("weixinpay_out_trade_no"));
		//dump($flag);
        if (!$flag) {
			//echo "<script>alert('非法操作，订单不存在！');location.href='".U("index/index")."'</script>";
            jump_success('非法操作，订单不存在!',U('index/index'));
			//exit();
        }
        //更新订单记录  2待发送
       $alss= M("orderinfo")->where(" order_id=" . $order_id . " and user_id=" . session("user_id"))
		->save(array("order_state" => 2));
        if ($alss) {
			//先根据订单号查下单数量
			$order=M('Orderlist')->where('order_id='.$order_id)->select();
			//查询agcy_id
			$orders=M('Orderinfo')->where('order_id='.$order_id)->find();
			foreach($order as $k=>$v){
                //减少商品库存(多规格 =>多库存)
                $goods=M("agcygoods")->where("agcy_goods_id=".$v['goods_id'])->find();
                $spec=explode(',',$goods['agcy_goods_spec']);//多规格
                $repertory=explode(',',$goods['agcy_goods_repertory']);//多库存
                foreach($spec as $key=>$value){
                    if($value==$v['goods_spec']){
                        $repertory[$key]-=$v['goods_num'];//减规格所对应的库存
                    }
                }
                $kucun=join(',',$repertory);
                M("agcygoods")->where("agcy_goods_id=".$v['goods_id'])->save(array('agcy_goods_repertory'=>$kucun));//减库存（对应相应的规格减少库存）
                M("agcygoods")->where("agcy_goods_id=".$v['goods_id'])->setInc('agcy_goods_salenum',$v['goods_num']);	//增加商品销售量(不对应多规格) 
            }
			//M('Agcy')->where('agcy_id='.$orders['agcy_id'])->setInc('shop_ordernumber',1);//给店铺的历史订单数加1
			//给商户发消息
			$message= new \Admin\Model\MessageModel();
			$msg_title="订单支付成功消息通知";
			$messages="您有新的订单(订单编号为:".$orders['order_number'].")等待处理";
			$message->getmessage_($orders['agcy_id'],2,$msg_title,$messages);
			//echo "<script>alert('订单支付成功！');location.href='".U("expert/wodeshangpindingdan")."'</script>";
            jump_success('订单支付成功！',U('expert/wodeshangpindingdan'));
			exit();
        } else {
			//echo "<script>alert('非法操作，数据更新失败！');location.href='".U("index/index")."'</script>";
            jump_success('非法操作，数据更新失败！!',U('index/index'));
			//exit();
        }
    }

    //支付失败页面
    public function FailUrl() {
		header("Content-type: text/html; charset=utf-8");
        $order_id = I("order_id");
		//echo "<script>alert('订单支付失败！');location.href='".U("expert/wodeshangpindingdan")."'</script>";
        jump_success('订单支付失败！!',U('expert/wodeshangpindingdan'));
		//exit();
    }

    //支付通知页面
    public function notify() {
		$xml = file_get_contents('php://input');
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		session("weixinpay_out_trade_no",$values['out_trade_no']);
		session("order_id",$values['attach']);

        //引入WxpayAPI
        vendor('WxpayAPI.WxPayJsApiPay');
        vendor('WxpayAPI.WxPayNotify');
        //使用通用通知接口
        $notify = new \PayNotify();
        $flag = $notify->Queryorder_out_trade_no(session("weixinpay_out_trade_no")); //查询订单 by out_trade_no 商户订单号
        if ($flag) {
            //这里写要支付成功的操作
			//更新订单记录
			$flag2=M("orderinfo")->where("order_state=1 and order_number=" . session("weixinpay_out_trade_no"))->save(array("order_state" => 2));
			if($flag2){
                //先根据订单号查下单数量
                $order=M('Orderlist')->where('order_id='.session("order_id"))->select();
                //查询agcy_id
                $orders=M('Orderinfo')->where('order_id='.session("order_id"))->find();
                foreach($order as $k=>$v){
                    //减少商品库存(多规格 =>多库存)
                    $goods=M("agcygoods")->where("agcy_goods_id=".$v['goods_id'])->find();
                    $spec=explode(',',$goods['agcy_goods_spec']);//多规格
                    $repertory=explode(',',$goods['agcy_goods_repertory']);//多库存
                    foreach($spec as $key=>$value){
                        if($value==$v['goods_spec']){
                            $repertory[$key]-=$v['goods_num'];//减规格所对应的库存
                        }
                    }
                    $kucun=join(',',$repertory);
                    M("agcygoods")->where("agcy_goods_id=".$v['goods_id'])->save(array('agcy_goods_repertory'=>$kucun));//减库存（对应相应的规格减少库存）
                    M("agcygoods")->where("agcy_goods_id=".$v['goods_id'])->setInc('agcy_goods_salenum',$v['goods_num']);	//增加商品销售量(不对应多规格) 
                }//M('Agcy')->where('agcy_id='.$orders['agcy_id'])->setInc('shop_ordernumber',1);//给店铺的历史订单数加1
                //给商户发消息
                $message= new \Admin\Model\MessageModel();
                $msg_title="订单支付成功消息通知";
                $messages="您有新的订单(订单编号为:".$orders['order_number'].")等待处理";
                $message->getmessage_($orders['agcy_id'],2,$msg_title,$messages);
				session("weixinpay_orderid", "");
				session("weixinpay_out_trade_no", "");
				session("order_id","");
				
			}            
        }
        $notify->Handle(false);
    }
	//支付完成============
    

}