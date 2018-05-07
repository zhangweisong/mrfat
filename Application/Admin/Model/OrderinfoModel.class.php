<?php
namespace Admin\Model;
use Think\Model;
class OrderinfoModel extends Model 
{
	 //支付方式
    public function pay_type(){
        return array(
            '1'=>'微信', 
            '2'=>'余额'   
        );
    }
   //订单状态
    public function state(){
        return array(
            '1'=>'待付款',
            '2'=>'待发货',
            '3'=>'已完成',
            );
    }
}