//配置layer 主题
layer.config({
    extend: ['skin/espresso/style.css'], //加载新皮肤
    skin: 'layer-ext-espresso' //一旦设定，所有弹层风格都采用此主题。
});

//注册用户
var obj={};
function regCount(nuvs){
	$.data(obj,'strvs',nuvs);
	$.fancybox({
		'type':'ajax',
		'href':App+'/Admin/Index/showreg.html',
		'autoScale' :false,
		'hideOnOverlayClick':true,
	});
}
//投资人数
var obj_buy={};
function buyUserCount(uvs){
	$.data(obj_buy,'utrvs',uvs);
	$.fancybox({
		'type':'ajax',
		'href':App+'/Admin/Index/showbuy.html',
		'autoScale':true,
	});
}
//平台数据
var obj_p ={};
function platUserCount(plat){
    $.data(obj_p,'plats',plat);
    $.fancybox({
      'type':'ajax',
      'href':App+'/Admin/Platdata/showplat.html',
      'autoScale' :true,
    });
}
//用户绑卡查看弹窗
var bank_user_id="";
function checkit(userid){
	bank_user_id=userid;
	$.fancybox({
		'type':'ajax',
		'href':App+'/Admin/Bank/check_bank.html',
		'autoScale':true
	});
}

//用户详情
function openUserTable(user_id){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/User/user_info?user_id="+user_id,
		'autoScale':true,
	});
}

