<?php
namespace Home\Controller;
use Think\Controller;
/*
 * 超级返利
 */
class SuperRebateController extends BaseController {
	
    public function index(){
    }

    public function SuperList(){
        $this->display();
    }
    //超级返利详情页
    public function SuperDetil(){

    	$sr_id  = $_GET['sr_id'];
    	// dump($sr_id);exit;
    	$model = M('cq_product');
    	$where = array();
    	$where['a.IS_DEL'] = 0;
    	$where['a.PRODUCT_ID'] = $sr_id;

    	$result = $model->table('cq_product a')->join('cq_plat as b ON a.PLAT_SHORTNAME = b.PLAT_ID')->field('a.TARGET_NAME,a.ANNUAL_INCOME_RATE,a.INVEST_MONTH,a.INVEST_DAY,a.START_INVEST_AMOUNT,a.END_INVEST_AMOUNT,a.PRODUCT_SUM,a.CQ_RATE,a.UNIT_RATE,a.BID_SECURITY_TYPE,a.BID_SECURITY_OTHER,a.REBATE_TYPE,a.REBATE_OTHER,a.INTEREST_TYPE,a.INTEREST_OTHER,b.PLAT_SHORTNAME,b.PLAT_LOGO,b.PLATFORM_SITE,b.ONLINE_TIME,b.REGION_PROVINCE,b.REGION_CITY,b.REGISTER_MONEY')->where($where)->find();

        
    	$time = '';
    	if(!$result['invest_month'] == ''){
    		$time = '<span class="text_seven">'.$result['invest_month'].'</span>个月';
    	}elseif(!$result['invest_day'] == ''){
    		$time = '<span class="text_seven">'.$result['invest_day'].'</span>天';
    	}
    	//计息方式
    	$interest = $result['interest_type'];
    	if($interest == 1 ){
    		$interest = '等额本息';
    	}
    	if($interest == 2){
    		$interest = '每月付息，到期还本';
    	}
    	if($interest == 3){
    		$interest = $result['interest_other'];
    	}
    	if($interest == 4){
    		$interest = '次日计息';
    	}
    	if($interest == 5){
    		$interest = '满标次日计息';
    	}
    	//保障方式
    	$guarantee = $result['bid_security_type'];
    	if($guarantee == 1){
    		$guarantee = '本息保障';
    	}
    	if($guarantee == 2){
    		$guarantee = '本金保障';
    	}
    	if($guarantee == 3){
    		$guarantee = $result['bid_security_other'];
    	}
    	//还款方式
    	$repayment = $result['rebate_type'];
    	if($repayment == 1){
    		$repayment = '等额本息';
    	}
    	if($repayment == 2){
    		$repayment = '每月付息，到期还本';
    	}
    	if($repayment == 3){
    		$repayment = '到期还本付息';
    	}
    	if($repayment == 4){
    		$repayment = $result['rebate_other'];
    	}
        $where_b = array();
        $where_b['AREA_ID'] = $result['region_province'];
        $where_b['IS_DEL']  = 0;
        $m = M('cq_area');
        $province = $m->field('AREA_NAME')->where($where_b)->find();

        $where_c = array();
        $where_c['AREA_ID'] = $result['region_city'];
        $where_c['IS_DEL']  = 0;
        $m = M('cq_area');
        $city = $m->field('AREA_NAME')->where($where_c)->find();
        $address = '';

        if($province['area_name'] == $city['area_name']){
            $address =$city['area_name'];
        }else{
            $address =$province['area_name'].$city['area_name'];
        }
        
    	// dump($city);exit;
    	// dump($model->getLastSql());exit;
    	$this->assign('superdetail',$result);
    	$this->assign('time',$time);	//投资期限
    	$this->assign('interest',$interest);	//计息方式
    	$this->assign('guarantee',$guarantee);	//保障方式
        $this->assign('repayment',$repayment);  //还款方式
        $this->assign('address',$address);    //省 市区
    	$this->display();
    }
   
    //判断用户是否登录
    public function check_islogin(){
        $flag = isLogin();
        //$this->ajaxReturn($flag,'JSON');
        if($flag == false){
            $this->ajaxReturn(0,'JSON');
        }else{
            $this->ajaxReturn(1,'JSON');
        }
    }

}