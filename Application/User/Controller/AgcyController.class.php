<?php

/**
 * 经销商
 */

namespace User\Controller;

use Think\Controller;
use Think\Exception;
class AgcyController extends Controller {

    function __construct() {
        //继承父类
        parent::__construct();
        $user_id = session("user_id");
        //判断登录 
        if ($user_id == "") {//未登录
            header("Location:" . U('index/index'));
            exit();
        }
    }

    public function _empty() {
        $this->index();
    }

    //经销商详情 
    public function index() {
        $user_id = session("user_id");
        $agcy_id = I('agcy_id');
        $this->assign("agcy_id", $agcy_id);
        //经销商详情
        $agcyinfo = M("agcy")->find($agcy_id);
        $this->assign("agcyinfo", $agcyinfo);
        //经销商秒杀活动列表
        $activitylist_miaosha = M("activity")
                //条件 活动未结束 时间未到
                ->where("ec_activity.act_classify=1 AND ec_activity.agcy_id=" . $agcy_id . " AND ec_activity.act_endtime>" . time())
                ->join("LEFT JOIN ec_agcygoods ON ec_agcygoods.agcy_goods_id=ec_activity.act_goods")
                ->select();
        foreach ($activitylist_miaosha AS $k => $v) {
            if ($v['act_starttime'] > time()) {
                $activitylist_miaosha[$k]['act_state'] = 1;
            } elseif ($v['act_starttime'] < time() && $v['act_endtime'] > time()) {
                $activitylist_miaosha[$k]['act_state'] = 2;
            } else {
                $activitylist_miaosha[$k]['act_state'] = 3;
            }
        } 
        $this->assign("activitylist_miaosha", $activitylist_miaosha);
        //经销商团购活动列表
        $activitylist_tuangou = M("activity")
                ->where("ec_activity.act_classify=2 AND ec_activity.agcy_id=" . $agcy_id . " AND ec_activity.act_endtime>" . time())
                ->join("LEFT JOIN ec_agcygoods ON ec_agcygoods.agcy_goods_id=ec_activity.act_goods")
                ->select();
        $this->assign("activitylist_tuangou", $activitylist_tuangou);
        //经销商商品列表  
        $where = "ec_agcygoods.agcy_goods_state=1 and ec_agcygoods.agcy_goods_states=2 and ec_agcygoods.agcy_id=" . $agcy_id;
        $condition = I("condition") ? I("condition") : "all";
        $order = I("order") ? I("order") : 1;
        if ($order == 2) {
            $desc = "DESC";
        } else {
            $desc = "ASC";
        }
        switch ($condition) {
            case "all":
                $sort = "ec_agcygoods.agcy_goods_addtime " . $desc;
                break;
            case "price":
                $sort = "ec_agcygoods.agcy_default_price " . $desc;
                break;
            case "sale":
                $sort = "ec_agcygoods.agcy_goods_salenum " . $desc;
                break;
            default:
                $sort = "";
                break;
        }
        $countAgcyGoods = M("agcygoods")
                ->where($where)
                ->count();
        $Page = new \Think\Page($countAgcyGoods, 6);
        $listAgcyGoods = M("agcygoods")
                ->limit($Page->firstRow . ', ' . $Page->listRows)
                ->where($where)
                ->order($sort)
                ->select();
        if (IS_AJAX) {
            if (count($listAgcyGoods) > 0) {
                $html = '';
                foreach ($listAgcyGoods AS $k => $v) {
                    $html .= '<li>
                            <a href="' . U('shopcar/xiangqing', array('agcy_goods_id' => $v['agcy_goods_id'])) . '">
                                <i class="img"><img src="' . explode('|', $v['agcy_goods_images'])[0] . '"/></i>
                                <div class="text">
                                    <h5>' . $v['agcy_goods_name'] . '</h5>
                                    <div class="jiage">
                                        <span>￥' . $v['agcy_default_price'] . '</span>
                                        <em>￥' . $v['agcy_default_price'] . '</em>
                                    </div>
                                    <div class="xiaoliang">
                                        <span>销量：' . $v['agcy_goods_salenum'] . '</span>
                                        <em></em>
                                    </div>
                                </div>
                            </a>
                        </li>';
                }
                $this->ajaxReturn($html);
            } else {
                $this->ajaxReturn(null);
            }
        } else {
            $this->assign("listAgcyGoods", $listAgcyGoods);
            $this->display();
        }
    }

//    //倒计时结束 活动状态改为进行中 
//    public function act_state_tostart() {
//        $act_id = I("act_id");
//        M("activity")->where("act_id=" . $act_id)->save(array("act_state" => 2));
//    }
//
//    //倒计时结束 活动状态改为结束 
//    public function act_state_toend() {
//        $act_id = I("act_id");
//        M("activity")->where("act_id=" . $act_id)->save(array("act_state" => 3));
//    }
    //秒杀详情
    public function miaosha_xq() {
        //秒杀活动
        $act_id = I("act_id");
        $miaosha = M("activity")
                //条件 活动未结束 时间未到
                ->join("LEFT JOIN ec_agcygoods ON ec_agcygoods.agcy_goods_id=ec_activity.act_goods")
                ->find($act_id);

        if ($miaosha['act_starttime'] > time()) {
            $miaosha['act_state'] = 1;
        } elseif ($miaosha['act_starttime'] < time() && $miaosha['act_endtime'] > time()) {
            $miaosha['act_state'] = 2;
        } else {
            $miaosha['act_state'] = 3;
        }
        $this->assign("miaosha", $miaosha);
        $this->display();
    }

