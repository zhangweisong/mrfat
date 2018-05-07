<?php
namespace Admin\Controller;
use Think\Controller;
class ExperttypeController extends Controller {

	function __construct() {
		//继承父类
		parent::__construct();

		//判断登录状态
		if(!D('admininfo')->islogin()){//未登录
			jump('您尚未登录，请先登录！',U('login/login'));
		}
		
    }

	//空方法，防止报错
	public function _empty(){
        $this->index();
    }
	

	//列表
    public function index(){
		$where=" 1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword']));
                $where.="and type_name like '%".$info['keyword']."%'";  
                $this->assign('keyword',urldecode($info['keyword']));
			}
			if($type_isable){
				$info['type_isable']=urldecode(trim($info['type_isable']));
                $where.="and type_isable ='".$info['type_isable']."' ";  
                $this->assign('type_isable',urldecode($info['type_isable']));
				
			}
		}
		$expert_type=M('expert_type');
		// 查询满足要求的总记录数
		$count      = $expert_type->where($where)->count();
		$Page       = new \Think\Page($count,20);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$listinfo =  $expert_type
					->where($where)
					->order('ec_expert_type.type_sort asc,ec_expert_type.type_id desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$this->assign('listinfo',$listinfo);
		$this->assign('pageshow',$pageshow);
        //是否显示
		$this->assign('type_isables',D('expert')->type_isables());
		$this->display();
    }

	//添加
    public function add(){
		if (IS_POST){
			$info=I("info");
            $type_name=trim($info['type_name']);
			$count_=M('expert_type')->where(" type_name='".$type_name."' ")->count();
			if($count_>0){
				jump('添加问答分类失败,此问答分类已存在！',U('experttype/index'));
			}
			$flag=M("expert_type")->add($info);//echo $flag;die;
			if($flag){
				jump('添加问答分类成功！',U('experttype/index'));
				
			}else{
				jump('添专问答分类失败！',U('experttype/index'));
			}
		}else{
			$this->display();
		}
    }

	//编辑
    public function edit(){
		$type_id=I("get.type_id");
		if (IS_POST){
			$info=I("info");
            $type_name=trim($info['type_name']);
			$count_=M('expert_type')->where(" type_name='".$type_name."' and type_id!=".$type_id." ")->count();			
			if($count_>0){
				jump('编辑问答分类失败,此问答分类已存在！',U('experttype/index'));
			}
			$flag=M("expert_type")->where("type_id=".$type_id)->save($info);
			if($flag){
				jump('编辑问答分类成功！',U('experttype/index'));
			}else{
				jump('编辑问答分类失败！',U('experttype/index'));
			}
		}else{
			$expert_type=M("expert_type")->where("type_id=".$type_id)->find();
			$this->assign('expert_type',$expert_type);
			$this->display();
		}
    }

	//删除
	public function del(){
		$type_id=I("get.type_id");
		M('expert_type')->where('type_id='.$type_id)->delete();
		jump('问答分类删除成功！',U('experttype/index'));
	}	
	
    
    //ajax处理状态
	public function ajaxable_on(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "type_isable"://on
				M("expert_type")->where('type_id='.$id)->save(array("type_isable"=>1));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	public function ajaxable_off(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "type_isable"://off
				M("expert_type")->where('type_id='.$id)->save(array("type_isable"=>2));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	
}