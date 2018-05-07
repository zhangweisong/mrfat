<?php
namespace Factory\Controller;
use Think\Controller;
class AgcyapplyController extends Controller {

    function __construct() {
        //继承父类 
        parent::__construct();
        //判断登录状态
		if(!D('Factory')->islogin()){//未登录
			jump('您尚未登录，请先登录！',U('login/login'),3);
		}
    }

    //空方法，防止报错
    public function _empty() {
        $this->index();
    }
	
	
    //经销商代理申请表
    public function index() {  
        $factory_id=session('factory.factory_id');
        $where = "1 and ec_agcyapply.factory_id=".$factory_id." ";
        $info = I("info");
        if ($info) {
            @extract($info);
            //按负责人搜索
            if ($key) {
                $key = urldecode(trim($key));
                $where .= " and (ec_agcy.agcy_name like '%".$key."%'
					or ec_fgoods.fgoods_name like '%".$key."%'
					or ec_factory.factory_name like '%".$key."%'
					or ec_factory.factory_man like '%".$key."%'
					or ec_factory.factory_tel like '%".$key."%'
				) ";
                $this->assign('keys', $key);
            }  
            //按状态搜索
            if ($state) {
                $state = urldecode(trim($state));
                $where .= " and ec_agcyapply.agcy_apply_flag like '%".$state."%' ";
                $this->assign('states', $state);
            } 
            //按添加时间搜索
            if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_agcyapply.agcy_apply_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_agcyapply.agcy_apply_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
        }
        $count = M("agcyapply")
			->where($where)
			->join('left join ec_factory on ec_agcyapply.factory_id=ec_factory.factory_id')
			->join('left join ec_agcy on ec_agcyapply.agcy_id=ec_agcy.agcy_id')
			->join('left join ec_fgoods on ec_agcyapply.fgoods_id=ec_fgoods.fgoods_id')
			->field('ec_agcyapply.*,ec_factory.factory_name,ec_factory.factory_man,ec_factory.factory_tel,ec_agcy.agcy_name,ec_fgoods.fgoods_name,ec_fgoods.fgoods_images')
            ->count();
        
        $Page = new \Think\Page($count, 15);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info[' . $key . ']'] = urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        $agcyapply = M('agcyapply')
				->where($where)
				->join('left join ec_factory on ec_agcyapply.factory_id=ec_factory.factory_id')
				->join('left join ec_agcy on ec_agcyapply.agcy_id=ec_agcy.agcy_id')
				->join('left join ec_fgoods on ec_agcyapply.fgoods_id=ec_fgoods.fgoods_id')
				->field('ec_agcyapply.*,ec_factory.factory_name,ec_factory.factory_man,ec_factory.factory_tel,ec_agcy.agcy_name,ec_fgoods.fgoods_name,ec_fgoods.fgoods_images')
                ->order("ec_agcyapply.agcy_apply_id desc")
				->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();  
		//状态
		$state = D('agcyapply')->state(); 
        $this->assign('pageshow', $pageshow);
        $this->assign('agcyapply', $agcyapply); 
        $this->assign('state', $state); 
        $this->display();
    }
    

    
    
    //详情信息
    public function info() {
        $agcy_apply_id = I("get.agcy_apply_id"); 
        $agcyapply = M('agcyapply')
				->where("ec_agcyapply.agcy_apply_id='".$agcy_apply_id."'")
				->join('left join ec_factory on ec_agcyapply.factory_id=ec_factory.factory_id')
				->join('left join ec_agcy on ec_agcyapply.agcy_id=ec_agcy.agcy_id')
				->join('left join ec_fgoods on ec_agcyapply.fgoods_id=ec_fgoods.fgoods_id')
				->field('ec_agcyapply.*,ec_factory.factory_name,ec_factory.factory_man,ec_factory.factory_tel,ec_factory.factory_address,ec_agcy.agcy_name,ec_agcy.agcy_address,ec_agcy.agcy_manager,ec_agcy.agcy_tel,ec_fgoods.*') 
                ->find(); 
		$state = D('agcyapply')->state(); 		
        $this->assign('agcyapply', $agcyapply);
		$this->assign('state', $state); 
        $this->display();
    }
	
	
     

}
