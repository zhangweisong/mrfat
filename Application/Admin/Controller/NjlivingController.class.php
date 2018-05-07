<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/04/27 0027
 * Time: 16:20:21
 */
namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class NjlivingController extends Controller {

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

    //农技直播列表
    public function index() {
        $where = " 1 ";
        $info = I("info");
        if ($info) {// 搜索条件
            @extract($info);
            if ($nj_name_s) {
                $where .= " and ec_njliving.nj_name LIKE '%" . $nj_name_s . "%'";
                $nj_name_s = urldecode(trim($info['nj_name_s']));
                $this->assign('nj_name_s', $nj_name_s);
            }
            if ($yiji_s) {
                $where .= " and ec_njliving.nj_classify_one=" . $yiji_s;
                $erji = M("Njclassify")->where(" nj_classify_fatherid=" . $yiji_s)->select();
                $this->assign('erji', $erji);
                $this->assign('yiji_s', $yiji_s);
            }
            if ($erji_s) {
                $where .= " and ec_njliving.nj_classify_two='" . $erji_s . "'";
                $this->assign('erji_s', $erji_s);
            }
            if($nj_isable){
                $info['nj_isable']=urldecode(trim($info['nj_isable']));
                $where.=" and ec_njliving.nj_isable ='".$info['nj_isable']."'";
                $this->assign('nj_isable', $info['nj_isable']);
            }
        }

        $model = M('Njliving');
        // 查询满足要求的总记录数
        $count = $model
            ->join("LEFT JOIN ec_njclassify AS yiji ON yiji.nj_classify_id = ec_njliving.nj_classify_one")
            ->join("LEFT JOIN ec_njclassify AS erji ON erji.nj_classify_id = ec_njliving.nj_classify_two")
            ->where($where)
            ->count();
        $Page = new \Think\Page($count, 20);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info[' . $key . ']'] = urlencode($val);
            }
        }
        $pageshow = $Page->adminshow();
        // 进行分页数据查询
        $listinfo = $model
            ->join("LEFT JOIN ec_njclassify AS yiji ON yiji.nj_classify_id = ec_njliving.nj_classify_one")
            ->join("LEFT JOIN ec_njclassify AS erji ON erji.nj_classify_id = ec_njliving.nj_classify_two")
            ->field("ec_njliving.*,yiji.nj_classify_name AS yiji_name,erji.nj_classify_name AS erji_name")
            ->where($where)
            ->order("nj_id DESC")
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow);
        //一级分类
        $yiji = M("Njclassify")->where("nj_classify_fatherid = 0 ")->select();
        $this->assign('nj_isables',D("Expert")->nj_isable());//状态
        $this->assign('yiji', $yiji);
        $this->display();
    }

    //创建图书
    public function add() {
        if (IS_POST) {
            $data = I("info");
            $data['nj_addtime']=time();
            $flag = M("Njliving")->add($data);
            if ($flag) {
                jump('添加农技直播成功！', U('njliving/index'));
            } else {
                jump('添加农技直播失败！', U('njliving/index'));
            }

        } else {
            $yiji = M("Njclassify")->where("nj_classify_fatherid = 0 ")->select();
            $this->assign('yiji', $yiji);
            $this->display();
        }
    }

    //编辑图书
    public function edit() {
        $nj_id = I("get.nj_id");
        if (IS_POST) {
            $data = I("info");
            M("Njliving")->where("nj_id=" . $nj_id)->save($data);
            jump('编辑农技直播成功！', U('njliving/index'));
        } else {
            $njliving = M("Njliving")->find($nj_id);
            $yiji = M("Njclassify")->where("nj_classify_fatherid = 0 ")->select();
            $erji = M("Njclassify")->where("nj_classify_fatherid=" . $njliving['nj_classify_one'])->select();
            $this->assign('yiji', $yiji);
            $this->assign('erji', $erji);
            $this->assign('njliving', $njliving);
            $this->assign('nj_id', $nj_id);
            $this->display();
        }
    }



    function get_erji() {
        $yiji_id = I("yiji_id");
        $where = " nj_classify_fatherid=" . $yiji_id;
        $erji = M("Njclassify")->where($where)->select();
        $this->ajaxReturn($erji);
    }

    //删除图书
    public function delete() {
        $nj_id = I("nj_id");
        $flag = M("Njliving")->where("nj_id=" . $nj_id)->delete();
        if ($flag) {
            jump('删除农技直播成功！', U('njliving/index'));
        } else {
            jump('删除农技直播失败！', U('njliving/index'));
        }
    }

    //判断图书名称的存在性
    public function names(){
        $nj_name = urldecode(trim(I("nj_name")));
        $yijif = I("yijif");
        $erjif = I("erjif");
        $count = M("Njliving")->where("nj_name ='".$nj_name."' and nj_classify_one='".$yijif."' and nj_classify_two='".$erjif."'")->count();
        if ($count) {
            $response = array(
                'errno' => 0,
            );
        } else {
            $response = array(
                'errno' => -1,
            );
        }

        echo json_encode($response);

    }

    //判断图书名称的存在性
    public function name_es(){
        $nj_name = urldecode(trim(I("nj_name")));
        $yijif = I("yijif");
        $erjif = I("erjif");
        $nj_id = I("get.nj_id");
        $count = M("Njliving")->where("nj_id !='".$nj_id."' and nj_name='".$nj_name."' and nj_classify_one='".$yijif."' and nj_classify_two ='".$erjif."'")->count();
        if ($count) {
            $response = array(
                'errno' => 0,
            );
        } else {
            $response = array(
                'errno' => -1,
            );
        }
        echo json_encode($response);

    }
    //ajax处理状态
    public function ajaxable_on(){
        $nj_id=I("get.nj_id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "nj_isable"://on
                M("Njliving")->where('nj_id='.$nj_id)->save(array("nj_isable"=>1));
                break;

        }
        $this->ajaxReturn(array("flag"=>"1"));
    }
    public function ajaxable_off(){
        $nj_id=I("get.nj_id");//ID
        $field=I("get.field");//字段，用作下面代码处理
        switch($field){
            case "nj_isable"://off
                M("Njliving")->where('nj_id='.$nj_id)->save(array("nj_isable"=>2));
                break;

        }
        $this->ajaxReturn(array("flag"=>"1"));
    }

    //查看详情
    public function info() {
        $nj_id = I("get.nj_id");
        $njliving = M('Njliving')
            ->where("nj_id = ".$nj_id)
            ->find();
        $this->assign('njliving',$njliving);
        $this->display();
    }


}
