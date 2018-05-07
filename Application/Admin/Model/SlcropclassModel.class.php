<?php
namespace Admin\Model;
use Think\Model;
class SlcropclassModel extends Model{

    //
    public function cpc_isable(){
        return array(
            '1'=>'启用',
            '2'=>'禁用',
        );
    }
     public function cpc_isables(){
        return array(
            '1'=>'启用',
            '2'=>'禁用',
        );
    }
    //农作物分类
    public function cpClass(){
        $cropClass=M('slcropclass')->field('cpc_id,cpc_title')->where('cpc_isable = 1')->select();
        $return=array();
        foreach($cropClass as $k=>$v){
            $return[$v['cpc_id']]=$v['cpc_title'];
        }
        return $return;
    }

    //分类排序
    public function orderInfo(){
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

    //政策解读分类
    public function policyClass(){
        $policyClass=M('slpolicyintprtclass')->field('pic_id,pic_title')->order('pic_order,pic_id desc')->where('pic_isable = 1')->select();
        $return=array();
        foreach($policyClass as $k=>$v){
            $return[$v['pic_id']]=$v['pic_title'];
        }
        return $return;
    }
	//农作物分类
    public function cropclass(){
        $policyClass=M('slcropclass')->field('cpc_id,cpc_title')->where('cpc_isable = 1')->select();
        $return=array();
        foreach($policyClass as $k=>$v){
            $return[$v['cpc_id']]=$v['cpc_title'];
        }
        return $return;
    }

    //农作物
    public function cropName(){
        $policyClass=M('slcrop')->field('cp_id,cp_title')->where('cp_isable = 1')->select();
        $return=array();
        foreach($policyClass as $k=>$v){
            $return[$v['cp_id']]=$v['cp_title'];
        }
        return $return;
    }
    //农技分类
    public function cropskill(){
        $policyClass=M('slskillclass')->field('skc_id,skc_title')->select();
        $return=array();
        foreach($policyClass as $k=>$v){
            $return[$v['skc_id']]=$v['skc_title'];
        }
        return $return;
    }
}