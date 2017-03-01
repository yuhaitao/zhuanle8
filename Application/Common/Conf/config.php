<?php
return array(
	//'配置项'=>'配置值'
	/* 数据库配置 */
    'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => 'localhost', // 服务器地址114.55.101.199
    'DB_NAME'   => 'zhuanle8', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => 'root',  // 密码
    //yjSEbOp1tsrZM8KnXK6uBSMnnymgNtdsggW7ZjJpupDYI7kpcWAPxd32HF9e9Rtd
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => '', // 数据库表前缀
    //'URL_MODEL'                    => 2,
    //'URL_CASE_INSENSITIVE'        => true,

    'SHOW_PAGE_TRACE'=>false,

    /*常量*/
    'WEB_SITE_TITLE' => '赚乐扒',
    'WEB_SYSTEM_SITE_TITLE' => '赚乐扒后台管理系统',
    
    //*********************************************
    'MODULE_DENY_LIST' => array('Common'),
    'MODULE_ALLOW_LIST' => array('Home','Admin'),
);
