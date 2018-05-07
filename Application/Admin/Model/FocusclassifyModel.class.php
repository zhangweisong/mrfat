<?php
namespace Admin\Model;
use Think\Model;

class FocusclassifyModel extends Model {

    //轮播类型1首页轮播，2其他轮播
    public function focusclassify_isable(){
        return array(
            '1'=>'启用',
            '2'=>'禁用',
        );
    }
    //排序
    public function focusclassify_sort(){
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