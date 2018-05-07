<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class CultivateController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录状态
        if (!D('admininfo')->islogin()) {//未登录
           jump('您尚未登录，请先登录！', U('login/login'));

        }
    }

    //空方法，防止报错
    public function _empty() {
        $this->index();
    }
	 //公共success
    public function home_success($msg, $url = "", $wait = 3) {
        $this->assign('msg', $msg);
        $this->assign('url', $url ? $url : U("index/index"));
        $this->assign('wait', $wait);
        $this->display('user/home_success');
        exit();
    }

    //公共error
    public function home_error($msg, $url = "", $wait = 3) {
        $this->assign('msg', $msg);
        $this->assign('url', $url ? $url : U("index/index"));
        $this->assign('wait', $wait);
        $this->display('user/home_error');
        exit();
    }

    //培训报名列表
    public function index() { 
        $where = " 1 ";
        $info = I("info");
        if ($info) {
            @extract($info);
            if ($key) { 
				$key=urldecode(trim($info['key'])); 
                $where .= " and (
					ec_cultivate.u_name LIKE '%" . $key . "%'
					or 	ec_cultivate.u_phone LIKE '%" . $key . "%'	
					or 	ec_train.name LIKE '%" . $key . "%'
					or 	ec_trainclassfiy.name LIKE '%" . $key . "%'
				)";  
                $this->assign('key', $key);
            } 
			 
			//状态
            if ($state) {
				$state=urldecode(trim($info['state']));
                $where .= " and ec_cultivate.status=".$state;
                $this->assign('state_', $state);
            } 
			//时间
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_cultivate.addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_cultivate.addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
        } 
        // 查询满足要求的总记录数
        $count = M('cultivate') 
				->join("left join ec_train on ec_cultivate.u_id=ec_train.id")
                ->join("left join ec_trainclassfiy on ec_cultivate.j_id=ec_trainclassfiy.id")
				->field("ec_cultivate.*,ec_train.name as names,ec_trainclassfiy.name")
                ->where($where)
                ->count();
        $Page = new \Think\Page($count, 20);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info[' . $key . ']'] = urlencode($val);
            }
        }
        $pageshow = $Page->adminshow();
        // 进行分页数据查询
        $cultivate = M('cultivate') 
                ->where($where)
				->join("left join ec_train on ec_cultivate.t_id=ec_train.id")
                ->join("left join ec_trainclassfiy on ec_cultivate.j_id=ec_trainclassfiy.id")
				->field("ec_cultivate.*,ec_train.name as names,ec_trainclassfiy.name")
                ->order("ec_cultivate.id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
			 
        foreach($cultivate as $k=>$v){
            $cultivate[$k]['addtime']=date("Y-m-d H:i:s",$v['addtime']);
        }   
		//状态
		$status = [1=>'申请中',2=>'报名成功',3=>'驳回']; 
		$this->assign('status', $status); 			
        $this->assign('cultivate', $cultivate); 
		$this->assign('pageshow', $pageshow); 
        $this->display();
    }
	
	//详情信息
    public function info() {
        $id = I("get.id"); 
        $cultivate = M('cultivate') 
                ->where("ec_cultivate.id='".$id."'")
				->join("left join ec_train on ec_cultivate.t_id=ec_train.id")
                ->join("left join ec_trainclassfiy on ec_cultivate.j_id=ec_trainclassfiy.id")
				->field("ec_cultivate.*,ec_train.name as names,ec_trainclassfiy.name")
                ->order("ec_cultivate.id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->find();
         $cultivate['addtime']=date("Y-m-d H:i:s",$cultivate['addtime']);        
		//状态
		$status = [1=>'申请中',2=>'报名成功',3=>'驳回']; 
		$this->assign('status', $status); 				
        $this->assign('cultivate', $cultivate); 
        $this->display();
    }
	
	//通过
    public function adopt() {
		$id = I("id"); 
		$this->assign('id', $id); 
		$this->display(); 
	}
	
	//驳回
    public function refuse() {
		$id = I("id"); 
		$this->assign('id', $id); 
		$this->display(); 
	}
	
	//通过
    public function adopted(){ 
        $id=I('post.id'); 
        $res=M('cultivate')->where('id = '.$id)->find();  
        $remark=I('post.remark');
        $data['status']=2;
        $data['remark']=$remark; 
		$flag=M('cultivate')->where('id = '.$id)->save($data);  
        if($flag){
			//增加消息通知
			$message= new \Admin\Model\MessageModel();
			$message->getmessage_($res['u_id'],1,"培训报名通知","您的培训报名申请已通过"); 
            $this->home_success('已通过申请！', U('cultivate/index'));
        }else{
            $this->home_error('通过申请失败！', U('cultivate/index'));
        }
		 
		 
    }
	
	public function refused(){ 
        $id=I('post.id'); 
        $res=M('cultivate')->where('id = '.$id)->find();  
        $remark=I('post.remark');
        $data['status']=3;
        $data['remark']=$remark; 
		$flag=M('cultivate')->where('id = '.$id)->save($data);  
        if($flag){
			//增加消息通知
			$message= new \Admin\Model\MessageModel();
			$message->getmessage_($res['u_id'],1,"培训报名通知","您的培训报名申请已驳回"); 
            $this->home_success('已驳回申请！', U('cultivate/index'));
        }else{
            $this->home_error('驳回申请失败！', U('cultivate/index'));
        }
    } 
	  //删除
    public function del(){
    	$id = I('get.id'); 
    	M('cultivate')->where(array('id'=>$id))->delete();
    	jump('删除成功',U('cultivate/index'));
    }

}
