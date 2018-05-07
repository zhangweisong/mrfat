<?php
namespace Admin\Controller;
use Think\Controller;

class ExpertstatisticsController extends Controller {

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
	
	//专家统计
    public function index() {
		$where="1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_expert.expert_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_expert.expert_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
		}
		if(!$starttime){
			$starttime=date("Y-m-d",strtotime("-15 day"));$this->assign('starttime',$starttime);
		}
		if(!$endtime){
			$endtime=date("Y-m-d",SYS_TIME);$this->assign('endtime',$endtime);
		}
		//$this->assign('title',"店铺统计".$starttime."至".$endtime);

		$daylist=$this->get_daylist($starttime,$endtime);
		$infolist=array();
		foreach($daylist as $k=>$v){
			$where_=$where;
			$where_.=" and ec_expert.expert_addtime>=".strtotime($v." 00:00:00")." ";
			$where_.=" and ec_expert.expert_addtime<=".strtotime($v." 23:59:59")." ";
			$infolist[$k]['nums'] =  M('expert')->where($where_)->count();
			$infolist[$k]['dayinfo'] =$v;
		}
		$this->assign('infolist',$infolist);
        $this->display();
    }
    
	///////////////////////////////////////////////////////////////////////
	//获取两个日期段内所有日期
	public function get_daylist($startdate,$enddate){
		$stimestamp = strtotime($startdate);
		$etimestamp = strtotime($enddate);
		// 计算日期段内有多少天
		$days = ($etimestamp-$stimestamp)/86400+1;
		// 保存每天日期
		$date = array();
		for($i=0; $i<$days; $i++){
			$date[] = date('Y-m-d', $stimestamp+(86400*$i));
		}
        //dd($date);
		return $date;
	}
}