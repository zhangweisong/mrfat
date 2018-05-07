<?php
namespace Admin\Model;
use Think\Model;

class AdminmenuModel extends Model {

	//获取菜单信息，递归调用
	/*
	 * father_id 父级节点菜单id
	 * return array
	 */
	function getmenuninfo($father_id=0){
		$where="1 ";
		$where.="and menu_fatherid=".$father_id;
		
		$menuninfo=$this->where($where)->order('sort desc,menu_id asc')->select();
		foreach($menuninfo as $k=>$v){//获取子分类
			$child=$this->getmenuninfo($v['menu_id']);
			if($child){
				$menuninfo[$k]['child']=$child;
			}
		}
		return $menuninfo;
	}
}