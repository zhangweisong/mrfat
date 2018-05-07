<?php
namespace User\Controller;
use Think\Controller;
class TrainController extends Controller {
	function __construct() {
		//继承父类
		parent::__construct();
	    //判断登录状态 
		$user_id=session("user_id");
		//判断是否在微信中打开
        if (__ACTION == "notify") {//微信回调不验证登录
             
        }else{
			if($user_id==""){//未登录
				header("Location:".U('index/index'));
				exit();
			}
		}

    }
	//空方法，防止报错
	public function _empty(){
        $this->index();
    }
	
	//农业培训
    public function index(){ 
		$where = '1';  
		$count  = M('train')->where($where)->count();
		$Page = new \Think\Page($count,6);
		$piinfo = M('train')
		         ->where($where)
				 ->order('id desc')
				 ->limit($Page->firstRow.','.$Page->listRows)
				 ->select();
	    $arr=array();
		foreach($piinfo as $k=>$v){ 
			 $piinfo[$k]['name'] = str_cut(strip_tags(htmlspecialchars_decode($v['name'])),50);
			 $piinfo[$k]['address'] = str_cut(strip_tags(htmlspecialchars_decode($v['address'])),110);
		}
		if(IS_AJAX){
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('piinfo' => $piinfo));
            } else {
                $this->ajaxReturn(array('piinfo' => array()));
            }
        }else { 
			$this->assign('piinfo',$piinfo);
        } 
		
		$this->display(); 
    }
    
    //详情
    public function xq(){
        $id=I('id');
        $train = M('train')
            ->where('id='.$id)
            ->find();   
        //查看公告
        $notice = M("notice")->where("train_id='".$id."' and n_flag=1")->select();
        $count = M("notice")->where("train_id='".$id."' and n_flag=1")->count();
        //查看职位
        $job = M("train")->find($id);
        $res = explode('|',$job['job']);
        $arr = [];
        foreach($res as $k=>$v){
            $arr[] = M("trainclassfiy")->find($v);
        }
        
        $this->assign('notice',$notice); 
        $this->assign('train',$train); 
        $this->assign('count',$count); 
        $this->assign('arr',$arr); 
        $this->display();  
    } 

    //详情
    public function xqs(){
        $id=I('id');
        $train = M('train')
            ->where('id='.$id)
            ->find();  
        $this->assign('train',$train); 
        $this->display();  
    }     
	
    //新增欠条
	public function add(){
        $data['u_id']=session("user_id");
        $data['t_id']=I("traid");  
        $data['j_id']=I("traid");  
		$data['u_name']=filterEmoji(I("names"),2);  
		$data['u_phone']=filterEmoji(I("phone"),2); 
        $data['u_address']=filterEmoji(I("address"),2); 
		$data['addtime']=time();  
        $data['status']=1;
        $data['remark']="";  
		$res = M("cultivate")->add($data);
		if($res){
			$this->ajaxReturn(array("data"=>1));
		}else{
            $this->ajaxReturn(array("data"=>0));
        } 
	}
    
     //公告
    public function gonggao(){
        $n_id=I('n_id');
        $notice = M('notice')
            ->where('n_id='.$n_id)
            ->find();  
        $this->assign('notice',$notice); 
        $this->display();  
    }   
}