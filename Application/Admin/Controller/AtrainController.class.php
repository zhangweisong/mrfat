<?php
/**
 * Created by PhpStorm.
 * User: DangMengmeng
 * Date: 2018/2/28 0028
 * Time: 下午 5:06
 */
namespace Admin\Controller;
use Think\Controller;

class AtrainController extends Controller{
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
    public function index(){
        $where=" 1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($name){
                $info['name']=urldecode(trim($info['name']));
                $where.=" and name like '%".$info['name']."%' ";
                $this->assign('names',$info['name']);
            }  
        }
        $dgtpaper=M('trainclassfiy');
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
            ->order('id desc')
            ->select();
        $this->assign('dgtpInfo',$dgtpInfo);
        $this->assign('pageshow',$pageshow); 
        $this->display();
    }

    //添加农业数字报
    public function add(){
        if (IS_POST){
            $data['name']=trim($_POST['name']); 
            $addInfo=M('trainclassfiy')->add($data);
            if($addInfo){
                jump('添加成功！',U('atrain/index'));
            }else{
                jump('添加失败！',U('atrain/index'));
            }
        }else{
            $this->display();
        }
    }

    //修改数字报
    public function edit(){
        $id = I("get.id");
        if (IS_POST) {
            $data = I("info");
            $flag=M("trainclassfiy")->where("id = " .$id)->save($data);
            if($flag){
                jump('修改成功！', U('atrain/index'));
            }else{
                jump('您未做任何修改！', U('atrain/index'));
            }
        } else {
            $cropInfo=M('trainclassfiy')->find($id);
            $this->assign('crop',$cropInfo);
            $this->display();
        }
    }

    //删除数字报
    public function del(){
        $id = I("get.id");
        $flag=M("trainclassfiy")->where("id = " .$id)->delete();
        if($flag){
            jump('删除成功！', U('atrain/index'));
        }else{
            jump('删除失败！', U('atrain/index'));
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
 
}