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
					<li><a href="<?php echo U('admininfo/index');?>">管理员列表</a></li>
					<li> > </li>
					<li><a href="javascript:void(0);">修改管理员</a></li>
				</ul>
			</div>
			<div class="min">
				<div class="form_box">
					<form name="myform" id="myform" action="<?php echo U('admininfo/editadmin',array('admin_id'=>$admininfo['admin_id']));?>" method="post">
						<div class="form-item">
							<label class="form-label">用户名</label>
							<div class="form-text form-input-block">
								<input name="info[username]" id="username" type="text"  datatype="*4-20" errormsg="用户名范围在4~20位之间！" maxlength="20" readonly value="<?php echo ($admininfo["username"]); ?>" />
								<i class="Validform_checktip"></i>
							</div>
						</div>
						<div class="form-item">
							<label class="form-label">手机号</label>
							<div class="form-text form-input-block">
								<input name="info[phone]" id="phone" type="text" datatype="/^1\d{10}$/" errormsg="请输入正确手机号！" nullmsg="请输入正确手机号！"  onkeyup='this.value=this.value.replace(/\D/gi,"")' maxlength="11"  value="<?php echo ($admininfo["phone"]); ?>" />
								<i class="Validform_checktip"></i>
							</div>
						</div>
						<div class="form-item">
							<label class="form-label">真实姓名</label>
							<div class="form-text form-input-block">
								<input name="info[realname]" id="realname" type="text" class="dfinput"  datatype="*2-15" errormsg="真实姓名范围在2~15位之间！" maxlength="15" value="<?php echo ($admininfo["realname"]); ?>" />
								<i class="Validform_checktip"></i>
							</div>
						</div>
						<div class="form-item">
							<label class="form-label">状态</label>
							<div class="form-radio form-input-block">
								<div class="skin-section">
									<ul>
										<li>
											<input type="radio" name="info[isable]" <?php if($admininfo["isable"] == 1 ): ?>checked<?php endif; ?> value="1">
											<span>启用</span>
										</li>
										<li>
											<input type="radio" name="info[isable]" <?php if($admininfo["isable"] == 2 ): ?>checked<?php endif; ?>  value="2">
											<span>禁用</span>
										</li>
										
									</ul>
								</div>
								<i class="Validform_checktip"></i>
							</div>
						</div>
						<div class="form-item">
							<label class="form-label">角色</label>
							<div class="form-select form-input-block">
								<select id="adminrole" name="info[adminrole]" datatype="*" errormsg="角色不能为空！">
									<option value="">选择角色</option>
									<?php if(is_array($groupinfo)): $i = 0; $__LIST__ = $groupinfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?><option value="<?php echo ($data["role_id"]); ?>" <?php if($data['role_id'] == $admininfo['adminrole']): ?>selected<?php endif; ?>><?php echo ($data["role_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
								</select>
								<i class="Validform_checktip"></i>
							</div>
						</div>
						
						
						<div class="form-item">
							<div class="form-button form-input-block">
								<input class="chaxun but" type="submit" value="提交"/>
								<button class="but chongzhi" type="button" onclick="location.href='<?php echo U('admininfo/index');?>'">返回</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</body>
</html>
 
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