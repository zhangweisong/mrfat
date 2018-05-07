<?php
namespace Factory\Controller;
use Think\Controller;
class ForderController extends Controller
{
   function __construct() {
		//继承父类
		parent::__construct();		
		//判断登录状态
		if(!D('Factory')->islogin()){//未登录
			jump('您尚未登录，请先登录！', U('login/login'));
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
        $this->display('Forder/home_success');
        exit();
    }

    //公共error
    public function home_error($msg, $url = "", $wait = 3) {
        $this->assign('msg', $msg);
        $this->assign('url', $url ? $url : U("index/index"));
        $this->assign('wait', $wait);
        $this->display('Forder/home_error');
        exit();
    }
    
	//经销商订单列表
    public function index() {
        $factory_id=session('factory.factory_id');
        $where = "1 and ec_forder.factory_id=".$factory_id." ";
        $info = I("info");
        if ($info) {
            @extract($info);
            //按名称搜索
            if ($agcy_name) {    
                $agcy_name = urldecode(trim($agcy_name));
                $where .= " and (ec_agcy.agcy_name like '%" . $agcy_name . "%'
                                or ec_forder.forder_number like '%" . $agcy_name . "%'
                )";
                $this->assign('agcy_name', $agcy_name);
            }
            if ($forder_flag) {    
                $where .= " and (ec_forder.forder_flag=".$forder_flag.")";
                $this->assign('forder_flag', $forder_flag);
            }
              
            if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_forder.forder_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_forder.forder_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
        }
        $count = M("forder")->where($where)
                ->join("LEFT JOIN ec_agcy ON ec_agcy.agcy_id=ec_forder.agcy_id")
                ->count();
        $pagesize = I('pagesize') ? I('pagesize') : 15;
        $Page = new \Think\Page($count, $pagesize);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info[' . $key . ']'] = urlencode($val);
            }
        }
        $Page->parameter['pagesize'] = $pagesize;
        $pageshow = $Page->adminshow();
        $forderlist = M('forder')
                ->join("LEFT JOIN ec_agcy ON ec_agcy.agcy_id=ec_forder.agcy_id")
                ->where($where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order("ec_forder.forder_addtime desc")
                ->select();

        foreach ($forderlist AS $k => $v) {
            $forderlist[$k]['forder_addtime'] = date("Y-m-d H:i:s", $v['forder_addtime']);
        }
        $this->assign('pageshow', $pageshow);
        $this->assign('forderlist', $forderlist); 
        $this->display();
    }
 
    //详情信息
    public function orderdetails() { 
        $forder_id = I("get.forder_id"); //ID
        $forder=M("forder")
                ->join('left join ec_agcy on ec_agcy.agcy_id=ec_forder.agcy_id')
                ->where("forder_id=".$forder_id)
                ->field('ec_forder.*,ec_agcy.agcy_name as agcyname,ec_agcy.agcy_manager as agcymanager')
                ->find();
               
        $forderlist = M("forderlist") 
                ->join("LEFT JOIN ec_fgoods ON ec_fgoods.fgoods_id=ec_forderlist.fgoods_id")
                ->join("LEFT JOIN ec_factory ON ec_factory.factory_id=ec_fgoods.factory_id") 
                ->where("ec_forderlist.forder_id='" . $forder_id . "'")
                ->select(); 
        foreach($forderlist as $k=>$v){
            $res = explode('|',$v['fgoods_images']);
            $forderlist[$k]['fgoods_images'] = $res[0];
        }  
        $this->assign('forderlist', $forderlist);
        $this->assign('forder', $forder); 
        $this->display(); 
    }
	
	//发货
	public function send() {
		$forder_id = I("get.forder_id"); 
		$res = M("forder")->where("forder_id='".$forder_id."'")->find();
 	
		if (IS_POST) {
            $data = I("info");
			$data['forder_flag']=3; 
			$data['forder_tptime']=time();
			$data['forder_flagstr']="已完成";
            $flag = M("forder")->where("forder_id='".$forder_id."'")->save($data);
            
            $forderlist = M("forderlist")->where("forder_id='".$forder_id."'")->find();
            //算返的积分
            $jifen_change = M('setinfo')->where("set_key='".jifen_change."'")->find();
            $jifennum = round($forderlist['forderlist_tonnum']*$jifen_change['set_value'],2);
            //积分变动
			M('jifenchange')->add(array('jifen_agcy_id'=>$res['agcy_id'],'jifen_change_type'=>2,'jifen_goods_point'=>$jifennum,'jifen_change_addtime'=>time(),'jifen_change_flag'=>3,'jifen_state'=>2,'jifen_goods'=>$jifen['jifen_goods_id']));
            
            //经销商的加积分
            M('agcy')->where("agcy_id='".$res['agcy_id']."' ")->setInc('agcy_points',$jifennum);
                
            if ($flag) {
                //增加消息通知
				$message= new \Admin\Model\MessageModel();
				$message->getmessage_($res['agcy_id'],2,"经销商订单发货通知","订单编号为".$res['forder_number']."的订单已发货"); 
				$this->home_success('发货成功！', U('forder/index'), 3);
            } else { 
				$this->home_error('发货失败！', U('forder/index'), 3);
            } 
        } else {  
			$this->assign('res', $res); 
            $this->display();
        }  
	}
}