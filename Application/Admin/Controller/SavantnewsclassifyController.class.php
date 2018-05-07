<?php
namespace Admin\Controller;
use Think\Controller;
class SavantnewsclassifyController extends Controller {

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
	

	//专家文章分类列表
    public function index(){
		$where=" 1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword']));
                $where.="and snclassify_name like '%".$info['keyword']."%'";  
                $this->assign('keyword',urldecode($info['keyword']));
			}
			if($snclassify_isable){
				$info['snclassify_isable']=urldecode(trim($info['snclassify_isable']));
                $where.="and snclassify_isable ='".$info['snclassify_isable']."' ";  
                $this->assign('snclassify_isable',urldecode($info['snclassify_isable']));
				
			}
		}
		$savantnewsclassify=M('savantnewsclassify');
		// 查询满足要求的总记录数
		$count      = $savantnewsclassify->where($where)->count();
		$Page       = new \Think\Page($count,20);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$listinfo =  $savantnewsclassify
					->where($where)
					->order('ec_savantnewsclassify.snclassify_sort asc ,ec_savantnewsclassify.snclassify_id desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$this->assign('listinfo',$listinfo);
		$this->assign('pageshow',$pageshow);
        //是否显示
		$this->assign('snclassify_isables',D('expert')->snclassify_isables());
		$this->display();
    }

	//添加等级
    public function add(){
		if (IS_POST){
			$info=I("info");
            $snclassify_name=trim($info['snclassify_name']);
			$count_=M('savantnewsclassify')->where(" snclassify_name='".$snclassify_name."' ")->count();
			if($count_>0){
				jump('添加专家文章类型失败,此文章类型已存在！',U('savantnewsclassify/index'));
			}
			$flag=M("savantnewsclassify")->add($info);//echo $flag;die;
			if($flag){
				jump('添加专家文章类型成功！',U('savantnewsclassify/index'));
				
			}else{
				jump('添加专家文章类型失败！',U('savantnewsclassify/index'));
			}
		}else{
			$this->display();
		}
    }

	//编辑等级
    public function edit(){
		$snclassify_id=I("get.snclassify_id");
		if (IS_POST){
			$info=I("info");
            $snclassify_name=trim($info['snclassify_name']);
			$count_=M('savantnewsclassify')->where(" snclassify_name='".$snclassify_name."' and snclassify_id!=".$snclassify_id." ")->count();			
			if($count_>0){
				jump('编辑专家文章类型失败,此专家文章类型已存在！',U('savantnewsclassify/index'));
			}
			$flag=M("savantnewsclassify")->where("snclassify_id=".$snclassify_id)->save($info);
			if($flag){
				jump('编辑专家文章类型成功！',U('savantnewsclassify/index'));
			}else{
				jump('编辑专家文章类型失败！',U('savantnewsclassify/index'));
			}
		}else{
			$savantnewsclassify=M("savantnewsclassify")->where("snclassify_id=".$snclassify_id)->find();
			$this->assign('savantnewsclassify',$savantnewsclassify);
			$this->display();
		}
    }

	//删除等级
	public function del(){
		$snclassify_id=I("get.snclassify_id");
		M('savantnewsclassify')->where('snclassify_id='.$snclassify_id)->delete();
		jump('专家文章类型删除成功！',U('savantnewsclassify/index'));
	}	
	
    
    //ajax处理状态
	public function ajaxable_on(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "snclassify_isable"://on
				M("savantnewsclassify")->where('snclassify_id='.$id)->save(array("snclassify_isable"=>1));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	public function ajaxable_off(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "snclassify_isable"://off
				M("savantnewsclassify")->where('snclassify_id='.$id)->save(array("snclassify_isable"=>2));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	
}