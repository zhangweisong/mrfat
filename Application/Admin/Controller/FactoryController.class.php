<?php

/*
 * 厂家管理
 * 张伟松
 * 2018年3月1日09:38:31 
 */

namespace Admin\Controller;

use Think\Controller;

class FactoryController extends Controller {

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
            if ($factory_name) {
                $factory_name = urldecode(trim($factory_name));
                $where .= " and (ec_factory.factory_name like '%" . $factory_name . "%'
							or ec_factory.factory_man like '%" . $factory_name . "%'
							or ec_factory.factory_tel like '%" . $factory_name . "%'
				)";
                $this->assign('factory_name', $factory_name);
            }
            if ($p_id) {
                $where .= " and ec_factory.p_id=" . $p_id;
                $this->assign('p_id', $p_id);
                $city = $this->cityofprovince($p_id);
                $this->assign('city', $city);
            }
            if ($c_id) {
                $where .= " and ec_factory.c_id=" . $c_id;
                $this->assign('c_id', $c_id);
                $area = $this->areaofcity($c_id);
                $this->assign('area', $area);
            }
            if ($a_id) {
                $where .= " and ec_factory.a_id=" . $a_id;
                $this->assign('a_id', $a_id);
            }
            if ($classify_isable) {
                $info['classify_isable'] = urldecode(trim($info['classify_isable']));
                $where .= " and ec_factory.factory_state ='" . intval($info['classify_isable']) . "' ";
                $this->assign('classify_', $info['classify_isable']);
            } 
            if ($factory_state) {
                $info['factory_state'] = urldecode(trim($info['factory_state']));
                $where .= " and ec_factory.factory_state =" . $info['factory_state'] . " ";
                $this->assign('factory_state', $info['factory_state']);
            }
        }
        $count = M("Factory")->where($where)
                        ->join("LEFT JOIN ec_province ON ec_province.p_id=ec_factory.p_id")
                        ->join("LEFT JOIN ec_city ON ec_city.c_id=ec_factory.c_id")
                        ->join("LEFT JOIN ec_area ON ec_area.a_id=ec_factory.a_id")->count();
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
        $factorylist = M('Factory')
                ->field("ec_factory.*,"
                        . "ec_province.p_name,"
                        . "ec_city.c_name,"
                        . "ec_area.a_name")
                ->join("LEFT JOIN ec_province ON ec_province.p_id=ec_factory.p_id")
                ->join("LEFT JOIN ec_city ON ec_city.c_id=ec_factory.c_id")
                ->join("LEFT JOIN ec_area ON ec_area.a_id=ec_factory.a_id")
                ->where($where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order("ec_factory.factory_id desc")
                ->select();
        $this->assign('classify_isable', D("Bookorder")->isable()); //状态 
        $this->assign('pageshow', $pageshow);
        $this->assign('factorylist', $factorylist);
        $this->assign('cpc_isable', D("slcropclass")->cpc_isable());
        $this->assign("province", $this->allprovince());
        $this->display();
    }

    //添加店铺
    public function addfactory() {
        if (IS_POST) {
            $info = I("info");
            $info['factory_pwd'] = md5('a123456');
            $info['factory_addtime'] = time();
            if ($this->usrname_exist($info['factory_usrnm'])) {
                jump('该厂家名称已经存在，请重新录入！', U('factory/addfactory'));
                exit();
            }
            if (M("factory")->add($info)) {
                jump('厂家添加成功！', U('factory/index'));
            } else {
                jump('厂家添加失败！', U('factory/index'));
            }
        } else {
            $province = $this->allprovince(); //所有省份
            $this->assign('province', $province);
            $this->display();
        }
    }

    //编辑店铺
    public function editfactory() {
        $factory_id = I("get.factory_id");
        if (IS_POST) {
            $info = I("info");
            M("factory")->data($info)->where("factory_id=" . $factory_id)->save();
            jump('店铺编辑成功！', U('factory/index'));
        } else {
            $factory = M("factory")->find($factory_id);
            $province = $this->allprovince(); //所有省份
            $city = $this->cityofprovince($factory['p_id']); //所有省份 
            $area = $this->areaofcity($factory['c_id']); //所有省份
            $this->assign('province', $province);
            $this->assign('city', $city);
            $this->assign('area', $area);
            $this->assign('factory', $factory);
            $this->display();
        }
    }

    //店铺列表重置密码
    public function chongzhi() {
        $factory_id = I("get.factory_id");
        $flag = M("Shop")->where("factory_id=" . $factory_id)->save(array("password" => MD5('123456')));
        if ($flag) {
            jump('重置密码成功！', U('factory/factorylist'));
        } else {
            jump('重置密码失败！', U('factory/factorylist'));
        }
    }

