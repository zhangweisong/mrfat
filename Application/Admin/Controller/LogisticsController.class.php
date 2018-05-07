<?php
namespace Admin\Controller;
use Think\Controller;
class LogisticsController extends Controller {

	function __construct() {
		//继承父类
		parent::__construct();

       //判断登录状态
		if(!D('admininfo')->islogin()){//未登录
			jump('您尚未登录，请先登录！',U('login/login'),3);
		}	
    }

	//空方法，防止报错
	public function _empty(){
        $this->index();
    }
	
	//默认首页
    public function index(){
		$this->display();
    }
    
   
    //物流公司列表
    public function logistics(){
        $where="1 ";
        $count= M('logistics')->where($where)->count();
        $Page= new \Think\Page($count,15);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info['.$key.']'] = urlencode($val);
            }
        }
        $pageshow = $Page->adminshow();  
        $logistics=M('logistics')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select(); 
        $this->assign('logistics',$logistics);
        $this->display(); 
    }
    
    //添加
    public function addlogistics(){
        if(IS_POST){
            $info=I("info");
            $data['logistics_name']=$info['logistics_name'];
            $data['logistics_phone']=$info['logistics_phone'];
            dump($data);
            $flag=M('logistics')->add($data);
            if($flag){
                jump('添加成功',U('logistics/logistics'));
            }else{
                jump('添加失败',U('logistics/logistics'));
            }
        }else{
            $this->display();  
        }
    }
    
    //编辑
    public function editlogistics(){
        $logistics_id=I("get.logistics_id");
        if(IS_POST){
            $info=I("info");
            $flag=M('Logistics')->where('logistics_id='.$logistics_id)->save(array('logistics_name'=>$info['logistics_name'],'logistics_phone'=>$info['logistics_phone']));
            if($flag){
                jump('编辑成功',U('logistics/logistics'));
            }else{
                jump('编辑失败',U('logistics/logistics'));
            }
        }else{
            $flag=M('Logistics')->where('logistics_id='.$logistics_id)->find();
            $this->assign('flag',$flag);
            $this->assign('logistics_id',$logistics_id);
            $this->display();  
        }
    }
    

    

    //删除
    public function dellogistics(){
        $logistics_id=I("get.logistics_id");
        $flag=M('Logistics')->where('logistics_id='.$logistics_id)->delete();
        if($flag){
            jump('删除成功',U('logistics/logistics'));
        }else{
            jump('删除失败',U('logistics/logistics'));
        }
       
    }
  
    
}