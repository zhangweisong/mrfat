<?php
namespace Expert\Controller;
use Think\Controller;
class LoginController extends Controller {
	function __construct() {
		//继承父类
		parent::__construct();
    }
	//空方法，防止报错
	public function _empty(){
        $this->login();
    }
	
	//登录页面
    public function login(){ 
		$this->display();  
    }
	
	public function check(){
		$tel = trim(I("tel"));
		$password_ = trim(I("password"));
        $password = md5($password_);
		$expert = M("expert")->where("expert_username='".$tel."' and expert_pwd='".$password."' ")->find();
		if($expert){
			session("expert_id",$expert['expert_id']);
			$this->ajaxReturn(array("data"=>1));
		}else{
			$this->ajaxReturn(array("data"=>0));
		} 	
	}
	 
}