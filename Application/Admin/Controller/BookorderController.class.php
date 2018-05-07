<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class BookorderController extends Controller {

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

    //图书订单列表
    public function index() { 
        $where = " 1 ";
        $info = I("info");
        if ($info) {
            @extract($info);
            if ($names) { 
				$name_=urldecode(trim($info['names'])); 
                $where .= " and (
					ec_bookorder.border_number LIKE '%" . $name_ . "%'
					or 	ec_user.realname LIKE '%" . $name_ . "%'	
					or 	ec_bookorder.baddr_phone LIKE '%" . $name_ . "%'
					or 	ec_bookorder.baddr_name LIKE '%" . $name_ . "%'
				)";  
                $this->assign('names', $name_);
            } 
			//支付方式
            if ($pay_type) { 
				$pay_type=urldecode(trim($info['pay_type']));
                $where .= " and ec_bookorder.border_type=".$pay_type;  
                $this->assign('paytype', $pay_type);
            }
			//订单状态
            if ($state) {
				$state=urldecode(trim($info['state']));
                $where .= " and ec_bookorder.border_state=".$state;
                $this->assign('state_', $state);
            } 
			//时间
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_bookorder.border_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_bookorder.border_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
        } 
        // 查询满足要求的总记录数
        $count = M('bookorder') 
				->join("left join ec_user on ec_bookorder.user_id=ec_user.user_id")
				->field("ec_bookorder.*,ec_user.realname,ec_user.nickname")
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
        $listinfo = M('bookorder') 
                ->where($where)
				->join("left join ec_user on ec_bookorder.user_id=ec_user.user_id")
				->field("ec_bookorder.*,ec_user.realname,ec_user.nickname")
                ->order("border_id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        foreach($listinfo as $k=>$v){
            $listinfo[$k]['realname']=$v['realname']?$v['realname']:$v['nickname'];
        }            
        
		//支付方式
		$pay_type=D('Bookorder')->pay_type();
		//订单状态
		$state=D('Bookorder')->state();
		$this->assign('pay_type', $pay_type); 
		$this->assign('state', $state); 			
        $this->assign('listinfo', $listinfo); 
		$this->assign('pageshow', $pageshow); 
        $this->display();
    }
	
	//查看详情
    public function orderinfo() {
        $border_id = I("border_id"); 
    	//订单详情
    	$bookorder = M('bookorder') 
			//用户
			->join("left join ec_user on ec_bookorder.user_id=ec_user.user_id") 
			->where("ec_bookorder.border_id = ".$border_id)   
			->find();
        $bookorder['realname']= $bookorder['realname']?$bookorder['realname']:$bookorder['nickname']; 
		//支付方式		
		$pay_type=D('Bookorder')->pay_type();
		//订单状态
		$state=D('Bookorder')->state(); 
		 
		//商品详情
		$goods = M('bookorderlist') 
			->join('left join ec_book on ec_bookorderlist.book_id=ec_book.book_id')
			->field("ec_bookorderlist.*,ec_book.book_name,ec_book.book_price")
			->where("ec_bookorderlist.border_id=".$border_id)
			->select();
		 
		$this->assign('pay_type', $pay_type); 
		$this->assign('state', $state); 		 
		$this->assign('bookorder',$bookorder); 
		$this->assign('goods',$goods);  
    	$this->display();
    }

	//发货
	public function send() {
		$border_id = I("get.border_id"); 
		$res = M("bookorder")->where("border_id='".$border_id."'")->find(); 
		if (IS_POST) {
            $data = I("info");
			$data['border_state']=3; 
			$data['border_gotime']=time(); 
            $flag = M("bookorder")->where("border_id='".$border_id."'")->save($data); 
            if ($flag) {
                //增加消息通知
				$message= new \Admin\Model\MessageModel();
				$message->getmessage_($res['user_id'],1,"图书订单发货通知","图书订单编号为".$res['border_number']."的订单已发货"); 
				$this->home_success('发货成功！', U('bookorder/index'), 3);
            } else { 
				$this->home_error('发货失败！', U('bookorder/index'), 3);
            } 
        } else {  
			$this->assign('res', $res); 
            $this->display();
        }  
	}

}
