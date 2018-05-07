<?php
namespace Factory\Controller;
use Think\Controller;
class FwithdrawController extends Controller {

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

    //提现列表
    public function index(){
        $factory_id=session('factory.factory_id');
        $where="1  and ec_factory.factory_id=".$factory_id." ";
        $info=I("info");
        if ($info){
            @extract($info);
            /*if($msg_title){//标题
                $info['msg_title']=urldecode(trim($info['msg_title']));
                $where.="and (ec_message.msg_title like '%".$info['msg_title']."%')";
                $this->assign('msg_title',$info['msg_title']);
            }*/
            if($fwithdraw_flagstr){//状态
                $info['fwithdraw_flagstr']=urldecode(trim($info['fwithdraw_flagstr']));
                $where.="and ec_fwithdraw.fwithdraw_flagstr like '%".$info['fwithdraw_flagstr']."%'";
                $this->assign('fwithdraw_flagstr',$info['fwithdraw_flagstr']);
            }
			//申请时间
			if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_fwithdraw.fwithdraw_addtime >=".strtotime_2($info['starttime']."00:00:00")." ";  
                $this->assign('starttime',$info['starttime']);
            }
           	if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_fwithdraw.fwithdraw_addtime <=".strtotime_2($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
        }
        $fwithdraw=M('Fwithdraw');
        // 查询满足要求的总记录数
        $count      = $fwithdraw
					->join("left join ec_factory on ec_factory.factory_id=ec_fwithdraw.factory_id")
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
        $listinfo = $fwithdraw
			->join("left join ec_factory on ec_factory.factory_id=ec_fwithdraw.factory_id")
            ->where($where)
			->field("ec_fwithdraw.*,ec_factory.factory_name as factoryname ")
            ->order('ec_fwithdraw.fwithdraw_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('listinfo',$listinfo);
        $this->assign('pageshow',$pageshow);
		//手续费
		$pop = M('setinfo')->where("set_key='".factory_change."'")->find();
		$this->assign('pop',$pop);
        $this->display();
    }
    //申请提现
	public function addinfo(){
        $factory_id=session('factory.factory_id');
        //获取提现最大值，最小值，手续费
        /*$pop_max=M('Setinfo')->where("set_key='pop_max'")->field('set_value')->find();
        $pop_min=M('Setinfo')->where("set_key='pop_min'")->field('set_value')->find();
        $pop_routine=M('Setinfo')->where("set_key='pop_routine_shop'")->field('set_value')->find();//商户手续费pop_routine_shop
	    dump($pop_routine);*/
		if (IS_POST){
			$info=I("info");
            $fwithdraw_amount=abs($info['fwithdraw_amount']);
            $info['fwithdraw_amount']=$fwithdraw_amount;
			//手续费
			$pop = M('setinfo')->where("set_key='".factory_change."'")->find(); 
			
			$fwithdraw_fact_ = abs(round($fwithdraw_amount-($fwithdraw_amount*$pop['set_value']/100),2)); 
			$info['fwithdraw_fact'] = $fwithdraw_fact_; 
			$info['factory_id']=$factory_id;
			$info['fwithdraw_addtime']=time();
			//$info['pop_type']=2;//1 用户提现，2商家提现
			$info['fwithdraw_flag']=1;//1 申请，2通过，3驳回
			$info['fwithdraw_flagstr']='申请';//1 申请，2通过，3驳回
			$res=M('Fwithdraw')->add($info);
            //扣除账户余额
            $factory=M('Factory')->where('factory_id='.$factory_id)->find();
            $factory_money=$factory['factory_money'];
            $money=$factory_money-$fwithdraw_amount;
            $flag=M('Factory')->where('factory_id='.$factory_id)->save(array('factory_money'=>$money));
			if($res&&$flag){
                //增加消息通知
                $message= new \Admin\Model\MessageModel();
                $message->getmessage_($factory_id,4,'厂家申请提现通知',$factory['factory_name'].'申请提现通知');
                $this->home_success('申请提现成功！',U('fwithdraw/index'));
			}else{
                //账户余额恢复至扣除前
                M('Factory')->where('factory_id='.$factory_id)->save(array('factory_money'=>$factory_money));
                $this->home_error('申请提现失败！',U('fwithdraw/index'));
			}
		}else{
            /*$this->assign('pop_max',$pop_max['set_value']);
            $this->assign('pop_min',$pop_min['set_value']);
            $this->assign('pop_routine',$pop_routine['set_value']);*/
			//手续费
			$pop = M('setinfo')->where("set_key='".factory_change."'")->find();
			$this->assign('pop',$pop);
            //商户金额
            $factory=M('Factory')->where('factory_id='.$factory_id)->find();
            $this->assign('factory_money',$factory['factory_money']);
			$this->display();
		}
    }

    //详情
    public function info(){
        $fwithdraw_id=I('get.fwithdraw_id');
        $fwithdraw=M('fwithdraw')
                ->join("left join ec_factory on ec_factory.factory_id=ec_fwithdraw.factory_id")
                ->join("left join ec_admininfo on ec_admininfo.admin_id=ec_fwithdraw.fwithdraw_bank_checkperson")
                ->field("ec_fwithdraw.*,ec_factory.factory_name as factoryname,ec_admininfo.realname as admimname ")
                ->where('ec_fwithdraw.fwithdraw_id='.$fwithdraw_id)
                ->find();
        $this->assign('fwithdraw',$fwithdraw);
        //$this->assign('pop_flags',D("expert")->pop_flags());//状态 1申请，2通过,3驳回
        $this->display();
    }
}    