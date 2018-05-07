<?php

/*
 * 厂家提现
 * 张伟松
 * 2018年3月2日09:15:11
 */

namespace Admin\Controller;

use Think\Controller;

class FwithdrawController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录状态
//        if (!D('Admin')->islogin()) {//未登录
//            jump('您尚未登录，请先登录！', U('login/login'));
//        }
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
            if ($factory_name) {
                $factory_name = urldecode(trim($factory_name));
                $where .= " and (ec_factory.factory_name like '%" . $factory_name . "%')";
                $this->assign('factory_name', $factory_name);
            }
            if ($status) {
                $info['status'] = urldecode(trim($info['status']));
                $where .= " and ec_fwithdraw.fwithdraw_flag = " . $info['status'];
                $this->assign('status_', $info['status']);
            }
            if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_fwithdraw.fwithdraw_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_fwithdraw.fwithdraw_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
        }
        $count = M("fwithdraw")->where($where)
                ->join("LEFT JOIN ec_factory ON ec_factory.factory_id=ec_fwithdraw.factory_id")
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
        $withdrawlist = M('fwithdraw')
                ->join("LEFT JOIN ec_factory ON ec_factory.factory_id=ec_fwithdraw.factory_id")
                ->where($where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order("ec_fwithdraw.fwithdraw_addtime desc")
                ->select();
        foreach ($withdrawlist AS $k => $v) {
            $withdrawlist[$k]['fwithdraw_addtime'] = date("Y-m-d H:i:s", $v['fwithdraw_addtime']);
        }
        $this->assign('pageshow', $pageshow);
        $this->assign('withdrawlist', $withdrawlist);
		//手续费
		$pop = M('setinfo')->where("set_key='".factory_change."'")->find();
		$this->assign('pop',$pop);
        $this->assign('status', D('Agcypopinfo')->state());
        $this->display();
    }

    //通过
    public function adopt() {
        $agcy_pop_id = I("agcy_pop_id");
        $res = M('fwithdraw')
                ->join("LEFT JOIN ec_factory ON ec_factory.factory_id=ec_fwithdraw.factory_id")
                ->where("ec_fwithdraw.fwithdraw_id='" . $agcy_pop_id . "'")
                ->find();
        $this->assign('res', $res);
        $this->assign('fwithdraw_id', $agcy_pop_id);
        $this->display();
    }

    //驳回
    public function refuse() {
        $agcy_pop_id = I("agcy_pop_id");
        $res = M('fwithdraw')
                ->join("LEFT JOIN ec_factory ON ec_factory.factory_id=ec_fwithdraw.factory_id")
                ->where("ec_fwithdraw.fwithdraw_id='" . $agcy_pop_id . "'")
                ->find();
        $this->assign('res', $res);
        $this->assign('fwithdraw_id', $agcy_pop_id);
        $this->display();
    }

    //通过提现申请
    public function adopted() {
        $admin_id = session('admin.admin_id');
        $fwithdraw_id = I('post.fwithdraw_id'); //  
        $data['fwithdraw_flag'] = 2;
        $data['fwithdraw_flagstr'] = "通过";
        $data['fwithdraw_bank_remark'] = I('post.fwithdraw_bank_remark');
        $data['fwithdraw_bank_checkperson'] = $admin_id;
        $data['fwithdraw_bank_checktime'] = time();
        if (M('fwithdraw')->where('fwithdraw_id = ' . $fwithdraw_id)->save($data)) {
            //增加消息通知 1 农户 2经销商，3专家 4工厂
            $message = new \Admin\Model\MessageModel();
            $order = M('fwithdraw')->find($fwithdraw_id);
            $message->getmessage_($order['factory_id'], 4, '厂家申请提现通知', "您的提现申请已经通过");
            $this->home_success('通过提现申请成功！', U('fwithdraw/index'), 3);
        } else {
            $this->home_error('通过提现申请失败！', U('fwithdraw/index'), 3);
        }
    }

    public function refused() {
        $admin_id = session('admin.admin_id');
        $fwithdraw_id = I('post.fwithdraw_id'); //   
        $data['fwithdraw_flag'] = 3;
        $data['fwithdraw_flagstr'] = "驳回";
        $data['fwithdraw_bank_remark'] = I('post.fwithdraw_bank_remark');
        $data['fwithdraw_bank_checkperson'] = $admin_id;
        $data['fwithdraw_bank_checktime'] = time();
        if (M('fwithdraw')->where('fwithdraw_id = ' . $fwithdraw_id)->save($data)) {
            //驳回成功时增加商户余额
            $pop = M('fwithdraw')->where('fwithdraw_id = ' . $fwithdraw_id)->find();
            M('factory')->where('factory_id=' . $pop['factory_id'])->setInc('factory_money', abs($pop['fwithdraw_amount']));
            
            //增加消息通知 1 农户 2经销商，3专家 4工厂
            $message = new \Admin\Model\MessageModel(); 
            $message->getmessage_($pop['factory_id'], 4, '厂家申请提现通知', "您的提现申请被驳回");
            $this->home_success('已驳回提现申请！', U('fwithdraw/index'), 3);
        } else {
            $this->home_error('驳回提现申请失败！', U('fwithdraw/index'), 3);
        }
    }

    //详情信息
    public function info() {
        $fwithdraw_id = I("get.agcy_pop_id");
        $fwithdrawinfo = M('fwithdraw')
                ->where("ec_fwithdraw.fwithdraw_id='" . $fwithdraw_id . "'")
                ->join("LEFT JOIN ec_factory ON ec_factory.factory_id=ec_fwithdraw.factory_id")
                ->join('left join ec_admininfo on ec_fwithdraw.fwithdraw_bank_checkperson=ec_admininfo.admin_id')
                ->find();
        if($fwithdrawinfo['fwithdraw_addtime']){
            $fwithdrawinfo['fwithdraw_addtime'] = date("Y-m-d H:i:s", $fwithdrawinfo['fwithdraw_addtime']);
        }    
        if($fwithdrawinfo['fwithdraw_bank_checktime']){
            $fwithdrawinfo['fwithdraw_bank_checktime'] = date("Y-m-d H:i:s", $fwithdrawinfo['fwithdraw_bank_checktime']);
        }
        
        $this->assign('fwithdrawinfo', $fwithdrawinfo);
		//手续费
		$pop = M('setinfo')->where("set_key='".factory_change."'")->find();
		$this->assign('pop',$pop);
        $this->display();
    }

}
