<?php
namespace agcy\Controller;
use Think\Controller;

/*
 *前端主架构控制器0902
 * controller 控制器
 * action 方法
 * return true/false
 */
class PayinfoController extends Controller {
    function __construct()
    {
        parent::__construct();
		if (__ACTION == "notify") {//微信回调不验证登录
            //
        }else{
			//判断登录
			$agcy_id=session("agcy_id");
			if($agcy_id==""){//未登录
				header("Location:".U('login/login'));
				exit();
			}
		}
    }
    //空方法，防止报错
    public function _empty(){
        echo "非法操作！！！";
        exit();
    }
	//去支付
	public function buy(){
        
		header("Content-type: text/html; charset=utf-8");
		$notes=I('get.notes')?I('get.notes'):0;//备注(经销商)
        $forder_id=I('get.forder_id'); //订单id		
        //更新备注
        M('forder')->where('forder_id='.$forder_id)->save(array('forder_memo'=>$notes));
		//重定向
		$this->redirect('payinfo/weixinpay', array('forder_id' => $forder_id), 0, '页面跳转中...');
	 }
	
	//支付开始============
	//微信支付
    public function weixinpay() {
		header("Content-type: text/html; charset=utf-8");
        $forder_id = I('get.forder_id');
        //根据订单号查询所需的订单编号，订单金额
		$orderlist=M('forder')->where('forder_id='.$forder_id)->field('forder_price,forder_number,forder_id')->find();
		$out_trade_no=$orderlist['forder_number'];//订单编号
		$amount=$orderlist['forder_price'];//订单金额
		$body="订单".$out_trade_no."下单成功";
        session("weixinpay_orderid", $forder_id);
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
        $input->SetAttach($forder_id); //附加数据-订单id
        $input->SetOut_trade_no($out_trade_no); //商户系统内部订单号，要求32个字符内、且在同一个商户号下唯一
        //$input->SetTotal_fee(1); //订单总金额，单位为分
        $input->SetTotal_fee($amount * 100); //订单总金额，单位为分
        $input->SetTime_start(date("YmdHis")); //交易起始时间
        $input->SetTime_expire(date("YmdHis", time() + 600)); //交易结束时间
        $input->SetGoods_tag($body); //商品标记
        $input->SetNotify_url(WEB_HOST . 'index.php/agcy/payinfo/notify'); //通知地址
        $input->SetTrade_type("JSAPI"); //交易类型 取值如下：JSAPI，NATIVE，APP等
        $input->SetOpenid($openId);
        $order = \WxPayApi::unifiedOrder($input);

        $jsApiParameters = $jsApi->GetJsApiParameters($order);
        //获取共享收货地址js函数参数
        //$editAddress = $jsApi->GetEditAddressParameters();
        $this->assign('jsApiParameters', $jsApiParameters);
        $token = md5($forder_id . "weur2938423");
        $this->assign('SuccessUrl', U('payinfo/SuccessUrl', array('forder_id' => $forder_id, "token" => $token))); //支付成功跳转页面
        $this->assign('FailUrl', U('payinfo/FailUrl', array("forder_id" => $forder_id,))); //支付失败跳转页面
        //echo $jsApiParameters;
        $this->display();
    }

    //支付成功页面
    public function SuccessUrl() {
		header("Content-type: text/html; charset=utf-8");
        $forder_id = I("get.forder_id");
        $token = I("get.token");
        $agcy_id = session("agcy_id");
        if ($token != md5($forder_id . "weur2938423")) { 
            jump_success('非法操作，参数错误！',U('index/index'),3);
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
            jump_success('非法操作，订单不存在！',U('index/index'),3);
			exit();
        }
        //更新订单记录  2待配送
		$alss= M("forder")->where(" forder_id=" . $forder_id . " and agcy_id=" . session("agcy_id"))
		->save(array("forder_flag" => 2,'forder_flagstr'=>'待发货'));
        if ($alss) {
			//先根据订单号查下单数量
			$order=M('forderlist')->where('forder_id='.$forder_id)->select();
			//查询厂家id ->  factory_id
			$orders=M('forder')->where('forder_id='.$forder_id)->find();
			foreach($order as $k=>$v){
				M("fgoods")->where("fgoods_id=".$v['fgoods_id'])->setDec('fgoods_stock',$v['forderlist_tonnum']);	//减少商品库存
				M("fgoods")->where("fgoods_id=".$v['fgoods_id'])->setInc('fgoods_sale',$v['forderlist_tonnum']);	//增加商品销售量 
			}
			//给厂家发消息
			$message= new \Admin\Model\MessageModel();
			$msg_title="订单支付成功消息通知";
			$messages="您有新的订单(订单编号为:".$orders['forder_number'].")等待处理";
			$message->getmessage_($orders['factory_id'],4,$msg_title,$messages); 
            jump_success('订单支付成功！',U('user/agcyorder'),3);
			exit();
        } else {  
            jump_success('非法操作，数据更新失败！',U('index/index'),3);
			exit();
        }
    }

    //支付失败页面
    public function FailUrl() {
		header("Content-type: text/html; charset=utf-8");
        $forder_id = I("forder_id");  
        jump_success('订单支付失败！',U('index/index'),3);
		exit();
    }

    //支付通知页面
    public function notify() {
		$xml = file_get_contents('php://input');
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		session("weixinpay_out_trade_no",$values['out_trade_no']);
		session("forder_id",$values['attach']);

        //引入WxpayAPI
        vendor('WxpayAPI.WxPayJsApiPay');
        vendor('WxpayAPI.WxPayNotify');
        //使用通用通知接口
        $notify = new \PayNotify();
        $flag = $notify->Queryorder_out_trade_no(session("weixinpay_out_trade_no")); //查询订单 by out_trade_no 商户订单号
        if ($flag) {
            //这里写要支付成功的操作
			//更新订单记录
			$flag2= M("forder")->where(" forder_id=" . $forder_id . " and agcy_id=" . session("agcy_id"))
			->save(array("forder_flag" => 2,'forder_flagstr'=>'待发货'));
			if($flag2){
				//先根据订单号查下单数量
				$order=M('forderlist')->where('forder_id='.session("forder_id"))->select();
				//查询厂家id ->  factory_id
				$orders=M('forder')->where('forder_id='.session("forder_id"))->find();
				foreach($order as $k=>$v){
					M("fgoods")->where("fgoods_id=".$v['fgoods_id'])->setDec('fgoods_stock',$v['forderlist_tonnum']);	//减少商品库存
					M("fgoods")->where("fgoods_id=".$v['fgoods_id'])->setInc('fgoods_sale',$v['forderlist_tonnum']);	//增加商品销售量 
				}
				//给厂家发消息
				$message= new \Admin\Model\MessageModel();
				$msg_title="订单支付成功消息通知";
				$messages="您有新的订单(订单编号为:".$orders['forder_number'].")等待处理";
				$message->getmessage_($orders['factory_id'],4,$msg_title,$messages);
				session("weixinpay_orderid", "");
				session("weixinpay_out_trade_no", "");
				session("forder_id","");
				
			}            
        }
        $notify->Handle(false);
    }
	//支付完成============

   
}