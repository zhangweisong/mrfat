<?php
namespace Admin\Controller;
use Think\Controller;
class AgcyapplyController extends Controller {

    function __construct() {
        //继承父类 
        parent::__construct();
        //判断登录状态
        if (!D('admininfo')->islogin()) {//未登录
           jump('您尚未登录，请先登录！', U('login/login'));

        }
    }

    //空方法，防止报错
    public function _empty() {
        $this->index();
    }
	
	
	 //公共success
    public function home_success($msg, $url = "", $wait = 3) {
        $this->assign('msg', $msg);
        $this->assign('url', $url ? $url : U("index/index"));
        $this->assign('wait', $wait);
        $this->display('agcyapply/home_success');
        exit();
    }

    //公共error
    public function home_error($msg, $url = "", $wait = 3) {
        $this->assign('msg', $msg);
        $this->assign('url', $url ? $url : U("index/index"));
        $this->assign('wait', $wait);
        $this->display('agcyapply/home_error');
        exit();
    }
	
	
    //经销商代理申请表
    public function index() {  
        $where = "1 ";
        $info = I("info");
        if ($info) {
            @extract($info);
            //按负责人搜索
            if ($key) {
                $key = urldecode(trim($key));
                $where .= " and (ec_agcy.agcy_name like '%".$key."%'
					or ec_fgoods.fgoods_name like '%".$key."%'
					or ec_factory.factory_name like '%".$key."%'
					or ec_factory.factory_man like '%".$key."%'
					or ec_factory.factory_tel like '%".$key."%'
				) ";
                $this->assign('keys', $key);
            }  
            //按状态搜索
            if ($state) {
                $state = urldecode(trim($state));
                $where .= " and ec_agcyapply.agcy_apply_flag like '%".$state."%' ";
                $this->assign('states', $state);
            } 
            //按添加时间搜索
            if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_agcyapply.agcy_apply_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_agcyapply.agcy_apply_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
        }
        $count = M("agcyapply")
			->where($where)
			->join('left join ec_factory on ec_agcyapply.factory_id=ec_factory.factory_id')
			->join('left join ec_agcy on ec_agcyapply.agcy_id=ec_agcy.agcy_id')
			->join('left join ec_fgoods on ec_agcyapply.fgoods_id=ec_fgoods.fgoods_id')
			->field('ec_agcyapply.*,ec_factory.factory_name,ec_factory.factory_man,ec_factory.factory_tel,ec_agcy.agcy_name,ec_fgoods.fgoods_name,ec_fgoods.fgoods_images')
            ->count();
        
