<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title></title>
		<meta http-equiv="X-UA-Compatible" content="IE=Edge，chrome=1">
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/html5.css"/>
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/font/css/font-awesome.css"/>
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/login.css"/>
		<link href="/mrfat/Public/validform/Validform.css" rel="stylesheet" type="text/css" />
		<script src="/mrfat/Public/admin/js/jquery-1.10.2.min.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript" src="/mrfat/Public/validform/Validform_v5.3.2.js"></script>
	</head>
	<body>
		<div class="login_bg"></div>
		<div class="login_box">
			<div class="login">
				<h4 class="head"><?php echo (C("System.admin_title")); ?></h4>
				<dl>
					<dt><img src="/mrfat/Public/admin/images/dt_img.png"/></dt>
					<dd>
						<h4>
							<span>用户登录</span>
							<strong>USERLOGIN</strong>
						</h4>
						<form  name="myform" id="myform" action="<?php echo U('login/login');?>" method="post" class="form">
							<div class="text">
								<i class="fa fa-user-o ico"></i>
								<p><input name="username" id="username" type="text" class="loginuser" value="" placeholder="请输入登录用户名" datatype="*4-20" errormsg="用户名范围在4~20位之间！" maxlength="20" /></p>
							</div>
							<div class="password">
								<i class="fa fa-lock ico"></i>
								<p><input name="password" id="password" type="password" class="loginpwd" value="" placeholder="请输入登录密码" datatype="*5-15" errormsg="密码范围在5~15位之间！" maxlength="20" /></p>
							</div>
							<div class="submit">
								<input type="submit" value="登录"/>
							</div>
						</form>
					</dd>
				</dl>
			</div>
		</div>
		<div class="foot">
			<?php echo (C("System.copyright")); ?>
		</div>
	</body>
	<script>
	$(function(){
		$("#myform").Validform();
	}); 
	</script>
</html>