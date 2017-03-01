<?php
namespace Admin\Controller;
use Think\Controller;
class UserController extends BaseController {
    public function index(){
    	
    }
    public function user(){
    	$this->display();
    }
    //用户列表数据
    public function userlist(){
    	$page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        if($sidx=='user_id') $sidx = 'a.USER_ID';
        if(!$sord) $sord = 'desc';//默认倒序
        //查询条件 开始
        $username=$_POST['username'];
        $nickname=$_POST['nickname'];
        $mobile = $_POST['mobile'];
        $where=array();
        if($username){
            $where['a.USER_NAME']=array("like","%".$username."%");
        }
        if($nickname){
            $where['a.NICK_NAME']=array("like","%".$nickname."%");
        }
        if($mobile){
            $where['a.MOBILE']=array("like","%".$mobile."%");
        }
        $Model = M('lc_user');
        /*总记录数*/
        $count = $Model->table('lc_user a')->join('left join cq_user_finance b on a.USER_ID = b.USER_ID')->where('(a.USER_TYPE=1 OR a.USER_TYPE=4) AND a.IS_DEL=0 AND b.IS_DEL=0')->where($where)->count();
        //查询条件结束
        //根据记录数分页
        if($count > 0){
            $total_pages = ceil($count/$limit);
        }else{
            $total_pages=0;
        }
        if ($page > $total_pages)    
            $page = $total_pages;   
        $start = $limit * $page - $limit;   
        if ($start < 0 ) 
            $start = 0;
        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数
        $list = $Model->table('lc_user a')->join('left join cq_user_finance b on a.USER_ID = b.USER_ID')->where('(a.USER_TYPE=1 OR a.USER_TYPE=4) AND a.IS_DEL=0 AND b.IS_DEL=0')->where($where)->field('a.USER_ID,a.MOBILE,a.USER_NAME,a.NICK_NAME,b.BUY_AMONUT,b.CASH_AMOUNT,a.ADD_TIME,a.USER_REG_IP')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
        	$responce->rows[$key]['user_id']=$value['user_id'];
        	$responce->rows[$key]['mobile']="<a href='javascript:openUserTable(".$value['user_id'].");'>".$value['mobile']."</a>";
        	$responce->rows[$key]['user_name']=$value['user_name'];
        	$responce->rows[$key]['nick_name']=$value['nick_name'];
        	$responce->rows[$key]['buy_amonut']=$value['buy_amonut']*1;
        	$responce->rows[$key]['cash_amount']=$value['cash_amount'];
        	$responce->rows[$key]['add_time']=$value['add_time'];
            $reg=M('cq_invitation_friends')->where('USER_ID ='.$value['user_id'])->find();
        	$reg_type='本人注册';
        	if($reg > 0){
        		 $reg_type='好友邀请';
        	}
        	$responce->rows[$key]['reg_type']=$reg_type;
        	$responce->rows[$key]['user_reg_ip']=$value['user_reg_ip'];
            $responce->rows[$key]['edit'] = "<a href='javascript:top.goUser(".$value['user_id'].");'>修改</a>";
            $responce->rows[$key]['delete'] = "<a href='javascript:deleteUser(".$value['user_id'].");'>删除</a>";
            $responce->rows[$key]['tiaozhuan'] = "<a href='javascript:top.openProductJump(".$value['user_id'].");'>跳转列表</a>";
            $responce->rows[$key]['buy'] = "<a href='javascript:top.openProductBuy(".$value['user_id'].");'>购买清单</a>";
            $responce->rows[$key]['meg'] = "<a href='javascript:top.financeRecoed(".$value['user_id'].");'>资产信息</a>";
            $responce->rows[$key]['jilu'] = "<a href='javascript:top.openBrowse(".$value['user_id'].");'>浏览记录</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    /*添加新用户-检查手机号是否重复*/
    public function checkphone(){
        $cur_phone = $_POST['cur_phone'];
        $model = M('lc_user');
        $where = array();
        $where['MOBILE'] = $cur_phone;
        $old_phone = $model->where($where)->find();
        if($old_phone){
            $this->ajaxReturn(0,'JSON');
        }else{
            $this->ajaxReturn(1,'JSON');
        }
    }
    /*添加新用户*/
    public function insert_user(){
        $data['USER_NAME'] = $_POST['username'];
        $data['NICK_NAME'] = $_POST['nickname'];
        $data['BIRTH_DATE'] = $_POST['birth_date'];
        $data['MOBILE'] = $_POST['phone'];
        $data['TELEPHONR'] = $_POST['telephonr'];
        $data['IDENTITY'] = $_POST['per_num'];
        $data['USER_TYPE'] = $_POST['os'];
        $model = M('lc_user');
        $model_id = $model->add($data);
        if($model_id){
            $this->ajaxReturn($model_id,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }

    }
    /*修改用户-获取用户信息*/
    public function revise_user(){
        $model = M('lc_user');
        $where = array();
        $revise_id = I('revise_id'); //获取用户id
        $where['USER_ID'] = $revise_id;
        $revise_list = $model->where($where)->find();
        $options = '';
        $revise_type = $revise_list['user_type'];
        if($revise_type == 2){
            $options = '平台用户';
        }elseif($revise_type == 3){
            $options = '渠道用户';
        }
        // dump($revise_type);exit;
        $this->assign('revise_list',$revise_list);
        $this->assign('options',$options);
        $this->display();
    }
    /*修改用户-保存用户信息*/
    public function save_user(){
        $revise_id = $_POST['revise_id'];
        $data['USER_NAME'] = $_POST['username'];
        $data['NICK_NAME'] = $_POST['nickname'];
        $data['BIRTH_DATE'] = $_POST['birth_date'];
        $data['MOBILE'] = $_POST['phone'];
        $data['TELEPHONR'] = $_POST['telephonr'];
        $data['IDENTITY'] = $_POST['per_num'];
        $data['USER_TYPE'] = $_POST['os'];
        $model = M('lc_user');
        $where = array('USER_ID' => $revise_id);
        $model ->where($where)->save($data);
        if($model){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
    /*删除用户*/
    public function delete_user(){
        $delete_id = $_POST['delete_id'];
        $model = M('lc_user');
        $model->IS_DEL = 1; 
        $where = array('USER_ID' => $delete_id);
        $model ->where($where)->save();
        if($model){
            $this->ajaxReturn('1','JSon');
        }else{
            $this->ajaxReturn('0','JSON');
        }

    }
    /*跳转列表*/
    public function userjump(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        //传值接收
        $user_id = $_POST['jump_id'];
        //查询条件
        $where = array();
        $where['a.USER_ID'] = $user_id;
        $where['a.IS_DEL'] = 0;

        $m=M('cq_product_jump');
        /*总记录数*/
        $count = $m->where('USER_ID = '.$user_id.' AND IS_DEL = 0')->count();
        //根据记录数分页
        if($count > 0){
            $total_pages = ceil($count/$limit);
        }else{
            $total_pages=0;
        }
        if ($page > $total_pages)    
            $page = $total_pages;   
        $start = $limit * $page - $limit;   
        if ($start < 0 ) 
            $start = 0;
        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数
        $list=$m->table('cq_product_jump a')->join('cq_product b on a.PRODUCT_ID=b.PRODUCT_ID')->join('cq_plat c on b.PLAT_SHORTNAME=c.PLAT_ID')->field('a.JUMP_TIME,b.TARGET_NAME,b.PRODUCT_TYPE,b.INVEST_MONTH,b.START_INVEST_AMOUNT,b.END_INVEST_AMOUNT,b.CQ_RATE,b.UNIT_RATE,b.CQ_RED,b.THAWING_METHOD,b.FLOW_REBATE,b.RE_CAST,c.PLAT_SHORTNAME')->where($where)->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['plat_shortname'] = $value['plat_shortname'];//平台名
            $responce->rows[$key]['target_name'] = $value['target_name'];//标名
            $product_type = $value['product_type'];
            if($product_type==1){
                $product_type='稳健型产品';
            }elseif ($product_type==2) {
                $product_type='精选产品';
            }elseif($product_type==3){
                $product_type='高收益产品';
            }
            $responce->rows[$key]['product_type'] = $product_type;//标类型
            $responce->rows[$key]['invest_month'] = $value['invest_month'];//期限-月
            $responce->rows[$key]['start_invest_amount'] = $value['start_invest_amount'];//起投金额
            $responce->rows[$key]['end_invest_amount'] = $value['end_invest_amount'];//限投金额
            $responce->rows[$key]['cq_rate'] = $value['cq_rate'];//赚乐扒加息
            $responce->rows[$key]['unit_rate'] = $value['unit_rate'];//综合年化
            $responce->rows[$key]['cq_red'] = $value['cq_red'];//赚乐扒红包
            $thawing_method=$value['thawing_method'];
            if($thawing_method==1){
                $thawing_method='满标解冻';
            }elseif ($thawing_method==2) {
                $thawing_method='非满标解冻';
            }
            $responce->rows[$key]['thawing_method'] = $thawing_method;//解冻方式
            $flow_rebate=$value['flow_rebate'];
            if ($flow_rebate==1) {
                $flow_rebate='是';
            }elseif ($flow_rebate==2) {
                $flow_rebate='否';
            }
            $responce->rows[$key]['flow_rebate'] = $flow_rebate;//流标返利
            $re_cast=$value['re_cast'];
            if ($re_cast==1) {
                $re_cast='是';
            }elseif ($re_cast==2) {
                $re_cast='否';
            }
            $responce->rows[$key]['re_cast'] = $re_cast;//复投方式
            $responce->rows[$key]['jump_time'] = $value['jump_time'];//跳转时间
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //购买清单
    public function buylist(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.BUY_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        //传值接收
        $user_id = $_POST['buy_id'];
        //查询条件
        $where = array();
        $where['a.USER_ID'] = $user_id;
        $where['a.IS_DEL'] = 0;
        $where['a.HANDLE_STATUS'] = 1;
        $cond = array();
        $cond['USER_ID'] = $user_id;
        $cond['IS_DEL'] = 0;
        $cond['HANDLE_STATUS'] = 1;
        $m=M('cq_product_buy');
        /*总记录数*/
        $count = $m->where($cond)->count();
        //根据记录数分页
        if($count > 0){
            $total_pages = ceil($count/$limit);
        }else{
            $total_pages=0;
        }
        if ($page > $total_pages)    
            $page = $total_pages;   
        $start = $limit * $page - $limit;   
        if ($start < 0 ) 
            $start = 0;
        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数
        $list=$m->table('cq_product_buy a')->join('cq_product b on a.PRODUCT_ID = b.PRODUCT_ID')->join('cq_plat c on b.PLAT_SHORTNAME = c.PLAT_ID')->join('cq_user_finance_record d on a.SERIAL_NO = d.SERIAL_NO')->field('c.PLAT_SHORTNAME,b.TARGET_NAME,b.ANNUAL_INCOME_RATE,b.CQ_RATE,b.INVEST_MONTH,b.INVEST_DAY,a.BUY_MONEY,a.BUY_TIME,d.CASH_MONEY')->where($where)->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $info = '';

            if(!empty($value['invest_month'])){
                $info .= $value['invest_month']."个月";
            }
            if(!empty($value['invest_day'])){
                $info .= $value['invest_day']."天";
            }


            $responce->rows[$key]['plat_shortname'] = $value['plat_shortname'];//平台名
            $responce->rows[$key]['target_name'] = $value['target_name'];//标名
            $responce->rows[$key]['annual_income_rate'] = $value['annual_income_rate'];// 标年化
            $responce->rows[$key]['cq_rate'] = $value['cq_rate'];//赚乐扒加息
            $responce->rows[$key]['buy_money'] = $value['buy_money'];//
            $responce->rows[$key]['buy_state'] = "购买成功";//
            $responce->rows[$key]['buy_time'] = $value['buy_time'];//
            $responce->rows[$key]['cash_money'] = $value['cash_money'];//返利金额
            $responce->rows[$key]['invest_time'] = $info;   //投资期限
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //资产信息
    public function financerecord()
    {
        $finance_id=$_REQUEST['finance_id'];
        $this->assign("finance_id",$finance_id);
        $this->display();
    }
    //资产信息
    public function finance_record(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'OPERATE_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        //传值接收
        $user_id = $_POST['user_id'];
        $type = $_POST['type'];
        $freeze_status = $_POST['freeze_status'];
        $stime = $_POST['stime'];
        $etime = $_POST['etime'];
        //查询条件
        $where = array();
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        if($type){
            $where['TYPE'] = $type;
        }
        if($freeze_status){
            $where['FREEZE_STATUS'] = $freeze_status;
        }
        if($stime){
            $where['OPERATE_TIME'] = array('egt',$stime);
        }
        if($etime){
            $where['OPERATE_TIME'] = array('elt',$etime);
        }
        $m=M('cq_user_finance_record');
        /*总记录数*/
        $count = $m->where($where)->count();
        //根据记录数分页
        if($count > 0){
            $total_pages = ceil($count/$limit);
        }else{
            $total_pages=0;
        }
        if ($page > $total_pages)    
            $page = $total_pages;   
        $start = $limit * $page - $limit;   
        if ($start < 0 ) 
            $start = 0;
        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数
        $list=$m->field('SERIAL_NO,TYPE,CASH_MONEY,FREEZE_STATUS,FREEZE_TIME,UNFREEZE_TIME,OPERATE_TIME,REMARKS')->where($where)->order($sidx." ".$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['serial_no'] = $value['serial_no'];//流水号
            $type=$value['type'];
            $cash_money=$value['cash_money'];
            if($type==1){
                $type='产品红包';
            }elseif ($type==2) {
                $type='返现';
            }elseif ($type==3) {
                $type='系统红包';
            }elseif ($type==4) {
                $type='提现';
                $cash_money=$cash_money*-1;
            }elseif ($type==5) {
                $type='平台首投红包';
            }elseif ($type==6) {
                $type='好友返利';
            }elseif ($type==7) {
                $type='排名奖励';
            }else{
                $type='其他';
            }
            $freeze_status=$value['freeze_status'];
            if($freeze_status==1){
                $freeze_status='冻结';
            }elseif ($freeze_status==2) {
                $freeze_status='解冻';
            }
            $responce->rows[$key]['type'] = $type;//类型
            $responce->rows[$key]['cash_money'] = $cash_money;//提现金额
            $responce->rows[$key]['freeze_status'] = $freeze_status;//是否冻结
            $responce->rows[$key]['freeze_time'] = $value['freeze_time'];//冻结时间
            $responce->rows[$key]['unfreeze_time'] = $value['unfreeze_time'];//解冻时间
            $responce->rows[$key]['operate_time'] = $value['operate_time'];//操作时间
            $responce->rows[$key]['remarks'] = $value['remarks'];//备注
        }
        $this->ajaxReturn($responce,'JSON');
    }

    //浏览记录
    public function openBrowse(){
        $page = $_POST['page'];  //获取请求页数
        $limit = $_POST['rows']; //获取每页显示的记录数
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'LOG_ID';
        if(!$sord) $sord = 'desc';//默认倒序
        //传值接收
        $browse_id = $_POST['browse_id'];
        //查询条件
        $where = array();
        $where['USER_ID'] = $browse_id;
        $model = M('lc_log');
        $count = $model->where($where)->count(); // 获取浏览记录数
        if($count > 0){
            $total_pages = ceil($count/$limit);
        }else{
            $total_pages = 0;
        }
        if($page > $total_pages)
            $page = $total_pages;
        $start = $limit * $page - $limit;
        if($start < 0)
            $start = 0;
        $responce->page = intval($page); //当前页
        $responce->total = $total_pages; //总页数
        $responce->records = $count;     //总记录数

        $list = $model->field('USER_ID,USER_LOG,LOG_DATE')->where($where)->order($sidx." ".$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['user_log'] = $value['user_log'];//浏览记录
            $responce->rows[$key]['log_date'] = $value['log_date'];//浏览时间
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //导出
    public function export_excel(){
        import('Org.Util.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $savename='注册用户记录'.date("Y-m-d",time());  //生成的Excel文件文件名 
        $datetime = date('Y-m-d', time());        
        /*if (preg_match("/MSIE/", $ua)) {
            $savename = urlencode($savename); //处理IE导出名称乱码
        } */
        // excel头参数  
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Content-Type:application/force-download');
        header('Content-Type:application/vnd.ms-execl');
        header('Content-Type:application/octet-stream');
        header('Content-Type:application/download');
        header("Content-Disposition:attachment;filename='$savename.xls'");
        header('Content-Transfer-Encoding:binary');
        $list=M('lc_user')->field('MOBILE')->order('USER_ID ASC')->select();
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '手机号码');
        for ($i=0; $i < count($list); $i++) {
            $j=$i+2;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $j, $list[$i]['mobile']);
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output');
    }
    //好友列表
    public function friendList(){
       $this->display();
    }
    public function friends(){
        $page = $_POST['page'];  //获取请求页数
        $limit = $_POST['rows']; //获取每页显示的记录数
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'b.USER_ID';
        if(!$sord) $sord = 'desc';//默认倒序

        //查询条件 开始
        $nickname=$_POST['nickname'];
        $mobile = $_POST['mobile'];
        $where=array();
        if($nickname){
            $where['a.NICK_NAME']=array("like","%".$nickname."%");
        }
        if($mobile){
            $where['a.MOBILE']=array("like","%".$mobile."%");
        }

        $model = M('cq_invitation_code');
        $count = $model->where()->count(); // 获取浏览记录数
        if($count > 0){
            $total_pages = ceil($count/$limit);
        }else{
            $total_pages = 0;
        }
        if($page > $total_pages)
            $page = $total_pages;
        $start = $limit * $page - $limit;
        if($start < 0)
            $start = 0;
        $responce->page = intval($page); //当前页
        $responce->total = $total_pages; //总页数
        $responce->records = $count;     //总记录数

        $list = $model->table('cq_invitation_code b')->join('lc_user a ON a.USER_ID = b.USER_ID ')->field('a.MOBILE,a.NICK_NAME,b.USER_ID,(select count(*) from cq_invitation_friends c where c.invitation_code=b.invitation_code) as friendCount')->where($where)->order($sidx." ".$sord)->limit($start,$limit)->select();
        // dump($list);exit;
        foreach ($list as $key => $value) {
            $responce->rows[$key]['user_id'] = $value['user_id'];     //用户id
            $responce->rows[$key]['mobile'] = $value['mobile'];     //手机号
            $responce->rows[$key]['nick_name'] = $value['nick_name'];    //用户昵称
            $responce->rows[$key]['friendCount'] = $value['friendcount']; //好友数
            $responce->rows[$key]['chakan'] = "<a href='javascript:top.openQueryFriends($value[user_id]);'>查看</a>"; //好友数
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //查看好友列表
    public function chakan_friend(){
        $page = $_POST['page'];  //获取请求页数
        $limit = $_POST['rows']; //获取每页显示的记录数
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'c.USER_ID';
        if(!$sord) $sord = 'desc';//默认倒序

        //传值接收
        $friend_id = $_POST['friend_id'];

        //查询条件
        $where = array();
        $where['USER_ID'] = $friend_id;
        $model = M('cq_invitation_code');
        $invitation_code = $model->field('INVITATION_CODE')->where($where)->find(); //邀请码
        $where_b = array();
        $where_b['INVITATION_CODE'] = $invitation_code['invitation_code'];
        $where_b['IS_DEL'] = 0;
        $count = $model->where($where_b)->count(); // 获取浏览记录数
        if($count > 0){
            $total_pages = ceil($count/$limit);
        }else{
            $total_pages = 0;
        }
        if($page > $total_pages)
            $page = $total_pages;
        $start = $limit * $page - $limit;
        if($start < 0)
            $start = 0;
        $responce->page = intval($page); //当前页
        $responce->total = $total_pages; //总页数
        $responce->records = $count;     //总记录数

        
        $where_c = array();
        $where_c['a.INVITATION_CODE'] = $invitation_code['invitation_code'];
        $where_c['a.IS_DEL'] = 0;
        $list = $model->table('cq_invitation_friends a')->join('cq_user_finance b ON a.USER_ID = b.USER_ID')->join('lc_user c ON c.USER_ID = a.USER_ID')->field('c.USER_ID,c.USER_NAME,c.MOBILE,c.ADD_TIME,b.BUY_AMONUT,b.FROZEN_AMOUNT,b.CASH_AMOUNT')->where($where_c)->order($sidx." ".$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['mobile'] = $value['mobile'];     //用户手机号
            $responce->rows[$key]['user_name'] = $value['user_name'];   //用户名称
            $responce->rows[$key]['add_time']  = $value['add_time'];
            $responce->rows[$key]['buy_amonut'] = $value['buy_amonut'] ;    //购买金额
            $responce->rows[$key]['frozen_amount'] = $value['frozen_amount'];   //冻结金额
            $responce->rows[$key]['cash_amount'] = $value['cash_amount'];   //可提现金额
            $responce->rows[$key]['asset_msg'] = "<a href='javascript:financeRecoed($value[user_id]);'>资产信息</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //员工列表
    public function staff(){
        $this->display();
    }
    public function staff_list(){
        $page = $_POST['page'];  //获取请求页数
        $limit = $_POST['rows']; //获取每页显示的记录数
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'USER_ID';
        if(!$sord) $sord = 'desc';//默认倒序
        //查询条件
        
        $username=$_POST['username'];
        $nickname=$_POST['nickname'];
        $mobile = $_POST['mobile'];
        $where = array();
        $where['USER_TYPE']=array('in','2,3');
        $where['IS_DEL']=0;
        if($username){
            $where['USER_NAME']=array("like","%".$username."%");
        }
        if($nickname){
            $where['NICK_NAME']=array("like","%".$nickname."%");
        }
        if($mobile){
            $where['MOBILE']=array("like","%".$mobile."%");
        }
        //
        $model = M('lc_user');
        $count = $model->where($where)->count(); // 获取浏览记录数
        if($count > 0){
            $total_pages = ceil($count/$limit);
        }else{
            $total_pages = 0;
        }
        if($page > $total_pages)
            $page = $total_pages;
        $start = $limit * $page - $limit;
        if($start < 0)
            $start = 0;
        $responce->page = intval($page); //当前页
        $responce->total = $total_pages; //总页数
        $responce->records = $count;     //总记录数
        //查询数据
        $list = $model->field('USER_NAME,MOBILE,ADD_TIME,USER_ID,NICK_NAME,USER_TYPE')->where($where)->order($sidx." ".$sord)->limit($start,$limit)->select();
       //$this->ajaxReturn($model->getLastSql(),'JSON');
        $time = time();
        $start_time = date('Y-m',$time); //开始时间
        $end_time = date('Y',$time).'-'.(date('m',$time)+1);   //结束时间 年-月+1
        foreach ($list as $key => $value) {
            $m = M('cq_invitation_code');
            $where = array();
            $where['USER_ID'] = $value['user_id'];
            $invitation_code = $m->field('INVITATION_CODE')->where($where)->find();
            $m2 = M('cq_invitation_friends');
            $where_b = array();
            $where_b['INVITATION_CODE'] = $invitation_code;
            $where_b['ADD_TIME'] = array('between',array($start_time,$end_time));
            $count = $m2->where($where_b)->count(); //  本月邀请数
            $m3 = M('cq_product_buy');
            $where_c = array();
            $where_c['ADD_TIME'] = array('between',array($start_time,$end_time));
            $buy_money = $m3->field('BUY_MONEY')->where($where_c)->count();  // 本月投资额
            $back_user_type = $value['user_type'];
            //用户类型
            if($back_user_type==1){
                $back_user_type='一般用户';
            }elseif ($back_user_type==2) {
                $back_user_type='平台用户';
            }elseif($back_user_type==3){
                $back_user_type='渠道用户';
            }elseif($back_user_type==4){
                $back_user_type='羊头用户';
            }

            $responce->rows[$key]['user_id'] = $value['user_id'];   //用户ID
            $responce->rows[$key]['mobile'] = "<a href='javascript:openUserTable(".$value['user_id'].");'>".$value['mobile']."</a>";     //用户手机号
            $responce->rows[$key]['user_name'] = $value['user_name'];     //用户名称
            $responce->rows[$key]['nick_name'] = $value['nick_name'];     //用户昵称
            $responce->rows[$key]['yao_count'] = $count;     //本月邀请数
            $responce->rows[$key]['buy_money'] = $buy_money;     //本月投资(元)
            $responce->rows[$key]['back_user_type'] = $back_user_type;     //用户类型
            $responce->rows[$key]['add_time'] = $value['add_time'];     //创建日期
            $responce->rows[$key]['change'] = "<a href='javascript:top.goUser($value[user_id]);'>修改</a>";
            $responce->rows[$key]['delete'] = "<a href='javascript:deleteUser($value[user_id]);'>删除</a>";
            $responce->rows[$key]['check'] = "<a href='javascript:top.openQueryFriends($value[user_id]);'>查看</a>";

        }
        $this->ajaxReturn($responce,'JSON');
        
    }
    //导出上月注册人数
    public function export_lastMonth(){
        import('Org.Util.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $savename=intval(date("m",time())-1).'月注册用户记录';  //生成的Excel文件文件名 
        $datetime = date('Y-m-d', time());        
        /*if (preg_match("/MSIE/", $ua)) {
            $savename = urlencode($savename); //处理IE导出名称乱码
        } */
        // excel头参数  
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Content-Type:application/force-download');
        header('Content-Type:application/vnd.ms-execl');
        header('Content-Type:application/octet-stream');
        header('Content-Type:application/download');
        header("Content-Disposition:attachment;filename='$savename.xls'");
        header('Content-Transfer-Encoding:binary');
        $firstday=date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
        $lastday=date("Y-m-01",time());
        $m=M();
        $list=$m->query("SELECT a.MOBILE,a.USER_NAME,a.NICK_NAME,b.BUY_AMONUT,b.CASH_AMOUNT,a.ADD_TIME,e.MOBILE as phone,e.USER_NAME as realname,a.USER_REG_IP from lc_user a LEFT JOIN cq_user_finance b on a.USER_ID=B.USER_ID LEFT JOIN cq_invitation_friends c ON a.USER_ID = c.USER_ID LEFT JOIN cq_invitation_code d ON c.INVITATION_CODE = d.INVITATION_CODE LEFT JOIN lc_user e on d.USER_ID=e.USER_ID where (a.USER_TYPE=1 or a.USER_TYPE=4) and a.IS_DEL=0 and b.IS_DEL=0 and a.ADD_TIME BETWEEN '".$firstday."' and '".$lastday."' ORDER BY a.ADD_TIME DESC");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '注册手机');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '真实姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '用户昵称');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '购买金额');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '账户余额');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '注册时间');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '邀请人手机号');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '邀请人');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '注册IP');

        for ($i=0; $i < count($list); $i++) {
            $j=$i+2;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $j, $list[$i]['mobile']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $j, $list[$i]['user_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $j, $list[$i]['nick_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $j, $list[$i]['buy_amonut']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $j, $list[$i]['cash_amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $j, $list[$i]['add_time']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $j, $list[$i]['phone']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $j, $list[$i]['realname']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $j, $list[$i]['user_reg_ip']);
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output');
    }
    //员工列表-删除记录
    public function delete_record(){
        $this->display();
    }
    public function staff_delete(){

        $page = $_POST['page'];  //获取请求页数
        $limit = $_POST['rows']; //获取每页显示的记录数
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.STAFF_ID';
        if(!$sord) $sord = 'desc';//默认倒序

        $model = M('cq_staff');
        $where = array();
        $where['IS_DEL'] = 1;

        $count = $model->where($where)->count(); // 获取浏览记录数
        if($count > 0){
            $total_pages = ceil($count/$limit);
        }else{
            $total_pages = 0;
        }
        if($page > $total_pages)
            $page = $total_pages;
        $start = $limit * $page - $limit;
        if($start < 0)
            $start = 0;
        $responce->page = intval($page); //当前页
        $responce->total = $total_pages; //总页数
        $responce->records = $count;    

        $where_b = array();
        $where_b['a.IS_DEL'] = 1;
        //查询数据
        $list = $model->table('cq_staff a')->join('left join cq_back_user b ON a.USER_ID = b.BACK_USER_ID')->join('left join cq_back_user c ON a.UP_USER = c.BACK_USER_ID')->field('b.BACK_USER_NAME,c.BACK_USER_NAME as up_user,b.BACK_USER_MOBILE,a.UP_TIME,a.STAFF_ID')->where($where_b)->group('USER_ID')->order($sidx." ".$sord)->limit($start,$limit)->select();

        foreach ($list as $key => $value) {
            $responce->rows[$key]['staff_id'] = $value['staff_id'];     //用户id
            $responce->rows[$key]['back_user_mobile'] = $value['back_user_mobile'];     //手机号
            $responce->rows[$key]['back_user_name'] = $value['back_user_name'];    //用户昵称
            $responce->rows[$key]['up_user'] = $value['up_user']; //修改人
            $responce->rows[$key]['up_time'] = $value['up_time']; //修改时间
        }
        $this->ajaxReturn($responce,'JSON');
    }
    
    /**
     * 用户列表点击手机号弹出页面
     * @param
     * @author muyy
     * @data 2016年9月20日
     * @return 渲染模板
     */
    public function user_info(){
    	$user_id = $_REQUEST['user_id'];
    	$model = M('lc_user');
    	$userInfo = $model->where('USER_ID = '.$user_id)->find();
    	$invitation_code = M('cq_invitation_friends')->where('USER_ID = '.$user_id)->field('INVITATION_CODE')->find();
    	if($invitation_code){
	    	$invite_user_id = M('cq_invitation_code')->where('INVITATION_CODE = "'.$invitation_code['invitation_code'].'"')->field('USER_ID')->find();
	    	$invite_user = $model->where('USER_ID = '.$invite_user_id['user_id'])->field('USER_NAME')->find();
	    	$userInfo['invite_user'] = $invite_user['user_name'];
    	}else{
	    	$userInfo['invite_user'] = '';
    	}
    	$this->assign('user',$userInfo);
    	$this->assign('user_id',$user_id);
    	$this->display();
    }
    
    /**
     * 用户短信内容
     */
    public function user_sms(){
    	$page = $_POST['page'];  //获取请求页数
    	$limit = $_POST['rows']; //获取每页显示的记录数
    	$sidx = $_POST['sidx']; //获取默认排序字段
    	$sord = $_POST['sord']; //获取排序方式
    	if (!$sidx)    $sidx = 'SMS_ID';
    	if(!$sord) $sord = 'desc';//默认倒序
    	//传值接收
    	$user_id = $_POST['user_id'];
    	//查询条件
    	$where = array();
    	$where['USER_ID'] = $user_id;
		$model = M('lc_sms');
		$count = $model->where($where)->count();
    	if($count > 0){
    		$total_pages = ceil($count/$limit);
    	}else{
    		$total_pages = 0;
    	}
    	if($page > $total_pages)
    		$page = $total_pages;
    	$start = $limit * $page - $limit;
    	if($start < 0)
    		$start = 0;
    	$responce->page = intval($page); //当前页
    	$responce->total = $total_pages; //总页数
    	$responce->records = $count;     //总记录数
    	
    	$list = $model->field('USER_ID,SMS_CONTENT,SMS_DATE')->where($where)->order($sidx." ".$sord)->limit($start,$limit)->select();
    	foreach ($list as $key => $value) {
    		$responce->rows[$key]['sms_content'] = $value['sms_content'];//短信内容
    		$responce->rows[$key]['sms_date'] = $value['sms_date'];//浏览时间
    	}
    	$this->ajaxReturn($responce,'JSON');
    }
    
    /**
     * 用户邮箱内容
     */
    public function user_email(){
    	$page = $_POST['page'];  //获取请求页数
    	$limit = $_POST['rows']; //获取每页显示的记录数
    	$sidx = $_POST['sidx']; //获取默认排序字段
    	$sord = $_POST['sord']; //获取排序方式
    	if (!$sidx)    $sidx = 'EMAIL_ID';
    	if(!$sord) $sord = 'desc';//默认倒序
    	//传值接收
    	$user_id = $_POST['user_id'];
    	//查询条件
    	$where = array();
    	$where['USER_ID'] = $user_id;
    	$model = M('lc_email');
    	$count = $model->where($where)->count();
    	if($count > 0){
    		$total_pages = ceil($count/$limit);
    	}else{
    		$total_pages = 0;
    	}
    	if($page > $total_pages)
    		$page = $total_pages;
    	$start = $limit * $page - $limit;
    	if($start < 0)
    		$start = 0;
    	$responce->page = intval($page); //当前页
    	$responce->total = $total_pages; //总页数
    	$responce->records = $count;     //总记录数
    	 
    	$list = $model->field('USER_ID,SUBJECT,CONTENT,EMAIL_DATE,TO_EMAILS,EMAILTYPE')->where($where)->order($sidx." ".$sord)->limit($start,$limit)->select();
    	foreach ($list as $key => $value) {
    		$responce->rows[$key]['subject'] = $value['subject'];
    		$responce->rows[$key]['content'] = $value['content'];
    		$responce->rows[$key]['email_date'] = $value['email_date'];
    		$responce->rows[$key]['to_emails'] = $value['to_emails'];
    		$responce->rows[$key]['emailtype'] = $value['emailtype'];
    	}
    	$this->ajaxReturn($responce,'JSON');
    }
}