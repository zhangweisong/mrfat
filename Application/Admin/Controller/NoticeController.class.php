<?php
namespace Admin\Controller;
use Think\Controller;
class NoticeController extends Controller {

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
	
    //培训机构通告列表
    public function index(){
        $where=" ec_notice.n_flag=1 ";  //只显示启用的
        $count= M('Notice')
                ->where($where)
                ->join('left join ec_train on ec_train.id=ec_notice.train_id')
                ->join('left join ec_trainclassfiy on ec_trainclassfiy.id=ec_train.job')
                ->count();
        $Page= new \Think\Page($count,15);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info['.$key.']'] = urlencode($val);
            }
        }
        $pageshow = $Page->adminshow();  
        $notice=M('Notice')
                ->join('left join ec_train on ec_train.id=ec_notice.train_id')
                ->field('ec_notice.*,ec_train.name,ec_train.job')
                ->where($where)
                ->limit($Page->firstRow.','.$Page->listRows)
                ->select();
              
        $this->assign('notice',$notice);
        $this->display(); 
    }
    
    //添加
    public function addnotice(){
        if(IS_POST){
            $info=I("info");
            $data['train_id']=$info['train_id'];
            $data['n_content']=$info['n_content'];
            $data['n_name']=$info['n_name'];
            $flag=M('notice')->add($data);
            if($flag){
                jump('添加成功',U('notice/index'));
            }else{
                jump('添加失败',U('notice/index'));
            }
        }else{
            //查询机构名称
            $this->assign('train',D('notice')->get_train());
            $this->display();  
        }
    }
    
    //编辑
    public function editnotice(){
        $n_id=I("get.n_id");
        if(IS_POST){
            $info=I("info");
            $flag=M('notice')->where('n_id='.$n_id)->save(array('train_id'=>$info['train_id'],'n_content'=>$info['n_content'],'n_name'=>$info['n_name']));
            if($flag){
                jump('编辑成功',U('notice/index'));
            }else{
                jump('编辑失败',U('notice/index'));
            }
        }else{
            $trains=M('notice')->where('n_id='.$n_id)->find();
            $tr=M('train')->where('id='.$trains['train_id'])->find();
            $this->assign('tr',$tr['id']);
            $this->assign('trains',$trains);
            $this->assign('train',D('notice')->get_train());//查询机构名称
            $this->assign('n_id',$n_id);
            $this->display();  
        }
    }
    

    //删除
    public function delnotice(){
        $n_id=I("get.n_id");
        $flag=M('notice')->where('n_id='.$n_id)->delete();
        if($flag){
            jump('删除成功',U('notice/index'));
        }else{
            jump('删除失败',U('notice/index'));
        }
       
    }
    
    //ajax处理状态
	public function ajaxable_on(){
		$n_id=I("get.n_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "n_flag"://on
				M("notice")->where('n_id='.$n_id)->save(array("n_flag"=>1));
				break;
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
    
	public function ajaxable_off(){
		$n_id=I("get.n_id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "n_flag"://off
				M("notice")->where('n_id='.$n_id)->save(array("n_flag"=>2));
				break;
			
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
  
    
}