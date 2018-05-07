<?php
namespace Admin\Model;
use Think\Model;

class CategoryModel extends Model {

	
	public function category_isable(){
		return array(
			'1'=>'启用',
			'2'=>'禁用',
		);
	}
	//排序
    public function category_sort(){
        return array(
            '1'=>'1',
            '2'=>'2',
            '3'=>'3',
            '4'=>'4',
            '5'=>'5',
            '6'=>'6',
            '7'=>'7',
            '8'=>'8',
            '9'=>'9',
            '10'=>'10',
        );
    }
}




















