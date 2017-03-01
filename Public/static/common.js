$(function(){
	$('.nav_center >ul >li >a').siblings().addClass('.selected').removeClass('.selected');

	var dheight = document.body.clientHeight-94;
	$('.layout-button-left').on('click',function(){
		$('.main_left_show').css('position','absolute');
		$('.main_rig').css('margin-left','5px');
		
		$('.main_left_show').animate({
			left:-144,
			top:0,
		},function(){
			$('#layout-left').css('height',dheight);
			$('#layout-left').show(300);
			$('.main_left').css('width','32px');
		});
	});
	var left_height = document.body.clientHeight-125;
	$('.panel').css('height',left_height);
	var left_height2 = document.body.clientHeight-131;
	$('.panel-default').css('height',left_height2);
	// var dwidth = $(document).width() - 140;
	// var dheight = document.body.clientHeight-94;
	// var dwidth2 = $(document).width() - 72;
	// $("#rightContent").contents().find("#list4").setGridWidth(dwidth2);
	// $("#rightContent").contents().find("#gbox_list4").css('width',dwidth2);
	// $("#rightContent").contents().find("#gridPager").css('width',dwidth2);
	$('.layout-button-rig').on('click',function(){

			$('.layout-expand').hide(function(){
				$('.main_left_show').animate({
					left:0,
					top:0,
					width:140
				},function(){
					$('.main_left_show').css('position','relative');
					$('.main_left').css('width','140px');
					$('.main_rig').css('margin-left','5px');
				});
			});
	});
	//类型分类
	$('.type_left > li').click(function(){
		 $(this).addClass('selected')
                .siblings().removeClass('selected')
                .end()
                .find(":radio").attr("checked", true);
	});
	$('.type_left > li:last').css('border-bottom','solid 1px #95B8E7');

	//类型分类-帮助中心
	var lwidth = $(document).width() - 324;
	$('.main_type_rig').css('width',lwidth+2+'px');
	$('#arrow_left').on('click',function(){
		var lwidth2 = $(document).width() - 40;
		$('.main_type_left').css('position','absolute');
		$('.main_type_left').animate({
			left:-321,
			top:0,
		},function(){
			
		});
		var rheight = $(document).height()-130;
			$('.small_type_left').css('width','32px');
			$('.small_type_left').css('height',rheight+'px');
			$('.small_type_left').show(300);
			$('.main_type_rig').animate({width:lwidth2+2+'px',marginLeft:'5px'},300);
			$("#type_show").setGridWidth(lwidth2);
	});
	$('#arrow_rig').on('click',function(){
		$('.small_type_left').hide(300);
		$('.main_type_left').animate({
			left:0,
			top:0,
		},function(){
			$('.main_type_left').css('position','relative');
			$('.main_type_rig').css('width',lwidth+2+'px');
			$('.main_type_rig').animate({marginLeft:'5px'});
			$("#type_show").setGridWidth(lwidth);
		});
	});

	//对账管理
	var bwidth = $(document).width() - 540;
	$('.main_bill_rig').css('width',bwidth+2+'px');
	$('#bill_arrow_left').on('click',function(){
		var lwidth2 = $(document).width() - 40;
		$('.main_bill_left').css('position','absolute');
		$('.main_bill_left').animate({
			left:-540,
			top:0,
		},function(){
			
		});
		var rheight = $(document).height()-130;
			$('.small_type_left').css('width','32px');
			$('.small_type_left').css('height',rheight+'px');
			$('.small_type_left').show(300);
			$('.main_bill_rig').animate({width:lwidth2+2+'px',marginLeft:'5px'},300);
			$("#type_show").setGridWidth(lwidth2);
	});
	$('#arrow_rig').on('click',function(){
		$('.small_type_left').hide(300);
		$('.main_bill_left').animate({
			left:0,
			top:0,
		},function(){
			$('.main_bill_left').css('position','relative');
			$('.main_bill_rig').css('width',bwidth+2+'px');
			$('.main_bill_rig').animate({marginLeft:'5px'});
			$("#type_show").setGridWidth(bwidth);
		});
	});
});
