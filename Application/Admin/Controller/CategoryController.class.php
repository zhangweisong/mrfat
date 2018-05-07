<?php
namespace Admin\Controller;
use Think\Controller;
class CategoryController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录状态
        if(!D('admininfo')->islogin()){//未登录
            jump('您尚未登录，请先登录！',U('login/login'),3);
        }
    }
    //空方法，防止报错
    public function _empty() {
        $this->index();
    }
    //积分商品分类
    public function index(){
        $where="1 ";
        $info=I("info");
        if ($info){
            @extract($info);
			
            if($category_name){
                $info['category_name']=urldecode(trim($info['category_name']));
                $where.=" and (ec_category.category_name like '%".$info['category_name']."%')";
                $this->assign('category_name',$info['category_name']);
            }

            if($status){
                $info['status']=urldecode(trim($info['status']));
                $where.=" and ec_category.status = ".$info['status'];
                $this->assign('status',$info['status']);
            }
        }
        $category=M('category');
        // 查询满足要求的总记录数
        $count = $category
            ->where($where)
            ->count();
        $Page       = new \Think\Page($count,15);
        //分页跳转的时候保证查询条件
        if($info){
            foreach($info as $key=>$val) {
                $Page->parameter['info['.$key.']']   =   urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        // 进行分页数据查询
        $category = $category
            ->where($where)
            ->order('category_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('category',$category);
        $this->assign('pageshow',$pageshow);
        $this->assign('status1',D('category')->category_isable());
        $this->display();
    }
    //添加商品分类
    public function add()
    {
        if (IS_POST){
            $data['category_name']=trim($_POST['category_name']);           
            //$data['sort']=trim($_POST['category_sort']);
            $data['status']=trim($_POST['status']);
            $addInfo=M('category')->add($data);
            if($addInfo){
                jump('添加分类成功！',U('category/index'),3);
            }else{
                jump('添加分类失败',U('category/index'),3);
            }
        }else{
            //$this->assign('classOrder',D('category')->category_sort());
            $this->display();
        }
    }
	//添加商品分类时判断名称不重复
	public function existadd(){
		$param=I("param");
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{
			$count=M("category")->where("category_name = '".$param."'")->count();
			if($count){//存在，验证失败
				$status="n";
				$info="此商品分类名称已存在";
			}else{//不存在，验证成功
				$status="y";
				$info="此分类名称不存在，可以增加";
			}
		}
		$this->ajaxReturn(array('status'=>$status,'info'=>$info));
	}
    //编辑商品分类
    public function edit()
    {
        $category_id=I('get.category_id');
        if (IS_POST){
            $data['category_name']=trim($_POST['category_name']);          
            //$data['sort']=trim($_POST['category_sort']);
            $data['status']=trim($_POST['status']);
            $flag=M('category')->where("category_id = ".$category_id)->save($data);
            if($flag){
                jump('编辑分类成功！',U('category/index'),3);
            }else{
                jump('您未做任何修改！',U('category/index'),3);
            }
        }else{
            $category=M("category")->where("category_id = ".$category_id)->find();
            $this->assign('category',$category);
            //$this->assign('classOrder',D('category')->category_sort());
            $this->display();
        }
    }
	//编辑商品分类时判断名称不重复
	public function existedit(){
		$param=I("param");
		$category_id=I("get.category_id");
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{
			$category_name=M("category")->where("category_id = '".$category_id."'")->field('category_name')->find();
			if($category_name['category_name']==$param){
				$status="y";
				$info="通过信息验证！";
				
			}else{
				$count=M("category")->where("category_name = '".$param."'")->count();
				if($count){//存在，验证失败
					$status="n";
					$info="此分类名称已存在";
				}else{//不存在，验证成功
					$status="y";
					$info="此分类名称不存在，可以增加";
				}
			}
			
		}
		$this->ajaxReturn(array('status'=>$status,'info'=>$info));
	}
   //关闭->启用
	public function ajax_on(){
		$category_id=I("get.category_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "status"://on
				M("category")->where('category_id='.$category_id)->save(array("status"=>1));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	//启用->关闭
	public function ajax_off(){
		$category_id=I("get.category_id");//ID
		if($category_id==1){
			$this->ajaxReturn(array("flag"=>"-1"));
		}else{
			$field=I("get.field");//字段，用作下面代码处理
			switch($field){
				case "status"://off
					M("category")->where('category_id='.$category_id)->save(array("status"=>2));
					break;
				
			}
			$this->ajaxReturn(array("flag"=>"1"));
		}
		
	}
}
