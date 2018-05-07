<?php
namespace Factory\Model;
use Think\Model;
class AgcyapplyModel extends Model{
     
    //状态
    public function state(){
        return array(
            '1'=>'申请',
            '2'=>'通过',
            '3'=>'驳回',
            );
    }  

}