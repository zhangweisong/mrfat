<?php
namespace Factory\Controller;
use Think\Controller;
class IndexController extends Controller {

	function __construct() {
		//继承父类
		parent::__construct();

       //判断登录状态
		if(!D('Factory')->islogin()){//未登录
			jump('您尚未登录，请先登录！', U('login/login'));
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
		$factory_id = session('factory.factory_id');//管理员id
        $factoryinfo = M('factory')->find($factory_id); 
		$this->assign('factoryinfo',$factoryinfo);
		//订单(已完成)
        $bookorder=M('forder')->where("forder_flag=3 and factory_id='".$factory_id."' ")->count();
		//代理商(已通过)
		$agcy=M('agcyapply')->where("agcy_apply_flag=2 and factory_id='".$factory_id."' ")->count();
		//未读消息数
		$message=M('message')->where("msg_type=3 and factory_id='".$factory_id."' ")->count();
		//商品
		$fgoods=M('fgoods')->where("fgoods_state=1 and factory_id='".$factory_id."' ")->count();
        $this->assign('bookorder',$bookorder);
		$this->assign('agcy',$agcy);
		$this->assign('message',$message);
		$this->assign('fgoods',$fgoods);
		$this->display();
    }

	//顶部菜单
	public function top(){
		$this->display();
    }

	//左边菜单
	public function left(){				
		$this->display();
    }
}