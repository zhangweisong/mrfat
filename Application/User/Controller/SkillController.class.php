<?php
/**
 * Created by PhpStorm.
 * User: DangMengmeng
 * Name: 农技知识控制器
 * Date: 2018/3/19 0019
 * Time: 下午 1:46
 */
namespace User\Controller;
use Think\Controller;

class SkillController extends Controller{
    function __construct() {
        //继承父类
        parent::__construct();
        $user_id = session("user_id");
        //判断登录
        if ($user_id == "") {//未登录
            header("Location:" . U('index/index'));
            exit();
        }
    }

    public function _empty() {
        $this->skillclass();
    }

    /*农技知识分类 农技知识 一级分类
        1 视频
        2 图文
        3 病虫害
    */
    public function skillclass(){
        $classtype = I('get.classtype')?I('get.classtype'):1; 
        $this->assign('classtype',$classtype);
        $where = 'skc_fid='.$classtype;
        $count = M('slskillclass')
            ->where($where)
            ->count();
        $this->assign('count',$count);
        $Page       = new \Think\Page($count,10);
        $skillclass=M('slskillclass')
            ->where($where)
            ->order('skc_id ASC')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        if(IS_AJAX){
            if($Page->totalPages>=$p){
                $this->ajaxReturn(array('skillclass'=>$skillclass),'json');
            }else{
                $this->ajaxReturn(array('skillclass'=>array()),'json');
            }
        }else{
            $this->assign('skillclass',$skillclass);
        }
        $this->display();
    }
    //农技知识子分类 二级分类
    public function subclass(){
        $fid=I('get.fid');  
        $where = 'skc_fid='.$fid;
        $count = M('slskillclass')
            ->where($where)
            ->count();
        $this->assign('count',$count);
        $Page       = new \Think\Page($count,10);
        $subclass=M('slskillclass')
            ->order('skc_id ASC')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->where($where)
            ->select();
        if(IS_AJAX){
            if($Page->totalPages>=$p){
                $this->ajaxReturn(array('subclass'=>$subclass),'json');
            }else{
                $this->ajaxReturn(array('subclass'=>array()),'json');
            }
        }else{
            $this->assign('subclass',$subclass);
        }
        $this->display();
    }

    //农技知识列表 某二级分类下的
    public function skilllist(){
        //如果是视频 fid=1 显示videolist
        //农作物二级分类
        $skc_id=I('get.skc_id'); //类别id 
        $this->assign('skc_id',$skc_id);
        $where='ec_slcropskill.sk_three='.$skc_id; 
        $count = M('slcropskill')
            ->where($where)
            ->count();
        $this->assign('count',$count);
        $Page = new \Think\Page($count,8);
        $listinfo=M('slcropskill')
            ->where($where)
            ->limit($Page->firstRow,$Page->listRows) 
            ->order('sk_view desc')
            ->select();
        if(IS_AJAX){
            if($Page->totalPages>=$p){
                $this->ajaxReturn(array('listinfo'=>$listinfo),'json');
            }else{
                $this->ajaxReturn(array('listinfo'=>array()),'json');
            }
        }else{
            $this->assign('listinfo',$listinfo);
            if($this->gettype($skc_id) == 1){//视频 
                $this->display('videolist'); 
            }else{
                $this->display(); 
            }
        } 
    }

    //农技知识详情页
    public function detail(){
        $sk_id=I('get.sk_id'); 
        $this->viewplus($sk_id);
        $skillInfo=M('slcropskill')->find($sk_id); 
        $skillInfo['sk_addtime']=date('Y-m-d',$skillInfo['sk_addtime']);
        $this->assign('skill',$skillInfo);
        $this->display(); 
    }

    //视频详情页
    public function videodetails(){
        $sk_id=I('get.sk_id');
        $this->viewplus($sk_id);
        $video=M('slcropskill')->find($sk_id);
        $video['sk_addtime']=date('Y-m-d',$video['sk_addtime']);
        $this->assign('video',$video);
        $this->display();
    }

    public function gettype($kid){
        $current = M("slskillclass")->find($kid);
        $father = M("slskillclass")->find($current['skc_fid']);
        $father_f_id = $father['skc_fid'];
        return($father_f_id);
    }
    public function viewplus($kid){
        $father = M("slcropskill")->where("sk_id=$kid")->setInc("sk_view");
    }
}
