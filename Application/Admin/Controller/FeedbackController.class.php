<?php

namespace Admin\Controller;
use Think\Controller;
class FeedbackController extends Controller{
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

    //意见反馈
    public  function  index(){
        $where="1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            //关键字
            if($keyword){
                $info['keyword']=urldecode(trim(str_replace("'","",$info['keyword'])));
                $where.=" and (ec_user.realname like '%".$info['keyword']."%' or ec_user.phone like '%".$info['keyword']."%' or admin.realname like '%".$info['keyword']."%')";
                $this->assign('keyword',$info['keyword']);
            }                    
            //提交意见开始时间
            if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_feedback.addtime >=".strtotime($info['starttime']."00:00:00")." ";
                $this->assign('starttime',$info['starttime']);
            }
            //提交意见结束时间
            if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_feedback.addtime <=".strtotime($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
			//回复开始时间
            if($starttime2){
                $info['starttime2']=urldecode(trim($info['starttime2']));
                $where.=" and ec_feedback.feedbackaddtime >=".strtotime($info['starttime2']."00:00:00")." ";
                $this->assign('starttime2',$info['starttime2']);
            }
            //回复结束时间
            if($endtime2){
                $info['endtime2']=urldecode(trim($info['endtime2']));
                $where.=" and ec_feedback.feedbackaddtime <=".strtotime($info['endtime2']."23:59:59")." ";
                $this->assign('endtime2',$info['endtime2']);
            }
		   //反馈类型
            if($feedback_type){
                $info['feedback_type']=urldecode(trim($info['feedback_type']));
                $where.=" and ec_feedback.feedback_type = ".$info['feedback_type'];
                $this->assign('feedback_type1',$info['feedback_type']);
            }
            //用户状态
            if($state){
                $info['state']=urldecode(trim($info['state']));
                $where.=" and ec_feedback.state = ".$info['state'];
                $this->assign('state1',$info['state']);
            }			
        }

        $feedback=M('feedback');
        // 查询满足要求的总记录数
        $count      = $feedback
            ->where($where)
            ->join('left join ec_user on ec_feedback.user_id = ec_user.user_id')
			->join('left join ec_admininfo as admin on admin.admin_id = ec_feedback.admin_id')
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
        $listinfo = $feedback
            ->where($where)
            ->join('left join ec_user on ec_feedback.user_id = ec_user.user_id')
			->join('left join ec_admininfo as admin on admin.admin_id = ec_feedback.admin_id')
            ->order('ec_feedback.feedback_id desc')
            ->field('ec_feedback.*,admin.realname as realname1 ,ec_user.realname,ec_user.phone,ec_user.nickname')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select(); 
        foreach($listinfo as $k=>$v){ 
            $listinfo[$k]['realname1'] = $v['realname1']?$v['realname']:$v['nickname'];
			if(empty($listinfo[$k]['feedbackaddtime'])){
				$listinfo[$k]['feedbackaddtime']='---';
			}			
		}				
        $this->assign('listinfo',$listinfo);
        $this->assign('state',D('feedback')->state());
        $this->assign('feedback_type',D('feedback')->feedback_type());		
        $this->assign('pageshow',$pageshow);
        $this->display();
    }
	
	//查看详情
	public  function xiangqing(){
		$feedback_id=I('get.feedback_id');
		 //dump($feedback_id);die;
		 $listinfo = M('feedback')
            ->where('ec_feedback.feedback_id='.$feedback_id)
            ->join('left join ec_user on ec_feedback.user_id = ec_user.user_id')
			->join('left join ec_admininfo as admin on admin.admin_id = ec_feedback.admin_id')         
            ->field('ec_feedback.*,admin.realname as realname1 ,ec_user.realname,ec_user.phone,ec_user.nickname')           
            ->find();  
        $listinfo['realname'] = $listinfo['realname']?$listinfo['realname']:$listinfo['nickname'];       
        $this->assign('listinfo',$listinfo);
        $this->assign('state',D('feedback')->state());        
        $this->display();
	}
   public  function feedback(){
	   $feedback_id=I('get.feedback_id');
       $status=1;	   
	   if(IS_POST){		
		  $feedback=I('post.feedback');
		  $feedback_id=I('post.feedback_id');
		  $admin_id = session('admin.admin_id',"");//管理员id
          $data=array('state'=>2,'feedbackaddtime'=>time(),'feedback'=>$feedback,'admin_id'=>$admin_id);		  
		  $result = M('feedback')
            ->where('ec_feedback.feedback_id='.$feedback_id)               
            ->save($data); 
          if($result){
                $status=2;
            }				    				  
	   }
		 $this->assign('feedback_id',$feedback_id);
		 $this->assign('status',$status);
		 $this->display();                      
	
    }
}