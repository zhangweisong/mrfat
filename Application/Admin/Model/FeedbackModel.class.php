<?php
namespace Admin\Model;
use Think\Model;
class FeedbackModel extends Model{
	
	//意见反馈状态
	//回复状态：1未回复,2已回复
    public function state(){
        return array(
            '1'=>'未回复',
            '2'=>'已回复',			
        );
    }
    //反馈类型：1意见反馈；2记者举报
     public function feedback_type(){
        return array(
            '1'=>'意见反馈',
            '2'=>'记者举报',			
        );
    }
}