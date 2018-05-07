<?php
namespace Factory\Model;
use Think\Model;
class NewModel extends Model
{
	//新闻类型
	public function newtype(){
		 $newtype=M("newclass")
                    ->where('class_isshow=1')
                    ->field('class_id,class_name')
                    ->order('class_id desc')
                    ->select();
        $return=array();
        foreach ($newtype as $key=>$value){
            $return[$value['class_id']]=$value["class_name"];
        }
		//dd($return);
        return  $return;
	}
}