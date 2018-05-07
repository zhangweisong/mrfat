<?php
namespace Admin\Model;
use Think\Model;

class JifenclassifyModel extends Model {
	public function get_isable(){
		return array(
			'1'=>'启用',
			'2'=>'禁用',
		);
	}
	public function get_isables(){
		return array(
			'1'=>'是',
			'2'=>'否',
		);
	}
	public function get_state(){
		return array(
			//'1'=>'待兑换',
			'2'=>'待发货',
			'3'=>'已完成'
		);
	}
    public function get_states(){
		return array(
			'1'=>'未审核',
			'2'=>'已审核'
		);
	}
	public function get_questions_aim(){
		return array(
			'1'=>'针对',
			'2'=>'不针对'
		);
	}
	public function get_flag(){
		return array(
			'1'=>'未兑换',
			'2'=>'待发货',
			'3'=>'已完成'
			
		);
	}
	public function get_flags(){
		return array(
			'1'=>'兑换商品',
			'2'=>'平台赠送'
		);
	}
}




















