<?php
namespace Admin\Controller;
use Think\Controller;
class AdmininfoController extends Controller {
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
	
	//管理员列表
    public function index(){
		$where="1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword']));
				$where.="and (username like '%".$info['keyword']."%' or realname like '%".$info['keyword']."%' or phone like '%".$info['keyword']."%')";  
				$this->assign('keyword',$info['keyword']);
			}
			if($isable){
				$info['isable']=urldecode(trim($info['isable']));
				$where.="and isable ='".$info['isable']."'";  
				$this->assign('isable',$info['isable']);
			}			
		}
		$admin=M('admininfo');
		// 查询满足要求的总记录数
		$count      = $admin->where($where)->count();
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$listinfo = $admin
					->where($where)
					->order('admin_id desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		foreach($listinfo as $k=>$v){
			$groupinfo=M("adminrole")->where("role_id=".$v['adminrole'])->find();
			$listinfo[$k]['group_name']=$groupinfo['role_name'];
			unset($groupinfo);
		}
		$this->assign('listinfo',$listinfo);
		$this->assign('pageshow',$pageshow);
		$this->assign('admin_isable',D("admininfo")->admin_isable());//管理员状态
		$this->display();
    }

	//管理员用户名存在性
	public function existadmin(){
		$param=I("param");
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{
			$count=M("admininfo")->where("username = '".$param."'")->count();
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

	//添加管理员
    public function addadmin(){
		
		if (IS_POST){
			$info=I("info");
			$flag=D("admininfo")->addadmin($info);
			if($flag){
				
				jump('添加管理员成功！',U('admininfo/index'));
			}else{
				jump('添加管理员失败！',U('admininfo/index'));
			}
		}else{
			
			$groupinfo = M('adminrole')->order('role_id desc')->select();
			$this->assign('groupinfo',$groupinfo);
			$this->display();
		}
    }

	//编辑管理员
    public function editadmin(){
		$admin_id=I("get.admin_id");
		if (IS_POST){
			$info=I("info");
			$flag=D("admininfo")->editadmin($admin_id,$info);
			jump('编辑管理员成功！',U('admininfo/index'));
			
		}else{
			$admininfo=M("admininfo")->where("admin_id=".$admin_id)->find();
			$this->assign('admininfo',$admininfo);
			$groupinfo = M('adminrole')->order('role_id desc')->select();
			$this->assign('groupinfo',$groupinfo);
			$this->display();
		}
    }

	//修改密码
    public function pswadmin(){
		$admin_id=I("get.admin_id");
		if (IS_POST){
			$info=I("info");
		
			$flag=D("admininfo")->pswadmin($admin_id,trim($info['oldpassword']),trim($info['newpassword']));
			if($flag){
			
				jump('密码修改成功！',U('admininfo/index'));
			}else{
				jump('密码修改失败！',U('admininfo/index'));
			}
		}else{
	
			$admininfo=M("admininfo")->where("admin_id=".$admin_id)->find();
			$this->assign('admininfo',$admininfo);
			$this->display();
		}
    }
	//开启或禁用管理员
	public function isable(){
		$admin_id=I("get.admin_id");
		$isable=I("get.isable");
		$info['isable']=$isable;
		M('admininfo')->where('admin_id='.$admin_id)->save($info);
		
		jump('管理员设置成功！',U('admininfo/index'));
	}
	//修改账号密码
	public function resetpswadmin(){
		$admin_id=session('admin.admin_id');
		if (IS_POST){
			$admin_id=I("get.admin_id");
			$info=I("info");
			$oldpassword=MD5(trim($info['oldpassword']));
			if(trim($info['oldpassword'])==trim($info['newpassword'])){
				jump('原密码和新密码不能一致，修改失败！',U('admininfo/resetpswadmin'));
				exit();
			}
			$admininfo=M("admininfo")->where("admin_id=".$admin_id)->find();
			if($admininfo['password']==$oldpassword){
				$info_['password']=MD5(trim($info['newpassword']));
				M('admininfo')->where('admin_id='.$admin_id)->save($info_);
				jump('密码修改成功！',U('admininfo/resetpswadmin'));			
			}else{
				jump('密码修改失败，原密码不正确！',U('admininfo/resetpswadmin'));
			}			
		}else{
			$admininfo=M("admininfo")->where("admin_id=".$admin_id)->find();
			$this->assign('admininfo',$admininfo);
			$this->display();
		}
	}
	//重置密码
	public function repassword(){
		$admin_id=I("get.admin_id");
		$info['password']=md5('123456');
		M('admininfo')->where('admin_id='.$admin_id)->save($info);
		jump('密码重置成功！',U('admininfo/index'),3);
	}

	//删除管理员
	public function deladmin(){
		$admin_id=I("get.admin_id");
		M('admininfo')->where('admin_id='.$admin_id)->delete();
		jump('管理员删除成功！',U('admininfo/index'),3);
	}

}