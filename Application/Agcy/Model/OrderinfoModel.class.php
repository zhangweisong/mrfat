<?php
namespace Agcy\Model;
use Think\Model;
class OrderinfoModel extends Model
{   //订单状态
    public function  ordertype(){
		return array (
		    '1'=>'待付款',
            '2'=>'待发货',
            '3'=>'已完成',
		);
	}
	//订单支付方式
	public function  orderpay(){
		return array (
		    '1'=>'微信',
		);
	}
}