//修改用户
function goUser(revise_id){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/User/revise_user?revise_id="+revise_id,
		'autoScale' :true,
	});
}
//删除用户
function deleteUser(delete_id){
	if(delete_id){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post',
		  	url:App+'/Admin/User/delete_user',
		  	dataType:'json',
		  	async:true,
		  	data:{delete_id:delete_id},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功！',{icon: 1,time: 1000});
		  			$("#staff_show").trigger("reloadGrid");
		  			$("#lis_user").trigger("reloadGrid");
		  		}else{
		  			top.layer.msg('删除失败！',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//跳转列表
var obj_j = {};
function openProductJump(jump_id){
	$.data(obj_j,'jump_id',jump_id);//存储跳转记录ID
	$.fancybox({
		'type':'ajax',
		'href':App+'/Admin/User/jump.html',
		'autoScale' :true,
	});
}
//购买清单
var obj_d ={};
function openProductBuy(buy_id){
	$.data(obj_d,'buy_id',buy_id);//存储跳转记录ID
	$.fancybox({
		'type':'ajax',
		'href':App+'/Admin/User/buy_list.html',
		'autoScale' :true,
	});
}
//资产信息
function financeRecoed(financeid){
	layer.open({
		type:2,
		title:false,
		shadeClose:true,	
		skin: 'layer-ext-espresso',
		area: ['1200px', '600px'],
		content: App+'/Admin/User/financerecord?finance_id='+financeid
	});
}
//浏览记录
var obj_l ={};
function openBrowse(browse_id){
	$.data(obj_l,'browse_id',browse_id);//存储跳转记录ID
	$.fancybox({
		'type':'ajax',
		'href':App+'/Admin/User/browse.html',
		'autoScale' :true,
	});
}

//查看好友列表
var obj_friend = {};
function openQueryFriends(friend_id){
	$.data(obj_friend,'friend_id',friend_id); //存储查看记录ID
	$.fancybox({
		'type':'ajax',
		'href':App+'/Admin/User/check_friend.html',
		'autoScale':true,
	});
}

//修改银行卡
function updateBank(revise_id){
	layer.open({
		type:2,
		title: '修改银行卡',
		shadeClose:true,	
		skin: 'layer-ext-espresso',
		area: ['560px', '260px'],
		content: App+"/Admin/Bank/revise_bank?revise_id="+revise_id
	});
	
}
//删除 银行卡
function deleteBank(delete_id){
	if(delete_id){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/Bank/delete_bank',
		  	dataType:'json',
		  	async:true,
		  	data:{delete_id:delete_id},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功！',{icon: 1,time: 1000});
		  			jQuery("#banklist_show").trigger("reloadGrid");
		  		}else{
		  			top.layer.msg('删除失败！',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//回复-意见反馈
function openUpdate(reply_id){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/HelpCore/reply?reply_id="+reply_id,
		'autoScale':true,
	});
}
//删除-意见反馈
function del_Feedback(deleteFeedback_id){
	if(deleteFeedback_id){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/HelpCore/delete_feedback',
		  	dataType:'json',
		  	async:true,
		  	data:{delete_id:deleteFeedback_id},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功！',{icon: 1,time: 1000});
		  			$("#feedback").trigger("reloadGrid");
		  		}else{
		  			top.layer.msg('删除失败！',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
function deleteFeedback(deleteFeedback_id){
	if(deleteFeedback_id){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/HelpCore/delete_feedback',
		  	dataType:'json',
		  	async:true,
		  	data:{delete_id:deleteFeedback_id},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功！',{icon: 1,time: 1000});
		  			$("#type_show").trigger("reloadGrid");
		  		}else{
		  			top.layer.msg('删除失败！',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//刷新feedback
function refresh_feed(){
	$("#feedback").trigger("reloadGrid");
}
function refresh_bill(){
	//console.log('bill');
	$("#bill_show").trigger("reloadGrid");
}
//修改问题描述
function updateFeed(updateFeed_id){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/HelpCore/updateFeed?updateFeed_id="+updateFeed_id,
		'autoScale' :true
	});
}
//后台用户--修改密码
function OpenUpdatePassword(userid){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/BackUser/revise_user?revise_id="+userid,
		'autoScale' :true
	});
}
//后台用户--删除
function deleteBackUser(userid){
	if(userid){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/BackUser/delete_user',
		  	dataType:'json',
		  	async:true,
		  	data:{delete_id:userid},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功！',{icon: 1,time: 1000});
		  			jQuery("#backuser_show").trigger("reloadGrid");
		  		}else{
		  			top.layer.msg('删除失败！',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//对账管理-审核
function openCheckAll(product_id,plat_id){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/CheckBill/billReviewed?reviewed_id="+product_id+"&plat_id="+plat_id,
		'autoScale' :true
	});
}
//对账管理-修改 录入
function openEditCheck(pro_id,plat_id,type_id){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/CheckBill/billChange?pro_id="+pro_id+"&plat_id="+plat_id+"&type_id="+type_id,
		'autoScale' :true
	});
}

//--满标解冻 ： 解冻
function productThaw(pid){
	if(pid){
		top.layer.confirm('确定解冻？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/Thaw/productThaw',
		  	dataType:'json',
		  	async:true,
		  	data:{product_id:pid},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('解冻成功！',{icon: 1,time: 1000});
		  			jQuery("#thaw_list").trigger("reloadGrid");
		  		}else{
		  			top.layer.msg('解冻失败！',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//提现审核--审核
var cash_id = "";
function examine_cash(cid){
    cash_id=cid;
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Cash/examine_cash",
		'autoScale' :true
	});
}
//添加订单--查看
function checkThis(pid){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/AddOrder/product_view?product_id="+pid,
		'autoScale' :true
	});
}
//添加订单--添加订单
var add_pid='';
function AddOder(pid){
	add_pid=pid;
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/AddOrder/add_order.html",
		'autoScale' :true
	});
}
//订单审核---审核
var order_id="";
function reviewOrder(oid){
	order_id=oid;
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/AddOrder/review_order",
		'autoScale' :true
	});
}
//投资申报---提交审核
function submitDeclare(iid){
	if(iid){
		top.layer.confirm('确定提交？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/InvestDeclare/updateInvest',
		  	dataType:'json',
		  	async:true,
		  	data:{invest_id:iid},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('提交成功',{icon: 1,time: 1000});
		  			rightContent.window.reload_investdeclare_show();
		  		}else{
		  			top.layer.msg('提交失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//投资申报---审核
function reviewDeclare(iid){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/InvestDeclare/declare_invest?invest_id="+iid,
		'autoScale' :true
	});
}
//投资申报---查看
 function checkInvest(iid){
 	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/InvestDeclare/check_invest?invest_id="+iid,
		'autoScale' :true
	});
 }

//投资排行管理-添加金额
function editInvestRank(mobile_id){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/InvestRank/add_invest?invest_id="+mobile_id,
		'autoScale' :true
	});
}
//平台管理--平台列表--查看
function review_plat(pid){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Plat/review_plat?plat_id="+pid,
		'autoScale' :true
	});
}
//平台管理--平台列表--编辑
function edit_plat(pid){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Plat/edit_plat?plat_id="+pid,
		'autoScale' :true
	});
}
//投资排行管理-删除金额
function deleteInvestRank(delRank_id){
	if(delRank_id){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/InvestRank/deleteRank',
		  	dataType:'json',
		  	async:true,
		  	data:{delRank_id:delRank_id},
		  	success:function(data){
		  		if(data == 1){
	            	top.layer.msg('删除成功!',{icon: 1,time: 1500});
	            	$.fancybox.close();
	            	rightContent.window.reload_investRank();
	            }else{
	                top.layer.msg('删除失败!',{icon: 11,time: 1500});
	            }
		  	}
		  });
		}, function(){
		  
		});
	}
}
//平台管理--平台列表--删除
function delete_plat(pid){
	if(pid){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/Plat/delete_plat',
		  	dataType:'json',
		  	async:true,
		  	data:{plat_id:pid},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('提交成功',{icon: 1,time: 1000});
		  			rightContent.window.reload_plat_show();
		  		}else{
		  			top.layer.msg('提交失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//平台管理--平台列表--添加
function add_plat(){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Plat/add_plat.html",
		'autoScale' :true
	});
}
//平台管理--平台产品
function plat_product(plat_proId){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Plat/plat_product?plat_proId="+plat_proId,
		'autoScale' :true
	});
}

