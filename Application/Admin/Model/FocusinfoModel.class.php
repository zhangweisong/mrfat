<?php
namespace Admin\Model;
use Think\Model;

class FocusinfoModel extends Model {

    //轮播类型1首页轮播，2其他轮播
    public function focus_type(){

       $info =  M('focusclassify')->where(array('focusclassify_isable'=>1))->field('focusclassify_id,focusclassify_name')->select();
        $return = array();
        foreach ($info as $key => $value) {
            $return[$value['focusclassify_id']] = $value['focusclassify_name'];
        }
         return  $return;
    }
    //排序
    public function focus_sort(){
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