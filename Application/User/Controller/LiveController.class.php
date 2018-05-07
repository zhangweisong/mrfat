<?php
/**
 * Created by PhpStorm.
 * User: DangMengmeng
 * Name: 农技直播控制器
 * Date: 2018/3/20 0020
 * Time: 下午 3:40
 */
namespace User\Controller;
use Think\Controller;

class LiveController extends Controller{
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
        $this->index();
    }

    //农技直播农作物分类
    public function livecropclass(){
        $count = M('Njclassify')
            ->where(['nj_classify_fatherid'=> '0'])
            ->count();
        $this->assign('count',$count);
        $Page       = new \Think\Page($count,10);
        $njclassify=M('Njclassify')
            ->where(['nj_classify_fatherid'=> '0'])
            ->order('nj_classify_id')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        if(IS_AJAX){
            if($Page->totalPages>=$p){
                $this->ajaxReturn(array('njclassify'=>$njclassify),'json');
            }else{
                $this->ajaxReturn(array('njclassify'=>array()),'json');
            }
        }else{
            $this->assign('njclassify',$njclassify);
            $this->assign('count',$count);
            $this->display();
        }

    }
    //农技直播农作物子分类
    public function subclassify(){
        $nj_classify_id=I('get.nj_classify_id')?I('get.nj_classify_id'):0;
        if($nj_classify_id<=0){
            jump_success("该分类不存在！请重新选择",U('live/livecropclass'));
        }
        $this->assign('nj_classify_id',$nj_classify_id);
        $count = M('Njclassify')
            ->where(['nj_classify_fatherid'=> $nj_classify_id])
            ->count();
        $this->assign('count',$count);
        $Page       = new \Think\Page($count,10);
        $subclassify=M('njclassify')
            ->order('nj_classify_id')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->where(['nj_classify_fatherid'=> $nj_classify_id])
            ->select();
        if(IS_AJAX){
            if($Page->totalPages>=$p){
                $this->ajaxReturn(array('subclassify'=>$subclassify),'json');
            }else{
                $this->ajaxReturn(array('subclassify'=>array()),'json');
            }
        }else{
            $this->assign('subclassify',$subclassify);
            $this->assign('count',$count);
            $this->display();
        }
    }
    //直播区间
    public function living(){
        $nj_classify_fatherid=I('get.nj_classify_fatherid')?I('get.nj_classify_fatherid'):0;
        $nj_classify_id=I('get.nj_classify_id')?I('get.nj_classify_id'):0;
        if($nj_classify_fatherid<=0||$nj_classify_id<=0){
            jump_success("该分类不存在！请重新选择",U('live/livecropclass'));
        }
        $this->assign('nj_classify_fatherid',$nj_classify_fatherid);
        $this->assign('nj_classify_id',$nj_classify_id);
        $count = M('Njliving')
            ->where(['nj_classify_one'=> $nj_classify_fatherid,'nj_classify_two'=>$nj_classify_id,'nj_isable'=>1])
            ->count();
        $this->assign('count',$count);
        $Page       = new \Think\Page($count,10);
        $living=M('Njliving')
            ->order('nj_id')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->where(['nj_classify_one'=> $nj_classify_fatherid,'nj_classify_two'=>$nj_classify_id,'nj_isable'=>1])
            ->select();
        foreach ($living as $k=>$v) {
            $image=explode("|",$v['nj_image']);
            $living[$k]['nj_image']=$image[0];
            unset($image);
        }
        if(IS_AJAX){
            if($Page->totalPages>=$p){
                $this->ajaxReturn(array('living'=>$living),'json');
            }else{
                $this->ajaxReturn(array('living'=>array()),'json');
            }
        }else{
            $this->assign('living',$living);
            $this->assign('count',$count);
            $this->display();
        }

    }
}