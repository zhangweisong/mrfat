<?php
namespace Factory\Controller;
use Think\Controller;
class OrderpopController extends Controller {
	function __construct() {
		//继承父类
		parent::__construct();		
		//判断登录状态
		if(!D('Factory')->islogin()){//未登录
			jump('您尚未登录，请先登录！', U('login/login'));
		}	
    }
	//空方法，防止报错
	public function _empty(){
        $this->index();
    } 
   //厂家订单统计
    public function index() { 
		$where=" 1 and ec_forder.forder_flag=3";
		$info=I("info");
		if ($info){
			@extract($info); 
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_forder.forder_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_forder.forder_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }  
		}
		if(!$starttime){
			$starttime=date("Y-m-d",strtotime("-15 day"));
			$this->assign('starttime',$starttime);
		}
		if(!$endtime){
			$endtime=date("Y-m-d",SYS_TIME);
			$this->assign('endtime',$endtime);
		} 

		$daylist=$this->get_daylist($starttime,$endtime); 
		$infolist=array();
		foreach($daylist as $k=>$v){
			$where_=$where;
			$where_.=" and ec_forder.forder_addtime>=".strtotime($v." 00:00:00")." ";
			$where_.=" and ec_forder.forder_addtime<=".strtotime($v." 23:59:59")." ";
			$infolist[$k]['pop_fact'] =  M('forder') 
				->where($where_) 
				->sum("forder_price"); 
			$infolist[$k]['num'] =  M('forder') 
				->where($where_) 
				->count();  
            $agcys = M('forder') 
                ->field('ec_agcy.agcy_id,agcy_name')
                ->distinct(True)
				->join('left join ec_agcy on ec_forder.agcy_id=ec_agcy.agcy_id')
				->where($where_)
				->select();  
			$agcysA =  i_array_column($agcys,'agcy_name');
            $infolist[$k]['agcy'] = implode(',',$agcysA);
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
			$date[] = date('Y-m-d', $etimestamp-(86400*$i));
		}
		return $date;
	}
}