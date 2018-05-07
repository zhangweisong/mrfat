<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class Videl2Controller extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();  
        //判断登录状态
        if (!D('admininfo')->islogin()) { //未登录
            jump('您尚未登录，请先登录！', U('login/login'));
        }
    }

    //空方法，防止报错
    public function _empty() {
        $this->index();
    }

    //分类列表 类 分类
    public function index() {
        $classtype = 2;
        $where = "ec_slcropskill.sk_one=$classtype";  //1 
        $info = I("info");
        if ($info) {
            @extract($info);
            //search columns
            if ($sk_title) {
                $sk_title = urldecode(trim($sk_title));
                //用户名 或者电话
                $where .= " and ec_slcropskill.sk_title like '%$sk_title%'";
                $this->assign('sk_title', $sk_title);
            }
            if ($sk_two) {
                $where .= " and ec_slcropskill.sk_two =$sk_two";
                $this->assign('sk_two', $sk_two);
                
                $class2 = M('slskillclass')->where("ec_slskillclass.skc_fid=$sk_two")->select(); //一级
                $this->assign('class2', $class2);
            }
            if ($sk_three) {
                $where .= " and ec_slcropskill.sk_three =$sk_three";
                $this->assign('sk_three', $sk_three);
            }  
            if ($starttime) {
                $where .= " and ec_slcropskill.sk_addtime >=" . strtotime($starttime . "00:00:00") . " ";
                $this->assign('starttime', $starttime);
            }
            if ($endtime) {
                $where .= " and ec_slcropskill.sk_addtime <=" . strtotime($endtime . "23:59:59") . " ";
                $this->assign('endtime', $endtime);
            }
        }
        // 查询满足要求的总记录数
        $count = M('slcropskill')
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
        $listinfo = M('slcropskill')
                ->field("ec_slcropskill.*,a.skc_title aname,b.skc_title bname")
                ->join("LEFT JOIN ec_slskillclass a ON a.skc_id=ec_slcropskill.sk_two")//一级分类
                ->join("LEFT JOIN ec_slskillclass b ON b.skc_id=ec_slcropskill.sk_three")//er级分类
                ->where($where)
                ->order("ec_slcropskill.sk_addtime DESC") //按添加时间倒叙
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        foreach($listinfo AS $k=>$v){
            $listinfo[$k]['sk_addtime'] = date('Y-m-d H:i:s',$v['sk_addtime']);
        }
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow); 
        $class1 = M('slskillclass')->where("ec_slskillclass.skc_fid=$classtype")->select(); //一级
        $this->assign('class1', $class1);
        $this->display(); 
    } 

	//添加 
    public function add() {
        $classtype = 2;
        if (IS_POST) { 
            $data = I("info"); 
            $data['sk_one'] = $classtype; 
            $data['sk_addtime'] = time(); 
            $data['sk_isable'] = 1; 
            if (M("slcropskill")->add($data)) {
                jump('添加成功！', U('index'));
            } else {
                jump('添加失败！', U('index'));
            }
        } else { 
            $class1 = M('slskillclass')->where("ec_slskillclass.skc_fid=$classtype")->select(); //一级
            $this->assign('class1', $class1);
            $this->display();
        }
    } 
	
    //编辑图书分类
    public function edit() {
        $sk_id = I("get.sk_id"); 
        if (IS_POST) {
            $data = I("info");  
            M("slcropskill")->where("sk_id=" . $sk_id)->save($data);
            jump('编辑成功！', U('index'));
        } else { 
			$current = M("slcropskill")->find($sk_id);  
			$this->assign('current', $current); 
            $class1 = M('slskillclass')->where("ec_slskillclass.skc_fid=".$current['sk_one'])->select(); //一级
            $this->assign('class1', $class1);
            $class2 = M('slskillclass')->where("ec_slskillclass.skc_fid=".$current['sk_two'])->select(); //一级
            $this->assign('class2', $class2);
            $this->display();
        }
    } 

    

    //删除图书分类
    public function delete() {
        $sk_id = I("get.sk_id");
        if (M("slcropskill")->delete($sk_id)) {
            jump('删除成功', U('index'));
        }
    }   
     
    function get_erji($yiji_id) {
        if (IS_AJAX) {
            if (!I("get.yiji_id"))
                return;
            $this->ajaxReturn(M("slskillclass")->where("skc_fid=" . I("get.yiji_id"))->select());
        } else {
            if (!$p_id)
                return;
            return M("slcropskill")->where("skc_fid=" . $yiji_id)->select();
        }
    }
    
    
}
