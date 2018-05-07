<?php
namespace Agcy\Controller;
use Think\Controller;
class OrdermanagementController extends Controller {

    function __construct() {
		//继承父类
		parent::__construct();
	    //判断登录
		$agcy_id=session("agcy_id");
		if($agcy_id==""){//未登录
			header("Location:".U('login/login'));
			exit();
		}

    }
    //空方法，防止报错
    public function _empty(){
        $this->index();
    }

    //订货管理
    public function index(){
        $this->display();
    }
    //选择商品分类
    public function goodstypes(){
        //一级分类
        $type=M('fgoodsclassify')->where('f_classify_fatherid =0 ')->select();
        foreach($type as $k=>$v){
           $type[$k]['child']= M('fgoodsclassify')
                            ->where('f_classify_fatherid='.$v['f_classify_id'].' ')
                            //->order('f_classify_id desc')
                            ->select();
        }
        $this->assign('type',$type);
        $this->assign('typelen',count($type));
        $this->display();
    }
    //订货商城
    public function ordershop(){
        $agcy_id=session("agcy_id");
        $f_classify_id=intval(I('get.f_classify_id'));//二级
        $f_classify_fatherid=intval(I('get.f_classify_fatherid'));//一级
        $where='1 ';
        $where.=' and fgoods_1='.$f_classify_fatherid.' and fgoods_2='.$f_classify_id.' and fgoods_state=1 and fgoods_state2=1 ';
        //去掉同一乡镇代理的产品
        $goods_id=$this->fgoods($agcy_id,1);
        if($goods_id){
           $where.='and fgoods_id not in('.join(',',$goods_id).')'; 
        }
        $count =M('fgoods')
				->join('left join  ec_factory on ec_factory.factory_id=ec_fgoods.factory_id')
                ->where($where)//fgoods_isdaili  
                ->order('fgoods_id desc')
				->count();
		$Page = new \Think\Page($count, 5);
        
        $goodslist=M('fgoods')
                ->join('left join  ec_factory on ec_factory.factory_id=ec_fgoods.factory_id')
                ->where($where)
                ->field('ec_fgoods.*,ec_factory.factory_name as factoryname')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order('fgoods_id desc')
                ->select();
        
        //查询经销商的代理产品(已代理)
        $pass=M("agcyapply")->where(' agcy_apply_flag=2  and agcy_id='.$agcy_id)->field('fgoods_id')->select();
        //经销商的代理产品（申请中）
        $apply=M("agcyapply")->where(' agcy_apply_flag=1  and agcy_id='.$agcy_id)->field('fgoods_id')->select();
        //二维转一维数组
        $pass_=array();
        foreach($pass as $k=>$v){
            $pass_[$k]=$v['fgoods_id'];
        }
        $apply_=array();
        foreach($apply as $k=>$v){
            $apply_[$k]=$v['fgoods_id'];
        }
        //代理产品
        foreach($goodslist as $k=>$v){
            $image=explode('|',$v['fgoods_images']);
            $goodslist[$k]['fgoods_images']=$image[0];
            if(in_array($v['fgoods_id'],$pass_)){//该产品是否已代理
                $goodslist[$k]['flag']=2;//已代理
            }elseif(in_array($v['fgoods_id'],$apply_)){
                $goodslist[$k]['flag']=1;//申请中
            }else{
                $goodslist[$k]['flag']=0;//未代理
            }
        }
        
        $flags=array();
        $ids=array();
        foreach($goodslist as $key=>$val){  
            $flags[$key] = $val['flag'];  
            $ids[$key] = $val['fgoods_id'];  
        }  
        array_multisort($flags,SORT_DESC,$ids,SORT_DESC,$goodslist);  
        unset($flags);unset($ids);
        $this->assign('goodslist',$goodslist);        
        $this->assign('goodslen',count($goodslist));        
        $this->assign('f_classify_id',$f_classify_id);        
        $this->assign('f_classify_fatherid',$f_classify_fatherid);        
        $this->display(); 
        
        
    }
    
