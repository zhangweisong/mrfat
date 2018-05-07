<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/04/27 0027
 * Time: 14:13:19
 */
namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class NjclassifyController extends Controller {

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

    //农技分类列表
    public function index() {
        /*
         * 分类应该是：
         * 一级分= 父ID是0
         *
         * 二级分类 == 父ID是一级分类的ID
         *
         * 注意删除的时候 二级分类下没有图书，一级分类下没有二级分类
         */

        $nj_father_id = I("get.nj_father_id") ? I("get.nj_father_id") : 0;
        $this->assign("nj_father_id", $nj_father_id);
        $where = "1";
        $where .= " AND nj_classify_fatherid=" . $nj_father_id;
        $info = I("info");
        if ($info) {// 搜索条件
            @extract($info);
            if ($nj_classify_name_s) {
                $nj_classify_name_s = urldecode(trim($info['nj_classify_name_s']));
                $where .= " and nj_classify_name LIKE '%" . $nj_classify_name_s . "%'";
                $this->assign('nj_classify_name_s', $nj_classify_name_s);
            }
            if ($nj_father_id_s) {
                $where .= " and nj_classify_fatherid='" . $nj_father_id_s . "'";
                $this->assign('father_id_s', $nj_father_id_s);
            }
        }
        // 查询满足要求的总记录数
        $count = M('Njclassify')
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
        $listinfo = M('Njclassify')
            ->where($where)
            ->order("nj_classify_id DESC")
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        if ($nj_father_id == 0) {
            foreach ($listinfo AS $k => $v) {
                //获取父分类下子分类的个数
                $listinfo[$k]['child_count'] = M("Njclassify")->where("nj_classify_fatherid=" . $v["nj_classify_id"])->count();
            }
        } else {
            //获取子分类的父分类名称
            $father = M('Njclassify')->find($nj_father_id);
            $father_name = $father['nj_classify_name'];
            foreach ($listinfo AS $k => $v) {
                $listinfo[$k]['child_count'] = M("njliving")->where("nj_classify_two=" . $v["nj_classify_id"]." AND nj_isable=1")->count();
            }
        }
        $father = M("Njclassify")->where("nj_classify_fatherid=0")->select();
        $this->assign('father', $father);
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow);
        $this->assign('father_name', $father_name);
        $this->display();
    }

    //创建农技分类
    public function add() {
        $nj_father_id = I("get.nj_father_id") ? I("get.nj_father_id") : 0;
        $this->assign('nj_father_id', $nj_father_id);
        if (IS_POST) {
            $data = I("info");
            $flag = M("njclassify")->add($data);
            if ($flag) {
                jump('添加农技分类成功！', U('njclassify/index'));
            } else {
                jump('添加农技分类失败！', U('njclassify/index'));
            }
        } else {
            $where = "nj_classify_fatherid=0";
            $father = M("njclassify")->where($where)->select();
            $this->assign('father', $father);
            $this->display();
        }
    }

    //编辑图书分类
    public function edit() {
        $nj_classify_id = I("get.nj_classify_id");
        $njclassify = M("njclassify")->find($nj_classify_id);
        if (IS_POST) {
            $data = I("info");
            M("njclassify")->where("nj_classify_id=" . $nj_classify_id)->save($data);
            jump('编辑农技分类成功！', U('njclassify/index', array('father_id' => $njclassify['nj_classify_fatherid'])));
        } else {
            $where = "nj_classify_fatherid=0";
            $father = M("njclassify")->where($where)->select();
            $this->assign('father', $father);
            $this->assign('njclassify', $njclassify);
            $this->assign('nj_classify_id', $nj_classify_id);
            $this->display();
        }
    }



    //删除图书分类
    public function delete() {
        //删除的时候首先判断，如果是顶级分类，检查下面是否有次级分类
        //如果是次级分类，检查下面有没有图书
        $nj_classify_id = I("nj_classify_id");
        $njclassify = M("njclassify")->find($nj_classify_id);
        if ($njclassify['nj_classify_fatherid'] == 0) {
            $count = M("njclassify")->where("nj_classify_fatherid=" . $nj_classify_id)->count();
            if ($count > 0) {
                jump('底层有分类，不能删除！', U('njclassify/index'));
            } else {
                M("njclassify")->delete($nj_classify_id);
                jump('农技分类删除成功', U('njclassify/index'));
            }
        } else {
            $count = M("njliving")->where("nj_classify_two='" . $nj_classify_id)->count();
            if ($count > 0) {
                jump('该类目下有直播，不能删除！', U('njclassify/index', array('nj_father_id' => $njclassify['nj_classify_fatherid'])));
            } else {
                M("njclassify")->delete($nj_classify_id);
                jump('农技分类删除成功', U('njclassify/index', array('nj_father_id' => $njclassify['nj_classify_fatherid'])));
            }
        }
    }

    //判断一级分类的存在性
    public function names(){
        $nj_classify_name = urldecode(trim(I("nj_classify_name")));
        $nj_father_id = urldecode(trim(I("nj_father_id")?I("nj_father_id"):0));
        if($nj_father_id){
            //同一分类下的子分类不能重复，子分类不可以和父级重名
            $count = M("njclassify")->where("(nj_classify_name ='".$nj_classify_name."' and nj_classify_fatherid=".$nj_father_id." ) or (nj_classify_name ='".$nj_classify_name."' and nj_classify_id=".$nj_father_id.")")->count();
        }else{
            $count = M("njclassify")->where("nj_classify_name ='".$nj_classify_name."' and nj_classify_fatherid=".$nj_father_id." ")->count();
        }
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

    //判断一级分类的存在性修改
    public function name_es(){
        $nj_classify_name = urldecode(trim(I("nj_classify_name")));
        $nj_classify_id = I("nj_classify_id");
        $nj_classify_fatherid = I("nj_classify_fatherid");
        if($nj_classify_fatherid){//（二级分类名称不能与父级名称重复）
            $count = M("njclassify")->where("(nj_classify_id!='".$nj_classify_id."' and nj_classify_name ='".$nj_classify_name."' and nj_classify_fatherid=".$nj_classify_fatherid." )  or (nj_classify_name ='".$nj_classify_name."' and nj_classify_id=".nj_classify_fatherid.")")->count();
        }else{
            $count = M("njclassify")->where("nj_classify_id!='".$nj_classify_id."' and nj_classify_name ='".$nj_classify_name."' and nj_classify_fatherid =".$nj_classify_fatherid."")->count();
        }
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
}
