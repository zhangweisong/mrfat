<?php
function mbs_substr($str, $length, $tail = '...')
{
    $len = mb_strlen($str, 'utf-8');//获取字符串的长度
    if ($len > $length) {
        return mb_substr($str, 0, $length, 'utf-8') . $tail;
    } else {
        return $str;
    }
} 

//发送模板消息
function sendtemplate($data){
	//引入WxpayAPI
	vendor('WxpayAPI.WxPayJsApiPay');
	$jsApi = new \JsApiPay();
	$accessToken = $jsApi->getAccessToken();

	foreach($data as $v){//循环发送模板消息，速度可能受限制
		$jsApi->sendtemplate($v,$accessToken);
	}
	return true;
}
//发送短信通知
function sendphonemsg($phone,$msg){
	$checkphone=strlen($phone) == 11 && preg_match("/^1[0-9]{10}$/", $phone);
	if($checkphone){
		//接短信接口发送短信通知
		return true;
	}else{
		return false;
	}
}