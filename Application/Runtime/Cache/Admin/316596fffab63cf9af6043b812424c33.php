<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>菜单管理</title>
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/html5.css"/>
		<link rel="stylesheet" type="text/css"  href="/mrfat/Public/admin/font/iconfont.css">
		<link rel="stylesheet" type="text/css"  href="/mrfat/Public/admin/font/css/font-awesome.css">
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/index.css"/>
		<script src="/mrfat/Public/admin/js/jquery-1.10.2.min.js" type="text/javascript" charset="utf-8"></script>
	</head>
	<body>
		<div class="right_min">
			<div class="crumbs_nav">
				<i class="iconfont icon-shouye"></i>
				<ul>
					<li><a href="<?php echo U('index/welcome');?>">首页</a></li>
					<li> > </li>
					<li><a href="javascript:void(0);">系统设置</a></li>
					<li> > </li>
					<li><a href="<?php echo U('adminmenu/index');?>">菜单列表</a></li>
				
				</ul>
			</div>
			<div class="min">
				<!--添加菜单-->
				<div class="tianjia">
					<a class="tianjia_but" href="<?php echo U('adminmenu/addmenuinfo',array('menu_id'=>$father_menuinfo['menu_id']));?>">
						<i class="fa fa-plus"></i>
						添加菜单
					</a>
				</div>
				<div class="table_box">
					<table class="table" border="" cellspacing="" cellpadding="">
						<colgroup>
							<col width="80">
							<col width="100">
							<col width="100">
							<col width="100">
							<col width="100">
							<col width="100">
							<col>
							
						</colgroup>
						<thead>
							<tr>
								<th>编号</th>
								<th>菜单名称</th>
								<th>菜单类型</th>
								<th>控制器</th>	
								<th>方法</th>
								<th>操作</th>
							</tr>
						</thead>
						<tbody>
							<?php if(is_array($listinfo)): $i = 0; $__LIST__ = $listinfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menuinfo): $mod = ($i % 2 );++$i;?><tr>
								<td><?php echo ($menuinfo["menu_id"]); ?></td>
								<td><?php echo ($menuinfo["menu_name"]); ?></td>
								<td>
									<?php if($menuinfo['menu_type'] == 1 ): ?>左侧菜单
									<?php else: ?>
									操作菜单<?php endif; ?>
								
								</td>
								<td><?php echo ($menuinfo["controller"]); ?></td>
								<td><?php echo ($menuinfo["action"]); ?></td>
								<td>
									<a href="<?php echo U('adminmenu/index',array('menu_id'=>$menuinfo['menu_id']));?>" class="but  chaxun">
										 <i class="fa fa-eye"></i>
										查看下级菜单
									</a>
									<a href="<?php echo U('adminmenu/editmenuinfo',array('menu_id'=>$menuinfo['menu_id']));?>" class="but chaxun">
										<i class="fa fa-edit"></i>
										编辑
									</a>
									<!--
									<a href="<?php echo U('adminmenu/delmenuinfo',array('menu_id'=>$menuinfo['menu_id']));?>" onclick="{if(confirm('确认删除?')){return true;}return false;}" class="but chongzhi">
										<i class="fa fa-trash-o"></i>
										删除
									</a>
									-->
								</td>
							</tr><?php endforeach; endif; else: echo "" ;endif; ?>
						</tbody>
					</table>
				</div>
				<?php echo ($pageshow); ?>
			</div>
		</div>
	</body>
</html>
<script type="text/javascript">
	$(function(){
		$(".right_min").height($(window).height());
	});
</script>