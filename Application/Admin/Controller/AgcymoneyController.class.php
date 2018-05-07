<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class BookController extends Controller {

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

    //图书列表
    public function index() { 
        $where = " 1 ";
        $info = I("info");
        if ($info) {// 搜索条件
            @extract($info);
            if ($book_name_s) { 
                $where .= " and ec_book.book_name LIKE '%" . $book_name_s . "%'";
                $book_name_s = urldecode(trim($info['book_name_s']));
                $this->assign('book_name_s', $book_name_s);
            } 
            if ($yiji_s) {
                $where .= " and ec_book.classify_fatherid=" . $yiji_s;
                $erji = M("bookclassify")->where("classify_isable = 1 and classify_fatherid=" . $yiji_s)->select();
                $this->assign('erji', $erji);
                $this->assign('yiji_s', $yiji_s);
            }
            if ($erji_s) {
                $where .= " and ec_book.classify_id='" . $erji_s . "'";
                $this->assign('erji_s', $erji_s);
            }
			if($classify_isable){
				$info['classify_isable']=urldecode(trim($info['classify_isable']));
				$where.=" and book_isable ='".$info['classify_isable']."'";  
				$this->assign('classify_', $info['classify_isable']);
			}
        }

        $model = M('book');
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
                ->field("ec_book.*,yiji.classify_name AS yiji_name,erji.classify_name AS erji_name")
                ->join("LEFT JOIN ec_bookclassify AS yiji ON yiji.classify_id = ec_book.classify_fatherid")
                ->join("LEFT JOIN ec_bookclassify AS erji ON erji.classify_id = ec_book.classify_id")
                ->where($where)
                ->order("book_id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select(); 
        foreach ($listinfo AS $k => $v) {
            $listinfo[$k]['cover'] = explode('|', $v['book_image'])[0];
            $listinfo[$k]['book_remark'] = str_cut($v['book_remark'], 20);
        }
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow);
        //一级分类
        $yiji = M("bookclassify")->where("classify_fatherid = 0 and classify_isable = 1")->select();
		$this->assign('classify_isable',D("Bookorder")->classify_isable());//状态
        $this->assign('yiji', $yiji); 
        $this->display();
    }
	
	//创建图书
    public function add() {
        if (IS_POST) {
            $data = I("info"); 
			$data['book_addtime']=time();
            $flag = M("book")->add($data);
            if ($flag) {
                jump('添加图书成功！', U('book/index'));
            } else {
                jump('添加图书失败！', U('book/index'));
            }
           
        } else {
            $yiji = M("bookclassify")->where("classify_fatherid = 0 and classify_isable = 1")->select();
            $this->assign('yiji', $yiji); 
            $this->display();
        }
    }

    //编辑图书
    public function edit() {
        $book_id = I("get.book_id");
        if (IS_POST) {
            $data = I("info"); 
            M("book")->where("book_id=" . $book_id)->save($data); 
            jump('编辑图书成功！', U('book/index'));

        } else {
            $book = M("book")->find($book_id); 
            $book_images = explode('|', $book['book_image']);
            $yiji = M("bookclassify")->where("classify_fatherid = 0 and classify_isable = 1")->select();
            $erji = M("bookclassify")->where("classify_isable = 1 and classify_fatherid=" . $book['classify_fatherid'])->select();
            $this->assign('yiji', $yiji);
            $this->assign('book_images', $book_images);
            $this->assign('erji', $erji);
            $this->assign('book', $book);
			$this->assign('book_id', $book_id);
			//图书标签（所有年级） 
            $this->display();
        }
    }

    

    function get_erji() {
        $yiji_id = I("yiji_id");
        $where = "classify_isable = 1 and classify_fatherid=" . $yiji_id;
        $erji = M("bookclassify")->where($where)->select();
        $this->ajaxReturn($erji);
    }

    //删除图书
    public function delete() { 
        $book_id = I("book_id");
        $flag = M("book")->where("book_id=" . $book_id)->delete();
        if ($flag) {
            jump('删除图书成功！', U('book/index'));
        } else {
            jump('删除图书失败！', U('book/index'));
        }
    }
	
	//判断图书名称的存在性
	public function names(){
		$book_name = urldecode(trim(I("book_name")));
		$yijif = I("yijif");  
		$erjif = I("erjif");  
		$count = M("book")->where("book_name ='".$book_name."' and classify_fatherid='".$yijif."' and classify_id='".$erjif."'")->count(); 
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
	
	//判断图书名称的存在性
	public function name_es(){
		$book_name = urldecode(trim(I("book_name")));
		$yijif = I("yijif");  
		$erjif = I("erjif"); 
		$book_id = I("book_id");  		
		$count = M("book")->where("book_id !='".$book_id."' and book_name='".$book_name."' and classify_fatherid='".$yijif."' and classify_id ='".$erjif."'")->count(); 
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
		$book_id=I("get.book_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "book_isable"://on
				M("book")->where('book_id='.$book_id)->save(array("book_isable"=>1));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	public function ajaxable_off(){
		$book_id=I("get.book_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "book_isable"://off
				M("book")->where('book_id='.$book_id)->save(array("book_isable"=>2));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	
	
	//图书订单统计 
	public function bookall() { 
		$where=" 1 and ec_bookorder.border_state=3";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword'])); 
                $where .= " and  ec_user.realname like '%" . $info['keyword'] . "%' ";
                $this->assign('keyword',urldecode($info['keyword']));
			} 
				
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_bookorder.border_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_bookorder.border_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
			if($starttime){$this->assign('starttime',$starttime);}
			if($endtime){$this->assign('endtime',$endtime);}
		}
		if(!$starttime){
			$starttime=date("Y-m-d",strtotime("-15 day"));
			$this->assign('starttime',$starttime);
		}
		if(!$endtime){
			$endtime=date("Y-m-d",SYS_TIME);
			$this->assign('endtime',$endtime);
		}
		

		$daylist=$this->get_daylist($starttime,$endtime);  
		$infolist=array();
		foreach($daylist as $k=>$v){
			$where_=$where;
			$where_.=" and ec_bookorder.border_addtime>=".strtotime($v." 00:00:00")." ";
			$where_.=" and ec_bookorder.border_addtime<=".strtotime($v." 23:59:59")." ";
			$infolist[$k]['pop_fact'] =  M('bookorder')
				->where($where_)
				->join('left join ec_user on ec_bookorder.user_id=ec_user.user_id')
				->sum("border_money"); 
			$infolist[$k]['num'] =  M('bookorder')
				->where($where_)
				->join('left join ec_user on ec_bookorder.user_id=ec_user.user_id')
				->count();		
			$infolist[$k]['dayinfo'] =$v; 
		} 
		$this->assign('infolist',$infolist);
        $this->display();
	}
	///////////////////////////////////////////////////////////////////////
	//获取两个日期段内所有日期
	public function get_daylist($startdate,$enddate){
		$stimestamp = strtotime($startdate);
		$etimestamp = strtotime($enddate);
		// 计算日期段内有多少天
		$days = ($etimestamp-$stimestamp)/86400+1;
		// 保存每天日期
		$date = array();
		for($i=0; $i<$days; $i++){
			$date[] = date('Y-m-d', $stimestamp+(86400*$i));
		}
		return $date;
	}
    
    //查看详情
    public function info() {
        $book_id = I("book_id");  
    	$book = M('book')  
			->where("book_id = ".$book_id)   
			->find(); 
		$this->assign('book',$book);  
    	$this->display();
    }


}
