<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title></title>
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/html5.css"/>
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/top.css"/>
	</head>
	<body>
		<div class="top_box">
			<div class="left"><?php echo (C("System.admin_title")); ?></div>
			<div class="right">
				<h4>当前管理员：<?php echo ($_SESSION['admin']['realname']); ?>(<?php echo ($_SESSION['admin']['username']); ?>)</h4>
				<ul>
					<li><a href="<?php echo U('admininfo/resetpswadmin');?>" target="rightFrame">修改密码</a></li>
					<li> - </li>
					<li><a href="#" onclick="loginout();">退出</a></li>
				</ul>
			</div>
		</div>
	</body>
	<script>
	 //退出系统
	 function loginout() {
		var msg = "你确定要退出系统吗？\n\n请确认！";
		if (confirm(msg)==true){
			top.location.href='<?php echo U('login/loginout');?>';
		}else{
			return false;
		}
	}
	</script>
</html>