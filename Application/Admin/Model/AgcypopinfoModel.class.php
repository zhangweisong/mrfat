<?php
namespace Admin\Model;
use Think\Model;
class AgcypopinfoModel extends Model{
     
    //状态
    public function state(){
        return array(
            '1'=>'未审核',
            '2'=>'通过',
            '3'=>'驳回',
            );
    }  

	//上下架
    public function stat_(){
        return array(
            '1'=>'上架',
            '2'=>'下架', 
            );
    } 	
}