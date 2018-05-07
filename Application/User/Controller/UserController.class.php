<?php 
namespace User\Controller;
use Think\Controller;
class UserController extends Controller{
	function __construct() {
        $this->getuserid();
        //继承父类
        parent::__construct();
    }
    public function show_session(){
        dd($_SESSION);
    }
    //空方法，防止报错
    public function _empty(){
        $this->redirect("userinfo"); 
    }
    public function getuserid(){
        $user_id=session('user_id');
        if($user_id==""){//未登录
            header("Location:".U('index/index'));
            exit();
        }
        return $user_id;
    }
    /*
	用户个人中心
    */
    public function userinfo(){ 
    	//基本信息
    	$user_id=$this->getuserid();
    	$info=M('user')->where("user_id=$user_id")->field('avatar,nickname,phone,money,user_id')->find();
       
    	//关注店铺
    	$info['dp_num']=M('attention')->where("user_id=".$info['user_id']."")->count(); 
    	//未读消息数量
    	$info['me_num']=M('message')->where("user_id=".$info['user_id']." and msg_type=1 and msg_flag=1")->count();
    	//手机号隐藏4位
    	$info['phone']=trim($info['phone']);
    	if(!empty(trim($info['phone']))){
			$info['phone']= substr_replace($info['phone'],'****',3,4);
    	}
    	$this->assign('info',$info);
    	$this->display();

    }
    /*
	用户信息详情
    */
	public function info_detail(){
        $user_id=$this->getuserid();
        $info=M("user")->where("user_id=$user_id")->find();
        if(!empty(trim($info['phone']))){
            $info['phone']=substr_replace($info['phone'],'****',3,4);
        }
        if(!empty($info['realname'])){
            $info['realname']=substr_cut($info['realname']);
        }
        //dd($info);
        $this->assign('info',$info);
		$this->display();
	}
   
    /*
    修改真实姓名
    */
    public function edit_realname(){
        $user_id=$this->getuserid();
        if(IS_AJAX){
            $data['user_id']=$user_id;
            $data['realname']=filterEmoji(I("post.realname"),2);
            if(in_array('',$data)){
                $this->ajaxReturn(array("data"=>1,"msg"=>"参数错误"),'json');    
                exit;      
            }
            $if_suc=M("user")->save($data);
            if($if_suc!==false){
                $this->ajaxReturn(array("data"=>2,"msg"=>"修改成功"),'json');    
            }else{
                $this->ajaxReturn(array("data"=>3,"msg"=>"修改失败"),'json');    
            }
            exit;
        }
        $info=M("user")->where("user_id=$user_id")->field("realname")->find();
        $this->assign('info',$info);
        $this->display();
    }
    /*
    修改微信地址
    */
    public function edit_wx_address(){
        $user_id=$this->getuserid();
        if(IS_AJAX){
            $data['user_id']=$user_id;
            $data['address']=filterEmoji(I("post.address"),2);
            if(in_array('',$data)){
                $this->ajaxReturn(array("data"=>1,"msg"=>"参数错误"),'json');    
                exit;      
            }
            $if_suc=M("user")->save($data);
            if($if_suc!==false){
                $this->ajaxReturn(array("data"=>2,"msg"=>"修改成功"),'json');    
            }else{
                $this->ajaxReturn(array("data"=>3,"msg"=>"修改失败"),'json');    
            }
            exit;
        }
        
        $info=M("user")->where("user_id=$user_id")->field("address")->find();
        $this->assign('info',$info);
        $this->display();
    }
     /*
    修改手机号
    */
    public function edit_phone(){ 
        $this->display();
    }
     
