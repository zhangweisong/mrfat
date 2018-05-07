<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class GoodsclassifyController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录状态
        /*
        if (!D('admin')->islogin()) {//未登录
           jump('您尚未登录，请先登录！', U('login/login'));

        }*/
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
        $where .= " AND f_classify_fatherid=" . $father_id; 
        $info = I("info");
        if ($info) {// 搜索条件  
            @extract($info);
            if ($classify_name_s) {
                $classify_name_s = urldecode(trim($info['classify_name_s']));
                $where .= " and f_classify_name LIKE '%" . $classify_name_s . "%'";
                $this->assign('classify_name_s', $classify_name_s);
            }
            if ($father_id_s) {
                $where .= " and f_classify_fatherid='" . $father_id_s . "'";
                $this->assign('father_id_s', $father_id_s);
            }
			
			if($f_classify_able){
				$info['f_classify_able']=urldecode(trim($info['f_classify_able']));
				$where.=" and f_classify_able ='".$info['f_classify_able']."'";  
				$this->assign('classify_', $info['f_classify_able']);
			}
        } 
        // 查询满足要求的总记录数
        $count = M('fgoodsclassify')
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
        $listinfo = M('fgoodsclassify')
                ->where($where)
                ->order("f_classify_id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        if ($father_id == 0) { 
            foreach ($listinfo AS $k => $v) {
                $listinfo[$k]['child_count'] = M("fgoodsclassify")->where("f_classify_fatherid=" . $v["f_classify_id"])->count();
            }
        } else {
            $father = M('fgoodsclassify')->find($father_id);
            $father_name = $father['f_classify_name'];
            foreach ($listinfo AS $k => $v) {
                $listinfo[$k]['child_count'] = M("fgoods")->where("fgoods_2=" . $v["f_classify_id"]." ")->count(); 
            }
        }  
        foreach ($listinfo AS $k => $v) {
            $listinfo[$k]['f_classify_remarks'] = str_cut($v['f_classify_remarks'], 40);
        }
		$this->assign('f_classify_able',D("Factory")->f_classify_able());//状态
        $father = M("fgoodsclassify")->where("f_classify_fatherid=0")->select();
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
            $flag = M("fgoodsclassify")->add($data);
            if ($flag) {
                jump('添加商品分类成功！', U('Goodsclassify/index'));
            } else {
                jump('添加商品分类失败！', U('Goodsclassify/index'));
            } 
        } else {
            $where = "f_classify_fatherid=0";
            $father = M("fgoodsclassify")->where($where)->select();
            $this->assign('father', $father);
            $this->display();
        }
    }
	
    //编辑图书分类
    public function edit() {
        $f_classify_id = I("get.f_classify_id");  
        if (IS_POST) {
            $data = I("info"); 
            M("fgoodsclassify")->where("f_classify_id=" . $f_classify_id)->save($data);
            jump('编辑商品分类成功！', U('goodsclassify/index', array('father_id' => $classify['f_classify_fatherid']))); 
        } else { 
			$classify = M("fgoodsclassify")->find($f_classify_id); 
			$where = "f_classify_fatherid=0";
			$father = M("fgoodsclassify")->where($where)->select(); 
            $this->assign('father', $father); 
            $this->assign('classify', $classify); 
			$this->assign('f_classify_id', $f_classify_id); 
            $this->display();
        }
    }

    

    //删除图书分类
    public function delete() {
        //删除的时候首先判断，如果是顶级分类，检查下面是否有次级分类
        //如果是次级分类，检查下面有没有图书 
        $f_classify_id = I("f_classify_id");
        $classify = M("fgoodsclassify")->find($f_classify_id);
        if ($classify['f_classify_fatherid'] == 0) {
            $count = M("fgoodsclassify")->where("f_classify_fatherid=" . $f_classify_id)->count();
            if ($count > 0) {
                jump('底层有分类，不能删除！', U('goodsclassify/index'));
            } else {
                M("fgoodsclassify")->delete($f_classify_id);
                jump('商品分类删除成功', U('goodsclassify/index'));
            }
        } else {
            $count = M("fgoods")->where("fgoods_2='" . $f_classify_id."' ")->count();
            if ($count > 0) {
                jump('该类目下有商品，不能删除！', U('goodsclassify/index', array('father_id' => $classify['f_classify_fatherid'])));
            } else {
                M("fgoodsclassify")->delete($f_classify_id);
                jump('商品分类删除成功', U('goodsclassify/index', array('father_id' => $classify['f_classify_fatherid'])));
            }
        }
    }
	
	//判断一级分类的存在性
	public function names(){
		$f_classify_name = urldecode(trim(I("f_classify_name")));
		$f_classify_fatherid = urldecode(trim(I("f_classify_fatherid")));
        if($f_classify_fatherid){//（二级分类名称不能与父级名称重复）
            $count = M("fgoodsclassify")->where("(f_classify_name ='".$f_classify_name."' and f_classify_fatherid=".$f_classify_fatherid." ) or (f_classify_name ='".$f_classify_name."' and f_classify_id=".$f_classify_fatherid.")")->count(); 
        }else{
            $count = M("fgoodsclassify")->where("f_classify_name ='".$f_classify_name."' and f_classify_fatherid=".$f_classify_fatherid." ")->count(); 
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
		$f_classify_name = urldecode(trim(I("f_classify_name")));
		$f_classify_id = I("f_classify_id");  
        $f_classify_fatherid = urldecode(trim(I("f_classify_fatherid")));
        if($f_classify_fatherid){//（二级分类名称不能与父级名称重复）
            $count = M("fgoodsclassify")->where("(f_classify_id!='".$f_classify_id."' and f_classify_name ='".$f_classify_name."' and f_classify_fatherid=".$f_classify_fatherid." )  or (f_classify_name ='".$f_classify_name."' and f_classify_id=".$f_classify_fatherid.")")->count(); 
        }else{
            $count = M("fgoodsclassify")->where("f_classify_id!='".$f_classify_id."' and f_classify_name ='".$f_classify_name."' and f_classify_fatherid =".$f_classify_fatherid."")->count(); 
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
		$f_classify_id=I("get.f_classify_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "f_classify_able"://on
				M("fgoodsclassify")->where('f_classify_id='.$f_classify_id)->save(array("f_classify_able"=>1));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	public function ajaxable_off(){
		$f_classify_id=I("get.f_classify_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "f_classify_able"://off
				M("fgoodsclassify")->where('f_classify_id='.$f_classify_id)->save(array("f_classify_able"=>2));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}


}
