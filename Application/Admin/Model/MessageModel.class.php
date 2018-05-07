<?php

namespace Admin\Model;

use Think\Model;

class MessageModel extends Model {

    //用户消息+1
    /*
     * 	return array
     *  $user_id   用户id
     *  $shop_id   商户id
     *  $msg_type   类型
     *  $msg_title  消息标题
     *  $message   消息内容
     *  
     */
    public function getmessage_($id, $msg_type, $msg_title, $message) {
        if (!id || !$msg_type || !$msg_title || !$message)
            return false;
        if (intval($msg_type) == 1) {  //1用户，
            $user = M('User')->where('user_id=' . $id)->find();
            if (intval($user['user_status']) == 1) {
                $info['user_id'] = $id;
            } else {
                return false;
            }
        } else if (intval($msg_type) == 2) {//2经销商
            $agcy = M('agcy')->where('agcy_id=' . $id)->find();
            if (intval($agcy['agcy_state']) == 1) {
                $info['agcy_id'] = $id;
            } else {
                return false;
            }
        } else if (intval($msg_type) == 3) {//3专家
            $expert = M('expert')->where('expert_id=' . $id)->find();
            if (intval($expert['expert_state']) == 1) {
                $info['expert_id'] = $id; 
            } else {
                return false;
            }
        } else if (intval($msg_type) == 4) {//4厂家
            $factory = M('factory')->where('factory_id=' . $id)->find();
            if (intval($factory['factory_state']) == 1) {
                $info['factory_id'] = $id;
            } else {
                return false;
            }
        }
        $info['msg_type'] = $msg_type;
        $info['msg_title'] = $msg_title;
        $info['message'] = $message; 
        $info['msg_addtime'] = SYS_TIME;
        $info['msg_flag'] = 1;
        $data = $this->add($info);
        if ($data) {
            return true;
        } else {
            return false;
        }
    }
    
    
    //id专家id 
    public function get_message($id, $question_id,$msg_title, $message) {
        if (!id || !$question_id || !$msg_title || !$message){
            return false;
        }
        $info['expert_id'] = $id;
        $info['question_id'] = $question_id;
        $info['msg_type'] = 3;
        $info['msg_title'] = $msg_title;
        $info['message'] = $message; 
        $info['msg_addtime'] = SYS_TIME;
        $info['msg_flag'] = 1;
        $data = $this->add($info);
        if ($data) {
            return true;
        } else {
            return false;
        }
    }

    //消息状态
    public function msg_flags() {
        return array(
            '1' => '未读',
            '2' => '已读',
        );
    }

}
