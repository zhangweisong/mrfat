<?php
namespace User\Controller;
use Think\Controller;
class IndexController extends Controller
{
		function __construct() {
		//继承父类
		parent::__construct();
		//判断登录状态
		//session("user_id", null);exit();
        //引入WxpayAPI 判断是否关注公众号
        /*
        vendor('WxpayAPI.WxPayJsApiPay');
		$jsApi = new \JsApiPay();
		$userinfo = $jsApi->GetUserinfoU(); 
        if($userinfo['subscribe']==0){ //判断用户是否关注公众号
            //跳转关注公众号页面				
            header("Location:".U('policy/attention'));
            exit();
        }*/
         //dd(session('user_id'));    
        //分两种，一种是直接扫商家的码进来的，第二种是扫推荐人的码进来的 
		$user_id = I('get.user_id');
		$agcy_id = I('get.agcy_id'); 
	    if (!session("user_id")) {
			session("user_id", null);
			header("Location:" . WEB_HOST . "index.php/user/weixin/wxlogin/agcy_id/" . $agcy_id."/user_id/".$user_id);
			exit();
	    } else {
           // dd(session('user_id'));
            //通过推荐人的码扫进来的
			//session user_id存在不等于undefined或者数据库不存在当前用户
			$id = M("User")->where('user_id=' . intval(session("user_id")))->find();//查询当前登录的农户
           
           
            $atten = M('attention')->where("user_id='".$id['user_id']."' and agcy_id='".$agcy_id ."' ")->find(); 
            if(!$atten){
                //关注列表添加数据
                if($agcy_id!="" && $agcy_id!=0){ 
                    $data = array(
                       'refree_id'=>$user_id,
                       'user_id'=>$id['user_id'],
                       'agcy_id'=>$agcy_id,
                       'attention_addtime'=>time(),
                    );
                }
                //数据添加到关注表中
                M('attention')->add($data);
                //将上级更新在user表
                //M('User')->where('user_id='.session('user_id'))->save('refree_id',$user_id);
            }
            
			if (!$id) {
				header("Location:" . WEB_HOST . "index.php/user/weixin/wxlogin/agcy_id/" . $agcy_id."/user_id/".$user_id);
				exit();
			}
	    }
    }

	//空方法，防止报错
	public function _empty(){
        $this->index();
    }
	public function index(){	
		$user_id = session('user_id');
		//轮播图
		$tupian = M('focusinfo')->where('focus_type=3')->order('focus_sort asc')->select();
		//我的关注
		$followlist = M('attention')
				 ->where('ec_agcy.agcy_state=1 and user_id='.$user_id )
				 ->join('left join ec_agcy on ec_agcy.agcy_id = ec_attention.agcy_id')
				 ->order('attention_id desc')
				 ->select();
		//dump($followlist);		 
		$arr=array();
		foreach($followlist as $k=>$v){
			 $arr = explode("|",$v['agcy_face_images']);
			 $followlist[$k]['agcy_face_images'] = $arr[0];
		}
		unset($arr);
		//农技知识
        $skillclass=M('slcropclass')
            ->where(['cpc_isable' => '1','cpc_fid'=> '0'])
            ->order('cpc_id asc')
            ->limit(0,6)
            ->select();
		$this->assign('tupian',$tupian);
	    $this->assign('skillclass',$skillclass);
	    $this->assign('followlist',$followlist);
		$this->assign('user_id',$user_id);
		$this->display();
	}
}