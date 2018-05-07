<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo (C("System.admin_title")); ?></title>
		<meta http-equiv="X-UA-Compatible" content="IE=Edge，chrome=1">
		<frameset rows="*" cols="220,*" frameborder="no" border="0" framespacing="0">
			<frame src="<?php echo U('index/left');?>" name="leftFrame" scrolling="No" noresize="noresize" id="leftFrame" title="leftFrame" />
			<frameset cols="*" rows="55,*,45" frameborder="no" border="0" framespacing="0">
				<frame src="<?php echo U('index/top');?>" name="topFrame" scrolling="No" noresize="noresize" id="topFrame" title="topFrame" />
				<frame src="<?php echo U('index/welcome');?>" name="rightFrame" id="rightFrame" title="rightFrame" />
				<frame src="<?php echo U('index/bottom');?>" name="bottomFrame" id="bottomFrame" title="bottomFrame"/>
			</frameset>
		</frameset>
		<noframes>
			<body>
			</body>
		</noframes>
</html>