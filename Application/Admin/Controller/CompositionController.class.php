<?php
namespace Admin\Controller;
use Think\Controller;
class CompositionController extends Controller {

	function __construct() {
		//继承父类
		parent::__construct();

       //判断登录状态
		if(!D('admininfo')->islogin()){//未登录
			jump('您尚未登录，请先登录！',U('login/login'),3);
		}	
    }

	//空方法，防止报错
	public function _empty(){
        dd("no die");
    }
	  
    //新闻列表
    public function index(){
		$where=" 1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword']));
                $where.="and ec_composition.composition_name like '%".$info['keyword']."%'";  
                $this->assign('keyword',urldecode($info['keyword']));
			}
			if($category){ 
				$info['category']=urldecode(trim($info['category']));
                $where.="and ec_composition.category_id = '".$info['category']."' ";  
                $this->assign('category1',urldecode($info['category']));
				
			}
			if($status){ 
				$info['status']=urldecode(trim($info['status']));
                $where.="and ec_composition.status ='".$info['status']."' ";  
                $this->assign('status1',urldecode($info['status'])); 
			}
			
		} 
		// 查询满足要求的总记录数
		$count      = M('composition')->where($where)->count();
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$composition = M('composition')
			->where($where)
			->join('left join ec_category on ec_composition.category_id=ec_category.category_id')
			->field("ec_composition.*,ec_category.category_name")
			->order('ec_composition.composition_id desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->select(); 
			
		foreach($composition as $k=>$v) {
			$image=explode("|", $v['composition_img']);
			$composition[$k]['composition_img']=$image[0];
		} 
         
		//分类
		$category = M('category')->where("status=1")->select();
		//状态
		$status = [1=>"启用",2=>"禁用"]; 
		$this->assign('composition',$composition);
		$this->assign('pageshow',$pageshow);
		$this->assign('category',$category);
		$this->assign('status',$status); 
		$this->display();
    }

	//添加文章
    public function add(){
		if (IS_POST){
			$info=I("info");
			$info['status']=1;
			$info['addtime']=SYS_TIME;
			$info['composition_name']=trim($info['composition_name']);			
			$info['composition_text']=trim($info['content']);			
			$info['composition_img']=trim($info['image']);
            $count = M('composition')->where('composition_name="'.$info['composition_name'].'"')->count();
            if($count>0){
				jump('此文章标题已存在，请重新输入！',U('composition/index'));
			}			
			$flag=M("composition")->add($info); 
			if($flag){
				jump('添加文章成功！',U('composition/index'));
			}else{
				jump('添加文章失败！',U('composition/index'));
			}
		}else{
			$category = M('category')->where("status=1")->select();
			$this->assign('category',$category);
			$this->display();
		}
    }

	//编辑新闻
    public function edit(){
		$composition_id=I("get.composition_id"); 
		if (IS_POST){
			$info=I("info");				
			$info['status']=$info['status'];
			$info['addtime']=SYS_TIME;
			$info['composition_name']=trim($info['composition_name']);			
			$info['composition_text']=trim($info['content']);			
			$info['composition_img']=trim($info['image']);
            $count = M('composition')->where('composition_name="'.$info['composition_name'].'" and composition_id !='.$composition_id)->count();
            if($count>0){
				jump('此文章标题已存在，请重新输入！',U('composition/index'));
			}			
			$flag=M("composition")->where("composition_id=".$composition_id)->save($info);
                jump('编辑文章成功！',U('composition/index'));			
		}else{
			$content=M("composition")->where("composition_id=".$composition_id)->find();			
			if($content['composition_img']==""){
				$content_image_count=0;
			}else{
				$content_image_count=1;
			}			
			//类型 
			$category = M('category')->where("status=1")->select();
			$this->assign('content',$content);
			$this->assign('category',$category);
			$this->assign('image', explode("|", $content['composition_img'])); 			
			$this->assign('content_image_count', $content_image_count);
			$this->display();
		}
    }

    //删除文章
	public function del(){
		$composition_id=abs(I("get.composition_id")); 
		$content_=M('composition')
			->join('left join ec_category on ec_composition.category_id=ec_category.category_id')
			->where('ec_composition.composition_id='.$composition_id) 
			->find();
        if($content_['category_id']==1){
			 jump('此文章类型为单页禁止删除！',U('composition/index'));
		}			
		if($content_){
			M('composition')->where('composition_id='.$composition_id)->delete();
                jump('文章删除成功！',U('composition/index'));
		}else{
                jump('文章删除失败！',U('composition/index'));	
		}	
	}
	 	
    //文章关闭->启用
	public function ajax_on(){
		$composition_id=I("get.composition_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "status"://on
				M("composition")->where('composition_id='.$composition_id)->save(array("status"=>1));
				break;
			
		}		
	}
	//文章启用->关闭
	public function ajax_off(){
		$composition_id=I("get.composition_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "status"://off
				M("composition")->where('composition_id='.$composition_id)->save(array("status"=>2));
				break;
			
		}		
	}
  

}