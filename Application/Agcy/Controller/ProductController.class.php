<?php
/**
 * Created by PhpStorm.
 * User: DangMengmeng
 * Date: 2018/3/12 0012
 * Time: 上午 10:56
 */
namespace Agcy\Controller;
use Think\Controller;

class ProductController extends Controller{

    function __construct() {
        //继承父类
        parent::__construct();
        //判断登录
        $agcy_id=session("agcy_id");
        if($agcy_id==""){//未登录
            header("Location:".U('login/login'));
            exit();
        }
    }

    //空方法，防止报错
    public function _empty() {
        $this->index();
    }

    //商品管理列表
    public function productlist(){
        $agcy_id=session('agcy_id');
        $where='agcy_id =  '.$agcy_id;
        $condt=I('get.keywords');
        if($condt){
            $where.=" and agcy_goods_name like '%".$condt."%'";
            $this->assign('keywords',$condt);
        }
        $count = M('agcygoods')
            ->where($where)
            ->count();
        $this->assign('count',$count);
        $Page       = new \Think\Page($count,6);
        $productInfo=M('agcygoods')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('agcy_goods_id desc')
            ->select();
        foreach ($productInfo as $k =>$v){
            $productInfo[$k]['agcy_goods_imagess']=explode('|',$productInfo[$k]['agcy_goods_images']);
            $productInfo[$k]['goods_image']=$productInfo[$k]['agcy_goods_imagess'][0];
        }
        if(IS_AJAX){
            if($Page->totalPages>=$p){
                $this->ajaxReturn(array('product1'=>$productInfo),'json');
            }else{
                $this->ajaxReturn(array('product1'=>array()),'json');
            }
        }else{
            $this->assign('product',$productInfo);
        }
        $this->display();
    }

    //更改上下架状态
    public function shelfstatus(){
        $status=intval(I('get.status'));
        $goods_id=intval(I('get.goods_id'));
        if($status==1){ // 状态为上架，修改为下架
            $updateStatus1=M('agcygoods')->where(['agcy_goods_id'=>$goods_id])->save(['agcy_goods_state'=>2]);
        }
        if($status==2){ // 状态为上下架，修改为上架
            $updateStatus2=M('agcygoods')->where(['agcy_goods_id'=>$goods_id])->save(['agcy_goods_state'=>1]);
        }
        if($updateStatus1 || $updateStatus2){
            $this->ajaxReturn(array('status'=>1),'json');
        }else{
            $this->ajaxReturn(array('status'=>-1),'json');
        }
    }

    //商品分类列表
    public function productclassify(){
        $classList=M('agcyclassify')->where('agcy_classify_fatherid = 0 ')->order('agcy_classify_id desc')->select();
        foreach ($classList as $k=>$v){
            $classList[$k]['subclassinfo']=M("agcyclassify")->where('agcy_classify_fatherid ='.$v['agcy_classify_id'])->select();
        }
        $this->assign('classList',$classList);
        $this->assign('faName',D('agcygoods')->getGoodsClassName());
        $this->display();
    }


    /*
     * 一级分类
     * */


    //添加商品一级分类
    public function addproclass(){
        if(IS_AJAX){
            $data['agcy_classify_name']=I('get.class_name');
            $data['agcy_classify_fatherid']=0;
            $addInfo=M('agcyclassify')->add($data);
            if($addInfo){
                $this->ajaxReturn(array("data"=>1,'msg'=>'添加成功！'));
            }else{
                $this->ajaxReturn(array("data"=>-1,'msg'=>'添加失败！'));
            }
        }else{
            $this->display();
        }
    }


    //添加商品一级分类是否重名
    public function classexist(){
        $className=I('get.class_name');
        $isAble=M('agcyclassify')->where("agcy_classify_fatherid = 0 and agcy_classify_name = '".$className."'")->find();
        if($isAble){
            $this->ajaxReturn(array("data"=>1));
        }else{
            $this->ajaxReturn(array("data"=>-1));
        }
    }


    //编辑商品一级分类
    public function editproclass(){
        $agcyGoodsId=I('get.agcy_id');
        $goodsClass=M('agcyclassify')->where('agcy_classify_id = '.$agcyGoodsId)->find();
        $this->assign('goodsClass',$goodsClass);
        $this->assign('faName',D('agcygoods')->getGoodsClassName());
        $this->display();
    }


    //编辑商品一级分类处理
    public function editclass(){
        $classId=I('get.agcy_id');
        $className=I('get.class_name');
        //保存修改信息
        $saveInfo=M('agcyclassify')
            ->where('agcy_classify_id = '.$classId)
            ->save(array('agcy_classify_name' =>$className));
        if($saveInfo){
            $this->ajaxReturn(array("data"=>1,'msg'=>'修改成功！'));
        }else{
            $this->ajaxReturn(array("data"=>-1,'msg'=>'未做任何修改！'));
        }
    }
    //editexistclass
    public function editexistclass(){
        $classId=I('get.agcy_id');
        $className=I('get.class_name');
        $isExist=M('agcyclassify')->where("agcy_classify_id != ".$classId." and agcy_classify_name = '".$className."'")->find();
        if($isExist){
            $this->ajaxReturn(array("data"=>1));
        }else{
            $this->ajaxReturn(array("data"=>-1));
        }
    }


