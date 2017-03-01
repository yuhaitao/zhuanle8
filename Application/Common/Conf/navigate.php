<?php  
//位置导航
return array(
	'admin/index' => array(
		'name' => '数据统计',
		'action' => array(
			'index' => '昨日数据',
			'welcome' => '昨日数据',
		)
	),
	'admin/platdata' => array(
		'name' => '数据统计',
		'action' => array(
			'platdata' => '平台数据'
		)
	),
	'admin/user' => array(
		'name' => '用户管理',
		'action' => array(
			'user' => '用户列表',
			'friendList'=> '好友列表',
			'staff'=> '员工列表'
		)
	),
	'admin/bank' => array(
		'name' => '用户管理',
		'action' => array(
			'bank' => '用户绑卡'
		)
	),
	'admin/backuser' => array(
		'name' => '后台用户',
		'action' => array(
			'backuser' => '用户管理',
			'staffgroup' => '员工分组'
		)
	),
	'admin/helpcore' => array(
		'name' => '帮助中心',
		'action' => array(
			'feedback' => '意见反馈',
			'feedbackType' => '类型分类'
		)
	),
	'admin/cash' => array(
		'name' => '提现管理',
		'action' => array(
			'cash' => '提现审核',
			'cashSuccess' => '提现成功',
			'cashFail' => '提现失败'
		)
	),
	'admin/checkbill' => array(
		'name' => '对账管理',
		'action' => array(
			'checkbill' => '对账审核',
		)
	),
	'admin/cash' => array(
		'name' => '提现管理',
		'action' => array(
			'cash' => '提现审核',
			'cashSuccess' => '提现成功',
			'cashFail' => '提现失败'
		)
	),
	'admin/thaw' => array(
		'name' => '满标解冻',
		'action' => array(
			'thaw' => '稳健型产品',
			'limitThaw' => '精选产品',
			'superThaw' => '高收益产品'
		)
	),
	'admin/investrank' => array(
		'name' => '投资排行管理',
		'action' => array(
			'InvestRank' => '编辑投资排行',
			'topInvest' => 'Top10投资奖励'
		)
	),
	'admin/addorder' => array(
		'name' => '添加订单',
		'action' => array(
			'addorder' => '添加订单',
			'orderReview' => '订单审核',
			'orderInquiry' => '订单查询'
		)
	),
	'admin/investdeclare' => array(
		'name' => '投资申报',
		'action' => array(
			'investdeclare' => '已提交',
			'declaring' => '审核中',
			'declarsuccess' => '审核成功',
			'declarfail' => '审核失败'
		)
	),
	'admin/product' => array(
		'name' => '产品管理',
		'action' => array(
			'dayProduct' => '产品编辑  /  稳健型产品',
			'limitProduct' => '产品编辑  /  精选产品',
			'superProduct' => '产品编辑  /  高收益产品',
			'dayProductRelease' => '产品发布中  /  稳健型产品',
			'limitProductRelease' => '产品发布中  /  精选产品',
			'superProductRelease' => '产品发布中  /  高收益产品',
			'dayProduct_full' => '产品满标  /  稳健型产品',
			'limitProduct_full' => '产品满标  /  精选产品',
			'superProduct_full' => '产品满标  /  高收益产品',
			'dayProduct_shelves' => '产品下架  /  稳健型产品',
			'limitProduct_shelves' => '产品下架  /  精选产品',
			'superProduct_shelves' => '产品下架  /  高收益产品',
			'dayProductFlow' => '产品流标  /  稳健型产品',
			'limitProductFlow' => '产品流标  /  精选产品',
			'superProductFlow' => '产品流标  /  高收益产品',

		)
	),
	'admin/plat' => array(
		'name' => '平台管理',
		'action' => array(
			'plat' => '平台列表',
			'platHandle' => '跳转操作'
		)
	),
	'admin/system' => array(
		'name' => '系统管理',
		'action' => array(
			'role' => '权限管理  /  角色列表',
			'right' => '权限管理  /  页面元素',
			'sms' => '发送管理  /  短信管理',
			'email' => '发送管理  /  邮箱管理',
			'partner' => '合作渠道  /  合作伙伴',
			'link' => '合作渠道  /  友情链接',
			'dicBig' => '其他设置  /  大类字典',
			'dicSmall' => '其他设置  /  小类字典',
			'area' => '其他设置  /  区域字典',
			'website' => '其他设置  /  网站设置',
			'template' => '其他设置  /  短信模板',
			'banner' => '其他设置  /  主页轮播',
		)
	),
	'admin/article' => array(
		'name' => '文章管理',
		'action' => array(
			'companyInfo' => '公司介绍',
			'caiqiAnnoun' => '赚乐扒公告',
			'joinus' => '加入我们'
		)
	),
	'admin/active' => array(
		'name' => '活动',
		'action' => array(
			'activeManager' => '活动管理'
		)
	),
	'admin/news' => array(
		'name' => '理财学院',
		'action' => array(
			'slideNews' => '幻灯新闻',
			'news' => '理财新闻',
			'media' => '公司新闻',
			'hotTags' => '热门标签'
		)
	),
    'admin/platanalysis' => array(
        'name' => '平台数据管理',
        'action' => array(
            'analysisdata' => '统计数据'
        )
    )
);
?>