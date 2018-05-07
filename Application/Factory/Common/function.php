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


//二维转一维
function i_array_column($input, $columnKey, $indexKey = null) {
    if (!function_exists('array_column')) {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
        $indexKeyIsNull = (is_null($indexKey)) ? true : false;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
        $result = array();
        foreach ((array) $input as $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : null;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    } else {
        return array_column($input, $columnKey, $indexKey);
    }
}