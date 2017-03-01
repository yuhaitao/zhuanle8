<?php
namespace Home\Controller;
use Think\Controller;

class ProductController extends BaseController {
    public function index(){
        //$this->display();
    }
    
    //产品列表
    public function prod_list()
    {
        //年化利率范围
        $startAnnualIncomeRate=$_POST['startAnnualIncomeRate'];
        $endAnnualIncomeRate=$_POST['endAnnualIncomeRate'];
        //投资期限范围
        $startInvestLimit=$_POST['startInvestLimit'];
        $endInvestLimit=$_POST['endInvestLimit'];
        $limitType=$_POST['limitType'];
        //投资分类 首投 复投
        $ReCast=$_POST['ReCast'];
        //平台
        $platId=$_POST['platId'];
        //排序方式
        $order=$_POST['order'];
        //查看更多
        $more=$_POST['more'];
        // 查询条件
        $where=array();
        $where['a.PRODUCT_TYPE']=$_POST['product_type'];
        //-----
        if ($startAnnualIncomeRate) {
            $where['a.UNIT_RATE']=array("EGT",$startAnnualIncomeRate);
        }
        if ($endAnnualIncomeRate) {
            $where['a.UNIT_RATE']=array("ELT",$endAnnualIncomeRate);
        }
        //-----
        if ($limitType==1) {
            $where['a.INVEST_DAY']=array("ELT",$endInvestLimit);
        }elseif ($limitType==2) {
            if ($startInvestLimit) {
                $cond=array();
                $cond['a.INVEST_MONTH']=array("EGT",$startInvestLimit);
                $cond['a.INVEST_DAY']=array("EGT",intval($startInvestLimit)*30);
                $cond['_logic']="or";
                $where['_complex']=$cond;
            }
            if ($endInvestLimit) {
                $cond=array();
                $cond['a.INVEST_MONTH']=array("ELT",$endInvestLimit);
                $cond['a.INVEST_DAY']=array("ELT",intval($endInvestLimit)*30);
                $cond['_logic']="or";
                $where['_complex']=$cond;
            }
        }
        //-------
        if ($ReCast) {
            $where['a.RE_CAST']=$ReCast;
        }
        //--------
        if ($platId) {
            $where['a.PLAT_SHORTNAME']=$platId;
        }
        //-------
        if ($order==0||$order==1) {
            $order="case when a.RELEASE_STATUS = 0 then 1 else 2 end,a.ADD_TIME";
        }elseif ($order==2) {
            $order="case when a.RELEASE_STATUS = 0 then 1 else 2 end,a.UNIT_RATE";
        }elseif ($order==3) {
            $order="case when a.RELEASE_STATUS = 0 then 1 else 2 end,a.INVEST_MONTH desc,a.INVEST_DAY";
        }
        $where['a.RELEASE_STATUS']=array("in","0,3,9");
        //------
        $limit=12*($more+1);
        $m=M("cq_product a");
        $list=$m->join("cq_plat b on a.PLAT_SHORTNAME=b.PLAT_ID")->field("b.PLAT_LOGO,a.PRODUCT_ID,a.TARGET_NAME,a.ANNUAL_INCOME_RATE,a.CQ_RATE,a.UNIT_RATE,a.START_INVEST_AMOUNT,a.INVEST_MONTH,a.INVEST_DAY,a.RELEASE_STATUS,a.RE_CAST")->where($where)->order($order." desc")->limit($limit)->select();
        $response->rows=array();
        foreach ($list as $key => $value) {
            $response->rows[$key]['platLogo']=$value['plat_logo'];
            $response->rows[$key]['productId']=$value['product_id'];
            $target_name=$value['target_name'];
            $response->rows[$key]['targetName']=$target_name;
            $response->rows[$key]['annualIncomeRate']=$value['annual_income_rate'];
            $response->rows[$key]['cqRate']=$value['cq_rate'];
            $response->rows[$key]['unitRate']=$value['unit_rate'];
            $response->rows[$key]['startInvestAmount']=$value['start_invest_amount'];
            $response->rows[$key]['investMonth']=$value['invest_month'];
            $response->rows[$key]['investDay']=$value['invest_day'];
            if ($value['release_status']==0&&$value['re_cast']==2) {
                $user_info=session("user_info");
                $user_id=$user_info['user_id'];
                if ($user_id) {
                    $oop['USER_ID']=$user_id;
                    $oop['PRODUCT_ID']=$value['product_id'];
                    $oop['BUY_MONEY']=array("gt",0);
                    $oop['IS_DEL']=0;
                    $last=M("cq_product_buy")->where($oop)->find();
                    if ($last) {
                        $response->rows[$key]['releaseStatus']=1000;
                    }else{
                        $response->rows[$key]['releaseStatus']=$value['release_status'];
                    }
                }else{
                    $response->rows[$key]['releaseStatus']=$value['release_status'];
                }
            }else{
                $response->rows[$key]['releaseStatus']=$value['release_status'];
            }
        }
        $this->ajaxReturn($response,'JSON');
    }
    //产品列表页
    public function Product()
    {
        $plat_id=$_GET['plat_id'];
        if ($plat_id) {
            $plat_name=M("cq_plat")->where("PLAT_ID=$plat_id and IS_DEL=0")->getField("PLAT_SHORTNAME");
            $str=$plat_name;
            $product_type="1";
            $this->assign("plat_id",$plat_id);
            $this->assign("plat_name",$plat_name);
        }else{
            $product_type=$_GET['product_type'];
            $str1=$product_type;
            if ($product_type=="day") {
                $product_type="1";
                $str="稳健型产品";
            }elseif ($product_type=="super") {
                $product_type="2";
                $str="精选产品";
            }elseif ($product_type=="high") {
                $product_type="3";
                $str="高收益产品";
            }else{
                 exit;
            }
        }
        
        //跳转记录
        $this->lc_log($str,$str1,"Product");
        $site_title=$str."-赚乐扒";//网站title
        $this->assign('site_title',$site_title); 
        $this->assign("product_type",$product_type);
        $m=M("cq_plat");
        $result=$m->where("IS_DEL=0")->field("PLAT_ID,PLAT_SHORTNAME")->select();
        $this->assign("platList",$result);
        $this->display();
    }
    //标的详情页
    public function ProInfo(){

    	$detail_id = $_GET['detail_id'];
        $this->assign("product_id",$detail_id);
    	$model = M('cq_product');
    	$where = array();
    	$where['a.IS_DEL'] = 0;
    	$where['a.PRODUCT_ID'] = $detail_id;

    	$result = $model->table('cq_product a')->join('cq_plat as b ON a.PLAT_SHORTNAME = b.PLAT_ID')->field('a.PRODUCT_TYPE,a.TARGET_NAME,a.ANNUAL_INCOME_RATE,a.CQ_RATE,a.UNIT_RATE,a.INVEST_MONTH,a.INVEST_DAY,a.START_INVEST_AMOUNT,a.RELEASE_STATUS,a.RE_CAST,a.END_INVEST_AMOUNT,a.PRODUCT_SUM,a.PRODUCET_INFOR,a.BID_SECURITY_TYPE,a.BID_SECURITY_OTHER,a.REBATE_TYPE,a.REBATE_OTHER,a.INTEREST_TYPE,a.INTEREST_OTHER,a.JUMP_LINK,b.PLAT_ID,b.PLAT_SHORTNAME,b.PLAT_LOGO,b.PLATFORM_SITE,b.ONLINE_TIME,b.REGION_PROVINCE,b.REGION_CITY,b.REGISTER_MONEY,b.COR_REPRESENT,b.COMPANY_NAME,b.RECHARGE_COST,CASH_COST,b.RISK_MONEY,b.FINANCE_DEPOSIT,b.COMPANY_ADDRESS,b.TRANFER,b.PLAT_BRIEF,b.INVEST_GUIDE')->where($where)->find();

    	$lbnav = '';
    	if($result['product_type'] == 1){
    		$lbnav = '稳健型产品';
    	}
    	if($result['product_type'] == 2){
    		$lbnav = '精选产品';
    	}
    	if($result['product_type'] == 3){
    		$lbnav = '高收益产品';
    	}
    	//平台年化
        if(!is_float($result['annual_income_rate'])){
            $result['annual_income_rate'] = number_format($result['annual_income_rate'],1);
        }
        //赚乐扒加息
        if(!is_float($result['cq_rate'])){
            $result['cq_rate'] = number_format($result['cq_rate'],1);
        }
        //综合年化
        if(!is_float($result['unit_rate'])){
            $result['unit_rate'] = number_format($result['unit_rate'],1);
        }

        //期限
    	$time = '';
    	if(!$result['invest_month'] == ''){
    		$time = '<span class="text_seven">'.$result['invest_month'].'</span>个月';
    	}elseif(!$result['invest_day'] == ''){
    		$time = '<span class="text_seven">'.$result['invest_day'].'</span>天';
    	}
    	//计息方式
    	$interest = $result['interest_type'];
    	if($interest == 1 ){
    		$interest = '满标计息';
    	}
    	if($interest == 2){
    		$interest = '当日计息';
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
    	//地区
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

        //风险储备金
        if($result['risk_money'] == 1){
        	$result['risk_money'] = '有';
        }
        if($result['risk_money'] == 2){
        	$result['risk_money'] = '无';
        }
        //充值费用
        if($result['recharge_cost'] == 1){
        	$result['recharge_cost'] = '有';
        }
        if($result['recharge_cost'] == 2){
        	$result['recharge_cost'] = '无';
        }
        //提现费用
        if($result['cash_cost'] == 1){
        	$result['cash_cost'] = '有';
        }
        if($result['cash_cost'] == 2){
        	$result['cash_cost'] = '无';
        }
        //转让功能
        if($result['tranfer'] == 1){
        	$result['tranfer'] = '有';
        }
        if($result['tranfer'] == 2){
        	$result['tranfer'] = '无';
        }
        //平台介绍
        $plat_info = base64_decode($result['plat_brief']);
        //投资攻略
        $invest_guide = base64_decode($result['invest_guide']);
        //是否可以点击立即投资
        $click = 0;
        //用户
        $user_info=session("user_info");
        $userID=$user=$user_info['user_id'];
        if (!$user) {
            $user=0;
        }else{
            $userData=M("lc_user")->where("USER_ID=$user and IS_DEL=0")->field('MOBILE')->find();
            $user=$userData['mobile'];
        }
        $this->assign('user',$user);
        //复投方式
        $re_cast = '';
        if($result['re_cast'] == 1){
        	$re_cast = "<img src='".__ROOT__."/Public/images/home/icon/fu_tig.png' />";
            if ($result['release_status']==0) {
                $click=1;
            }else{
                $click=0;
            }
        }
        if($result['re_cast'] == 2){
        	$re_cast = "<img src='".__ROOT__."/Public/images/home/icon/shou_tig.png' />";
            if ($result['release_status']==0) {
                if ($user > 0) {
                    $oop['USER_ID']=$userID;
                    $oop['PRODUCT_ID']=$detail_id;
                    $oop['BUY_MONEY']=array("gt",0);
                    $oop['IS_DEL']=0;
                    $last=M("cq_product_buy")->where($oop)->find();
                    if ($last) {
                        $click=2;
                    }else{
                        $click=1;
                    }
                }else{
                    $click=1;
                }
                
            }else{
                $click=0;
            }
        }

        $link=M('cq_plat_handle')->where("PLAT_ID=".$result['plat_id']." and IS_DEL=0")->field("HANDLE_CONTROLLER")->find();
        $this->assign("regLink",$link['handle_controller']);

        $this->assign("click",$click);
    	$this->assign('time',$time);	//投资期限
    	$this->assign('lbnav',$lbnav);	//产品类型
    	$this->assign('interest',$interest);	//计息方式
    	$this->assign('guarantee',$guarantee);	//保障方式
        $this->assign('repayment',$repayment);  //还款方式
        $this->assign('address',$address);    //省 市区
        $this->assign('plat_info',$plat_info);    //平台介绍
        $this->assign("invest_guide",$invest_guide);
        $this->assign('re_cast',$re_cast);    //复投方式
        $this->assign('result',$result);
        $site_title=$result['target_name']."-赚乐扒";//网站title
        $this->assign('site_title',$site_title);    
    	
        $str="标的详情：[".$result['plat_shortname']."]".$result['target_name'];
        $str1="product/".$detail_id;
        $str2="ProInfo";
        $this->lc_log($str,$str1,$str2);

        $this->display();
    }

    //保存 点击立即投资 后需要保存的相关信息
    public function saveInvestRecord()
    {
        $product_id=$_GET['product_id'];
        $user_info=session("user_info");
        $user_id=$user_info['user_id'];//获取用户id
        $nowTime=date("Y-m-d H:i:s",time());
        $data['PRODUCT_ID']=$product_id;//产品id
        $data['USER_ID']=$user_id;
        $data['JUMP_TIME']=$nowTime;
        $data['ADD_TIME']=$nowTime;
        $data['ADD_USER']=$user_id;
        $data['IS_DEL']=0;
        //保存 立即投资 跳转记录
        M('cq_product_jump')->add($data);
        $buy=M("cq_product_buy")->where("PRODUCT_ID=$product_id and USER_ID=$user_id and IS_DEL=0")->field("PRODUCT_BUY_ID")->find();
        $re_cast=M("cq_product")->where("PRODUCT_ID=$product_id and IS_DEL=0")->getField("RE_CAST");
        //如果没有购买这个标的信息
        if (!$buy['product_buy_id'] || $re_cast==1) {
            $time=date("Y-m-d",time());
            $rule=M('cq_serial_rule')->where("SERIAL_TYPE='01' and SERIAL_DAY='".$time."'")->field("SERIAL_NUM")->find();
            //如果没有记录
            $number="";
            if (!$rule['serial_num']) {
                $param['SERIAL_TYPE']='01';
                $param['SERIAL_DAY']=$time;
                $param['SERIAL_NUM']='1';
                M('cq_serial_rule')->add($param);
                $number=1;
            }else{
                M('cq_serial_rule')->where("SERIAL_TYPE='01' and SERIAL_DAY='".$time."'")->setInc("SERIAL_NUM");
                $number=intval($rule['serial_num'])+1;
            }
            $serial_no="01".date("ymd").sprintf("%05d",$number);
            $record['PRODUCT_ID']=$product_id;
            $record['USER_ID']=$user_id;
            $record['SERIAL_NO']=$serial_no;
            $record['ADD_TIME']=$nowTime;
            $record['IS_DEL']=0;
            $record['HANDLE_STATUS']=2;
            M('cq_product_buy')->add($record);
        }
    }

    //添加 跳转记录
    public function lc_log($str,$str1,$str2)
    {
        $ip = get_client_ip();//IP地址获取
        $user_info=session("user_info");
        $user_id=$user_info['user_id'];//获取用户id
        if ($user_id) {
            $data['USER_ID']=$user_id;
        }
        $data['USER_IP']=$ip;
        $data['USER_LOG']=$str;
        $data['LOG_DATE']=date("Y-m-d H:i:s")." ".substr(date("l"), 0,3);
        $data['LOG_MTHOD']=$str2;
        $data['LOG_URL']="www.zhuanle8.com/".$str1.".html";
        M('lc_log')->add($data);
    }
}