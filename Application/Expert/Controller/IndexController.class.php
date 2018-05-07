<?php
namespace Expert\Controller;
use Think\Controller;
class IndexController extends Controller {
	function __construct() {
		//继承父类
		parent::__construct(); 
		$expert_id=session("expert_id"); 
		if($expert_id==""){//未登录
			header("Location:".U('login/login'));
			exit();
		}
    }
	//空方法，防止报错
	public function _empty(){
        $this->index();
    } 
	
	public function index(){
        $expert_id=session("expert_id"); 
		$expert = M("expert")
				->where('expert_id=' .$expert_id)
				->join('left join ec_savantclassify on ec_savantclassify.sclassify_id=ec_expert.expert_pessional')
				->find();
        //回答个数
        $expert_answers = M("expert_answers")->where('answers_expert_id=' .$expert_id)->count();
        //文章个数
        $expert_article = M("expert_article")->where('expert_article_author=' .$expert_id)->count();
		//未读消息
		$message = M('message')->where('msg_type=3 and msg_flag=1 and expert_id='.$expert_id)->count();
		//dd($message);
		$this->assign("message",$message); 
        $this->assign("expert",$expert);
        $this->assign("expert_answers",$expert_answers); 
        $this->assign("expert_article",$expert_article);  
		$this->display();
	}
	
	//专家信息
	public function expertinfo(){
		$expert_id=session("expert_id"); 
		$expert = M("expert")
            ->join('left join ec_savantclassify on ec_savantclassify.sclassify_id=ec_expert.expert_pessional')
            ->where('ec_expert.expert_id=' .$expert_id)
            ->field('ec_expert.*,ec_savantclassify.sclassify_name')
            ->find();
            
		$this->assign("expert",$expert);
		$this->display();
	}
	
	//编辑信息
	public function wanshan(){
		$expert_id=session("expert_id"); 
		$data['expert_nickname'] = filterEmoji(I("expert_nickname"),2);
		$data['expert_phone'] = I("expert_phone");
		$data['expert_pessional'] = I("expert_pessional");
		$data['expert_brief'] = filterEmoji(I("expert_brief"),2);
		$data['expert_avator'] = I("expert_avator"); 
		$data['expert_image'] = I("expert_image"); 
		$res = M("expert")->where('expert_id='.$expert_id)->save($data);
		if($res){
			$this->ajaxReturn(array("data"=>1));
		}else{
			$this->ajaxReturn(array("data"=>0));
		}
		
		
	}
	
	
	public function wanshanxinxi(){
		$expert_id=session("expert_id"); 
		$expert = M("expert")
            ->join('left join ec_savantclassify on ec_savantclassify.sclassify_id=ec_expert.expert_pessional')
            ->where('ec_expert.expert_id=' .$expert_id)
            ->field('ec_expert.*,ec_savantclassify.sclassify_name')
            ->find();
        //职称
        $zhicheng = M("savantclassify")->where("sclassify_isable = 1 ")->select();    
		$this->assign("expert",$expert); 
        $this->assign("zhicheng",$zhicheng); 
		$this->display();
	}  
	//退出登录
	public function outlogin(){
		session("expert_id",null);
		header("Content-Type:text/html;charset=utf-8");
		jump_success('退出成功！',U('login/login'));
		exit();
	}
	 
}