<?php 
namespace User\Controller;
use Think\Controller;
class PolicyController extends Controller
{
	//政策解读
	public function policy(){
		//政策解读分类
		$fenlei = M('slpolicyintprtclass')->where('pic_isable=1')->order('pic_order asc')->select();
        if($fenlei){
            $pic_id = I('get.pic_id',$fenlei[0]['pic_id']);
        }else{
            $pic_id = I('get.pic_id',0);
        } 
		$where = 'pi_isable = 1 and pi_cid='.$pic_id;
		$keyword = trim(I('get.keyword'));
		if($keyword){
			$where .= " and  pi_title like'%".$keyword."%'";
			$this->assign('keyword',$keyword);
		}
		$count  = M('slpolicyinterprate')->where($where)->count();
		$Page = new \Think\Page($count,6);
		$piinfo = M('slpolicyinterprate')
		         ->where($where)
				 ->order('pi_id desc')
				 ->limit($Page->firstRow.','.$Page->listRows)
				 ->select();
	    $arr=array();
		foreach($piinfo as $k=>$v){
			 $arr = explode("|",$v['pi_image']);
			 $piinfo[$k]['pi_image'] = $arr[0];
			 $piinfo[$k]['pi_title'] = str_cut(strip_tags(htmlspecialchars_decode($v['pi_title'])),60);
			 $piinfo[$k]['pi_content'] = str_cut(strip_tags(htmlspecialchars_decode($v['pi_content'])),150);
		}
		if(IS_AJAX){
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('piinfo' => $piinfo));
            } else {
                $this->ajaxReturn(array('piinfo' => array()));
            }
        }else {
			$this->assign('fenlei',$fenlei);
			$this->assign('pic_id',$pic_id);
			$this->assign('piinfo',$piinfo);
        } 
		
		$this->display('zhengcejiedu');
	}
	//政策解读详情
	public function policy_xq(){
		$pi_id = I('get.pi_id');
		$piinfo = M('slpolicyinterprate')->where('pi_id='.$pi_id)->find();
		$arr = explode("|",$piinfo['pi_image']);
	    $piinfo['pi_image'] = $arr[1]; 
		$piinfo['pi_addtime'] = date('Y-m-d H:i:s',$piinfo['pi_addtime']);
		$this->assign('piinfo',$piinfo);
		$this->display('tuwen_xq');
	}
	//我的关注
	public function follow(){
		//session('user_id',1);
		$user_id = session('user_id');
		$count =M('attention')
		     ->where('ec_agcy.agcy_state=1 and user_id='.$user_id )
			 ->join('left join ec_agcy on ec_agcy.agcy_id = ec_attention.agcy_id')
			 ->count();
		$Page = new \Think\Page($count,7);
		$followlist = M('attention')
		     ->where('ec_agcy.agcy_state=1 and user_id='.$user_id )
			 ->join('left join ec_agcy on ec_agcy.agcy_id = ec_attention.agcy_id')
			 ->order('attention_id desc')
			 ->limit($Page->firstRow.','.$Page->listRows)
			 ->select();
	    $arr=array();
		foreach($followlist as $k=>$v){
			 $arr = explode("|",$v['agcy_face_images']);
			 $followlist[$k]['agcy_face_images'] = $arr[0];
		}
		if(IS_AJAX){
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('followlist' => $followlist));
            } else {
                $this->ajaxReturn(array('followlist' => array()));
            }
        }else {
			  $this->assign('followlist',$followlist); 
		      $this->assign('user_id',$user_id); 
        } 
		//dd($followlist);
		$this->display('woguanzhudedianpu');
	}
    
    
	//我的推广
	public function extension(){
		$user_id = I('get.user_id');
		$agcy_id = I('get.agcy_id');
		vendor('WxpayAPI.WxPayJsApiPay'); 
		$jsApi = new \JsApiPay();
		$signPackage = $jsApi->GetSignPackage();
		$this->assign('signPackage',$signPackage); //获取用户appid   ------------------------------页面后期需要改
		$this->assign('user_id',$user_id); 
		$this->assign('agcy_id',$agcy_id); 
		$this->display('woyaotuiguang');
	}
    
    //关注公众号
    public function attention() {
		$this->display();
    }
    
    
	//农户数字报
	public function newspaper(){
		$count =M('sldigitalpaper')
		     ->where('slp_available=1')
			 ->count();
		$Page = new \Think\Page($count,6);
		$followlist = M('sldigitalpaper')
		     ->where('slp_available=1')
			 ->order('slp_id desc')
			 ->limit($Page->firstRow.','.$Page->listRows)
			 ->select();
		foreach($piinfo as $k=>$v){
			 $piinfo[$k]['slp_title'] = str_cut(strip_tags(htmlspecialchars_decode($v['slp_title'])),60);
		}
		if(IS_AJAX){
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('followlist' => $followlist));
            } else {
                $this->ajaxReturn(array('followlist' => array()));
            }
        }else {
			  $this->assign('followlist',$followlist); 
        } 
		$this->display('nongyeshuzibao');
	}
	
	
}