    /*
     * 二级分类
     * */

    //添加商品二级分类
    public function addprosubclass(){
        if(IS_AJAX){
            $data['agcy_classify_fatherid']=I('get.class_id');
            $data['agcy_classify_name']=I('get.class_name');
            $addSbuClass=M('agcyclassify')->add($data);
            if($addSbuClass){
                $this->ajaxReturn(array("data"=>1,'msg'=>'添加分类成功！'));
            }else{
                $this->ajaxReturn(array("data"=>-1,'msg'=>'添加分类失败！'));
            }
        }
        $this->assign('faName',D('agcygoods')->getGoodsClassName());
        $this->display();
    }


    //添加商品二级分类是否重名
    public function subclassexist(){
        $subClassName=I('get.class_name');
        $classId=I('get.class_id');
        $isAble=M('agcyclassify')->where("agcy_classify_fatherid = ".$classId." and agcy_classify_name = '".$subClassName."'")->find();
        if($isAble){
            $this->ajaxReturn(array("data"=>1));
        }else{
            $this->ajaxReturn(array("data"=>-1));
        }
    }

    //编辑商品二级分类 editprosubclass
    public function editprosubclass(){
        $classId=I('get.agcy_id');  //二级分类的id
        if(IS_AJAX){
            $data['agcy_classify_name']=I('get.class_name');    //二级分类修改后的名称
            $addSbuClass=M('agcyclassify')->where('agcy_classify_id = '.$classId)->save($data);
            if($addSbuClass){
                $this->ajaxReturn(array("data"=>1,'msg'=>'编辑分类成功！'));
            }else{
                $this->ajaxReturn(array("data"=>-1,'msg'=>'未做任何修改！'));
            }
        }
        $classInfo=M('agcyclassify')->find($classId);
        $this->assign('faName',D('agcygoods')->getGoodsClassName());
        $this->assign('classInfo',$classInfo);
        $this->display();
    }
    //编辑二级分类名称去重；
    public function editexistsubclass(){
        $classId=I('get.agcy_id');  //二级分类的id
        $subclassname=I('get.class_name');    //二级分类修改后的名称
        $isAble=M('agcyclassify')
            ->where("agcy_classify_name = '".$subclassname."' and agcy_classify_id !=".$classId)
            ->find();
        if($isAble){
            $this->ajaxReturn(array("data"=>1));
        }else{
            $this->ajaxReturn(array("data"=>-1));
        }
    }

    //添加商品
    public function addproduct(){
        if(IS_AJAX){
            $data['agcy_goods_name']=I('post.agcy_goods_name');
            $data['agcy_classifyone']=I('post.class_id');
            $data['agcy_classifytwo']=I('post.subclass_id');
            $data['agcy_goods_spec']=rtrim(I('post.specss'),",");
            $data['agcy_goods_price']=rtrim(I('post.pricess'),",");
            $data['agcy_goods_repertory']=rtrim(I('post.stockss'),",");
            $data['agcy_goods_remark']=I('post.contentsss');
            $data['agcy_goods_images']=I('post.images');
            $data['agcy_one']=I('post.agcy_one');
            $data['agcy_tow']=I('post.agcy_tow');
            $data['agcy_three']=I('post.agcy_three');
            $data['agcy_goods_addtime']=time();
            $data['agcy_goods_state']=1;
            $data['agcy_goods_states']=1;
            $data['agcy_id']=session("agcy_id");
            $data['agcy_goods_yes']=1;
            //默认第一个规格的价格
            $price=explode(',',I('post.pricess'));
            $data['agcy_default_price']=$price[0];
            $addInfo=M('agcygoods')->add($data);
            if($addInfo){
                $this->ajaxReturn(array("data"=>1,'msg'=>'添加商品成功！'));
            }else{
                $this->ajaxReturn(array("data"=>-1,'msg'=>'添加商品失败！'));
            }
        }
        $this->assign('faName',D('agcygoods')->getGoodsClassName());
        $this->display();
    }


