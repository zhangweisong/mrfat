<?php
namespace Admin\Controller;
use Think\Controller;
class UserController extends Controller{
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
    //农户列表
    public  function  index(){
        $where=" 1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            //关键字
            if($keyword){
                $info['keyword']=urldecode(trim(str_replace("'","",$info['keyword'])));
                $where.=" and (ec_user.realname like '%".$info['keyword']."%' or ec_user.phone like '%".$info['keyword']."%')";
                $this->assign('keyword',$info['keyword']);
            }
            //余额最小
            if($minMoney === ''){
                $this->assign('minMoney','');
            }else{
                $minMoney = ($info['minMoney'] == 0) ? 0 : floatval(trim($info['minMoney']));
                $where.=" and ec_user.money >= ".$minMoney;
                $this->assign('minMoney',$minMoney);
            }
            //余额最大
            if($maxMoney === ''){
                $this->assign('maxMoney','');
            }else{
                $maxMoney = ($info['maxMoney'] == 0) ? 0 : floatval(trim($info['maxMoney']));
                $where.=" and ec_user.money <= ".$maxMoney;
                $this->assign('maxMoney',$maxMoney);
            }
            //上级电话
            if($refree_phone){
                $info['refree_phone']=urldecode(trim($info['refree_phone']));
                $where.=" and refreeUser.realname like '%".$info['refree_phone']."%'";
                $this->assign('refree_phone',$info['refree_phone']);
            }
            //开始时间
            if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_user.addtime >=".strtotime($info['starttime']."00:00:00")." ";
                $this->assign('starttime',$info['starttime']);
            }
            //结束时间
            if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_user.addtime <=".strtotime($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
            //农户状态
            if($user_status){
                $info['user_status']=urldecode(trim($info['user_status']));
                $where.=" and ec_user.user_status = ".$info['user_status'];
                $this->assign('user_status',$info['user_status']);
            }
        }
        $user=M('User');
        // 查询满足要求的总记录数
        $count      = $user
            ->where($where)
            ->join('left join ec_user as refreeUser on refreeUser.user_id = ec_user.refree_id')
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
        $listinfo = $user
            ->where($where)
            ->join('left join ec_user as refreeUser on refreeUser.user_id = ec_user.refree_id')
            ->order('ec_user.user_id desc')
            ->field('ec_user.*,refreeUser.realname as refree_realname,refreeUser.nickname as refree_nickname ')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        foreach ($listinfo as $key =>$value){
            $listinfo[$key]['team_count']=M('user')->where('refree_id = '.$value['user_id'])->count();
            $listinfo[$key]['realname'] = $value['realname']?$value['realname']:$value['nickname'];
            $listinfo[$key]['refree_realname'] = $value['refree_realname']?$value['refree_realname']:$value['refree_nickname'];
        }
        $this->assign('listinfo',$listinfo);
        $this->assign('userStatus',D('user')->userStatus());
        $this->assign('pageshow',$pageshow);
        $this->display();
    }
    
    //用户查看用户详情
    public function xiangqing(){
        $user_id=I('get.user_id');
        $user=M("user")
            ->where("ec_user.user_id=".$user_id)
            ->find();
        $user['realname'] = $user['realname']?$user['realname']:$user['nickname'];    
        $this->assign('userStatus',D('user')->userStatus());
        $this->assign('getReferName',D('user')->getUserName());
        $this->assign('user',$user);
        $this->display();
    }

    //拉黑某一用户OK
    public function addBlack(){
        $user_id=I('get.user_id');
        //用户状态 1 正常，2 黑名单
        $data['user_status']=2;
        M('user')->where('user_id = '.$user_id)->save($data);
        jump('该用户已拉黑！',U('user/index'));
    }
    //恢复正常
    public function recover(){
        $user_id=I('get.user_id');
        //用户状态 1 正常，2 黑名单
        $data['user_status']=1;
        M('user')->where('user_id = '.$user_id)->save($data);
        jump('该用户已恢复正常！',U('user/index'));
    }
	  //我的团队
    public function myteam(){
        $user_id=I('get.user_id');
        $parent=I('get.parent');
        $where="refree_id = ".$user_id;
        $info=I("info");
        if ($info){
            @extract($info);
            if($keyword){
                $info['keyword']=urldecode(trim(str_replace("'","",$info['keyword'])));
                $where.="  and (ec_user.realname like '%".$info['keyword']."%' or ec_user.phone like '%".$info['keyword']."%') ";
                $this->assign('keyword',$info['keyword']);
            }
            if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_user.addtime >=".strtotime($info['starttime']."00:00:00")." ";
                $this->assign('starttime',$info['starttime']);
            }
            if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_user.addtime <=".strtotime($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
        }
        $user=M('User');
        // 查询满足要求的总记录数
        $count      = $user->where($where)->count();
        $Page       = new \Think\Page($count,15);
        //分页跳转的时候保证查询条件
        if($info){
            foreach($info as $key=>$val) {
                $Page->parameter['info['.$key.']']   =   urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        $myTeam=$user->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach ($myTeam as $key =>$value){
            $myTeam[$key]['subteam_count']=M('user')->where('refree_id = '.$value['user_id'])->count();
        }
        $this->assign('myTeam',$myTeam);
        $this->assign('user_id',$user_id);
        $this->assign('parent',$parent);
        $this->assign('pageshow',$pageshow);
        $this->assign('getReferName',D('user')->getUserName());
        $this->assign('userStatus',D('user')->userStatus());
        $this->display();
    }
    //提现记录
    public function popinfo(){
        $info=I("info");
		$where = "1 ";
        if ($info){
            @extract($info);
            if($keyword){
                $info['keyword']=urldecode(trim(str_replace("'","",$info['keyword'])));
                $where.=" and (ec_user.realname like '%".$info['keyword']."%' or ec_user.phone like '%".$info['keyword']."%' or ec_popinfo.pop_remarks like '%".$info['keyword']."%')";
                $this->assign('keyword',$info['keyword']);
            }
            //申请状态
            if($pop_flag){
                $info['pop_flag']=urldecode(trim($info['pop_flag']));
                $where.=" and ec_popinfo.pop_flag = ".$info['pop_flag'];
                $this->assign('pop_flag',$info['pop_flag']);
            }
            if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_popinfo.pop_addtime >=".strtotime($info['starttime']."00:00:00")." ";
                $this->assign('starttime',$info['starttime']);
            }
            if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_popinfo.pop_addtime <=".strtotime($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
        }
        $pop=M('popinfo');
        // 查询满足要求的总记录数
        $count    = $pop ->where($where)
            ->join('left join ec_user on ec_user.user_id = ec_popinfo.user_id')
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
        $popInfo = $pop
            ->join('left join ec_user on ec_user.user_id = ec_popinfo.user_id')
            ->where($where)
            ->order('ec_popinfo.pop_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        foreach($popInfo as $k=>$v){
             $popInfo[$k]['realname'] = $v['realname']?$v['realname']:$v['nickname'];
        }    
        $this->assign('popPaySatuas',D('user')->popPaySatuas());
        $this->assign('popFlag',D('user')->popFlag());
        $this->assign('popInfo',$popInfo);
        $this->assign('pageshow',$pageshow);
        $this->display();
    }
	//用户提现详情
    public function details(){
        $pop_id=I('get.pop_id');
        $popDetails=M("popinfo")
            ->join('left join ec_user on ec_user.user_id = ec_popinfo.user_id')
            ->where("ec_popinfo.pop_id =".$pop_id)
            ->find();
        $popDetails['realname'] = $popDetails['realname']?$popDetails['realname']:$popDetails['nickname'];        
        $this->assign('popPaySatuas',D('user')->popPaySatuas());
        $this->assign('popFlag',D('user')->popFlag());
        $this->assign('getAdminName',D('user')->getAdminName());
        $this->assign('pop',$popDetails);
        $this->display();
    }
	 public function adopt(){
        $pop_id=I('get.pop_id');
        $popinfo=M('popinfo')
            ->where('ec_popinfo.pop_id = '.$pop_id)
            ->join('left join ec_user on ec_user.user_id=ec_popinfo.user_id')
            ->find();
        $this->assign('popinfo',$popinfo);
        $this->display();
    }
