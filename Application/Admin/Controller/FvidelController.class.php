<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class FvidelController extends Controller {

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

    //分类列表 类 分类
    public function index() {
        $where = "1";
        $where .= " AND skc_fid=1"; 
        $info = I("info");
        if ($info) {// 搜索条件
            @extract($info);
            if ($skc_title) {
                $skc_title = urldecode(trim($skc_title));
                $where .= " and ec_slskillclass.skc_title LIKE '%$skc_title%'";
                $this->assign('skc_title', $skc_title);
            }
        }
        // 查询满足要求的总记录数
        $count = M('slskillclass')
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
        $listinfo = M('slskillclass')
                ->where($where)
                ->order("skc_id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select(); 
		 
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow); 
        $this->display(); 
    }
    //分类列表 类 分类 二级
    public function erji() {
        $where = "1";
        $fid = I("get.skc_id"); 
        $current_class = M('slskillclass')->find($fid);
        $this->assign('current_class', $current_class); 
        $where .= " AND skc_fid=$fid"; 
        $info = I("info");
        if ($info) {// 搜索条件
            @extract($info);
            if ($skc_title) {
                $skc_title = urldecode(trim($skc_title));
                $where .= " and ec_slskillclass.skc_title LIKE '%$skc_title%'";
                $this->assign('skc_title', $skc_title);
            }
        }
        // 查询满足要求的总记录数
        $count = M('slskillclass')
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
        $listinfo = M('slskillclass')
                ->where($where)
                ->order("skc_id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select(); 
		 
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow); 
        $this->display(); 
    }

	//添加一级分类
    public function add() {
        if (IS_POST) {
            $data = I("info"); 
            $data['skc_fid']=1;//
            if (M("slskillclass")->add($data)) {
                jump('一级分类添加成功！', U('index'));
            } else {
                jump('一级分类添加失败！', U('index'));
            }
        } else {
            #$father = M("slskillclass")->where("skc_id=1")->select(); //
            #$this->assign('father', $father);
            $this->display();
        }
    }
	//添加2级分类
    public function add_erji() {
        $fid = I('get.fid'); //此二级分类的父级分类ID
        if (IS_POST){
            $data = I("info"); 
            $data['skc_fid']=$fid; //此二级分类的父级分类ID
            if (M("slskillclass")->add($data)) {
                jump('二级分类添加成功！', U('erji',['skc_id'=>$fid]));
            } else {
                jump('二级分类添加失败！', U('erji',['skc_id'=>$fid]));
            }
        } else {
            $father = M("slskillclass")->find($fid);  
            $this->assign('father', $father);
            $this->display();
        }
    }
	
    //编辑图书分类
    public function edit() { 
        $skc_id = I("get.skc_id");  
        if (IS_POST) {
            $data = I("info");  
            M("slskillclass")->where("skc_id=" . $skc_id)->save($data);
            jump('一级分类编辑成功！', U('index'));
        } else { 
			$current = M("slskillclass")->find($skc_id);  
			$this->assign('current', $current); 
            $this->display();
        }
    }
    public function edit_erji() {  
        $skc_id = I("get.skc_id");  
			$current = M("slskillclass")->field("ec_slskillclass.*,f.skc_title fname")->join("LEFT JOIN ec_slskillclass f ON f.skc_id=ec_slskillclass.skc_fid")->where("ec_slskillclass.skc_id=".$skc_id)->find();  
        if (IS_POST) {
            $data = I("info"); 
            M("slskillclass")->where("skc_id=" . $skc_id)->save($data);
            jump('二级分类编辑成功！', U('erji',['skc_id'=>$current['skc_fid']]));
        } else { 
			$current = M("slskillclass")->field("ec_slskillclass.*,f.skc_title fname")->join("LEFT JOIN ec_slskillclass f ON f.skc_id=ec_slskillclass.skc_fid")->where("ec_slskillclass.skc_id=".$skc_id)->find();  
			$this->assign('current', $current);
            $this->display(); 
        }
    }

    

    //删除图书分类
    public function delete() { 
        $skc_id = I("get.skc_id"); 
        $count = M("slskillclass")->where("skc_fid=" . $skc_id)->count();
        if ($count > 0) {
            jump('底层有分类，不能删除！', U('index'));
        } else {
            M("slskillclass")->delete($skc_id);
            jump('一级分类删除成功', U('index'));
        } 
    }  
    public function delete_erji() {
        $skc_id = I("get.skc_id"); 
        $current = M("slskillclass")->find($skc_id);  
        M("slskillclass")->delete($skc_id);
        jump('一级分类删除成功', U('erji',['skc_id'=>$current['skc_fid']]));
    }  
	//判断一级分类的存在性修改
	public function yiji_check2(){
		$param=trim(I("param")); //input 提交的内容
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{  //检查分类下 是否有名称相同的一级分类
            $cid = I('get.cid');
			$count=M("slskillclass")->where("skc_fid=1 AND skc_id<>$cid AND skc_title='$param'")->count();
			if($count){//存在，验证失败
				$status="n";
				$info="此名称已存在";
			}else{//不存在，验证成功
				$status="y";
				$info="通过信息验证！";
			}
		}
		$this->ajaxReturn(array('status'=>$status,'info'=>$info)); 
	}  
	public function yiji_check(){
		$param=trim(I("param")); //input 提交的内容
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{  //检查分类下 是否有名称相同的一级分类
			$count=M("slskillclass")->where("skc_fid=1 AND skc_title='$param'")->count();
			if($count){//存在，验证失败
				$status="n";
				$info="此名称已存在";
			}else{//不存在，验证成功
				$status="y";
				$info="通过信息验证！";
			}
		}
		$this->ajaxReturn(array('status'=>$status,'info'=>$info));
			
	}  
	
	//判断一级分类的存在性修改
	public function erji_check(){
		$param=trim(I("param")); //input 提交的内容
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{  //检查分类下 是否有名称相同的一级分类 
            $fid = I('get.skc_id'); 
			$count=M("slskillclass")->where("skc_fid=$fid AND skc_title='$param'")->count();
			if($count){//存在，验证失败
				$status="n";
				$info="此名称已存在";
			}else{//不存在，验证成功
				$status="y";
				$info="通过信息验证！";
			}
		}
		$this->ajaxReturn(array('status'=>$status,'info'=>$info));
			
	}  
	public function erji_check2(){
		$param=trim(I("param")); //input 提交的内容
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{  //检查分类下 是否有名称相同的一级分类 
            $fid = I('get.fid'); 
            $cid = I('get.cid');
			$count=M("slskillclass")->where("skc_fid=$fid AND skc_id<>$cid AND skc_title='$param'")->count();
			if($count){//存在，验证失败
				$status="n";
				$info="此名称已存在";
			}else{//不存在，验证成功
				$status="y";
				$info="通过信息验证！";
			}
		}
		$this->ajaxReturn(array('status'=>$status,'info'=>$info));
			
	}  
}
