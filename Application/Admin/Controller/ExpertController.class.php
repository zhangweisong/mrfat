<?php
namespace Admin\Controller;
use Think\Controller;
class ExpertController extends Controller {
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
	
	//专家列表
    public function index(){
		$where=" 1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword']));
                $where.=" and (ec_expert.expert_realname like '%" . $info['keyword'] . "%' or ec_expert.expert_phone like '%" . $info['keyword'] . "%'   or ec_expert.expert_nickname like '%" . $info['keyword'] . "%' )  ";  
                $this->assign('keyword',urldecode($info['keyword']));
			}
            //职称
            if($expert_pessional){
				$info['expert_pessional']=urldecode(trim($info['expert_pessional']));
                $where.="and ec_expert.expert_pessional ='".$info['expert_pessional']."' ";  
                $this->assign('expert_pessional',urldecode($info['expert_pessional']));
				
			}
            //认证
			if($expert_isapprove){
				$info['expert_isapprove']=urldecode(trim($info['expert_isapprove']));
                $where.="and ec_expert.expert_isapprove ='".$info['expert_isapprove']."' ";  
                $this->assign('expert_isapprove',urldecode($info['expert_isapprove']));
				
			}
            //状态
            if($expert_state){
				$info['expert_state']=urldecode(trim($info['expert_state']));
                $where.="and ec_expert.expert_state ='".$info['expert_state']."' ";  
                $this->assign('expert_state',urldecode($info['expert_state']));
				
			}
            //开始时间
            if($starttime){
                $info['starttime']=urldecode(trim($info['starttime']));
                $where.=" and ec_expert.expert_addtime >=".strtotime($info['starttime']."00:00:00")." ";
                $this->assign('starttime',$info['starttime']);
            }
            //结束时间
            if($endtime){
                $info['endtime']=urldecode(trim($info['endtime']));
                $where.=" and ec_expert.expert_addtime <=".strtotime($info['endtime']."23:59:59")." ";
                $this->assign('endtime',$info['endtime']);
            }
		}
		$expert=M('expert');
		// 查询满足要求的总记录数
		$count      = $expert
                    ->where($where)
                    ->join('left join ec_savantclassify on ec_savantclassify.sclassify_id=ec_expert.expert_pessional')->count();
		$Page       = new \Think\Page($count,20);
		//分页跳转的时候保证查询条件
		if($info){
			foreach($info as $key=>$val) {
				$Page->parameter['info['.$key.']']   =   urlencode($val);
			}
		}
		$pageshow   = $Page->adminshow();
		// 进行分页数据查询
		$listinfo =  $expert
					->join('left join ec_savantclassify on ec_savantclassify.sclassify_id=ec_expert.expert_pessional')
					->where($where)
					->order('ec_expert.expert_id desc')
                    ->field('ec_expert.*,ec_savantclassify.sclassify_name as sclassify_name')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$this->assign('listinfo',$listinfo);
		$this->assign('pageshow',$pageshow);
        //专家状态 1 正常，2禁用
		$this->assign('expert_states',D('expert')->expert_states());
        //认证状态  1已认证 0未认证
		$this->assign('expert_isapproves',D('expert')->expert_isapproves());
        //专家职称
		$this->assign('expert_pessionals',D('expert')->expert_pessionals());
		$this->display();
    }

    
	//添加
    public function add(){
		if (IS_POST){
			$info=I("info");
            $expert_username=trim($info['expert_username']);
			$count_=M('expert')->where(" expert_username='".$expert_username."' ")->count();
			if($count_>0){
				jump('添加专家失败,此用户名已存在！',U('expert/index'));
			}
            $info['expert_pwd']=md5('a123456');
            $info['expert_avator']=C('WEB_URL').'Public/admin/images/tou.png';
            $info['expert_addtime']=time();
            $info['expert_isapprove']=1;
			$flag=M("expert")->add($info);//echo $flag;die;
			if($flag){
				jump('添加专家成功！',U('expert/index'));
				
			}else{
				jump('添专家失败！',U('expert/index'));
			}
		}else{
            //专家职称
            $this->assign('expert_pessionals',D('expert')->expert_pessionals());
			$this->display();
		}
    }

	//编辑
    public function edit(){
	    $expert_id=I('get.expert_id');
        if (IS_POST){
            $info=I("info");
            $flag=M("expert")->where("expert_id=".$expert_id)->save($info);
            if($flag){
                jump('编辑专家成功！',U('expert/index'));
            }else{
                jump('编辑专家失败！',U('expert/index'));
            }
        }else{
            //专家
            $expert=M("expert")->where("expert_id=".$expert_id)->find();
            $expert_images=explode('|',$expert['expert_image']);
            $this->assign('expert',$expert);
            $this->assign('expert_images',$expert_images);
            $this->assign('expert_pessionals',D('expert')->expert_pessionals());
            $this->display();
        }
    }
    
    //专家认证 认证状态  1未认证 2已认证
    public function renzheng() {
        $expert_id = I("get.expert_id");
        M('expert')->where('expert_id=' . $expert_id)->save(array('expert_isapprove'=>2));
        jump('专家认证成功！', U('expert/index'));
    }

    //重置密码
    public function repassword() {
        $expert_id = I("get.expert_id");
        $info['expert_pwd'] = md5('a123456');
        M('expert')->where('expert_id=' . $expert_id)->save($info);
        jump('密码重置成功！', U('expert/index'));
    }
    
    //查看详情
    public function xiangqing(){
        $expert_id=I('get.expert_id');
        $expert=M("expert")
                ->join('left join ec_savantclassify on ec_savantclassify.sclassify_id=ec_expert.expert_pessional')
                ->where("ec_expert.expert_id=".$expert_id)
                ->find();
        //专家状态 1 正常，2禁用
		$this->assign('expert_states',D('expert')->expert_states());
        //认证状态  1已认证 0未认证
		$this->assign('expert_isapproves',D('expert')->expert_isapproves());
        //专家职称
		$this->assign('expert_pessionals',D('expert')->expert_pessionals());
        $image=explode('|',$expert['expert_image']);
        $this->assign('image',$image);
		$this->assign('expert',$expert);
        $this->display();
    }
    
    //ajax处理状态
	public function ajaxable_on(){
		$expert_id=I("get.expert_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "expert_state"://on
				M("expert")->where('expert_id='.$expert_id)->save(array("expert_state"=>1));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	public function ajaxable_off(){
		$expert_id=I("get.expert_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "expert_state"://off
				M("expert")->where('expert_id='.$expert_id)->save(array("expert_state"=>2));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	
}