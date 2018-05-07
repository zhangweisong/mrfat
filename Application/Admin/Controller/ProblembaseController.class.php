<?php
namespace Admin\Controller;
use Think\Controller;
class ProblembaseController extends Controller {

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
	

	//专家答题库列表
    public function index(){
		$where=" 1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword']));
                $where.="and (ec_expert_problembase.problembase_question like '%".$info['keyword']."%' or ec_expert_problembase.problembase_answers like '%".$info['keyword']."%' )";
                $this->assign('keyword',urldecode($info['keyword']));
			}
            if($expert_realname){
				$info['expert_realname']=urldecode(trim($info['expert_realname']));
                $where.="and (ec_expert.expert_realname like '%".$info['expert_realname']."%')";
                $this->assign('expert_realname',urldecode($info['expert_realname']));
			}
			if($problembase_type_id){
				$info['problembase_type_id']=urldecode(trim($info['problembase_type_id']));
                $where.="and ec_expert_problembase.problembase_type_id ='".$info['problembase_type_id']."' ";  
                $this->assign('problembase_type_id',urldecode($info['problembase_type_id']));
			}
            if($problembase_satae){//状态  1未审核 2已审核
				$info['problembase_satae']=urldecode(trim($info['problembase_satae']));
                $where.="and ec_expert_problembase.problembase_satae ='".$info['problembase_satae']."' ";  
                $this->assign('problembase_satae',urldecode($info['problembase_satae']));
			}
            //时间
			if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_expert_problembase.problembase_addtime >=".strtotime_2($info['starttime']."00:00:00")." ";  
                $this->assign('starttime',$info['starttime']);
            }
           	if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_expert_problembase.problembase_addtime <=".strtotime_2($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
		}
		$expert_problembase=M('expert_problembase');
		// 查询满足要求的总记录数
		$count      = $expert_problembase
                    ->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_problembase.problembase_type_id')
                    ->join('left join ec_expert on ec_expert.expert_id=ec_expert_problembase.problembase_expert_id')
                    ->where($where)
                    ->count();
		$Page       = new \Think\Page($count,20);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$listinfo =  $expert_problembase
                    ->join('left join ec_expert_type on ec_expert_type.type_id=ec_expert_problembase.problembase_type_id')
                    ->join('left join ec_expert on ec_expert.expert_id=ec_expert_problembase.problembase_expert_id')
					->where($where)
					->order('ec_expert_problembase.problembase_id desc')
                    ->field('ec_expert_problembase.*,ec_expert_type.type_name,ec_expert.expert_realname')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$this->assign('listinfo',$listinfo);
		$this->assign('pageshow',$pageshow);
        //状态 1 未审核 2 已审核
		$this->assign('problembase_sataes',D('expert')->problembase_sataes());
        //问答分类
		$this->assign('expert_types',D('expert')->expert_types());
		$this->display();
    }
    //状态  1未审核 2已审核
    public function shenhe() {
        $problembase_id = I("get.problembase_id");
        M('expert_problembase')->where('problembase_id=' . $problembase_id)->save(array('problembase_satae'=>2));
        jump('审核成功！', U('problembase/index'));
    }
    //详情
    public  function  xiangqing(){
        $problembase_id=I("get.problembase_id");
        $problembase=M('expert_problembase')
                    ->join('left join ec_expert on ec_expert.expert_id=ec_expert_problembase.problembase_expert_id')
                    ->where('problembase_id='.$problembase_id)
                    ->field('ec_expert_problembase.*,ec_expert.expert_avator as image,ec_expert.expert_nickname as nickname,
                    ec_expert.expert_realname as realname')
                    ->find();
        //问答分类
        $this->assign('expert_types',D('expert')->expert_types());
        $this->assign('problembase',$problembase);
        $this->display();
    }
	//删除
	public function del(){
		$problembase_id=I("get.problembase_id");
		M('expert_problembase')->where('problembase_id='.$problembase_id)->delete();
		jump('答题库删除成功！',U('problembase/index'));
	}	
	
	
}