//产品管理--  --跳转列表
var product_jump_id="";
function productJump(pid){
	product_jump_id=pid;
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Product/product_jump.html",
		'autoScale' :true
	});
}
//产品管理--  -- 购买清单
var product_buy_id="";
function productBuy(pid){
	product_buy_id=pid;
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Product/product_buy.html",
		'autoScale' :true
	});
}
//跳转操作
function openPlatUpdate(jump_opearId){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Plat/edit_jumpOpear?jump_opearId="+jump_opearId,
		'autoScale' :true
	});
}
//产品管理-----编辑
function productEdit(pid,type){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Product/product_edit?product_id="+pid+"&product_type="+type,
		'autoScale' :true
	});

}

//修改角色 
function editRole(rid){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/System/role_edit?role_id="+rid,
		'autoScale' :true
	});
}
//角色列表-- --添加用户
function addRoleUser (rid) {
	top.$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/System/addRoleUser?role_id="+rid,
		'autoScale' :true
	});
}
//角色列表--  --修改权限
function editPower (rid) {
	layer.open({
		type:2,
		title: '修改权限',
		shadeClose:true,	
		skin: 'layer-ext-espresso',
		area: ['560px', '560px'],
		content: App+"/Admin/System/editPower?role_id="+rid
	});
}
//页面元素-- --修改
function editRight (rid) {
	layer.open({
		type:2,
		title: '修改',
		shadeClose:true,	
		skin: 'layer-ext-espresso',
		area: ['560px', '260px'],
		content: App+"/Admin/System/editRight?right_id="+rid
	});

}
//页面元素-- --删除
function deleteRight(rid){
	if(rid){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/System/deleteRight',
		  	dataType:'json',
		  	async:true,
		  	data:{right_id:rid},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_right_list();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}

//------合作渠道---删除
function deleteFLink (lid) {
	if(lid){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/System/deleteFLink',
		  	dataType:'json',
		  	async:true,
		  	data:{link_id:lid},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_partner_show();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//------大类字典---删除
function delDicBig(did) {
	if(did){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/System/delDicBig',
		  	dataType:'json',
		  	async:true,
		  	data:{dicbig_id:did},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_dicbig_show();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//页面元素-- --修改
function editDicBig(rid) {
	top.layer.open({
		type:2,
		title: '修改',
		shadeClose:true,	
		skin: 'layer-ext-espresso',
		area: ['560px', '260px'],
		content: App+"/Admin/System/editDicBig?dicbig_id="+rid
	});
}
//------主页轮播---删除
function delBanner(did) {
	if(did){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/System/delBanner',
		  	dataType:'json',
		  	async:true,
		  	data:{banner_id:did},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_banner_show();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//------短信模板---删除
function delTemplate(tid) {
	if(tid){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/System/delTemplate',
		  	dataType:'json',
		  	async:true,
		  	data:{template_id:tid},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_template_show();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//------小类字典---删除
function delDicSmall(tid) {
	if(tid){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/System/delDicSmall',
		  	dataType:'json',
		  	async:true,
		  	data:{dicsmall_id:tid},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_dicSmall_show();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//------区域字典---删除
function deleteArea(aid) {
	if(aid){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/System/deleteArea',
		  	dataType:'json',
		  	async:true,
		  	data:{area_id:aid},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_area_list();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//------主页展示---删除
function deleteHomepage(aid) {
	if(aid){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/Product/delHomepage',
		  	dataType:'json',
		  	async:true,
		  	data:{homepage_id:aid},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			reload_homepageList_show();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}

//赚乐扒公告修改

function openUpdateAnnoun(announId){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Article/updateAnnoun?announId="+announId,
		'autoScale' :true
	});
}
//删除赚乐扒公告
function deleteAnnoun(deleteAnnounId){
	if(deleteAnnounId){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/Article/deleteAnnoun',
		  	dataType:'json',
		  	async:true,
		  	data:{deleteAnnounId:deleteAnnounId},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_announ();
                    top.layer.closeAll();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}

//加入我们修改

function editJoinus(joinusId){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Article/updateJoinUs?joinusId="+joinusId,
		'autoScale' :true
	});
}

//删除加入我们
function deleteJoinus(deleteJoinusId){
	if(deleteJoinusId){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/Article/deleteJoinus',
		  	dataType:'json',
		  	async:true,
		  	data:{deleteJoinusId:deleteJoinusId},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_joinus();
                    top.layer.closeAll();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}

//活动管理
function openActiveForm(activeId){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/Active/updateActive?activeId="+activeId,
		'autoScale' :true
	});
}

//理财学院-幻灯新闻
function openNewsForm(slideNewId){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/News/updateSlideNews?slideNewId="+slideNewId,
		'autoScale' :true
	});
}
//理财学院-删除幻灯新闻
function deleteNewsForm(slideNewId){
	if(slideNewId){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/News/deleteSlideNews',
		  	dataType:'json',
		  	async:true,
		  	data:{slideNewId:slideNewId},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_slideNews();
                    top.layer.closeAll();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//修改理财新闻
function openNews(newsId) {
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/News/updateNews?newsId="+newsId,
		'autoScale' :true
	});
}

//理财学院-删除幻灯新闻
function deleteNews(newId){
	if(newId){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/News/deleteNews',
		  	dataType:'json',
		  	async:true,
		  	data:{newId:newId},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_News();
                    top.layer.closeAll();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//修改媒体报道
function openMediaForm(mediaId) {
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/News/updateMedia?mediaId="+mediaId,
		'autoScale' :true
	});
}

//删除媒体报道
function deleteMediaForm(demediaId){
	if(demediaId){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/News/deleteMedia',
		  	dataType:'json',
		  	async:true,
		  	data:{demediaId:demediaId},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_Media();
                    top.layer.closeAll();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//修改热门标签
function openTagsForm(tagId){
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/News/updateTag?tagId="+tagId,
		'autoScale' :true
	});
}
//删除热门标签
function deleteTagsForm(deleteId){
	if(deleteId){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/News/deleteTags',
		  	dataType:'json',
		  	async:true,
		  	data:{delete_id:deleteId},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_Tag();
                    top.layer.closeAll();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}
//修改新闻分类
function openClassifyForm (classifyId) {
	$.fancybox({
		'type':'ajax',
		'href':App+"/Admin/News/updateClassify?classifyId="+classifyId,
		'autoScale' :true
	});
}
//删除新闻分类
function deleteClassifyForm (classifyId) {
	if(classifyId){
		top.layer.confirm('确定删除？', {
		  btn: ['确定','取消'] 
		}, function(){
		  $.ajax({
		  	type:'post', 
		  	url:App+'/Admin/News/deleteClassify',
		  	dataType:'json',
		  	async:true,
		  	data:{classifyId:classifyId},
		  	success:function(data){
		  		if(data == 1){
		  			top.layer.msg('删除成功',{icon: 1,time: 1000});
		  			top.rightContent.window.reload_classifyList();
                    top.layer.closeAll();
		  		}else{
		  			top.layer.msg('删除失败',{icon: 2,time: 1000});
		  		}
		  	}
		  });
		}, function(){
		  
		});
	}
}