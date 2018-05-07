<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class AgcyclassifyController extends Controller {

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

    //经销商商品分类列表
    public function index() {
        /*
         * 分类应该是：
         * 一级分= 父ID是0
         * 
         * 二级分类 == 父ID是一级分类的ID 
         * 
         * 注意删除的时候 二级分类下没有图书，一级分类下没有二级分类
         */

		$father_id = I("get.father_id") ? I("get.father_id") : 0; 
        $this->assign("father_id", $father_id);
        $where = "1";
        $where .= " AND agcy_classify_fatherid=" . $father_id; 
        $info = I("info");
        if ($info) {// 搜索条件  
            @extract($info);
            if ($classify_name_s) {
                $classify_name_s = urldecode(trim($info['classify_name_s']));
                $where .= " and agcy_classify_name LIKE '%" . $classify_name_s . "%'";
                $this->assign('classify_name_s', $classify_name_s);
            }  
        } 
        // 查询满足要求的总记录数
        $count = M('agcyclassify')
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
        $listinfo = M('agcyclassify')
                ->where($where)
                ->order("agcy_classify_id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();  
        if ($father_id == 0) { 
            foreach ($listinfo AS $k => $v) {
                $listinfo[$k]['child_count'] = M("agcyclassify")->where("agcy_classify_fatherid=" . $v["agcy_classify_id"])->count();
            }
        } else {
            $father = M('agcyclassify')->find($agcy_classify_fatherid);
            $father_name = $father['agcy_classify_name'];
            foreach ($listinfo AS $k => $v) {
                $listinfo[$k]['child_count'] = M("agcygoods")->where("agcy_classifytwo=" . $v["agcy_classify_id"]." AND agcy_goods_state=1")->count(); 
            }
        }  
        foreach ($listinfo AS $k => $v) {
            $listinfo[$k]['agcy_classify_remarks'] = str_cut($v['agcy_classify_remarks'], 40);
        }  
		 
        $father = M("agcyclassify")->where("agcy_classify_fatherid=0")->select();
        $this->assign('father', $father);
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow);
        $this->assign('father_name', $father_name);
        $this->display();
    }

	//创建经销商商品分类
    public function add() { 
        $father_id = I("get.father_id") ? I("get.father_id") : 0;
        $this->assign('father_id', $father_id);
        if (IS_POST) {
            $data = I("info"); 
            $flag = M("agcyclassify")->add($data);
            if ($flag) {
                jump('添加经销商商品分类成功！', U('agcyclassify/index'));
            } else {
                jump('添加经销商商品分类失败！', U('agcyclassify/index'));
            } 
        } else {
            $where = "agcy_classify_fatherid=0";
            $father = M("agcyclassify")->where($where)->select();
            $this->assign('father', $father); 
            $this->display();
        }
    }
	
    //编辑经销商商品分类
    public function edit() {
        $agcy_classify_id = I("get.agcy_classify_id");  
        if (IS_POST) {
            $data = I("info"); 
            M("agcyclassify")->where("agcy_classify_id=" . $agcy_classify_id)->save($data);
            jump('编辑经销商商品分类成功！', U('agcyclassify/index', array('father_id' => $classify['agcy_classify_fatherid']))); 
        } else { 
			$classify = M("agcyclassify")->find($agcy_classify_id); 
			$where = "agcy_classify_fatherid=0";
			$father = M("agcyclassify")->where($where)->select();  
            $this->assign('father', $father);
            $this->assign('classify', $classify); 
			$this->assign('agcy_classify_id', $agcy_classify_id); 
            $this->display();
        }
    }

    

    //删除经销商商品分类
    public function delete() {
        //删除的时候首先判断，如果是顶级分类，检查下面是否有次级分类
        //如果是次级分类，检查下面有没有图书 
        $agcy_classify_id = I("agcy_classify_id");
        $classify = M("agcyclassify")->find($agcy_classify_id);
        if ($classify['agcy_classify_fatherid'] == 0) {
            $count = M("agcyclassify")->where("agcy_classify_fatherid=" . $agcy_classify_id)->count();
            if ($count > 0) {
                jump('底层有分类，不能删除！', U('agcyclassify/index'));
            } else {
                M("agcyclassify")->delete($agcy_classify_id);
                jump('经销商商品分类删除成功', U('agcyclassify/index'));
            }
        } else {
            $count = M("agcygoods")->where("agcy_classifytwo='" . $agcy_classify_id."' and agcy_goods_state=1")->count();
            if ($count > 0) {
                jump('该类目下有仙品，不能删除！', U('agcyclassify/index', array('father_id' => $classify['agcy_classify_fatherid'])));
            } else {
                M("agcyclassify")->delete($agcy_classify_id);
                jump('经销商商品分类删除成功', U('agcyclassify/index', array('father_id' => $classify['agcy_classify_fatherid'])));
            }
        }
    }
	
	//判断一级分类的存在性
	public function names(){
		$classify_name = urldecode(trim(I("classify_name")));
		$count = M("agcyclassify")->where("agcy_classify_name ='".$classify_name."'")->count(); 
		if ($count) {
			$response = array(
			'errno' => 0,
			);
		} else {
			$response = array(
			'errno' => -1,
			);
		}
		
		echo json_encode($response);
			
	}
	
	//判断一级分类的存在性修改
	public function name_es(){
		$classify_name = urldecode(trim(I("classify_name")));
		$agcy_classify_id = I("agcy_classify_id");  
		$count = M("agcyclassify")->where("agcy_classify_id!='".$agcy_classify_id."' and agcy_classify_name ='".$classify_name."'")->count(); 
		if ($count) {
			$response = array(
			'errno' => 0,
			);
		} else {
			$response = array(
			'errno' => -1,
			);
		} 
		echo json_encode($response);
			
	}

}
