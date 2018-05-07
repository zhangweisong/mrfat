<?php

/*
 * 经销商订单管理
 * 张伟松
 * 2018年3月2日10:53:15 
 */

namespace Admin\Controller;

use Think\Controller;

class ForderController extends Controller {

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

    //店铺列表
    public function index() {
        $where = "1 ";
        $info = I("info");
        if ($info) {
            @extract($info);
            //按名称搜索
//factory_name  p_id factory_man factory_tel
            if ($agcy_name) {
                $agcy_name = urldecode(trim($agcy_name));
                $where .= " and (ec_agcy.agcy_name like '%" . $agcy_name . "%'
                            or ec_forder.forder_number like '%" . $agcy_name . "%'
                )";
                $this->assign('agcy_name', $agcy_name);
            }
            if ($forder_flag) {
                $where .= " and (ec_forder.forder_flag=" . $forder_flag . ")";
                $this->assign('forder_flag', $forder_flag);
            }

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
        $count = M("forder")->where($where)
                ->join("LEFT JOIN ec_agcy ON ec_agcy.agcy_id=ec_forder.agcy_id")
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
        $forderlist = M('forder')
                ->join("LEFT JOIN ec_agcy ON ec_agcy.agcy_id=ec_forder.agcy_id")
                ->where($where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order("ec_forder.forder_addtime desc")
                ->select();

        foreach ($forderlist AS $k => $v) {
            $forderlist[$k]['forder_addtime'] = date("Y-m-d H:i:s", $v['forder_addtime']);
        }
        $this->assign('pageshow', $pageshow);
        $this->assign('forderlist', $forderlist);
        $this->display();
    }

    //详情信息
    public function orderdetails() {
        $forder_id = I("get.forder_id"); //ID
        $forderlist = M("forderlist")
                ->join("LEFT JOIN ec_fgoods ON ec_fgoods.fgoods_id=ec_forderlist.fgoods_id")
                ->join("LEFT JOIN ec_factory ON ec_factory.factory_id=ec_fgoods.factory_id")
                ->where("ec_forderlist.forder_id='" . $forder_id . "'")
                ->select();
        $forderinfo = M("forder")
                        ->join("LEFT JOIN ec_agcy ON ec_agcy.agcy_id=ec_forder.agcy_id")->find($forder_id);
        $forderinfo['forder_addtime'] = date("Y-m-d H:i:s", $forderinfo['forder_addtime']);
        $this->assign('forderinfo', $forderinfo);
        $this->assign('forderlist', $forderlist);
        $this->display();
    }

}
