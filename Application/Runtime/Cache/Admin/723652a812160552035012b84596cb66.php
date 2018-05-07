<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>数据库备份</title>
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
					<li><a href="<?php echo U('databak/index');?>">数据库备份</a></li>
				</ul>
			</div>
			<div class="min">
				<!--添加角色-->
				<div class="tianjia">
					<a class="tianjia_but" href="<?php echo U('databak/backdb');?>">
						<i class="fa fa-plus"></i>
						备份数据库
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
						</colgroup>
						<thead>
							<tr>
								<th>编号</th>
								<th>文件名</th>
								<th>文件大小</th>
								<th>创建时间</th>
								<th>操作</th>
							</tr>
						</thead>
						<tbody>
						<?php if(is_array($listinfo)): $key = 0; $__LIST__ = $listinfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$info): $mod = ($key % 2 );++$key;?><tr>
								<td><?php echo ($key); ?></td>
								<td><?php echo ($info["name"]); ?></td>
								<td><?php echo ($info["size"]); ?></td>
								<td><?php echo ($info["time"]); ?></td>
								<td>
									<a href="<?php echo U('databak/delbak',array('name'=>$info['name']));?>" onclick="{if(confirm('确认删除?')){return true;}return false;}" class="but chongzhi">
									<i class="fa fa-trash-o"></i>
									删除</a>
									<a href="<?php echo U('databak/downloadbak',array('name'=>$info['name']));?>" class="but chaxun">
									<i class=" fa fa-cloud-download"></i>
									下载</a>
								</td>
							</tr><?php endforeach; endif; else: echo "" ;endif; ?>
						
						</tbody>
					</table>
				</div>
			</div>
    </div>
<script type="text/javascript">
	$('.tablelist tbody tr:odd').addClass('odd');
</script>
</body>

</html>