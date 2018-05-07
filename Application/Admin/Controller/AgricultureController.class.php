<?php
/**
 * Created by PhpStorm.
 * User: DangMengmeng
 * Date: 2018/2/28 0028
 * Time: 下午 5:06
 */
namespace Admin\Controller;
use Think\Controller;

class AgricultureController extends Controller{
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

    //农业数字报
    public function digitalpaper(){
        $where=" 1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($slp_title){
                $info['slp_title']=urldecode(trim($info['slp_title']));
                $where.=" and slp_title like '%".$info['slp_title']."%' ";
                $this->assign('slp_title',$info['slp_title']);
            }
            //开始时间
            if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and slp_addtime >=".strtotime($info['starttime']."00:00:00")." ";
                $this->assign('starttime',$info['starttime']);
            }
            //结束时间
            if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and slp_addtime <=".strtotime($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
            if($slp_available){
                $info['slp_available']=urldecode(trim($info['slp_available']));
                $where.=" and slp_available = ".$info['slp_available'];
                $this->assign('slp_available',$info['slp_available']);
            }
        }
        $dgtpaper=M('sldigitalpaper');
        // 查询满足要求的总记录数
        $count      = $dgtpaper->where($where)->count();
        $Page       = new \Think\Page($count,15);
        //分页跳转的时候保证查询条件
        if($info){
            foreach($info as $key=>$val) {
                $Page->parameter['info['.$key.']']   =   urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        // 进行分页数据查询
        $dgtpInfo = $dgtpaper
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('slp_id desc')
            ->select();
        $this->assign('dgtpInfo',$dgtpInfo);
        $this->assign('pageshow',$pageshow);
        $this->assign('cpIsable',D("slcropclass")->cpc_isables());
        $this->display();
    }

    //添加农业数字报
    public function adddgtpaper(){
        if (IS_POST){
            $data['slp_title']=trim($_POST['slp_title']);
            $data['slp_url']=trim($_POST['slp_url']);
            $data['slp_image']=trim($_POST['slp_image']);
            $data['slp_addtime']=time();
            $data['slp_available']=trim($_POST['slp_available']);
            $addInfo=M('sldigitalpaper')->add($data);
            if($addInfo){
                jump('农业数字报添加成功！',U('agriculture/digitalpaper'));
            }else{
                jump('农业数字报添加失败！',U('agriculture/digitalpaper'));
            }
        }else{
            $this->display();
        }
    }

    //修改数字报
    public function editdgtpaper(){
        $slp_id = I("get.slp_id");
        if (IS_POST) {
            $data = I("info");
            $flag=M("sldigitalpaper")->where("slp_id = " .$slp_id)->save($data);
            if($flag){
                jump('修改农业数字报成功！', U('agriculture/digitalpaper'));
            }else{
                jump('您未做任何修改！', U('agriculture/digitalpaper'));
            }
        } else {
            $cropInfo=M('sldigitalpaper')->find($slp_id);
            $this->assign('crop',$cropInfo);
            $this->display();
        }
    }

    //删除数字报
    public function deldgtpaper(){
        $slp_id = I("get.slp_id");
        $flag=M("sldigitalpaper")->where("slp_id = " .$slp_id)->delete();
        if($flag){
            jump('删除农业数字报成功！', U('agriculture/digitalpaper'));
        }else{
            jump('删除农业数字报失败！', U('agriculture/digitalpaper'));
        }
    }
    //正确内容
    //政策解读分类
    public function policyinterclass(){
        $where=" 1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($pic_title){
                $info['pic_title']=urldecode(trim($info['pic_title']));
                $where.=" and pic_title like '%".$info['pic_title']."%' ";
                $this->assign('pic_title',$info['pic_title']);
            }
            if($pic_isable){
                $info['pic_isable']=urldecode(trim($info['pic_isable']));
                $where.=" and pic_isable = ".$info['pic_isable'];
                $this->assign('pic_isable',$info['pic_isable']);
            }
        }
        $policy=M('slpolicyintprtclass');
        // 查询满足要求的总记录数
        $count      = $policy->where($where)->count();
        $Page       = new \Think\Page($count,15);
        //分页跳转的时候保证查询条件
        if($info){
            foreach($info as $key=>$val) {
                $Page->parameter['info['.$key.']']   =   urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        // 进行分页数据查询
        $policyInfo = $policy
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('pic_id desc')
            ->select();
        $this->assign('policyInfo',$policyInfo);
        $this->assign('pageshow',$pageshow);
        $this->assign('cpc_isable',D("slcropclass")->cpc_isable());
        $this->assign('orderInfo',D("slcropclass")->orderInfo());
        $this->display();
    }
    /*
     * 此模块已OK
     * */
    //添加政策分类解读
    public function addpolicyclass(){
        if (IS_POST){
            $data['pic_title']=trim($_POST['pic_title']);
            $data['pic_isable']=trim($_POST['pic_isable']);
            //$data['pic_order']=trim($_POST['pic_order']);
            //判断分类是否存在
            $classcount=M('slpolicyintprtclass')->where(' pic_title = "'.$data['pic_title'].'" ')->count();
            if($classcount>0){
                jump('分类添加失败，此分类已存在！',U('agriculture/policyinterclass'));
            }
            $addInfo=M('slpolicyintprtclass')->add($data);
            if($addInfo){
                jump('政策解读分类添加成功！',U('agriculture/policyinterclass'));
            }else{
                jump('政策解读分类添加失败！',U('agriculture/policyinterclass'));
            }
        }else{
            $this->assign('cpc_isable',D("slcropclass")->cpc_isable());
            $this->assign('orderInfo',D("slcropclass")->orderInfo());
            $this->display();
        }
    }


     /*
     * 此模块已OK
     * */
    //修改政策分类解读
    public function editpolicyclass(){
        $pic_id = I("get.pic_id");
        if (IS_POST) {
            $data = I("info");
            //判断分类名称是否存在
            $classcount=M('slpolicyintprtclass')->where(" pic_title = '".$data['pic_title']."' and pic_id!=".$pic_id )->count();
            if($classcount>0){
                jump('修改失败，此分类已存在！',U('agriculture/policyinterclass'));
            }
            $flag=M("slpolicyintprtclass")->where("pic_id = " .$pic_id)->save($data);
            if($flag){
                jump('修改政策解读分类成功！', U('agriculture/policyinterclass'));
            }else{
                jump('您未做任何修改！', U('agriculture/policyinterclass'));
            }
        } else {
            $policyclass=M('slpolicyintprtclass')->find($pic_id);
            $this->assign('polclass',$policyclass);
            $this->assign('cpc_isable',D("slcropclass")->cpc_isable());
            $this->assign('orderInfo',D("slcropclass")->orderInfo());
            $this->display();
        }
    }
    /*
     * 此模块已OK
     * */
    //删除政策解读分类
    public function delpolicyclass(){
        $pic_id = I("get.pic_id");
        $flag=M("slpolicyintprtclass")->where("pic_id = " .$pic_id)->delete();;
        if($flag){
            jump('删除政策解读分类成功！', U('agriculture/policyinterclass'));
        }else{
            jump('删除政策解读分类失败！', U('agriculture/policyinterclass'));
        }
    }
    //判断政策解读分类的存在性
    public function existploclass(){
        $pic_id=I("get.pic_id");
        $param=trim(I("param"));
        if(!$param){
            $status="n";
            $info="参数不正确";
        }else{
            if($pic_id){
                $count=M("slpolicyintprtclass")->where("pic_title = '".$param."' and pic_id!='".$pic_id."'")->count();
            }else{
                $count=M("slpolicyintprtclass")->where("pic_title = '".$param."'")->count();
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

    //政策解读
    public function policyinter(){
        $where=" 1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($pi_title){
                $info['pi_title']=urldecode(trim($info['pi_title']));
                $where.=" and pi_title like '%".$info['pi_title']."%' ";
                $this->assign('pi_title',$info['pi_title']);
            }
            if($pi_cid){
                $info['pi_cid']=urldecode(trim($info['pi_cid']));
                $where.=" and pi_cid = ".$info['pi_cid'];
                $this->assign('pi_cid',$info['pi_cid']);
            }
            //开始时间
            if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and pi_addtime >=".strtotime($info['starttime']."00:00:00")." ";
                $this->assign('starttime',$info['starttime']);
            }
            //结束时间
            if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and pi_addtime <=".strtotime($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
            if($pi_isable){
                $info['pi_isable']=urldecode(trim($info['pi_isable']));
                $where.=" and pi_isable = ".$info['pi_isable'];
                $this->assign('pi_isable',$info['pi_isable']);
            }
        }
        $policy=M('slpolicyinterprate');
        // 查询满足要求的总记录数
        $count      = $policy->where($where)->count();
        $Page       = new \Think\Page($count,15);
        //分页跳转的时候保证查询条件
        if($info){
            foreach($info as $key=>$val) {
                $Page->parameter['info['.$key.']']   =   urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        // 进行分页数据查询
        $policyInfo = $policy
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('pi_id desc')
            ->select();
        foreach($policyInfo as $k=>$v){
             $res= explode('|',$v['pi_image']);
             $policyInfo[$k]['pi_image'] = $res[0];
        }
        $this->assign('policyInfo',$policyInfo);
        $this->assign('pageshow',$pageshow);
        $this->assign('cpc_isable',D("slcropclass")->cpc_isables());
        $this->assign('policyClass',D("slcropclass")->policyClass());
        $this->display();
    }

    //添加政策解读
    public function addpolicy(){
        if (IS_POST){
            $data['pi_cid']=trim($_POST['pi_cid']);
            $data['pi_title']=trim($_POST['pi_title']);
            $data['pi_image']=trim($_POST['pi_image']);
            $data['pi_url']=trim($_POST['pi_url']);
            $data['pi_addtime']=time();
            $data['pi_content']=trim($_POST['pi_content']);
            $data['pi_isable']=trim($_POST['pi_isable']);
            //判断分类是否存在
            $count=M('slpolicyinterprate')->where(' pi_title = "'.$data['pi_title'].'" ')->count();
            if($count>0){
                jump('政策解读添加失败，此名称已存在！',U('agriculture/policyinter'));
            }
            $addInfo=M('slpolicyinterprate')->add($data);
            if($addInfo){
                jump('政策解读添加成功！',U('agriculture/policyinter'));
            }else{
                jump('政策解读添加失败！',U('agriculture/policyinter'));
            }
        }else{
            $this->assign('cpc_isable',D("slcropclass")->cpc_isables());
            $this->assign('policyClass',D("slcropclass")->policyClass());
            $this->display();
        }
    }
    //判断政策解读分类的存在性
    public function existplotitle(){
        $pi_id=I("get.pi_id");
        $param=trim(I("param"));
        if(!$param){
            $status="n";
            $info="参数不正确";
        }else{
            if($pi_id){
                $count=M("slpolicyinterprate")->where("pi_title = '".$param."' and pi_id!='".$pi_id."'")->count();
            }else{
                $count=M("slpolicyinterprate")->where("pi_title = '".$param."'")->count();
            }

            if($count){//存在，验证失败
                $status="n";
                $info="此名称已存在";
            }else{//不存在，验证成功
                $status="y";
                $info="此名称不存在，可以添加";
            }
        }
        $this->ajaxReturn(array('status'=>$status,'info'=>$info));
    }

    //修改政策解读
    public function editpolicy(){
        $pi_id = I("get.pi_id");
        if (IS_POST) {
            $data = I("info");
            $data['pi_addtime']=time();
            //判断分类名称是否存在
            $count=M('slpolicyinterprate')->where(" pi_title = '".$data['pi_title']."' and pi_id!=".$pi_id )->count();
            if($count>0){
                jump('修改失败，此名称已存在！',U('agriculture/policyinter'));
            }
            $flag=M("slpolicyinterprate")->where("pi_id = " .$pi_id)->save($data);
            if($flag){
                jump('修改政策解读成功！', U('agriculture/policyinter'));
            }else{
                jump('您未做任何修改！', U('agriculture/policyinter'));
            }
        } else {
            $policy=M('slpolicyinterprate')->find($pi_id); 
            $images_ = explode('|', $policy['pi_image']);
            $this->assign('policy',$policy);
            $this->assign('images_',$images_);
            $this->assign('cpc_isable',D("slcropclass")->cpc_isables());
            $this->assign('policyClass',D("slcropclass")->policyClass());
            $this->display();
        }
    }

    //删除政策解读
    public function delpolicy(){
        $pi_id = I("get.pi_id");
        $flag=M("slpolicyinterprate")->where("pi_id = " .$pi_id)->delete();;
        if($flag){
            jump('删除政策解读成功！', U('agriculture/policyinter'));
        }else{
            jump('删除政策解读失败！', U('agriculture/policyinter'));
        }
    }
	
	//ajax处理状态
	public function ajaxable_on(){
		$slp_id=I("get.slp_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "slp_available"://on
				M("sldigitalpaper")->where('slp_id='.$slp_id)->save(array("slp_available"=>1));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	public function ajaxable_off(){
		$slp_id=I("get.slp_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "slp_available"://off
				M("sldigitalpaper")->where('slp_id='.$slp_id)->save(array("slp_available"=>2));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}

    //ajax处理状态
    public function ajaxable_on_(){
        $id=I("get.id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "pi_isable"://on
                M("slpolicyinterprate")->where('pi_id='.$id)->save(array("pi_isable"=>1));
                break;
        }
        $this->ajaxReturn(array("flag"=>"1"));
    }

    public function ajaxable_off_(){
        $id=I("get.id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "pi_isable"://off
                M("slpolicyinterprate")->where('pi_id='.$id)->save(array("pi_isable"=>2));
                break;
        }
        $this->ajaxReturn(array("flag"=>"1"));
    }



    public function name_es(){
        $classify_name = urldecode(trim(I("classify_name")));
        $classify_id = I("classify_id");
        $count = M("slpolicyinterprate")->where("pi_id!='".$classify_id."' and pi_title ='".$classify_name."'")
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