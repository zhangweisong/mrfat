<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class SlcropclassController extends Controller {

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

    //农作物分类列表
    public function cropclass() {
        $father_id = I("get.cpc_fid") ? I("get.cpc_fid") : 0; //父类id
        $this->assign("father_id", $father_id);
        $where = "1";
        $where .= " AND cpc_fid=" . $father_id;
        $info = I("info");
		if ($info) {// 搜索条件  
            @extract($info);
            if ($classify_name_s) {
                $classify_name_s = urldecode(trim($info['classify_name_s']));
                $where .= " and cpc_title LIKE '%" . $classify_name_s . "%'";
                $this->assign('classify_name_s', $classify_name_s);
            }
            if ($father_id_s) {
                $where .= " and cpc_isable = 1 and cpc_fid='" . $father_id_s . "'";
                $this->assign('father_id_s', $father_id_s);
            }
            if($cpc_isable){
                $info['cpc_isable']=urldecode(trim($info['cpc_isable']));
                $where.=" and cpc_isable = ".$info['cpc_isable'];
                $this->assign('cpc_isablee',$info['cpc_isable']);
            }
        }
        $model = M('Slcropclass');
        // 查询满足要求的总记录数
        $count = $model
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
                ->where($where)
                ->order("cpc_id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        if ($father_id == 0) {
            foreach ($listinfo AS $k => $v) {
                $listinfo[$k]['child_count'] = M("Slcropclass")->where("cpc_fid=" . $v["cpc_id"])->count();
            }
        } else {
            $father = $model->find($father_id);
            $father_name = $father['cpc_title'];
            foreach ($listinfo AS $k => $v) {
                $listinfo[$k]['child_count'] = M("Slcrop")->where("cp_twoid=" . $v["cpc_id"]." and cp_isable=1 and cp_oneid='".$father_id."'")->count(); 
            }
        } 
        foreach ($listinfo AS $k => $v) {
            $listinfo[$k]['cpc_remarks'] = str_cut($v['cpc_remarks'], 20);
            $listinfo[$k]['remarks'] = $v['cpc_remarks'];
        }
        $father = M("Slcropclass")->where("cpc_isable = 1 and cpc_fid=0")->select();
        $this->assign('father', $father);
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow);
		$this->assign('isable', D('jifenclassify')->get_isable());
        $this->assign('cpc_isable',D("slcropclass")->cpc_isables());
        $this->assign('father_name', $father_name);
        $this->display();
    }
	
	//添加农作物分类
    public function add() {
        $father_id = I("get.cpc_fid") ? I("get.cpc_fid") : 0;
        $this->assign('father_id', $father_id);
        if (IS_POST) {
            $data = I("info"); 
            $flag = M("Slcropclass")->add($data);
            if ($flag) {
                jump('添加农作物分类成功！', U('slcropclass/cropclass'));
            } else {
                jump('添加农作物分类失败！', U('slcropclass/cropclass'));
            }
            //
        } else {
            $where = "cpc_fid=0";
            $father = M("Slcropclass")->where($where)->select();
            $this->assign('father', $father);
            $this->display();
        }
    }

	//判断一级分类的存在性
	public function names(){
		$classify_name = urldecode(trim(I("classify_name")));
		$count = M("Slcropclass")->where("cpc_title ='".$classify_name."'")->count(); 
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
    //编辑农作物分类
    public function edit() {
        $classify_id = I("get.cpc_id");
        $classify = M("Slcropclass")->find($classify_id);
        if (IS_POST) {
            $data = I("info");
            M("Slcropclass")->where("cpc_id=" . $classify_id)->save($data);
            jump('编辑农作物分类成功！', U('slcropclass/cropclass', array('cpc_fid' => $classify['cpc_fid'])));
        } else {
            $where = "cpc_fid=0";
            $this->assign('classify', $classify);
			$this->assign('classify_id', $classify_id); 
            $father = M("Slcropclass")->where($where)->select();
            $this->assign('father', $father);
            $this->display();
        }
    }
	
	//判断一级分类的存在性修改
	public function name_es(){
		$classify_name = urldecode(trim(I("classify_name")));
		$classify_id = I("classify_id");  
		$count = M("Slcropclass")->where("cpc_id!='".$classify_id."' and cpc_title ='".$classify_name."'")
				->count(); 
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

    //删除农作物分类
    public function delete() {
        //删除的时候首先判断，如果是顶级分类，检查下面是否有次级分类
        //如果是次级分类，检查下面有没有图书 
        $classify_id = I("cpc_id");
        $classify = M("Slcropclass")->find($classify_id);
        if ($classify['cpc_fid'] == 0) {
            $count = M("Slcropclass")->where("cpc_fid=" . $classify_id)->count();
            if ($count > 0) {
                jump('底层有分类，不能删除！', U('slcropclass/cropclass'));
            } else {
                M("Slcropclass")->delete($classify_id);
                jump('农作物分类删除成功', U('slcropclass/cropclass'));
            }
        } else {
            $count = M("Slcrop")->where("cp_twoid='" . $classify_id."' and cp_isable=1")->count();
            if ($count > 0) {
                jump('该类目下有农作物，不能删除！', U('slcropclass/cropclass', array('father_id' => $classify['cp_oneid'])));
            } else {
                M("Slcropclass")->delete($classify_id);
                jump('农作物分类删除成功', U('slcropclass/cropclass', array('father_id' => $classify['cp_oneid'])));
            }
        }
    }
	
	
	//农作物列表
    public function slcrop() {
        $where = "cp_isable = 1 ";
        $info = I("info");
        if ($info) {// 搜索条件
            @extract($info);
            if ($goods_name_s) { 
                $where .= " and ec_slcrop.cp_title LIKE '%" . $goods_name_s . "%'";
                $goods_name = urldecode(trim($info['goods_name_s']));
                $this->assign('goods_name', $goods_name);
            }
            if ($yiji_s) {
                $where .= " and ec_slcrop.cp_oneid=" . $yiji_s;
                $erji = M("Slcropclass")->where(" cpc_isable = 1 and cpc_fid=" . $yiji_s)->select();
                $this->assign('erji', $erji);
                $this->assign('yiji_s', $yiji_s);
            }
            if ($erji_s) {
                $where .= " and ec_slcrop.cp_twoid='" . $erji_s . "'";
                $this->assign('erji_s', $erji_s);
            }
        }

        $model = M('Slcrop');
        // 查询满足要求的总记录数
        $count = $model
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
					->field("ec_slcrop.*,yiji.cpc_title AS yiji_name,erji.cpc_title AS erji_name")
					->join("LEFT JOIN ec_slcropclass AS yiji ON yiji.cpc_id = ec_slcrop.cp_oneid")
					->join("LEFT JOIN ec_slcropclass AS erji ON erji.cpc_id = ec_slcrop.cp_twoid")
					->where($where)
					->order("ec_slcrop.cp_id DESC")
					->limit($Page->firstRow . ',' . $Page->listRows)
					->select();
        foreach ($listinfo AS $k => $v) {
            $listinfo[$k]['cover'] = explode('|', $v['cp_image'])[0];
            $listinfo[$k]['cp_remarks'] = str_cut($v['cp_remarks'], 20);
        }
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow);
        //一级分类
        $yiji = M("Slcropclass")->where("cpc_isable = 1 and cpc_fid=0")->select();
        $this->assign('yiji', $yiji);
		$this->assign('flag', D('jifenclassify')->get_isables());
        $this->display();
    }

	function get_erji() {
        $yiji_id = I("yiji_id");
        $where = "cpc_isable = 1 and cpc_fid=" . $yiji_id;
        $erji = M("Slcropclass")->where($where)->select();
        $this->ajaxReturn($erji);
    }
	
	//添加农作物
    public function add_slcrop() {
        if (IS_POST) {
            $data = I("info");
            $flag = M("Slcrop")->add($data);
            if ($flag) {
                jump('添加农作物成功！', U('slcropclass/slcrop'));
            } else {
                jump('添加农作物失败！', U('slcropclass/slcrop'));
            }
        } else {
            $yiji = M("slcropclass")->where("cpc_fid=0 and cpc_isable=1 ")->select();//分类显示的状态
            $this->assign('yiji', $yiji);
            $this->display();
        }
    }

	
    //编辑农作物
    public function edit_slcrop() {
        $cp_id = I("get.cp_id");
        if (IS_POST) {
            $data = I("info");
            M("Slcrop")->where("cp_id=" . $cp_id)->save($data);
            jump('编辑农作物成功！', U('slcropclass/slcrop'));
        } else {
            $goods = M("Slcrop")->find($cp_id);
            $goods_images = explode('|', $goods['cp_image']);
            $yiji = M("Slcropclass")->where("cpc_fid=0 and cpc_isable=1 ")->select();
            $erji = M("Slcropclass")->where("cpc_fid=" . $goods['cp_oneid'])->select();
            $this->assign('yiji', $yiji);
			//dump($yiji);
            $this->assign('goods_images', $goods_images);
            $this->assign('erji', $erji);
            $this->assign('goods', $goods);
            $this->display();
        }
    }

    //删除农作物
    public function delete_slcrop() {
        //删除的时候首先判断，如果是顶级分类，检查下面是否有次级分类
        //如果是次级分类，检查下面有没有图书 
        $cp_id = I("cp_id");
        $flag = M("Slcrop")->where("cp_id=" . $cp_id)->save(array("cp_isable" => 2));
        if ($flag) {
            jump('删除农作物成功！', U('slcropclass/slcrop'));
        } else {
            jump('删除农作物失败！', U('slcropclass/slcrop'));
        }
    }

	//ajax处理状态
	public function ajaxable_on(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "cpc_isable"://on
				M("Slcropclass")->where('cpc_id='.$id)->save(array("cpc_isable"=>1));
				break;
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	
	public function ajaxable_off(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "cpc_isable"://off
				M("Slcropclass")->where('cpc_id='.$id)->save(array("cpc_isable"=>2));
				break;
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}

}
