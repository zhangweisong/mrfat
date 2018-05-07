<?php
namespace User\Controller;
use Think\Controller;
class ZhenController extends Controller {
	function __construct() {
		//继承父类
		parent::__construct();
	    //判断登录状态
        //session("user_id",1);
		/*$user_id=session("user_id");
		if($user_id==""){//未登录
			header("Location:".U('index/index'));
			exit();
		}*/

    }
	//空方法，防止报错
	public function _empty(){
        $this->index();
    }
	
	//真假查询
    public function index(){
		 
		$this->display();
    }    
	//记者举报
    public function jizhejvbao(){
		$this->display();
    } 
	//添加意见
	public  function yijian(){
		$user_id= session('user_id');
		$title= I('title');
		$data['title']=filterEmoji($title, 2);
		$data['user_id']= $user_id;
		$data['feedback_type']= 2;
		$data['addtime']= time();
		$data['state']= 1;
		$rel = M('feedback')->add($data);
		if($rel){
			$this->ajaxReturn(array('stat'=>1));
		}
	}
}