    public function edit(){  
        $ser_yzm = session('ser_yzm'); //发的验证码 
        $tels = session('tel');
        $phones = trim(I('phone')); 
        $yzm  = trim(I('code')); 
        $count = M('User')->where("phone='".$phones."' ")->find();
        if (empty($count)) {
           if ( $yzm != "" && $yzm == $ser_yzm) {
                if($tels==$phones){
                    $flag = M('user')->where("user_id='".session('user_id')."'")->save(array('phone'=>$phones));
                    if ($flag) {
                        $this->ajaxReturn(array('msg'=>'电话修改成功','status'=>'2'));
                    }else{
                        $this->ajaxReturn(array('msg'=>'电话修改失败','status'=>'-1'));
                    } 
                }else{
                    $this->ajaxReturn(array('msg'=>'电话修改失败','status'=>'-1'));
                }      
           }else{
                $this->ajaxReturn(array('msg'=>'验证码有误，请重新输入!','status'=>'1'));
           }
        }else{
            $this->ajaxReturn(array('msg'=>'手机号码已经存在!','status'=>'0'));
        }
    }
    //发短信，获取验证码
    public function smsCode(){
        //生成验证码
        //$msg=rand(123456, 999999);//;;
        $msg=123456;
        session('ser_yzm',$msg);
        $tel=I('post.phone'); 
        session('tel',$tel);
        $verify ="【爱我爱你】您的动态验证码为".$msg."请在页面输入完成验证。如非本人操作请忽略。";
        //以下信息自己填以下
        $mobile=$tel; 
      //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

		//创蓝发送短信接口URL, 请求地址请参考253云通讯自助通平台查看或者询问您的商务负责人获取
		$chuanglan_config['api_send_url'] = 'http://smssh1.253.com/msg/send/json';
		//创蓝变量短信接口URL, 请求地址请参考253云通讯自助通平台查看或者询问您的商务负责人获取
		$chuanglan_config['API_VARIABLE_URL'] = 'http://smssh1.253.com/msg/variable/json';
		 
		//创蓝短信余额查询接口URL, 请求地址请参考253云通讯自助通平台查看或者询问您的商务负责人获取
		$chuanglan_config['api_balance_query_url'] = 'http://smssh1.253.com/msg/balance/json';
		//创蓝账号 替换成你自己的账号
		$chuanglan_config['api_account']    = 'CN0721220';

		//创蓝密码 替换成你自己的密码
		$chuanglan_config['api_password']   = 'elVdcw0Zrv1732';

		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		//创蓝接口参数
		$postArr = array (
			'account'  =>  $chuanglan_config['api_account'],
			'password' => $chuanglan_config['api_password'],
			'msg' => urlencode($verify),
			'phone' => $mobile,
			'report' => 'true'
		);
		//$result = $this->curlPost( $chuanglan_config['api_send_url'] , $postArr);
		return $result;

    }
	/**
     * 通过CURL发送HTTP请求
     * @param string $url  //请求URL
     * @param array $postFields //请求参数 
     * @return mixed
     */
    private function curlPost($url,$postFields){
        $postFields = json_encode($postFields);
        $ch = curl_init ();
        curl_setopt( $ch, CURLOPT_URL, $url ); 
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8'
            )
        );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt( $ch, CURLOPT_TIMEOUT,1); 
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec ( $ch );
        if (false == $ret) {
            $result = curl_error(  $ch);
        } else {
            $rsp = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "请求状态 ". $rsp . " " . curl_error($ch);
            } else {
                $result = $ret;
            }
        }
        curl_close ( $ch );
		
        return $result;
    }
	/*
	我的佣金记录
	*/
	public function my_money(){
        $user_id=$this->getuserid();
        $money=M("user")->where("user_id=$user_id")->field('money')->find();
        $page_num=20;
        $Page = new \Think\Page($count,$page_num);
        $count=M("income")->where("user_id=$user_id")->count();
        $info=M("income")->limit($Page->firstRow . ',' . $Page->listRows)->where("user_id=$user_id")->order('income_time desc')->select();
        $con=C('income');
        foreach ($info as $key => $value) {
            if(!empty($value['income_handwin'])){
                $info[$key]['income_handwin']=$con[$value['income_handwin']];
            }
            if(!empty($value['income_time'])){
                $info[$key]['income_time']=date('Y-m-d H:i:s',$value['income_time']);
            }
        }
        if(IS_AJAX){
            $num=count($info);
            if(($num)>0){
                $html='';
                foreach ($info as $key => $value) {
                    $html.="<tr><td>".date('Y-m-d H:i:s',$info[$key]['income_time'])."</td><td>￥".$info[$key]['money']."</td><td>".$info[$key]['income_handwin']."</td></tr>";
                }
                if($num<$page_num){
                    $this->ajaxReturn(array('html'=>$html,'data'=>1));
                }
                $this->ajaxReturn(array('html'=>$html,'data'=>2));
            }else{
                $this->ajaxReturn(array('html'=>$html,'data'=>1));
                exit;
            }
        }
        $this->assign('money',$money);
        $this->assign('info',$info);
		$this->display();
	}
    /*
    提现记录
    */
    public function with_record(){
        $user_id=$this->getuserid();
        $page_num=11;
        $Page = new \Think\Page($count,$page_num);
        $count=M("popinfo")->where("user_id=$user_id")->count();
        $info=M("popinfo")->limit($Page->firstRow . ',' . $Page->listRows)->where("user_id=$user_id")->order("pop_addtime desc")->select();
        $con=C('popinfo');
        foreach ($info as $key => $value) {
            if(!empty($value['pop_flag'])){
               $info[$key]['pop_flag']=$con[$value['pop_flag']];
            }
            $info[$key]['pop_addtime']=date('Y-m-d H:i:s',$info[$key]['pop_addtime']);
        }
        if(IS_AJAX){
            $num=count($info);
            if(($num)>0){
                $html='';
                foreach ($info as $key => $value) {
                    $html.="<li><h5><span>".$info[$key]['pop_flag']."</span><i>-".$info[$key]['pop_money']."元</i></h5><p><span>".$info[$key]['pop_addtime']."</span><i>提现到".$info[$key]['pop_account']."</i></p></li>";
                }
                if($num<$page_num){
                    $this->ajaxReturn(array('html'=>$html,'data'=>1));
                }
                $this->ajaxReturn(array('html'=>$html,'data'=>2));
            }else{
                $this->ajaxReturn(array('data'=>1));
                exit;
            }
        }
        $this->assign('info',$info);
        $this->display();
    }
    /*
    申请提现
    */
    public function start_with(){
        $user_id=$this->getuserid();
        if(IS_AJAX){
            $data['user_id']=$user_id;
            $data['pop_money']=abs(I('post.pop_money'));
            $data['pop_name']=filterEmoji(I('post.pop_name'));
            $data['pop_code']=I('post.pop_code');
            $data['pop_account']=filterEmoji(I('post.pop_account'));
            $data['pop_phone']=I('post.pop_phone');
            $data['pop_idcard']=I('post.pop_idcard');
            $data['pop_truemoney']=I('post.pop_truemoney');
            $data['pop_addtime']=time();
            if(in_array('',$data)){
                $this->ajaxReturn(array("data"=>1,"msg"=>"参数错误"));
                exit;
            }
            //后台判断余额
            $balance=M("user")->where("user_id=$user_id")->field("money")->find()['money'];
            if($data['pop_money']>$balance){
                $this->ajaxReturn(array("data"=>4,"msg"=>"余额不足"));
                exit;
            }
            //扣除余额+记录入库
            $add=M("popinfo")->add($data);
            $dec=M("user")->where("user_id=$user_id")->setDec("money",$data['pop_money']);
            if($add && $dec){
                $this->ajaxReturn(array("data"=>2,"msg"=>"申请成功"));
            }else{
                $this->ajaxReturn(array("data"=>3,"msg"=>"申请失败"));
            }
            exit;
        }
        $user_change=M("setinfo")->where("set_key='user_change'")->field('set_value')->find();
        //查询银行卡信息
        $info=M("bindbank")->where("user_id=$user_id")->find();
        //查询用户余额
        $balance=M("user")->where("user_id=$user_id")->field('money')->find();
        $this->assign('info',$info);
        $this->assign('balance',$balance['money']);
        $this->assign('change',$user_change['set_value']);
        $this->display();

    }
    /*
    我的推荐
    */
    public function my_recommend(){
    	$user_id=$this->getuserid();
        //推荐人数综合
        $page_num=13;
        $count=M("attention")
                ->where("refree_id=$user_id")
                ->count();
        $Page = new \Think\Page($count,$page_num);
        $info=M("attention a")
                ->where("a.refree_id=$user_id")
                ->join("left join ec_user b on a.user_id=b.user_id left join ec_agcy c on a.agcy_id=c.agcy_id")
                ->order("a.attention_addtime desc")
                ->field("a.agcy_id,b.user_id,b.avatar,b.nickname,c.agcy_name")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        //查询当前店铺下级的下级
        foreach ($info as $key => $value) {
            if(!empty($value['user_id'])){
                $info[$key]['lower']=M("attention a")
                    ->where("a.refree_id=".$value['user_id']." and a.agcy_id=".$value['agcy_id'])
                    ->join("left join ec_user b on a.user_id=b.user_id")
                    ->field("b.avatar,b.nickname")
                    ->order("a.attention_addtime desc")
                    ->select();
            }
        }
        if(IS_AJAX){
            $num=count($info);
            if(($num)>0){
                $html='';
                foreach ($info as $key => $value) {
                    $html.="<dl><dt><span><i><img src='".$value['avatar']."'/></i><p>".$value['nickname']."</p></span><strong>".$value['agcy_name']."</strong>";
                    //循环第二层
                    if(!empty($value['lower'])){
                        $html.="<em></em>";
                    }
                    $html.="</dt>";
                    if(!empty($value['lower'])){
                        $html.="<dd>";
                        foreach ($value['lower'] as $k => $v) {
                            $html.="<div class=\"list\"><span><i><img src='".$v['avatar']."'/></i><p>".$v['nickname']."</p></span></div>";
                        }
                        $html.="</dd>";
                    }
                    $html.="</dl>";
                }
                if($num<$page_num){
                    $this->ajaxReturn(array('html'=>$html,'data'=>1));
                }
                $this->ajaxReturn(array('html'=>$html,'data'=>2));
            }else{
                //用于阻止前端滚动加载不断请求服务器
                $this->ajaxReturn(array('data'=>1));
                exit;
            }
        }
        $this->assign("num",$count);
        $this->assign("info",$info);
    	$this->display();
    }
    /*
	我的咨询
    */
    public function my_consult(){
        $user_id=$this->getuserid();
        $page_num=5;
        $count=M("expert_questions")->where("questions_user_id=$user_id")->count();
        $Page = new \Think\Page($count,$page_num);
        $info=M("expert_questions a")
                ->where("a.questions_user_id=$user_id")
                ->join("left join ec_user b on a.questions_user_id=b.user_id left join ec_expert_type c on a.questions_type_id=c.type_id")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->field("a.questions_id,a.questions_addtime,a.questions_content,a.questions_image,a.questions_answers,a.questions_like,b.avatar,b.nickname,c.type_name")
                ->order("a.questions_addtime desc")
                ->select();
        //处理时间和图片
        foreach ($info as $key => $value) {
            $info[$key]['questions_addtime']=from_time($value['questions_addtime']);
            $info[$key]['questions_content']=str_cut($value['questions_content'],300);
            if(!empty($value['questions_image'])){
                $img=explode('|',$value['questions_image']);
                if(count($img)>2){
                    $info[$key]['questions_image']=array_slice($img,0,2);
                }else{
                    $info[$key]['questions_image']=$img;
                }
            }
        }
        if(IS_AJAX){
            $num=count($info);
            if(($num)>0){
                $html='';
                $url=U('expert/zhuanjiawenda_xq');
                foreach ($info as $key => $value) {
                    $html.="<div class='wenda_ct'><div class=\"wenda_list\"><div class=\"nonghu\"><dl class=\"c\"><dt><a href='#'><img src='".$value['avatar']."'/></a></dt><dd><div class=\"top\"><a href='#'><h4>".$value['nickname']."<em>".$value['type_name']."</em></h4><span>".$value['questions_addtime']."</span></a></div><div class=\"ct\"><a href='".$url."&questions_id=".$value['questions_id']."'><p>".$value['questions_content']."</p></a></div>";
                    if(!empty($value['questions_image'])){
                        $html.=" <div class=\"img c\"><a href='".$url."&questions_id=".$value['questions_id']."'>";
                    }
                    for ($i=0; $i < count($value['questions_image']); $i++)
                    { 
                        $html.="<span><img src='".$value['questions_image'][$i]."'/></span>";
                    }
                    if(!empty($value['questions_image'])){
                        $html.="</a></div>";
                    }
                        $html.="<div class=\"bottom\"><span><i class=\"icon-zan\"></i><em>".$value['questions_like']."</em></span><span><i class=\"icon-xiaoxi1\"></i><em>".$value['questions_answers']."</em></span></div></dd></dl></div></div></div>";
                }
                if($num<$page_num){
                    $this->ajaxReturn(array('html'=>$html,'data'=>1));
                }
                $this->ajaxReturn(array('html'=>$html,'data'=>2));
            }else{
                $this->ajaxReturn(array('data'=>1));
                exit;
            }
        }
        $this->assign("info",$info);
    	$this->display();
    }
    
    /*
    问题详情
    */
    // public function consult_detail(){
    //     $id=I("id");
    //     if(empty($id)){
    //         echo '参数错误';
    //         $this->redirect("my_consult");
    //         exit;
    //     }
    //     $info=M("expert_questions a")
    //         ->where("a.questions_id=$id")
    //         ->join("left join ec_user b on a.questions_user_id=b.user_id left join ec_expert_type c on a.questions_type_id=c.type_id")
    //         ->field("a.questions_id,a.questions_addtime,a.questions_content,a.questions_image,a.questions_answers,a.questions_like,b.avatar,b.nickname,c.type_name")
    //         ->find();
    //     $info['questions_addtime']=from_time($info['questions_addtime']);
    //     if(!empty($value['questions_image'])){
    //         $img=explode('|',$value['questions_image']);
    //         if(count($img)>2){
    //             $info['questions_image']=array_slice($img,0,2);
    //         }else{
    //             $info['questions_image']=$img;
    //         }
    //     }
    //     //专家回复
    //     $expert=M("expert_answers a")
    //         ->where("a.answers_question_id=$id")
    //         ->join("left join ec_expert b on a.answers_expert_id=b.expert_id")
    //         ->field("a.*,b.expert_nickname,b.expert_avator")
    //         ->order("a.answers_like desc,a.answers_addtime desc")
    //         ->select();
    //     foreach ($expert as $key => $value) {
    //         $expert[$key]['answers_addtime']=from_time($value['answers_addtime']);
    //         if(!empty($value['answers_image'])){
    //             $img=explode('|',$value['answers_image']);
    //             if(count($img)>2){
    //                 $expert[$key]['answers_image']=array_slice($img,0,2);
    //             }else{
    //                 $expert[$key]['answers_image']=$img;
    //             }
    //         }
    //     }
    //     $this->assign('info',$info);
    //     $this->assign('expert',$expert);
    //     $this->display();
    // }
    /*
    专家回复点赞
    */
    // public function consult_thumb(){
    //     $id=I('post.expert_id');
    //     if(empty($id)){
    //         $this->ajaxReturn(array("data"=>1,"msg"=>"参数错误"),'json');
    //         exit;
    //     }
    //     $add=M("expert_answers")->where("answers_id=$id")->setInc('answers_like');
    //     if($add){
    //         $this->ajaxReturn(array("data"=>2),'json');
    //     }else{
    //         $this->ajaxReturn(array("data"=>3,"msg"=>"点赞失败"),'json');
    //     }
    // }

    /*
	绑定银行卡
    */
    public function my_bankcard(){
        $user_id=$this->getuserid();
        $info=M("bindbank")->where("user_id=$user_id")->find();
        if(IS_AJAX){
            $data['account_no']=I('post.account_no');
            $data['account_name']=filterEmoji(I('account_name'),2);
            $data['bank_name']=filterEmoji(I('bank_name'),2); 
            if(in_array('',$data)){
                $this->ajaxReturn(array('data'=>1,"msg"=>'参数错误'));
                exit;
            }
            if($info){
                $save=M("bindbank")->where("user_id=$user_id")->save($data);
                if($save!==false){
                    $this->ajaxReturn(array('data'=>2,"msg"=>'保存成功'));
                    exit;
                }else{
                    $this->ajaxReturn(array('data'=>3,"msg"=>'保存失败'));
                    exit;
                }
            }else{
                $data['user_id']=$user_id;
                $add=M("bindbank")->add($data);
                if($add){
                    $this->ajaxReturn(array('data'=>2,"msg"=>'保存成功'));
                    exit;
                }else{
                    $this->ajaxReturn(array('data'=>3,"msg"=>'保存失败'));
                    exit;
                }
            }
        }
        $this->assign('info',$info);
    	$this->display();
    }
    /*
	我的消息
    */
    public function my_message(){
        $user_id=$this->getuserid();
        $page_num=12;
        $Page = new \Think\Page($count,$page_num);
        $count=M("message")->where("user_id=$user_id")->count();
        $info=M("message")->limit($Page->firstRow . ',' . $Page->listRows)->where("user_id=$user_id")->order("msg_addtime desc")->select();
        foreach ($info as $key => $value) {
            $info[$key]['msg_addtime']=date('Y-m-d H:i:s',$value['msg_addtime']);
            $info[$key]['msg_title']=str_cut($value['msg_title'],35);
            $info[$key]['message']=str_cut($value['message'],80);
        }
        if(IS_AJAX){
            $num=count($info);
            if(($num)>0){
                $html='';
                $url=U('user/message_detail');
                foreach ($info as $key => $value) {
                    $html.="<li><a href='".$url."&id=".$value['msg_id']."'><h4><span";
                    if($value['msg_flag']==2){
                        $html.=" class='no_light'";
                    }
                    $html.=" >".$value['msg_title']."</span><i>".$value['msg_addtime']."</i></h4><p>".$value['message']."</p>";;
                    if($value['msg_flag']==1){
                        $html.="<strong></strong>";
                    }
                    $html.="</a></li>";
                }
                if($num<$page_num){
                    $this->ajaxReturn(array('html'=>$html,'data'=>1));
                }
                $this->ajaxReturn(array('html'=>$html,'data'=>2));
            }else{
                $this->ajaxReturn(array('data'=>1));
                exit;
            }
        }
        $this->assign('info',$info);
        $this->display();
    }
    /*
    消息详情
    */
    public function message_detail(){
        $id=I('get.id');
        if(empty($id)){
            echo "参数错误";
            $this->redirect("my_message");
            exit;
        }
        //状态修改为已读
        $upd=M("message")->save(array("msg_id"=>$id,"msg_flag"=>2));
        $info=M("message")->where("msg_id=$id")->field("msg_title,msg_addtime,message")->find();
        $info['msg_addtime']=date("Y-m-d H:i:s",$info['msg_addtime']);
        $this->assign('info',$info);
        $this->display();
    }

    /*
    意见反馈
    */
    public function feedback(){
        if(IS_AJAX){
            $data['user_id']=$this->getuserid();
            $data['title']=filterEmoji(I('post.title'),2);
            $data['addtime']=time();
            if(in_array('',$data)){
                $this->ajaxReturn(array('msg'=>'参数错误','data'=>1),'json');
                exit;
            }
            $add=M("feedback")->add($data);
            if($add){
                $this->ajaxReturn(array('msg'=>'提交成功','data'=>2),'json');
                exit;
            }
        }  
        $this->display();
    }
    /*
	地址管理
    */
    public function my_address(){ 
    	$user_id=$this->getuserid();
    	//收货地址查询
    	$address=M('address a')
    		->where("a.user_id=$user_id and a.status=0")
    		->join("left join ec_user b on a.user_id=b.user_id")
    		->field('a.id,a.username,a.id,a.province,a.detail,a.a_default,a.phone')
    		->order('a.a_default desc,a.addtime desc')
    		->select();
    	//去除收货地址中的“-”和空格
    	foreach ($address as $key => $value) {
            if(!empty($value['province'])){
        		$address[$key]['province']=preg_replace("/[-]||[' ']/i",'',$address[$key]['province']);
            }
    	}
    	//dd($address);
    	$this->assign('info',$address);
       
    	$this->display();
    }
    /*
	添加地址
    */
    public function add_address(){
        $flag = I("flag",0); 
        $bookorder = I("bookorder",0);
        $user_id=$this->getuserid();
    	if(IS_AJAX){
    		//添加地址信息
    		$data['user_id']=$user_id;
    		$data['username']=filterEmoji(trim(I('post.username')),2);
    		$data['phone']=I('post.phone');
    		$data['province']=filterEmoji(I('post.expressArea'),2);
    		$data['detail']=filterEmoji(trim(I('post.detail')),2);
    		$data['addtime']=time();
    		if(in_array('',$data)){
    			$this->ajaxReturn(array('msg'=>'某一项数据为空','data'=>'1'),'json');
    		}else{
    			//查询是否有可用的收货地址，如果是第一次添加地址设为默认地址
	    		$address_num=M('address')->where("user_id=".$data['user_id']." and status=0")->count();
	    		if($address_num>0){
	    			$data['a_default']=0;
	    		}else{
	    			$data['a_default']=1;
	    		}

    			$add_address=M('address')->add($data);
    			if($add_address){
    				$this->ajaxReturn(array('msg'=>'地址添加成功','data'=>'2'),'json');
    			}else{
    				$this->ajaxReturn(array('msg'=>'地址添加失败','data'=>'3'),'json');
    			}
    		}
    		exit;
    	}
        $this->assign('flag',$flag);
        $this->assign('bookorder',$bookorder);
    	$this->display();
    }
    /*
	删除地址
    */
    public function del_address(){
    	$id=I('post.id');
    	$data['status']=1;
    	$del=M('address')->where("id=$id")->save($data);
    	if($del){
    		$this->ajaxReturn(array('msg'=>'删除成功','data'=>'1'),'json');
    	}else{
    		$this->ajaxReturn(array('msg'=>'删除失败','data'=>'2'),'json');
    	}
    }
    /*
	编辑地址
    */
    public function edit_address(){
        $user_id=$this->getuserid();
    	if(IS_AJAX){
    		//地址信息
            $data['id']=I('post.id');
            $data['username']=filterEmoji(trim(I('post.username')),2);
            $data['phone']=I('post.phone');
            $data['province']=filterEmoji(I('post.expressArea'),2);
            $data['detail']=filterEmoji(trim(I('post.detail')),2);
            if(in_array('',$data)){
                $this->ajaxReturn(array('msg'=>'某一项数据为空','data'=>'1'),'json');
            }else{
                $edit_address=M('address')->save($data);
                if($edit_address!==false){
                    $this->ajaxReturn(array('msg'=>'地址修改成功','data'=>'2'),'json');
                }else{
                    $this->ajaxReturn(array('msg'=>'地址修改失败','data'=>'3'),'json');
                }
            }
            exit;
    	}
        $id=I('get.id');
        if(empty($id)){
            echo '参数错误';
            $this->redirect("my_address");
            exit;
        }
        $info=M("address")->where("id=$id")->find();
        $this->assign('info',$info);
    	$this->display();
    }
    /*
    默认地址设置
    */
    public function set_default(){
        $user_id=$this->getuserid();
        $id=I("post.id");
        $data['id']=$id;
        $data['a_default']=1;
        $det1=M("address")->save(array("id"=>$id,"a_default"=>1));
        $data['a_default']=0;
        $det2=M("address")->where("user_id=$user_id and id!=$id and status=0")->save(array("a_default"=>0));
        if($det1&&$det2){
            $this->ajaxReturn(array("data"=>1));
        }else{
            $this->ajaxReturn(array("data"=>2));
        }
    }
}
 ?>