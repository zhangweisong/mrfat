<?php

namespace Admin\Controller;

use Think\Controller;

//系统主架构控制器
/*
 * controller 控制器
 * action 方法
 * return true/false
 */
class JifenController extends Controller {

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
	
	public function home_success($msg, $url = "", $wait = 3) {
        $this->assign('msg', $msg);
        $this->assign('url', $url ? $url : U("index/index"));
        $this->assign('wait', $wait);
        $this->display('user/home_success');
        exit();
    }

    //公共error
    public function home_error($msg, $url = "", $wait = 3) {
        $this->assign('msg', $msg);
        $this->assign('url', $url ? $url : U("index/index"));
        $this->assign('wait', $wait);
        $this->display('user/home_error');
        exit();
    }
	

    //积分分类列表
    public function index() {
        $father_id = I("get.father_id") ? I("get.father_id") : 0; 
        $this->assign("father_id", $father_id);
        $where = "1";
        $where .= " AND jifen_classify_fatherid=" . $father_id;
        $info = I("info");
		if ($info) {// 搜索条件  
            @extract($info);
            if ($classify_name_s) {
                $classify_name_s = urldecode(trim($info['classify_name_s']));
                $where .= " and jifen_classify_name LIKE '%" . $classify_name_s . "%'";
                $this->assign('classify_name_s', $classify_name_s);
            }
            if ($father_id_s) {
                $where .= " and jifen_classify_fatherid='" . $father_id_s . "'";
                $this->assign('father_id_s', $father_id_s);
            }
			//状态
			if ($jifen_classify_isable) {
			$where .= " and jifen_classify_isable='" . $jifen_classify_isable . "'";
			$this->assign('jifen_classify_isable', $jifen_classify_isable);
            }
        }
        $model = M('jifenclassify');
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
                ->order("jifen_classify_id DESC")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        if ($father_id == 0) {
            //$father_name = "顶级分类";
            foreach ($listinfo AS $k => $v) {
                $listinfo[$k]['child_count'] = M("jifenclassify")->where("jifen_classify_fatherid=" . $v["jifen_classify_id"]." and jifen_classify_isable=1  " )
				->count();
            }
        } else {
            $father = $model->find($father_id);
            $father_name = $father['jifen_classify_name'];
            foreach ($listinfo AS $k => $v) {
                $listinfo[$k]['child_count'] = M("Jifengoods")->where("jifen_classify_twoid=" . $v["jifen_classify_id"]." and jifen_goods_isable=1 ")->count(); 
            }
        } 
        foreach ($listinfo AS $k => $v) {
            $listinfo[$k]['jifen_classify_remarks'] = str_cut($v['jifen_classify_remarks'], 20);
            $listinfo[$k]['remarks'] =$v['jifen_classify_remarks'];
        }
        $father = M("jifenclassify")->where("jifen_classify_fatherid=0")->select();
        $this->assign('father', $father);
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow);
		$this->assign('isable', D('jifenclassify')->get_isable()); 
        $this->assign('father_name', $father_name);
        $this->display();
    }
	
	//添加积分分类
    public function add() {
        $father_id = I("get.father_id") ? I("get.father_id") : 0;
        $this->assign('father_id', $father_id);
        if (IS_POST) {
            $data = I("info"); 
            $flag = M("Jifenclassify")->add($data);
            if ($flag) {
                jump('添加积分分类成功！', U('jifen/index'));
            } else {
                jump('添加积分分类失败！', U('jifen/index'));
            }
            //
        } else {
            $where = "jifen_classify_fatherid=0";
            $father = M("Jifenclassify")->where($where)->select();
            $this->assign('father', $father);
            $this->display();
        }
    }

	//判断一级分类的存在性
	public function names(){
		$classify_name = urldecode(trim(I("classify_name")));
		$count = M("Jifenclassify")->where("jifen_classify_name ='".$classify_name."'")->count(); 
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
    //编辑积分分类
    public function edit() {
        $classify_id = I("get.classify_id");
        $classify = M("Jifenclassify")->find($classify_id);
        if (IS_POST) {
            $data = I("info");
            M("Jifenclassify")->where("jifen_classify_id=" . $classify_id)->save($data);
            jump('编辑积分分类成功！', U('jifen/index', array('jifen_father_id' => $classify['jifen_classify_fatherid'])));
            //
        } else {
            $where = "jifen_classify_fatherid=0";
            $this->assign('classify', $classify);
			$this->assign('classify_id', $classify_id); 
            $father = M("Jifenclassify")->where($where)->select();
            $this->assign('father', $father);
            $this->display();
        }
    }
	
	//判断一级分类的存在性修改
	public function name_es(){
		$classify_name = urldecode(trim(I("classify_name")));
		$classify_id = I("classify_id");  
		$count = M("Jifenclassify")->where("jifen_classify_id!='".$classify_id."' and jifen_classify_name ='".$classify_name."'")
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

    //删除商品分类
    public function delete() {
        //删除的时候首先判断，如果是顶级分类，检查下面是否有次级分类
        //如果是次级分类，检查下面有没有图书 
        $classify_id = I("classify_id");
        $classify = M("Jifenclassify")->find($classify_id);
        if ($classify['jifen_classify_fatherid'] == 0) {
            $count = M("Jifenclassify")->where("jifen_classify_fatherid=" . $classify_id)->count();
            if ($count > 0) {
                jump('底层有分类，不能删除！', U('jifen/index'));
            } else {
                M("Jifenclassify")->delete($classify_id);
                jump('积分分类删除成功', U('jifen/index'));
            }
        } else {
            $count = M("Jifengoods")->where("jifen_classify_oneid='" . $classify_id."' and jifen_goods_isable=1")->count();
            if ($count > 0) {
                jump('该类目下有商品，不能删除！', U('jifen/index', array('father_id' => $classify['jifen_classify_oneid'])));
            } else {
                M("Jifenclassify")->delete($classify_id);
                jump('积分分类删除成功', U('jifen/index', array('father_id' => $classify['jifen_classify_oneid'])));
            }
        }
    }
	
	//积分商品列表
    public function jifen_goods() {
        $where = "jifen_goods_isable=1 ";
        $info = I("info");
        if ($info) {// 搜索条件
            @extract($info);
            if ($goods_name_s) { 
                $where .= " and ec_jifengoods.jifen_goods_name LIKE '%" . $goods_name_s . "%'";
                $goods_name = urldecode(trim($info['goods_name_s']));
                $this->assign('goods_name', $goods_name);
            }
            if ($yiji_s) {
                $where .= " and ec_jifengoods.jifen_classify_oneid=" . $yiji_s;
                $erji = M("jifenclassify")->where("jifen_classify_fatherid=" . $yiji_s)->select();
                $this->assign('erji', $erji);
                $this->assign('yiji_s', $yiji_s);
            }
            if ($erji_s) {
                $where .= " and ec_jifengoods.jifen_classify_twoid='" . $erji_s . "'";
                $this->assign('erji_s', $erji_s);
            }
        }

        $model = M('Jifengoods');
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
					->field("ec_jifengoods.*,yiji.jifen_classify_name AS yiji_name,yiji.jifen_classify_isable as yiji_able,erji.jifen_classify_isable as erji_able,erji.jifen_classify_name AS erji_name")
					->join("LEFT JOIN ec_jifenclassify AS yiji ON yiji.jifen_classify_id = ec_jifengoods.jifen_classify_oneid")
					->join("LEFT JOIN ec_jifenclassify AS erji ON erji.jifen_classify_id = ec_jifengoods.jifen_classify_twoid")
					->where($where)//两级分类都必须是显示状态
					->order("ec_jifengoods.jifen_goods_id DESC")
					->limit($Page->firstRow . ',' . $Page->listRows)
					->select();
        foreach ($listinfo AS $k => $v) {
            $listinfo[$k]['cover'] = explode('|', $v['jifen_goods_image'])[0];
            $listinfo[$k]['jifen_goods_remark'] = str_cut($v['jifen_goods_remark'], 20);
        }
        $this->assign('listinfo', $listinfo);
        $this->assign('pageshow', $pageshow);
        //一级分类
        $yiji = M("Jifenclassify")->where("jifen_classify_fatherid=0 and jifen_classify_isable=1")->select();
        $this->assign('yiji', $yiji);
        $this->display();
    }

	function get_erji() {
        $yiji_id = I("yiji_id");
        $where = "jifen_classify_fatherid='".$yiji_id."' and jifen_classify_isable=1 ";
        $erji = M("Jifenclassify")->where($where)->select();
        $this->ajaxReturn($erji);
    }
	
	//添加商品
    public function add_goods() {
        if (IS_POST) {
            $data = I("info");
			$data['jifen_goods_addtime']=time();
            $flag = M("Jifengoods")->add($data);
            if ($flag) {
                jump('添加商品成功！', U('jifen/jifen_goods'));
            } else {
                jump('添加商品失败！', U('jifen/jifen_goods'));
            }
           
        } else {
            $yiji = M("jifenclassify")->where("jifen_classify_fatherid=0 and jifen_classify_isable=1 ")->select();//分类显示的状态
            $this->assign('yiji', $yiji);
            $this->display();
        }
    }

	
    //编辑商品
    public function edit_goods() {
        $goods_id = I("get.goods_id");
        if (IS_POST) {
            $data = I("info");
            M("Jifengoods")->where("jifen_goods_id=" . $goods_id)->save($data);
            jump('编辑商品成功！', U('jifen/jifen_goods'));
        } else {
            $goods = M("Jifengoods")->find($goods_id);
			//dump($goods);die;
            $goods_images = explode('|', $goods['jifen_goods_image']);
            $yiji = M("Jifenclassify")->where("jifen_classify_fatherid=0 and jifen_classify_isable=1 ")->select();
            $erji = M("Jifenclassify")->where("jifen_classify_fatherid=" . $goods['jifen_classify_oneid'])->select();
            $this->assign('yiji', $yiji);
            $this->assign('goods_id', $goods_id);
            $this->assign('goods_images', $goods_images);
            $this->assign('erji', $erji);
            $this->assign('goods', $goods);
            $this->display();
        }
    }

    //删除商品
    public function delete_goods() {
        //删除的时候首先判断，如果是顶级分类，检查下面是否有次级分类
        //如果是次级分类，检查下面有没有图书 
        $goods_id = I("goods_id");
		$rel = M('jifenorder')->where('jifen_order_state!=2 and jifen_goods_id='.$goods_id)->count();
		if($rel){
			$flag = M("jifengoods")->where("jifen_goods_id=" . $goods_id)->save(array("jifen_goods_isable" => 2));
			if ($flag) {
				jump('删除商品成功！', U('jifen/jifen_goods'));
			} else {
				jump('删除商品失败！', U('jifen/jifen_goods'));
			}	
		}else{
			jump('该商品有订单未完成', U('jifen/orderlist'));
		}
        
    }
	
	// 商品详情
	public function commoditydetails(){
		 $goods_id = I("get.jifen_goods_id");  
		 $data = M("jifengoods")->where("jifen_goods_id=" . $goods_id)->find();
		 $data['jifen_goods_image'] = explode('|',$data['jifen_goods_image']);
		 $yiji= M("jifenclassify")->where("jifen_classify_id=".$data['jifen_classify_oneid'])->find();
		 $erji = M("jifenclassify")->where("jifen_classify_id=".$data['jifen_classify_twoid'])->find();
		 $data['jifen_classify_oneid']=$yiji['jifen_classify_name'];
		 $data['jifen_classify_twoid']=$erji['jifen_classify_name'];
		 $this->assign('data',$data);
		 $this->display('info');
	}
	//判断积分商品存在性
	public function names_goods(){
		$yiji =I("yiji");
		$erji =I("erji");
		$goods_name = urldecode(trim(I("goods_name")));
		$count = M("jifengoods")->where("jifen_classify_oneid='".$yiji."' and jifen_classify_twoid='".$erji."' and jifen_goods_name ='".$goods_name."'")->count(); 
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
	public function name_es_goods(){
		$goods_name = urldecode(trim(I("goods_name")));
		$yiji = I("yiji");  
		$erji = I("erji"); 
		$goods_id = I("goods_id");  		
		$count = M("jifengoods")->where("jifen_goods_id !='".$goods_id."' and jifen_classify_oneid='".$yiji."' and jifen_classify_twoid='".$erji."' and jifen_goods_name ='".$goods_name."'")->count(); 
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
	//订单列表
    public function orderlist(){
        $where=" 1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			//经销商 经销商电话 订单号
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword'])); 
                $where .= " and (
                		ec_agcy.agcy_name like '%" . $info['keyword'] . "%' 
                		or ec_agcy.agcy_tel like '%" . $info['keyword'] . "%' 
						or ec_jifenorder.jifen_order_number like '%" . $info['keyword'] . "%' 
                	)";
                $this->assign('keyword',urldecode($info['keyword']));
			}
			//订单状态
			if($jifen_order_state){ 
				$info['jifen_order_state']=urldecode(trim($info['jifen_order_state']));
                $where.="and ec_jifenorder.jifen_order_state ='".$info['jifen_order_state']."' ";  
                $this->assign('jifen_order_state',urldecode($info['jifen_order_state'])); 
			}
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_jifenorder.jifen_order_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_jifenorder.jifen_order_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
			
		}
		// 查询满足要求的总记录数
		$count      = M('Jifenorder') 
					->join("left join ec_agcy on ec_agcy.agcy_id=ec_jifenorder.jifen_agcy_id")
					->join("left join ec_jifengoods on ec_jifengoods.jifen_goods_id=ec_jifenorder.jifen_goods_id")
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
		$orderinfo = M('Jifenorder')
			->where($where)
			->join("left join ec_agcy on ec_agcy.agcy_id=ec_jifenorder.jifen_agcy_id")
			->join("left join ec_jifengoods on ec_jifengoods.jifen_goods_id=ec_jifenorder.jifen_goods_id")
			->order('ec_jifenorder.jifen_order_id desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->select();
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('state',D('Jifenclassify')->get_state());
		$this->assign('pageshow',$pageshow);
		$this->display();
    }
	
    //查看详情
    public function orderinfo(){
    	$order_id = I("order_id");
    	//订单详情
    		$orderinfo = M('Jifenorder')
					->where("ec_jifenorder.jifen_order_id=".$order_id)
					->join("left join ec_agcy on ec_agcy.agcy_id=ec_jifenorder.jifen_agcy_id")
					->join("left join ec_jifengoods on ec_jifengoods.jifen_goods_id=ec_jifenorder.jifen_goods_id")
					->order('ec_jifenorder.jifen_order_id desc')
					->find();
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('state',D('Jifenclassify')->get_state());
    	$this->display();
    } 
	
	
	//发货
	public function fahuo() {
		$jifen_order_id = I("get.jifen_order_id"); 
		$res = M("Jifenorder")->where("jifen_order_id='".$jifen_order_id."'")->find(); 
		if (IS_POST) {
            $data = I("info");
			$data['jifen_order_state']=3; 
			$data['jifen_order_sendtime']=time(); 
            $flag = M("Jifenorder")->where("jifen_order_id='".$jifen_order_id."'")->save($data); 
            if ($flag) {
				//积分变动
				M('jifenchange')->add(array('jifen_agcy_id'=>$res['jifen_agcy_id'],'jifen_change_type'=>2,'jifen_goods_point'=>$res['jifen_goods_price'],'jifen_change_addtime'=>time(),'jifen_change_flag'=>3,'jifen_state'=>1,'jifen_goods'=>$res['jifen_goods_id']));
                //增加消息通知
				$message= new \Admin\Model\MessageModel();
				$message->getmessage_($res['jifen_agcy_id'],2,'积分订单发货通知',"积分订单号为".($res['jifen_order_number'])."的商品已发货");
				$this->home_success('该订单发货成功！', U('jifen/orderlist'), 3);
            } else { 
				$this->home_error('该订单发货失败！', U('jifen/orderlist'), 3);
            } 
        } else {  
			$this->assign('res', $res); 
            $this->display();
        }  
	}
	
	
	
	
	//ajax处理状态
	public function ajaxable_on(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "jifen_classify_isable"://on
				M("Jifenclassify")->where('jifen_classify_id='.$id)->save(array("jifen_classify_isable"=>1));
				break;
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	
	public function ajaxable_off(){
		$id=I("get.id");//ID
		$field=I("get.field");//字段，用作下面代码处理
		switch($field){
			case "jifen_classify_isable"://off
				M("Jifenclassify")->where('jifen_classify_id='.$id)->save(array("jifen_classify_isable"=>2));
				break;
		}
		$this->ajaxReturn(array("flag"=>"1"));
	}
	
	
	//积分变动
    public function jifenchange(){
        $where=" 1 ";
		$info=I("info");
		if ($info){
			@extract($info);
			//经销商 经销商电话 订单号
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword'])); 
                $where .= " and (
                		ec_agcy.agcy_name like '%" . $info['keyword'] . "%' 
                		or ec_agcy.agcy_tel like '%" . $info['keyword'] . "%' 
                	)";
                $this->assign('keyword',urldecode($info['keyword']));
			}
			//兑换状态
			if($jifen_change_flag){ 
				$info['jifen_change_flag']=urldecode(trim($info['jifen_change_flag']));
                $where.="and ec_jifenchange.jifen_change_flag ='".$info['jifen_change_flag']."' ";  
                $this->assign('jifen_change_flag',urldecode($info['jifen_change_flag'])); 
			}
			//积分方式
			if($jifen_state){ 
				$info['jifen_state']=urldecode(trim($info['jifen_state']));
                $where.="and ec_jifenchange.jifen_state ='".$info['jifen_state']."' ";  
                $this->assign('jifen_state',urldecode($info['jifen_state'])); 
			}
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_jifenchange.jifen_change_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_jifenchange.jifen_change_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
			
		}
		// 查询满足要求的总记录数
		$count      = M('Jifenchange') 
					->join("left join ec_agcy on ec_agcy.agcy_id=ec_jifenchange.jifen_agcy_id")
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
		$orderinfo = M('Jifenchange')
			->where($where)
			->join("left join ec_agcy on ec_agcy.agcy_id=ec_jifenchange.jifen_agcy_id")
			->order('ec_jifenchange.jifen_change_addtime desc ')
			->limit($Page->firstRow.','.$Page->listRows)
			->select();
		$this->assign('orderinfo',$orderinfo); 
		$this->assign('flag',D('Jifenclassify')->get_flag());
		$this->assign('flags',D('Jifenclassify')->get_flags());
		$this->assign('pageshow',$pageshow);
		$this->display();
    }
	//积分订单统计
    public function jifenpop() { 
		$where=" ec_jifenorder.jifen_order_state=3 ";
		$info=I("info");
		if ($info){
			@extract($info);
			if($keyword){
				$info['keyword']=urldecode(trim($info['keyword'])); 
                $where .= " and  ec_agcy.agcy_name like '%" . $info['keyword'] . "%' ";
                $this->assign('keyword',urldecode($info['keyword']));
			}  
			if ($starttime) {
                $info['starttime'] = urldecode(trim($info['starttime']));
                $where .= " and ec_jifenorder.jifen_order_addtime >=" . strtotime($info['starttime'] . "00:00:00") . " ";
                $this->assign('starttime', $info['starttime']);
            }
            if ($endtime) {
                $info['endtime'] = urldecode(trim($info['endtime']));
                $where .= " and ec_jifenorder.jifen_order_addtime <=" . strtotime($info['endtime'] . "23:59:59") . " ";
                $this->assign('endtime', $info['endtime']);
            }
		}
		if(!$starttime){
			$starttime=date("Y-m-d",strtotime("-15 day"));
			$this->assign('starttime',$starttime);
		}
		if(!$endtime){
			$endtime=date("Y-m-d",SYS_TIME);
			$this->assign('endtime',$endtime);
		}
		$daylist=$this->get_daylist($starttime,$endtime); 
		$infolist=array();
		foreach($daylist as $k=>$v){
			$where_=$where;
			$where_.=" and ec_jifenorder.jifen_order_addtime>=".strtotime($v." 00:00:00")." ";
			$where_.=" and ec_jifenorder.jifen_order_addtime<=".strtotime($v." 23:59:59")." ";
			$infolist[$k]['pop_fact'] =  M('Jifenorder')
										->join(' left join ec_agcy on ec_agcy.agcy_id=ec_jifenorder.jifen_agcy_id')
										->where($where_) 
										->sum("jifen_goods_price"); 
			$infolist[$k]['count'] =  M('Jifenorder')
										->where($where_) 
										->count(); 										
			$infolist[$k]['dayinfo'] =$v;
		} 
		$this->assign('infolist',$infolist);
        $this->display();
	}
	
	
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

}
