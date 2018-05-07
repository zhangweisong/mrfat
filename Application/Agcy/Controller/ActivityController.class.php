<?php

namespace Agcy\Controller;

use Think\Controller;

class ActivityController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();

		if(ACTION_NAME=="shangjiaerweima" || ACTION_NAME=="yingxiaoguanli"){
			//
		}else{
			//判断登录
			$agcy_id = session("agcy_id");
			if ($agcy_id == "") {//未登录
				header("Location:" . U('login/login'));
				exit();
			}
		}
    }

    //空方法，防止报错
    public function _empty() {
        $this->index();
    }

    //促销活动页面
    public function cuxiaohuodong() {
        $this->display();
    }

    //添加团购/秒杀活动
    public function tianjiatuangou() {
        $agcy_id = session("agcy_id");
        $act_classify = I('get.act_classify');
        $count = M('Agcygoods')->where("agcy_id='" . $agcy_id . "' and agcy_goods_state=1 ")->count(); //商品上架的
        $Page = new \Think\Page($count, 6);
        $agcygoods = M('Agcygoods')->where("agcy_id='" . $agcy_id . "' and agcy_goods_state=1 ")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order("agcy_goods_id desc")
                ->select();
        foreach ($agcygoods as $k => $v) {
            $agcygoods[$k]['act_classifys'] = $act_classify;
            $agcygoods[$k]['image'] = explode('|', $agcygoods[$k]['agcy_goods_images']);
        }
        if (IS_AJAX) {
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('agcygoods' => $agcygoods), 'json');
            } else {
                $this->ajaxReturn(array('agcygoods' => array()), 'json');
            }
        } else {
            $this->assign('agcygoods', json_encode(array('agcygoods' => $agcygoods)));
            $this->assign('act_classify', $act_classify);
            $this->display();
        }
    }

    //查看团购/秒杀活动 2团购 1秒杀
    public function chakantuangouhuodong() {
        $agcy_id = session("agcy_id");
        $act_classify = I('get.act_classify');
        //获取条数
        $count = M('Activity')
                //条件团购还是秒杀 是否审核通过  是否是此经销商   活动未结束的  活动不审核 and act_state<3
                ->where("ec_activity.agcy_id=" . $agcy_id . " and ec_activity.act_classify='" . $act_classify . "'  ")
                ->join('left join ec_agcygoods on ec_agcygoods.agcy_goods_id=ec_activity.act_goods')
                ->count();
        $Page = new \Think\Page($count, 6);
        //获取内容
        $activity = M('Activity')
                //条件团购还是秒杀 是否审核通过  是否是此经销商   活动未结束的  and act_flag=3 and act_state<3
                ->where("ec_activity.agcy_id=" . $agcy_id . " and ec_activity.act_classify='" . $act_classify . "' ")
                ->join('left join ec_agcygoods on ec_agcygoods.agcy_goods_id=ec_activity.act_goods')
                ->order('ec_activity.act_id desc')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        //调整时间格式
        foreach ($activity as $k => $v) {
            //$activity[$k]['act_starttime']=date("Y-m-d H:i:s",$activity[$k]['act_starttime']);
            //$activity[$k]['act_endtime']=date("Y-m-d H:i:s",$activity[$k]['act_endtime']);
            $activity[$k]['image'] = explode('|', $activity[$k]['agcy_goods_images']);

            if ($v['act_starttime'] > time()) {
                $activity[$k]['act_state'] = 1;
            } elseif ($v['act_starttime'] < time() && $v['act_endtime'] > time()) {
                $activity[$k]['act_state'] = 2;
            } else {
                $activity[$k]['act_state'] = 3;
            }
        }
        if (IS_AJAX) {
            if ($Page->totalPages >= $p) {
                $this->ajaxReturn(array('activity' => $activity), 'json');
            } else {
                $this->ajaxReturn(array('activity' => array()), 'json');
            }
        } else {
            $this->assign('activity', json_encode(array('activity' => $activity)));
            $this->assign('act_classify', $act_classify);
            $this->display();
        }
    }

    //设置团购/秒杀信息
    public function shezhituangouxinx() {
        $agcy_id = session("agcy_id");
        $act_classify = I('get.act_classify'); //团购还是秒杀
        $agcy_goods_id = I('get.agcy_goods_id'); //商品id
        $this->assign('act_classify', $act_classify);
        $this->assign('agcy_goods_id', $agcy_goods_id);
        if (IS_AJAX) {
            $act_classifys = I("post.act_classify");
            $act_names = I("post.act_name");
            $act_name = filterEmoji($act_names, 2);
            $agcy_goods_ids = I("post.agcy_goods_id");
            $act_starttime = strtotime(I("post.getstart"));
            $act_endtime = strtotime(I("post.getend"));
            $act_guige = I("post.act_guige");
            $act_price = I("post.act_price");
            $act_count = I("post.count");
            //通过传来的规格key值查询价格
            $act_guige_key = I("post.act_guige_key");
            //查询价格
            $agcygoods = M('agcygoods')->find($agcy_goods_ids);
            //价格
            $price = explode(',', $agcygoods['agcy_goods_price']);
            $act_guige_price = $price[$act_guige_key];
            //如果所选的开始时间小于当前时间就直接是活动进行中
//            if ($act_starttime > time()) {
//                $act_state = 1; //预热中
//            } else {
//                $act_state = 2; //活动进行中
//            }
            if ($act_classifys == 2) {
                $flag = M('activity')->add(array(
                    'agcy_id' => $agcy_id,
                    'act_name' => $act_name,
                    'act_classify' => 2,
                    'act_guige' => $act_guige,
                    'act_goods' => $agcy_goods_ids,
                    'act_starttime' => $act_starttime,
                    'act_endtime' => $act_endtime,
//                    'act_state' => $act_state, 
                    'act_price' => $act_price,
                    'act_guige_price' => $act_guige_price,
                    'act_teamcount' => $act_count,
                    'act_flag' => 2,
                    'act_addtime' => time()
                ));
                $msg = "团购";
            } else {
                $flag = M('activity')->add(array(
                    'agcy_id' => $agcy_id,
                    'act_name' => $act_name,
                    'act_classify' => 1,
                    'act_guige' => $act_guige,
                    'act_goods' => $agcy_goods_ids,
                    'act_starttime' => $act_starttime,
                    'act_endtime' => $act_endtime,
//                    'act_state' => $act_state,
                    'act_price' => $act_price,
                    'act_guige_price' => $act_guige_price,
                    'act_seccount' => $act_count,
                    'act_flag' => 2,
                    'act_addtime' => time()
                ));
                $msg = "秒杀";
            }
            if ($flag) {
                $this->ajaxReturn(array('status' => 1, 'msg' => $msg), 'json');
            } else {
                $this->ajaxReturn(array('status' => 2, 'msg' => $msg), 'json');
            }
        } else {
            //查看此活动商品的规格
            $guige = M('Agcygoods')->where('agcy_goods_id=' . $agcy_goods_id)->field('agcy_goods_spec')->find();
            $guiges = explode(',', $guige['agcy_goods_spec']);
            $this->assign('guige', $guiges);
        }
        $this->display();
    }

    //结束活动
    public function jieshuhuodong() {
        $act_id = I('get.act_id');
        if ($act_id == "" || $act_id == "undefined") {
            $this->ajaxReturn(array('status' => 0, 'data' => 0, 'msg' => "系统参数有误"), 'jsonp');
        }
        M('activity')->where('act_id=' . $act_id)->save(array('act_starttime' => time(),'act_endtime' => time()));
        $this->ajaxReturn(array('status' => 1, 'data' => $data), 'jsonp');
    }

    //商家二维码 
    public function shangjiaerweima() {

        //引入WxpayAPI
        vendor('WxpayAPI.WxPayJsApiPay');
        $jsApi = new \JsApiPay();
        $signPackage = $jsApi->GetSignPackage();
        $this->assign('signPackage', $signPackage); //获取用户appid   ------------------------------页面后期需要改

        $agcy_id =I('get.agcy_id');
        //查询经销商图片
        $agcy_image = M('Agcy')->where('agcy_id=' . $agcy_id)->find();
        $image = explode('|', $agcy_image['agcy_face_images']);
        $this->assign('image', $image[0]);
        $this->assign('agcy_name', $agcy_image['agcy_name']);
        $this->assign('agcy_id', $agcy_id);
        $this->display();
    }

    //营销管理
    public function yingxiaoguanli() {
        $agcy_id = I('get.agcy_id');
        $this->assign('agcy_id', $agcy_id);
        $this->display();
    }

    //活动规则
    public function huodongguize() {
        $data = M('composition')->where('composition_id=4')->find();
        $composition_text = content_strip($data['composition_text']);
        $this->assign('composition_text', $composition_text);
        $this->display();
    }

}