        $Page = new \Think\Page($count, 15);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info[' . $key . ']'] = urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        $agcyapply = M('agcyapply')
				->where($where)
				->join('left join ec_factory on ec_agcyapply.factory_id=ec_factory.factory_id')
				->join('left join ec_agcy on ec_agcyapply.agcy_id=ec_agcy.agcy_id')
				->join('left join ec_fgoods on ec_agcyapply.fgoods_id=ec_fgoods.fgoods_id')
				->field('ec_agcyapply.*,ec_factory.factory_name,ec_factory.factory_man,ec_factory.factory_tel,ec_agcy.agcy_name,ec_fgoods.fgoods_name,ec_fgoods.fgoods_images')
                ->order("ec_agcyapply.agcy_apply_id desc")
				->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();  
		//状态
		$state = D('agcypopinfo')->state(); 
        $this->assign('pageshow', $pageshow);
        $this->assign('agcyapply', $agcyapply);  
        $this->assign('state', $state); 
        $this->display();
    }
    

    
    
    //详情信息
    public function info() {
        $agcy_apply_id = I("get.agcy_apply_id"); 
        $agcyapply = M('agcyapply')
				->where("ec_agcyapply.agcy_apply_id='".$agcy_apply_id."'")
				->join('left join ec_factory on ec_agcyapply.factory_id=ec_factory.factory_id')
				->join('left join ec_agcy on ec_agcyapply.agcy_id=ec_agcy.agcy_id')
				->join('left join ec_fgoods on ec_agcyapply.fgoods_id=ec_fgoods.fgoods_id')
				->field('ec_agcyapply.*,ec_factory.factory_name,ec_factory.factory_man,ec_factory.factory_tel,ec_factory.factory_address,ec_agcy.agcy_name,ec_agcy.agcy_address,ec_agcy.agcy_manager,ec_agcy.agcy_tel,ec_fgoods.*') 
                ->find(); 
		$state = D('agcypopinfo')->state(); 		
        $this->assign('agcyapply', $agcyapply);
		$this->assign('state', $state); 
        $this->display();
    }
	
	//通过
    public function adopt() {
		$agcy_apply_id = I("agcy_apply_id"); 
		$this->assign('agcy_apply_id', $agcy_apply_id); 
		$this->display(); 
	}
	
	//驳回
    public function refuse() {
		$agcy_apply_id = I("agcy_apply_id"); 
		$this->assign('agcy_apply_id', $agcy_apply_id); 
		$this->display(); 
	}
	
	//通过
    public function adopted(){ 
        $agcy_apply_id=I('post.agcy_apply_id');
		$res=M('agcyapply')->where('agcy_apply_id = '.$agcy_apply_id)->find();
		//判断同一个乡镇的代理商不能代理同一个厂家的同一个产品 
		$noyes = $this->panduan($res['agcy_id'],$agcy_apply_id); 
		if($noyes==2){
			$this->home_error('通过申请失败！', U('agcyapply/index'));
		}else{
			//给经销商产品表加数据
			$fgoods_id=$res['fgoods_id']; 
			$goods = M('fgoods')->where("fgoods_id='".$fgoods_id."'")->find();
			//厂家名称
			$factory = M('factory')->where("factory_id='".$res['factory_id']."'")->field("factory_name")->find(); 
			$row['agcy_goods_name']=$goods['fgoods_name'];
			$row['agcy_goods_images']=$goods['fgoods_images']; 
			$row['agcy_goods_price']=$goods['fgoods_zhidaojia'];
			$row['agcy_goods_state']=2;
			$row['agcy_goods_states']=2;
			$row['agcy_fgoods_id']=$res['fgoods_id'];
			$row['agcy_factory_id']=$res['factory_id'];
			$row['agcy_id']=$res['agcy_id']; 
			$row['agcy_goods_addtime']=time();
			$row['agcy_goods_spec']=$goods['fgoods_spec'];
			$row['agcy_goods_yes']=2;
			$row['agcy_goods_salenum']=0;
            //$row['agcy_goods_repertory']=0;
			$row['agcy_goods_remark']=$goods['fgoods_detail']; 
			$flag2=M('agcygoods')->add($row); 
            
            //给agcygoods加
            $fgoods_zhidaojia = $goods['fgoods_zhidaojia'];
            $price = explode(',',$fgoods_zhidaojia);
            M("agcygoods")->where("agcy_goods_id='".$flag2."'")->save(array('agcy_default_price'=>$price[0]));
			//改变状态
			$remark=I('post.agcy_apply_check_remark');
			$data['agcy_apply_flag']=2;
			$data['agcy_apply_check_remark']=$remark;
			$data['agcy_apply_check_time']=time(); 
			$flag=M('agcyapply')->where('agcy_apply_id = '.$agcy_apply_id)->save($data);
			
			if($flag2 && $flag){
				//增加消息通知
				$message= new \Admin\Model\MessageModel();
				$message->getmessage_($res['agcy_id'],2,"经销商代理申请通知","您申请代理厂家:".$factory['factory_name']."的产品:".$goods['fgoods_name']."已通过审核"); 
				$this->home_success('通过申请成功！', U('agcyapply/index'));
			}else{
				$this->home_error('通过申请失败！', U('agcyapply/index'));
			}
		}
		 
    }
	
	public function refused(){ 
        $agcy_apply_id=I('post.agcy_apply_id');
        $res=M('agcyapply')->where('agcy_apply_id = '.$agcy_apply_id)->find();
        $remark=I('post.agcy_apply_check_remark');
        $data['agcy_apply_flag']=3;
        $data['agcy_apply_check_remark']=$remark;
        $data['agcy_apply_check_time']=time(); 
		$flag=M('agcyapply')->where('agcy_apply_id = '.$agcy_apply_id)->save($data);  
        if($flag){
			//增加消息通知
			$message= new \Admin\Model\MessageModel();
			$message->getmessage_($res['agcy_id'],2,"经销商代理申请通知","您的代理申请已驳回"); 
            $this->home_success('已驳回申请！', U('agcyapply/index'));
        }else{
            $this->home_error('驳回申请失败！', U('agcyapply/index'));
        }
    } 
	
	
	public function panduan($agcy_id,$agcy_apply_id){
		//返回1是可以，返回2是不可以
		//获取经销商的id
		$agcy = M('agcy')->where('agcy_id=' .$agcy_id)->find();
		$agcy_apply_a = M('agcyapply')->where('agcy_apply_id=' .$agcy_apply_id)->find();
        
		//从经销商表查找和该经销商在同一个乡镇的经销商
		$agcy_a = M('agcy')->where("agcy_id!='".$agcy['agcy_id']."' and villages='".$agcy['villages']."' ")->select();
        
		if($agcy_a){ 
			foreach($agcy_a as $k=>$v){ 
				$agcy_apply= M('agcyapply')->where("agcy_id='".$v['agcy_id']."' and factory_id='".$agcy_apply_a['factory_id']."' and fgoods_id='".$agcy_apply_a['fgoods_id']."'and agcy_apply_flag=2 ")->find();
                if($agcy_apply){
                   $row[] =  $agcy_apply;
                }  
			} 
            if($row){
               return 2; 
            }else{
               return 1; 
            }     
		}else{
			return 1;
		}
		
	}
	
		
     

}
