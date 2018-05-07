<?php
namespace Expert\Controller;
use Think\Controller;
class ProblembaseController extends Controller {

    function __construct() {
		//继承父类
		parent::__construct();
	    //判断登录
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
    
    //答题库
    public function index(){
        $expert_id=session('expert_id');
        $count ==M('expert_problembase')
                ->where('problembase_expert_id='.$expert_id)
				->count();
		$Page = new \Think\Page($count, 5);
        
        $expert_problembase=M('expert_problembase')
                            ->where('problembase_expert_id='.$expert_id)
                            ->limit($Page->firstRow . ',' . $Page->listRows)
                            ->order('problembase_id desc')
                            ->select();
        if(IS_AJAX){
           if($expert_problembase){
                if($Page->totalPages >= $p){
                $this->ajaxReturn(array('status'=>1,'data'=>$expert_problembase,'msg'=>"",'data_'=>count($expert_problembase)));
                }else{
                    $this->ajaxReturn(array('status' => 0, 'data' => array(), 'msg' => "没有数据了"));
                } 
           }else{
               $this->ajaxReturn(array('status' => 0, 'data' => array(), 'msg' => "没有数据了"));
           } 
        }else{
            $this->assign('expert_problembase',$expert_problembase);                    
            $this->assign('length',count($expert_problembase));                    
            $this->display(); 
            
        }
        
    }
    //添加答题库
    public function add(){
        $expert_id=session('expert_id');
       if(IS_POST){
            $info=I('info');
            $info['problembase_question']=filterEmoji($info['problembase_question'],2);
            $info['problembase_answers']=filterEmoji($info['problembase_answers'],2);
            $info['problembase_expert_id']=$expert_id;
            $info['problembase_addtime']=time();
            $flag=M('expert_problembase')->add($info);
            if($flag){
                jump_success('答题库添加成功',U('problembase/index'));
            }else{
                jump_success('答题库添加失败,继续添加',U('problembase/add'));
            }
       }else{
           $this->assign('experttypes',D('Expert')->getExpertType());
           $this->display();
       }
    }
    //专家回复
    public function huifu(){
        $expert_id=session('expert_id');
        $questions_id=I('get.questions_id'); 
        if(IS_AJAX){
            $questions_id=I('post.questions_id'); 
            $answers_content=I('post.answers_content');
            $answers_image=I('post.answers_image');
            $info['answers_question_id']=$questions_id;
            $info['answers_expert_id']=$expert_id;
            $info['answers_content']=$answers_content;
            $info['answers_addtime']=time();
            $info['answers_image']=$answers_image;
            $flag=M('expert_answers')->fetchSql(false)->add($info);
            //echo $flag;die;
            //加回答数
            $result=M('expert_questions')->where('questions_id='.$questions_id)->setInc('questions_answers',1);
            //加活跃值
            $value=M("setinfo")->where("set_key= 'expert_money'")->find();
            $flags=M('expert')->where('expert_id='.$expert_id)->setInc('expert_activevalue',$value['set_value']);
            if($flag && $result && $flags){
                //jump_success('专家回复成功',U('answer/xiangq',array('questions_id'=>$questions_id)));
                $this->ajaxReturn(array("data"=>1));
            }else{
                //jump_success('专家回复失败',U('problembase/huifu',array('questions_id'=>$questions_id)));
                $this->ajaxReturn(array("data"=>0));
            }    
        }else{
            $this->assign('questions_id',$questions_id);
            $this->display();
        }
       
      
    
      
    }
  
  

}