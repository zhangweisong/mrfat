<?php
/**
 * Created by PhpStorm.
 * User: DangMengmeng
 * Date: 2018/3/7 0007
 * Time: 上午 9:58
 */
namespace Admin\Controller;
use Think\Controller;

class CountagcyController extends Controller{
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
        dd("no die");
    }

    //经销商统计
    public function countagency() {
        //经销商状态为开启
        $where="1 and agcy_state = 1 ";
        $info=I("info");
        if ($info){
            @extract($info);
            //区域
            if($p_id){
                $info['p_id']=urldecode(trim($info['p_id']));
                $where.="and province = '".$info['p_id']."' ";
                $cityList=M('city')->where('p_id = '.$p_id)->field('ec_city.c_id,ec_city.c_name')->select();
                $this->assign('city1',$cityList);
                $this->assign('p_id',urldecode($info['p_id']));
            }

            if($c_id){
                $info['c_id']=urldecode(trim($info['c_id']));
                $where.=" and city = '".$info['c_id']."' ";
                $areaList=M('area')->where('c_id = '.$c_id)->field('ec_area.a_id,ec_area.a_name')->select();
                $this->assign('area1',$areaList);
                $this->assign('c_id',urldecode($c_id));
            }

            if($area_id){
                $info['area_id']=urldecode(trim($info['area_id']));
                $where.=" and area = '".$info['area_id']."' ";
                $villagesList=M('villages')->where('a_id = '.$area_id)->field('ec_villages.v_id,ec_villages.v_name')->select();
                $this->assign('villages1',$villagesList);
                $this->assign('area_id',urldecode($info['area_id']));
            }
            //乡镇搜索
            if ($v_id) {
                $info['v_id']=urldecode(trim($info['v_id']));
                $where .= " and villages = " .$info['v_id'];
                $this->assign('v_id',urldecode($v_id));
            }
            if($starttime){$this->assign('starttime',$starttime);}
            if($endtime){$this->assign('endtime',$endtime);}
        }
        if(!$starttime){
            $starttime=date("Y-m-d",strtotime("-15 day"));$this->assign('starttime',$starttime);
        }
        if(!$endtime){
            $endtime=date("Y-m-d",SYS_TIME);$this->assign('endtime',$endtime);
        }
        $this->assign('title',"经销商统计".$starttime."至".$endtime);

        $daylist=$this->get_daylist($starttime,$endtime);
        $infolist=array();
        foreach($daylist as $k=>$v){
            $where_=$where;
            $where_.=" and agcy_addtime>=".strtotime($v." 00:00:00")." ";
            $where_.=" and agcy_addtime<=".strtotime($v." 23:59:59")." ";
            $infolist[$k]['nums'] = M("agcy")->where($where_)->count();
            $infolist[$k]['dayinfo'] =$v;
        }
        $province = M('province')->select();
        $this->assign('province',$province);
        $this->assign('infolist',$infolist);
        $this->display();
    }


    ///////////////////////////////////////////////////////////////////////
    //获取两个日期段内所有日期
    public function get_daylist($startdate,$enddate){
        $stimestamp = strtotime($startdate);
        $etimestamp = strtotime($enddate);
        // 计算日期段内有多少天
        $days = ($etimestamp-$stimestamp)/86400+1;
        // 保存每天日期
        $date = array();
        for($i=0; $i<$days; $i++){
            $date[] = date('Y-m-d', $stimestamp+(86400*$i));
        }
        return $date;
    }


    //根据选择的省份获取相对应的城市信息
    public function getCityName(){
        $pid=I('get.p_id');
        $result=M('city')->where('p_id = '.$pid)->select();
        $this->ajaxReturn(array("data"=>$result));
    }
    //getAreaName
    public function getAreaName(){
        $cid=I('get.c_id');
        $result=M('area')->where('c_id = '.$cid)->select();
        $this->ajaxReturn(array("data"=>$result));
    }
    //getVillageName
    public function getVillageName(){
        $aid=I('get.a_id');
        $result=M('villages')->where('a_id = '.$aid)->select();
        $this->ajaxReturn(array("data"=>$result));
    }
}