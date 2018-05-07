<?php
namespace Agcy\Controller;
use Think\Controller;
class IndexController extends Controller {
	function __construct() {
		//继承父类
		parent::__construct(); 
		$agcy_id=session("agcy_id"); 
		if($agcy_id==""){//未登录
			header("Location:".U('login/login'));
			exit();
		}
    }
	//空方法，防止报错
	public function _empty(){
        $this->index();
    } 
	
	public function index(){
		//$agcy_id=I("agcy_id");
		$agcy_id = I('agcy_id') ? I('agcy_id') : session("agcy_id");
		$agcy = M("agcy")->where('agcy_id=' .$agcy_id)->find();
		$res = explode('|',$agcy['agcy_face_images']);
		$agcy_face_images = $res[0];
		//查询今日订单
		$begintime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y'))));
		$endtime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1)); 
		$ordernum = M('orderinfo')->where("agcy_id='".$agcy_id."' and order_addtime>'".$begintime."' and order_addtime<'".$endtime."' ")->count(); 
        //$ordermoney = M('orderinfo')->where("agcy_id='".$agcy_id."' and order_state!=1")->sum('order_money');   
		//查询是否有未读消息
		$message = M('message')->where('msg_type=2 and msg_flag=1 and agcy_id='.$agcy_id)->count();
		$this->assign("agcy",$agcy); 
		$this->assign("message",$message); 
		$this->assign("agcy_face_images",$agcy_face_images); 
		$this->assign("agcy_id",$agcy_id);
		$this->assign("ordernum",$ordernum);
        //$this->assign("ordermoney",$ordermoney);
		$this->display();
	}
	
	//店铺信息
	public function information(){
		$agcy_id=session("agcy_id");
		$agcy = M("agcy")->where('agcy_id=' .$agcy_id)->find();
		$this->assign("agcy",$agcy); 
		$this->display();
	}
	
	//编辑店铺信息
	public function editshop(){
		$agcy_id = I("agcy_id");
		$data['agcy_address'] = filterEmoji(I("agcy_address"),2);
		$data['agcy_tel'] = I("agcy_tel");
        $data['agcy_manager'] = filterEmoji(I("agcy_manager"),2);
		$data['agcy_slogan'] = filterEmoji(I("agcy_slogan"),2);
		$data['agcy_logo'] = I("agcy_logo");
		$data['agcy_face_images'] = I("agcy_face_images");
		$res = M("agcy")->where('agcy_id='.$agcy_id)->save($data);
		if($res){
			$this->ajaxReturn(array("data"=>1));
		}else{
			$this->ajaxReturn(array("data"=>0));
		}
	}
	
	
	public function shangjiaxinxi(){
		$agcy_id=session("agcy_id");
		$agcy = M("agcy")->where('agcy_id=' .$agcy_id)->find();
		$this->assign("agcy",$agcy);  
		$this->display();
	}
	
	//欠条
	public function qiantiaoguanli(){
		$agcy_id=session("agcy_id");
		$where=" 1 and ec_nopay.acgy_id='".$agcy_id."' "; 
		$key = trim(I('key'));
        if ($key) {
            $where .= " and ec_nopay.name LIKE '%".$key."%' ";
            $this->assign('key', $key);
        }
		$count = M('nopay') 
			->where($where)
			->count();
		$Page = new \Think\Page($count,4);
		
		$nopay = M("nopay")
			->where($where)
			->limit($Page->firstRow.','.$Page->listRows)
			->order("id desc")
			->select();
		foreach($nopay as $k=>$v){
			$nopay[$k]['time'] = date('Y-m-d H:i:s',$v['addtime']);
			$nopay[$k]['remark'] =str_cut($v['remark'],80);
		} 
		 
		if(IS_AJAX){
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('nopay' => $nopay), 'json');
            } else {
                $this->ajaxReturn(array('nopay' => array()), 'json');
            }
        }else {
			$this->assign('nopay', json_encode(array('nopay' => $nopay)));
            $this->display();
        }
	}
	
	//欠条详情
	public function qiantiaoxiangq(){
		$id=I('get.id');
		$nopayinfo = M("nopay")->where('id=' .$id)->find(); 
		$nopayinfo['time'] = date('Y-m-d H:i:s',$nopayinfo['addtime']); 
		$this->assign("nopayinfo",$nopayinfo);  
		$this->display(); 
	}
	
	//新增欠条
	public function addqiantiao(){ 
		$this->display(); 
	}
	
	//新增欠条
	public function add(){
		$data['name']=filterEmoji(I("name"),2);  
		$data['remark']=filterEmoji(I("remark"),2); 
		$data['acgy_id']=session("agcy_id");
		$data['addtime']=time();  
		$res = M("nopay")->add($data);
		if($res){
			$this->ajaxReturn(array("data"=>1));
		}else{
            $this->ajaxReturn(array("data"=>0));
        } 
	}
	
	//编辑欠条
	public function bianjiqiantiao(){
		$id=I('get.id');
		$nopayinfo = M("nopay")->where('id=' .$id)->find();  
		$this->assign("nopayinfo",$nopayinfo); 
		$this->display();  
	}
	
	
	//编辑欠条
	public function bianji(){
		$id=I('get.id');
		$data['name']=filterEmoji(I("name"),2);  
		$data['remark']=filterEmoji(I("remark"),2);
		$res = M("nopay")->where('id=' .$id)->save($data);
		if($res){
			$this->ajaxReturn(array("data"=>1));
		}else{
            $this->ajaxReturn(array("data"=>0));
        }  
	}
	
	
	//删除欠条
	public function delqiantiao(){
		$id=I('get.id');
		$nopayinfo = M("nopay")->where('id=' .$id)->delete();
        if($nopayinfo){
            $this->ajaxReturn(array('data' => 1), 'json');
        }else{
            $this->ajaxReturn(array('data' => 0), 'json');
        }   
	}
	
	//营业收入 
	public function yingyeshouru(){
		$agcy_id=session("agcy_id");
		$agcy = M("agcy")->where('agcy_id=' .$agcy_id)->find();
		//今日营业收入
		$begintime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y'))));
		$endtime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1)); 
		$ordermoney = M('orderinfo')->where("agcy_id='".$agcy_id."' and order_addtime>'".$begintime."' and order_addtime<'".$endtime."' and order_state!=1")->sum('agcy_money'); 
        //$money = M('orderinfo')->where("agcy_id='".$agcy_id."' and order_state!=1")->sum('order_money'); 
		$this->assign("agcy",$agcy);
		$this->assign("ordermoney",$ordermoney);
        //$this->assign("money",$money);
		$this->display();
	}
	
	//申请提现 
	public function shenqingtixian(){
		$agcy_id=I("agcy_id");
		$agcy = M("agcy")->where('agcy_id=' .$agcy_id)->find();
		//手续费
		$pop = M('setinfo')->where("set_key='".agcy_change."'")->find();
		$this->assign('pop',$pop);
		$this->assign("agcy",$agcy);
		$this->display();
	}
	
	//提交申请提现 
	public function shenqing(){ 
		$data['agcy_id']=I("agcy_id");
		$money = I("agcy_pop_money");
		//手续费
		$pop = M('setinfo')->where("set_key='".agcy_change."'")->find();
		$fact_money = abs(round($money-($money*$pop['set_value']/100),2));
        $agcy_pop_routine = abs(round($money*$pop['set_value']/100,2)); 
		$data['agcy_pop_money']=I("agcy_pop_money"); 
		$data['agcy_pop_fact']=$fact_money; 
        $data['agcy_pop_routine']=$agcy_pop_routine; 
		$data['agcy_pop_name']=filterEmoji(I("agcy_pop_name"),2);
		$data['agcy_pop_account']=filterEmoji(I("agcy_pop_account"),2);
		$data['agcy_pop_code']=I("agcy_pop_code");
		$data['agcy_pop_phone']=I("agcy_pop_phone");
		$data['agcy_pop_idcard']=I("agcy_pop_idcard"); 
		$data['agcy_pop_remarks']=filterEmoji(I("agcy_pop_remarks"),1); 
		$data['agcy_pop_flag']=1;
		$data['agcy_pop_addtime']=time();
		$flag = M("agcypopinfo")->add($data);
		//余额减
		$flag = M("agcy")->where("agcy_id='".I("agcy_id")."'")->setDec("agcy_money",I("agcy_pop_money"));
		if ($flag) {
			$response = array(
				'errno' => 1, 
			);
	    } else {
			$response = array(
				'errno' => 0,
			);
	    }
		echo json_encode($response);  
	}
	
	//提交申请提现 
	public function shenqingjilu(){
		$agcy_id=I("agcy_id"); 
		$count = M('agcypopinfo') 
			->where("agcy_id='".$agcy_id."'")
			->count();
		$Page = new \Think\Page($count,10); 
		$agcypopinfo = M("agcypopinfo")
			->where("agcy_id='".$agcy_id."'")
			->limit($Page->firstRow.','.$Page->listRows)
			->order("agcy_pop_id desc")
			->select();
		foreach($agcypopinfo as $k=>$v){
			$agcypopinfo[$k]['time'] = date('Y-m-d H:i:s',$v['agcy_pop_addtime']);
			if($v['agcy_pop_flag']==1){
				$agcypopinfo[$k]['agcy_pop_flag']="申请中";
			}else if($v['agcy_pop_flag']==2){
				$agcypopinfo[$k]['agcy_pop_flag']="通过";
			}else if($v['agcy_pop_flag']==3){
				$agcypopinfo[$k]['agcy_pop_flag']="驳回";
			}
		}  
		if(IS_AJAX){
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('agcypopinfo' => $agcypopinfo), 'json');
            } else {
                $this->ajaxReturn(array('agcypopinfo' => array()), 'json');
            }
        }else {
			$this->assign('agcypopinfo', json_encode(array('agcypopinfo' => $agcypopinfo)));
            $this->display();
        } 
	}
    
    //数据统计
    public function tongji(){
        $where=" 1 "; 
	    $starttime = trim(I('get.starttime'));
		$endtime = trim(I('get.endtime')); 
        if ($starttime) {
            $info['starttime'] = urldecode(trim($info['starttime']));
            $where .= " and ec_orderinfo.order_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
            $this->assign('starttime', $info['starttime']);
        }
        if ($endtime) {
            $info['endtime'] = urldecode(trim($info['endtime']));
            $where .= " and ec_orderinfo.order_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
            $this->assign('endtime', $info['endtime']);
        } 
        
        if($starttime){
            $this->assign('starttime',$starttime);
        }
        if($endtime){
            $this->assign('endtime',$endtime);
        }
		 
		if(!$starttime){
			$starttime=date("Y-m-d",strtotime("-10 day"));
			$this->assign('starttime',$starttime);
		}
		if(!$endtime){
			$endtime=date("Y-m-d",SYS_TIME);
			$this->assign('endtime',$endtime);
		} 
		$daylist=$this->get_daylist($starttime,$endtime);  
		$infolist=array();
		foreach($daylist as $k=>$v){
			$where_=$where;
			$where_.=" and ec_orderinfo.order_addtime>=".strtotime($v." 00:00:00")." ";
			$where_.=" and ec_orderinfo.order_addtime<=".strtotime($v." 23:59:59")." ";
			$infolist[$k]['pop_fact'] =  M('orderinfo')
				->where($where_) 
				->sum("order_money"); 
			$infolist[$k]['num'] =  M('orderinfo')
				->where($where_) 
				->count();		
			$infolist[$k]['dayinfo'] =$v; 
		} 
		$this->assign('infolist',$infolist);
        $this->display();
    }
    
    //获取两个日期段内所有日期
	public function get_daylist($startdate,$enddate){
		$stimestamp = strtotime($startdate);
		$etimestamp = strtotime($enddate);
		// 计算日期段内有多少天
		$days = ($etimestamp-$stimestamp)/86400+1;
		// 保存每天日期
		$date = array();
		for($i=0; $i<$days; $i++){
			$date[] = date('Y-m-d', $stimestamp+(86400*$i));
		}
		return $date;
	}
    
	//退出登录
	public function outlogin(){
		session("agcy_id",null);
		header("Content-Type:text/html;charset=utf-8");
		jump_success('退出成功！',U('login/login'));
		exit();
	}
}