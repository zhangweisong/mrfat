<?php
namespace Admin\Controller;
use Think\Controller;
class AgcyController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录状态
        /*if (!D('Admin')->islogin()) {//未登录
            jump('您尚未登录，请先登录！', U('login/login'));
        }*/
    }

    //空方法，防止报错
    public function _empty() {
        $this->index();
    }

    //经销商列表
    public function index() {
        $where = "1 ";
        $info = I("info");
        if ($info) {
            @extract($info);
            //按负责人搜索
            if ($agcy_manager) {
                $agcy_manager = urldecode(trim($agcy_manager));
                $where .= " and (ec_agcy.agcy_manager like '%" . $agcy_manager . "%'
					or ec_agcy.agcy_name like '%" . $agcy_manager . "%'
					or ec_agcy.agcy_tel like '%" . $agcy_manager . "%'
				)";
                $this->assign('agcy_manager', $agcy_manager);
            }  
		 
            //按省份搜索
            if ($p_id) {
                $where .= " and ec_agcy.province=" . $p_id;
                $this->assign('p_id', $p_id);
                $city = $this->cityofprovince($p_id);
                $this->assign('city', $city);
            }
            //按市区搜索
            if ($c_id) {
                $where .= " and ec_agcy.city=" . $c_id;
                $this->assign('c_id', $c_id);
                $area = $this->areaofcity($c_id);
                $this->assign('area', $area);
            }
            //按县搜索
            if ($a_id) {
                $where .= " and ec_agcy.area=" . $a_id;
                $this->assign('a_id', $a_id);
				$villages = $this->villagesofcity($a_id);
                $this->assign('villages', $villages);
            } 
			//乡镇搜索
            if ($v_id) {
                $where .= " and ec_agcy.villages=" . $v_id;
                $this->assign('v_id', $v_id);
            }
            //按店铺地址搜索
            if ($agcy_address) {
                $agcy_address = urldecode(trim($info['agcy_address']));
                $where .= " and ec_agcy.agcy_address like '%" . $agcy_address . "%'";
                $this->assign('agcy_address', $agcy_address);
            }
            //按经销商状态搜索
            if ($agcy_state) {
                $info['agcy_state'] = urldecode(trim($info['agcy_state']));
                $where .= " and ec_agcy.agcy_state =" . $info['agcy_state'] . " ";
                $this->assign('agcy_state', $info['agcy_state']);
            }
            //按添加时间搜索
            if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_agcy.agcy_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_agcy.agcy_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
        }
        $count = M("Agcy")->where($where)
                        ->join("LEFT JOIN ec_province ON ec_province.p_id=ec_agcy.province")
                        ->join("LEFT JOIN ec_city ON ec_city.c_id=ec_agcy.city")
                        ->join("LEFT JOIN ec_area ON ec_area.a_id=ec_agcy.area")
						->join("LEFT JOIN ec_villages ON ec_villages.v_id=ec_agcy.villages")
						->count();
        
        $Page = new \Think\Page($count, 15);
        //分页跳转的时候保证查询条件
        if ($info) {
            foreach ($info as $key => $val) {
                $Page->parameter['info[' . $key . ']'] = urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        $agcylist = M('Agcy')
                ->field("ec_agcy.*,"
                        . "ec_province.p_name,"
                        . "ec_city.c_name,"
                        . "ec_area.a_name,"
						. "ec_villages.v_name"
						)
                ->join("LEFT JOIN ec_province ON ec_province.p_id=ec_agcy.province")
                ->join("LEFT JOIN ec_city ON ec_city.c_id=ec_agcy.city")
                ->join("LEFT JOIN ec_area ON ec_area.a_id=ec_agcy.area")
				->join("LEFT JOIN ec_villages ON ec_villages.v_id=ec_agcy.villages")
                ->where($where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order("agcy_id desc")
                ->select();
				 
        foreach ($agcylist AS $k => $v) {
            $agcylist[$k]["agcy_addtime"] = date("Y-m-d H:i:s", $v["agcy_addtime"]);
        }
        $this->assign('pageshow', $pageshow);
        $this->assign('agcylist', $agcylist);
        $this->assign('category', $category);
        $this->assign('agcystate_', array('1'=>'启用','2'=>'禁用'));
        $province = $this->allprivince(); //所有省份
        $this->assign('province', $province);
        $this->display();
    }
    //经销商重置密码
    public function chongzhi() {
        $agcy_id = I("get.agcy_id");
        $flag = M("agcy")->where("agcy_id=".$agcy_id)->save(array("agcy_password" => MD5('123456')));
        jump('重置密码成功!', U('agcy/index'));
    }

    //全部省份
    function allprivince() {
        return M("province")->select();
    }
    //省份地下的城市
    function cityofprovince($p_id) {
        if (IS_AJAX) {
            if (!I("get.p_id"))
                return;
            $this->ajaxReturn(M("city")->where("p_id=" . I("get.p_id"))->select());
        } else {
            if (!$p_id)
                return;
            return M("city")->where("p_id=" . $p_id)->select();
        }
    }
    //城市下的县区
    function areaofcity($c_id) {
        if (IS_AJAX) {
            if (!I("get.c_id"))
                return;
            $this->ajaxReturn(M("area")->where("c_id=" . I("get.c_id"))->select());
        } else {
            if (!$c_id)
                return;
            return M("area")->where("c_id=" . $c_id)->select();
        }
    }
	
	//县区下的乡镇
    function villagesofcity($a_id) {
        if (IS_AJAX) {
            if (!I("get.a_id"))
                return;
            $this->ajaxReturn(M("villages")->where("a_id=" . I("get.a_id"))->select());
        } else {
            if (!$a_id)
                return;
            return M("villages")->where("a_id=" . $a_id)->select();
        }
    }
	
    //经销商关闭->开启
    public function ajax_on() {
        $agcy_id = I("get.agcy_id"); //ID
        $field = I("get.field"); //字段，用作下面代码处理
        switch ($field) {
            case "isshow"://on
                M("agcy")->where('agcy_id=' . $agcy_id)->save(array("agcy_state" => 1));
                break;
        }
        $this->ajaxReturn(array("flag" => "1"));
    }

    //经销商开启->关闭
    public function ajax_off() {
        $agcy_id = I("get.agcy_id"); //ID
        $field = I("get.field"); //字段，用作下面代码处理
        switch ($field) {
            case "isshow"://off
                M("agcy")->where('agcy_id=' . $agcy_id)->save(array("agcy_state" => 2));
                break;
        }
        $this->ajaxReturn(array("flag" => "1"));
    }
    //详情信息
    public function details() {
        $agcy_id = I("get.agcy_id");
        $agcy = M("agcy")
                ->field("ec_agcy.*,ec_province.p_name,ec_city.c_name,ec_area.a_name")
                ->join("LEFT JOIN ec_province ON ec_province.p_id=ec_agcy.province")
                ->join("LEFT JOIN ec_city ON ec_city.c_id=ec_agcy.city")
                ->join("LEFT JOIN ec_area ON ec_area.a_id=ec_agcy.area")
                ->where("ec_agcy.agcy_id='" . $agcy_id . "'")
                ->find();
        if ($agcy) {
            $agcy["agcy_addtime"] = date("Y-m-d", $agcy["agcy_addtime"]);
        }
        $arr = explode('|', $agcy["agcy_license_images"]);
        if ($agcy["agcy_license_images"] != "") {
            $arr = $arr;
        } else {
            $arr = "";
        } 
        
        $arr_1 = explode('|', $agcy["agcy_face_images"]);
        if ($agcy["agcy_face_images"] != "") {
            $arr_1 = $arr_1;
        } else {
            $arr_1 = "";
        }
        
        $this->assign('agcy', $agcy);
        $this->assign('arr', $arr);
        $this->assign('arr_1', $arr_1); 
        $this->assign('agcy_id', $agcy_id);
        $this->display();
    }
    
    //添加经销商
    public function addagcy() {
        if (IS_POST) {
            $info = I("info");
            //dd($info);
            $info['admin_id'] = session("admin.admin_id");
            $info['admin_realname'] = session("admin.realname");
            $info['agcy_addtime'] = time();
            $info['agcy_password'] = md5($info['agcy_password']);
            $agcy_name = trim($info['agcy_name']);
            //查询店铺名称去重
            $agcynum_ = M("agcy")->where("agcy_name='" . $agcy_name . "'")->find();
            if ($agcynum_) {
                jump('该店铺名称已经存在，请重新录入！', U('agcy/addagcy'));
                exit();
            }
            $flag = M("agcy")->add($info);
            if ($flag) {
                jump('经销商添加成功！', U('agcy/index'));
            } else {
                jump('经销商添加失败！', U('agcy/index'));
            }
        } else {
            $province = $this->allprivince(); //所有省份
            $this->assign('province', $province);
            $this->display();
        }
    }
    
    //编辑经销商
    public function editagcy() {
        $agcy_id = I("get.agcy_id");
        if (IS_POST) {
            $info = I("info");
            $info['admin_id'] = session("admin.admin_id");
            $info['admin_realname'] = session("admin.realname");
            $agcy_name = trim($info['agcy_name']);
            M("agcy")->data($info)->where("agcy_id=" . $agcy_id)->save();
            jump('经销商编辑成功！', U('agcy/index'));
        } else { 
            $agcy = M("agcy")->where("agcy_id=" . $agcy_id)->find();
            $province = $this->allprivince(); //所有省份
            $city = $this->cityofprovince($agcy['province']); //所有市 
            $area = $this->areaofcity($agcy['city']); //所有区县
			
			$villages = $this->villagesofcity($agcy['area']); //所有乡镇  
            $this->assign('province', $province);
            $this->assign('city', $city);
            $this->assign('area', $area);
			$this->assign('villages', $villages);
            $this->assign('agcy', $agcy);
            $this->display();
        }
    }
    //检测人员信息
    public function checkusername() {
        $agcy_username =trim(I("get.agcy_username")); //用户账号
        $where = " agcy_username= '" . $agcy_username . "' ";
        $listinfo = M('agcy')
                ->where($where)
                ->find();
        if (count($listinfo) < 1) {
            $this->ajaxReturn(array("status" => 0));
        }
        $this->ajaxReturn(array("status" => 2));
    }

    //检测店铺名是否唯一
    public function checkagcy_name() { 
		$agcy_name = urldecode(trim(I("agcy_name"))); 
		$count = M("agcy")->where("agcy_name ='".$agcy_name."'")->count(); 
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
	
	//检测店铺名是否唯一  编辑除了自己以外的
    public function checkagcy_names() { 
		$agcy_name = urldecode(trim(I("agcy_name"))); 
		$agcy_id = urldecode(trim(I("agcy_id"))); 
		$count = M("agcy")->where("agcy_name ='".$agcy_name."' and agcy_id!='".$agcy_id."' ")->count(); 
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

    //检测店铺名是否唯一2
    public function checkagcy_name2() {
        $agcy_name = I("get.agcy_name");
        $agcy_id = I("get.agcy_id");

        $where = " agcy_name= '" . $agcy_name . "' AND agcy_id<>" . $agcy_id;
        $listinfo = M('agcy')
                ->where($where)
                ->find();
        $this->ajaxReturn(count($listinfo));
    }

    //检测手机号 
    public function checkagcy_tel() { 
		$agcy_tel = urldecode(trim(I("agcy_tel"))); 
		$count = M("agcy")->where("agcy_tel ='".$agcy_tel."'")->count(); 
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

    //检测手机号 
    public function checkagcy_tel_2() {
        $agcy_tel = I("get.agcy_tel"); //检测手机号  跟自己不重复
        $agcy_id = I("get.agcy_id");
        $where = " agcy_tel= '" . $agcy_tel . "' AND agcy_id<>" . $agcy_id;
        $listinfo = M('agcy')
                ->where($where)
                ->find();
        if (count($listinfo) < 1) {
            $this->ajaxReturn(array("status" => 0));
        }
        $this->ajaxReturn(array("status" => 2));
    }
    //删除经销商
    public function delagcy(){
        $agcy_id=I("get.agcy_id");
        $agcyinfo=M("agcy")->where('agcy_id = '.$agcy_id)->delete();
        if($agcyinfo){
            jump('删除成功！',U('agcy/index'),3);
        }else{
            jump('删除失败！',U('agcy/index'),3);
        }
    }

}
