<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title></title>
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/html5.css"/>
		<link rel="stylesheet" type="text/css"  href="/mrfat/Public/admin/font/iconfont.css">
		<link rel="stylesheet" type="text/css"  href="/mrfat/Public/admin/font/css/font-awesome.css">
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/css/index.css"/>
		<script src="/mrfat/Public/admin/js/jquery-1.10.2.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="/mrfat/Public/admin/js/icheck/icheck.js" type="text/javascript" charset="utf-8"></script>
		<link rel="stylesheet" type="text/css" href="/mrfat/Public/admin/js/icheck/square/green.css">
		<link href="/mrfat/Public/validform/Validform.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="/mrfat/Public/validform/Validform_v5.3.2.js"></script>
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
					<li> > </li>
					<li><a href="javascript:void(0);">修改菜单</a></li>
				</ul>
			</div>
			<div class="min">
				<div class="form_box">
						<form name="myform" id="myform" action="<?php echo U('adminmenu/editmenuinfo',array('menu_id'=>$adminmenu['menu_id']));?>" method="post">
						<div class="form-item">
							<label class="form-label">菜单名</label>
							<div class="form-text form-input-block">
								<input type="text"name="info[menu_name]" id="menu_name" type="text" class="dfinput" datatype="*2-30" errormsg="菜单名范围在2~30位之间！" maxlength="30" value="<?php echo ($adminmenu["menu_name"]); ?>"/>
								<i class="Validform_checktip"></i>
							</div>
						</div>
						<div class="form-item">
							<label class="form-label">菜单父节点</label>
							<div class="form-text form-input-block">
								<input type="text" name="menu_father_title" id="menu_father_title" type="text" class="dfinput" datatype="s2-30" errormsg="菜单父节点范围在2~30位之间！" maxlength="30" value="<?php echo ($father_menuinfo["menu_name"]); ?>" readonly  />
								<i class="Validform_checktip"></i>
								<input name="info[menu_fatherid]" id="menu_fatherid" type="hidden" class="dfinput" datatype="/^[1-9]\d*|0$/" errormsg="菜单父节点范围在1~5位之间！" maxlength="5" value="<?php echo ($father_menuinfo["menu_id"]); ?>" />
							</div>
						</div>
						<div class="form-item">
							<label class="form-label">菜单类型</label>
							<div class="form-select form-input-block">
								<select id="menu_type" name="info[menu_type]" datatype="*" errormsg="菜单类型不能为空！">
									<option value="1" <?php if($adminmenu['menu_type'] == 1): ?>selected<?php endif; ?>>菜单</option>
									<option value="2" <?php if($adminmenu['menu_type'] == 2): ?>selected<?php endif; ?>>操作</option>
								</select>
								<i class="Validform_checktip"></i>
							</div>
						</div>
						
						<div class="form-item">
							<label class="form-label">控制器</label>
							<div class="form-text form-input-block">
								<input name="info[menu_controller]" id="menu_controller" type="text" class="dfinput" datatype="s2-20" errormsg="控制器范围在2~20位之间！" maxlength="20" value="<?php echo ($adminmenu["controller"]); ?>"  /><i class="Validform_checktip"></i>
							</div>
						</div>
						<div class="form-item">
							<label class="form-label">方法</label>
							<div class="form-text form-input-block">
								<input name="info[menu_action]" id="menu_action" type="text" class="dfinput" datatype="s2-20" errormsg="方法范围在2~20位之间！"maxlength="20" value="<?php echo ($adminmenu["action"]); ?>"  /><i class="Validform_checktip"></i>
							</div>
						</div>
						<div class="form-item">
							<label class="form-label">菜单图标</label>
							<div class="form-text form-input-block">
								<input name="info[menu_icon]" id="menu_icon" type="text" class="dfinput" datatype="*2-20" errormsg="菜单图标范围在2~20位之间！" maxlength="20" value="<?php echo ($adminmenu["menu_icon"]); ?>"  /><i class="Validform_checktip"></i>
							</div>
						</div>
						<div class="form-item">
							<label class="form-label">排序</label>
							<div class="form-text form-input-block">
								<input name="info[sort]" id="sort" type="text" class="dfinput" datatype="/^[1-9]\d*|0$/" errormsg="排序范围在1~5位之间！"  maxlength="5" value="<?php echo ($adminmenu["sort"]); ?>"  /><i class="Validform_checktip"></i>
							</div>
						</div>
						
						<div class="form-item">
							<div class="form-button form-input-block">
								<input class="chaxun but" type="submit" value="提交"/>
								<button class="but chongzhi" type="button" onclick="location.href='<?php echo U('adminmenu/index');?>'">返回</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</body>
</html>

<script type="text/javascript">
	$(function(){
		$(".right_min").height($(window).height());
		
		$('.skin-section input').iCheck({
			checkboxClass: 'icheckbox_square-green',
			radioClass: 'iradio_square-green',
		});
		$('input').on('ifChecked', function(event){
		  alert($(this).val());
		});
	});
</script>
	<script>
	$(function(){
		$("#myform").Validform({
			//tiptype:2
			tiptype:function(msg,o,cssctl){
				var objtip=o.obj.siblings(".Validform_checktip");
				cssctl(objtip,o.type);
				objtip.text(msg);
			}
		});
	}); 
	</script>