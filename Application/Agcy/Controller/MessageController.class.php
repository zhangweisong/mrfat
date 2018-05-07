<?php
namespace Agcy\Controller;
use Think\Controller;
class MessageController extends Controller
{
		function __construct() {
		//继承父类
		parent::__construct();
		if(ACTION_NAME=="yingxiaoguanli"){
			
		}else{
			//判断登录状态
			$agcy_id=session("agcy_id");
			//未登录
			if($agcy_id==""){
				header("Location:".U('login/login'));
				exit();
			}
		}

    }
	//消息列表
	public function index(){
		$agcy_id=session("agcy_id");
		$count = M('message')
			->where('msg_type=2 and agcy_id='.$agcy_id)
			->count();
		$Page = new \Think\Page($count,15);
		$message = M('message')
			->where('msg_type=2 and agcy_id='.$agcy_id)
			->order('msg_id desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->select();
		foreach($message as $k=>$v){
		    $message[$k]['msg_addtime']=date("Y-m-d H:i:s",$v['msg_addtime']);	
		} 
		if(IS_AJAX){
            if (count($message) > 0) {
                $html = '';
                foreach ($message AS $k => $v) {
				    if($v['msg_flag']==1){
						$html .= "<li>
									<a href='" . U('message/xiangqing', array('msg_id' => $v['msg_id'])) . "' class='flexb'>
										<p>" . $v['msg_title'] . "<span style=\"display: inline-block;margin-bottom: 0.25rem;margin-right: 0.25rem;background: red;width: 0.5rem;height: 0.5rem;border-radius: 0.25rem;\"></span></p>
										<p> ".$v['msg_addtime'] . "</p>
										<img src='public/agcy/images/right.png' />
									</a>
								</li>";
					}else{
						$html .= "<li>
									<a href='" . U('message/xiangqing', array('msg_id' => $v['msg_id'])) . "' class='flexb'>
										<p>" . $v['msg_title'] . "</p>
										<p> ".$v['msg_addtime'] . "</p>
										<img src='public/agcy/images/right.png' />
									</a>
								</li>";
					}
                }
                $this->ajaxReturn($html);
            } else {
                $this->ajaxReturn(null);
            }
        }else{
			$this->assign('message',$message);
		    $this->display();
		}
		
	}
	//消息详情
	public function xiangqing(){
		$msg_id=I("get.msg_id");
		$message = M('message')->where('msg_id='.$msg_id)->find();
		$message['msg_addtime']=date("Y-m-d H:i:s",$message['msg_addtime']);	
		$data['msg_flag']=2;
		$rel = M('message')->where('msg_id='.$msg_id)->save($data);
	    $this->assign('message',$message);
		$this->display();
	}
	
}