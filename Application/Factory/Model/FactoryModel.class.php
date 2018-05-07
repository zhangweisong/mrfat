<?php
namespace Factory\Model;
use Think\Model;

class FactoryModel extends Model {

	//判断当前用户是否登录
	/*
	 * return true/false
	 */
	public function islogin(){
		$factory_id = session('factory.factory_id',"");//管理员id
		$factory_usrnm = session('factory.factory_usrnm',"");//管理员username
		if(!$factory_id || !$factory_usrnm){
			return false;
		}else{
			return true;
		}
	}

	

	//管理员登录
	/*
	 * username 用户名
	 * password 密码
	 * return true/false
	 */
	public function factorylogin($username,$password){
		if(!$username || !$password)return false;
		$data = $this->where("factory_usrnm='".$username."' and factory_state=1")->find();
		if($data){
			if($data['factory_pwd']==md5($password)){
				session('factory',$data); 
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	//管理员修改密码
	/*
	 * factory_id 管理员id
	 * oldpassword 原密码
	 * newpassword 新密码
	 * return true/false
	 */
	public function pswadmin($factory_id,$oldpassword,$newpassword){
		if(!$factory_id || !$oldpassword || !$newpassword)return false;

		$data = $this->where("factory_id=".$factory_id)->find();
		if($data){
			if($data['factory_pwd']==md5($oldpassword)){
				$newpassword=md5($newpassword);
				$this->where("factory_id=".$factory_id)->save(array("factory_pwd"=>$newpassword));
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public function f_classify_able(){
		return array(
			'1'=>'启用',
			'2'=>'禁用',
		);
	}

}




















