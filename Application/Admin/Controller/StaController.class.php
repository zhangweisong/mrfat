<?php
namespace Admin\Controller;
use Think\Controller;
class StaController extends Controller
{
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
	//注册农户统计
    public function index() {
		$where="1 ";
	    $info = I('info');
		if ($info){
			@extract($info);
			if($starttime){$this->assign('starttime',$starttime);}
			if($endtime){$this->assign('endtime',$endtime);}
		}
		if(!$starttime){
			$starttime=date("Y-m-d",strtotime("-15 day"));$this->assign('starttime',$starttime);
		}
		if(!$endtime){
			$endtime=date("Y-m-d",SYS_TIME);$this->assign('endtime',$endtime);
		}
		$this->assign('title',"注册农户统计".$starttime."至".$endtime);

		$daylist=$this->get_daylist($starttime,$endtime);
		$infolist=array();
		foreach($daylist as $k=>$v){
			$where_=$where;
			$where_.=" and addtime>=".strtotime($v." 00:00:00")." ";
			$where_.=" and addtime<=".strtotime($v." 23:59:59")." ";
			$infolist[$k]['nums'] = M("User")->where($where_)->count();
			$infolist[$k]['dayinfo'] =$v;
		}
		$this->assign('infolist',$infolist);
        $this->display();
    }
	//农户订单统计
    public function userorder() {
	    $where=" ec_orderinfo.order_state=3 ";
		//$where="1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_orderinfo.order_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_orderinfo.order_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
		}
		if(!$starttime){
			$starttime=date("Y-m-d",strtotime("-15 day"));$this->assign('starttime',$starttime);
		}
		if(!$endtime){
			$endtime=date("Y-m-d",SYS_TIME);$this->assign('endtime',$endtime);
		}
		$this->assign('title',"用户订单统计".$starttime."至".$endtime);

		$daylist=$this->get_daylist($starttime,$endtime);
		$infolist=array();
		foreach($daylist as $k=>$v){
			$where_=$where;
			$where_.=" and ec_orderinfo.order_addtime>=".strtotime($v." 00:00:00")." ";
			$where_.=" and ec_orderinfo.order_addtime<=".strtotime($v." 23:59:59")." ";
			$infolist[$k]['nums'] =  M('orderinfo')
									->where($where_)
									//用户
									->join("left join ec_user on ec_orderinfo.user_id=ec_user.user_id")
									->count();
			$money_ =  M('orderinfo')
									->where($where_)
									//用户
									->join("left join ec_user on ec_orderinfo.user_id=ec_user.user_id") 
									->sum("ec_orderinfo.order_money");
			$infolist[$k]['money']=round($money_,2);									
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
		return $date;
	}
}