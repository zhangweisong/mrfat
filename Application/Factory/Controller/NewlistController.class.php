<?php
namespace Factory\Controller;
use Think\Controller;
class NewlistController extends Controller
{
	function __construct() {
		//继承父类
		parent::__construct();		
		//判断登录状态
		if(!D('Factory')->islogin()){//未登录
			jump('您尚未登录，请先登录！', U('login/login'));
		}	
    }
	//空方法，防止报错
	public function _empty(){
        $this->index();
    }
	//新闻列表
	public function index(){
		$factory_id=session('factory.factory_id');
		$where="factory_id=".$factory_id." ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword']));
                $where.="and ec_new.new_title like '%".$info['keyword']."%'";  
                $this->assign('keyword',urldecode($info['keyword']));
			}
			if($newtype){ 
				$info['newtype']=urldecode(trim($info['newtype']));
                $where.="and ec_new.new_type = '".$info['newtype']."' ";  
                $this->assign('newtype_',urldecode($info['newtype']));	
			}
			if($isshow){ 
				$info['isshow']=urldecode(trim($info['isshow']));
                $where.="and ec_new.new_isshow ='".$info['isshow']."' ";  
                $this->assign('isshow_',urldecode($info['isshow'])); 
			}
			
		} 
		// 查询满足要求的总记录数
		$count      = M('new')->where($where)->count();
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$newlist = M('new')
			->where($where)
			->order('new_id desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->select(); 
		foreach($newlist as $k=>$v) {
			$image=explode("|", $v['image']);
			$newlist[$k]['new_image']=$image[0];
		} 
		//状态
		$isshow = [1=>"启用",2=>"禁用"]; 
		$this->assign('newlist',$newlist);
		$this->assign('pageshow',$pageshow);
		$this->assign('isshow',$isshow); 
		$this->assign('newtype',D('new')->newtype());
		$this->display();
	}
	//添加新闻
	public function addnewlist(){
		$factory_id=session('factory.factory_id');
		if (IS_POST){
			$info=I("info");
			$info['new_isshow']=1;
			$info['new_time']=SYS_TIME;
			$info['factory_id']=$factory_id;
			$title=trim($info['new_title']);
			$count_=M('new')->where("new_title='".$title."' ")->count();
			if($info['new_type'] !=5){
               $result = M('new')->where('new_type='.$info['new_type'])->count();
			}
            if($result>0){
				jump('此分类已有文章,如有变动请去编辑！',U('newlist/index'));
			}else{		
				if($count_>0){
					jump('添加新闻失败,此新闻已存在！',U('newlist/index'));
				}else{
					$flag=M("new")->add($info); 
					if($flag){
						jump('添加新闻成功！',U('newlist/index'));
					}else{
						jump('添加新闻失败！',U('newlist/index'));
					}
				}
			}	
		}else{
			$this->assign('newtype',D('new')->newtype());
		    $this->display();
		}
	}
    //编辑新闻
	public function editnewlist(){
		$factory_id=session('factory.factory_id');
		$id=I("get.id"); 
		if (IS_POST){
			$info=I("info");
			$info['factory_id']=$factory_id;
			$title=trim($info['new_title']);	
			$count_=M('new')->where("new_title='".$title."' and new_id!=".$id." ")->count();			
			if($count_>0){
				jump('编辑新闻失败,此新闻已存在！',U('newlist/index'));
			}
			$flag=M("new")->where("new_id=".$id)->save($info);
                jump('编辑新闻成功！',U('newlist/index'));			
		}else{
			$content=M("new")->where("new_id=".$id)->find();
			if($content['new_img']==""){
				$content_image_count=0;
			}else{
				$content_image_count=1;
			}
			//类型 
			$this->assign('newtype',D('new')->newtype());
			$this->assign('content',$content);
			$this->assign('content_image', explode("|", $content['new_img'])); 
			$this->assign('content_image_count', $content_image_count);
			$this->display();
		}
	}
	//删除不是单页的新闻
	public function delnewlist (){
		$id=I("get.id"); 
		$content_=M('new')
			->where('new_id='.$id) 
			->find(); 
		if($content_){
			if(trim($content_['new_type'])!=5){
				jump('新闻类型为单页禁止删除！',U('newlist/index'));
			}
			M('new')->where('new_id='.$id)->delete();
            jump('新闻删除成功！',U('newlist/index'));
		}else{
            jump('新闻删除失败！',U('newlist/index'));	
		}	
	}
	 //分类隐藏->显示
	public function ajax_on(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "new_isshow"://on
				M("new")->where('new_id='.$id)->save(array("new_isshow"=>1));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	//分类显示->隐藏
	public function ajax_off(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "new_isshow"://off
				M("new")->where('new_id='.$id)->save(array("new_isshow"=>2));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	//新闻分类
    public function newtype(){
    	$where = " 1 ";
    	$info=I("info");
		if ($info){
			@extract($info);
			if($class_name){
				$info['class_name']=urldecode(trim($info['class_name']));
                $where.="and class_name like '%".$info['class_name']."%'";  
                $this->assign('class_name',urldecode($info['class_name']));
			}  
			if($class_isshow){ 
				$info['class_isshow']=urldecode(trim($info['class_isshow']));
                $where.="and class_isshow ='".$info['class_isshow']."' ";  
                $this->assign('class_isshow',urldecode($info['class_isshow'])); 
			} 
		}
		// 查询满足要求的总记录数
		$count      = M('newclass')->where($where)->count();
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
    	$newtype = M('newclass')
    		->where($where)
    		->order('class_id desc')
    		->limit($Page->firstRow.','.$Page->listRows)
    		->select();
    	//状态
    	$isshow = [1=>"启用",2=>"禁用"];
    	$this->assign('newtype',$newtype);
    	$this->assign('isshow',$isshow);
    	$this->assign('pageshow',$pageshow);    
		$this->display();
    }

    //删除分类
	public function delnewtype(){
		$id=abs(I("get.id")); 
		$content_=M('newclass')->where('class_id='.$id)->find();
		if($content_){
			if($content_['class_name']=="单页"){
				jump('分类名称为单页禁止删除！',U('newlist/newtype'));
			}
			 M('newclass')->where('class_id='.$id)->delete();
                jump('分类删除成功！',U('newlist/newtype'));
		}else{
                jump('分类删除失败！',U('newlist/newtype'));	
		}	
	}

	//添加新闻分类
    public function addnewtype(){
		if (IS_POST){
			$info=I("info"); 
			$name=trim($info['class_name']);
			$count_=M('newclass')->where(" class_name='".$name."' ")->count();			
			if($count_>0){
				jump('添加分类失败,此分类已存在！',U('newlist/newtype'));
			}
			$flag=M("newclass")->add($info);
			if($flag){
				jump('添加分类成功！',U('newlist/newtype'));
			}else{
				jump('添加分类失败！',U('newlist/newtype'));
			}
		}else{ 
			$this->display();
		}
    }

    //编辑新闻分类
    public function editnewtype(){
		$id=I("get.id");
		if (IS_POST){
			$info=I("info");
			$name=trim($info['class_name']);	
			$count_=M('newclass')->where("class_name='".$name."' and class_id!=".$id." ")->count();
			if($count_>0){
				jump('编辑分类失败,此分类已存在！',U('newlist/newtype'));
			}
			$flag=M("newclass")->where("class_id=".$id)->save($info);
                jump('编辑分类成功！',U('newlist/newtype'));			
		}else{
			$content=M("newclass")->where("class_id=".$id)->find(); 
			$this->assign('content',$content); 
			$this->display();
		}
    }
	    //分类隐藏->显示
	public function ajaxable_on(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "isshow"://on
				M("newclass")->where('class_id='.$id)->save(array("class_isshow"=>1));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	//分类显示->隐藏
	public function ajaxable_off(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "isshow"://off
				M('newclass')->where('class_id='.$id)->save(array("class_isshow"=>2));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
}