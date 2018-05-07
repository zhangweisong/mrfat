<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>跳转提示</title>
		<link rel="stylesheet" type="text/css" href="__PUBLIC__/admin/css/html5.css"/>
		<link rel="stylesheet" type="text/css" href="__PUBLIC__/admin/css/top.css"/>
	</head>
	<body>
		<div class="tiaozhuanye_box">
			<div class="tiaozhuanye">
			
				<?php if(isset($message)) {?>
				<i class="img"><img src="__PUBLIC__/admin/images/tiaozhuanye_img.jpg"/></i>
				<p class="text"><?php echo($message); ?></p>
				<?php }else{?>
				<i class="img"><img src="__PUBLIC__/admin/images/tiaozhuanshibai.jpg"/></i>
				<p class="text"><?php echo($error); ?></p>
				<?php }?>
				<div class="text">
					<span>页面自动<a id="href" href="<?php echo($jumpUrl); ?>">跳转</a></span>
					<span>等待时间：<em id="wait"><?php echo($waitSecond); ?></em>秒</span>
				</div>
			</div>
		</div>
	</body>
<script type="text/javascript">
(function(){
	var wait = document.getElementById('wait'),href = document.getElementById('href').href;
	var interval = setInterval(function(){
		var time = --wait.innerHTML;
		if(time <= 0) {
		top.location.href = href;
		clearInterval(interval);
		};
	}, 1000);
})();
</script>
</html>
