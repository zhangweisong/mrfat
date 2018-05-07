<?php
namespace Admin\Model;
use Think\Model;
class UserModel extends Model{
    //农户状态
    public function userStatus(){
        return array(
            '1'=>'正常',
            '2'=>'锁定',
        );
    }
    //根据user_id 获取名称
    public function  getUserName(){
        $user=M("user")->field('user_id,realname,nickname')->select();
        $return=array();
        foreach ($user as $key=>$value){
            $return[$value['user_id']]=$value["realname"]?$value["realname"]:$value["nickname"];
        }
        return  $return;
    }
    //返佣状态
    public function incomeStatus(){
        return array(
            '1'=>'未返现',
            '2'=>'已返现',
        );
    }
    //提现第三方代付状态
    //提现状态
    public function popPaySatuas(){
//        1受理成功，2代付成功，3代付失败，4手动处理
        return array(
            '1'=>'受理成功',
            '2'=>'代付成功',
            '3'=>'代付失败',
            '4'=>'手动处理',
        );
    }

    //申请提现状态
    public function popFlag(){
        return array(
            '1'=>'申请',
            '2'=>'通过',
            '3'=>'驳回',
        );
    }

    //根据Admin_id获取Admin名称
    public function getAdminName(){
        $admin=M("admininfo")->field('admin_id,realname')->select();
        $return=array();
        foreach ($admin as $key=>$value){
            $return[$value['admin_id']]=$value["realname"];
        }
        return  $return;
    }
}