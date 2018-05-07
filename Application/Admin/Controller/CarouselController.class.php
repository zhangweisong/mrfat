<?php
namespace Admin\Controller;
use Think\Controller;
class CarouselController extends Controller{

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


    //轮播列表
    public function index(){
    	$where="1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($focus_title){
                $info['focus_title']=urldecode(trim($info['focus_title']));
                $where.="and (focus_title like '%".$info['focus_title']."%')";
                $this->assign('focus_title',$info['focus_title']);
            }
            if($focus_type){
                $info['focus_type']=urldecode(trim($info['focus_type']));
                $where.="and focus_type ='".$info['focus_type']."'";
                $this->assign('focus_type',$info['focus_type']);
            }
        }
        $focus=M('focusinfo');
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
            ->order('focus_type asc,focus_id desc')
			->join('left join ec_focusclassify on    ec_focusclassify.focusclassify_id=ec_focusinfo.focus_type')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('focusInfo',$listinfo);
        $this->assign('pageshow',$pageshow);
        $this->assign('focusType',D("focusinfo")->focus_type());//幻灯类型
        $this->display();

    }

    //轮播添加
    public function Carouseladd(){

    	if (IS_POST){
           $data['focus_title']=$_POST['focus_title'];
           $data['focus_type']=$_POST['focus_type'];
           $data['focus_url']=$_POST['focus_url'];
           $data['focus_image']=$_POST['focus_image'];
           //$data['focus_sort']=$_POST['focus_sort'];
           $data['focus_addtime']=time();
           $addInfo=M('focusinfo')->add($data);
           if($addInfo){
               jump('添加轮播成功！',U('Carousel/index'));
           }else{
               jump('添加轮播失败！',U('Carousel/index'));
           }

        }else{
            $this->assign('focusType',D('Focusinfo')->focus_type());
            $this->assign('focusOrder',D('Focusinfo')->focus_sort());
            $this->display();
        }
    	
    }


    //轮播编辑
    public function Carouseledit(){

    	$focusId= I('get.focus_id');
        if (IS_POST){
            $data['focus_title']=$_POST['focus_title'];
            $data['focus_type']=$_POST['focus_type'];
            $data['focus_url']=$_POST['focus_url'];
            $data['focus_image']=$_POST['focus_image'];
            //$data['focus_sort']=$_POST['focus_sort'];
            $data['focus_addtime']=time();
            $flag=M('focusinfo')->where("focus_id=".$focusId)->save($data);
            if($flag){
                jump('编辑轮播成功！',U('Carousel/index'));
            }else{
                jump('编辑轮播失败！',U('Carousel/index'));
            }
        }else{
            $focusInfo=M("focusinfo")->where("focus_id=".$focusId)->find();
            $this->assign('focus',$focusInfo);
            $this->assign('focusOrder',D('Focusinfo')->focus_sort());
            $this->assign('focusType',D('Focusinfo')->focus_type());
            $this->display();
        }
    	
    }


    //轮播删除
    public function Carouseldel(){
    	$focus_id = I('get.focus_id');
    	M('focusinfo')->where(array('focus_id'=>$focus_id))->delete();
    	jump('移除轮播成功',U('Carousel/index'));
    }
	
}