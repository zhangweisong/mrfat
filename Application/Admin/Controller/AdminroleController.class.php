<?php
namespace Admin\Controller;
use Think\Controller;
class adminroleController extends Controller {
	function __construct() {
		//继承父类
		parent::__construct();
		//判断登录状态
		if(!D('admininfo')->islogin()){//未登录
			jump('您尚未登录，请先登录！',U('login/login'));
		}	
    }

	//空方法，防止报错
	public function _empty(){
        $this->index();
    }	
	//管理角色列表
    public function index(){
		$where="1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$where.="and role_name like '%$keyword%' ";
				$this->assign('keyword',$keyword);
			}
		}
		$admingroup=M('adminrole');
		// 查询满足要求的总记录数
		$count      = $admingroup->where($where)->count();
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$listinfo = $admingroup
					->where($where)
					->order('role_id desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$this->assign('listinfo',$listinfo);
		$this->assign('pageshow',$pageshow);
		$this->display();
    }
	//管理员角色存在性
	public function existrole(){
		$param=I("param");
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{
			$count=M("adminrole")->where("role_name = '".$param."'")->count();
			if($count){//存在，验证失败
				$status="n";
				$info="此用户名已存在";
			}else{//不存在，验证成功
				$status="y";
				$info="此用户名不存在，可以增加";
			}
		}
		$this->ajaxReturn(array('status'=>$status,'info'=>$info));
	}
	//添加角色
    public function addadmingroup(){
		if (IS_POST){
			$info=I("info");
			$info['role_name']=trim($info['role_name']);
			$menu_id=$info['menu_id'];
			$info['role_power']=join(",",$menu_id);
			unset($info['menu_id']);
			$flag=M("adminrole")->add($info);
			if($flag){
				F("group_menu_".$flag,$menu_id);
		
				jump('添加角色成功！',U('adminrole/index'));
			}else{
				jump('添加角色失败！',U('adminrole/index'));
			}
		}else{
			$adminmenu=D("adminmenu")->getmenuninfo(0);
			$this->assign('adminmenu',$adminmenu);
			$this->display();
		}
    }

	//编辑角色
    public function editadmingroup(){
		$role_id=I("get.role_id");
		if (IS_POST){
			$info=I("info");
			$info['role_name']=trim($info['role_name']);
			$menu_id=$info['menu_id'];
			$info['role_power']=join(",",$menu_id);
			unset($info['menu_id']);
			
			$flag=M("adminrole")->where("role_id=".$role_id)->save($info);			
			F("group_menu_".$role_id,$menu_id);
			jump('编辑角色成功！',U('adminrole/index'));
			
		}else{
			$groupinfo=M("adminrole")->where("role_id=".$role_id)->find();
			$this->assign('groupinfo',$groupinfo);

			$adminmenu=D("adminmenu")->getmenuninfo(0);
			$this->assign('adminmenu',$adminmenu);
			$this->display();
		}
    }

	//删除角色
	public function deladmingroup(){
		$role_id=I("get.role_id");
		M('adminrole')->where('role_id='.$role_id)->delete();
		jump('角色删除成功！',U('adminrole/index'));
	}

}