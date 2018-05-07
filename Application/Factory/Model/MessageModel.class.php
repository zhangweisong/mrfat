<?php
namespace Factory\Model;
use Think\Model;
class MessageModel extends Model
{
    //消息状态
	public function msg_flags(){
        return array(
            '1'=>'未读',
            '2'=>'已读',
        );
    }
}