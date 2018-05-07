<?php
namespace Admin\Controller;
use Think\Controller;
class TownshipController extends Controller
{
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
	//乡镇列表
    public function index(){
        $where=" 1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($p_id){
                $info['p_id']=urldecode(trim($info['p_id']));
                $where.=" and ec_area.p_id = ".$p_id;
                $cityList=M('city')->where('p_id = '.$p_id)->field('ec_city.c_id,ec_city.c_name')->select();
                $this->assign('city1',$cityList);
                $this->assign('p_id',$p_id);
            }
            if($c_id){
                $info['c_id']=urldecode(trim($info['c_id']));
                $where.=" and  ec_area.c_id = ".$c_id;
                $areaList=M('area')->where('c_id = '.$c_id)->field('ec_area.a_id,ec_area.a_name')->select();
                $this->assign('area1',$areaList);
                $this->assign('c_id',urldecode($c_id));
            }
			if($a_id){
                $info['a_id']=urldecode(trim($info['a_id']));
                $where.=" and  ec_villages.a_id = ".$a_id;
                $areaList=M('villages')->where('a_id = '.$a_id)->field('ec_villages.v_id,ec_villages.v_name')->select();
                $this->assign('villages1',$areaList);
                $this->assign('a_id',urldecode($a_id));
            }
        }
        $villages=M('villages');
        // 查询满足要求的总记录数
        $count      = $villages
            ->join(" left join ec_province on ec_province.p_id = ec_villages.p_id")
            ->join(" left join ec_city on ec_city.c_id = ec_villages.c_id")
			->join(" left join ec_area on ec_area.a_id = ec_villages.a_id")
            ->where($where)
            ->count();
        $Page       = new \Think\Page($count,15);
        //分页跳转的时候保证查询条件
        if($info){
            foreach($info as $key=>$val) {
                $Page->parameter['info['.$key.']']   =   urlencode($val);
            }
        }
        $pageshow   = $Page->adminshow();
        // 进行分页数据查询
        $areaList = $villages
            ->where($where)
            ->join(" left join ec_province on ec_province.p_id = ec_villages.p_id")
            ->join(" left join ec_city on ec_city.c_id = ec_villages.c_id")
			->join(" left join ec_area on ec_area.a_id = ec_villages.a_id")
            ->order('v_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('areaList',$areaList);
        $this->assign('province',D("province")->getProvinceName());
        $this->assign('pageshow',$pageshow);
        $this->display();
    }


    //乡镇添加
    public function  add()
    {
        if (IS_POST){
            $data['p_id']=$_POST['p_id'];
            $data['c_id']=$_POST['c_id'];
			$data['a_id']=$_POST['a_id'];
            $data['v_name']=trim($_POST['v_name']);
            $flag = M('villages')->where($data)->find();
            if (!$flag) {
                    $addInfo=M('villages')->add($data);
                if($addInfo){
                    jump('添加乡镇信息成功！',U('township/index'));
                }else{
                    jump('添加乡镇信息失败！',U('township/add'));
                }
            }else{
                jump('该市区已存在此乡镇名称！',U('township/add'));
            }
        }else{
            $this->assign('province',D("province")->getProvinceName());
            $this->display();
        }
    }
 

    //编辑乡镇信息
    public function edit()
    {
        $vId=$_GET['v_id'];
        if (IS_POST){
            $data['p_id']=$_POST['p_id'];
            $data['c_id']=$_POST['c_id'];
            $data['a_id']=$_POST['a_id'];
			$data['v_name']=trim($_POST['v_name']);
			$where_=" p_id ='".$data['p_id']."' and c_id='".$data['c_id']."' and v_name='".$data['v_name']."' and a_id='".$data['a_id']."' and  v_id!='".$vId."' ";
            $info = M("villages")->where($where_)->find();

            if (!$info) {
                    $flag=M('villages')->where("v_id=".$vId)->save($data);
                if($flag){
                    jump('编辑县区信息成功！',U('township/index'));
                }else{
                    jump('您未做任何修改！',U('township/edit',array('v_id' =>$vId)));
                }
            }else{
                jump('该市区已存在此乡镇名称！',U('township/edit',array('v_id' =>$vId)));
            }
           
        }else{
            $this->assign('province',D("province")->getProvinceName());
            $areaInfo=M('villages')->where("v_id=".$vId)->find();
            //获取响应的省份下面的城市：
            $p_id=$areaInfo['p_id'];
            $cityList=M('city')->where('p_id = '.$p_id)->field('ec_city.c_id,ec_city.c_name')->select();
			$c_id=$areaInfo['c_id'];
			$arealist=M('area')->where('c_id = '.$c_id)->field('ec_area.a_id,ec_area.a_name')->select();
            $this->assign('city1',$cityList);
			$this->assign('area1',$arealist);
            $this->assign('area',$areaInfo);
			//dump($areaInfo);die;
            $this->display();
        }
    }
	//删除乡镇
	public function del(){
		$vId=$_GET['v_id'];
        $delAreaM=M('villages')->where('v_id ='.$vId)->delete();
        if($delArea){
            jump('删除乡镇信息成功！',U('township/index'));
        }else{
            jump('删除乡镇信息失败！',U('township/index'));
        }
	}
	  //根据选择的省份获取相对应的城市信息
    public function getCityName()
    {
        $pid=I('get.p_id');
        $result=M('city')->where('p_id = '.$pid)->select();
        $this->ajaxReturn(array("data"=>$result));
    }
	 //getAreaName
    public function getAreaName()
    {
        $cid=I('get.c_id');
        $result=M('area')->where('c_id = '.$cid)->field('ec_area.a_id,ec_area.a_name')->select();
        $this->ajaxReturn(array("data"=>$result));
    }
}