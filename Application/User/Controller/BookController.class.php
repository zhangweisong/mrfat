<?php
namespace User\Controller;
use Think\Controller;
class BookController extends Controller {
	function __construct() {
		//继承父类
		parent::__construct();
	    //判断登录状态 
		$user_id=session("user_id");
		//判断是否在微信中打开
        if (__ACTION == "notify") {//微信回调不验证登录
             
        }else{
			if($user_id==""){//未登录
				header("Location:".U('index/index'));
				exit();
			}
		}

    }
	//空方法，防止报错
	public function _empty(){
        $this->book();
    }
	
	//图书商城
    public function book(){
		$order="";
		$n=intval(I("get.n",1));
		switch($n){
			case 1:
				$order="book_id";
				break;
			case 2:
				$order="book_buynum";
				break;
			case 3:
				$order="book_price";
				break;
			default:
				$order="book_id";
				break;
		}
		$type=intval(I("get.type",1));
		switch($type){
			case 0:
				$order.=" asc";
				break;
			case 1:
				$order.=" desc";
				break;
			default:
				$order.=" desc";
				break;
		}
        //图书轮播
        $banner = M('focusinfo')
            ->where('focus_type=7')
            ->order('focus_sort asc,focus_id desc')
            ->select(); 
        //搜索过来的
        $yiji = I('yiji',0);
        $erji = I('erji',0);
        $where = "1 and book_isable=1 ";  
        if($yiji!=0 && $erji!=0){ 
            $where.='and classify_fatherid='.$yiji.' and classify_id='.$erji.' ';
        } 
         
        $key = trim(I('key'));
        if ($key) {
            $where .= " and ec_book.book_name LIKE '%".$key."%' ";
            $this->assign('key', $key);
        }
        $count =M('book') 
            ->where($where) 
            ->count();
		$Page = new \Think\Page($count,4); 
        
        //查找图书
        $book = M('book')
            ->where($where)
            ->order($order) 
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();  
        foreach($book as $k=>$v){
            $res = explode('|',$v['book_image']);
            $book[$k]['img'] = $res[0];
        } 
        
        if(IS_AJAX){ 
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('book' => $book));
            } else {
                $this->ajaxReturn(array('book' => array()));
            }   
        }else {
		    $this->assign('type',$type); 
			$this->assign('n',$n); 
			$this->assign('erji',$erji); 
			$this->assign('yiji',$yiji);
			$this->assign('book', json_encode(array('book' => $book)));
            $this->assign('banner',$banner); 
            $this->assign('res',$res);//搜索问题分类id 	
        } 
		$this->display();
    }  
    
    
    public function type(){ 
        //一级分类
        $type=M('bookclassify')
            ->where('classify_fatherid=0 and classify_isable=1')
            ->order('classify_id desc')
            ->select();
        foreach($type as $k=>$v){
           $type[$k]['child']= M('bookclassify')
                ->where('classify_fatherid='.$v['classify_id'].' ') 
                ->select();
        } 
        $this->assign('type',$type); 
        $this->display();   
    }
    
    //书籍详情
    public function bookinfo(){
        $book_id=I('book_id');
        $bookinfo = M('book')
            ->where('book_id='.$book_id)
            ->find(); 
        if($bookinfo['book_num']==''){
			$bookinfo['book_num']=0;
		}
        $this->assign('bookinfo',$bookinfo); 
        $this->display();  
    }
    
    //加入购物车
    public function addcar(){
        $book_id = I('book_id');  
        $num = I("num",0);
        //判断库存和加入数量
        $book = M('book')->where("book_id='".$book_id."' ")->find();
        if(abs($num)>abs($book['book_num'])){
            $this->ajaxReturn(array('data' => 3), 'json');
        };
        
        $flag = M('bookcar')->where("book_id='".$book_id."' and user_id='".session("user_id")."'")->find(); 
        if($flag){
            $this->ajaxReturn(array('data' => 2), 'json');
        }else{
            $data = [
                'user_id'=>session("user_id"),
                'book_id'=>$book_id,
                'book_num'=>$num,
            ];
            $flag2 = M('bookcar')->add($data);
        }; 
        if($flag1 || $flag2){
            $count=M('bookcar')
                ->where("user_id='".session("user_id")."' ")
                ->sum('book_num');  
            $this->ajaxReturn(array('data' => 1,'count'=>$count), 'json');
        }else{
            $this->ajaxReturn(array('data' => 0), 'json');
        }   
    }
    
    //显示购物车数量
	public function carnum(){
		$user_id=session("user_id");
		$count=M('bookcar')
            ->where("user_id='".$user_id."' ")
            ->sum('book_num');
		echo $count;
	}
    
    //购物车
    public function car(){
        //从购物车查找
        $user_id=session("user_id"); 
        $count =M('bookcar')
            ->join('left join ec_book on ec_bookcar.book_id=ec_book.book_id')
            ->where("user_id='".$user_id."' ")
            ->field("ec_bookcar.*,ec_book.book_name,ec_book.book_price,ec_book.book_price,ec_book.book_image")
            ->count();
		$Page = new \Think\Page($count,4);  
		$info=M('bookcar')
            ->join('left join ec_book on ec_bookcar.book_id=ec_book.book_id')
            ->where("user_id='".$user_id."' ")
            ->field("ec_bookcar.*,ec_book.book_name,ec_book.book_price,ec_book.book_price,ec_book.book_image,ec_book.book_num as numss")
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
          
        foreach($info as $k=>$v){
            $res = explode('|',$v['book_image']);
            $info[$k]['img'] = $res[0];
        }
        
        if(IS_AJAX){ 
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('info' => $info), 'json');
            } else {
                $this->ajaxReturn(array('info' => array()), 'json');
            }   
        }else {
			$this->assign('info', json_encode(array('info' => $info))); 
            $this->display();
        }  
        
    }
    
    //删除购物车
    public function delcar(){
        $bcar_id = I('bcar_id'); 
        $flag = M('bookcar')->where("bcar_id='".$bcar_id."' ")->delete();  
        if($flag){
            $this->ajaxReturn(array('data' => 1), 'json');
        }else{
            $this->ajaxReturn(array('data' => 0), 'json');
        }   
    }
    
    //更改购物车数量
    public function changenum(){ 
        $user_id=session("user_id");
		$book_id=I('post.book_id'); 
		$zongshu=I('post.zongshu');
		if($zongshu){
			//更新
			M('bookcar')->where("book_id='".$book_id."' and user_id='".$user_id."' ")->save(array('book_num'=>$zongshu));
		}
		echo json_encode(); 
         
    }
    
    //购物车提交
    public function account(){
        $user_id=session('user_id');
        $bookid=I('get.book_id'); //图书id
        $booknum=I('get.book_num'); //图书数量 
        $timeStamp = time();
        $border_number = $timeStamp.random(2); 
        $book_id=explode(",",$bookid);
        $book_num=explode(",",$booknum);
        if(sizeof($book_id)==0 || sizeof($book_num)==0 ){
            $this->redirect('index/index', array(), 0, '页面跳转中...');
		}
        //根据购物车查询金额
        $buy=M('bookcar')
            ->join('left join ec_book on ec_bookcar.book_id=ec_book.book_id')
            ->where("ec_bookcar.book_id in (".join(",",$book_id).")  and ec_bookcar.user_id='".$user_id."'")
            ->field("ec_bookcar.*,ec_book.book_price")
            ->select();
        //判断购物车中是否有该商品 
        if(!$buy){
            $this->redirect('index/index', array(), 0, '页面跳转中...');
        }
        //计算总价格
        foreach($buy as $k=>$v){
            $total += abs($v['book_price']*$v['book_num']+10); 
        }
        //添加bookorder
        $data = [
            'user_id'=>$user_id,
            'border_money'=>$total,
            'border_state'=>1, 
            'border_addtime'=>time(),
            'border_number'=>$border_number, 
        ];
        $bookorder=M('bookorder')->add($data); 
        //添加bookorderlist
        $carinfo=M('bookcar')
            ->join('left join ec_book on ec_bookcar.book_id=ec_book.book_id')
            ->where("ec_bookcar.book_id in (".join(",",$book_id).")  and ec_bookcar.user_id='".$user_id."'")
            ->field("ec_bookcar.*,ec_book.book_price")
            ->select(); 
        foreach($carinfo as $key=>$val){
            M('bookorderlist')->add(array('book_price'=>$val['book_price'],'book_id'=>$val['book_id'],'book_num'=>$val['book_num'],'border_id'=>$bookorder));
            //删除购物车
            M('bookcar')->where(array('user_id'=>$user_id,'book_id'=>$val['book_id']))->delete(); 
        } 
        
        if($bookorder){ 
            $this->redirect('book/pay', array('bookorder'=>$bookorder), 0, '页面跳转中...');
        }
       
    }
    
    //直接购买
    public function direct(){
        $user_id=session('user_id');
        $book_id = Intval(I('get.book_id'));      //数量 
		$num = Intval(I('get.num'));              //id
        $timeStamp = time();
        $border_number = $timeStamp.random(2); 
        //判断库存和加入数量
        $book = M('book')->where("book_id='".$book_id."' ")->find();
        if(abs($num)>abs($book['book_num'])){
            jump_success('库存不足',U('book/bookinfo',array('book_id'=>$book_id)));
        };
        //计算价格
        $total = abs($book['book_price']*$num+10); 
        //添加bookorder
        $data = [
            'user_id'=>$user_id,
            'border_money'=>$total,
            'border_state'=>1, 
            'border_addtime'=>time(),
            'border_number'=>$border_number, 
        ];
        $bookorder=M('bookorder')->add($data); 
        $info=array(
            'book_id'=>$book_id,
            'book_num'=>$num,
            'book_price'=>$total,
            'border_id'=>$bookorder,
		); 
		M('bookorderlist')->add($info);
        echo $bookorder; 
    }
    
    //结算页面 
    public function pay(){
        $user_id=session("user_id");
		$border_id= I('get.bookorder'); 
		$orderlist =M('bookorderlist')
            ->where("border_id='".$border_id."'") 
            ->join("left join ec_book on ec_bookorderlist.book_id=ec_book.book_id")
            ->field('ec_bookorderlist.*,ec_book.book_name,ec_book.book_price,ec_book.book_oldprice,ec_book.book_image')
            ->order('ec_bookorderlist.blist_id desc') 
            ->select();  
        foreach($orderlist as $k=>$v){
            $res= explode('|',$v['book_image']);
            $orderlist[$k]['img'] = $res[0];
            
        } 
       
		//商品总件数 
        $num =M('bookorderlist')
            ->where("border_id='".$border_id."'")  
            ->sum('book_num');  
		$order =M('bookorder')
            ->where("border_id='".$border_id."'") 
            ->find();	
        //默认地址
        $addr =M('address')
            ->where("a_default=1 and user_id='".$user_id."' and status=0") 
            ->find();
        $res = $addr['province'].$addr['detail'];
        $addr['address'] = $res;
        //除了默认地址外的其他地址
        $address =M('address')
            ->where(" user_id='".$user_id."' and status=0")
            ->order("a_default desc")
            ->select();
        foreach($address as $k=>$v){ 
            $address[$k]['addr'] = $v['province'].$v['detail'];
        }  
		$money=	$order['border_money'];  
		$this->assign('orderlist',$orderlist);		//商户订单产品
		$this->assign('money',$money);		        //总价格
        $this->assign('num',$num);
        $this->assign('addr',$addr);
        $this->assign('address',$address);
        $this->assign('border_id',$border_id);	
		$this->display();
        
    }
    
    //我的图书订单
    public function bookorder(){
        $user_id=session("user_id");
        $where = "ec_bookorder.user_id=" . $user_id;
        if (I("type")) {
            $where .= " AND ec_bookorder.border_state=" . I("type");
        } 
        $count = M("bookorder")
            ->where($where)
            ->count();
        $Page = new \Think\Page($count,3);
        $orderlist = M("bookorder")
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->where($where)
            ->order("ec_bookorder.border_addtime DESC")
            ->select();
        foreach ($orderlist AS $k => $v) {
            $orderlist[$k]['forder_addtime'] = date("Y-m-d H:i:s", $v['border_addtime']);
            $orderlist[$k]['goodslist'] = M("bookorderlist")
                ->join("LEFT JOIN ec_book ON ec_book.book_id=ec_bookorderlist.book_id")
                ->field("ec_bookorderlist.*,ec_book.book_name,ec_book.book_price,ec_book.book_image")
                ->where("border_id=" . $v['border_id'])
                ->select();
            $orderlist[$k]['num'] =M('bookorderlist')
                ->where("border_id=" . $v['border_id'])
                ->sum('book_num');   
        }  
        
        if (IS_AJAX) {
            if (count($orderlist) > 0) {
                $html = '';
                foreach ($orderlist AS $k => $v) { 
                    $html .='<div class="dingdangl_ct">
						<div class="danhao">
							<span>单号：'.$v['border_number'].'</span>
							<i>'.$v['forder_addtime'].'</i>
						</div>';
                        foreach($v['goodslist'] AS $kk=>$vv):
                        $image = explode('|',$vv['book_image']);
						$html .='<div class="shangpin"> 
							<dl>
								<dt>
									<img src="'.$image[0].'" />
								</dt>
								<dd>
									<h4>'.$vv['book_name'].'</h4>
									<p>
										<span>￥'.$vv['book_price'].'</span>
										<i>x'.$vv['book_num'].'</i>
									</p>
								</dd>
							</dl> 
						</div>';
                        endforeach;
						$html .='<div class="beizhu">
							<table border="0" cellspacing="" cellpadding="">
								<tr>
									<td style="width:55px; text-align: center;">备注：</td>
									<td>'.$v['border_remark'].'</td>
								</tr>
							</table>
						</div>
						<div class="zongjia c"> 
							<p>
								<span>共'.$v['num'].'件商品</span>
								<em>合计：'.$v['border_money'].'(含快递)</em>
							</p> ';
                            if($v['border_state']==1): 
							$html .='<strong class="strong_1" onclick="pay('.$v['border_id'].')">去支付</strong> 
							<strong class="strong_2" onclick="quxiao('.$v['border_id'].')">取消订单</strong>';
                            elseif($v['border_state']==2):
                            $html .='<strong class="strong_1">待发货</strong>';  
                            elseif($v['border_state']==3):
                            $html .='<strong class="strong_1">已完成</strong>';
                            endif;
						$html .='</div>
					</div>';
                    
                }   
                $this->ajaxReturn($html);
            } else {
                $this->ajaxReturn(null);
            }
        } else { 
            $this->assign("orderlist", $orderlist);
            $this->display();
        }
        
    }
    
    //取消订单
    public function delorder(){
        $border_id = I("border_id"); 
        $flag1 = M("bookorder")->where('border_id='.$border_id)->delete();
        $flag2 = M("bookorderlist")->where('border_id='.$border_id)->delete();
        if($flag1 && $flag2){
            $this->ajaxReturn(array('data' => 1), 'json');
        }else{
            $this->ajaxReturn(array('data' => 0), 'json');
        } 
        
    }
    
    //去支付
	public function paymoney(){
        $user_id=session("user_id");
        $border_id= I('get.border_id');//订单id
        $border_type= I('get.paytype');//付款方式
        $address= I('get.address');//收货地址
        $phone= I('get.phone');//收货电话
        $username= I('get.username');//收货人
        $border_remark= I('get.border_remark');//备注
        //补充订单表
        $data['baddr_name'] = $username; 
        $data['baddr_phone'] = $phone;
        $data['baddr_address'] = $address;
        $data['border_remark'] = $border_remark;
        $data['border_type'] = $border_type;
        $data['border_way'] = 2;
        M('bookorder')->where("border_id='".$border_id."' ")->save($data); 
        $this->redirect('book/weixinpay', array('order_id' => $border_id), 0, '页面跳转中...'); 
    }
    
    //支付开始============
	//微信支付
    public function weixinpay() {
		header("Content-type: text/html; charset=utf-8");
        $order_id = I('get.order_id'); 
        //根据订单号查询所需的订单编号，订单金额
		$orderlist=M('bookorder')->where('border_id='.$order_id)->field('border_money,border_number,border_id')->find(); 
		$out_trade_no=$orderlist['border_number'];//订单编号
		$amount=$orderlist['border_money'];//订单金额
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
        $input->SetNotify_url(WEB_HOST . 'index.php/User/book/notify'); //通知地址
        $input->SetTrade_type("JSAPI"); //交易类型 取值如下：JSAPI，NATIVE，APP等
        $input->SetOpenid($openId);
        $order = \WxPayApi::unifiedOrder($input); 
        $jsApiParameters = $jsApi->GetJsApiParameters($order); 
        //获取共享收货地址js函数参数
        //$editAddress = $jsApi->GetEditAddressParameters();
        $this->assign('jsApiParameters', $jsApiParameters);
        $token = md5($order_id . "weur2938423"); 
        $this->assign('SuccessUrl', U('book/SuccessUrl', array('order_id' => $order_id, "token" => $token))); //支付成功跳转页面
        $this->assign('FailUrl', U('book/FailUrl', array("order_id" => $order_id,))); //支付失败跳转页面 
        $this->display();
    }

    //支付成功页面
    public function SuccessUrl() {
		header("Content-type: text/html; charset=utf-8");
        $order_id = I("get.order_id");
        $token = I("get.token");
        $user_id = session("user_id");
        if ($token != md5($order_id . "weur2938423")) {
            jump_success('非法操作，参数错误',U('index/index'));
			//echo "<script>alert('非法操作，参数错误');location.href='".U("index/index")."'</script>";
			exit();
        }
        //引入WxpayAPI
        vendor('WxpayAPI.WxPayJsApiPay');
        vendor('WxpayAPI.WxPayNotify');
        //使用通用通知接口
        $notify = new \PayNotify();
        $flag = $notify->Queryorder_out_trade_no(session("weixinpay_out_trade_no")); 
        if (!$flag) {
            jump_success('非法操作，订单不存在！',U('index/index'));
			//echo "<script>alert('非法操作，订单不存在！');location.href='".U("index/index")."'</script>";
			exit();
        }
        //更新订单记录  2待发送
        $alss= M("bookorder")->where(" border_id=" . $order_id . " and user_id=" . session("user_id"))
		->save(array("border_state" => 2));
        if ($alss) {
			//先根据订单号查下单数量
			//先根据订单号查下单数量
            $order=M('bookorderlist')->where('border_id='.$order_id)->select(); 
            $orders=M('bookorder')->where('border_id='.$order_id)->find();
            foreach($order as $k=>$v){
                M("book")->where("book_id=".$v['book_id'])->setDec('book_num',$v['book_num']);	    //减少图书库存
                M("book")->where("book_id=".$v['book_id'])->setInc('book_buynum',$v['book_num']);	//增加图书销售量 
            } 
			//给农户发消息
			/*$message= new \Admin\Model\MessageModel();
			$msg_title="图书订单支付成功消息通知";
			$messages="您有新的图书订单(订单编号为:".$orders['border_number'].")等待处理";
			$message->getmessage_($orders['user_id'],1,$msg_title,$messages);*/ 
            jump_success('订单支付成功！',U('book/bookorder'));
			//echo "<script>alert('订单支付成功！');location.href='".U("book/bookorder")."'</script>";
			exit();
        } else {
            jump_success('非法操作，数据更新失败！',U('index/index'));
			//echo "<script>alert('非法操作，数据更新失败！');location.href='".U("index/index")."'</script>";
			exit();
        }
    }

    //支付失败页面
    public function FailUrl() {
		header("Content-type: text/html; charset=utf-8");
        $order_id = I("order_id");
        jump_success('订单支付失败！',U('book/bookorder'));
		//echo "<script>alert('订单支付失败！');location.href='".U("book/bookorder")."'</script>";
		exit();
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
			$flag2= M("bookorder")->where(" border_id=" . $order_id . " and user_id=" . session("user_id"))
		    ->save(array("border_state" => 2));
			if($flag2){
                $order=M('bookorderlist')->where('border_id='.$order_id)->select(); 
                $orders=M('bookorder')->where('border_id='.$order_id)->find();
                foreach($order as $k=>$v){
                    M("book")->where("book_id=".$v['book_id'])->setDec('book_num',$v['book_num']);	    //减少图书库存
                    M("book")->where("book_id=".$v['book_id'])->setInc('book_buynum',$v['book_num']);	//增加图书销售量 
                } 
                //给农户发消息
                /*$message= new \Admin\Model\MessageModel();
                $msg_title="图书订单支付成功消息通知";
                $messages="您有新的图书订单(订单编号为:".$orders['border_number'].")等待处理";
                $message->getmessage_($orders['user_id'],1,$msg_title,$messages);*/ 
                session("weixinpay_orderid", "");
                session("weixinpay_out_trade_no", "");
                session("order_id",""); 
			}            
        }
        $notify->Handle(false);
    }
	 
    
    
    
	 
	 
}