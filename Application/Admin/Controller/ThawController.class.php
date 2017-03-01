<?php
namespace Admin\Controller;
use Think\Controller;
class ThawController extends BaseController {
    public function index(){
    	
    }
    //满标解冻
    //天天返利
    public function thaw(){
    	$this->display();
    }
    //限时返利
    public function limitThaw(){
    	$this->display();
    }
    //超级返利
    public function superThaw(){
    	$this->display();
    }
    //满标解冻列表
    public function thawlist(){
    	$page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = '1';
        //传值接收
        $product_type=$_POST['product_type'];
        $product_id=$_POST['product_id'];
        $target_name=$_POST['target_name'];
        //查询条件
        $where = array();
        $where['a.PRODUCT_TYPE']= $product_type;
        $where['a.IS_DEL'] = 0;
        $where['a.RELEASE_STATUS']=3;
        if($target_name){
        	$where['a.TARGET_NAME']=array("like","%".$target_name."%");
        }
        if($product_id){
        	$where['a.PRODUCT_ID']=$product_id;
        }
        
        $m=M('cq_product');
        /*总记录数*/
        $count = $m->table('cq_product a')->join('cq_back_user b on a.UP_USER=b.BACK_USER_ID')->join('cq_plat c on a.PLAT_SHORTNAME=c.PLAT_ID')->where($where)->count();
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
        $list=$m->table('cq_product a')->join('cq_back_user b on a.UP_USER=b.BACK_USER_ID')->join('cq_plat c on a.PLAT_SHORTNAME=c.PLAT_ID')->where($where)->field('a.PRODUCT_ID,a.TARGET_NAME,c.PLAT_SHORTNAME,a.INVEST_MONTH,a.INVEST_DAY,a.ONLINE_TIME,a.DOWN_TIME,b.BACK_USER_NAME,a.RELEASE_STATUS,a.IS_THAW')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
        	$responce->rows[$key]['product_id']=$value['product_id'];
        	$responce->rows[$key]['target_name']=$value['target_name'];
        	$responce->rows[$key]['plat_shortname']=$value['plat_shortname'];
        	$long="";
        	if($value['invest_month'] > 0){
        		$long=$value['invest_month']."月";
        	}elseif ($value['invest_day'] > 0) {
        		$long=$value['invest_day']."天";
        	}
        	$responce->rows[$key]['period']=$long;
        	$responce->rows[$key]['online_time']=$value['online_time'];
        	$responce->rows[$key]['down_time']=$value['down_time'];
        	$responce->rows[$key]['back_user_name']=$value['back_user_name'];
        	$responce->rows[$key]['release_status']="满标";
        	if($value['is_thaw']==1){
        		$responce->rows[$key]['is_thaw']='已解冻';
        		$responce->rows[$key]['handle']="";
        	}else{
        		$responce->rows[$key]['is_thaw']='已冻结';
        		$responce->rows[$key]['handle']="<a href='javascript:productThaw(".$value['product_id'].");'>解冻</a>";
        	}
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //解冻
    public function productThaw(){
    	$product_id=$_POST['product_id'];
    	$user_id=session('back_user_id');
        $time=date("Y-m-d H:i:s",time());
        //满标-解冻返利
            //查询该产品购买记录和返利相关的内容
        $result=M("cq_product_buy a")->join("cq_user_finance_record b on a.SERIAL_NO=b.SERIAL_NO")->where("a.PRODUCT_ID=".$product_id." and a.BUY_MONEY > 0 and a.IS_DEL=0 and b.FREEZE_STATUS=1")->field("a.USER_ID,a.BUY_MONEY,b.USER_FINANCE_RECORE_ID,b.CASH_MONEY")->select();
        foreach ($result as $key => $value) {
            $u_id=$value['user_id'];
            $u_f_id=$value['user_finance_recore_id'];
            $cash_money=$value['cash_money'];
            $nowtime=date("Y-m-d H:i:s",time());
            //修改状态为解冻
            $note['FREEZE_STATUS']=2;
            $note['UNFREEZE_TIME']=$nowtime;
            $note['UP_USER']=$user_id;
            $note['UP_TIME']=$nowtime;
            M('cq_user_finance_record')->where("USER_FINANCE_RECORE_ID=".$u_f_id)->save($note);
            
            //更改资产字段值
            $userInfo=M("cq_user_finance")->where("USER_ID=".$u_id." and IS_DEL=0")->field("FROZEN_AMOUNT,CASH_AMOUNT")->find();

            $new_Money=$new_Frozen=0;
            //账户余额 增
            $new_Money=bcadd($userInfo['cash_amount'],$cash_money,2);
            //冻结金额 减
            $new_Frozen=bcsub($userInfo['frozen_amount'], $cash_money,2);
            $data_f['CASH_AMOUNT']=$new_Money;
            $data_f['FROZEN_AMOUNT']=$new_Frozen;
            $data_f['UP_TIME']=$nowtime;
            M("cq_user_finance")->where("USER_ID=".$u_id." and IS_DEL=0")->save($data_f);
            /*
            *好友返利：
            ***判断投资人是否是被邀请的
            ****否--到此结束
            ****是--判断邀请奖励是否已发放
            *******否--发放邀请奖励  然后发放邀请投资的返利
            *******是--发放邀请投资的返利
            */
            $useTime=date("Y-m-d",time());
            $inviteInfo=M("cq_invitation_friends")->where("USER_ID=".$u_id." and IS_DEL=0")->field("INVITATION_CODE,IS_REWARD")->find();
            if ($inviteInfo) {
                $thisUser=M('cq_invitation_code')->where("INVITATION_CODE='".$inviteInfo['invitation_code']."' and IS_DEL=0")->getField("USER_ID");
                //邀请人存在
                if ($thisUser) {
                    if ($inviteInfo['IS_REWARD']==0) {
                        //
                        $rule=M('cq_serial_rule')->where("SERIAL_TYPE='06' and SERIAL_DAY='".$useTime."'")->field("SERIAL_NUM")->find();
                        //如果没有记录
                        $number="";
                        if (!$rule['serial_num']) {
                            $param=array();
                            $param['SERIAL_TYPE']='06';
                            $param['SERIAL_DAY']=$useTime;
                            $param['SERIAL_NUM']='1';
                            M('cq_serial_rule')->add($param);
                            $number=1;
                        }else{
                            M('cq_serial_rule')->where("SERIAL_TYPE='06' and SERIAL_DAY='".$useTime."'")->setInc("SERIAL_NUM");
                            $number=intval($rule['serial_num'])+1;
                        }
                        $serial_no="06".date("ymd").sprintf("%05d",$number);
                        $option=array();
                        $option['USER_ID']=$thisUser;
                        $option['TYPE']='06';
                        $option['CASH_MONEY']=2;
                        $option['SERIAL_NO']=$serial_no;
                        $option['OPERATE_TIME']=$nowtime;
                        $option['FREEZE_STATUS']=2;
                        $option['UNFREEZE_TIME']=$nowtime;
                        $option['REMARKS']="依据平台：《邀请好友人数返利》返利规则";
                        $option['ADD_USER']=$thisUser;
                        $option['ADD_TIME']=$nowtime;
                        //添加返利记录
                        M("cq_user_finance_record")->add($option);
                        //修改资产信息
                        $nowCash=M("cq_user_finance")->where("USER_ID=".$thisUser." and IS_DEL=0")->getField("CASH_MONEY");
                        $newCash=bcadd($nowCash, 2,2);
                        M("cq_user_finance")->where("USER_ID=".$thisUser." and IS_DEL=0")->setField("CASH_MONEY",$newCash);
                        //修改状态 表示已返利
                        M("cq_invitation_friends")->where("USER_ID=".$u_id." and IS_DEL=0")->setField("IS_REWARD",1);
                    }
                    //好友投资返利 :: 开始
                    $rule=M('cq_serial_rule')->where("SERIAL_TYPE='06' and SERIAL_DAY='".$useTime."'")->field("SERIAL_NUM")->find();
                    //如果没有记录
                    $number="";
                    if (!$rule['serial_num']) {
                        $param=array();
                        $param['SERIAL_TYPE']='06';
                        $param['SERIAL_DAY']=$useTime;
                        $param['SERIAL_NUM']='1';
                        M('cq_serial_rule')->add($param);
                        $number=1;
                    }else{
                        M('cq_serial_rule')->where("SERIAL_TYPE='06' and SERIAL_DAY='".$useTime."'")->setInc("SERIAL_NUM");
                        $number=intval($rule['serial_num'])+1;
                    }
                    $serial_no="06".date("ymd").sprintf("%05d",$number);
                    //判断返利率
                    $rate=0;
                    if ($result['buy_money'] > 0 && $result['buy_money'] <= 100000) {
                        $rate=0.0013;
                    }elseif ($result['buy_money'] > 100000 && $result['buy_money'] <= 300000) {
                        $rate=0.0018;
                    }elseif ($result['buy_money'] > 300000 && $result['buy_money'] <= 500000) {
                        $rate=0.0023;
                    }elseif ($result['buy_money'] > 500000 && $result['buy_money'] <= 1000000) {
                        $rate=0.0028;
                    }elseif ($result['buy_money'] > 1000000) {
                        $rate=0.0033;
                    }
                    //计算返利金额
                    $cashMoney=number_format($result['buy_money']*$rate,2);
                    $option=array();
                    $option['USER_ID']=$thisUser;
                    $option['TYPE']='08';
                    $option['CASH_MONEY']=$cashMoney;
                    $option['SERIAL_NO']=$serial_no;
                    $option['OPERATE_TIME']=$nowtime;
                    $option['FREEZE_STATUS']=2;
                    $option['UNFREEZE_TIME']=$nowtime;
                    $option['REMARKS']="依据平台：《邀请好友得返利》返利规则";
                    $option['ADD_USER']=$thisUser;
                    $option['ADD_TIME']=$nowtime;
                    //添加返利记录
                    M("cq_user_finance_record")->add($option);
                    //更改账户金额
                    $nowCash=M("cq_user_finance")->where("USER_ID=".$thisUser." and IS_DEL=0")->getField("CASH_MONEY");
                    $newCash=bcadd($nowCash, $cashMoney,2);
                    M("cq_user_finance")->where("USER_ID=".$thisUser." and IS_DEL=0")->setField("CASH_MONEY",$newCash);
                }
            }
            /*首次投资并且金额超过两千 返利20元*/
        }
        $m=M('cq_product');
        $m->IS_THAW = 1;
        $m->UP_USER = $user_id;
        $m->UP_TIME = $time;
        $where=array('PRODUCT_ID' => $product_id);
        $model = $m ->where($where)->save();
        if($model){
            $this->ajaxReturn('1','JSon');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
}