    //团购详情
    public function tuangou_xq() {
        //秒杀活动
        $act_id = I("act_id");
        $miaosha = M("activity")
                //条件 活动未结束 时间未到 
                ->join("LEFT JOIN ec_agcygoods ON ec_agcygoods.agcy_goods_id=ec_activity.act_goods")
                ->find($act_id);
        if ($miaosha['act_starttime'] > time()) {
            $miaosha['act_state'] = 1;
        } elseif ($miaosha['act_starttime'] < time() && $miaosha['act_endtime'] > time()) {
            $miaosha['act_state'] = 2;
        } else {
            $miaosha['act_state'] = 3;
        }
        $this->assign("miaosha", $miaosha);
        $this->display();
    }

    //分类页面
    public function fenlei() {
        $agcy_id = I('agcy_id');
        //所有分类
        $fenlei = M("agcyclassify")->order("agcy_classify_sort ASC")->where("agcy_classify_fatherid=0")->select();
        foreach ($fenlei AS $k => $v) {
            $fenlei[$k]['children'] = M("agcyclassify")->where("agcy_classify_fatherid=" . $v['agcy_classify_id'])->order("agcy_classify_sort ASC")->select();
            foreach ($fenlei[$k]['children'] as $kk => $vv) {
                $fenlei[$k]['children'][$kk]['agcy_id'] = $agcy_id;
            }
        }
        $this->assign("fenlei", $fenlei);
        $this->display();
    }

    //商品列表
    public function goodslist() {
        //商品列表 搜索 
        $agcy_id = I('agcy_id');
        $this->assign("agcy_id", $agcy_id);
        $where = "ec_agcygoods.agcy_id=" . $agcy_id;
        $fenlei1 = I("fenlei1");
        if ($fenlei1) {
            $where .= " AND ec_agcygoods.agcy_classifyone=" . $fenlei1;
            $this->assign("fenlei1", $fenlei1);
        }
        $fenlei2 = I("fenlei2");
        if ($fenlei2) {
            $where .= " AND ec_agcygoods.agcy_classifytwo=" . $fenlei2;
            $this->assign("fenlei2", $fenlei2);
        }
        $keyword = trim(I("keyword"));
        if ($keyword) {
            $where .= " AND ec_agcygoods.agcy_goods_name LIKE '%" . $keyword . "%'";
            $this->assign("keyword", $keyword);
        }
        //经销商商品列表   
        $condition = I("condition") ? I("condition") : "all";
        $order = I("order") ? I("order") : 1;
        if ($order == 2) {
            $desc = "DESC";
        } else {
            $desc = "ASC";
        }
        switch ($condition) {
            case "all":
                $sort = "ec_agcygoods.agcy_goods_addtime " . $desc;
                break;
            case "price":
                $sort = "ec_agcygoods.agcy_default_price " . $desc;
                break;
            case "sale":
                $sort = "ec_agcygoods.agcy_goods_salenum " . $desc;
                break;
            default:
                $sort = "";
                break;
        }
        $countAgcyGoods = M("agcygoods")
                ->where($where)
                ->count();
        $Page = new \Think\Page($countAgcyGoods, 6);
        $listAgcyGoods = M("agcygoods")
                ->limit($Page->firstRow . ', ' . $Page->listRows)
                ->where($where)
                ->order($sort)
                ->select();
        if (IS_AJAX) {
            if (count($listAgcyGoods) > 0) {
                $html = '';
                foreach ($listAgcyGoods AS $k => $v) {
                    $html .= '<li>
                            <a href="' . U('shopcar/xiangqing', array('agcy_goods_id' => $v['agcy_goods_id'])) . '">
                                <i class="img"><img src="' . explode('|', $v['agcy_goods_images'])[0] . '"/></i>
                                <div class="text">
                                    <h5>' . $v['agcy_goods_name'] . '</h5>
                                    <div class="jiage">
                                        <span>￥' . $v['agcy_default_price'] . '</span>
                                        <em>￥' . $v['agcy_default_price'] . '</em>
                                    </div>
                                    <div class="xiaoliang">
                                        <span>销量：' . $v['agcy_goods_salenum'] . '</span>
                                        <em></em>
                                    </div>
                                </div>
                            </a>
                        </li>';
                }
                $this->ajaxReturn($html);
            } else {
                $this->ajaxReturn(null);
            }
        } else {
            $this->assign("listAgcyGoods", $listAgcyGoods);
            $this->display();
        }
    }

