<?php
namespace User\Controller;  
use Think\Controller;

class ExpertController extends Controller {
	function __construct() {
		//继承父类
		parent::__construct();
        $user_id=session("user_id");
		if($user_id==""){//未登录
            header("Location:".U('index/index'));
            exit();
		}
    }

	//空方法，防止报错
	public function _empty(){
        $this->index();
    }
	
	
	//专家问答
	public function zhuanjiawenda(){
		$user_id=session("user_id");
		$questions_type_id =I("get.questions_type_id");//分类id
		$where="1 ";
		if($questions_type_id==''||$questions_type_id==0){
			$where.=" ";
		}else{
			$where.=" and ec_expert_questions.questions_type_id='".$questions_type_id."'  ";
		}
		//查询所有农户提问问题
		$count=M('expert_questions')
				->join('left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id')//农户表
				->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id')//问答分类表
				->where($where)
				->count();
		$Page=new \Think\Page($count,4);
		$question=M('expert_questions')
				->join('left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id')//农户表
				->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id')//问答分类表
				->where($where)
				->order('ec_expert_questions.questions_addtime desc ')
				->limit($Page->firstRow.','.$Page->listRows)
				->select();
				
		foreach ($question as $k => $v) {
            //截取内容
            $question[$k]["questions_content"] = str_cut($v['questions_content'],130);
			$question[$k]["questions_addtime"] = from_time($v['questions_addtime']);//时间换算
			$question[$k]["questions_image"] = explode('|',$v['questions_image']);//图片
            $question[$k]["realname"] = $v['realname']?$v['realname']:$v['nickname'];
		}
        if(IS_AJAX){
			if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('question' => $question), 'json');
            } else {
                $this->ajaxReturn(array('question' => array()), 'json');
            }
		}else{
			$this->assign('question', json_encode(array('question' => $question)));
			$this->assign('type',D('Expert')->getExpertType());//问题分类
			$this->assign('questions_type_id',$questions_type_id);//搜索问题分类id
			$this->display();
		}		
	}
	
	//问题详情
    function zhuanjiawenda_xq(){
		$questions_id=I('get.questions_id');
		//问题详情
		$questions=M('expert_questions')
				->join('left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id')//农户表
				->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id')//问答分类表
				->join('left join ec_expert on ec_expert.expert_id=ec_expert_questions.questions_expert_id')//专家表
				->where("questions_id='".$questions_id."' ")
				->find();
		$questions["questions_addtime"] = from_time($questions['questions_addtime']);//时间换算
		$questions["questions_image"] = explode('|',$questions['questions_image']);//图片
        $questions["realname"]=$questions["realname"]?$questions["realname"]:$questions["nickname"];
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
			$answers[$k]["answers_image"] = explode('|',$answers['answers_image']);//图片
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
	
	
	//专家文章
	public function zhuanjiawenzhang(){
		$snclassify_id=I('snclassify_id');//文章分类
		$keyword=I('keyword')?I('keyword'):'';//搜索条件
		$where="ec_savantnewsclassify.snclassify_isable=1 and ec_expert.expert_state=1  ";
		//搜索不为空时
		if($keyword!='' && $snclassify_id!=''){
			if($snclassify_id!=0){
				$where.=" and (ec_expert_article.expert_article_title like '%".$keyword."%' or ec_expert.expert_realname like '%".$keyword."%') and ec_savantnewsclassify.snclassify_id='".$snclassify_id."' ";
			}else{
				$where.=" and (ec_expert_article.expert_article_title like '%".$keyword."%' or ec_expert.expert_realname like '%".$keyword."%')  ";
			}
		}else if($keyword=='' && $snclassify_id!=''){
			if($snclassify_id==0){
				$where.=" ";
			}else{
				$where.=" and ec_savantnewsclassify.snclassify_id='".$snclassify_id."' ";
			}
		}else if($keyword=='' && $snclassify_id==''){
			$where.=" ";
		}
		$count=M('expert_article')
				->where($where)
				->join('left join ec_savantnewsclassify on ec_savantnewsclassify.snclassify_id=ec_expert_article.expert_article_type')
				->join('left join ec_expert on ec_expert.expert_id=ec_expert_article.expert_article_author')
				->count();
		$Page= new \ Think \ Page ($count,6);
		$wenzhang=M('expert_article')
				->where($where)
				->join('left join ec_savantnewsclassify on ec_savantnewsclassify.snclassify_id=ec_expert_article.expert_article_type')//文章分类
				->join('left join ec_expert on ec_expert.expert_id=ec_expert_article.expert_article_author')//专家表
				->order('ec_expert_article.expert_article_addtime desc')
				->limit($Page->firstRow.','.$Page->listRows)
				->select();
			//	dd($wenzhang);
		foreach ($wenzhang as $k => $v) {
			$wenzhang[$k]["expert_article_addtime"] =date("Y-m-d H:i:s",$v['expert_article_addtime']);//时间换算
		}	
		//dd($wenzhang);		
		if(IS_AJAX){
			if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('wenzhang'=>$wenzhang),'json');
            } else {
                $this->ajaxReturn(array('wenzhang' => array()), 'json');
            }
			
		}else{
			$this->assign('type',D('expert')->gettype());
			$this->assign('snclassify_id',$snclassify_id);
			$this->assign('keyword',$keyword);
			$this->assign('wenzhang',json_encode(array('wenzhang' => $wenzhang)));
			$this->display();
		}
	}
	
	//专家文章详情
	public function zhuanjiawenzhang_xq(){
		$expert_article_id=I("expert_article_id");//文章id 
		//查询文章内容 
		$expert_article=M('expert_article')
						->where("expert_article_id='".$expert_article_id."' ")
						->join('left join ec_savantnewsclassify on ec_savantnewsclassify.snclassify_id=ec_expert_article.expert_article_type')
						->join('left join ec_expert on ec_expert.expert_id=ec_expert_article.expert_article_author')
						->find();
		$expert_article['expert_article_addtime']=date('Y-m-d H:i:s',$expert_article['expert_article_addtime']);
        $expert_article["expert_article_content"] = str_replace("font-size","font_size",$expert_article["expert_article_content"]);
		$count=M('expert_comment')
				->where("ec_expert_comment.comment_article='".$expert_article_id."'")
				->join('left join ec_user on ec_user.user_id=ec_expert_comment.comment_user_id')
				->count();
		$Page=new \Think\Page($count,4);		
		//查询这个文章下的评论
		$comment=M('expert_comment')
				->where("ec_expert_comment.comment_article='".$expert_article_id."'")
				->join('left join ec_user on ec_user.user_id=ec_expert_comment.comment_user_id')
				->order('ec_expert_comment.comment_addtime desc')
				->limit($Page->firstRow.','.$Page->listRows)
				->select();	
		foreach ($comment as $k => $v) {
			$comment[$k]["comment_addtime"] = from_time($v['comment_addtime']);//时间换算
            $comment[$k]["comment_content"] = str_replace("font-size","font_size",$v['comment_content']);
		}		
		if(IS_AJAX){
			if($Page->totalPages >= $p){
				$this->ajaxReturn(array('comment'=>$comment),'json');
			}else{
				$this->ajaxReturn(array('comment'=>array()),'json');
			}
		}else{
			$this->assign('expert_article_id',$expert_article_id);//文章id
			$this->assign('expert_article',$expert_article);
			$this->assign('comment',json_encode(array('comment' => $comment)));
			$this->display();
		}	

	}
	
	//给文章点赞
    public function dianzan_article() {
		$expert_article_id = I('post.expert_article_id');
	    $sql = M("expert_article")->where("expert_article_id='" . $expert_article_id . "'")->setInc('expert_article_like', 1);
	    $num = M("expert_article")->where("expert_article_id='" . $expert_article_id . "'")->find();
	    $expert_article_like = $num['expert_article_like'];
	    if ($sql) {
			$response = array(
				'errno' => 1,
				'expert_article_like' => $expert_article_like,
			);
	    } else {
			$response = array(
				'errno' => -1,
			);
	    }
		echo json_encode($response);
    }
	
	//给文章评论点赞
    public function dianzan_comment() {
		$comment_id = I('post.comment_id');
	    $sql = M("expert_comment")->where("comment_id='" . $comment_id . "'")->setInc('comment_like', 1);
	    $num = M("expert_comment")->where("comment_id='" . $comment_id . "'")->find();
	    $comment_like = $num['comment_like'];
	    if ($sql) {
			$response = array(
				'errno' => 1,
				'comment_like' => $comment_like,
			);
	    } else {
			$response = array(
				'errno' => -1,
			);
	    }
		echo json_encode($response);
    }
	
	//发表评论
	function fabiaopinglun(){
		$user_id=session('user_id');
		$comment_content =filterEmoji(I('post.comment_content'),2);//评论内容
		$comment_article = I('post.comment_article');//文章编号
		$flag=M('expert_comment')->add(array('comment_article'=>$comment_article,'comment_content'=>$comment_content,'comment_user_id'=>$user_id,'comment_addtime'=>time()));
		if(IS_AJAX){
            //给文章评论加1
            $flag=M('expert_article')->where('expert_article_id='.$comment_article)->setInc('expert_article_comment',1);
            if ($flag) {
                $this->ajaxReturn(array('data'=>1),'json');
            } else {
                $this->ajaxReturn(array('data' =>0), 'json');
            }
			
		}
         $this->display();
			//jump_success('评论提交失败！', U('expert/zhuanjiawenzhang_xq',array('expert_article_id'=>$comment_article)));
		
	}
	
	//专家介绍
	public function zhuanjiajieshao(){
		//查询所有专家
		$count=M('expert')
				->where('ec_savantclassify.sclassify_isable=1 and ec_expert.expert_state=1 ')
				->join('left join ec_savantclassify on ec_savantclassify.sclassify_id=ec_expert.expert_pessional')
				->count();
		$Page= new \ Think \ Page ($count,6);
		$expert=M('expert')
				->where('ec_savantclassify.sclassify_isable=1 and ec_expert.expert_state=1 ')
				->join('left join ec_savantclassify on ec_savantclassify.sclassify_id=ec_expert.expert_pessional')
				->order('expert_id desc')
				->limit($Page->firstRow.','.$Page->listRows)
				->select();
		if(IS_AJAX){
			if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('expert'=>$expert),'json');
            } else {
                $this->ajaxReturn(array('expert' => array()), 'json');
            }
			
		}else{
			$this->assign('expert',json_encode(array('expert' => $expert)));
			$this->display();
		}
	}
	//专家介绍详情
	public function zhuanjiajieshao_xq(){
		$expert_id=I("get.expert_id");
		$expertxq=M('expert')
				->where("ec_savantclassify.sclassify_isable=1 and ec_expert.expert_state=1 and  ec_expert.expert_id='".$expert_id."'")
				->join('left join ec_savantclassify on ec_savantclassify.sclassify_id=ec_expert.expert_pessional')
				->find();  
		$this->assign('expertxq',$expertxq);
		$this->assign('expert_id',$expert_id);
		$this->display();	
	}
	
	//查询某个专家回答的问题  ---历史回答
	public function  lishihuida1(){
		$expert_id=I("expert_id");
		// 某个专家
		$where="ec_expert_questions.questions_expert_id='".$expert_id."' ";
		$count=M('expert_questions')
				->join('left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id')//农户表
				->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id')//问答分类表
				->where($where)
				->count();
		$Page = new \Think\Page($count,4);
		//农户提问的所有不针对专家的问题或者针对自己的问题
		$question=M('expert_questions')
				->join('left join ec_user on ec_user.user_id=ec_expert_questions.questions_user_id')//农户表
				->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_questions.questions_type_id')//问答分类表
				->where($where)
				->order('ec_expert_questions.questions_addtime desc ')
				->limit($Page->firstRow.','.$Page->listRows)
				->select();
		foreach ($question as $k => $v) {
			$question[$k]["questions_addtime"] = from_time($v['questions_addtime']);//时间换算
			$question[$k]["questions_image"] = explode('|',$v['questions_image']);//图片
		}	
		if(IS_AJAX){
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('question' => $question), 'json');
            } else {
                $this->ajaxReturn(array('question' => array()), 'json');
            }
        }else {
			$this->assign('question', json_encode(array('question' => $question)));
			$this->assign('expert_id',$expert_id);
            $this->display();
        } 
		
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
    

	//在线咨询
	public function zaixianzixun(){
		$questions_user_id=session('user_id');
		$expert_id=I("get.expert_id");//针对某个专家
		if($expert_id!=''||$expert_id!=null ){
			$questions_aim=1;
		}else{
			$questions_aim=2;
		}
		if(IS_POST){
			$info=I("post.info");
			$flag=M('expert_questions')->add(array('questions_user_id'=>$questions_user_id,'questions_type_id'=>$info['questions_type_id'],
					'questions_content'=>$info['questions_content'],'questions_addtime'=>time(),'questions_aim'=>$info['questions_aim'],
					'questions_expert_id'=>$info['questions_expert_id'],
					'questions_image'=>$info['questions_image']));
            //给专家发消息 
            $message= new \Admin\Model\MessageModel();
            $message->get_message($info['questions_expert_id'],$flag,"农户提问通知","您好，有农户向你提问题了，点击查看！");  
			if ($flag) {
				jump_success('咨询问题提交成功！', U('expert/zhuanjiawenda'));
			} else {
				jump_success('咨询问题提交失败！', U('expert/zhuanjiawenda'));
			}
		}else{
			$this->assign('type',D('Expert')->getExpertType());//问题分类
			$this->assign('expert_id',$expert_id);
			$this->assign('questions_aim',$questions_aim);
			$this->display();
		}
	}
	//我的商品订单
	public function wodeshangpindingdan(){
        $this->deltuangou();
		$user_id=session('user_id');
		$order_state=I("get.order_state");
		$where="ec_orderinfo.user_id='".$user_id."' ";
		if($order_state==''||$order_state==null||$order_state==0){
			$where.="";
		}else{
			$where.=" and ec_orderinfo.order_state='".$order_state."'";
		}
		$count=M('orderinfo')->where($where)->count();
		$Page = new \Think\Page($count,4);
		//查询所有订单 
		$order=M('orderinfo')
				->where($where)
				->order('ec_orderinfo.order_addtime desc ')
				->limit($Page->firstRow.','.$Page->listRows)
				->select();
		foreach($order as $k=>$v){
			$order[$k]['order_addtime']=date("Y-m-d",$v['order_addtime']);
			$order[$k]['order_addtimes']=date("H:i:s",$v['order_addtime']);
			$where_="  ec_orderlist.order_id='".$v['order_id']."' ";
			//查询订单里面的商品
			$order[$k]['shangpins'] = M('orderlist')
									->join('left join ec_agcygoods on ec_agcygoods.agcy_goods_id = ec_orderlist.goods_id')
									->where($where_)
									->select();
			//订单数量
			$order[$k]['counts'] = M('orderlist')
									->where($where_)
									->sum('goods_num');
            foreach($order[$k]['shangpins'] as $kk=>$vv){
                $image=explode('|',$vv['agcy_goods_images']);
                $order[$k]['shangpins'][$kk]['images'] = $image[0];
            } 
            if($v['order_act_id']!=0){
                $act=M('activity')->find($v['order_act_id']);
                if($act['act_classify']==1){
                    $order[$k]['act_type']='秒杀';
                }else{
                    $order[$k]['act_type']='团购';
                }
                
            }    
			
		}
        //dd($order);
		if(IS_AJAX){
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('order' => $order), 'json');
            } else {
                $this->ajaxReturn(array('order' => array()), 'json');
            }
        }else {
			$this->assign('count',$count);
			$this->assign('order',json_encode(array('order' => $order)));
			$this->display();
		}
	}
	
	//取消订单
	function quxiao(){
		$order_id=I('order_id');
		//删除订单
		$order_qx=M('orderinfo')->where('order_id='.$order_id)->delete();
		$order_xq=M('orderlist')->where('order_id='.$order_id)->delete();
		if($order_qx&&$order_xq){
			echo json_encode(array('status'=>1));
		}else{
			echo json_encode(array('status'=>0));
		}
	}
    //删除未支付的团购，秒杀
    public function deltuangou(){
        $user_id=session('user_id');
        $list=M("orderinfo")->where('user_id='.$user_id)->select();
        foreach($list as $k=>$v){
            if($v['order_state']==1 && $v['order_act_id']!=0){
                M('orderinfo')->where('order_id='.$v['order_id'])->delete();
            }
            
        }
        
    }
    
}