    //编辑经销商自己的商品
    public function editproduct(){
        if(IS_AJAX){
            $agcy_goods_id=intval(I('post.agcy_goods_id'));
            $data['agcy_goods_name']=I('post.agcy_goods_name');
            $data['agcy_classifyone']=I('post.class_id');
            $data['agcy_classifytwo']=I('post.subclass_id');
            $data['agcy_goods_spec']=rtrim(I('post.specss'),",");
            $data['agcy_goods_price']=rtrim(I('post.pricess'),",");
            $data['agcy_goods_repertory']=rtrim(I('post.stockss'),",");
            $data['agcy_goods_remark']=I('post.contentsss');
            $data['agcy_goods_images']=I('post.images');
            $data['agcy_one']=I('post.agcy_one');
            $data['agcy_tow']=I('post.agcy_tow');
            $data['agcy_three']=I('post.agcy_three');
            $data['agcy_goods_addtime']=time();
            $data['agcy_goods_state']=1;
            $data['agcy_goods_states']=1;
            $data['agcy_id']=session("agcy_id");
            $data['agcy_goods_yes']=1;
            //默认第一个规格的价格
            $price=explode(',',I('post.pricess'));
            $data['agcy_default_price']=$price[0];
            $addInfo=M('agcygoods')->where('agcy_goods_id = '.$agcy_goods_id)->save($data);
            if($addInfo){
                $this->ajaxReturn(array("data"=>1,'msg'=>'修改商品成功！'));
            }else{
                $this->ajaxReturn(array("data"=>-1,'msg'=>'修改商品失败！'));
            }
        }else{
            $agcy_goods_id=intval(I('get.agcy_goods_id'));
            $goodsInfo=M('agcygoods')->find($agcy_goods_id);
            $goodsInfo['agcy_goods_remark']=htmlspecialchars_decode($goodsInfo['agcy_goods_remark']); 
            $agcy_goods_spec = explode(',',$goodsInfo['agcy_goods_spec']);
            $agcy_goods_price = explode(',',$goodsInfo['agcy_goods_price']);
            $agcy_goods_repertory = explode(',',$goodsInfo['agcy_goods_repertory']); 
            //规格，价格，库存
            $row=[];
            for($i=0;$i<sizeof($agcy_goods_spec);$i++){
                $row[$i]['spec'] = $agcy_goods_spec[$i];
                $row[$i]['price'] = $agcy_goods_price[$i];
                $row[$i]['repertory'] = $agcy_goods_repertory[$i];
            }
            $goodsInfo['details']=$row;
            $classa=$goodsInfo['agcy_classifyone'];
            if($classa){
                $subclass=M('agcyclassify')->where(' agcy_classify_fatherid = '.$classa)->field('agcy_classify_id,agcy_classify_name')->select();
                $this->assign('subclass',$subclass);
            }
            $this->assign('faName',D('agcygoods')->getGoodsClassName());
            $this->assign('goods',$goodsInfo);
        }
        $this->display();
    }

    //编辑经销商代理的商品
    public function editagcypro(){
        if(IS_AJAX){
            $agcy_goods_id=intval(I('post.agcy_goods_id'));
            $data['agcy_classifyone']=I('post.class_id');
            $data['agcy_classifytwo']=I('post.subclass_id');
            $data['agcy_goods_remark']=I('post.contentsss');
            $data['agcy_goods_repertory']=I('post.stock');
            $data['agcy_goods_images']=I('post.images');
            $data['agcy_one']=I('post.agcy_one');
            $data['agcy_tow']=I('post.agcy_tow');
            $data['agcy_three']=I('post.agcy_three');
            $data['agcy_goods_state']=1;
            $data['agcy_goods_states']=1;
            $data['agcy_id']=session("agcy_id");
            $data['agcy_goods_yes']=2;
            $addInfo=M('agcygoods')->where('agcy_goods_id = '.$agcy_goods_id)->save($data);
            if($addInfo){
                $this->ajaxReturn(array("data"=>1,'msg'=>'修改商品成功！'));
            }else{
                $this->ajaxReturn(array("data"=>-1,'msg'=>'修改商品失败！'));
            }
        }else{
            $agcy_goods_id=intval(I('get.agcy_goods_id'));
            $goodsInfo=M('agcygoods')->find($agcy_goods_id); 
            $classa=$goodsInfo['agcy_classifyone'];
            $goodsInfo['agcy_goods_remark']=htmlspecialchars_decode($goodsInfo['agcy_goods_remark']); 
            if($classa){
                $subclass=M('agcyclassify')->where(' agcy_classify_fatherid = '.$classa)->field('agcy_classify_id,agcy_classify_name')->select();
                $this->assign('subclass',$subclass);
            }
            $this->assign('faName',D('agcygoods')->getGoodsClassName());
            $this->assign('goods',$goodsInfo);
        }
        $this->display();
    }

    //getsubClassName
    public function getsubClassName(){
        $class_id=I('get.class_id');
        $subclassInfo=M('agcyclassify')->where('agcy_classify_fatherid = '.$class_id)->field('agcy_classify_id,agcy_classify_name')->select();
        $this->ajaxReturn(array("data"=>$subclassInfo));
    }
}