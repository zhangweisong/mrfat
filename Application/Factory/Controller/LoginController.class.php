<?php
namespace Factory\Controller;
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
		if (IS_POST){
			$username=trim(I("username"));
			$password=trim(I("password"));
			$flag=D("factory")->factorylogin($username,$password);
			if($flag){
				jump('登录成功！',U('index/index'),3);
			}else{
				jump('账号或密码有误，请联系管理员！',U('login/login'),3);
			}
		}else{
			$this->display();
		}
    }
	//退出系统
	public function loginout(){
		session('factory',null);
		header("Content-Type:text/html;charset=utf-8");
		echo("<script>top.location.href='index.php?m=factory&c=login&a=login';</script>");
		exit();
    }
}