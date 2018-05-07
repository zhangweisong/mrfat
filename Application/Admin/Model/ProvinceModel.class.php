<?php
namespace Admin\Model;
use Think\Model;

class ProvinceModel extends Model {
    //获取省份名称
    public function  getProvinceName(){
        $province=M("province")->select();
        $return=array();
        foreach ($province as $key=>$value){
            $return[$value['p_id']]=$value["p_name"];
        }
        return  $return;
    }

    //获取城市名称  //前端offer控制器也用到此model修改请慎重
    public function  getCityName(){
        $city=M("city")->select();
        $return=array();
        foreach ($city as $key=>$value){
            $return[$value['c_id']]=$value["c_name"];
        }
        return  $return;
    }

    //获取县区名称 //前端offer控制器也用到此model修改请慎重
    public function  getAreaName(){
        $area=M("area")->select();
        $return=array();
        foreach ($area as $key=>$value){
            $return[$value['a_id']]=$value["a_name"];
        }
        return  $return;
    }

}