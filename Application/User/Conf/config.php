<?php
return array(
	//'配置项'=>'配置值'
	"URL_MODEL"=>0,
	'DEFAULT_MODULE'        =>  'Home',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称
    /*佣金类型*/
	'income' => array(
	   1=>'下级返佣', 
	   2=>'自身返佣',
	),
	/*农户提现申请状态，1申请，2通过 , 3驳回*/
	'popinfo' => array(
	   1=>'申请中', 
	   2=>'提现成功',
	   3=>'驳回',
	),
);