    //订货排序 综合排名
    public function paixu(){
        $agcy_id=session("agcy_id");
        $datas=intval(I('get.datas'))?intval(I('get.datas')):1;
        if($datas==2){//销量
            $orders="fgoods_sale desc";
        }else if($datas==3){//价格
            $orders="fgoods_tonprice desc";
        }else{//综合排名
            $orders="fgoods_id desc";
        }
        $f_classify_id=intval(I('get.f_classify_id'));//二级
        $f_classify_fatherid=intval(I('get.f_classify_fatherid'));//一级
        
        $where='1 ';
        $where.=' and fgoods_1='.$f_classify_fatherid.' and fgoods_2='.$f_classify_id.' and fgoods_state=1 and fgoods_state2=1 ';
        //去掉同一乡镇代理的产品
        $goods_id=$this->fgoods($agcy_id,1);
        if($goods_id){
           $where.='and fgoods_id not in('.join(',',$goods_id).')'; 
        }
        
        $count =M('fgoods')
				->join('left join  ec_factory on ec_factory.factory_id=ec_fgoods.factory_id')
                ->where($where)
                ->order($orders)
				->count();
		$Page = new \Think\Page($count, 5);
        $goodslist=M('fgoods')
                ->join('left join  ec_factory on ec_factory.factory_id=ec_fgoods.factory_id')
                ->where($where)
                ->field('ec_fgoods.*,ec_factory.factory_name as factoryname')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order($orders)
                ->select();
          
        //查询经销商的代理产品(已代理)
        $pass=M("agcyapply")->where(' agcy_apply_flag=2  and agcy_id='.$agcy_id)->field('fgoods_id')->select();
        //经销商的代理产品（申请中）
        $apply=M("agcyapply")->where(' agcy_apply_flag=1  and agcy_id='.$agcy_id)->field('fgoods_id')->select();
        //二维转一维数组
        $pass_=array();
        foreach($pass as $k=>$v){
            $pass_[$k]=$v['fgoods_id'];
        }
        $apply_=array();
        foreach($apply as $k=>$v){
            $apply_[$k]=$v['fgoods_id'];
        }
        //代理产品
        foreach($goodslist as $k=>$v){
            $image=explode('|',$v['fgoods_images']);
            $goodslist[$k]['fgoods_images']=$image[0];
            if(in_array($v['fgoods_id'],$pass_)){//该产品是否已代理
                $goodslist[$k]['flag']=2;//已代理
            }elseif(in_array($v['fgoods_id'],$apply_)){
                $goodslist[$k]['flag']=1;//申请中
            }else{
                $goodslist[$k]['flag']=0;//未代理
            }
        }
        //dd($goodslist);         
        if(IS_AJAX){
            if($goodslist){
               if($Page->totalPages >= $p){
				$this->ajaxReturn(array('status'=>1,'data'=>$goodslist,'msg'=>"",'data_'=>count($goodslist)));
                }else{
                    $this->ajaxReturn(array('status' => 0, 'data' => array(), 'msg' => "没有数据了"));
                } 
            }else{
                $this->ajaxReturn(array('status' => 0, 'data' => array(), 'msg' => "没有数据了"));
            }
            
        }

    }
    //搜索
    public function sousuo(){
        $agcy_id=session("agcy_id");
        $keywords=I('get.keywords');
        $f_classify_id=intval(I('get.f_classify_id'));//二级
        $f_classify_fatherid=intval(I('get.f_classify_fatherid'));//一级
        $where='1 ';
        $where.=' and fgoods_1='.$f_classify_fatherid.' and fgoods_2='.$f_classify_id.'  and fgoods_state=1 and fgoods_state2=1 and ( fgoods_name like \'%'.$keywords.'%\' or ec_factory.factory_name like \'%'.$keywords.'%\') ';
        //去掉同一乡镇代理的产品
        $goods_id=$this->fgoods($agcy_id,1);
        if($goods_id){
           $where.='and fgoods_id not in('.join(',',$goods_id).')'; 
        }
        $goodslist=M('fgoods')
                ->join('left join  ec_factory on ec_factory.factory_id=ec_fgoods.factory_id')
                ->where($where)
                ->field('ec_fgoods.*,ec_factory.factory_name as factoryname')
                ->order('fgoods_id desc')
                ->select();
            
        //查询经销商的代理产品(已代理)
        $pass=M("agcyapply")->where(' agcy_apply_flag=2  and agcy_id='.$agcy_id)->field('fgoods_id')->select();
        //经销商的代理产品（申请中）
        $apply=M("agcyapply")->where(' agcy_apply_flag=1  and agcy_id='.$agcy_id)->field('fgoods_id')->select();
        //二维转一维数组
        $pass_=array();
        foreach($pass as $k=>$v){
            $pass_[$k]=$v['fgoods_id'];
        }
        $apply_=array();
        foreach($apply as $k=>$v){
            $apply_[$k]=$v['fgoods_id'];
        }
        //代理产品
        foreach($goodslist as $k=>$v){
            $image=explode('|',$v['fgoods_images']);
            $goodslist[$k]['fgoods_images']=$image[0];
            if(in_array($v['fgoods_id'],$pass_)){//该产品是否已代理
                $goodslist[$k]['flag']=2;//已代理
            }elseif(in_array($v['fgoods_id'],$apply_)){
                $goodslist[$k]['flag']=1;//申请中
            }else{
                $goodslist[$k]['flag']=0;//未代理
            }
        }
        if(IS_AJAX){
            if($goodslist){
               if($Page->totalPages >= $p){
				$this->ajaxReturn(array('status'=>1,'data'=>$goodslist,'msg'=>"",'data_'=>count($goodslist)));
                }else{
                    $this->ajaxReturn(array('status' => 0, 'data' => array(), 'msg' => "没有数据了"));
                } 
            }else{
                $this->ajaxReturn(array('status' => 0, 'data' => array(), 'msg' => "没有数据了"));
            }
            
        }
    }
    
