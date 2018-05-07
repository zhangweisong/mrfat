<?php
namespace Admin\Model;
use Think\Model;

class AdmininfoModel extends Model {

	//判断当前用户是否登录
	/*
	 * return true/false
	 */
	public function islogin(){
		$admin_id = session('admin.admin_id',"");//管理员id
		$admin_username = session('admin.username',"");//管理员username
		$admin_role = session('admin.adminrole',"");//管理员role
		if(!$admin_id || !$admin_username || !$admin_role){
			return false;
		}else{
			return true;
		}
	}
	
	//判断当前用户是否登录
	/*
	 * controller 控制器
	 * action 方法
	 * return true/false
	 */
	public function isoperate($controller,$action){
		if(!$controller || !$action)return false;
		//管理员组信息
		$group_id = session('admin.adminrole',"");//管理员role
		$groupinfo=F('group_menu_'.$group_id);//获取本管理员组的group_menu缓存数组
		
		//菜单信息
		$menuinfo=M("adminmenu")->where("controller='".strtolower($controller)."' and action='".strtolower($action)."'")->find();

		if(in_array($menuinfo['menu_id'],$groupinfo) || !$menuinfo){//在数组中或者查无结果，均为授权
			return true;
		}else{
			return false;
		}
	}

	//管理员登录
	/*
	 * username 用户名
	 * password 密码
	 * return true/false
	 */
	public function adminlogin($username,$password){
		if(!$username || !$password)return false;
		$data = $this->where("username='".$username."' and isable=1")->find();
		if($data){
			if($data['password']==md5($password)){
				session('admin',$data); 
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
	 * admin_id 管理员id
	 * oldpassword 原密码
	 * newpassword 新密码
	 * return true/false
	 */
	public function pswadmin($admin_id,$oldpassword,$newpassword){
		if(!$admin_id || !$oldpassword || !$newpassword)return false;

		$data = $this->where("admin_id=".$admin_id)->find();
		if($data){
			if($data['password']==md5($oldpassword)){
				$newpassword=md5($newpassword);
				$this->where("admin_id=".$admin_id)->save(array("password"=>$newpassword));
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	//管理员增加
	/*
	 * info 管理员增加信息
	 * return true/false
	 */
	public function addadmin($info){
		if(!$info)return false;
		$info['username']=trim($info['username']);
		$info['password']=md5($info['password']);
		$info['realname']=trim($info['realname']);
		$info['phone']=trim($info['phone']);
		$info['adminrole']=trim($info['adminrole']);
		$date=$this->add($info);
		return true;
	}

	//管理员编辑
	/*
	 * admin_id 管理员id
	 * info 管理员编辑信息
	 * return true/false
	 */
	public function editadmin($admin_id,$info){
		if(!$info || !$admin_id)return false;

		$info['role']=trim($info['role']);
		$this->where("admin_id=".$admin_id)->save($info);
		return true;
	}
	//获取管理员状态
	/*
	 * return array  `isable` tinyint(4) DEFAULT '1' COMMENT '状态，1启用，2禁用',
	 */
	public function admin_isable(){
		return array(
			'1'=>'启用',
			'2'=>'禁用',
		);
	}

}




