    //参加秒杀
    public function miaosha() {
        
        if (!IS_AJAX) {
            return false;
        }
        $user_id = session('user_id');
        $act_id = I("act_id");
        //1 判断活动是否过期（不是活动进行时间）
        //2 判断活动人数是否剩余
        $flag = $this->act_state($act_id);
        if ($flag['code'] == 0) {
            $this->ajaxReturn($flag);
        }
        //活动正常可以进行秒杀 
        $actinfo = M("activity")->find($act_id);
        
        M("activity")->where("act_id=" . $act_id)->setInc("act_ordercount");
        $temp = array(
                    "user_id" => $user_id,
                    "agcy_id" => $actinfo['agcy_id'],
                    "order_money" => $actinfo['act_price'],
                    "order_state" => 1,
                    "order_addtime" => time(),
                    "order_act_id" => $act_id,
                    "order_number" => time() . rand(10000, 99999),
                    "order_remarks"=>''
                ); 
        $order_id = M("orderinfo")->data($temp)->add();  
        M("orderlist")->add(array(
            "goods_id" => $actinfo['act_goods'],
            "goods_num" => 1,
            "goods_price" => $actinfo['act_price'],
            "order_id" => $order_id,
            "goods_spec" => $actinfo['act_guige'],
        ));
        //参加成功后给活动表 订单表数据
        //返回订单编号 
        $this->ajaxReturn(array("code" => $order_id, "msg" => "抢购成功，请立即支付！"));
    }

    //活动时间  状态是否正确
    private function act_state($act_id) {
        $actinfo = M("activity")->find($act_id);
        if ($actinfo['act_starttime'] > time()) {
            $code = 0;
            $msg = "活动尚未开始";
        } elseif ($actinfo['act_endtime'] < time()) {
            $code = 0;
            $msg = "活动已经结束";
        } elseif ($actinfo['act_seccount'] - $actinfo['act_ordercount'] <= 0) {
            $code = 0;
            $msg = "已经被抢完";
        } else {
            $code = 1;
            $msg = "正常";
        }
        return(array("code" => $code, "msg" => $msg));
    }

    //参加秒杀
    public function tuangou() {
        if (!IS_AJAX) {
            return false;
        }
        $user_id = session("user_id");
        $act_id = I("act_id");
        //1 判断活动是否过期（不是活动进行时间）
        //2 判断活动人数是否剩余
        $flag = $this->act_state2($act_id);
        if ($flag['code'] == 0) {
            $this->ajaxReturn($flag);
        }
        //活动正常可以进行团购
        $actinfo = M("activity")->find($act_id);
        M("activity")->where("act_id=" . $act_id)->setInc("act_ordercount");
        $order_id = M("orderinfo")->data(array(
                    "user_id" => $user_id,
                    "agcy_id" => $actinfo['agcy_id'],
                    "order_money" => $actinfo['act_price'],
                    "order_state" => 1,
                    "order_addtime" => time(),
                    "order_act_id" => $act_id,
                    "order_number" => time() . rand(10000, 99999),
                ))->add();
        M("orderlist")->add(array(
            "goods_id" => $actinfo['act_goods'],
            "goods_num" => 1,
            "goods_price" => $actinfo['act_price'],
            "order_id" => $order_id,
            "goods_spec" => $actinfo['act_guige'],
        ));
        //参加成功后给活动表 订单表数据
        //返回订单编号 
        $this->ajaxReturn(array("code" => $order_id, "msg" => "抢购成功，请立即支付！"));
    }

    //活动时间  状态是否正确
    private function act_state2($act_id) {
        $actinfo = M("activity")->find($act_id);
        if ($actinfo['act_starttime'] > time()) {
            $code = 0;
            $msg = "活动尚未开始";
        } elseif ($actinfo['act_endtime'] < time()) {
            $code = 0;
            $msg = "活动已经结束";
        } elseif ($actinfo['act_teamcount'] - $actinfo['act_ordercount'] <= 0) {
            $code = 0;
            $msg = "已经被抢完";
        } else {
            $code = 1;
            $msg = "正常";
        }
        return(array("code" => $code, "msg" => $msg));
    }

    //显示购物车数量
    public function carnum() {
        $user_id = session("user_id");
        $count = M("car")
                ->where("user_id=" . $user_id)
                ->sum("goods_num");
        echo $count;
    }

}
