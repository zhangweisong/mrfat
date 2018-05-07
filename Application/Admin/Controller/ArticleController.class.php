<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class ArticleController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录状态
        if (!D('admininfo')->islogin()) {//未登录
            jump('您尚未登录，请先登录！', U('login/login'));
        }
    }

    //空方法，防止报错
    public function _empty() {
        $this->index();
    }

	//专家文章评论
    public function articlelist(){
        $where=" 1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword'])); 
                $where .= " and (
                		ec_expert.expert_realname like '%" . $info['keyword'] . "%' 
                		or ec_expert_article.expert_article_title like '%" . $info['keyword'] . "%' 
                	)";
                $this->assign('keyword',urldecode($info['keyword']));
			}
			//文章分类
			if($expert_article_type){ 
				$info['expert_article_type']=urldecode(trim($info['expert_article_type']));
                $where.="and ec_expert_article.expert_article_type ='".$info['expert_article_type']."' ";  
                $this->assign('expert_article_type',urldecode($info['expert_article_type'])); 
			}
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_expert_article.expert_article_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_expert_article.expert_article_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
			
		}
		// 查询满足要求的总记录数
		$count      = M('Expert_article') 
					->join("left join ec_savantnewsclassify on ec_savantnewsclassify.snclassify_id=ec_expert_article.expert_article_type")
					->join("left join ec_expert on ec_expert.expert_id=ec_expert_article.expert_article_author")
					->where($where) 
					->count(); 
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$orderinfo = M('Expert_article')
			->where($where)
			->join("left join ec_savantnewsclassify on ec_savantnewsclassify.snclassify_id=ec_expert_article.expert_article_type")
			->join("left join ec_expert on ec_expert.expert_id=ec_expert_article.expert_article_author")//专家
			->order('ec_expert_article.expert_article_addtime desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->select();
		//查询文章分类
        //$fenlei=M("savantnewsclassify")->where('snclassify_isable=1')->select();		
		$this->assign('fenlei',$fenlei); 
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('state',D('Jifenclassify')->get_states());//文章状态
		$this->assign('article_types',D('expert')->article_types());//文章分类
		$this->assign('pageshow',$pageshow);
		$this->display();
    }
	
    //查看文章详情
    public function article_xq(){
    	$expert_article_id = I("get.expert_article_id");//文章id
		$orderinfo = M('Expert_article')
				->where("ec_expert_article.expert_article_id=".$expert_article_id)
				->join("left join ec_savantnewsclassify on ec_savantnewsclassify.snclassify_id=ec_expert_article.expert_article_type")
				->join("left join ec_expert on ec_expert.expert_id=ec_expert_article.expert_article_author")
				->find();
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('state',D('Jifenclassify')->get_states());
    	$this->display();
    } 
	
    //审核文章
    public function shenhe(){
		$expert_article_id = I("get.expert_article_id");
		if($expert_article_id){
			//改状态
			$article=M('Expert_article')->where('expert_article_id = '.$expert_article_id)->save(array('expert_article_state'=>2));
			//给专家加钱expert_article_author专家id
			$money=M('Setinfo')->where("set_key='expert_money' ")->find();//查询给专家加多钱
			M('Expert')->where('expert_id='.$article['expert_article_author'])->setInc('expert_money',$money['set_value']);
			if($article){
				jump('文章审核成功！',U('article/articlelist'));
			}else{
				jump('文章审核失败！',U('article/articlelist'));
			}
		}
    } 
	//文章评论
	public function commentlist(){
		$expert_article_id = I("get.expert_article_id");
		$this->assign('expert_article_id',$expert_article_id); 
        $where=" ec_expert_comment.comment_article='".$expert_article_id."' ";//某个文章的评论
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword'])); 
                $where .= " and (
                		ec_expert_comment.comment_content like '%" . $info['keyword'] . "%' 
                        or ec_user.realname like '%" . $info['keyword'] . "%'
                	)";
                $this->assign('keyword',urldecode($info['keyword']));
			}
			if($comment_state){ 
				$info['comment_state']=urldecode(trim($info['comment_state']));
                $where.="and ec_expert_comment.comment_state ='".$info['comment_state']."' ";  
                $this->assign('comment_state',urldecode($info['comment_state'])); 
			}
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_expert_comment.comment_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_expert_comment.comment_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
			
		}
		// 查询满足要求的总记录数
		$count      = M('expert_comment') 
					->where($where)
					->join('left join ec_user on ec_user.user_id=ec_expert_comment.comment_user_id') 
					->count(); 
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$orderinfo = M('expert_comment')
					->where($where)
					->join('left join ec_user on ec_user.user_id=ec_expert_comment.comment_user_id')
					->order('ec_expert_comment.comment_addtime desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('state',D('Jifenclassify')->get_states());
		$this->assign('pageshow',$pageshow);
		$this->display();
    }
	
	
	//审核评论
    public function shenhe_comment(){
		$comment_article = I("get.comment_article");//文章id
		$comment_id = I("get.comment_id");
		if($comment_id){
			$article=M('Expert_comment')->where('comment_id = '.$comment_id)->save(array('comment_state'=>2));
			if($article){
				jump('评论审核成功！',U('article/commentlist',array('expert_article_id'=>$comment_article)));
			}else{
				jump('评论审核失败！',U('article/commentlist',array('expert_article_id'=>$comment_article)));
			}
		}
    } 
	
	//查看评论详情
    public function comment_xq(){
    	$comment_id = I("comment_id");
    	//评论详情
		$orderinfo = M('Expert_comment')
				->where("ec_expert_comment.comment_id=".$comment_id)
				->join("left join ec_expert_article on ec_expert_article.expert_article_id=ec_expert_comment.comment_article")
				->join("left join ec_user on ec_user.user_id=ec_expert_comment.comment_user_id")
				->find();
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('state',D('Jifenclassify')->get_states());
    	$this->display();
    } 
	
	
	//用户提问管理
    public function questionslist(){
        $where=" 1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword'])); 
                $where .= " and (
                		ec_user.realname like '%" . $info['keyword'] . "%' 
                		or ec_expert.expert_realname like '%" . $info['keyword'] . "%' 
                	)";
                $this->assign('keyword',urldecode($info['keyword']));
			}
            //问答分类
			if($questions_type_id){ 
				$info['questions_type_id']=urldecode(trim($info['questions_type_id']));
                $where.="and ec_expert_questions.questions_type_id ='".$info['questions_type_id']."' ";  
                $this->assign('questions_type_id',urldecode($info['questions_type_id'])); 
			}
			//是否针对专家
			if($questions_aim){ 
				$info['questions_aim']=urldecode(trim($info['questions_aim']));
                $where.="and ec_expert_questions.questions_aim ='".$info['questions_aim']."' ";  
                $this->assign('questions_aim',urldecode($info['questions_aim'])); 
			}
            
			//审核状态
			if($questions_state){ 
				$info['questions_state']=urldecode(trim($info['questions_state']));
                $where.="and ec_expert_questions.questions_state ='".$info['questions_state']."' ";  
                $this->assign('questions_state',urldecode($info['questions_state'])); 
			}
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_expert_questions.questions_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_expert_questions.questions_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
			
		}
		// 查询满足要求的总记录数
		$count      = M('Expert_questions') 
					->join("left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id")
					->join("left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id")
					->join("left join ec_expert on ec_expert.expert_id=ec_expert_questions.questions_expert_id")
					->where($where) 
					->count(); 
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$orderinfo = M('Expert_questions')
					->where($where)
					->join("left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id")
					->join("left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id")
					->join("left join ec_expert on ec_expert.expert_id=ec_expert_questions.questions_expert_id")
					->order('ec_expert_questions.questions_addtime desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
        foreach($orderinfo as $k=>$v){
            $orderinfo[$k]['realname'] = $v['realname']?$v['realname']:$v['nickname'];
        }            
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('states',D('Jifenclassify')->get_questions_aim());//是否针对专家
		$this->assign('state',D('Jifenclassify')->get_states());//审核状态
		$this->assign('expert_types',D('expert')->expert_types());//问答分类
		$this->assign('pageshow',$pageshow);
		$this->display();
    }
	
	//审核问题
    public function shenhe_questions(){
		$questions_id = I("get.questions_id");
		if($questions_id){
			$article=M('Expert_questions')->where('questions_id = '.$questions_id)->save(array('questions_state'=>2));
			if($article){
				jump('问题审核成功！',U('article/questionslist'));
			}else{
				jump('问题审核失败！',U('article/questionslist'));
			}
		}
    }
	//查看提问详情
    public function questions_xq(){
    	$questions_id = I("questions_id");
    	//评论详情
		$orderinfo = M('Expert_questions')
			->where("ec_expert_questions.questions_id=".$questions_id)
			->join("left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id")
			->join("left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id")
			->join("left join ec_expert on ec_expert.expert_id=ec_expert_questions.questions_expert_id")
			->find();
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('state',D('Jifenclassify')->get_states());
		$this->assign('states',D('Jifenclassify')->get_questions_aim());
    	$this->display();
    } 
	
	//问题回答
	public function answerslist(){
		$questions_id = I("get.questions_id");
		$this->assign('questions_id',$questions_id); 
        $where=" ec_expert_answers.answers_question_id='".$questions_id."' ";//某个问题的回答
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword'])); 
                $where .= " and (
                		ec_expert_answers.answers_content like '%" . $info['keyword'] . "%'
                        or ec_expert.expert_realname like '%" . $info['keyword'] . "%' 
                	)";
                $this->assign('keyword',urldecode($info['keyword']));
			}
			if($answers_state){ 
				$info['answers_state']=urldecode(trim($info['answers_state']));
                $where.="and ec_expert_answers.answers_state ='".$info['answers_state']."' ";  
                $this->assign('answers_state',urldecode($info['answers_state'])); 
			}
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_expert_answers.answers_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_expert_answers.answers_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
			
		}
		// 查询满足要求的总记录数
		$count      = M('Expert_answers') 
					->where($where)
					->join('left join ec_expert on ec_expert.expert_id=ec_expert_answers.answers_expert_id') 
					->join('left join ec_expert_questions on ec_expert_questions.questions_id=ec_expert_answers.answers_question_id') 
					->count(); 
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$orderinfo = M('Expert_answers')
					->where($where)
					->join('left join ec_expert on ec_expert.expert_id=ec_expert_answers.answers_expert_id') 
					->join('left join ec_expert_questions on ec_expert_questions.questions_id=ec_expert_answers.answers_question_id') 
					->order('ec_expert_answers.answers_addtime desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('state',D('Jifenclassify')->get_states());
		$this->assign('pageshow',$pageshow);
		$this->display();
    }
	
	
	//审核回答发放佣金
    public function shenhe_answers(){
		$answers_id = I("get.answers_id");
		$answers_question_id = I("get.answers_question_id");//问题id
		if($answers_id){
			$article=M('Expert_answers')->where('answers_id = '.$answers_id)->save(array('answers_state'=>2));
				//给专家加钱expert_article_author专家id
			$money=M('Setinfo')->where("set_key='expert_money' ")->find();//查询给专家加多钱
			M('Expert')->where('expert_id='.$article['expert_article_author'])->setInc('expert_money',$money['set_value']);
			if($article){
				jump('发放佣金成功！',U('article/answerslist',array('questions_id'=>$answers_question_id)));
			}else{
				jump('发放佣金失败！',U('article/answerslist',array('questions_id'=>$answers_question_id)));
			}
		}
    } 
	
	//查看评论详情
    public function answers_xq(){
    	$answers_id = I("answers_id");
    	//评论详情
		$orderinfo = M('Expert_answers')
					->where("ec_expert_answers.answers_id=".$answers_id)
					->join('left join ec_expert_questions on ec_expert_questions.questions_id=ec_expert_answers.answers_question_id') 
					->join('left join ec_expert on ec_expert.expert_id=ec_expert_answers.answers_expert_id') 
					->find();
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('state',D('Jifenclassify')->get_states());
    	$this->display();
    } 
	//删除回答
	public function delete_answers(){
		$answers_id = I("answers_id");
		if($answers_id){
			$answers=M('Expert_answers')->where('answers_id='.$answers_id)->delete();
			if($answers){
				jump('回答删除成功！',U('article/answerslist'));
			}else{
				jump('回答删除失败！',U('article/answerslist'));
			}
		}
	}
	
	
}
