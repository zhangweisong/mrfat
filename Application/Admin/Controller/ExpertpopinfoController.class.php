<?php
namespace Admin\Controller;
use Think\Controller;
class ExpertpopinfoController extends Controller {

    function __construct() {
		//继承父类
		parent::__construct();

       //判断登录状态
		if(!D('admininfo')->islogin()){//未登录
			 jump('您尚未登录，请先登录！',U('login/login'));
		}	
    }

    //空方法，防止报错
    public function _empty(){
        $this->index();
    }
    //提现列表
    public function index(){
        $where="1    ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($expert_realname){//店铺名称
                $info['expert_realname']=urldecode(trim($info['expert_realname']));
                $where.="and (ec_expert.expert_realname like '%".$info['expert_realname']."%')";
                $this->assign('expert_realname',$info['expert_realname']);
            }
            if($pop_flag){//状态
                $info['pop_flag']=urldecode(trim($info['pop_flag']));
                $where.="and ec_expert_popinfo.pop_flag ='".$info['pop_flag']."'";
                $this->assign('pop_flag',$info['pop_flag']);
            }
			//申请时间
			if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_expert_popinfo.pop_addtime >=".strtotime_2($info['starttime']."00:00:00")." ";  
                $this->assign('starttime',$info['starttime']);
            }
           	if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_expert_popinfo.pop_addtime <=".strtotime_2($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
        }
        $expert_popinfo=M('expert_popinfo');
        // 查询满足要求的总记录数
        $count      = $expert_popinfo
					->join("left join ec_expert on ec_expert.expert_id=ec_expert_popinfo.expert_id")
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
        $listinfo = $expert_popinfo
                    ->join("left join ec_expert on ec_expert.expert_id=ec_expert_popinfo.expert_id")
                    ->where($where)
                    ->field('ec_expert_popinfo.*,ec_expert.expert_realname as realname,ec_expert.expert_phone as phone ')
                    ->order('ec_expert_popinfo.pop_id desc')
                    ->limit($Page->firstRow.','.$Page->listRows)
                    ->select();
        $this->assign('listinfo',$listinfo);
        $this->assign('pageshow',$pageshow);
        $this->assign('pop_flags',D("expert")->pop_flags());//状态 1申请，2通过,3驳回 
        $this->display();
    }
   //详情
    public function xiangqing(){
        $pop_id=I('get.pop_id');
        $expert_popinfo=M('expert_popinfo')
                ->join("left join ec_expert on ec_expert.expert_id=ec_expert_popinfo.expert_id")
                ->join("left join ec_admininfo on ec_admininfo.admin_id=ec_expert_popinfo.pop_check_person")
                ->field("ec_expert_popinfo.*,ec_expert.expert_realname as realname,ec_admininfo.realname as admimname ")
                ->where('ec_expert_popinfo.pop_id='.$pop_id)
                ->find();
        $this->assign('popinfo',$expert_popinfo);
        $this->assign('pop_flags',D("expert")->pop_flags());//状态 1申请，2通过,3驳回
        
        $this->display();
    }
   //通过
   public function tongguo(){
        $admin_id=session('admin.admin_id');
        $pop_id=I('get.pop_id');
        $status=1;
        if(IS_POST){
            $info=I('info');
            $info['pop_flag']=2;
            $info['pop_check_time']=time();
            $info['pop_check_person']=$admin_id;
            //申请提现信息
            $pop=M('expert_popinfo')->where('pop_id='.$pop_id)->find();
            
            //获取专家信息
            $expert=M('expert')->where('expert_id='.$pop['expert_id'])->find();
            $flag=M('expert_popinfo')->where('pop_id='.$pop_id)->save($info);
            if($flag){
                //增加消息通知
                $message= new \Admin\Model\MessageModel();
                $message->getmessage_($pop['expert_id'],3,'专家提现审核通过',$expert['expert_realname'].'提现审核通过');
                $status=2;
            }
        }
            $this->assign('status',$status);
            $this->assign('pop_id',$pop_id);
            $this->display();
   }
   
   //驳回
   public function bohui(){
      $admin_id=session('admin.admin_id');
      $pop_id=I('get.pop_id');
      $status=1;
      if(IS_POST){
            $info=I('info');
            $info['pop_flag']=3;
            $info['pop_check_time']=time();
            $info['pop_check_person']=$admin_id;
            $pop=M('expert_popinfo')->where('pop_id='.$pop_id)->find();
            $flag=M('expert_popinfo')->where('pop_id='.$pop_id)->save($info);
            //驳回成功时增加商户余额
            $expert=M('expert')->where('expert_id='.$pop['expert_id'])->setInc('expert_money', abs($pop['pop_money']));
            if($flag&&$expert){
                //增加消息通知
                $message= new \Admin\Model\MessageModel();
                $message->getmessage_($pop['expert_id'],3,'专家驳回申请提现',$expert['expert_realname'].'驳回申请提现');
                $status=2;
            }
            /*else{
                //驳回失败商户余额减少
                M('Shop')->where('shop_id='.$pop['shop_id'])->setDec('shop_money', abs($pop['pop_money']));
                M('popinfo')->where('pop_id='.$pop_id)->save(array('pop_flag'=>1));
                jump('驳回失败！',U('popinfo/index'),3);
            } */
        }
            $this->assign('status',$status);
            $this->assign('pop_id',$pop_id);
            $this->display();
   }

}