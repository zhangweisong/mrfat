<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title></title>
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/html5.css"/>
		<link rel="stylesheet" type="text/css"  href="/mrfat/Public/admin/font/iconfont.css">
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/left.css"/>
		<script src="/mrfat/Public/admin/js/jquery-1.10.2.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="/mrfat/Public/admin/js/divscroll.js" type="text/javascript" charset="utf-8"></script>
	</head>
	<body class="left-body">
		<div class="left_box">
			<div class="left-top">
				<img src="/mrfat/Public/admin/images/logo.jpg">
			</div>
		</div>
		<div class="nav-box item">
			<ul class="">
				<li class="nav link">
					<i class="ico-left iconfont icon-shouye"></i>
					<a class="tit_nav" href="<?php echo U('index/welcome');?>" target="rightFrame">首页</a>
				</li>
				<?php if(is_array($menuinfo)): $i = 0; $__LIST__ = $menuinfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li class="nav">
					<i class="ico-left iconfont <?php echo ($menu["menu_icon"]); ?>"></i>
					<a class="tit_nav" href="javascript:void(0);" target="rightFrame"><?php echo ($menu["menu_name"]); ?></a>
					<i class="ico-right iconfont icon-jiantou2"></i>
					<ol class="nav_1">
						<?php if(is_array($menu['child'])): $i = 0; $__LIST__ = $menu['child'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$child): $mod = ($i % 2 );++$i;?><li class="li_1">
								<i class="ico-left iconfont icon-dot"></i>
								<a href="javascript:void(0);" target="rightFrame"><?php echo ($child["menu_name"]); ?></a>
				
								<i class="ico-right iconfont icon-202-copy"></i>
								<ul class="nav_2">
									<?php if(is_array($child['childchild'])): $i = 0; $__LIST__ = $child['childchild'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$childchild): $mod = ($i % 2 );++$i;?><li>
											<i class="ico-left iconfont icon-dot"></i>
											<a data="<?php echo U($childchild['controller'].'/'.$childchild['action']);?>" target="rightFrame" title="<?php echo ($childchild["menu_name"]); ?>"><?php echo ($childchild["menu_name"]); ?></a>
										</li><?php endforeach; endif; else: echo "" ;endif; ?>
								</ul>
							</li><?php endforeach; endif; else: echo "" ;endif; ?>
					</ol>
				</li><?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>
		</div>
	</body>
</html>
<script type="text/javascript">
	$(function(){
		$(".nav .tit_nav").click(function(){
			$(this).parents(".nav").siblings().find(".nav_1").hide(300);
			$(this).parents(".nav").addClass("link").siblings().removeClass("link");
			$(this).parents(".nav").toggleClass("hove").siblings().removeClass("hove");
			$(this).siblings(".nav_1").slideToggle(300);
			$(this).parents(".nav").siblings().find(".nav_1 li > .nav_2").hide().parents().siblings().find(".nav_2").hide();
			$(this).parents(".nav").siblings().find(".nav_1 li.li_1").removeClass("link");
			$(this).parents(".nav").siblings().find(".nav_1 li.li_1 .nav_2 > li").removeClass("link");
		});
		$(".nav_1 > li > a").click(function(){
			 $(this).parents(".li_1").siblings().find(".nav_2").hide(300);
			 $(this).siblings(".nav_2").slideToggle(300);
			 $(this).parents(".li_1").toggleClass("link").siblings().removeClass("link");
			 $(this).parents(".li_1").siblings().find(".nav_2 > li").removeClass("link");
		});
		$(".nav_2 > li").click(function(){
			 var data=$(this).find("a").attr("data");
			 $(this).addClass("link").siblings().removeClass("link");
			 $("#rightFrame",parent.document.body).prop("src",data);
		});
		
		$(".nav-box").height($(window).height()-$(".left_box").height()-20);
	    $('.nav-box').niceScroll({
	        cursorcolor: "#ccc",//#CC0071 光标颜色
	        cursoropacitymax: 0, //改变不透明度非常光标处于活动状态（scrollabar“可见”状态），范围从1到0
	        touchbehavior: false, //使光标拖动滚动像在台式电脑触摸设备
	        cursorwidth: "5px", //像素光标的宽度
	        cursorborder: "0", // 	游标边框css定义
	        cursorborderradius: "5px",//以像素为光标边界半径
	        autohidemode: true //是否隐藏滚动条
	    });
	});
</script>