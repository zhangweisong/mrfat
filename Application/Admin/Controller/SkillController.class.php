<?php
/**
 * Created by PhpStorm.
 * User: DangMengmeng
 * Date: 2018/3/2 0002
 * Time: 上午 11:45
 */
namespace Admin\Controller;
use Think\Controller;

class SkillController extends Controller{
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

    //农技知识分类
    public function index(){
        $fid=I('get.skc_id')?I('get.skc_id'):0;
        if($fid){
            $fatherName=M('slskillclass')->find($fid);
            $father_name=$fatherName['skc_title'];
            $this->assign('father_name',$father_name);

        }
        $this->assign('fid',$fid);
        $where=" 1 and skc_fid = ".$fid;
        $info=I("info");
        if ($info){
            @extract($info);
            if($skc_title){
                $info['skc_title']=urldecode(trim($info['skc_title']));
                $where.=" and skc_title like '%".$info['skc_title']."%' ";
                $this->assign('skc_title',$info['skc_title']);
            }
            if($skc_isable){
                $info['skc_isable']=urldecode(trim($info['skc_isable']));
                $where.=" and skc_isable = ".$info['skc_isable'];
                $this->assign('skc_isable',$info['skc_isable']);
            }
        }
        $skillclass=M('slskillclass');
        // 查询满足要求的总记录数
        $count      = $skillclass->where($where)->count();
        $Page       = new \Think\Page($count,15);
        //分页跳转的时候保证查询条件
        if($info){
            foreach($info as $key=>$val) {
                $Page->parameter['info['.$key.']']   =   urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        // 进行分页数据查询
        $skillInfo = $skillclass
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('skc_id desc')
            ->select();
        foreach ($skillInfo as $k =>$v){
            $skillInfo[$k]['childcount']=M('slskillclass')->where('skc_fid = '.$v['skc_id'])->count();
            $skillInfo[$k]['skc_remarks']=str_cut($v['skc_remarks'], 20);
        }
        $this->assign('skill',$skillInfo);
        $this->assign('cpc_isable',D("slcropclass")->cpc_isable());
        $this->assign('pageshow',$pageshow);
        $this->display();
    }
    //添加农技知识分类
    public function add(){
        $fid = I("get.skc_fid") ? I("get.skc_fid") : 0;
        if($fid){
            $fatherName=M('slskillclass')->find($fid);
            $father_name=$fatherName['skc_title'];
            $this->assign('father_name',$father_name);
        }
        $this->assign('fid',$fid);
        if (IS_POST){
            $data['skc_title']=trim($_POST['skc_title']);
            $data['skc_image']=trim($_POST['skc_image']);
            $data['skc_remarks']=trim($_POST['skc_remarks']);
            $data['skc_fid']=trim($_POST['skc_fid'])?trim($_POST['skc_fid']):0;
            $data['skc_url']=trim($_POST['skc_url']);
//            dd($data);
            //判断分类是否存在
            $classcount=M('slskillclass')->where(' skc_title = "'.$data['skc_title'].'" ')->count();
            if($classcount>0){
                jump('分类添加失败，此分类已存在！',U('skill/index'));
            }
            $addInfo=M('slskillclass')->add($data);
            if($addInfo){
                jump('农技知识分类添加成功！',U('skill/index',array('skc_id' =>$fid)));
            }else{
                jump('农技知识分类添加失败！',U('skill/index',array('skc_id' =>$fid)));
            }
        }else{
            $this->display();
        }
    }

    //修改农技知识分类
    public function edit(){
        $skc_id = I("get.skc_id");
        $faInfo=M('slskillclass')->find($skc_id);
        if (IS_POST) {
            $data = I("info");
            //判断分类名称是否存在
            $classcount=M('slskillclass')->where(" skc_title = '".$data['skc_title']."' and skc_id!=".$skc_id )->count();
            if($classcount>0){
                jump('修改失败，此分类已存在！',U('skill/index',array('skc_id' =>$faInfo['skc_fid'])));
            }
            $flag=M("slskillclass")->where("skc_id = " .$skc_id)->save($data);
            if($flag){
                jump('修改农技知识分类成功！', U('skill/index',array('skc_id' =>$faInfo['skc_fid'])));
            }else{
                jump('您未做任何修改！', U('skill/index',array('skc_id' =>$faInfo['skc_fid'])));
            }
        } else {
            $fid=$faInfo['skc_fid'];
            if($fid){
                $faNmae=M('slskillclass')->find($fid);
                $faname=$faNmae['skc_title'];
                $f_id=$faNmae['skc_id'];
                $this->assign('father_name',$faname);
                $this->assign('fid',$f_id);
            }
            $this->assign('skill',$faInfo);

            $this->display();
        }
    }

    //删除农技知识分类
    public function del(){
        $skc_id = I("get.skc_id");
        $faInfo=M('slskillclass')->find($skc_id);
        $fid=$faInfo['skc_fid'];
        $flag=M("slskillclass")->where("skc_id = " .$skc_id)->delete();;
        if($flag){
            jump('删除农技知识分类成功！', U('skill/index',array('skc_id' =>$fid)));
        }else{
            jump('删除农技知识分类失败！', U('skill/index',array('skc_id' =>$fid)));
        }
    }
    //判断农技知识分类的存在性
    public function existname(){
        $skc_id=I("get.skc_id");
        $param=trim(I("param"));
        if(!$param){
            $status="n";
            $info="参数不正确";
        }else{
            if($skc_id){
                $count=M("slskillclass")->where("skc_title = '".$param."' and skc_id!='".$skc_id."'")->count();
            }else{
                $count=M("slskillclass")->where("skc_title = '".$param."'")->count();
            }
            if($count){//存在，验证失败
                $status="n";
                $info="此分类已存在";
            }else{//不存在，验证成功
                $status="y";
                $info="此分类不存在，可以添加";
            }
        }
        $this->ajaxReturn(array('status'=>$status,'info'=>$info));
    }

    //农技知识
    public function skill(){
        $where=" 1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($sk_title){
                $info['sk_title']=urldecode(trim($info['sk_title']));
                $where.=" and sk_title like '%".$info['sk_title']."%' ";
                $this->assign('sk_title',$info['sk_title']);
            } 
            //1
            if($sk_cropclass_id){
                $info['sk_cropclass_id']=urldecode(trim($info['sk_cropclass_id']));
                $where.=" and sk_cropclass_id = ".$info['sk_cropclass_id'];
                $cropSubClass=M('slcropclass')->where('cpc_fid = '.$sk_cropclass_id.' and cpc_isable = 1')->field('cpc_id,cpc_title')->select();
                $this->assign('cropsubclass_',$cropSubClass);
                $this->assign('sk_cropclass_id',$info['sk_cropclass_id']);
            }
            //2
            if($sk_crop_id){
                $info['sk_crop_id']=urldecode(trim($info['sk_crop_id']));
                $where.=" and sk_crop_id = ".$info['sk_crop_id'];
                $this->assign('sk_crop_id',$info['sk_crop_id']);
            }
            //3
            if($sk_skillclass_id){
                $info['sk_skillclass_id']=urldecode(trim($info['sk_skillclass_id']));
                $where.=" and sk_skillclass_id = ".$info['sk_skillclass_id'];
                $skillSubClass=M('slskillclass')->where('skc_fid = '.$sk_skillclass_id)->field('skc_id,skc_title')->select();
                $this->assign('skillSubClass_',$skillSubClass);
                $this->assign('sk_skillclass_id',$info['sk_skillclass_id']);
            }
            //4
            if($sk_skillsubclass_id){
                $info['sk_skillsubclass_id']=urldecode(trim($info['sk_skillsubclass_id']));
                $where.=" and sk_skillsubclass_id = ".$info['sk_skillsubclass_id'];
                $this->assign('sk_skillsubclass_id',$info['sk_skillsubclass_id']);
            }
            //开始时间
            if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and sk_addtime >=".strtotime($info['starttime']."00:00:00")." ";
                $this->assign('starttime',$info['starttime']);
            }
            //结束时间
            if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and sk_addtime <=".strtotime($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
            //审核状态
            if($sk_isable){
                $info['sk_isable']=urldecode(trim($info['sk_isable']));
                $where.=" and sk_isable = ".$info['sk_isable'];
                $this->assign('sk_isable',$info['sk_isable']);
            }
        }
        $skill=M('slcropskill');
        // 查询满足要求的总记录数
        $count      = $skill->where($where)->count();
        $Page       = new \Think\Page($count,15);
        //分页跳转的时候保证查询条件
        if($info){
            foreach($info as $key=>$val) {
                $Page->parameter['info['.$key.']']   =   urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        // 进行分页数据查询
        $skillInfo = $skill
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('sk_id desc')
            ->select();

        //获取农作物一级分类
        $cropclass=M('slcropclass')->where('cpc_fid = 0 and cpc_isable = 1')->field('cpc_id,cpc_title')->select();
        $this->assign('cropclass1',$cropclass);
        //获取农技知识一级分类
        $cropskill=M('slskillclass')->where('skc_fid = 0 and skc_isable = 1 ')->field('skc_id,skc_title')->select();
        $this->assign('cropskills',$cropskill);
        $this->assign('skill',$skillInfo);
        $this->assign('pageshow',$pageshow);
        $this->assign('cpc_isable',D("slcropclass")->cpc_isable());
        $this->assign('cropclass',D("slcropclass")->cropclass());
        $this->assign('cropName',D("slcropclass")->cropName());
        $this->assign('cropskill',D("slcropclass")->cropskill());
        $this->display();
    }

    //添加农技知识
    public function addskill(){
        if (IS_POST){
            $data['sk_cropclass_id']=trim($_POST['sk_cropclass_id']);
            $data['sk_crop_id']=trim($_POST['sk_crop_id']);
            $data['sk_skillclass_id']=trim($_POST['sk_skillclass_id']);
            $data['sk_skillsubclass_id']=trim($_POST['sk_skillsubclass_id']);
            $data['sk_title']=trim($_POST['sk_title']);
            $data['sk_img']=trim($_POST['sk_img']);
            $data['sk_addtime']=time();
            $data['sk_content']=trim($_POST['sk_content']);
            $data['sk_isable']=1;
            $data['sk_view']=trim($_POST['sk_view']);
            $data['sk_vedio_url']=trim($_POST['sk_vedio_url']);
            //判断分类是否存在
            $count=M('slcropskill')->where(' sk_title = "'.$data['sk_title'].'" ')->count();
            if($count>0){
                jump('农技知识添加失败，此名称已存在！',U('skill/skill'));
            }
            $addInfo=M('slcropskill')->add($data);
            if($addInfo){
                jump('农技知识添加成功！',U('skill/skill'));
            }else{
                jump('农技知识添加失败！',U('skill/skill'));
            }
        }else{
            //获取农作物一级分类
            $cropclass=M('slcropclass')->where('cpc_fid = 0 and cpc_isable = 1')->field('cpc_id,cpc_title')->select();
            $this->assign('cropclass',$cropclass);
            //获取农技知识一级分类
            $cropskill=M('slskillclass')->where('skc_fid = 0 and skc_isable = 1 ')->field('skc_id,skc_title')->select();
            $this->assign('cropskill',$cropskill);
            $this->display();
        }
    }

    //根据选定的农作物一级分类选取其二级分类
    public function getCropSubClass(){
        $cpc_fid=I('get.cpc_fid');
        $cropSubClass=M('slcropclass')->where('cpc_fid = '.$cpc_fid.' and cpc_isable = 1')->field('cpc_id,cpc_title')->select();
        $this->ajaxReturn($cropSubClass);
    }

    //根据选定的农机知识一级分类获取其二级分类
    public function getSkillSubClass(){
        $skc_fid=I('get.skc_fid');
        $skillSubClass=M('slskillclass')->where('skc_isable = 1 and skc_fid = '.$skc_fid)->field('skc_id,skc_title')->select();
        $this->ajaxReturn($skillSubClass);
    }

    public function existskill(){
        $sk_id=I("get.sk_id");
        $param=trim(I("param"));
        if(!$param){
            $status="n";
            $info="参数不正确";
        }else{
            if($sk_id){
                $count=M("slcropskill")->where("sk_title = '".$param."' and sk_id!='".$sk_id."'")->count();
            }else{
                $count=M("slcropskill")->where("sk_title = '".$param."'")->count();
            }
            if($count){//存在，验证失败
                $status="n";
                $info="此标题已存在";
            }else{//不存在，验证成功
                $status="y";
                $info="此标题不存在，可以添加";
            }
        }
        $this->ajaxReturn(array('status'=>$status,'info'=>$info));
    }



    //修改农技知识
    public function editskill(){
        $sk_id = I("get.sk_id");
        if (IS_POST) {
            $data = I("info");
            //判断分类名称是否存在
            $classcount=M('slcropskill')->where(" sk_title = '".$data['sk_title']."' and sk_id!=".$sk_id )->count();
            if($classcount>0){
                jump('修改失败，此标题已存在！',U('skill/skill'));
            }
            $flag=M("slcropskill")->where("sk_id = " .$sk_id)->save($data);
            if($flag){
                jump('修改农技知识成功！', U('skill/skill'));
            }else{
                jump('您未做任何修改！', U('skill/skill'));
            }
        } else {
            $skillInfo=M('slcropskill')->find($sk_id);
            $sk_cropclass_id=$skillInfo['sk_cropclass_id'];
            $cropSubClass=M('slcropclass')->where('cpc_fid = '.$sk_cropclass_id.' and cpc_isable = 1')->field('cpc_id,cpc_title')->select();
            $this->assign('cropsubclass_',$cropSubClass);
            $sk_skillclass_id=$skillInfo['sk_skillclass_id'];
            $skillSubClass=M('slskillclass')->where('skc_fid = '.$sk_skillclass_id)->field('skc_id,skc_title')->select();
            $this->assign('skillSubClass_',$skillSubClass);
            $this->assign('skill',$skillInfo);
            //获取农作物一级分类
            $cropclass=M('slcropclass')->where('cpc_fid = 0 and cpc_isable = 1')->field('cpc_id,cpc_title')->select();
            $this->assign('cropclass',$cropclass);
            //获取农技知识一级分类
            $cropskill=M('slskillclass')->where('skc_fid = 0 and skc_isable = 1  ')->field('skc_id,skc_title')->select();
            $this->assign('cropskill',$cropskill);
            $this->display();
        }
    }
    //删除农技知识
    public function delskill(){
        $sk_id = I("get.sk_id");
        $flag=M("slcropskill")->where("sk_id = " .$sk_id)->delete();;
        if($flag){
            jump('删除农技知识成功！', U('skill/skill'));
        }else{
            jump('删除农技知识失败！', U('skill/skill'));
        }
    }
   //ajax处理状态
    public function ajaxable_on(){
        $id=I("get.id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "sk_isable"://on
                M("slcropskill")->where('sk_id='.$id)->save(array("sk_isable"=>1));
                break;
        }
        $this->ajaxReturn(array("flag"=>"1"));
    }

    public function ajaxable_off(){
        $id=I("get.id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "sk_isable"://off
                M("slcropskill")->where('sk_id='.$id)->save(array("sk_isable"=>2));
                break;
        }
        $this->ajaxReturn(array("flag"=>"1"));
    }

    //ajax处理状态
    public function ajaxable_on_(){
        $id=I("get.id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "skc_isable"://on
                M("slskillclass")->where('skc_id='.$id)->save(array("skc_isable"=>1));
                break;
        }
        $this->ajaxReturn(array("flag"=>"1"));
    }

    public function ajaxable_off_(){
        $id=I("get.id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "skc_isable"://off
                M("slskillclass")->where('skc_id='.$id)->save(array("skc_isable"=>2));
                break;
        }
        $this->ajaxReturn(array("flag"=>"1"));
    }
    //ajax处理状态
    public function ajaxable_onn(){
        $id=I("get.id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "pic_isable"://on
                M("slpolicyintprtclass")->where('pic_id='.$id)->save(array("pic_isable"=>1));
                break;
        }
        $this->ajaxReturn(array("flag"=>"1"));
    }

    public function ajaxable_offf(){
        $id=I("get.id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "pic_isable"://off
                M("slpolicyintprtclass")->where('pic_id='.$id)->save(array("pic_isable"=>2));
                break;
        }
        $this->ajaxReturn(array("flag"=>"1"));
    }

    //判断农技知识标题的存在性修改
    public function name_es(){
        $classify_name = urldecode(trim(I("classify_name")));
        $classify_id = I("classify_id");
        $count = M("slcropskill")->where("sk_id!='".$classify_id."' and sk_title ='".$classify_name."'")
            ->count();
        if ($count) {
            $response = array(
                'errno' => 0,
            );
        } else {
            $response = array(
                'errno' => -1,
            );
        }
        echo json_encode($response);
    }
    //判断农技知识分类的存在性修改
    public function name_ess(){
        $classify_name = urldecode(trim(I("classify_name")));
        $classify_id = I("classify_id");
        $count = M("slskillclass")->where("skc_id!='".$classify_id."' and skc_title ='".$classify_name."'")
            ->count();
        if ($count) {
            $response = array(
                'errno' => 0,
            );
        } else {
            $response = array(
                'errno' => -1,
            );
        }
        echo json_encode($response);
    }
}