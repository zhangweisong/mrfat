<?php
namespace Admin\Model;
use Think\Model;

class ExpertModel extends Model {
		
    //专家职称显示 1显示，2隐藏
	public function sclassify_isables(){
        return array(
            '1'=>'启用',
            '2'=>'禁用',
        );
    }
    //专家文章类型snclassify_isables
    public function snclassify_isables(){
        return array(
            '1'=>'启用',
            '2'=>'禁用',
        );
    }
    //专家问答类型
    public function type_isables(){
        return array(
            '1'=>'启用',
            '2'=>'禁用',
        );
    }
    //专家状态  1 正常，2禁用 
	public function expert_states(){
        return array(
            '1'=>'启用',
            '2'=>'禁用',
        );
    }
    
    //认证状态  1已认证 0未认证
	public function expert_isapproves(){
        return array(
            '1'=>'<p style="color:red"> 未认证 </p>',
            '2'=>'<p style="color:green"> 已认证 </p>',
        );
    }
    
    //专家职称
    public function  expert_pessionals(){
        $savantclassify=M("savantclassify")
                        ->where('sclassify_isable=1')
                        ->field('sclassify_id,sclassify_name')
                        ->order('sclassify_sort asc,sclassify_id desc')
                        ->select();
        $return=array();
        foreach ($savantclassify as $key=>$value){
            $return[$value['sclassify_id']]=$value["sclassify_name"];
        }
        return  $return;
    }
    
    //提现申请状态，1申请，2通过,3驳回 
	public function pop_flags(){
        return array(
            '1'=>'申请',
            '2'=>'通过',
            '3'=>'驳回',
        );
    }
    //答题库 状态 1 未审核 2 已审核
    public function problembase_sataes(){
        return array(
            '1'=>'<p style="color:red"> 未审核 </p>',
            '2'=>'<p style="color:green"> 已审核 </p>',
        );
    }
    //专家问答分类
    public function  expert_types(){
        $expert_type=M("expert_type")
                    ->where('type_isable=1')
                    ->field('type_id,type_name')
                    ->order('type_sort asc,type_id desc')
                    ->select();
        $return=array();
        foreach ($expert_type as $key=>$value){
            $return[$value['type_id']]=$value["type_name"];
        }
        return  $return;
    }
    //专家文章分类
    public function  article_types(){
        $article_type=M("savantnewsclassify")
                    ->where('snclassify_isable=1')
                    ->field('snclassify_id,snclassify_name')
                    ->order('snclassify_sort asc,snclassify_id desc')
                    ->select();
        $return=array();
        foreach ($article_type as $key=>$value){
            $return[$value['snclassify_id']]=$value["snclassify_name"];
        }
        return  $return;
    }
    //农技直播状态
    public function nj_isable(){
        return array(
            '1'=>'启用',
            '2'=>'禁用',
        );
    }
}