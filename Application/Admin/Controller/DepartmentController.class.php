<?php
namespace Admin\Controller;
use Think\Controller;
class DepartmentController extends Controller 
{
   
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
        dd("no die");
    }
    //省份列表
    public function province(){
        $where=" 1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($keyword){
                $info['keyword']=urldecode(trim($info['keyword']));
                $where.=" and ( ec_siteinfo.code like '%".$info['keyword']."%' )";
                $this->assign('keyword',urldecode($info['keyword']));
            }
        }
        $province=M('province');
        // 查询满足要求的总记录数
        $count      = $province
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
        $proList = $province
            ->where($where)
            ->order('p_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('proList',$proList);
        $this->assign('pageshow',$pageshow);
        $this->display();
    }

    //添加省份
    public function  addprovince()
    {
        if (IS_POST){
            $data['p_name']=trim($_POST['p_name']);
            $addInfo=M('province')->add($data);
            if($addInfo){
                jump('省份信息添加成功！',U('department/province'));
            }else{
                jump('省份信息添加失败！',U('department/province'));
            }
        }else{
            $this->display();
        }
    }
	//省份名称存在性
    public function existnewtype(){
		$param=I("param");
		$p_id=intval($_GET['p_id']);
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{
			if($p_id){
				$where="p_name ='".$param."' and p_id!='".$p_id."' ";
			}else{
				$where="p_name ='".$param."' ";
			}
			$count=M("province")->where($where)->count();
			if($count){//存在，验证失败
				$status="n";
				$info="此省份名称已存在";
			}else{//不存在，验证成功
				$status="y";
				$info="此省份名称不存在，可以增加";
			}
		}
		$this->ajaxReturn(array('status'=>$status,'info'=>$info));
	}
    //编辑省份
    public function editprovince()
    {
        $pId=$_GET['p_id'];
        if (IS_POST){
            $data['p_name']=trim($_POST['p_name']);
            $flag=M('province')->where("p_id=".$pId)->save($data);
            if($flag){
                jump('省份信息编辑成功！',U('department/province'));
            }else{
                jump('您未做任何修改！',U('department/editprovince',array('p_id' =>$pId)));
            }
        }else{
            $provinceInfo=M('province')->where("p_id=".$pId)->find();
            $this->assign('provinceInfo',$provinceInfo);
            $this->display();
        }
    }
    //删除省份信息
    public function delprovince()
    {
        $pId=$_GET['p_id'];
        //删除省份表信息
        $delpro=M('province')->where('p_id ='.$pId)->delete();
        //删除该省份下属的城市信息
        M('city')->where('p_id ='.$pId)->delete();
        //删除该省份下属的县区信息
        M('area')->where('p_id ='.$pId)->delete();
		//删除该省份下属的乡镇信息
        M('villages')->where('p_id ='.$pId)->delete();
        if($delpro){
            jump('删除省份信息成功！',U('department/province'));
        }else{
            jump('删除省份信息失败！',U('department/province'));
        }
    }


    //城市列表：
    public function city(){
        $where=" 1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            if($p_id){
                $info['p_id']=urldecode(trim($info['p_id']));
                $where.=" and ec_city.p_id = ".$p_id;
                $this->assign('p_id',$p_id);
            }
        }
        $city=M('city');
        // 查询满足要求的总记录数
        $count      = $city
            ->join(" left join ec_province on ec_province.p_id = ec_city.p_id")
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
        $cityList = $city
            ->where($where)
            ->join(" left join ec_province on ec_province.p_id = ec_city.p_id")
            ->order('c_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('cityList',$cityList);
        $this->assign('province',D("province")->getProvinceName());
        $this->assign('pageshow',$pageshow);
        $this->display();
    }


    //城市添加
    public function  addcity()
    {
        if (IS_POST){
            $data['p_id']=$_POST['p_id'];
            $data['c_name']=trim($_POST['c_name']);
            $addInfo=M('city')->add($data);
            if($addInfo){
                jump('添加城市信息成功！',U('department/city'));
            }else{
                jump('添加城市信息失败！',U('department/city'));
            }
        }else{
            $this->assign('province',D("province")->getProvinceName());
            $this->display();
        }
    }
     	 //城市名称存在性
  public function existnewtype1(){
		$param=I("param");
		$c_id=intval($_GET['c_id']);
		$p_id=intval($_GET['p_id']); //省份名称id
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{
			if($c_id){
				$where="c_name ='".$param."' and c_id!='".$c_id."' and p_id='".$p_id."' ";
			}else{
				$where="c_name ='".$param."' and p_id='".$p_id."' ";
			}
			$count=M("city")->where($where)->count();
			if($count){	//存在，验证失败
				$status="n";
				$info="此城市名称已存在";
			}else{	//不存在，验证成功
				$status="y";
				$info="此城市名称不存在，可以增加";
			}
		}
		$this->ajaxReturn(array('status'=>$status,'info'=>$info));
	}
    //编辑城市信息
    public function editcity()
    {
        $cId=$_GET['c_id'];
        if (IS_POST){
            $data['p_id']=$_POST['p_id'];
            $data['c_name']=trim($_POST['c_name']);
            $flag=M('city')->where("c_id=".$cId)->save($data);
            if($flag){
                jump('编辑城市信息成功！',U('department/city'));
            }else{
                jump('您未做任何修改！',U('department/editcity',array('c_id' =>$cId)));
            }
        }else{
            $this->assign('province',D("province")->getProvinceName());
            $cityInfo=M('city')->where("c_id=".$cId)->find();
            $this->assign('cityInfo',$cityInfo);
            $this->display();
        }
    }
    //删除城市信息
    public function delcity()
    {
        $cId=$_GET['c_id'];
        $delCity=M('city')->where('c_id ='.$cId)->delete();
        //删除该城市下属的县区信息
        M('area')->where('c_id ='.$cId)->delete();
        //删除该城市下属的乡镇信息
        M('villages')->where('c_id ='.$cId)->delete();
        if($delCity){
            jump('删除城市信息成功！',U('department/city'));
        }else{
            jump('删除城市信息失败！',U('department/city'));
        }
    }
	
	//县区名称存在性
    public function existnewtype2(){
		$param=I("param");
		$a_id=intval($_GET['a_id']);
		$c_id=intval($_GET['c_id']); //市表id
		$p_id=intval($_GET['p_id']); //省份名称id
		if(!$param){
			$status="n";
			$info="参数不正确";
		}else{
			if($a_id){
				$where="a_name ='".$param."' and a_id!='".$a_id."'  and c_id='".$c_id."'  and p_id='".$p_id."' ";
			}else{
				$where="a_name ='".$param."' and c_id='".$c_id."'  and p_id='".$p_id."' ";
			}
			$count=M("area")->where($where)->count();
			if($count){	//存在，验证失败
				$status="n";
				$info="此县区名称已存在";
			}else{	//不存在，验证成功
				$status="y";
				$info="此县区名称不存在，可以增加";
			}
		}
		$this->ajaxReturn(array('status'=>$status,'info'=>$info));
	}
    //县区列表：
    public function area(){
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
			
        }
        $area=M('area');
        // 查询满足要求的总记录数
        $count      = $area
            ->join(" left join ec_province on ec_province.p_id = ec_area.p_id")
            ->join(" left join ec_city on ec_city.c_id = ec_area.c_id")
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
        $areaList = $area
            ->where($where)
            ->join(" left join ec_province on ec_province.p_id = ec_area.p_id")
            ->join(" left join ec_city on ec_city.c_id = ec_area.c_id")
            ->order('a_id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('areaList',$areaList);
        $this->assign('province',D("province")->getProvinceName());
        $this->assign('pageshow',$pageshow);
        $this->display();
    }


    //县区添加
    public function  addarea()
    {
        if (IS_POST){
            $data['p_id']=$_POST['p_id'];
            $data['c_id']=$_POST['c_id'];
            $data['a_name']=trim($_POST['a_name']);
			$data['location']=trim($_POST['location']);

            $flag = M("area")->where($data)->find();
            if (!$flag) {
                    $addInfo=M('area')->add($data);
                if($addInfo){
                    jump('添加县区信息成功！',U('department/area'));
                }else{
                    jump('添加县区信息失败！',U('department/addarea'));
                }
            }else{
                jump('该市区已存在此县区名称！',U('department/addarea'));
            }
        }else{
            $this->assign('province',D("province")->getProvinceName());
            $this->display();
        }
    }
 

    //编辑县区信息
    public function editarea()
    {
        $aId=$_GET['a_id'];
        if (IS_POST){
            $data['p_id']=$_POST['p_id'];
            $data['c_id']=$_POST['c_id'];
            $data['a_name']=trim($_POST['a_name']);
			$data['location']=trim($_POST['location']);
			$where_=" p_id ='".$data['p_id']."' and c_id='".$data['c_id']."' and a_name='".$data['a_name']."' and  a_id!='".$aId."' ";
            $info = M("area")->where($where_)->find();

            if (!$info) {
                    $flag=M('area')->where("a_id=".$aId)->save($data);
                if($flag){
                    jump('编辑县区信息成功！',U('department/area'));
                }else{
                    jump('您未做任何修改！',U('department/editarea',array('a_id' =>$aId)));
                }
            }else{
                jump('该市区已存在此县区名称！',U('department/editarea',array('a_id' =>$aId)));
            }
           
        }else{
            $this->assign('province',D("province")->getProvinceName());
            $areaInfo=M('area')->where("a_id=".$aId)->find();
            //获取响应的省份下面的城市：
            $p_id=$areaInfo['p_id'];
            $cityList=M('city')->where('p_id = '.$p_id)->field('ec_city.c_id,ec_city.c_name')->select();
            $this->assign('city1',$cityList);
            $this->assign('area',$areaInfo);
			//dump($areaInfo);die;
            $this->display();
        }
    }
    //根据选择的省份获取相对应的城市信息
    public function getCityName()
    {
        $pid=I('get.p_id');
        $result=M('city')->where('p_id = '.$pid)->select();
        $this->ajaxReturn(array("data"=>$result));
    }

    //删除县区信息
    public function delarea()
    {
        $aId=$_GET['a_id'];
        $delArea=M('area')->where('a_id ='.$aId)->delete();
		//删除该区县下属的乡镇信息
        M('villages')->where('a_id ='.$aId)->delete();
        if($delArea){
            jump('删除县区信息成功！',U('department/area'));
        }else{
            jump('删除县区信息失败！',U('department/area'));
        }
    }
    //getAreaName
    public function getAreaName()
    {
        $cid=I('get.c_id');
        $result=M('area')->where('c_id = '.$cid)->field('ec_area.a_id,ec_area.a_name,location')->select();
        $this->ajaxReturn(array("data"=>$result));
    }
   
}