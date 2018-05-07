<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class AmoneyController extends Controller {

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

    //农资贷款列表
    public function index() {
        $where = " 1 ";     
        $model = M('agcymoney');
        // 查询满足要求的总记录数
        $count = $model
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
                ->where($where)
                ->order("id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select(); 
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow);  
        $this->display();
    }

    //创建金额
    public function add() {
        if (IS_POST) {
            $data = I("info"); 
            $flag = M("agcymoney")->add($data);
            if ($flag) {
                jump('添加成功！', U('amoney/index'));
            } else {
                jump('添加失败！', U('amoney/index'));
            }
        } else { 
            $this->display();
        }
    }

    //编辑
    public function edit() {
        $id = I("get.id");
        if (IS_POST) {
            $data = I("info");
            M("agcymoney")->where("id=" . $id)->save($data);
            jump('编辑成功！', U('amoney/index'));
        } else {
            $agcymoney = M("agcymoney")->find($id); 
            $this->assign('agcymoney', $agcymoney); 
            $this->display();
        }
    }

            
    //删除图书
    public function delete() {
        $id = I("id");
        $flag = M("agcymoney")->where("id=" . $id)->delete();
        if ($flag) {
            jump('删除成功！', U('amoney/index'));
        } else {
            jump('删除失败！', U('amoney/index'));
        }
    } 
   
}
