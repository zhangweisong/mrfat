<?php
namespace Admin\Model;
use Think\Model;
class BookorderModel extends Model{
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
	
	public function classify_isable(){
		return array(
			'1'=>'启用',
			'2'=>'禁用',
		);
	}
	
	public function isable(){
		return array(
			'1'=>'启用',
			'0'=>'禁用',
		);
	}
}