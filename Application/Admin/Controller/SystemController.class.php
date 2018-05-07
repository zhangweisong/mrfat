<?php
namespace Admin\Controller;
use Think\Controller;
class SystemController extends Controller {

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
	
	//系统设置列表
    public function index(){
		$where="1 ";
		$setinfo=M('Setinfo');
		// 查询满足要求的总记录数
		$count      = $setinfo->where($where)->count();
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$listinfo = $setinfo
					->where($where)
					->order('set_id desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$this->assign('listinfo',$listinfo);
		$this->assign('pageshow',$pageshow);
		$this->display();
    }
	//添加配置
	public function addinfo(){
		if (IS_POST){
			$info=I("info");
			$res=M('setinfo')->add($info);
			if($res){
				jump('添加参数设置成功！',U('system/index'));
			}else{
				jump('添加参数设置失败！',U('system/index'));
			}
		}else{
			$this->display();
		}
    }
    //编辑配置
	public function editinfo(){
		$set_id=I("get.set_id");
		if (IS_POST){
			$info=I("info");
			$info['set_remark']=str_replace("，",",",$info['set_remark']);
			$flag=M("setinfo")->where("set_id=".$set_id)->save($info);
			jump('编辑参数设置成功！',U('system/index'));
			
		}else{
			$setinfo=M("setinfo")->where("set_id=".$set_id)->find();
			$this->assign('setinfo',$setinfo);
			$this->display();
		}
    }
	//存在性
  public function exist(){
		$param=I("param");
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{
			$count=M("Setinfo")->where("set_key = '".$param."'")->count();
			if($count){//存在，验证失败
				$status="n";
				$info="此名称已存在";
			}else{//不存在，验证成功
				$status="y";
				$info="此名称不存在，可以增加";
			}
		}
		$this->ajaxReturn(array('status'=>$status,'info'=>$info));
	}

}