<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class BookclassifyController extends Controller {

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

    //图书分类列表
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
        $where .= " AND classify_fatherid=" . $father_id; 
        $info = I("info");
        if ($info) {// 搜索条件  
            @extract($info);
            if ($classify_name_s) {
                $classify_name_s = urldecode(trim($info['classify_name_s']));
                $where .= " and classify_name LIKE '%" . $classify_name_s . "%'";
                $this->assign('classify_name_s', $classify_name_s);
            }
			if ($father_id_s) {
                $where .= " and classify_fatherid='" . $father_id_s . "'";
                $this->assign('father_id_s', $father_id_s);
            }
            if($classify_isable){
				$info['classify_isable']=urldecode(trim($info['classify_isable']));
				$where.=" and classify_isable ='".$info['classify_isable']."'";  
				$this->assign('classify_', $info['classify_isable']);
			}
        } 
        // 查询满足要求的总记录数
        $count = M('bookclassify')
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
        $listinfo = M('bookclassify')
                ->where($where)
                ->order("classify_id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        if ($father_id == 0) { 
            foreach ($listinfo AS $k => $v) {
                $listinfo[$k]['child_count'] = M("bookclassify")->where("classify_fatherid=" . $v["classify_id"])->count();
            }
        } else {
            $father = M('bookclassify')->find($father_id);
            $father_name = $father['classify_name'];
            foreach ($listinfo AS $k => $v) {
                $listinfo[$k]['child_count'] = M("book")->where("classify_id=" . $v["classify_id"]." AND book_isable=1")->count(); 
            }
        }  
        
		$this->assign('classify_isable',D("Bookorder")->classify_isable());//状态
        $father = M("bookclassify")->where("classify_fatherid=0")->select();
        $this->assign('father', $father);
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow);
        $this->assign('father_name', $father_name);
        $this->display();
    }

	//创建图书分类
    public function add() { 
        $father_id = I("get.father_id") ? I("get.father_id") : 0;
        $this->assign('father_id', $father_id);
        if (IS_POST) {
            $data = I("info"); 
            $flag = M("bookclassify")->add($data);
            if ($flag) {
                jump('添加图书分类成功！', U('bookclassify/index'));
            } else {
                jump('添加图书分类失败！', U('bookclassify/index'));
            } 
        } else {
            $where = "classify_fatherid=0";
            $father = M("bookclassify")->where($where)->select();
            $this->assign('father', $father);
            $this->display();
        }
    }
	
    //编辑图书分类
    public function edit() {
        $classify_id = I("get.classify_id");  
        if (IS_POST) {
            $data = I("info"); 
            M("bookclassify")->where("classify_id=" . $classify_id)->save($data);
            jump('编辑图书分类成功！', U('bookclassify/index', array('father_id' => $classify['classify_fatherid']))); 
        } else { 
			$classify = M("bookclassify")->find($classify_id); 
			$where = "classify_fatherid=0";
			$father = M("bookclassify")->where($where)->select();  
            $this->assign('father', $father); 
            $this->assign('classify', $classify); 
			$this->assign('classify_id', $classify_id); 
            $this->display();
        }
    }

    

    //删除图书分类
    public function delete() {
        //删除的时候首先判断，如果是顶级分类，检查下面是否有次级分类
        //如果是次级分类，检查下面有没有图书 
        $classify_id = I("classify_id");
        $classify = M("bookclassify")->find($classify_id);
        if ($classify['classify_fatherid'] == 0) {
            $count = M("bookclassify")->where("classify_fatherid=" . $classify_id)->count();
            if ($count > 0) {
                jump('底层有分类，不能删除！', U('bookclassify/index'));
            } else {
                M("bookclassify")->delete($classify_id);
                jump('图书分类删除成功', U('bookclassify/index'));
            }
        } else {
            $count = M("book")->where("classify_id='" . $classify_id."' and book_isable=1")->count();
            if ($count > 0) {
                jump('该类目下有图书，不能删除！', U('bookclassify/index', array('father_id' => $classify['classify_fatherid'])));
            } else {
                M("bookclassify")->delete($classify_id);
                jump('图书分类删除成功', U('bookclassify/index', array('father_id' => $classify['classify_fatherid'])));
            }
        }
    }
	
	//判断一级分类的存在性
	public function names(){
		$classify_name = urldecode(trim(I("classify_name")));
		$father_id = urldecode(trim(I("father_id")));
		if($father_id){
			$count = M("bookclassify")->where("(classify_name ='".$classify_name."' and classify_fatherid=".$father_id." ) or (classify_name ='".$classify_name."' and classify_id=".$father_id.")")->count(); 
		}else{
			$count = M("bookclassify")->where("classify_name ='".$classify_name."' and classify_fatherid=".$father_id." ")->count();
		}   
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
		$classify_id = I("classify_id");  
		$classify_fatherid = I("classify_fatherid"); 
		if($classify_fatherid){//（二级分类名称不能与父级名称重复）
            $count = M("bookclassify")->where("(classify_id!='".$classify_id."' and classify_name ='".$classify_name."' and classify_fatherid=".$classify_fatherid." )  or (classify_name ='".$classify_name."' and classify_id=".$classify_fatherid.")")->count(); 
        }else{
            $count = M("bookclassify")->where("classify_id!='".$classify_id."' and classify_name ='".$classify_name."' and classify_fatherid =".$classify_fatherid."")->count(); 
        }  
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
//ajax处理状态
    public function ajaxable_on(){
        $id=I("get.id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "classify_isable"://on
                M("bookclassify")->where('classify_id='.$id)->save(array("classify_isable"=>1));
                break;
        }
        $this->ajaxReturn(array("flag"=>"1"));
    }

    public function ajaxable_off(){
        $id=I("get.id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "classify_isable"://off
                M("bookclassify")->where('classify_id='.$id)->save(array("classify_isable"=>2));
                break;
        }
        $this->ajaxReturn(array("flag"=>"1"));
    }
}
