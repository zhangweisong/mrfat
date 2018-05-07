<?php

/**
 * 
 */

namespace Agcy\Controller;

use Think\Controller;

class UserController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录
        $agcy_id=session("agcy_id");
        if($agcy_id==""){//未登录
            header("Location:".U('login/login'));
            exit();
        }

    }
    //空方法，防止报错
    public function _empty(){
        $this->redirect("userlist"); 
    }
                
    //客户列表
    public function userlist() {
        $agcy_id =session("agcy_id");
        $count = M("attention")
                ->where("ec_attention.agcy_id=" . $agcy_id)
                ->join("LEFT JOIN ec_user ON ec_user.user_id = ec_attention.user_id")
                ->count();
        $Page = new \Think\Page($count, 15);
        $userlist = M("attention")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->where("ec_attention.agcy_id=" . $agcy_id)
                ->join("LEFT JOIN ec_user ON ec_user.user_id = ec_attention.user_id")
                ->order("ec_attention.user_id DESC")
                ->select(); 
        foreach ($userlist AS $k => $v) {
            $userlist[$k]['moneysum'] = M("orderinfo")->where("user_id=" . $v['user_id'] . " AND agcy_id=" . $v['agcy_id'] . " AND order_state=3")->sum('order_money');
        }
        if (IS_AJAX) {
            if (count($userlist) > 0) {
                $html = '';
                foreach ($userlist AS $k => $v) {
                    $html .= "<li>
                                <a href='" . U('user/userdetail', array('user_id' => $v['user_id'])) . "' class='flexb'>
                                    <p>" . $v['realname'] . "</p>
                                    <p>累计消费：" . $v['moneysum'] . "</p>
                                    <img src='public/agcy/images/right.png' />
                                </a>
                            </li>";
                }
                $this->ajaxReturn($html);
            } else {
                $this->ajaxReturn(null);
            }
        } else {
            $this->assign("userlist", $userlist);
            $this->display();
        }
    }

    //客户详情
    public function userdetail() {
        $agcy_id = session("agcy_id");
        $user_id = I("get.user_id");
        $userinfo = M("user")
                ->where("ec_user.user_id=" . $user_id) 
                ->find();
        $userinfo['moneysum'] = M("orderinfo")->where("user_id=" . $userinfo['user_id'] . " AND agcy_id=" . $agcy_id . " AND order_state=3")->sum('order_money');
        $this->assign("userinfo", $userinfo);
        $this->display();
    }

    //群发短信
    public function sendmsg() { 
        $this->display(); 
    }
    
    //发短信，获取验证码
    public function smsCode(){
        //生成验证码
        $content= I('content'); 
        $verify =$content;
         
        //查询该经销商底下的所有用户
        $agcy_id = session("agcy_id");
        $user = M("attention")
            ->join('left join ec_user on ec_attention.user_id=ec_user.user_id')
            ->where("ec_attention.agcy_id")
            ->select();
        $mobiles = [];    
        foreach($user as $k=>$v){
           $mobiles[] = $v['phone'];  
        }
        $mobile = implode(',',$mobiles);
      //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

		//创蓝发送短信接口URL, 请求地址请参考253云通讯自助通平台查看或者询问您的商务负责人获取
		$chuanglan_config['api_send_url'] = 'http://smssh1.253.com/msg/send/json';
		//创蓝变量短信接口URL, 请求地址请参考253云通讯自助通平台查看或者询问您的商务负责人获取
		$chuanglan_config['API_VARIABLE_URL'] = 'http://smssh1.253.com/msg/variable/json';
		 
		//创蓝短信余额查询接口URL, 请求地址请参考253云通讯自助通平台查看或者询问您的商务负责人获取
		$chuanglan_config['api_balance_query_url'] = 'http://smssh1.253.com/msg/balance/json';
		//创蓝账号 替换成你自己的账号
		$chuanglan_config['api_account']    = 'CN0721220';

		//创蓝密码 替换成你自己的密码
		$chuanglan_config['api_password']   = 'elVdcw0Zrv1732';

		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		//创蓝接口参数
		$postArr = array (
			'account'  =>  $chuanglan_config['api_account'],
			'password' => $chuanglan_config['api_password'],
			'msg' => urlencode($verify),
			'phone' => $mobile,
			'report' => 'true'
		);
		//$result = $this->curlPost( $chuanglan_config['api_send_url'] , $postArr); 
        $this->ajaxReturn(array("data"=>$result));

    }
	/**
     * 通过CURL发送HTTP请求
     * @param string $url  //请求URL
     * @param array $postFields //请求参数 
     * @return mixed
     */
    private function curlPost($url,$postFields){
        $postFields = json_encode($postFields);
        $ch = curl_init ();
        curl_setopt( $ch, CURLOPT_URL, $url ); 
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8'
            )
        );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt( $ch, CURLOPT_TIMEOUT,1); 
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec ( $ch );
        if (false == $ret) {
            $result = curl_error(  $ch);
        } else {
            $rsp = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "请求状态 ". $rsp . " " . curl_error($ch);
            } else {
                $result = $ret;
            }
        }
        curl_close ( $ch );
		
        return $result;
    }

    //客户订单详情
    public function userorder() {
        $agcy_id = session("agcy_id");
        $user_id = I("get.user_id");
        $count = M("orderinfo")
                ->where("ec_orderinfo.user_id=" . $user_id . " AND ec_orderinfo.agcy_id=" . $agcy_id)
                ->count();
        $Page = new \Think\Page($count, 15);
        $orderlist = M("orderinfo")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->where("ec_orderinfo.user_id=" . $user_id . " AND ec_orderinfo.agcy_id=" . $agcy_id)
                ->order("ec_orderinfo.order_addtime DESC")
                ->select();
        foreach ($orderlist AS $k => $v) {
            $orderlist[$k]['goodslist'] = M("orderlist")
                    ->join("LEFT JOIN ec_agcygoods ON ec_agcygoods.agcy_goods_id=ec_orderlist.goods_id")
                    ->where("order_id=" . $v['order_id'])
                    ->select();
            //$orderlist[$k]['order_sendtime'] = date("Y-m-d H:i:s", $v['order_sendtime']);
        }
        if (IS_AJAX) {
            if (count($orderlist) > 0) {
                $html = '';
                foreach ($orderlist AS $k => $v) {
                    $html .= '<div class="cont">
                                <p>' . $v['order_addtime'] . '<span>已完成</span></p>';
                    foreach ($v['goodslist'] AS $kk => $vv) {
                        $image = explode('|', $vv['agcy_goods_images']);
                        $html .= '<div class="sp">
                                    <img alt="产品图" src="' . $image[0] . '" />
                                    <p class="omit1">' . $vv['agcy_goods_name'] . '</p>
                                    <p>￥:' . $vv['goods_num'] * $vv['goods_price'] . '</p>
                                    <p>规格：' . $vv['goods_spec'] . '<span>X' . $vv['goods_num'] . '</span></p>
                                </div>';
                    }
                    $html .= '<div class="hj"> 
                            <p><span>合计￥' . $v['order_money'] . '元</span></p>
                        </div>
                        <div class="clear" style="height: 0.5rem;"></div>
                    </div>';
                }
                $this->ajaxReturn($html);
            } else {
                $this->ajaxReturn(null);
            }
        } else {
            $this->assign("user_id", $user_id);
            $this->assign("orderlist", $orderlist);
            $this->display();
        }
    }

    //经销商订单详情（本人
    public function agcyorder() {
        $agcy_id = session("agcy_id");
        $where = "ec_forder.agcy_id=" . $agcy_id;
        if (I("type")) {
            $where .= " AND ec_forder.forder_flag=" . I("type");
        }
        $count = M("forder")
                ->where($where)
                ->count();
        $Page = new \Think\Page($count, 5);
        $orderlist = M("forder")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->where($where)
                ->order("ec_forder.forder_addtime DESC")
                ->select();
        foreach ($orderlist AS $k => $v) {
            $orderlist[$k]['forder_addtime'] = date("Y-m-d H:i:s", $v['forder_addtime']);
            $orderlist[$k]['goodslist'] = M("forderlist")
                    ->join("LEFT JOIN ec_fgoods ON ec_fgoods.fgoods_id=ec_forderlist.fgoods_id")
                    ->where("forder_id=" . $v['forder_id'])
                    ->select();
            //1=待付款 2=待发货 3=已完成'
            switch ($v['forder_flag']) {
                case 1:
                    $orderlist[$k]['span_html'] = '<button onclick="location.href=\''.U("ordermanagement/jiesuan",array('forder_id'=>$v['forder_id'],'state'=>1)).'\'">去支付</button>';
                    break;
                case 2:
                    $orderlist[$k]['span_html'] = ' ';
                    break;
                case 3:
                    $orderlist[$k]['span_html'] = ' ';
                    break;
                default:
                    break;
            }
        }
        //dd($orderlist);
        if (IS_AJAX) {
            if (count($orderlist) > 0) {
                $html = '';
                foreach ($orderlist AS $k => $v) {
                    $html .= '<div class="cont">
                                <p>' . $v['forder_addtime'] . '<span>' . $v['forder_flagstr'] . '</span></p>';
                    foreach ($v['goodslist'] AS $kk => $vv) {
                        $image = explode('|', $vv['fgoods_images']);
                        $html .= '<div class="sp">
                                    <img alt="产品图" src="' . $image[0] . '" />
                                    <p class="omit1">' . $vv['fgoods_name'] . '</p>
                                    <p>￥:' . $vv['forderlist_totprice']/$vv['forderlist_tonnum'] . '</p>
                                    <p>规格：' . $vv['forderlist_spec'] . '<span>X' . $vv['forderlist_tonnum'] . '</span></p>
                                </div>';
                    }
                    $html .= '<div class="hj">
                                   <!-- <p>物流：￥' . $v['forder_tpcost'] . '元</p>-->
                                    <p>共计' . count($v['goodslist']) . '件商品，<span>合计￥' . $v['forder_total'] . '元(含物流)</span></p>
                                </div>
                                '.$v['span_html'].'
                                <div class="clear" style="height: 0.5rem;"></div>
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
}
