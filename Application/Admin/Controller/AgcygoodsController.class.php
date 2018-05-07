<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class AgcygoodsController extends Controller {

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
	 //公共success
    public function home_success($msg, $url = "", $wait = 3) {
        $this->assign('msg', $msg);
        $this->assign('url', $url ? $url : U("index/index"));
        $this->assign('wait', $wait);
        $this->display('user/home_success');
        exit();
    }

    //公共error
    public function home_error($msg, $url = "", $wait = 3) {
        $this->assign('msg', $msg);
        $this->assign('url', $url ? $url : U("index/index"));
        $this->assign('wait', $wait);
        $this->display('user/home_error');
        exit();
    }
    //经销商产品列表
    public function index() {  
        $where = " 1  ";
        $info = I("info");
        if ($info) {// 搜索条件
            @extract($info);
            if ($book_name_s) { 
                $where .= " and (ec_agcygoods.agcy_goods_name LIKE '%" . $book_name_s . "%'
							or ec_agcy.agcy_name LIKE '%" . $book_name_s . "%'
				)";
                $book_name_s = urldecode(trim($info['book_name_s']));
                $this->assign('book_name_s', $book_name_s);
            } 
            if ($yiji_s) {
                $where .= " and ec_agcygoods.agcy_classifyone=" . $yiji_s;
                $erji = M("agcyclassify")->where("agcy_classify_fatherid=" . $yiji_s)->select();
                $this->assign('erji', $erji);
                $this->assign('yiji_s', $yiji_s);
            }
            if ($erji_s) {
                $where .= " and ec_agcygoods.agcy_classifytwo='" . $erji_s . "'";
                $this->assign('erji_s', $erji_s);
            }
			if ($agcy_goods_states) { 
                $where .= " and ec_agcygoods.agcy_goods_states='" . $agcy_goods_states . "'";
                $this->assign('order_', $agcy_goods_states);
            }
			 
        }

        $model = M('agcygoods');
        // 查询满足要求的总记录数
        $count = $model
				->join("LEFT JOIN ec_agcyclassify AS yiji ON yiji.agcy_classify_id = ec_agcygoods.agcy_classifyone")
                ->join("LEFT JOIN ec_agcyclassify AS erji ON erji.agcy_classify_id = ec_agcygoods.agcy_classifytwo")
				->join("LEFT JOIN ec_agcy on ec_agcygoods.agcy_id=ec_agcy.agcy_id")
				->join("LEFT JOIN ec_factory on ec_agcygoods.agcy_factory_id=ec_factory.factory_id")
                ->where($where)
                ->count();
        $Page = new \Think\Page($count, 20);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info[' . $key . ']'] = urlencode($val);
            }
        }
        $pageshow = $Page->adminshow();
        // 进行分页数据查询
        $listinfo = $model 
                ->join("LEFT JOIN ec_agcyclassify AS yiji ON yiji.agcy_classify_id = ec_agcygoods.agcy_classifyone")
                ->join("LEFT JOIN ec_agcyclassify AS erji ON erji.agcy_classify_id = ec_agcygoods.agcy_classifytwo")
				->join("LEFT JOIN ec_agcy on ec_agcygoods.agcy_id=ec_agcy.agcy_id")
				->join("LEFT JOIN ec_factory on ec_agcygoods.agcy_factory_id=ec_factory.factory_id")
				->field("ec_agcygoods.*,yiji.agcy_classify_name AS yiji_name,erji.agcy_classify_name AS erji_name,ec_agcy.agcy_name,ec_factory.factory_name")
                ->where($where)
                ->order("agcy_goods_id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select(); 
        foreach ($listinfo AS $k => $v) {
            $listinfo[$k]['cover'] = explode('|', $v['book_image'])[0];
            $listinfo[$k]['book_remark'] = str_cut($v['book_remark'], 20);
        }
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow);
		//审核状态
		$str_cut=D('Agcypopinfo')->state();  
        //一级分类
        $yiji = M("agcyclassify")->where("agcy_classify_fatherid=0")->select();
        $this->assign('yiji', $yiji); 
		$this->assign('str_cut', $str_cut); 
        $this->display();
    }
	 
	//详情信息
    public function info() {
        $agcy_goods_id = I("get.agcy_goods_id"); 
        $agcygoodsinfo = M('agcygoods')
				->where("ec_agcygoods.agcy_goods_id='".$agcy_goods_id."'")
				->join("LEFT JOIN ec_agcyclassify AS yiji ON yiji.agcy_classify_id = ec_agcygoods.agcy_classifyone")
                ->join("LEFT JOIN ec_agcyclassify AS erji ON erji.agcy_classify_id = ec_agcygoods.agcy_classifytwo")
				->join("LEFT JOIN ec_agcy on ec_agcygoods.agcy_id=ec_agcy.agcy_id")
				->field("ec_agcygoods.*,yiji.agcy_classify_name AS yiji_name,erji.agcy_classify_name AS erji_name,ec_agcy.agcy_name,ec_agcy.agcy_address,ec_agcy.agcy_manager,ec_agcy.agcy_tel")  
                ->find(); 
		$res=explode('|',$agcygoodsinfo['agcy_goods_images']);
		$this->assign('res', $res); 
        $this->assign('agcygoodsinfo', $agcygoodsinfo); 
        $this->display();
    } 

    function get_erji() {
        $yiji_id = I("yiji_id");
        $where = "agcy_classify_fatherid=" . $yiji_id;
        $erji = M("agcyclassify")->where($where)->select();
        $this->ajaxReturn($erji);
    }
 
	 
	
	//产品审核通过
	 public function adopt(){
        $agcy_goods_id = I("get.agcy_goods_id"); 
        $popinfo=M('agcygoods')
            ->where('ec_agcygoods.agcy_goods_id = '.$agcy_goods_id)
            ->find();
        $this->assign('popinfo',$popinfo);
        $this->display();
    }
    public function pass() {
		$agcy_default_remskss = I('post.agcy_default_remskss');
        M("agcygoods")->where('agcy_goods_id=' . I("post.agcy_goods_id"))->save(array("agcy_goods_states" => 2 ,'agcy_default_remskss'=>$agcy_default_remskss));
        $this->home_success("审核通过", U("agcygoods/index"));
    }

    //产品审核不通过
	public function refuse(){
        $agcy_goods_id = I("get.agcy_goods_id"); 
        $pop=M('agcygoods')
            ->where('ec_agcygoods.agcy_goods_id = '.$agcy_goods_id)
            ->find();
        $this->assign('popinfo',$pop);
        $this->display();
    }
    public function reject() {
		$agcy_default_remskss = I('post.agcy_default_remskss');
        M("agcygoods")->where('agcy_goods_id=' . I("post.agcy_goods_id"))->save(array("agcy_goods_states" => 3,'agcy_default_remskss'=>$agcy_default_remskss));
        $this->home_error("审核不通过", U("agcygoods/index"));
    }
	
	//ajax处理状态
	public function ajaxable_on(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "agcy_goods_state"://on
				M("agcygoods")->where('agcy_goods_id='.$id)->save(array("agcy_goods_state"=>1));
				break;
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	
	public function ajaxable_off(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "agcy_goods_state"://off
				M("agcygoods")->where('agcy_goods_id='.$id)->save(array("agcy_goods_state"=>2));
				break;
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	
	//编辑
	public function edit(){ 
		$agcy_goods_id = I('get.agcy_goods_id');
        if (IS_POST) {
            $data = I("info");
			$res = $data['agcy_goods_price'];
			$row = explode(',',$res);
			$data['agcy_default_price']=$row[0];
            M("agcygoods")->where("agcy_goods_id=" . $agcy_goods_id)->save($data); 
            jump('编辑商品成功！', U('agcygoods/index'));

        } else {
            $goods = M("agcygoods")
				->join("LEFT JOIN ec_agcy on ec_agcygoods.agcy_id=ec_agcy.agcy_id")
				->where("agcy_goods_id='".$agcy_goods_id."'")
				->find(); 
			//一级分类 
			
			$yiji = M("agcyclassify")->where("agcy_classify_fatherid=0")->select(); //分类显示的状态
            $this->assign('yiji', $yiji);
            $erji = M("agcyclassify")->where("agcy_classify_fatherid='".$goods['agcy_classifyone']."' ")->select(); //分类显示的状态
            $this->assign('erji', $erji);			
			$this->assign('goods', $goods); 
			 
            $this->display();
        }
    }
	
	
	 
	
	


}
