<?php
namespace Admin\Controller;
use Think\Controller;
class CarouselclassController extends Controller{

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


    //轮播分类列表
    public function index(){
    	$where="1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($focusclassify_name){
                $info['focusclassify_name']=urldecode(trim($info['focusclassify_name']));
                $where.="and (focusclassify_name like '%".$info['focusclassify_name']."%')";
                $this->assign('focusclassify_name',$info['focusclassify_name']);
            } 
			if($classify_isable){
				$info['classify_isable']=urldecode(trim($info['classify_isable']));
				$where.=" and focusclassify_isable ='".$info['classify_isable']."'";  
				$this->assign('classify_', $info['classify_isable']);
			}
        }
        $focus=M('focusclassify');
        // 查询满足要求的总记录数
        $count      = $focus->where($where)->count();
        $Page       = new \Think\Page($count,15);
        //分页跳转的时候保证查询条件
        if($info){
            foreach($info as $key=>$val) {
                $Page->parameter['info['.$key.']']   =   urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        // 进行分页数据查询
        $listinfo = $focus
            ->where($where)
            ->order('focusclassify_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
            //dd($listinfo);
        $this->assign('focusInfo',$listinfo);
        $this->assign('pageshow',$pageshow);
		$this->assign('classify_isable',D("Bookorder")->classify_isable());//状态
        $this->assign('focusType',D("focusinfo")->focus_type());//幻灯类型
        $this->display();
    }

    //轮播分类添加
    public function Carouselclassadd(){
        if (IS_POST){
           $data['focusclassify_name']= trim(I('focusclassify_name'));
           //$data['focusclassify_remarks']= trim(I('focusclassify_remarks'));
           //$data['focusclassify_sort']= trim(I('focusclassify_sort'));
           $data['focusclassify_isable']= trim(I('focusclassify_isable'));
           $addInfo=M('focusclassify')->add($data);
           if($addInfo){
               jump('添加轮播分类成功！',U('Carouselclass/index'));
           }else{
               jump('添加轮播分类失败！',U('Carouselclass/index'));
           }

        }else{
        	//轮播分类状态
            $this->assign('focusType',D('Focusclassify')->focusclassify_isable());
            //轮播分类排序
            //$this->assign('focusOrder',D('Focusclassify')->focusclassify_sort());
            $this->display();
        }
    }

     //轮播分类编辑
    public function Carouselclassedit(){
           
           $focusId= I('get.focusclassify_id');
    	   if (IS_POST){
           $data['focusclassify_name']= trim(I('focusclassify_name'));
           //$data['focusclassify_remarks']= trim(I('focusclassify_remarks'));
           //$data['focusclassify_sort']= trim(I('focusclassify_sort'));
           $data['focusclassify_isable']= trim(I('focusclassify_isable'));
           $addInfo=M('focusclassify')->where("focusclassify_id=".$focusId)->save($data);
           if($addInfo){
               jump('编辑轮播分类成功！',U('Carouselclass/index'));
           }else{
               jump('编辑轮播分类失败！',U('Carouselclass/index'));
           }

        }else{
            //列表数据
        	$focusInfo=M("focusclassify")->where("focusclassify_id=".$focusId)->find();
            $this->assign('focus',$focusInfo);
            //dd($focusInfo);
        	//轮播分类状态
            $this->assign('focusType',D('Focusclassify')->focusclassify_isable());
            //轮播分类排序
            //$this->assign('focusOrder',D('Focusclassify')->focusclassify_sort());
            $this->display();
        }
    }

     //轮播分类删除
    public function Carouselclassdel(){
       $focusclassify_id = I('get.focusclassify_id');
    	M('focusclassify')->where(array('focusclassify_id'=>$focusclassify_id))->delete();
    	jump('移除轮播分类成功',U('Carouselclass/index'));
    }

    //轮播分类->开启
    public function ajax_on() {
        $shop_id = I("get.focusclassify_id"); //ID
        $field = I("get.field"); //字段，用作下面代码处理
        switch ($field) {
            case "isshow"://on
                M("focusclassify")->where('focusclassify_id=' . $shop_id)->save(array("focusclassify_isable" => 1));
                break;
        }
        $this->ajaxReturn(array("flag" => "1"));
    }

    //轮播分类->关闭
    public function ajax_off() {
        $shop_id = I("get.focusclassify_id"); //ID
        $field = I("get.field"); //字段，用作下面代码处理
        switch ($field) {
            case "isshow"://off
                M("focusclassify")->where('focusclassify_id=' . $shop_id)->save(array("focusclassify_isable" => 2));
                break;
        }
        $this->ajaxReturn(array("flag" => "1"));
    }
}