<?php
namespace Admin\Controller;
use Think\Controller;
class AdminmenuController extends Controller {
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
	
	//菜单列表
    public function index(){
		$menu_id=I("get.menu_id",0);
		$where="1 and menu_fatherid=".$menu_id." ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword']));
				$where.="and  menu_name like '%".$info['keyword']."%' ";  
				$this->assign('keyword',$info['keyword']);
			}
		}

		$menuinfo=M('adminmenu');
		// 查询满足要求的总记录数
		$count      = $menuinfo->where($where)->count();
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$listinfo = $menuinfo
					->where($where)
					->order('sort desc,menu_id asc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$this->assign('listinfo',$listinfo);
		$this->assign('pageshow',$pageshow);
		if($menu_id){//非顶级菜单
			$father_menuinfo=M("adminmenu")->where("menu_id=".$menu_id)->find();
			$this->assign('father_menuinfo',$father_menuinfo);
		}else{//顶部菜单
			$this->assign('father_menuinfo',array("menu_id"=>0,"menu_fatherid"=>0,"menu_name"=>'顶部菜单'));
		}
  
		$this->display();
    }
	//添加菜单
    public function addmenuinfo(){		
		if (IS_POST){
			$info=I("info");
			$info_['menu_name']=trim($info['menu_name']);
			$info_['menu_fatherid']=intval($info['menu_fatherid']);
			$info_['menu_type']=intval($info['menu_type']);
			$info_['controller']=strtolower(trim($info['menu_controller']));
			$info_['action']=strtolower(trim($info['menu_action']));
			$info_['menu_icon']=trim($info['menu_icon']);
			$info_['sort']=intval($info['sort']);		
			$flag=M("adminmenu")->add($info_);
			if($flag){				
				jump('添加菜单成功！',U('adminmenu/index',array("menu_id"=>$info['menu_fatherid'])));
			}else{
				jump('添加菜单失败！',U('adminmenu/index',array("menu_id"=>$info['menu_fatherid'])));
			}
		}else{
			$menu_fatherid=I("get.menu_id");
			if($menu_fatherid){//非顶级菜单
				$father_menuinfo=M("adminmenu")->where("menu_id=".$menu_fatherid)->find();
				$this->assign('father_menuinfo',$father_menuinfo);
			}else{//顶部菜单
				$this->assign('father_menuinfo',array("menu_id"=>0,"menu_fatherid"=>0,"menu_name"=>'顶部菜单'));
			}
			
			$this->display();
		}
    }

	//编辑菜单
    public function editmenuinfo(){
		$menu_id=I("get.menu_id");
		if (IS_POST){
			$info=I("info");
			$info['menu_name']=trim($info['menu_name']);
			$info['menu_fatherid']=intval($info['menu_fatherid']);
			$info['menu_type']=intval($info['menu_type']);
			$info['controller']=strtolower(trim($info['menu_controller']));
			$info['action']=strtolower(trim($info['menu_action']));
			$info['menu_icon']=trim($info['menu_icon']);
			$info['sort']=intval($info['sort']);
			$flag=M("adminmenu")->where("menu_id=".$menu_id)->save($info);
			jump('编辑菜单成功！',U('adminmenu/index',array("menu_id"=>$info['menu_fatherid'])));
			
		}else{
			$menuinfo=M("adminmenu")->where("menu_id=".$menu_id)->find();
			$this->assign('adminmenu',$menuinfo);

			if($menuinfo['menu_fatherid']){//非顶级菜单
				$father_menuinfo=M("adminmenu")->where("menu_id=".$menuinfo['menu_fatherid'])->find();
				$this->assign('father_menuinfo',$father_menuinfo);
			}else{//顶部菜单
				$this->assign('father_menuinfo',array("menu_id"=>0,"menu_fatherid"=>0,"menu_name"=>'顶部菜单'));
			}
			$this->display();
		}
    }

	//删除
	public function delmenuinfo(){
		$menu_id=I("get.menu_id");
		M('adminmenu')->where('menu_id='.$menu_id)->delete();
		jump('菜单删除成功！',U('adminmenu/index'));
	}

}