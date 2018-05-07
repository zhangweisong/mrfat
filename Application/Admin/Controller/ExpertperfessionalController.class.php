<?php
namespace Admin\Controller;
use Think\Controller;
class ExpertperfessionalController extends Controller {

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
                $where.="and sclassify_name like '%".$info['keyword']."%'";  
                $this->assign('keyword',urldecode($info['keyword']));
			}
			if($sclassify_isable){
				$info['sclassify_isable']=urldecode(trim($info['sclassify_isable']));
                $where.="and sclassify_isable ='".$info['sclassify_isable']."' ";  
                $this->assign('sclassify_isable',urldecode($info['sclassify_isable']));
				
			}
		}
		$savantclassify=M('savantclassify');
		// 查询满足要求的总记录数
		$count      = $savantclassify->where($where)->count();
		$Page       = new \Think\Page($count,20);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$listinfo =  $savantclassify
					->where($where)
					->order('ec_savantclassify.sclassify_sort asc,ec_savantclassify.sclassify_id desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$this->assign('listinfo',$listinfo);
		$this->assign('pageshow',$pageshow);
        //是否显示
		$this->assign('sclassify_isables',D('expert')->sclassify_isables());
		$this->display();
    }

	//添加等级
    public function add(){
		if (IS_POST){
			$info=I("info");
            $sclassify_name=trim($info['sclassify_name']);
			$count_=M('savantclassify')->where(" sclassify_name='".$sclassify_name."' ")->count();
			if($count_>0){
				jump('添加专家职称失败,此专家职称已存在！',U('expertperfessional/index'));
			}
			$flag=M("savantclassify")->add($info);//echo $flag;die;
			if($flag){
				jump('添加专家职称成功！',U('expertperfessional/index'));
				
			}else{
				jump('添专家职称失败！',U('expertperfessional/index'));
			}
		}else{
			$this->display();
		}
    }

	//编辑
    public function edit(){
		$sclassify_id=I("get.sclassify_id");
		if (IS_POST){
			$info=I("info");
            $sclassify_name=trim($info['sclassify_name']);
			$count_=M('savantclassify')->where(" sclassify_name='".$sclassify_name."' and sclassify_id!=".$sclassify_id." ")->count();			
			if($count_>0){
				jump('编辑专家职称失败,此专家职称已存在！',U('expertperfessional/index'));
			}
			$flag=M("savantclassify")->where("sclassify_id=".$sclassify_id)->save($info);
			if($flag){
				jump('编辑专家职称成功！',U('expertperfessional/index'));
			}else{
				jump('编辑专家职称失败！',U('expertperfessional/index'));
			}
		}else{
			$savantclassify=M("savantclassify")->where("sclassify_id=".$sclassify_id)->find();
			$this->assign('savantclassify',$savantclassify);
			$this->display();
		}
    }

	//删除
	public function del(){
		$sclassify_id=I("get.sclassify_id");
		M('savantclassify')->where('sclassify_id='.$sclassify_id)->delete();
		jump('专家职称删除成功！',U('expertperfessional/index'));
	}	
	
    
    //ajax处理状态
	public function ajaxable_on(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "sclassify_isable"://on
				M("savantclassify")->where('sclassify_id='.$id)->save(array("sclassify_isable"=>1));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	public function ajaxable_off(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "sclassify_isable"://off
				M("savantclassify")->where('sclassify_id='.$id)->save(array("sclassify_isable"=>2));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	
}