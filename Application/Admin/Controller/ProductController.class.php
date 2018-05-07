<?php
namespace Admin\Controller;
use Think\Controller;
class ProductController extends Controller{

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
        $this->index();
    }

    //产品形态列表
    public function index(){
    	$where="1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($gs_title){
                $info['gs_title']=urldecode(trim($info['gs_title']));
                $where.="and (gs_title like '%".$info['gs_title']."%')";
                $this->assign('gs_title',$info['gs_title']);
            }
			if($classify_isable){
				$info['classify_isable']=urldecode(trim($info['classify_isable']));
				$where.=" and gs_isable ='".$info['classify_isable']."'";  
				$this->assign('classify_', $info['classify_isable']);
			}
        }
        $focus=M('goodshape');
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
            ->order('gs_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
           // dd($listinfo);
        $this->assign('focusInfo',$listinfo);
		$this->assign('classify_isable',D("Bookorder")->classify_isable());//状态
        $this->assign('pageshow',$pageshow);
        $this->display();
    }

     //产品形态添加
    public function Productadd(){
    		if (IS_POST){
           $data['gs_title']= trim(I('gs_title'));
           $data['gs_image']= trim(I('gs_image'));
           $data['gs_isable']= trim(I('gs_isable'));
           $addInfo=M('goodshape')->add($data);
           if($addInfo){
               jump('添加产品形态成功！',U('Product/index'));
           }else{
               jump('添加产品形态失败！',U('Product/index'));
           }

        }else{
        	//轮播分类状态
            $this->assign('focusType',D('Focusclassify')->focusclassify_isable());
            //轮播分类排序
            $this->assign('focusOrder',D('Focusclassify')->focusclassify_sort());
            $this->display();
        }
    }


     //产品形态编辑
    public function Productedit(){
    	  $focusId= I('get.gs_id');
    	   if (IS_POST){
           $data['gs_title']= trim(I('gs_title'));
           $data['gs_image']= trim(I('gs_image'));
           $data['gs_isable']= trim(I('gs_isable'));
           $addInfo=M('goodshape')->where("gs_id=".$focusId)->fetchsql(false)->save($data);
           //dd($addInfo);
           if($addInfo){
               jump('编辑产品形态成功！',U('Product/index'));
           }else{
               jump('编辑产品形态失败！',U('Product/index'));
           }

        }else{
            //列表数据
        	$focusInfo=M("goodshape")->where("gs_id=".$focusId)->find();
            $this->assign('focus',$focusInfo);
            //dd($focusInfo);
        	//轮播分类状态
            $this->assign('focusType',D('Focusclassify')->focusclassify_isable());
            //轮播分类排序
            $this->assign('focusOrder',D('Focusclassify')->focusclassify_sort());
            $this->display();
        }
    }


     //产品形态移除
    public function Productdel(){
    	$gs_id = I('get.gs_id');
    	M('goodshape')->where(array('gs_id'=>$gs_id))->delete();
    	jump('移除产品形态成功',U('Product/index'));
    }

      //产品形态->开启
    public function ajax_on() {
        $gs_id = I("get.gs_id"); //ID
        $field = I("get.field"); //字段，用作下面代码处理
        switch ($field) {
            case "isshow"://on
                M("goodshape")->where('gs_id=' . $gs_id)->save(array("gs_isable" => 1));
                break;
        }
        $this->ajaxReturn(array("flag" => "1"));
    }

    //产品形态->关闭s
     public function ajax_off() {
        $gs_id = I("get.gs_id"); //ID
        $field = I("get.field"); //字段，用作下面代码处理
        switch ($field) {
            case "isshow"://on
                M("goodshape")->where('gs_id=' . $gs_id)->save(array("gs_isable" => 2));
                break;
        }
        $this->ajaxReturn(array("flag" => "1"));
    }

}