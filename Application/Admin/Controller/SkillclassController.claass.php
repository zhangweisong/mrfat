<?php
/**
 * Created by PhpStorm.
 * User: DangMengmeng
 * Date: 2018/3/2 0002
 * Time: 上午 11:45
 */
namespace Admin\Controller;
use Think\Controller;

class SkillclassController extends Controller{
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
}