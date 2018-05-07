<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends Controller {

	function __construct() {
		//继承父类
		parent::__construct();

       //判断登录状态
		if(!D('admininfo')->islogin()){//未登录
			jump('您尚未登录，请先登录！',U('login/login'),3);
		}	
    }

	//空方法，防止报错
	public function _empty(){
        $this->index();
    }
	
	//默认首页
    public function index(){
		$this->display();
    }

	//欢迎页
	public function welcome(){
		$admin_id=session('admin.admin_id');
		//用户数
        $usercount=M('User')->where('user_status=1')->count();
        $this->assign('usercount',$usercount);
        //经销商
        $agcycount=M('Agcy')->where('agcy_state=1')->count();
        $this->assign('agcycount',$agcycount);
        //专家
        $expertcount=M('Expert')->where('expert_state=1')->count();
        $this->assign('expertcount',$expertcount);
        //厂家
        $factorycount=M('Factory')->where('factory_state=1')->count();
        $this->assign('factorycount',$factorycount);
        //图书订单(已完成)
        $bookorder=M('Bookorder')->where('border_state=3')->count();
        $this->assign('bookorder',$bookorder);
        //积分订单(已完成)
        $jifenorder=M('Jifenorder')->where('jifen_order_state=3')->count();
        $this->assign('jifenorder',$jifenorder);
        //dd($usercount);
		$this->display();
    }

	//顶部菜单
	public function top(){
	
		$this->display();
    }

	//左边菜单
	public function left(){
		$group_id = session('admin.adminrole',"");//管理员role
		$groupinfo=F('group_menu_'.$group_id);//获取本管理员组的group_menu缓存数组
		if($groupinfo==""){
			jump('您尚无此操作权限！',U('index/welcome'),3);
		}
		$groupinfo=join(",",$groupinfo);
		
		//左侧一级菜单
			$menuinfo=M("adminmenu")->where("menu_fatherid=0 and menu_type=1 and menu_id in (".$groupinfo.")")->order('sort desc,menu_id asc')->fetchsql(false)->order('sort desc,menu_id asc')->select();

		foreach($menuinfo as $k=>$v){
			//左侧二级菜单
			$menuinfo[$k]['child']=M("adminmenu")->where("menu_fatherid=".$v['menu_id']." and menu_type=1  and menu_id in (".$groupinfo.")")->order('sort desc,menu_id asc')->select();
			
			foreach($menuinfo[$k]['child'] as $m=>$v){
				//左侧三级菜单
				$menuinfo[$k]['child'][$m]['childchild']=M("adminmenu")->where("menu_fatherid=".$v['menu_id']." and menu_type=1  and menu_id in (".$groupinfo.")")->order('sort desc,menu_id asc')->select();
				
			}
		}
		$this->assign('menuinfo',$menuinfo);		
		$this->display();
    }
}