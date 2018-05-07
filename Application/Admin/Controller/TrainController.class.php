<?php

/*
 * 厂家管理
 * 张伟松
 * 2018年3月1日09:38:31 
 */

namespace Admin\Controller;

use Think\Controller;

class TrainController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录状态
//        if (!D('Admin')->islogin()) {//未登录
//            jump('您尚未登录，请先登录！', U('login/login'));
//        }
    }

    //空方法，防止报错
    public function _empty() {
        $this->index();
    }

    //店铺列表
    public function index() {
        $where = "1 ";
        $info = I("info");
        if ($info) {
            @extract($info);
            //按名称搜索 
            if ($key) {
                $key = urldecode(trim($key));
                $where .= " and (name like '%" . $key . "%'
							or  phone like '%" . $key . "%'
							or  people like '%" . $key . "%'
				)";
                $this->assign('key', $key);
            }
            
             
        }
        $count = M("train")->where($where) 
                        ->count();
        $pagesize = I('pagesize') ? I('pagesize') : 15;
        $Page = new \Think\Page($count, $pagesize);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info[' . $key . ']'] = urlencode($val);
            }
        }
        $Page->parameter['pagesize'] = $pagesize;
        $pageshow = $Page->adminshow();
        $train = M('train')  
                ->where($where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order("id desc")
                ->select();  
        $this->assign('pageshow', $pageshow);
        $this->assign('train', $train);  
        $this->display();
    }

    //添加店铺
    public function add() {
        if (IS_POST) {
            $info = I("info"); 
            //数组
            $label = $_POST['labels'];
            $res = implode("|",$label);
            $info['job'] = $res ;  
            if (M("train")->add($info)) {
                jump('添加成功！', U('train/index'));
            } else {
                jump('添加失败！', U('train/index'));
            }
        } else { 
             //行业标签
            $biao = M("trainclassfiy")->where("1")->select(); 
            $this->assign('biao',$biao); 
            $this->display();
        }
    }

    //编辑店铺
    public function edit() {
        $id = I("get.id");
        if (IS_POST) {
            $info = I("info");
            M("train")->data($info)->where("id=" . $id)->save();
            jump('编辑成功！', U('train/index'));
        } else {
            $train = M("train")->find($id);
             //行业标签
            $biao = M("trainclassfiy")->where("1")->select(); 
            $this->assign('biao',$biao);    
            $this->assign('train', $train);
            $this->display();
        }
    }  
    
     //删除
    public function del(){
    	$id = I('get.id'); 
    	M('train')->where(array('id'=>$id))->delete();
    	jump('删除成功',U('train/index'));
    }
    
     public function info(){
    	$id = I('get.id'); 
    	$train = M('train') 
            ->where(array('id'=>$id))
            ->find();
        $job = explode('|',$train['job']);
        $row = [];
        foreach($job as $k=>$v){
            $res = M("trainclassfiy")->find($v);
            $row[] = $res['name'];
        }  
        $arr2=implode(",",$row); 
        $train['job'] =  $arr2; 
    	$this->assign('train', $train); 
        $this->display();
        
    }
    
    //添加店铺
    public function addnotice() { 
        $id = I("id"); 
        if (IS_POST) {
            $info = I("info");  
            $info['train_id'] = $id;
            $info['n_flag'] = 1; 
            if (M("notice")->add($info)) {
                jump('添加成功！', U('train/index'));
            } else {
                jump('添加失败！', U('train/index'));
            }
        } else {  
            $this->assign('id', $id); 
            $this->display();
        }
    }
}
