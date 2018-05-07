<?php 
namespace User\Controller;
use Think\Controller;
class HelpController extends Controller{
	function __construct() {
        //继承父类
        parent::__construct();
    }
    //空方法，防止报错
    public function _empty(){
        $this->redirect("user/userinfo"); 
    }
    /*
	帮助与反馈
    */
    public function index(){
        $phone_show=M("setinfo")->where("set_key='servicetel'")->field("set_value")->find()['set_value'];
        $phone_url=preg_replace("/[' ']||[-]/i",'',$phone_show);
        $this->assign("phone_show",$phone_show);
        $this->assign("phone_url",$phone_url);
    	$this->display();
    }
    /*
	平台运营规则
    */
    public function rules(){
    	$content=M("composition")->where("composition_id=2")->field("composition_text")->find();
    	if(empty($content)){
            header("Content-type:text/html;charset=utf-8");
    		echo "暂无内容";
    		exit;
    	}
    	$this->assign('rules',$content['composition_text']);
    	$this->display();
    }
    /*
	常见问题
    */
    public function problem(){
    	$content=M("composition")->where("composition_id=3")->field("composition_text")->find();
    	if(empty($content)){
            header("Content-type:text/html;charset=utf-8");
    		echo "暂无内容";
    		exit;
    	}
    	$this->assign('problem',$content['composition_text']);
    	$this->display();
    }
}
 ?>