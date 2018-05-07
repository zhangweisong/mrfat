<?php
/**
 * 
 * 微信支付API异常类
 * @author widyhu
 * 
 */
class WxPayException extends Exception{
	public function __construct()
    {
		echo "<script>alert('参数异常，请稍后重新提交');location.href='".WEB_HOST."index.php?m=home';</script>";
		exit();
	}
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
