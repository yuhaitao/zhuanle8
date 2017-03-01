<?php
namespace Admin\Controller;
use Think\Controller;
class CashController extends BaseController {
    public function index(){
    	
    }
    //提现审核
    public function cash(){
    	$this->display();
    }
    //审核成功
    public function cashSuccess(){
    	$this->display();
    }
    //审核失败
    public function cashFail(){
    	$this->display();
    }
    //提现审核列表
    public function cashlist(){
    	$page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        //传值接收
        $status=$_POST['status'];
        $username=$_POST['username'];
        $draw_no=$_POST['draw_no'];
        //查询条件
        $where = array();
        $where['a.EXAMINE_STATUS']= $status;
        $where['a.IS_DEL'] = 0;
        $where['d.PARENT_ID']=19;
        if($username){
        	$where['b.USER_NAME']=array("like","%".$username."%");
        }
        if($draw_no){
        	$where['a.DRAW_NO']=$draw_no;
        }
        
        $m=M('cq_draw_cash');
        /*总记录数*/
        $count = $m->table('cq_draw_cash a')->join('lc_user b on a.USER_ID=b.USER_ID')->join('cq_bank c on a.DRAW_NO=c.BANK_NUMBER')->join('lc_dictionary_small d on a.DRAW_CODE=d.DICSMALL_NO')->where($where)->count();
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
        $list=$m->table('cq_draw_cash a')->join('lc_user b on a.USER_ID=b.USER_ID')->join('cq_bank c on a.DRAW_NO=c.BANK_NUMBER')->join('lc_dictionary_small d on a.DRAW_CODE=d.DICSMALL_NO')->join('left join cq_back_user e on a.UP_USER=e.BACK_USER_ID')->field('a.DRAW_CASH_ID,b.USER_NAME,a.DRAW_MONEY,d.DICSMALL_NAME,c.BANK_ADDRESS,a.DRAW_NO,a.ADD_TIME,e.BACK_USER_NAME,a.UP_TIME,a.EXAMINE_REMARK,a.USER_ID')->where($where)->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
        	$responce->rows[$key]['user_name']=$value['user_name'];//提现人
        	$responce->rows[$key]['draw_money']=$value['draw_money'];//提现金额
        	$responce->rows[$key]['bank_name']=$value['dicsmall_name'];//银行
        	$responce->rows[$key]['bank_address']=$value['bank_address'];//开户行
        	$responce->rows[$key]['draw_no']=$value['draw_no'];
        	$responce->rows[$key]['add_time']=$value['add_time'];
        	$examine="";
        	$state='';
	        if($status==0){
	        	$state="未审核";
	        	$examine="<a href='javascript:parent.examine_cash(".$value['draw_cash_id'].");'>审核</a>";
	        }elseif ($status==1) {
	        	$state="提现成功";
	        	$examine="审核成功";
	        }elseif ($status==2) {
	        	$state="提现失败";
	        	$examine="审核失败";
	        }
	        $responce->rows[$key]['examine_status']=$state;
	        $responce->rows[$key]['up_user']=$value['back_user_name'];
	        $responce->rows[$key]['up_time']=$value['up_time'];
	        $responce->rows[$key]['examine_remark']=$value['examine_remark'];
	        $responce->rows[$key]['buy_list'] = "<a href='javascript:top.openProductBuy(".$value['user_id'].");'>购买清单</a>";
            $responce->rows[$key]['asset'] = "<a href='javascript:top.financeRecoed(".$value['user_id'].");'>资产信息</a>";
            $responce->rows[$key]['examine']=$examine;
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //保存审核结果
    public function save_examine(){
        $cash_id=$_POST['cash_id'];
        $mark=$_POST['mark'];
        $remark=$_POST['remark'];
        $user_id=session('back_user_id');//添加人
        $time=date("Y-m-d H:i:s",time());//添加时间
        //审核通过
        if ($mark==1) {
            //查询流水号
            $serial=M('cq_draw_cash')->where("DRAW_CASH_ID=$cash_id")->field("SERIAL_NO,DRAW_MONEY,USER_ID,ADD_TIME")->find();
            //减去资产中冻结中的相应金额
            $moneyArr=M("cq_user_finance")->where("USER_ID=".$serial['user_id'])->field("FROZEN_AMOUNT")->find();
            $new_Frozen=bcsub($moneyArr['frozen_amount'], $serial['draw_money'],2);
            //如果提现金额大于冻结金额 则不可提现
            if ($new_Frozen < 0) {
                $this->ajaxReturn('用户账户冻结金额不足，请仔细对账','JSON');
            }
            $data_M['FROZEN_AMOUNT']=$new_Frozen;
            $data_M['UP_TIME']=$time;
            M("cq_user_finance")->where("USER_ID=".$serial['user_id'])->save($data_M);
            //解冻提现记录
            $data_F['FREEZE_STATUS']=2;
            $data_F['UNFREEZE_TIME']=$time;
            $data_F['UP_USER']=$user_id;
            $data_F['UP_TIME']=$time;
            M('cq_user_finance_record')->where("SERIAL_NO='".$serial['serial_no']."' and USER_ID=".$serial['user_id'])->save($data_F);
            //添加账户信息变动通知
            $theDay=date("Y-m-d",strtotime($serial['add_time']));
            $datad=array();
            $datad['MES_CONTENT']="您于".$theDay."申请的提现已成功到账，提现金额".$serial['draw_money']."元，请检查银行卡金额。";
            $datad['MES_TYPE']=0;
            $datad['MES_TIME']=$time;
            $datad['USER_ID']=$serial['user_id'];
            $datad['LOOK_TYPE']=0;
            $datad['ADD_TIME']=$time;
            $datad['IS_DEL']=0;
            M('lc_message')->add($datad);
        //审核失败
        }elseif ($mark==2) {
            //查询流水号
            $serial=M('cq_draw_cash')->where("DRAW_CASH_ID=$cash_id")->field("SERIAL_NO,DRAW_MONEY,USER_ID")->find();
            //减去资产中冻结中的相应金额
            $moneyArr=M("cq_user_finance")->where("USER_ID=".$serial['user_id'])->field("FROZEN_AMOUNT,CASH_AMOUNT")->find();
            $new_Frozen=bcsub($moneyArr['frozen_amount'], $serial['draw_money'],2);
            $new_Cash=bcadd($moneyArr['cash_amount'], $serial['draw_money'],2);
            $data_M['FROZEN_AMOUNT']=$new_Frozen;
            $data_M['CASH_AMOUNT']=$new_Cash;
            $data_M['UP_TIME']=$time;
            M("cq_user_finance")->where("USER_ID=".$serial['user_id'])->save($data_M);
            //解冻提现记录
            $data_F['FREEZE_STATUS']=2;
            $data_F['UNFREEZE_TIME']=$time;
            $data_F['UP_USER']=$user_id;
            $data_F['UP_TIME']=$time;
            $data_F['IS_DEL']=1;
            $data_F['BALANCE']=$new_Cash;
            M('cq_user_finance_record')->where("SERIAL_NO='".$serial['serial_no']."' and USER_ID=".$serial['user_id'])->save($data_F);
            //添加账户信息变动通知
            $theDay=date("Y-m-d",strtotime($serial['add_time']));
            $datad=array();
            $datad['MES_CONTENT']="您于".$theDay."申请的提现因为金额错误审核失败。";
            $datad['MES_TYPE']=0;
            $datad['MES_TIME']=$time;
            $datad['USER_ID']=$serial['user_id'];
            $datad['LOOK_TYPE']=0;
            $datad['ADD_TIME']=$time;
            $datad['IS_DEL']=0;
            M('lc_message')->add($datad);
        //传参异常
        }else{
            $this->ajaxReturn('审核失败','JSON');
        }

        $where['DRAW_CASH_ID']=$cash_id;
        $data['EXAMINE_STATUS']=$mark;
        $data['EXAMINE_REMARK']=$remark;
        $data['UP_USER']=$user_id;
        $data['UP_TIME']=$time;
        //保存审核结果
        $m=M('cq_draw_cash')->where($where)->save($data);
        if($m){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('审核失败','JSON');
        }
    }
}