//全部省份
    function allprovince() {
        $data = M("province")->select();
        if (IS_AJAX) {
            $this->ajaxReturn($data);
        } else {
            return $data;
        }
    }

//省份地下的城市
    function cityofprovince($p_id) {
        if (IS_AJAX) {
            if (!I("get.p_id"))
                return;
            $this->ajaxReturn(M("city")->where("p_id=" . I("get.p_id"))->select());
        } else {
            if (!$p_id)
                return;
            return M("city")->where("p_id=" . $p_id)->select();
        }
    }

//城市地下的县区
    function areaofcity($c_id) {
        if (IS_AJAX) {
            if (!I("get.c_id"))
                return;
            $this->ajaxReturn(M("area")->where("c_id=" . I("get.c_id"))->select());
        } else {
            if (!$c_id)
                return;
            return M("area")->where("c_id=" . $c_id)->select();
        }
    }

    //厂家关闭->开启
    public function ajax_on() {
        M("factory")->where('factory_id=' . I("get.factory_id"))->save(array("factory_state" => 1));
    }

    //厂家开启->关闭
    public function ajax_off() {
        M("factory")->where('factory_id=' . I("get.factory_id"))->save(array("factory_state" => 0));
    }

    //重置密码
    public function repassword() {
        $factory_id = I("get.factory_id");
        $info['factory_pwd'] = md5('a123456');
        M('factory')->where('factory_id=' . $factory_id)->save($info);
        jump('密码重置成功！', U('factory/index'));
    }

    //检测人员信息
    public function checkusername() {
        $flag = $this->usrname_exist(I("username"));
        if ($flag) {
            $this->ajaxReturn(array("status" => 0, "msg" => "用户名已存在！"));
        }
        $this->ajaxReturn(array("status" => 1, "msg" => "ok"));
    }

    public function usrname_exist($username) {
        $count = M("factory")->where("factory_usrnm='" . $username . "'")->count();
        if ($count) {
            return true;
        } else {
            return false;
        }
    }
	
	 

    //检测shoujihao 
    public function checkfactory_tel() {
        $factory_tel = I("get.factory_tel"); //检测shoujihao

        $where = " factory_tel= '" . $factory_tel . "' ";
        $listinfo = M('factory')
                ->where($where)
                ->find();
        if (count($listinfo) < 1) {
            $this->ajaxReturn(array("status" => 0));
        }
        $this->ajaxReturn(array("status" => 2));
    }

    //检测shoujihao 
    public function checkfactory_tel_2() {
        $factory_tel = I("get.factory_tel"); //检测手机号  跟自己不重复
        $factory_id = I("get.factory_id");
        $where = " factory_tel= '" . $factory_tel . "' AND factory_id<>" . $factory_id;
        $listinfo = M('factory')
                ->where($where)
                ->find();
        if (count($listinfo) < 1) {
            $this->ajaxReturn(array("status" => 0));
        }
        $this->ajaxReturn(array("status" => 2));
    }

    //详情信息
    public function details() {
        $factory_id = I("get.factory_id"); //ID
        $factory = M("factory")
                ->field("ec_factory.*,ec_province.p_name,ec_city.c_name,ec_area.a_name")
                ->join("LEFT JOIN ec_province ON ec_province.p_id=ec_factory.p_id")
                ->join("LEFT JOIN ec_city ON ec_city.c_id=ec_factory.c_id")
                ->join("LEFT JOIN ec_area ON ec_area.a_id=ec_factory.a_id")
                ->where("ec_factory.factory_id='" . $factory_id . "'")
                ->find();
        if ($factory) {
            $temp = explode(',', $factory["productcategory_ids"]);
            $arr = explode('|', $factory["factory_license_images"]);
            foreach ($temp AS $kk => $vv) {
                $temp[$kk] = M("productcategory")->find($vv);
            }
            $factory["productcategories"] = implode(',', i_array_column($temp, 'productcategory_name'));
            $factory["factory_addtime"] = date("Y-m-d", $factory["factory_addtime"]);
        }
        if ($factory["factory_license_images"] != "") {
            $arr = $arr;
        } else {
            $arr = "";
        }
        $arr_ = explode('|', $factory["factory_logo"]);
        if ($factory["factory_logo"] != "") {
            $imageurl = $arr_[0];
        } else {
            $imageurl = "";
        }
        $this->assign('factory', $factory);
        $this->assign('arr', $arr);
        $this->assign('imageurl', $imageurl);
        $this->display();
    }

    //申请变更
    public function exchange() {
        $where = "1 ";
        $info = I("info");
        if ($info) {
            @extract($info);
            //按名称搜索
            if ($factory_name) {
                $factory_name = urldecode(trim($factory_name));
                $where .= " and (ec_factory.factory_name like '%" . $factory_name . "%') ";
                $this->assign('factoryname', $factory_name);
            }
            //按状态搜索
            if ($bank_flag) {
                $info['bank_flag'] = urldecode(trim($info['bank_flag']));
                $where .= " and bank_flag =" . $info['bank_flag'] . " ";
                $this->assign('ec_bank_flag', $info['bank_flag']);
            }
        }
        $count = M("bankset")->where($where)
                ->join("LEFT JOIN ec_factory ON ec_factory.factory_id=ec_bankset.factory_id")
                ->count();
        $Page = new \Think\Page($count, 15);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info[' . $key . ']'] = urlencode($val);
            }
        }

        $pageshow = $Page->adminshow();
        $listinfo = M('bankset')
                ->join("LEFT JOIN ec_factory ON ec_factory.factory_id=ec_bankset.factory_id")
                ->where($where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order("id desc")
                ->field('ec_bankset.*,ec_factory.factory_name')
                ->select();
        $this->assign('pageshow', $pageshow);
        $this->assign('listinfo', $listinfo);
        $this->assign('bank_flag', array('1' => '待审核', '2' => '通过', '3' => '驳回'));
        $this->display();
    }

    //通过申请
    public function adopt() {
        $id = I('get.id');
        //更新商户条件
        $bankset = M('bankset')->where('id = ' . $id)->find();
        $info = array(
            'bank_card' => $bankset['bank_card'],
            'bank_name' => $bankset['bank_name'],
            'bank_mobile' => $bankset['bank_mobile'],
            'idcard' => $bankset['idcard'],
            'bank_username' => $bankset['bank_username']
        );
        //1待审核，2已通过，3驳回'
        $turnFlag = M('bankset')->where('id = ' . $id)->save(array('bank_flag' => '2'));
        if ($turnFlag) {
            //更新商户表个人信息
            $list = M('Shop')->where(array('factory_id' => $bankset['factory_id']))->save($info);
            $msg_title = "申请变更";
            $messages = "您的申请已通过，请注意查看。";
            D("Message")->getmessage_($bankset['factory_id'], $msg_title, $messages);
            jump('已通过申请！', U('factory/exchange'));
        } else {
            jump('操作失败！', U('factory/exchange'));
        }
    }

    //拒绝申请
    public function refuse() {
        $id = I('get.id');
        //1待审核，2已通过，3驳回'
        $turnFlag = M('bankset')->where('id = ' . $id)->save(array('bank_flag' => '3'));
        $bankset = M('bankset')->where('id = ' . $id)->find();
        if ($turnFlag) {
            $msg_title = "申请变更";
            $messages = "您的申请已被驳回，请注意查看。";
            D("Message")->getmessage_($bankset['factory_id'], $msg_title, $messages);
            jump('已拒绝申请！', U('factory/exchange'));
        } else {
            jump('操作失败！', U('factory/exchange'));
        }
    }

}
