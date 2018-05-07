<?php
namespace Factory\Controller;
use Think\Controller;
class FactoryController extends Controller {
	function __construct() {
		//继承父类
		parent::__construct();		
		//判断登录状态
		if(!D('Factory')->islogin()){//未登录
			jump('您尚未登录，请先登录！',U('login/login'),3);
		}	
    }
	//空方法，防止报错
	public function _empty(){
        $this->index();
    }
	
	//管理员列表
    public function index(){
		$factory_id=session('factory.factory_id');
		$where="1 and factory_id='".$factory_id."' ";
		$info=I("info");
		if ($info){
			@extract($info);
						
		}
		$Factory=M('Factory');
		
		// 进行分页数据查询
		$listinfo = $Factory
					->where($where)
					->find();
		$this->assign('listinfo',$listinfo);
		$this->display();
    }

	//修改账号密码
	public function resetpswadmin(){
		$factory_id=session('factory.factory_id');
		if (IS_POST){
			$factory_id=I("get.factory_id");
			$info=I("info");
			$oldpassword=MD5(trim($info['oldpassword']));
			if(trim($info['oldpassword'])==trim($info['factory_pwd'])){
				jump('原密码和新密码不能一直，修改失败！',U('factory/resetpswadmin'),3);
				exit();
			}
			$factory=M("factory")->where("factory_id=".$factory_id)->find();
			if($factory['factory_pwd']==$oldpassword){
				$info_['factory_pwd']=MD5(trim($info['newpassword']));
				M('factory')->where('factory_id='.$factory_id)->save($info_);
				jump('密码修改成功！',U('factory/resetpswadmin'),3);			
			}else{
				jump('密码修改失败，原密码不正确！',U('factory/resetpswadmin'),3);
			}			
		}else{
			$factory=M("factory")->where("factory_id=".$factory_id)->find();
			$this->assign('factory',$factory);
			$this->display();
		}
	}
}