    //代理商协议
    public function agreement(){
        $agcy_id=session("agcy_id");
        $f_classify_id=intval(I('get.f_classify_id'));//二级
        $f_classify_fatherid=intval(I('get.f_classify_fatherid'));//一级
        $fgoods_id=I('get.fgoods_id');
        $factory_id=I('get.factory_id');
        //同一乡镇已代理的产品id
        $goods_id=$this->fgoods($agcy_id,1);
        if(in_array($fgoods_id,$goods_id)){
            $flag=1; //商品已被代理
        }else{
            $flag=2; 
        }
        if($flag==1){
            jump_success("该商品已被代理，请重新选择！",U('ordermanagement/ordershop',array('f_classify_id'=>$f_classify_id,'f_classify_fatherid'=>$f_classify_fatherid)));    
        }else{
            $shenqing=M('composition')->where('composition_id=6')->find();
            $this->assign('shenqing',$shenqing['composition_text']);
            $this->assign('fgoods_id',$fgoods_id);
            $this->assign('factory_id',$factory_id);
            $this->assign('f_classify_id',$f_classify_id);
            $this->assign('f_classify_fatherid',$f_classify_fatherid);
            $this->display(); 
        }
         
    }
    //申请代理
    public function agcyapply(){
        $agcy_id=session("agcy_id");
        $fgoods_id=I('get.fgoods_id');
        $factory_id=I('get.factory_id');
        $f_classify_id=intval(I('get.f_classify_id'));//二级
        $f_classify_fatherid=intval(I('get.f_classify_fatherid'));//一级
        //产品
        $fgoods=M('fgoods')
                ->where('fgoods_state=1 and fgoods_state2=1 and fgoods_id='.$fgoods_id)
                ->field('fgoods_id,fgoods_name,fgoods_images,fgoods_tonprice')
                ->find();
        $image=explode('|',$fgoods['fgoods_images']);
        $fgoods['fgoods_images']= $image[0];
        if($fgoods){
           // dump($fgoods);
            $agcy=M('agcy')
                ->where('agcy_id='.$agcy_id)
                ->field('agcy_name,agcy_address,agcy_manager,agcy_tel')
                ->find();
            $this->assign('fgoods_id',$fgoods_id);
            $this->assign('factory_id',$factory_id);
            $this->assign('f_classify_id',$f_classify_id);
            $this->assign('f_classify_fatherid',$f_classify_fatherid);
            $this->assign('fgoods',$fgoods);
            $this->assign('agcy',$agcy);
            $this->display();
            
        }else{
           jump("该商品不存在或者未上架",U('ordermanagement/ordershop',array('f_classify_id'=>$f_classify_id,'f_classify_fatherid'=>$f_classify_fatherid)));
        }
        
    }
    