//    //通过提现申请
    public function adopted(){
        $admin_id=session('admin.admin_id');
        $pop_id=I('post.pop_id');
        $remark=I('post.pop_reviewnotesr');
		$data['pop_flag']=2;
		$data['pop_reviewnotesr']=$remark;
        $flag=M('popinfo')->where('pop_id = '.$pop_id)->save($data);
        //增加消息通知
        $pop=M('popinfo')->where('ec_popinfo.pop_id = '.$pop_id)
            ->join('left join ec_user on ec_user.user_id=ec_popinfo.user_id')
            ->find();
        $message= new \Admin\Model\MessageModel();
        $message->getmessage_($pop['user_id'],1,'提现申请通知',$pop['realname'].'您的提现申请通过审核，提现金额48小时内到账，请耐心等待！');
         if($flag){
            $this->home_success('已通过提现申请！', U('user/popinfo'), 3);
        }else{
            $this->home_error('提现申请失败！', U('user/popinfo'), 3);
        }
    }
    //驳回提现申请
    public function refuse(){
        $pop_id=I('get.pop_id');
        $pop=M('popinfo')
            ->where('ec_popinfo.pop_id = '.$pop_id)
            ->join('left join ec_user on ec_user.user_id=ec_popinfo.user_id')
            ->find();
        $this->assign('popinfo',$pop);
        $this->display();
    }
	
    public function refused(){
        $admin_id=session('admin.admin_id');
        $pop_id=I('post.pop_id');
        $remark=I('post.pop_reviewnotesr');
		$data['pop_flag']=3;
		$data['pop_reviewnotesr']=$remark;
        $flag=M('popinfo')->where('pop_id = '.$pop_id)->save($data);
        //增加消息通知
        $pop=M('popinfo')->where('ec_popinfo.pop_id = '.$pop_id)
            ->join('left join ec_user on ec_user.user_id=ec_popinfo.user_id')
            ->find();
		//驳回成功时增加商户余额
		$falg1=M('User')->where('user_id='.$pop['user_id'])->setInc('money', abs($pop['pop_money']));	
        $message= new \Admin\Model\MessageModel();
        $message->getmessage_($pop['user_id'],1,'提现申请通知',$pop['realname'].'您的提现申请未通过审核，请确保信息无误再进行操作！');
        if($flag&&$falg1){
            $this->home_success('已驳回提现申请！', U('user/popinfo'), 3);
        }else{
            $this->home_error('驳回提现申请失败！', U('user/popinfo'), 3);
        }
    }
	 //农户返佣 返佣表 ec_income
    public function userincome(){
        $where="1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($keyword){
                $info['keyword']=urldecode(trim(str_replace("'","",$info['keyword'])));
                $where.=" and (ec_user.realname like '%".$info['keyword']."%' or ec_user.phone like '%".$info['keyword']."%')";
                $this->assign('keyword',$info['keyword']);
            }
            if($income_back){
                $info['income_back']=urldecode(trim($info['income_back']));
                $where.=" and ec_income.income_back = ".$info['income_back'];
                $this->assign('income_back',$info['income_back']);
            }
            if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_income.income_time >=".strtotime($info['starttime']."00:00:00")." ";
                $this->assign('starttime',$info['starttime']);
            }
            if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_income.income_time <=".strtotime($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
        }
        $income=M('income');
        // 查询满足要求的总记录数
        $count    = $income ->where($where)
                            ->join('left join ec_user on ec_user.user_id = ec_income.user_id')
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
        $incomeInfo = $income
            ->join('left join ec_user on ec_user.user_id = ec_income.user_id')
            ->where($where)
            ->order('ec_income.income_id desc')
			->field('ec_income.*,ec_user.realname,ec_user.phone,ec_user.nickname')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        foreach($incomeInfo as $k=>$v){
             $incomeInfo[$k]['realname'] = $v['realname']?$v['realname']:$v['nickname'];
        }     
        $this->assign('incomeStatus',D('user')->incomeStatus());
        $this->assign('incomeInfo',$incomeInfo);
        $this->assign('pageshow',$pageshow);
        $this->display();
    }
	//农户关注列表
	public function attention(){
		$user_id=I("get.user_id");
		if($user_id){
			$where = 'ec_attention.user_id='.$user_id.' ';
		}else{
			$where = '1 ';
		}
		$info = I('info');
		 if ($info){
            @extract($info);
            if($keyword){
                $info['keyword']=urldecode(trim(str_replace("'","",$info['keyword'])));
                $where.=" and (ec_user.realname like '%".$info['keyword']."%' or ec_agcy.agcy_name like '%".$info['keyword']."%')";
                $this->assign('keyword',$info['keyword']);
            }
            if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_attention.attention_addtime >=".strtotime($info['starttime']."00:00:00")." ";
                $this->assign('starttime',$info['starttime']);
            }
            if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_attention.attention_addtime <=".strtotime($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
        }
		$atten = M('attention');
		$count = $atten->where($where)
		       ->join('left join ec_user on ec_user.user_id = ec_attention.user_id')
			   ->join('left join ec_agcy on ec_agcy.agcy_id = ec_attention.agcy_id')
			   ->count();
	    $Page = new \Think\Page($count,15);
		 if($info){
            foreach($info as $key=>$val) {
                $Page->parameter['info['.$key.']']   =   urlencode($val);
            }
        }
        $pageshow  = $Page->adminshow();
		$attention = $atten->where($where)
		       ->join('left join ec_user on ec_user.user_id = ec_attention.user_id')
			   ->join('left join ec_agcy on ec_agcy.agcy_id = ec_attention.agcy_id')
			   ->order('ec_attention.attention_id desc')
			   ->limit($Page->firstRow.','.$Page->listRows)
			   ->select();
        foreach($attention as $k=>$v){
             $attention[$k]['realname'] = $v['realname']?$v['realname']:$v['nickname'];
        }        
	    $this->assign('user_id',$user_id);
	    $this->assign('attention',$attention);
        $this->assign('pageshow',$pageshow);
        $this->display();
	}
}