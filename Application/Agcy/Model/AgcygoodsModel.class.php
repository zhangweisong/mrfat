<?php
/**
 * Created by PhpStorm.
 * User: DangMengmeng
 * Date: 2018/3/12 0012
 * Time: 下午 2:02
 */
namespace Agcy\Model;
use Think\Model;

class AgcygoodsModel extends Model{
    //经销商商品的一级分类名称
    public function getGoodsClassName(){
        $info =  M('agcyclassify')->where('agcy_classify_fatherid = 0 ')->field('agcy_classify_id,agcy_classify_name')->select();
        $return = array();
        foreach ($info as $key => $value) {
            $return[$value['agcy_classify_id']] = $value['agcy_classify_name'];
        }
        return  $return;
    }
}