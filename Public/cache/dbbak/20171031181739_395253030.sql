/* This file is created by MySQLReback 2017-10-31 18:17:39 */
 /* 创建表结构 `ec_admininfo`  */
 /* 插入数据 `ec_admininfo` */
 INSERT INTO `ec_admininfo` VALUES ('1','admin','e10adc3949ba59abbe56e057f20f883e','1','13333333333','管理员','1',null),('2','1stmoon','e10adc3949ba59abbe56e057f20f883e','2','13445564657','张三','1',null);/* MySQLReback Separation */
 /* 创建表结构 `ec_adminmenu`  */
 /* 插入数据 `ec_adminmenu` */
 INSERT INTO `ec_adminmenu` VALUES ('1','系统管理','0','system','index','0','1','icon-shezhi'),('2','管理员设置','1','admininfo','index','1','1',null),('3','管理员列表','2','admininfo','index','0','1',null),('4','管理员添加','3','admininfo','addadmin','0','2',null),('5','管理员删除','3','admininfo','deladmin','0','2',null),('6','重置密码','3','admininfo','repasswrod','0','2',null),('10','管理员修改','3','admininfo','editadmin','0','2',null),('11','菜单列表','2','adminmenu','index','0','1',null),('12','角色列表','2','adminrole','index','0','1',null),('13','数据库备份','2','databak','index','0','1','index');/* MySQLReback Separation */
 /* 创建表结构 `ec_adminrole`  */
 /* 插入数据 `ec_adminrole` */
 INSERT INTO `ec_adminrole` VALUES ('1','系统管理员','1,2,3,11,12,13'),('2','工作人员','1,2,3,11,12');/* MySQLReback Separation */
 /* 创建表结构 `ec_infolist`  */