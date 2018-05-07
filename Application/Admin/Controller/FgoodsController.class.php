<?php

/*
 * 厂家产品管理
 * 张伟松
 * 2018年3月2日10:21:26
 */

namespace Admin\Controller;

use Think\Controller;

class FgoodsController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录状态
//        if (!D('Admin')->islogin()) {//未登录
//            jump('您尚未登录，请先登录！', U('login/login'));
//        }
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
    //店铺列表
    public function index() {
        $where = "1 ";
        $info = I("info");
        if ($info) { 
            @extract($info);
            //按名称搜索
//factory_name  p_id factory_man factory_tel
            if ($factory_name) {
                $factory_name = urldecode(trim($factory_name));
                $where .= " and (ec_factory.factory_name like '%" . $factory_name . "%')";
                $this->assign('factory_name', $factory_name);
            }
            if ($fgoods_name) {
                $fgoods_name = urldecode(trim($fgoods_name));
                $where .= " and (ec_fgoods.fgoods_name like '%" . $fgoods_name . "%')";
                $this->assign('fgoods_name', $fgoods_name);
            } 
            if ($add_person) { 
                $where .= " and (ec_fgoods.add_person=".$add_person.")";
                $this->assign('add_person', $add_person);
            } 
            
        }
        $count = M("fgoods")->where($where)
                ->join("LEFT JOIN ec_factory ON ec_factory.factory_id=ec_fgoods.factory_id")
                ->count();
        $pagesize = I('pagesize') ? I('pagesize') : 15;
        $Page = new \Think\Page($count, $pagesize);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info[' . $key . ']'] = urlencode($val);
            }
        }
        $Page->parameter['pagesize'] = $pagesize;
        $pageshow = $Page->adminshow();
        $fgoodslist = M('fgoods')
                ->field("ec_fgoods.*,ec_factory.*,a.f_classify_name name1,b.f_classify_name name2")
                ->join("LEFT JOIN ec_factory ON ec_factory.factory_id=ec_fgoods.factory_id")
                ->join("LEFT JOIN ec_fgoodsclassify a ON a.f_classify_id=ec_fgoods.fgoods_1")
                ->join("LEFT JOIN ec_fgoodsclassify b ON b.f_classify_id=ec_fgoods.fgoods_2")
                ->where($where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order("ec_fgoods.fgoods_id desc")
                ->select();  
        $this->assign('pageshow', $pageshow);
        $this->assign('fgoodslist', $fgoodslist);
       
        $this->display();
    }
    //产品上架
    public function ajax_on() {
        M("fgoods")->where('fgoods_id=' . I("get.fgoods_id"))->save(array("fgoods_state2" => 1));
    }

    //产品下架
    public function ajax_off() {
        M("fgoods")->where('fgoods_id=' . I("get.fgoods_id"))->save(array("fgoods_state2" => 0));
    }
    //通过
    public function adopt() {
        $fgoods_id = I('get.goods_id');
        $res = M('fgoods')
                ->where("ec_fgoods.fgoods_id='" . $fgoods_id . "'")
                ->find();
	  
        $this->assign('res', $res);
        $this->assign('fgoods_id', $fgoods_id);
        $this->display();
    }

    //驳回
    public function refuse() {
        $fgoods_id = I("goods_id");
        $res = M('fgoods')
                ->where("ec_fgoods.fgoods_id='" . $fgoods_id . "'")
                ->find();
        $this->assign('res', $res);
        $this->assign('fgoods_id', $fgoods_id);
        $this->display();
    }
    //产品审核通过
    public function pass() {
		$fgoods_rewss = I("post.fgoods_rewss");
        $fgoods_zhidaojia = I("post.fgoods_zhidaojia");
        M("fgoods")->where('fgoods_id=' . I("fgoods_id"))->save(array("fgoods_state" => 1,'fgoods_rewss'=>$fgoods_rewss,'fgoods_zhidaojia'=>$fgoods_zhidaojia));
        $this->home_success("审核通过", U("fgoods/index"));
    }

    //产品审核不通过
    public function reject() {
		$fgoods_rewss = I("post.fgoods_rewss");
        M("fgoods")->where('fgoods_id=' . I("fgoods_id"))->save(array("fgoods_state" => 2,'fgoods_rewss'=>$fgoods_rewss));
        $this->home_error("审核不通过", U("fgoods/index"));
    }

    //添加店铺
    public function addfgoods() {
        if (IS_POST) {
            $info = I("info");
            $info['factory_pwd'] = md5('a123456');
            $info['factory_addtime'] = time();
            $info['add_person'] = 1; 
            $info['fgoods_state'] = 1; 
            $info['fgoods_state2'] = 1;  
            if (M("fgoods")->add($info)) {
                jump('产品添加成功！', U('index'));
            } else {
                jump('产品添加失败！', U('index'));
            }
        } else {  
            $factorylist = M("Factory")->select();  
            $this->assign('factorylist', $factorylist);
            $yiji = M("Fgoodsclassify")->where("f_classify_fatherid=0 and f_classify_able=1 ")->select(); //分类显示的状态
            $this->assign('yiji', $yiji);
            $this->display();
        }
    }
 
    private function usrname_exist($username) {
        $count = M("factory")->where("factory_usrnm='" . $username . "'")->count();
        if ($count) {
            return true;
        } else {
            return false;
        }
    }
    //获取二级分类
    function get_erji() {
        $yiji_id = I("yiji_id");
        $where = "f_classify_fatherid='" . $yiji_id . "' and f_classify_able=1 ";
        $erji = M("Fgoodsclassify")->where($where)->select();
        $this->ajaxReturn($erji);
    }

    //检测产品名是否重复
    public function checkusername() {
        $factory_id = I("factory_id");
        $fgoods_name = I("fgoods_name");
        $flag = M("fgoods")->where("factory_id=".$factory_id." AND fgoods_name='".$fgoods_name."'")->find(); 
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
        $flag = M("fgoods")->where("factory_id=".$factory_id." AND fgoods_name='".$fgoods_name."' AND fgoods_id<>".$fgoods_id)->find(); 
        if ($flag) {
            $this->ajaxReturn(array("status" => 0, "msg" => "该产品已存在！"));
        }
        $this->ajaxReturn(array("status" => 1, "msg" => "ok"));
    }

    //编辑店铺
    public function editfgoods() {
        $fgoods_id = I("get.fgoods_id");
        if (IS_POST) {
            $info = I("info");
            $fgoods_zhidao = explode(',',$info['fgoods_zhidaojia']);
            M("fgoods")->data($info)->where("fgoods_id=" . $fgoods_id)->save(); 
            //更新经销商产品中的商品价格
            $goods = M("fgoods")->where("fgoods_id=" . $fgoods_id)->find(); 
            $res = M('agcygoods')
                ->where("agcy_factory_id='".$goods['factory_id']."' and agcy_fgoods_id='".$fgoods_id."' ")
                ->save(array('agcy_goods_price'=>$info['fgoods_zhidaojia'],'agcy_default_price'=>$fgoods_zhidao[0]));
            //更新经销商产品中的agcy_default_price
            
            jump('产品编辑成功！', U('index'));
        } else { 
            $fgoodsinfo = M("fgoods")->find($fgoods_id);
            $factorylist = M("Factory")->select();  
            $this->assign('fgoodsinfo', $fgoodsinfo);
            $this->assign('factorylist', $factorylist);
            $yiji = M("Fgoodsclassify")->where("f_classify_fatherid=0 and f_classify_able=1 ")->select(); //分类显示的状态
            $this->assign('yiji', $yiji);
            $erji = M("Fgoodsclassify")->where("f_classify_fatherid=".$fgoodsinfo['fgoods_1']." and f_classify_able=1 ")->select(); //分类显示的状态
            $this->assign('erji', $erji);
            $this->display();
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

}
