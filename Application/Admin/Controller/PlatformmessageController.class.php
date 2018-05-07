<?php
namespace Admin\Controller;
use Think\Controller;
class PlatformmessageController extends Controller {

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

    //用户消息
    public function usermessage(){
        $where="1 and ec_message.msg_type=1  ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($msg_title){//标题
                $info['msg_title']=urldecode(trim($info['msg_title']));
                $where.="and (ec_message.msg_title like '%".$info['msg_title']."%')";
                $this->assign('msg_title',$info['msg_title']);
            }
			if($realname){//用户名
                $info['realname']=urldecode(trim($info['realname']));
                $where.="and (ec_user.realname like '%".$info['realname']."%')";
                $this->assign('realname',$info['realname']);
            }
            if($msg_flag){//消息状态
                $info['msg_flag']=urldecode(trim($info['msg_flag']));
                $where.="and ec_message.msg_flag ='".$info['msg_flag']."'";
                $this->assign('msg_flag',$info['msg_flag']);
            }
			//消息时间
			if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_message.msg_addtime >=".strtotime_2($info['starttime']."00:00:00")." ";  
                $this->assign('starttime',$info['starttime']);
            }
           	if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_message.msg_addtime <=".strtotime_2($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
        }
        $message=M('Message');
        // 查询满足要求的总记录数
        $count      = $message
					->join("left join ec_user on ec_user.user_id=ec_message.user_id")
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
        $listinfo = $message
			->join("left join ec_user on ec_user.user_id=ec_message.user_id")
            ->where($where)
			->field("ec_message.*,ec_user.realname as realname,ec_user.nickname")
            ->order('ec_message.msg_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        foreach($listinfo as $k=>$v){
            $listinfo[$k]['realname'] = $v['realname']?$v['realname']:$v['nickname'];
        }    
        $this->assign('listinfo',$listinfo);
        $this->assign('pageshow',$pageshow);
        $this->assign('msgFlag',D("Message")->msg_flags());//消息状态
        $this->display();
    }

    //经销商消息
    public function agcymessage(){
        $where="1 and ec_message.msg_type=2  ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($msg_title){//标题
                $info['msg_title']=urldecode(trim($info['msg_title']));
                $where.="and (ec_message.msg_title like '%".$info['msg_title']."%')";
                $this->assign('msg_title',$info['msg_title']);
            }
			if($realname){//店铺名
                $info['realname']=urldecode(trim($info['realname']));
                $where.="and (ec_agcy.agcy_name like '%".$info['realname']."%')";
                $this->assign('realname',$info['realname']);
            }
            if($msg_flag){//消息状态
                $info['msg_flag']=urldecode(trim($info['msg_flag']));
                $where.="and ec_message.msg_flag ='".$info['msg_flag']."'";
                $this->assign('msg_flag',$info['msg_flag']);
            }
			//消息时间
			if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_message.msg_addtime >=".strtotime_2($info['starttime']."00:00:00")." ";  
                $this->assign('starttime',$info['starttime']);
            }
           	if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_message.msg_addtime <=".strtotime_2($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
        }
        $message=M('Message');
        // 查询满足要求的总记录数
        $count      = $message
					->join("left join ec_agcy on ec_agcy.agcy_id=ec_message.agcy_id")
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
        $listinfo = $message
			->join("left join ec_agcy on ec_agcy.agcy_id=ec_message.agcy_id")
            ->where($where)
			->field("ec_message.*,ec_agcy.agcy_name as realname ")
            ->order('ec_message.msg_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('listinfo',$listinfo);
        $this->assign('pageshow',$pageshow);
        $this->assign('msgFlag',D("Message")->msg_flags());//消息状态
        $this->display();
    }
    //专家消息
    public function expertmessage(){
        $where="1 and ec_message.msg_type=3  ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($msg_title){//标题
                $info['msg_title']=urldecode(trim($info['msg_title']));
                $where.="and (ec_message.msg_title like '%".$info['msg_title']."%')";
                $this->assign('msg_title',$info['msg_title']);
            }
			if($realname){//店铺名
                $info['realname']=urldecode(trim($info['realname']));
                $where.="and (ec_expert.expert_realname like '%".$info['realname']."%')";
                $this->assign('realname',$info['realname']);
            }
            if($msg_flag){//消息状态
                $info['msg_flag']=urldecode(trim($info['msg_flag']));
                $where.="and ec_message.msg_flag ='".$info['msg_flag']."'";
                $this->assign('msg_flag',$info['msg_flag']);
            }
			//消息时间
			if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_message.msg_addtime >=".strtotime_2($info['starttime']."00:00:00")." ";  
                $this->assign('starttime',$info['starttime']);
            }
           	if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_message.msg_addtime <=".strtotime_2($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
        }
        $message=M('Message');
        // 查询满足要求的总记录数
        $count      = $message
					->join("left join ec_expert on ec_expert.expert_id=ec_message.expert_id")
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
        $listinfo = $message
			->join("left join ec_expert on ec_expert.expert_id=ec_message.expert_id")
            ->where($where)
			->field("ec_message.*,ec_expert.expert_realname as realname ")
            ->order('ec_message.msg_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('listinfo',$listinfo);
        $this->assign('pageshow',$pageshow);
        $this->assign('msgFlag',D("Message")->msg_flags());//消息状态
        $this->display();
    }
    //专家消息
    public function factorymessage(){
        $where="1 and ec_message.msg_type=4  ";
        $info=I("info");
        if ($info){ 
            @extract($info);
            if($msg_title){//标题
                $info['msg_title']=urldecode(trim($info['msg_title']));
                $where.="and (ec_message.msg_title like '%".$info['msg_title']."%')";
                $this->assign('msg_title',$info['msg_title']);
            }
			if($realname){//名
                $info['realname']=urldecode(trim($info['realname']));
                $where.="and (ec_factory.factory_name like '%".$info['realname']."%')";
                $this->assign('realname',$info['realname']);
            }
            if($msg_flag){//消息状态
                $info['msg_flag']=urldecode(trim($info['msg_flag']));
                $where.="and ec_message.msg_flag ='".$info['msg_flag']."'";
                $this->assign('msg_flag',$info['msg_flag']);
            }
			//消息时间
			if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_message.msg_addtime >=".strtotime_2($info['starttime']."00:00:00")." ";  
                $this->assign('starttime',$info['starttime']);
            }
           	if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_message.msg_addtime <=".strtotime_2($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
        }
        $message=M('Message');
        // 查询满足要求的总记录数
        $count      = $message
					->join("left join ec_factory on ec_factory.factory_id=ec_message.factory_id")
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
        $listinfo = $message
			->join("left join ec_factory on ec_factory.factory_id=ec_message.factory_id")
            ->where($where)
			->field("ec_message.*,ec_factory.factory_name as realname ")
            ->order('ec_message.msg_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('listinfo',$listinfo);
        $this->assign('pageshow',$pageshow);
        $this->assign('msgFlag',D("Message")->msg_flags());//消息状态
        $this->display();
    }
    //删除
    public function del(){
        $msg_id=$_GET['msg_id'];
        $msg_type=$_GET['msg_type'];
        $del=M('Message')->where('msg_id ='.$msg_id)->delete();
        if($msg_type==1){
          if($del){
                jump('删除消息成功！',U('platformmessage/usermessage'));
            }else{
                jump('删除消息失败！',U('platformmessage/usermessage'));
            }  
            
        }else if($msg_type==2){
             if($del){
                jump('删除消息成功！',U('platformmessage/agcymessage'));
            }else{
                jump('删除消息失败！',U('platformmessage/agcymessage'));
            } 
        }else if($msg_type==3){
             if($del){
                jump('删除消息成功！',U('platformmessage/expertmessage'));
            }else{
                jump('删除消息失败！',U('platformmessage/expertmessage'));
            } 
        }else if($msg_type==4){
             if($del){
                jump('删除消息成功！',U('platformmessage/factorymessage'));
            }else{
                jump('删除消息失败！',U('platformmessage/factorymessage'));
            } 
        }
        
    }
}