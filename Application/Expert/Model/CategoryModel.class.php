<?php
/**
 * Created by PhpStorm.
 * User: DangMengmeng
 * Date: 2018/3/12 0012
 * Time: 下午 2:02
 */
namespace Expert\Model;
use Think\Model;

class CategoryModel extends Model{
    //文章的一级分类名称
    public function get_article_ca_Name(){
        $info =  M('savantnewsclassify')->where('snclassify_isable = 1 ')->field('snclassify_id,snclassify_name')->order('snclassify_sort desc')->select();
        $return = array();
        foreach ($info as $key => $value) {
            $return[$value['snclassify_id']] = $value['snclassify_name'];
        }
        return  $return;
    }
}