<?php
namespace Admin\Model;
use Think\Model;
class NoticeModel extends Model 
{
	 //培训机构
    public function get_train(){
        $return=array();
        $train=M('Train')->select();
        foreach($train as $k=>$v){
            $return[$v['id']]=$v['name'];
        }
        return $return;
    }
   
}