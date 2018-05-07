<?php
namespace Agcy\Controller;
use Think\Controller;
class OrderlistController extends Controller
{
	   function __construct() {
		 //判断登录
		parent::__construct();
	    $agcy_id=session("agcy_id");
            if($agcy_id==""){//未登录
                header("Location:".U('login/login'));
                exit();
            }
        }
	//删除不符合的订单	
	public function del(){
		$agcy_id=session("agcy_id");
		$rel = M('orderinfo')->where('order_act_id!=0 AND order_state =1 and agcy_id='.$agcy_id)->select();
		foreach ($rel as $k=>$v){
			M('orderlist')->where('order_id='.$v['order_id'])->delete();
			M('orderinfo')->delete($v['order_id']);
		}
	}
	//订单管理列表
	public function index(){
		$this->del();
		$agcy_id=session("agcy_id");
		$where="ec_orderinfo.agcy_id='".$agcy_id."' "; 
		$keyword = trim(I('get.keyword'));
	    $starttime = trim(I('get.starttime'));
		$endtime = trim(I('get.endtime'));
        if ($keyword) {
            $where .= " and (ec_user.realname LIKE '%".$keyword."%' or ec_user.phone LIKE'%".$keyword."%' or ec_orderinfo.order_number LIKE'%".$keyword."%')";
			//dd($where);
            $this->assign('keyword', $keyword);
        }
		if ($starttime) {
                $starttime = urldecode(trim($starttime));
                $where .= " and ec_orderinfo.order_addtime >=" . strtotime($starttime."00:00:00") . " ";
                $this->assign('starttime', $starttime);
        }
		if ($endtime) {
			$endtime = urldecode(trim($endtime));
			$where .= " and ec_orderinfo.order_addtime <=" . strtotime($endtime."23:59:59") . " ";
			$this->assign('endtime', $endtime);
		}
		$count = M('orderinfo')
		      ->where($where)
		      ->join('left join ec_user on ec_user.user_id = ec_orderinfo.user_id')
			  ->join('left join ec_activity on ec_activity.act_id = ec_orderinfo.order_act_id')
			  ->count();
	    $Page       = new \Think\Page($count,15);
		$data = M('orderinfo')
		      ->where($where)
		      ->join('left join ec_user on ec_user.user_id = ec_orderinfo.user_id')
			 ->join('left join ec_activity on ec_activity.act_id = ec_orderinfo.order_act_id')
			  ->order('order_addtime desc,order_id desc')
			  ->limit($Page->firstRow.','.$Page->listRows)
			  ->field('ec_orderinfo.*,ec_user.realname,ec_user.nickname,ec_user.phone,ec_activity.act_classify')
			  ->select();
		foreach($data as $k=>$v){
			$data[$k]['order_addtime'] = date('Y-m-d H:i:s',$v['order_addtime']);
            $data[$k]['realname'] = $v['realname']?$v['realname']:$v['nickname'];
		}	
        if(IS_AJAX){
			if ($Page->totalPages >= $p) {
				$this->ajaxReturn(array('status' =>1, 'data' => $data, 'msg' => ""));
			} else {
				$this->ajaxReturn(array('status' => 0, 'data' => array(), 'msg' => "没有更多数据了"));
			}
		}else{
			$this->assign('data',$data);
            $this->assign('ordertype',D('orderinfo')->ordertype());
		}			
		$this->display();
	}
	//订单详情
	public function dingdanxiangq(){
        //$agcy_id=session("agcy_id");
		$order_id = I('get.order_id');
		$where="order_id=".$order_id; 
		$data = M('orderinfo')
		      ->where($where)
		      ->join('left join ec_user on ec_user.user_id = ec_orderinfo.user_id')
			  ->join('left join ec_activity on ec_activity.act_id = ec_orderinfo.order_act_id')
			  ->field('ec_orderinfo.*,ec_user.realname,ec_user.nickname,ec_user.phone,ec_activity.act_classify')
			  ->find();
        $data['realname'] = $data['realname']?$data['realname']:$data['nickname'];   
	    $data['order_addtime'] = date('Y-m-d H:i:s',$data['order_addtime']); 
		$datainfo = M('orderlist')
			  ->where($where)
			  ->join('left join ec_agcygoods on ec_agcygoods.agcy_goods_id = ec_orderlist.goods_id')
			  ->order('ec_agcygoods.agcy_goods_id desc')
			  ->select();
		foreach($datainfo as $k=>$v){
			$arr=explode("|",$v['agcy_goods_images']);
			$datainfo[$k]['agcy_goods_images']=$arr[0];
		}	  
		$this->assign('data',$data);
		$this->assign('datainfo',$datainfo);
		$this->assign('ordertype',D('orderinfo')->ordertype());
		$this->assign('orderpay',D('orderinfo')->orderpay());
		$this->display();
	}
	//订单发货
	public function isabout(){
        $agcy_id=session("agcy_id");
	    $order_id = I('get.order_id');
		$pop = M('orderinfo')->where('order_id='.$order_id)->find();
		$order_remark = filterEmoji(I('get.order_remark'),2);
		$data = array(
		   'order_remark'=>$order_remark,
		   'order_state'=>3,
		   'order_sendtime'=>time()
		 );
		$message= new \Admin\Model\MessageModel();
        $message->getmessage_($pop['user_id'],1,'订单通知','订单编号为'.$pop['order_number'].'的订单已完成！');
        //更改订单状态
        $result = M('orderinfo')->where('order_id='.$order_id)->save($data);
        //三级返佣
        //查询都有哪些商品
        $goods=M('Orderlist')
                ->where('ec_orderlist.order_id='.$order_id)
                ->join('left join ec_agcygoods on ec_agcygoods.agcy_goods_id=ec_orderlist.goods_id')
                ->select();
        foreach($goods as $k=>$v){
            //查询商品的比例
            $bili=M('agcygoods')->where('agcy_goods_id='.$v['agcy_goods_id'])->find();
            //求商品返佣和
            $all +=$v['goods_price']*$bili['agcy_one']*$v['goods_num']/100;
            $yi_shangjis=M('user')->where('user_id='.$pop['user_id'])->find();
            //查询农户的上级
            $yi_shangji=M('user')->where('user_id='.$yi_shangjis['refree_id'])->find();
            if($yi_shangji){
                $all_two +=$v['goods_price']*$bili['agcy_tow']*$v['goods_num']/100;
                //在查询上级的上级
                $er_shangjis=M('user')->where('user_id='.$yi_shangji['refree_id'])->find();  
                if($er_shangjis){
                    $all_one +=$v['goods_price']*$bili['agcy_three']*$v['goods_num']/100;            
                }else{
                    $all_one=0;
                }
            }else{
                $all_two=0;
            }
        }
        //给下单的用户自己返钱(一级)
        M('User')->where('user_id='.$pop['user_id'])->setInc('money',$all);
        $three=M('Income')->add(array('user_id'=>$pop['user_id'],'income_time'=>time(),'income_type'=>2,'income_back'=>2,'income_handwin'=>2,'remarks'=>"自身返佣",'money'=>$all));
        if($three){
            $message= new \Admin\Model\MessageModel();
            $message->getmessage_($pop['user_id'],1,'订单通知','订单编号为'.$pop['order_number'].'的订单为您自身返佣'.$all.'元！');
        }
        //查询农户的上级
        $user_yi=M('user')->where('user_id='.$pop['user_id'])->find();
        //$yi=M('user')->where('user_id='.$user_yi['refree_id'])->count();
        if($user_yi){
            $yis=M('user')->where('user_id='.$user_yi['refree_id'])->find();
              // dd($yis);
            //给二级用户返佣
            M('User')->where('user_id='.$yis['user_id'])->setInc('money',$all_two);  
            $two=M('Income')->add(array('user_id'=>$yis['user_id'],'income_time'=>time(),'income_type'=>2,'income_back'=>2,'income_handwin'=>1,'remarks'=>"二级返佣",'money'=>$all_two));
            if($two){
                $message= new \Admin\Model\MessageModel();
                $message->getmessage_($yis['user_id'],1,'订单通知','订单编号为'.$pop['order_number'].'的订单为您返佣'.$all_two.'元！');
            }
            //查询农户的上级
           // $er=M('user')->where('user_id='.$yis['refree_id'])->count();
            $ers=M('user')->where('user_id='.$yis['refree_id'])->find();
            if($ers){
                //给三级用户返佣
                M('User')->where('user_id='.$ers['user_id'])->setInc('money',$all_one); 
                $one=M('Income')->add(array('user_id'=>$ers['user_id'],'income_time'=>time(),'income_type'=>2,'income_back'=>2,'income_handwin'=>1,'remarks'=>"三级返佣",'money'=>$all_one)); 
                if($one){
                    $message= new \Admin\Model\MessageModel();
                    $message->getmessage_($ers['user_id'],1,'订单通知','订单编号为'.$pop['order_number'].'的订单为您返佣'.$all_one.'元！');
                } 
            }         
        }        
        $order_p=$pop['order_money']-$all-$all_two-$all_one;
        $order_ps=floatval($order_p);
        //给经销商加剩余的钱  
        M('agcy')->where('agcy_id='.$agcy_id)->setInc('agcy_money',$order_ps);
        //给订单表加经销商剩余的钱
        M('orderinfo')->where('order_id='.$order_id)->setInc('agcy_money',$order_ps);
        $this->ajaxReturn(array('data'=>1));
	}
}