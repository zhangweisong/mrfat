<?php

namespace Admin\Controller;

use Think\Controller;

class AgcypopinfoController extends Controller {

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

    //经销商提现申请列表
    public function index() {
        $where = "1 ";
        $info = I("info");
        if ($info) {
            @extract($info);
            //按负责人搜索
            if ($key) {
                $key = urldecode(trim($key));
                $where .= " and (ec_agcy.agcy_name like '%" . $key . "%' 
					or ec_admininfo.realname like '%" . $key . "%'
				) ";
                $this->assign('keys', $key);
            }
            //按状态搜索
            if ($state) {
                $state = urldecode(trim($state));
                $where .= " and ec_agcypopinfo.agcy_pop_flag like '%" . $state . "%' ";
                $this->assign('states', $state);
            }
            //按添加时间搜索
            if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_agcypopinfo.agcy_pop_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_agcypopinfo.agcy_pop_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
        }
        $count = M("agcypopinfo")
                ->where($where)
                ->join('left join ec_agcy on ec_agcypopinfo.agcy_id=ec_agcy.agcy_id')
                ->join('left join ec_admininfo on ec_agcypopinfo.agcy_pop_check_person=ec_admininfo.admin_id')
                ->field('ec_agcypopinfo.*,ec_agcy.agcy_name,ec_admininfo.realname')
                ->count();

        $Page = new \Think\Page($count, 15);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info[' . $key . ']'] = urlencode($val);
            }
        }
        $pageshow = $Page->adminshow();
        $agcypopinfo = M('agcypopinfo')
                ->where($where)
                ->join('left join ec_agcy on ec_agcypopinfo.agcy_id=ec_agcy.agcy_id')
                ->join('left join ec_admininfo on ec_agcypopinfo.agcy_pop_check_person=ec_admininfo.admin_id')
                ->field('ec_agcypopinfo.*,ec_agcy.agcy_name,ec_admininfo.realname')
                ->order("ec_agcypopinfo.agcy_pop_id desc")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        //状态
        $state = D('agcypopinfo')->state();
        $this->assign('pageshow', $pageshow);
        $this->assign('agcypopinfo', $agcypopinfo);
        $this->assign('state', $state);
        $this->display();
    }

    //详情信息
    public function info() {
        $agcy_pop_id = I("get.agcy_pop_id");
        $agcypopinfo = M('agcypopinfo')
                ->where("ec_agcypopinfo.agcy_pop_id='" . $agcy_pop_id . "'")
                ->join('left join ec_agcy on ec_agcypopinfo.agcy_id=ec_agcy.agcy_id')
                ->join('left join ec_admininfo on ec_agcypopinfo.agcy_pop_check_person=ec_admininfo.admin_id')
                ->find();
        $state = D('agcypopinfo')->state();
        $this->assign('agcypopinfo', $agcypopinfo);
        $this->assign('state', $state);
        $this->display();
    }

    //通过
    public function adopt() {
        $agcy_pop_id = I("agcy_pop_id");
        $res = M('agcypopinfo')
                ->join('left join ec_agcy on ec_agcypopinfo.agcy_id=ec_agcy.agcy_id')
                ->where("ec_agcypopinfo.agcy_pop_id='" . $agcy_pop_id . "'")
                ->find();
        $this->assign('res', $res);
        $this->assign('agcy_pop_id', $agcy_pop_id);
        $this->display();
    }

    //驳回
    public function refuse() {
        $agcy_pop_id = I("agcy_pop_id");
        $res = M('agcypopinfo')
                ->join('left join ec_agcy on ec_agcypopinfo.agcy_id=ec_agcy.agcy_id')
                ->where("ec_agcypopinfo.agcy_pop_id='" . $agcy_pop_id . "'")
                ->find();
        $this->assign('res', $res);
        $this->assign('agcy_pop_id', $agcy_pop_id);
        $this->display();
    }

    //通过提现申请
    public function adopted() {
        $admin_id = session('admin.admin_id');
        $agcy_pop_id = I('post.agcy_pop_id');
        $res = M('agcypopinfo')->where('agcy_pop_id = ' . $agcy_pop_id)->find();
        $remark = I('post.agcy_pop_check_remark');
        $data['agcy_pop_flag'] = 2;
        $data['agcy_pop_check_remark'] = $remark;
        $data['agcy_pop_check_time'] = time();
        $data['agcy_pop_check_person'] = $admin_id;
        $data['agcy_pop_fact'] = abs($res['agcy_pop_money'] - $res['agcy_pop_routine']);
        $flag = M('agcypopinfo')->where('agcy_pop_id = ' . $agcy_pop_id)->save($data); 
        if ($flag) {
			//增加消息通知
			$message= new \Admin\Model\MessageModel();
			$message->getmessage_($res['agcy_id'],2,"经销商提现通知","您的提现申请已通过"); 
            $this->home_success('通过提现申请成功！', U('agcypopinfo/index'), 3);
        } else { 
            $this->home_error('通过提现申请失败！', U('agcypopinfo/index'), 3);
        }
    }

    public function refused() {
        $admin_id = session('admin.admin_id');
        $agcy_pop_id = I('post.agcy_pop_id'); 
        $remark = I('post.agcy_pop_check_remark');
        $data['agcy_pop_flag'] = 3;
        $data['agcy_pop_check_remark'] = $remark;
        $data['agcy_pop_check_time'] = time();
        $data['agcy_pop_check_person'] = $admin_id;
        $flag = M('agcypopinfo')->where('agcy_pop_id = ' . $agcy_pop_id)->save($data);
        //增加消息通知
        $pop = M('agcypopinfo')->where('agcy_pop_id = ' . $agcy_pop_id)->find();
        //驳回成功时增加商户余额
        $falg1 = M('agcy')->where('agcy_id=' . $pop['agcy_id'])->setInc('agcy_money', abs($pop['agcy_pop_money'])); 
        if ($flag && $falg1) {
			//增加消息通知
			$message= new \Admin\Model\MessageModel();
			$message->getmessage_($pop['agcy_id'],2,"经销商提现通知","您的提现申请已驳回"); 
            $this->home_success('已驳回提现申请！', U('agcypopinfo/index'), 3);
        } else {
            $this->home_error('驳回提现申请失败！', U('agcypopinfo/index'), 3);
        }
    }

}
