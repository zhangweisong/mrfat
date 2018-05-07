<?php

/**
 * 
 */

namespace Agcy\Controller;

use Think\Controller;

class LoansController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录
        $agcy_id=session("agcy_id");
        if($agcy_id==""){//未登录
            header("Location:".U('login/login'));
            exit();
        }

    }
    //空方法，防止报错
    public function _empty(){
        $this->redirect("userlist"); 
    }
                
    //农资贷款
    public function nzdk() {
        $agcy_id =session("agcy_id");
        if(IS_POST){
            $agcym_id=I("post.agcym_id");      
            $flag= M('loans')->add(array(
                'agcy_id'=>$agcy_id,
                'agcym_id'=>$agcym_id,
                'status'=>3,
                'loans_time'=>time()
            ));
            if($flag){
                $this->ajaxReturn(array("data"=>1));
            }else{
                $this->ajaxReturn(array("data"=>0));
            }
        }else{
            //店铺负责人信息
            $agcy=M('agcy')->where('agcy_id='.$agcy_id)->find();
            //贷款额度范围
            $agcymoney=M('Agcymoney')->select();
            $this->assign('agcy',$agcy);
            $this->assign('agcymoney',$agcymoney);
            $this->display(); 
        }
    }

   
}
