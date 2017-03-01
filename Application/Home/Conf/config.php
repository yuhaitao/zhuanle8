<?php
return array(
	// //'配置项'=>'配置值'
	'URL_MODEL' => 1,
	'URL_ROUTER_ON'   => true, //开启路由
    'URL_ROUTE_RULES' => array( //定义路由规则 
        
        /***个人中心相关**/
        'userinfo/accountView' => 'Personal/accountView',    //个人中心------
        'userinfo/accountInfo' => 'Personal/accountInfo',    //账户信息------
        'saveNickName' => 'Personal/saveNickName',           //修改昵称
        'savePassWord' => 'Personal/savePassWord',           //修改密码
        'saveEmail' => 'Personal/saveEmail',                 //绑定邮箱
        'saveAddress' => 'Personal/saveAddress',             //保存联系地址
        'saveRealname' => 'Personal/saveRealname',           //实名认证
        'saveIcon' => 'Personal/saveIcon',                   //上传头像

        'userinfo/wdApply' => 'Personal/wdApply',            //提现申请------
        'userinfo/addBank' => 'Personal/addBank',            //添加银行卡
        'userinfo/sendCode' => 'Personal/sendCode',          //发送验证码
        'userinfo/saveCash' => 'Personal/saveCash',          //保存提现申请
        'getArea' => 'Personal/getArea',                     //获取开户地区
        'saveBank' => 'Personal/saveBank',                   //保存银行卡
        'userinfo/deleteBank' => 'Personal/deleteBank',      //删除银行卡
        'userinfo/saveDelBank' => 'Personal/saveDelBank',    //保存删除银行卡

        'userinfo/wdRecode' => 'Personal/wdRecode',          //提现记录------
        'userinfo/wdrecode_list' => 'Personal/wdrecode_list',//记录列表

        'userinfo/investRecord' => 'Personal/investRecord',  //投资记录------
        'userinfo/investrecord_list' => 'Personal/investrecord_list',//记录列表

        'userinfo/investPlat' => 'Personal/investPlat',      //投资平台------
        'userinfo/investPlat_list' => 'Personal/investPlat_list',//平台列表
        'userinfo/investrecord_list' => 'Personal/investrecord_list',//标的明细

        'userinfo/incomeDet' => 'Personal/incomeDet',        //收益明细------
        'userinfo/incomedet_list' => 'Personal/incomedet_list',//明细列表

        'userinfo/investRepair' => 'Personal/investRepair',  //投资申报------
        'addInvestRepair' => 'Personal/addInvestRepair',    //添加申报记录
        'userinfo/investrepair_list' => 'Personal/investrepair_list',//申报记录

        'userinfo/skipCode' => 'Personal/skipCode',          //跳转记录------
        'userinfo/skipcode_list' => 'Personal/skipcode_list',//记录列表

        'userinfo/inviteFriend' => 'Personal/inviteFriend',  //邀请好友------
        'share/:share_id' => 'Personal/share',                    //快速注册------
        's/:share_id' => 'Personal/cmVn',                            //邀请好友------

        'userinfo/inviteRecode' => 'Personal/inviteRecode',  //邀请记录------
        'userinfo/inviterecode_list' => 'Personal/inviterecode_list',//记录列表

        'userinfo/inforMation' => 'Personal/inforMation',    //消息中心------
        'userinfo/information_list' => 'Personal/information_list',//消息列表
        'userinfo/signState' => 'Personal/signState',        //标记为已读
        'userinfo/deleteState' => 'Personal/deleteState',    //删除
        /******产品详情相关********/
        'check_islogin' => 'SuperRebate/check_islogin',         //检测是否登录
        'saveInvestRecord' => 'Product/saveInvestRecord',       //保存点击 立即投资后相关的信息
        /****网站首页相关****/

        'superRebate' => 'superRebate/SuperDetil',              //超级返利
        'prod_list' => 'Product/prod_list',                     //产品列表页
        'product/:detail_id\d' => 'Product/ProInfo',              //产品详情页 点立即投资跳转
        'activecenter' => 'Active/activeCenter',                //活动专区
        'activeList' => 'Active/activeList',                    //活动专区
        'news/slide/:slide_id\d' => 'News/slideInfo',           //幻灯新闻-详情
        'newsList/:tag_id/:page' => 'News/newsList',             //相关分页写法
        'newsList/:tag_id\d' => 'News/newsList',                 //新闻列表页
        'news' => 'News/newsvw',                                  //理财学院
		'newsvw/:page' => 'News/newsvw',                          //相关分页写法
		'newsview/:news_id\d' => 'News/newsInfo',                    //新闻详情页
        'slidenews/:slide_id\d' => 'News/slideInfo',                 //幻灯新闻详情页
        
	    'financeview/:news_id' => 'News/financeDetail',                //媒体报道-详情
        'financeList/:page' => 'News/financeList',                  //媒体报道分页列表
        'finance' => 'News/financeList',                        //媒体报道列表
        
		
		
		'help' => 'Help/index',                                 //新手指引
        'doorReg/help' => 'Help/help',                          //帮助中心
        'doorReg/helpList' => 'Help/helpList',                  //帮助中心列表

        'aboutus/sitenotice/:article_id\d' => 'Article/SeeDetail',//公告详情
        'aboutus/sitenotice' => 'Article/SeeList',              //赚乐扒公告
        'SeeList/:page' => 'Article/SeeList',
        
        'aboutus/businessnews/:business_id\d' => 'Article/BusinessDetail',//理财资讯详情
        'aboutus/businessnews' => 'Article/BusinessNews',           //理财资讯
        'BusinessNews/:page' => 'Article/BusinessNews',
        
        'aboutus/companyinfo' => 'Article/CompanyInfo',         //公司介绍
        'aboutus/join' => 'Article/JoinUs',                     //加入我们
        'aboutus/contact' => 'Article/ContactUs',               //联系我们
        'aboutus/view' => 'Article/View',                       //意见反馈
        'feedback' => 'Article/ViewControl',                    //提交意见反馈
        'reg/calculator' => 'Tools/calculator',                 //计算器
        'index/count' => 'Index/everyDayCount',                 //统计数据


        'login/login' => 'Login/login',                         //登录界面
        'check_login' => 'Login/check_login',                   //登录检测 登录验证
        'login/passwords' => 'Login/passwords',                 //忘记密码
        'login/quit' => 'Login/logout',                         //退出登录
        'login/reg' => 'Register/register',                     //注册界面
        'login/regSuc' => 'Register/regSuc',                    //注册成功
        'login/agreement' => 'Register/agreement',              //同意注册
        'login/attention' => 'Register/attention',              //扫描微信

        'login/check_code' => 'Register/check_code',            //检测验证码
        'login/sendcode' => 'Register/sendcode',                //发送注册验证码
        'login/save_register' => 'Register/save_register',      //注册保存
        'login/verify' => 'Register/verify',                    //图形验证码
        'login/checkmobile' => 'Register/checkmobile',          //验证手机号码是否重复
        'login/checkshare' => 'Register/checkshare',            //验证邀请码是否正确
        'login/getMobileCode' => 'Login/sendcode',              //发送找回密码验证码
        'login/changePassword' => 'Login/changePassword',       //保存修改后的密码
        'user/gotoTradePWD' => 'Login/gotoTradePWD',            //找回交易密码
        'user/verify' => 'Register/verify',                     //验证码
        'userSpeedReg' => 'Personal/userSpeedReg',              //快速注册
        'user/saveTrade' => 'Login/saveTrade',
        //'share' => 'Personal/share',                            //邀请好友注册界面
        'productByPlat/:plat_id' => 'Product/Product',         //平台产品列表
        ':product_type' => 'Product/Product',                   //产品界面  
    ),

);