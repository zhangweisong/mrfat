<?php

namespace Admin\Controller;

use Think\Controller;

class FactorystaController extends Controller {

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

    //注册用户分析
    public function index() {
        echo '暂无操作';
    }

    //注册用户统计
    public function factory() {
        $where = "1 ";
        $info = I("info");
        if ($info) { 
            @extract($info);
            //区域
            if ($p_id) { //省 
                $where .= "and ec_factory.p_id = '" . $p_id . "' ";
                $cityList = M('city')->where('p_id = ' . $p_id)->select();
                $this->assign('city', $cityList);
                $this->assign('p_id', $p_id);
            }
            if ($c_id) {
                $where .= " and  ec_factory.c_id = '" . $c_id . "' ";
                $areaList = M('area')->where('c_id = ' . $c_id)->select();
                $this->assign('area', $areaList);
                $this->assign('c_id', $c_id);
            }
            if ($a_id) {
                $where .= " and ec_factory.a_id = '" . $info['area_id'] . "' ";
                $this->assign('a_id', $a_id);
            }
            if ($starttime) {
                $this->assign('starttime', $starttime);
            }
            if ($endtime) {
                $this->assign('endtime', $endtime); //当前时间
            }
        }
        if (!$starttime) {
            $starttime = date("Y-m-d", strtotime("-15 day"));
            $this->assign('starttime', $starttime);
        }
        if (!$endtime) {
            $endtime = date("Y-m-d", time());
            $this->assign('endtime', $endtime);
        }
        $this->assign('title', "新增厂家统计" . $starttime . "至" . $endtime);

        $daylist = $this->get_daylist($starttime, $endtime); //开始时间到结束时间的日期
        $infolist = array();
        foreach ($daylist as $k => $v) {
            $where_ = $where;
            $where_ .= " and factory_addtime>=" . strtotime($v . " 00:00:00") . " ";
            $where_ .= " and factory_addtime<=" . strtotime($v . " 23:59:59") . " ";
            $infolist[$k]['nums'] = M("factory")->where($where_)->count();
            $infolist[$k]['dayinfo'] = $v;
        }
        $province = M('province')->select();
        $this->assign('province', $province);
        $this->assign('infolist', $infolist);
        $this->display();
    }

    //用户返佣统计
    public function agcyorder() {
        $where = "1 ";
        $info = I("info");
        if ($info) {
            @extract($info);  
            if ($starttime) {
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $this->assign('endtime', $info['endtime']);
            }
        }
        if (!$starttime) {
            $starttime = date("Y-m-d", strtotime("-15 day"));
            $this->assign('starttime', $starttime);
        }
        if (!$endtime) {
            $endtime = date("Y-m-d", time());
            $this->assign('endtime', $endtime);
        }
        $this->assign('title', "经销商订单统计" . $starttime . "至" . $endtime);  
        $daylist = $this->get_daylist($starttime, $endtime);
        $infolist = array();
        foreach ($daylist as $k => $v) {
            $where_ = $where;
            $where_ .= " and ec_forder.forder_tptime>=" . strtotime($v . " 00:00:00") . " "; //按照发货时间计算
            $where_ .= " and ec_forder.forder_tptime<=" . strtotime($v . " 23:59:59") . " ";
            $money_ = M('forder')->where($where_)
                    //用户
                   // ->join("left join ec_user on ec_income.user_id=ec_user.user_id")
                    ->sum("ec_forder.forder_total");
            $infolist[$k]['dayinfo'] = $v;
            $infolist[$k]['nums'] = M("forder")->where($where_)->count(); 
            $infolist[$k]['money'] = round($money_, 2);
        }
        $province = M('province')->select();
        $this->assign('province', $province);
        $this->assign('infolist', $infolist); 
        $this->display();
    }
 
    ///////////////////////////////////////////////////////////////////////
    //获取两个日期段内所有日期
    public function get_daylist($startdate, $enddate) {
        $stimestamp = strtotime($startdate);
        $etimestamp = strtotime($enddate);
        // 计算日期段内有多少天
        $days = ($etimestamp - $stimestamp) / 86400 + 1;
        // 保存每天日期
        $date = array();
        for ($i = 0; $i < $days; $i++) {
            $date[] = date('Y-m-d', $stimestamp + (86400 * $i));
        }
        return $date;
    }

}
