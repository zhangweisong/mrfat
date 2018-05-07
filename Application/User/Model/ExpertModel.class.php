<?php
namespace user\Model;
use Think\Model;

class ExpertModel extends Model{
    //问题分类
    public function getExpertType(){
        $info =  M('Expert_type')->where('type_isable = 1 ')->field('type_id,type_name')->select();
        $return = array();
        foreach ($info as $key => $value) {
            $return[$value['type_id']] = $value['type_name'];
        }
        return  $return;
    }
	//文章分类
	public function gettype(){
		$info =  M('savantnewsclassify')->where('snclassify_isable = 1 ')->field('snclassify_id,snclassify_name')->order('snclassify_sort ')->select();
		$return=array();
		foreach($info as $key => $value){
			 $return[$value['snclassify_id']] = $value['snclassify_name'];
		}
		return $return;
	}
}