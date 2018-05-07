<?php
namespace User\Controller;
use Think\Controller;
//秒杀 团购 结算
class ActbuyController extends Controller {

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
     
    
    //结算
    public function jiesuan(){
        //秒杀 团购支付 页面 
        $user_id=session('user_id');
        $order_id = I("order_id");
        $orderinfo = M("orderinfo")->find($order_id); //订单信息
        $goodsinfo = M("orderlist") //商品信息
                ->join("LEFT JOIN ec_agcygoods ON ec_agcygoods.agcy_goods_id=ec_orderlist.goods_id")
                ->where("ec_orderlist.order_id=".$order_id)
                ->find();
        //自提地址显示经销商地址 
        $agcyinfo = M("agcy")->find($orderinfo['agcy_id']);
        $this->assign("agcyinfo",$agcyinfo);
        $this->assign("orderinfo",$orderinfo);
        $this->assign("goodsinfo",$goodsinfo);
        $this->display(); 
    }
    
    
    //支付
    public function zhifu(){ 
        // 添加用户备注 
        $type = I("post.zhifu"); 
        $order_id = I("post.order_id");
        $this->paytypes($order_id,$type);//跳支付  微信支付
        
    }
    
    //支付方式
    public function paytypes($order_id,$type){
        if($type==1){
           //微信支付
           //重定向
            $this->redirect('actbuy/weixinpay', array('order_id' => $order_id), 0, '页面跳转中...');
        }else if($type==2){//银联支付
            dd('暂未开发');
        }else{
            echo "<script>alert('请选择支付方式');location.href='".U("index/index")."'</script>";
            exit(); 
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
        $input->SetTotal_fee(1); //订单总金额，单位为分
        //$input->SetTotal_fee($amount * 100); //订单总金额，单位为分
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
			echo "<script>alert('非法操作，参数错误');location.href='".U("index/index")."'</script>";
			exit();
        }
        //引入WxpayAPI
        vendor('WxpayAPI.WxPayJsApiPay');
        vendor('WxpayAPI.WxPayNotify');
        //使用通用通知接口
        $notify = new \PayNotify();
        $flag = $notify->Queryorder_out_trade_no(session("weixinpay_out_trade_no"));
		//dump($flag);
        if (!$flag) {
			echo "<script>alert('非法操作，订单不存在！');location.href='".U("index/index")."'</script>";
			exit();
        }
        //更新订单记录 
       $alss= M("orderinfo")->where(" order_id=" . $order_id)->save(array("order_state" => 3));
        if ($alss) {
			//查询agcy_id
			$orders=M('Orderinfo')->where('order_id='.$order_id)->find();
            
			//给商户发消息
			$message= new \Admin\Model\MessageModel();
			$msg_title="订单支付成功消息通知";
			$messages="您有新的订单(订单编号为:".$orders['order_number'].")等待处理";
			$message->getmessage_($orders['agcy_id'],2,$msg_title,$messages);
			echo "<script>alert('订单支付成功！');location.href='".U("expert/wodeshangpindingdan")."'</script>";
			exit();
        } else {
			echo "<script>alert('非法操作，数据更新失败！');location.href='".U("index/index")."'</script>";
			exit();
        }
    }

    //支付失败页面
    public function FailUrl() {
		header("Content-type: text/html; charset=utf-8");
        $order_id = I("order_id");
		echo "<script>alert('订单支付失败！');location.href='".U("expert/wodeshangpindingdan")."'</script>";
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
			$flag2=M("orderinfo")->where("order_number=" . session("weixinpay_out_trade_no"))->save(array("order_state" => 3));
			if($flag2){ 
                //查询agcy_id
                $orders=M('Orderinfo')->where('order_id='.session("order_id"))->find();
                 //M('Agcy')->where('agcy_id='.$orders['agcy_id'])->setInc('shop_ordernumber',1);//给店铺的历史订单数加1
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