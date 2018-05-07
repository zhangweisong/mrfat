<?php
namespace Agcy\Controller;
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
		$agcy = M("agcy")->where("agcy_username='".$tel."' and agcy_password='".$password."' ")->find();
		if($agcy){
			session("agcy_id",$agcy['agcy_id']);
			$this->ajaxReturn(array("data"=>1));
		}else{
			$this->ajaxReturn(array("data"=>0));
		} 	
	}
	 
}