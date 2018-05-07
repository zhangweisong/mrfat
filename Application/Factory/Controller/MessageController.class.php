<?php
namespace Factory\Controller;
use Think\Controller;
class MessageController extends Controller {
    
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

    //默认首页(消息)
    public function index(){
        $factory_id=session('factory.factory_id');
        $where="1 and ec_message.msg_type=4 and ec_message.factory_id=".$factory_id." ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($msg_title){//标题
                $info['msg_title']=urldecode(trim($info['msg_title']));
                $where.="and (ec_message.msg_title like '%".$info['msg_title']."%')";
                $this->assign('msg_title',$info['msg_title']);
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
        $del=M('Message')->where('msg_id ='.$msg_id)->delete();
        if($del){
            jump('删除消息成功！',U('message/index'));
        }else{
            jump('删除消息失败！',U('message/index'));
        }
    }
   }