    //提交成功
    public function applysucc(){
        $agcy_id=session("agcy_id");
        $fgoods_id=I('get.fgoods_id');
        $factory_id=I('get.factory_id');
        $remarks=I('get.remarks');
        $f_classify_id=intval(I('get.f_classify_id'));//二级
        $f_classify_fatherid=intval(I('get.f_classify_fatherid'));//一级
        //同一乡镇已代理的产品id
        $goods_id=$this->fgoods($agcy_id,1);
        if(in_array($fgoods_id,$goods_id)){
            $flag=1; //若已被代理，flag=1；
        }else{
            $flag=2; 
        }
        
        if($flag==1){
            jump_success("该商品已被代理",U('ordermanagement/ordershop',array('f_classify_id'=>$f_classify_id,'f_classify_fatherid'=>$f_classify_fatherid)));
        }else{
            //添加申请
            $info['agcy_id']=$agcy_id;
            $info['factory_id']=$factory_id;
            $info['fgoods_id']=$fgoods_id;
            $info['agcy_apply_addtime']=time();
            $info['agcy_apply_flag']=1;
            $info['agcy_apply_remarks']=filterEmoji($remarks,2);
            $flag1=M('agcyapply')->add($info);
            if($flag1){
                $this->assign('f_classify_id',$f_classify_id);
                $this->assign('f_classify_fatherid',$f_classify_fatherid);
                $this->display();   
            }else{
                jump_success("代理申请失败！请重新申请",U('ordermanagement/agcyapply',array('f_classify_id'=>$f_classify_id,'f_classify_fatherid'=>$f_classify_fatherid,'fgoods_id'=>$fgoods_id,'factory_id'=>$factory_id)));
            }
        }
         
    }
    //订货详情
    public function xiangqing(){
        $agcy_id=session("agcy_id");
        $fgoods_id=I('get.fgoods_id'); 
        $f_classify_id=intval(I('get.f_classify_id'));//二级
        $f_classify_fatherid=intval(I('get.f_classify_fatherid'));//一级 
        //商品
        $fgoods=M('fgoods')->where('fgoods_id='.$fgoods_id.' and fgoods_state=1 and fgoods_state2=1')->find();
        if($fgoods){
            //该商品是否已被此经销商代理
            $apply=M('agcyapply')->where('fgoods_id='.$fgoods_id.' and agcy_id='.$agcy_id.' and agcy_apply_flag=2')->find();
            if($apply){
                $fgoods['flag']=1;//已代理
            }else{
                $fgoods['flag']=2;//未代理
            }
            
            $fgoods['fgoods_images']=explode('|',$fgoods['fgoods_images']);
            $fgoods['fgoods_spec']=explode(',',$fgoods['fgoods_spec']);
            //$fgoods['fgoods_specprice']=explode(',',$fgoods['fgoods_specprice']);
            $this->assign('fgoods',$fgoods);
            $this->assign('fgoods_zhidaojia',$fgoods['fgoods_zhidaojia']);
            $this->assign('f_classify_id',$f_classify_id);
            $this->assign('f_classify_fatherid',$f_classify_fatherid);
            $this->display();
        }else{
            jump_success("该商品不存在或者已下架",U('ordermanagement/ordershop',array('f_classify_id'=>$f_classify_id,'f_classify_fatherid'=>$f_classify_fatherid)));
        }
          
        
    }
    //立即订货  增加订单
    public function dinghuo(){
        $agcy_id=session("agcy_id");
        //$f_classify_id=intval(I('get.f_classify_id'));//二级
        //$f_classify_fatherid=intval(I('get.f_classify_fatherid'));//一级 
        $fgoods_id=intval(I('get.fgoods_id'));
        $num=intval(I('get.num'));
        $spec=I('get.spec');
        //$price=I('get.price');
        $fgoods=M('fgoods')->where('fgoods_id='.$fgoods_id.' and fgoods_state=1 and fgoods_state2=1 ')->find();
        if($fgoods){
            //订单表
            $price=floatval($fgoods['fgoods_tonprice']*$num);
            $info['factory_id']=$fgoods['factory_id'];
            $info['agcy_id']=$agcy_id;
            $info['forder_price']=$price;
            $info['forder_total']=$price;
            $info['forder_number']=time().rand(10000,99999);
            $info['forder_addtime']=time();
            $info['forder_flag']=1;
            $info['forder_flagstr']='待付款';
            $flag=M('forder')->add($info);
            //订单明细
            $info_['forder_id']=$flag;
            $info_['fgoods_id']=$fgoods_id;
            $info_['forderlist_spec']=$spec;
            $info_['forderlist_tonnum']=$num;
            $info_['forderlist_totprice']=$price;
            $result=M('forderlist')->add($info_);
            if($flag&&$result){
                $this->ajaxReturn(array('status'=>1,'msg'=>"订货申请成功，请支付！",'forder_id'=>$flag,'fgoods_id'=>$fgoods_id));
            }else{
                $this->ajaxReturn(array('status'=>-1,'data'=>"",'msg'=>"订货申请失败！"));
            }
        }else{
            $this->ajaxReturn(array('status'=>0,'data'=>"",'msg'=>"该商品已被代理，请重新选择！"));
        }
    }
        //结算
        public function jiesuan(){
            $agcy_id=session("agcy_id");
            $fgoods_id=I('get.fgoods_id');  
            $forder_id=I('get.forder_id'); 
            $f_classify_id=intval(I('get.f_classify_id'));//二级
            $f_classify_fatherid=intval(I('get.f_classify_fatherid'));//一级 
            $state=I('get.state')?I('get.state'):0;
            //$agcy=M('agcy')->where('agcy_id='.$agcy_id)->field('agcy_name,agcy_address,agcy_manager,agcy_tel')->find();
            //订货信息
            $forder=M('forder')
                    ->join('left join ec_agcy on ec_agcy.agcy_id=ec_forder.agcy_id')//商家信息
                    ->join('left join ec_factory on ec_factory.factory_id=ec_forder.factory_id')//厂家
                    ->field('ec_forder.*,ec_agcy.agcy_name as agcyname,ec_agcy.agcy_address as agcyaddress,ec_agcy.agcy_manager as agcymanager,ec_agcy.agcy_tel as agcytel,
                    ec_factory.factory_name as fname,ec_factory.factory_address as faddress,ec_factory.factory_tel as ftel,ec_factory.factory_man as fman')
                    ->where('ec_forder.forder_id='.$forder_id)
                    ->find();
            //订货详情
            $forderlist=M('forderlist')
                        ->join('left join ec_forder on ec_forder.forder_id=ec_forderlist.forder_id')
                        ->join('left join ec_fgoods on ec_fgoods.fgoods_id=ec_forderlist.fgoods_id')
                        ->where('ec_forderlist.forder_id='.$forder_id)
                        ->field('ec_forderlist.*,ec_fgoods.fgoods_name,ec_fgoods.fgoods_images,
                        ec_fgoods.fgoods_tonprice')
                        ->select();
            foreach($forderlist as $k=>$v){
               $image=array();
               $image=explode('|',$v['fgoods_images']); 
               $forderlist[$k]['fgoods_images']=$image[0];
               unset($image);
            }  
			//物流公司
			$logistics=M('logistics')->select();
            $this->assign('logistics',$logistics);
            $this->assign('forder',$forder);
            $this->assign('forderlist',$forderlist[0]);
            $this->assign('state',$state);
            $this->assign('forder_id',$forder_id);
            $this->assign('fgoods_id',$fgoods_id);
            $this->assign('f_classify_id',$f_classify_id);
            $this->assign('f_classify_fatherid',$f_classify_fatherid);
            $this->display();
        }

    
//------------------------------------- 

   //经销商可代理的产品id
    public function fgoods($agcy_id,$state){
        //查询同一乡镇的代理商（同一乡镇代理过的产品，该经销商订货时不显示）
        $agcys=M('agcy')->where('agcy_id='.$agcy_id)->field('villages')->find();
        $agcy_1=M('agcy')->where('villages='.$agcys['villages'].' and agcy_id!='.$agcy_id)->field('agcy_id')->select();
        //同一乡镇的经销商id
        $datas=array();
        foreach($agcy_1 as $k=>$v){
            $datas[$k]=$v['agcy_id']; 
        }
        
        //查询同一乡镇经销商代理的产品
        if($datas){
            $applyss=M('agcyapply')->where('agcy_id in ('.join(',',$datas).') and agcy_apply_flag=2 ')->field('fgoods_id')->select();
            $goods_id=array();
            foreach($applyss as $k=>$v){
                $goods_id[$k]=$v['fgoods_id']; 
            }
             return $goods_id; 
        }
            return ; 
       
      
      
        
    }
}