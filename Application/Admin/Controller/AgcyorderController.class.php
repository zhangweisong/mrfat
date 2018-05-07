<?php
namespace Admin\Controller;
use Think\Controller;
class AgcyorderController extends Controller
{
   function __construct() {
		//继承父类
		parent::__construct();		
		//判断登录状态
		if(!D('admininfo')->islogin()){//未登录
			jump('您尚未登录，请先登录！',U('login/login'),3);
		}	
    }
	//空方法，防止报错
	public function _empty(){
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
	//农户订单列表
	public function index(){
	  $where="1 ";
	  $info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword'])); 
                $where .= " and (
                		ec_user.realname like '%" . $info['keyword'] . "%' 
                        or ec_user.nickname like '%" . $info['keyword'] . "%'  
						or ec_agcy.agcy_name like '%" . $info['keyword'] . "%'
						or ec_orderinfo.order_number like '%" . $info['keyword'] . "%'
                	)";
                $this->assign('keyword',urldecode($info['keyword']));
			}
			if($order_type){ 
				$info['order_type']=urldecode(trim($info['order_type']));
                $where.="and ec_orderinfo.order_type = '".$info['order_type']."' ";  
                $this->assign('order_type',urldecode($info['order_type']));	
			}
			if($order_state){ 
				$info['order_state']=urldecode(trim($info['order_state']));
                $where.="and ec_orderinfo.order_state ='".$info['order_state']."' ";  
                $this->assign('order_state',urldecode($info['order_state'])); 
			}
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
			
		} 
		// 查询满足要求的总记录数
		$count  = M('orderinfo') 
			//用户
			->join("left join ec_user on ec_orderinfo.user_id=ec_user.user_id")
			//所属商户
			->join("left join ec_agcy on ec_orderinfo.agcy_id=ec_agcy.agcy_id")
			->field("ec_orderinfo.*,ec_user.realname,ec_user.nickname,ec_user.phone,ec_agcy.agcy_name")
			->where($where) 
			->count(); 
		$Page       = new \Think\Page($count,15);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$orderinfo = M('orderinfo')
			->where($where)
			//用户
			->join("left join ec_user on ec_orderinfo.user_id=ec_user.user_id")
			//所属商户
			->join("left join ec_agcy on ec_orderinfo.agcy_id=ec_agcy.agcy_id")
			->field("ec_orderinfo.*,ec_user.realname,ec_user.nickname,ec_user.phone,ec_agcy.agcy_name")
			->order('order_id desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->select();
        foreach($orderinfo as $k=>$v){
            $orderinfo[$k]['realname'] = $v['realname']?$v['realname']:$v['nickname'];
        }    
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('type',D('orderinfo')->pay_type());
		$this->assign('state',D('orderinfo')->state());
		$this->assign('province',$province); 
		$this->assign('pageshow',$pageshow);
		$this->display();
    }
    //查看详情
    public function orderinfo(){
    	$order_id = I("order_id");  
    	//订单详情
    	$orderinfo = M('orderinfo') 
			//用户
			->join("left join ec_user on ec_orderinfo.user_id=ec_user.user_id")
			//商户表 
			->join("left join ec_agcy on ec_orderinfo.agcy_id=ec_agcy.agcy_id")
			->field("ec_orderinfo.*,ec_user.realname,ec_user.nickname,ec_user.phone,ec_agcy.agcy_name")
			->where("ec_orderinfo.order_id = ".$order_id)   
			->find(); 
        $orderinfo['realname']=  $orderinfo['realname']?$orderinfo['realname']:$orderinfo['nickname']; 
		//商品详情
		$goods = M('orderlist') 
			->join('left join ec_agcygoods on ec_orderlist.goods_id=ec_agcygoods.agcy_goods_id')
			->field("ec_orderlist.*,ec_agcygoods.agcy_goods_name")
			->where("ec_orderlist.order_id=".$order_id)
			->select(); 
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('type',D('orderinfo')->pay_type());
		$this->assign('state',D('orderinfo')->state());
		$this->assign('goods',$goods); 
    	$this->display();
    	
    } 
	//发货
	public function send() {
		$order_id = I("get.order_id"); 
		//将状态改为已完成
		if(IS_POST){
			$data['order_remark']= I('order_remark');
			$data['order_state']=3;
			$data['order_sendtime']=time();
			//添加发货备注和修改订单状态
			$res=M("orderinfo")->where("order_id=".$order_id)->save($data);
			if($res){
				$this->home_success('发货成功！',U('Agcyorder/index')); 
			}else{
				$this->home_error('发货失败！',U('Agcyorder/send',array('order_id'=>$order_id ))); 
			} 
		}else{
			$orderinfo = M('orderinfo') 
				->join("left join ec_user on ec_orderinfo.user_id=ec_user.user_id")
				->field("ec_orderinfo.order_id,ec_orderinfo.order_number,ec_user.realname")
				->where("ec_orderinfo.order_id = ".$order_id)   
				->find(); 
		   $this->assign('popinfo',$orderinfo);
		   $this->display();
		}
    	
	}
}