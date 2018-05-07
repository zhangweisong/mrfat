<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>统计</title>
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/html5.css"/>
		<link rel="stylesheet" type="text/css"  href="/mrfat/Public/admin/font/iconfont.css">
		<link rel="stylesheet" type="text/css"  href="/mrfat/Public/admin/font/css/font-awesome.css">
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/index.css"/>
		<script src="/mrfat/Public/admin/js/jquery-1.10.2.min.js" type="text/javascript" charset="utf-8"></script>
	</head>
	<body>
		<style type="text/css">
			.right_min{background: #f2f2f2;}
			.query{background:#fff;}
		</style>
		<div class="right_min">
			<div class="crumbs_nav">
				<i class="iconfont icon-shouye"></i>
				<ul>
					<li><a href="<?php echo U('index/welcome');?>">首页</a></li>
					<li> > </li>
					<li><a href="javascript:void(0);">主页</a></li>
				</ul>
			</div>
			
			<div class="min">
			<!--查询-->
				<div class="query">
					<h1 style="font-size:25px;">欢迎【<font color=red><?php echo ($_SESSION['admin']['realname']); ?>(<?php echo ($_SESSION['admin']['username']); ?>)</font>】进入<?php echo (C("System.admin_title")); ?>！</h1>
				</div>
				<div class="tongji_box">
					<ul>
						<li class="tongji pg_lr15">
							<a href="<?php echo U('user/index');?>">
								<div class="title">
									<i class="fa fa-users" style="color:#54FF9F;font-size: 16px;"></i>
									<h4><span>农户数</span></h4>
								</div>
								<div class="center">
									<p> 
										<span><?php echo ($usercount); ?></span>
										<em>人</em>
									</p>
								</div>
							</a>
						</li>
					<li class="tongji pg_lr15">
							<a href="<?php echo U('agcy/index');?>">
								<div class="title">
									<i class="fa fa-users" style="color:#54FF9F;font-size: 16px;"></i>
									<h4><span>经销商数</span></h4>
								</div>
								<div class="center">
									<p> 
										<span><?php echo ($agcycount); ?></span>
										<em>个</em>
									</p>
								</div>
							</a>
						</li>
						<li class="tongji pg_lr15">
							<a href="<?php echo U('expert/index');?>">
								<div class="title">
									<i class="fa fa-users" style="color:#54FF9F;font-size: 16px;"></i>
									<h4><span>专家数</span></h4>
								</div>
								<div class="center">
									<p> 
										<span><?php echo ($expertcount); ?></span>
										<em>人</em>
									</p>
								</div>
							</a>
						</li>
						<li class="tongji pg_lr15">
							<a href="<?php echo U('factory/index');?>">
								<div class="title">
									<i class="fa fa-users" style="color:#54FF9F;font-size: 16px;"></i>
									<h4><span>厂家数</span></h4>
								</div>
								<div class="center">
									<p> 
										<span><?php echo ($factorycount); ?></span>
										<em>个</em>
									
									</p>
								</div>
							</a>
						</li>
						<li class="tongji pg_lr15">
							<a href="<?php echo U('bookorder/index');?>">
								<div class="title">
									<i class="fa fa-users" style="color:#54FF9F;font-size: 16px;"></i>
									<h4><span>图书订单数（已完成）</span></h4>
								</div>
								<div class="center">
									<p> 
										<span><?php echo ($bookorder); ?></span>
										<em>单</em>
									</p>
								</div>
							</a>
						</li>
						<li class="tongji pg_lr15">
							<a href="<?php echo U('jifen/orderlist');?>">
								<div class="title">
									<i class="fa fa-users" style="color:#54FF9F;font-size: 16px;"></i>
									<h4><span>积分订单数（已完成）</span></h4>
								</div>
								<div class="center">
									<p> 
										<span><?php echo ($jifenorder); ?></span>
										<em>单</em>
									
									</p>
								</div>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</body>
</html>
<script type="text/javascript">
	$(function(){
		$(".right_min").height($(window).height());
	});
</script>