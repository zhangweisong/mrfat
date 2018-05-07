<?php
namespace Expert\Controller;  
use Think\Controller;

class AnswerController extends Controller {
	function __construct() {
		//继承父类
		parent::__construct();
		if(ACTION_NAME=="yingxiaoguanli"){
			
		}else{
			//判断登录状态
			$expert_id=session("expert_id");
			//未登录
			if($expert_id==""){
				header("Location:".U('login/login'));
				exit();
			}
		}
    }

	//空方法，防止报错
	public function _empty(){
        $this->index();
    }
	
	//问题管理
	public function wentiguanli(){
		$expert_id=session("expert_id");
		$questions_type_id =I("get.questions_type_id");//分类id
		$where="(ec_expert_questions.questions_aim=2 or (ec_expert_questions.questions_aim=1 and questions_expert_id='".$expert_id."'))";
		if($questions_type_id==''||$questions_type_id==0){
			$where.=" ";
		}else{
			$where.=" and ec_expert_questions.questions_type_id='".$questions_type_id."'  ";
		}
		$count=M('expert_questions')
				->join('left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id')//农户表
				->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id')//问答分类表
				->join('left join ec_expert on ec_expert.expert_id=ec_expert_questions.questions_expert_id')//专家表
				->where($where)
				->count();
		$Page = new \Think\Page($count,4);
		//农户提问的所有不针对专家的问题或者针对自己的问题
		$question=M('expert_questions')
				->join('left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id')//农户表
				->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id')//问答分类表
				->join('left join ec_expert on ec_expert.expert_id=ec_expert_questions.questions_expert_id')//专家表
				->where($where)
				->order('ec_expert_questions.questions_addtime desc ')
				->limit($Page->firstRow.','.$Page->listRows)
				->select();
		foreach ($question as $k => $v) {
			$question[$k]["questions_addtime"] = from_time($v['questions_addtime']);//时间换算
			$question[$k]["questions_image"] = explode('|',$v['questions_image']);
            $question[$k]["questions_content"] = str_cut($v['questions_content'],130);
            $question[$k]["realname"] = $v['realname']?$v['realname']:$v['nickname'];
		}	
		if(IS_AJAX){
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('question' => $question), 'json');
            } else {
                $this->ajaxReturn(array('question' => array()), 'json');
            }
        }else {
			$this->assign('question', json_encode(array('question' => $question)));
			$this->assign('type',D('Expert')->getExpertType());//问题分类
			$this->assign('questions_type_id',$questions_type_id);//搜索问题分类id
            $this->display();
        } 
	}
	
	//问题详情
    function xiangq(){
		$questions_id=I('get.questions_id');
		//问题详情
		$questions=M('expert_questions')
				->join('left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id')//农户表
				->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id')//问答分类表
				->join('left join ec_expert on ec_expert.expert_id=ec_expert_questions.questions_expert_id')//专家表
				->where("ec_expert_questions.questions_id='".$questions_id."' ")
				->find();
		$questions["questions_addtime"] = from_time($questions['questions_addtime']);//时间换算
        $questions["questions_image"] = explode('|',$questions['questions_image']);
        $questions["realname"] = $questions['realname']?$questions['realname']:$questions['nickname'];
		//此问题的回答
		$count = M('expert_answers')
				->join('left join ec_expert_questions on ec_expert_questions.questions_id=ec_expert_answers.answers_question_id')//问题表
				->join('left join ec_expert on ec_expert.expert_id=ec_expert_answers.answers_expert_id')//专家表
				->where("ec_expert_questions.questions_id='".$questions_id."' ")
				->count();
		$Page = new \Think\Page($count,4);
		$answers=M('expert_answers')
				->join('left join ec_expert_questions on ec_expert_questions.questions_id=ec_expert_answers.answers_question_id')//问题表
				->join('left join ec_expert on ec_expert.expert_id=ec_expert_answers.answers_expert_id')//专家表
				->join('left join ec_savantclassify on ec_savantclassify.sclassify_id=ec_expert.expert_pessional')//专家职称表
				->where("ec_expert_questions.questions_id='".$questions_id."' ")
				->order('ec_expert_answers.answers_addtime desc ')
				->limit($Page->firstRow.','.$Page->listRows)
				->select();
		foreach ($answers as $k => $v) {
			$answers[$k]["answers_addtime"] = from_time($v['answers_addtime']);//时间换算
			$answers[$k]["answers_image"] = explode('|',$v['answers_image']);
		}
		if(IS_AJAX){
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('answers' => $answers), 'json');
            } else {
                $this->ajaxReturn(array('answers' => array()), 'json');
            }
        }else {
			$this->assign('answers', json_encode(array('answers' => $answers)));
			$this->assign('questions',$questions);//搜索问题分类id
			$this->assign('questions_id',$questions_id);
            $this->display();
        } 
	}
	
	//给问题点赞
    public function dianzan_q() {
		$questions_id = I('post.questions_id');
	    $sql = M("expert_questions")->where("questions_id='" . $questions_id . "'")->setInc('questions_like', 1);
	    $num = M("expert_questions")->where("questions_id='" . $questions_id . "'")->find();
	    $questions_like = $num['questions_like'];
	    if ($sql) {
			$response = array(
				'errno' => 1,
				'questions_like' => $questions_like,
			);
	    } else {
			$response = array(
				'errno' => -1,
			);
	    }
		echo json_encode($response);
    }
    
	
	//给回答点赞
    public function dianzan_a() {
		$answers_id = I('post.answers_id');
	    $sql = M("expert_answers")->where("answers_id='" . $answers_id . "'")->setInc('answers_like', 1);
	    $num = M("expert_answers")->where("answers_id='" . $answers_id . "'")->find();
	    $answers_like = $num['answers_like'];
	    if ($sql) {
			$response = array(
				'errno' => 1,
				'answers_like' => $answers_like,
			);
	    } else {
			$response = array(
				'errno' => -1,
			);
	    }
		echo json_encode($response);
    }
    
  
    //历史回答   ---此专家回复的问题
    public function lishihuida(){
		$expert_id=session("expert_id");
		$where=" answers_expert_id='".$expert_id."' ";
        //问题
		$count=M('expert_answers')
			->join('left join ec_expert_questions on ec_expert_questions.questions_id=ec_expert_answers.answers_question_id')
            ->join('left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id')//用户表
            ->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id')//问答分类表
			->where($where)
			->count();
		$Page = new \Think\Page($count,2);
        //这个专家回复的所有问题
		$question=M('expert_answers')
                ->join('left join ec_expert_questions  on ec_expert_questions.questions_id=ec_expert_answers.answers_question_id')
                ->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id')//问答分类表
                ->join('left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id')//用户表
                ->where($where)
				->order('ec_expert_answers.answers_addtime desc ')
                ->group('ec_expert_questions.questions_id ')
				->limit($Page->firstRow.','.$Page->listRows)
				->select();
		foreach ($question as $k => $v) {
            $question[$k]["questions_addtime"] = from_time($v['questions_addtime']);//时间换算
            $question[$k]["questions_image"] = explode('|',$v['questions_image']);
            //查询问题下的回复
            $question[$k]['answers']=M('expert_answers')
                    ->join('left join ec_expert_questions  on ec_expert_questions.questions_id=ec_expert_answers.answers_question_id')
                    ->join('left join ec_expert on ec_expert.expert_id=ec_expert_answers.answers_expert_id')//专家表
                    ->join('left join ec_savantclassify on ec_savantclassify.sclassify_id=ec_expert.expert_pessional')//专家职称
                    ->where('ec_expert_answers.answers_question_id='.$v['answers_question_id'] )
                    ->select(); 
            foreach($question[$k]['answers'] as $kk=>$vv){   
            $question[$k]['answers'][$kk]["answers_addtime"] = from_time($vv['answers_addtime']);//时间换算
            $question[$k]['answers'][$kk]["answers_image"] = explode('|',$vv['answers_image']);
            }            
		}        
		if(IS_AJAX){
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('question' => $question), 'json');
            } else {
                $this->ajaxReturn(array('question' => array()), 'json');
            }
        }else {
			$this->assign('question', json_encode(array('question' => $question)));
            $this->display();
        } 
	}
    
    
    
    
    
	
}
