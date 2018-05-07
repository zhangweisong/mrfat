<?php
namespace Agcy\Controller;
use Think\Controller;
class IntegralController extends Controller  
{
    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录
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
	 //我的积分（经销商）
	public function index(){
		$agcy_id = session('agcy_id');
		//查找经销商的积分
		$Intinfo = M('agcy')->where('agcy_id='.$agcy_id)->find();
		//积分来源
		$count = M('jifenchange')->where('jifen_agcy_id='.$agcy_id)->count();
		$Page       = new \Think\Page($count,15);
		$data = M('jifenchange')
			->where('jifen_agcy_id='.$agcy_id)
			->order('jifen_change_id desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->select();
		//dd($data);
		foreach($data as $k=>$v){
			$data[$k]['jifen_change_addtime'] = date('Y-m-d H:i:s',$v['jifen_change_addtime']);
		}
		if(IS_AJAX){
			if ($Page->totalPages >= $p) {
				$this->ajaxReturn(array('status' =>1, 'data' => $data, 'msg' => ""));
			} else {
				$this->ajaxReturn(array('status' => 0, 'data' => array(), 'msg' => "没有更多数据了"));
			}
		}else{
			$this->assign('data',$data);
		    $this->assign('Intinfo',$Intinfo);
		}
		 $this->display();
	}
	//兑换记录
	public function duihuanjilu(){
		$agcy_id = session('agcy_id');
		$count = M('jifenorder')
			->where('jifen_agcy_id='.$agcy_id)
			->join('left join ec_jifengoods on ec_jifengoods.jifen_goods_id = ec_jifenorder.jifen_goods_id')
			->count();
		$Page       = new \Think\Page($count,10);
		$data = M('jifenorder')
			->where('jifen_agcy_id='.$agcy_id)
			->join('left join ec_jifengoods on ec_jifengoods.jifen_goods_id = ec_jifenorder.jifen_goods_id')
			->order('jifen_order_id desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->field('ec_jifenorder.*,ec_jifengoods.jifen_goods_name')
			->select();
		foreach($data as $k=>$v){
			$data[$k]['jifen_order_addtime'] = date('Y-m-d H:i:s',$v['jifen_order_addtime']);
		}	
		//dd($data);
		if(IS_AJAX){
			if ($Page->totalPages >= $p) {
				$this->ajaxReturn(array('status' =>1, 'data' => $data, 'msg' => ""));
			} else {
				$this->ajaxReturn(array('status' => 0, 'data' => array(), 'msg' => "没有更多数据了"));
			}
		}else{
			$this->assign('data',$data);
		}
		$this->display();
	}
	//积分兑换
	public function jifenduihuan(){
		$agcy_id = session('agcy_id');
		$Intinfo = M('agcy')->where('agcy_id='.$agcy_id)->find();
		$count = M('jifengoods')
			->where('jifen_goods_isable=1 and jifen_goods_num>0')
			->count();
		$Page       = new \Think\Page($count,6);
		$data = M('jifengoods')
			->where('jifen_goods_isable=1 and jifen_goods_num>0')
			->order('jifen_goods_id desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->select(); 
		foreach($data as $k=>$v){
			$arr=explode("|",$v['jifen_goods_image']);
			$data[$k]['jifen_goods_image']=$arr[0];
		}
		if(IS_AJAX){
			if ($Page->totalPages >= $p) {
				$this->ajaxReturn(array('status' =>1, 'data' => $data, 'Intinfo'=>$Intinfo ,'msg' => ""));
			} else {
				$this->ajaxReturn(array('status' => 0, 'data' => array(), 'msg' => "没有更多数据了"));
			}
             
		}else{
            $this->assign('data', json_encode(array('data' => $data))); 
			$this->assign('Intinfo',json_encode(array('Intinfo'=>$Intinfo)));
		}
		$this->display();
	}
	//积分规则
	public function jifenguize(){
		$data = M('composition')->where('composition_id=5')->find();
		$data['composition_text']=content_strip($data['composition_text']); 
		//dd($data);
		$this->assign('data',$data);
		$this->display();
	}
	//积分兑换商品详情
	public function jifenshangpingxq(){
		$agcy_id = session('agcy_id');
		$Intinfo = M('agcy')->where('agcy_id='.$agcy_id)->find();
		$jifen_goods_id = I('get.jifen_goods_id');
		$data = M('jifengoods')->where('jifen_goods_id='.$jifen_goods_id )->find();
		$arr= explode("|",$data['jifen_goods_image']);  
		$this->assign('arr',$arr);
		$this->assign('Intinfo',$Intinfo);
		$this->assign('data',$data);
		$this->display();
	}
	//兑换成功
	public function duihuanchenggong(){
		$agcy_id = session('agcy_id');
		$jifen_goods_id = I('get.jifen_goods_id');
		//查找商品
		$reu= M('jifengoods')->where('jifen_goods_id='.$jifen_goods_id )->find();
        
        if($reu['jifen_goods_num']<1){
            jump_success('库存不足',U('index/index'));
            exit;
        };
		$reu['jifen_goods_buynum']+=1;
		$reu['jifen_goods_num']-=1;
		$jifen_goods_price = I('get.jifen_goods_price');
		//修改商品表的已售数
		M('jifengoods')->where('jifen_goods_id='.$jifen_goods_id )->save($reu);
		//修改自己的积分
		M('agcy')->where('agcy_id='.$agcy_id )->setDec('agcy_points',$jifen_goods_price);
		//查询收货人电话地址
		$result= M('agcy')->where('agcy_id='.$agcy_id )->find();
		//添加订单
		$data['jifen_agcy_id']=$agcy_id;
        $data['jifen_order_state']=2;
        $data['jifen_order_addtime']=time();
		$data['jifen_order_name']=$result['agcy_manager'];
        $data['jifen_order_phone']=$result['agcy_tel'];
        $data['jifen_order_address']=$result['agcy_address'];
        $data['jifen_order_number'] =time().random(5);
        $data['jifen_goods_id']=$jifen_goods_id;
        $data['jifen_goods_num']=1;
        $data['jifen_goods_price'] =$jifen_goods_price;
		M('jifenorder')->add($data);
		$this->display();
	}
}