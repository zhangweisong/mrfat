<?php 
namespace User\Controller;
use Think\Controller;
class ProvinceController extends Controller{
	function __construct() {
        //继承父类
        parent::__construct();
    }
    //查询省
    function province(){
    	$info=M("province")->select();
    	$this->ajaxReturn($info);
    }
    //查询市
    function city(){
    	$id=I("get.id");
    	$info=M("city")->where("p_id=$id")->select();
    	$this->ajaxReturn($info);
    }
    //查询区
    function area(){
    	$id=I("get.id");
    	$info=M("area")->where("c_id=$id")->select();
    	$this->ajaxReturn($info);
    }
}
 ?>