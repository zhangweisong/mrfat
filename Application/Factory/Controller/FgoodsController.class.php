<?php

namespace Factory\Controller;

use Think\Controller;

class FgoodsController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录状态
        if (!D('factory')->islogin()) {//未登录
            jump('您尚未登录，请先登录！', U('login/login'));
        }
    }

    //空方法，防止报错
    public function _empty() {
        $this->index();
    }

    //厂家商品列表
    public function index() {
        $factory_id = session("factory.factory_id");
        $where = "ec_fgoods.factory_id=" . $factory_id;
        $info = I("info");
        if ($info) {// 搜索条件  
            @extract($info);
            if ($keyword) {
                $keyword = urldecode(trim($keyword));
                $where .= " and (ec_fgoods.fgoods_name LIKE '%" . $keyword . "%'
				            OR ec_factory.factory_name LIKE '%" . $keyword . "%') ";
                $this->assign('keyword', $keyword);
            }
            if ($fgoods_state != "") {
                $where .= " and ec_fgoods.fgoods_state='" . $fgoods_state . "'";
                $this->assign('fgoods_state', $fgoods_state);
            }
            if($yiji){
                $info['yiji']=urldecode(trim($info['yiji']));
                $where.=" and ec_fgoods.fgoods_1 = ".$yiji;
                $er_ji=M('fgoodsclassify')->where('f_classify_fatherid = '.$yiji)->select(); 
                $this->assign('er_ji',$er_ji);
                $this->assign('yi_ji',$yiji);
            }
            if($erji){
                $info['erji']=urldecode(trim($info['erji']));
                $where.=" and  ec_fgoods.fgoods_2 = ".$erji; 
                $this->assign('erji',urldecode($erji));
            }
            
             
        }
        $model = M('Fgoods');
        // 查询满足要求的总记录数
        $count = $model
                ->where($where)
                ->join('left join ec_factory on ec_factory.factory_id=ec_fgoods.factory_id')
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
                ->where($where)
                ->join('left join ec_factory on ec_factory.factory_id=ec_fgoods.factory_id')
                ->order("ec_fgoods.fgoods_id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                //->fetchSql(true)
                ->select();
        //echo $listinfo;die;
        foreach ($listinfo AS $k => $v) {
            $listinfo[$k]['fgoods_detail'] = str_cut($v['fgoods_detail'], 20);
        } 
        //一级分类
        $yiji = M('fgoodsclassify')->where("f_classify_fatherid=0")->select();
        
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow); 
        $this->assign('yiji', $yiji); 
        $this->display();
    }
    
    public function geterji(){
        $f_classify_id=I('get.yiji');
        $result=M('fgoodsclassify')->where('f_classify_fatherid = '.$f_classify_id)->select();
        $this->ajaxReturn(array("data"=>$result));
    }

    //添加商品
    public function add() {
        if (IS_POST) {
            $data = I("info");
            $data['add_person'] = 2;
            $data['factory_id'] = session('factory.factory_id'); 
            $flag = M("Fgoods")->add($data);
            if ($flag) {
                jump('添加商品成功！', U('fgoods/index'));
            } else {
                jump('添加商品失败！', U('fgoods/index'));
            }
        } else {
            $yiji = M("Fgoodsclassify")->where("f_classify_fatherid=0 and f_classify_able=1 ")->select(); //分类显示的状态
            $this->assign('yiji', $yiji);
            $this->display();
        }
    }

    //获取二级分类
    function get_erji() {
        $yiji_id = I("yiji_id");
        $where = "f_classify_fatherid='" . $yiji_id . "' and f_classify_able=1 ";
        $erji = M("Fgoodsclassify")->where($where)->select();
        $this->ajaxReturn($erji);
    }

    //编辑商品
    public function edit() {
        $fgoods_id = I("get.fgoods_id");
        if (IS_POST) {
            $data = I("info"); 
            $data['fgoods_state'] = 0;
            M("Fgoods")->where("fgoods_id=" . $fgoods_id)->save($data);
            jump('编辑商品成功！', U('fgoods/index'));
        } else {
            $goods = M("Fgoods")->find($fgoods_id);
            $goods_images = explode('|', $goods['fgoods_images']);
            $yiji = M("fgoodsclassify")->where("f_classify_fatherid=0 and f_classify_able=1 ")->select();
            $erji = M("fgoodsclassify")->where("f_classify_fatherid=" . $goods['fgoods_1'])->select();
            $this->assign('yiji', $yiji);
            $this->assign('goods_images', $goods_images);
            $this->assign('erji', $erji);
            $this->assign('goods', $goods);
            $this->display();
        }
    }
	
	
	 //检测产品名是否重复
    public function checkusername() {
        $factory_id = I("factory_id");
        $fgoods_name = I("fgoods_name");
        $flag = M("fgoods")->where("fgoods_name='".$fgoods_name."'")->find(); 
        if ($flag) {
            $this->ajaxReturn(array("status" => 0, "msg" => "该产品已存在！"));
        }
        $this->ajaxReturn(array("status" => 1, "msg" => "ok"));
    }
    //检测产品名是否重复
    public function checkusername_edit() {
        $factory_id = I("factory_id");
        $fgoods_name = I("fgoods_name");
        $fgoods_id = I("fgoods_id");
        $flag = M("fgoods")->where("fgoods_name='".$fgoods_name."' AND fgoods_id<>".$fgoods_id)->find(); 
        if ($flag) {
            $this->ajaxReturn(array("status" => 0, "msg" => "该产品已存在！"));
        }
        $this->ajaxReturn(array("status" => 1, "msg" => "ok"));
    }
	
    //删除商品
    public function delete() {
        $fgoods_id = I("fgoods_id");
        $flag = M("Fgoods")->where("fgoods_id=" . $fgoods_id)->delete();
        if ($flag) {
            jump('删除商品成功！', U('fgoods/index'));
        } else {
            jump('删除商品失败！', U('fgoods/index'));
        }
    }

    //查看详情
    public function fgoods_xq() {
        $fgoods_id = I("fgoods_id");
        //订单详情
        $orderinfo = M('fgoods')
                ->where("ec_fgoods.fgoods_id=" . $fgoods_id)
                ->join('left join ec_factory on ec_factory.factory_id=ec_fgoods.factory_id')
                ->find();
        $this->assign('orderinfo', $orderinfo);
        $this->display();
    }

    //ajax处理状态
	public function ajaxable_on(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "fgoods_state2"://on
				M("Fgoods")->where('fgoods_id='.$id)->save(array("fgoods_state2"=>1));
				break;
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	
	public function ajaxable_off(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "fgoods_state2"://off
				M("Fgoods")->where('fgoods_id='.$id)->save(array("fgoods_state2"=>2));
				break;
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
}
