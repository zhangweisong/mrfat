<?php
namespace Admin\Controller;
use Think\Controller;
class bankController extends Controller {

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

    //默认首页（农户绑定银行卡表）
    public function index(){
        $where="1  ";
        $info=I("info");
        if ($info){
            @extract($info);            
			if($realname){//用户名或手机号
                $info['realname']=urldecode(trim($info['realname']));
                $where.="and (ec_user.realname like '%".$info['realname']."%' or ec_user.phone like '%".$info['realname']."%')";
                $this->assign('realname',$info['realname']);
            }
            if($bank_flag){//是否默认
                $info['bank_flag']=urldecode(trim($info['bank_flag']));
                $where.="and ec_bindbank.bank_flag ='".$info['bank_flag']."'";
                $this->assign('bank_flag1',$info['bank_flag']);
            }
			
        }
        $bindbank=M('bindbank');
        // 查询满足要求的总记录数
        $count      = $bindbank
					->join("left join ec_user on ec_user.user_id=ec_bindbank.user_id")
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
        $listinfo = $bindbank
			->join("left join ec_user on ec_user.user_id=ec_bindbank.user_id")
            ->where($where)
			->field("ec_bindbank.*,ec_user.realname as realname,ec_user.phone,ec_user.nickname ")
            ->order('ec_bindbank.bank_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        foreach($listinfo as $k=>$v){
             $listinfo[$k]['realname'] = $v['realname']?$v['realname']:$v['nickname'];
        }    
        $this->assign('listinfo',$listinfo);
        $this->assign('pageshow',$pageshow);
        $this->assign('bank_flag',D("bindbank")->bank_flag());//是否默认
        $this->display();
    }

    //删除
    public function del(){
        $bank_id=$_GET['bank_id'];
        $del=M('bindbank')->where('bank_id ='.$bank_id)->delete();
        if($del){
            jump('删除消息成功！',U('bank/index'));
        }else{
            jump('删除消息失败！',U('bank/index'));
        }
    }
   }