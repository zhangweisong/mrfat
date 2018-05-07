/* This file is created by MySQLReback 2017-11-02 10:34:28 */
 /* 创建表结构 `ec_admininfo`  */
CREATE TABLE `ec_admininfo` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `username` varchar(50) DEFAULT NULL COMMENT '管理员账号',
  `password` varchar(50) DEFAULT NULL COMMENT '管理员密码',
  `adminrole` int(11) DEFAULT '0' COMMENT '角色',
  `phone` varchar(15) DEFAULT NULL COMMENT '管理员电话号码',
  `realname` varchar(20) DEFAULT NULL COMMENT '真实姓名',
  `isable` tinyint(4) DEFAULT '1' COMMENT '状态，1启用，2禁用',
  `remarks` varchar(255) DEFAULT NULL COMMENT '简介',
  PRIMARY KEY (`admin_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='系统平台管理员表'
 /* 插入数据 `ec_admininfo` */
 INSERT INTO `ec_admininfo` VALUES ('1','admin','e10adc3949ba59abbe56e057f20f883e','1','13333333333','管理员','1',null),('2','1stmoon','e10adc3949ba59abbe56e057f20f883e','2','13445564657','张三','1',null);/* MySQLReback Separation */
 /* 创建表结构 `ec_adminmenu`  */
CREATE TABLE `ec_adminmenu` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `menu_name` varchar(50) DEFAULT NULL COMMENT '菜单名称',
  `menu_fatherid` int(11) DEFAULT '0' COMMENT '菜单级别，三层，顶级为0，其他fid',
  `controller` varchar(50) DEFAULT NULL COMMENT '控制器名，小写',
  `action` varchar(50) DEFAULT NULL COMMENT '方法名，小写',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `menu_type` tinyint(4) DEFAULT '0' COMMENT '菜单类型，1菜单，2操作',
  `menu_icon` varchar(50) DEFAULT NULL COMMENT '菜单图标样式',
  PRIMARY KEY (`menu_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='管理员菜单表'
 /* 插入数据 `ec_adminmenu` */
 INSERT INTO `ec_adminmenu` VALUES ('1','系统管理','0','system','index','5','1','icon-shezhi'),('2','管理员设置','1','admininfo','index','1','1',null),('3','管理员列表','2','admininfo','index','0','1',null),('4','管理员添加','3','admininfo','addadmin','0','2',null),('5','管理员删除','3','admininfo','deladmin','0','2',null),('6','重置密码','3','admininfo','repasswrod','0','2',null),('10','管理员修改','3','admininfo','editadmin','0','2',null),('11','菜单列表','2','adminmenu','index','0','1',null),('12','角色列表','2','adminrole','index','0','1',null),('13','数据库备份','2','databak','index','0','1','index'),('15','新闻管理','1','newlist','index','3','1','index'),('16','新闻列表','15','newlist','index','0','1','index'),('17','分类管理','15','newtype','index','0','1','index'),('18','新闻评论管理','15','commentlist','index','0','1','index'),('19','产品管理','1','product','index','2','1','index'),('20','产品列表','19','product','index','0','1','产品列表'),('21','产品分类','19','producttype','index','0','1','index'),('22','系统管理','1','system','index','5','1','index'),('23','系统设置','22','system','index','0','1','index'),('24','论坛管理','1','bbslist','index','1','1','index'),('25','用户管理','1','user','index','4','1','index'),('26','用户列表','25','user','index','0','1','index');/* MySQLReback Separation */
 /* 创建表结构 `ec_adminrole`  */
CREATE TABLE `ec_adminrole` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '角色编号',
  `role_name` varchar(50) DEFAULT NULL COMMENT '角色名称',
  `role_power` varchar(1000) DEFAULT NULL COMMENT '权限设置，用逗号隔开menu_id',
  PRIMARY KEY (`role_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='管理员自定义角色设置'
 /* 插入数据 `ec_adminrole` */
 INSERT INTO `ec_adminrole` VALUES ('1','系统管理员','1,2,3,11,12,13'),('2','工作人员','1,2,3,11,12');/* MySQLReback Separation */