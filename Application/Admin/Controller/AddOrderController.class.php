<?php
namespace Admin\Controller;
use Think\Controller;
class AddOrderController extends BaseController {
    public function index(){
    	
    }
    public function addorder(){
    	$this->display();
    }
    public function productlist(){
    	$page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.ONLINE_TIME';
        //传值接收
        $product_id=$_POST['product_id'];
        $target_name=$_POST['target_name'];
        //查询条件
        $where = array();
        $where['a.IS_DEL'] = 0;
        $where['a.RELEASE_STATUS']=0;
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
        $list=$m->table('cq_product a')->join('left join cq_back_user b on a.UP_USER=b.BACK_USER_ID')->join('left join cq_plat c on a.PLAT_SHORTNAME=c.PLAT_ID')->where($where)->field('a.PRODUCT_ID,a.TARGET_NAME,c.PLAT_SHORTNAME,a.INVEST_MONTH,a.INVEST_DAY,a.ONLINE_TIME,a.DOWN_TIME,b.BACK_USER_NAME')->order($sidx.' '.$sord)->limit($start,$limit)->select();
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
        	$responce->rows[$key]['check']="<a href='javascript:top.checkThis(".$value['product_id'].");'>查看</a>";
        	$responce->rows[$key]['addorder']="<a href='javascript:parent.AddOder(".$value['product_id'].");'>添加订单</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //查看
    public function product_view(){
    	$product_id=$_REQUEST['product_id'];
    	$m=M('cq_product');
    	$where['a.IS_DEL']=0;
    	$where['a.PRODUCT_ID']=$product_id;
    	$list=$m->table('cq_product a')->join('cq_plat b on a.PLAT_SHORTNAME=b.PLAT_ID')->field('b.PLAT_SHORTNAME,a.JUMP_LINK,a.TARGET_NAME,a.ANNUAL_INCOME_RATE,a.INVEST_MONTH,a.INVEST_DAY,a.START_INVEST_AMOUNT,a.END_INVEST_AMOUNT,a.PRODUCT_SUM,a.PRODUCT_TYPE,a.CQ_REBATE_RATE,a.CQ_RATE,a.UNIT_RATE,a.CQ_RED,a.RED_INFO,a.ONLINE_TIME,a.DOWN_TIME,a.THAWING_METHOD,a.FLOW_REBATE,a.RE_CAST,a.BID_SECURITY_TYPE,a.BID_SECURITY_OTHER,a.INTEREST_TYPE,a.INTEREST_OTHER,a.REBATE_TYPE,a.REBATE_OTHER')->where($where)->find();
        $this->assign("list",$list);
        $this->display();
    }
    //模糊查询可能的用户数据
    public function user_list(){
        $buy_man=$_POST['buy_man'];
        $where['MOBILE']  = array('like', '%'.$buy_man.'%');
        $where['USER_NAME']  = array('like','%'.$buy_man.'%');
        $where['NICK_NAME']  = array('like','%'.$buy_man.'%');
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        $map['IS_DEL']= array('eq',0);
        $m=M('lc_user');
        $list=$m->field('USER_ID,USER_NAME,NICK_NAME,MOBILE')->where($map)->limit(0,10)->select();
        $this->ajaxReturn($list,'JSON');
    }
    //保存订单
    public function save_order(){
        $buy_money=$_POST['buy_money'];
        $buy_time=$_POST['buy_time'];
        $buy_man=$_POST['buy_man_id'];
        $product_id=$_POST['product_id'];
        $user_id=session('back_user_id');//添加人
        $time=date("Y-m-d H:i:s",time());//添加时间
        $data['PRODUCT_ID']=$product_id;
        $data['EXAMINE_STATUS']=0;
        $data['ADD_USER']=$user_id;
        $data['ADD_TIME']=$time;
        $data['USER_ID']=$buy_man;
        $data['BUY_MONEY']=$buy_money;
        $data['BUY_TIME']=$buy_time;
        $data['IS_DEL']=0;
        $m=M('cq_order')->add($data);
        if($m){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
    //订单审核
    public function orderReview(){
        $this->display();
    }
    //未审核订单列表
    public function new_order_list()
    {
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        //传值接收
        $mobile=$_POST['mobile'];
        $user_name=$_POST['user_name'];
        $target_name=$_POST['target_name'];
        //查询条件
        $where = array();
        $where['a.IS_DEL'] = 0;
        $where['a.EXAMINE_STATUS']=0;
        if($target_name){
            $where['c.TARGET_NAME']=array("like","%".$target_name."%");
        }
        if($mobile){
            $where['b.MOBILE']=array("like","%".$mobile."%");
        }
        if($user_name){
            $where['b.USER_NAME']=array("like","%".$user_name."%");
        }
        $m=M('cq_order');
        /*总记录数*/
        $count = $m->table('cq_order a')->join('left join lc_user b on a.USER_ID=b.USER_ID')->join('left join cq_product c on a.PRODUCT_ID=c.PRODUCT_ID')->join('left join cq_back_user d on a.UP_USER=d.BACK_USER_ID')->where($where)->count();
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
        $list=$m->table('cq_order a')->join('left join lc_user b on a.USER_ID=b.USER_ID')->join('left join cq_product c on a.PRODUCT_ID=c.PRODUCT_ID')->join('left join cq_back_user d on a.ADD_USER=d.BACK_USER_ID')->where($where)->field('a.ORDER_ID,a.PRODUCT_ID,b.MOBILE,b.USER_NAME,c.TARGET_NAME,a.BUY_MONEY,a.BUY_TIME,d.BACK_USER_NAME,a.ADD_TIME')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['mobile']=$value['mobile'];
            $responce->rows[$key]['user_name']=$value['user_name'];
            $responce->rows[$key]['target_name']=$value['target_name'];
            $responce->rows[$key]['buy_money']=$value['buy_money'];
            $responce->rows[$key]['buy_time']=$value['buy_time'];
            $responce->rows[$key]['back_user_name']=$value['back_user_name'];
            $responce->rows[$key]['add_time']=$value['add_time'];
            $responce->rows[$key]['check']="<a href='javascript:top.checkThis(".$value['product_id'].");'>查看</a>";
            $responce->rows[$key]['review']="<a href='javascript:parent.reviewOrder(".$value['order_id'].");'>审核</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //保存审核结果
    public function save_review(){
        $order_id=$_POST['order_id'];
        $mark=$_POST['mark'];
        $remark=$_POST['remark'];
        $user_id=session('back_user_id');//添加人
        $nowtime=date("Y-m-d H:i:s",time());//添加时间
        //保存审核结果
        $where['ORDER_ID']=$order_id;
        $data['EXAMINE_STATUS']=$mark;
        $data['EXAMINE_REMARK']=$remark;
        $data['UP_USER']=$user_id;
        $data['UP_TIME']=$nowtime;
        $m=M('cq_order')->where($where)->save($data);
        //购买人 资产相关的操作 1-审核通过
        if ($mark==1) {
            //获取订单和产品相关的信息
            $result=M("cq_order a")->join("left join cq_product b on a.PRODUCT_ID=b.PRODUCT_ID")->where("a.ORDER_ID=$order_id and a.IS_DEL=0")->field("a.USER_ID,a.BUY_MONEY,a.BUY_TIME,b.PRODUCT_ID,b.TARGET_NAME,b.INVEST_MONTH,b.INVEST_DAY,b.CQ_RATE")->find();
            //计算返利金额 
            /*
                天标：投资金额*赚乐扒加息*天数/365
                月标：投资金额*赚乐扒加息*月数/12
                因为利率是百分比 所以要除以100 这里直接合并在月数和天数那里一块除了
                所以这里是除以1200 是12个月 *100 
            */
            $finance_Money=0;
            if ($result['invest_month'] > 0) {
                $finance_Money=bcdiv(bcmul(bcmul($result['buy_money'], $result['cq_rate'],2),$result['invest_month'],2),1200,2);
            }elseif ($result['invest_day'] > 0) {
                $finance_Money=bcdiv(bcmul(bcmul($result['buy_money'], $result['cq_rate'],2),$result['invest_day'],2),36500,2);
            }
            //获取流水号
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
                //字段 +1
                M('cq_serial_rule')->where("SERIAL_TYPE='01' and SERIAL_DAY='".$time."'")->setInc("SERIAL_NUM");
                $number=intval($rule['serial_num'])+1;
            }
            $serial_no="01".date("ymd").sprintf("%05d",$number);

            //**关于首投超2000 返20红包**
            $Tips['USER_ID']=$result['user_id'];
            $Tips['BUY_MONEY']=array("gt",0);
            $Tips['IS_DEL']=0;
            $user_buy=M("cq_product_buy")->where($Tips)->select();
            if (!$user_buy && $result['buy_money'] >= 2000 ) {
                //获取另外一个流水号
                $rule_A=M('cq_serial_rule')->where("SERIAL_TYPE='02' and SERIAL_DAY='".$time."'")->field("SERIAL_NUM")->find();
                //如果没有记录
                $number_A="";
                if (!$rule_A['serial_num']) {
                    $op['SERIAL_TYPE']='02';
                    $op['SERIAL_DAY']=$time;
                    $op['SERIAL_NUM']='1';
                    M('cq_serial_rule')->add($op);
                    $number_A=1;
                }else{
                    //字段 +1
                    M('cq_serial_rule')->where("SERIAL_TYPE='02' and SERIAL_DAY='".$time."'")->setInc("SERIAL_NUM");
                    $number_A=intval($rule['serial_num'])+1;
                }
                $serial_Num="02".date("ymd").sprintf("%05d",$number);
                //添加返利记录  返利金额非冻结状态
                $data_A['USER_ID']=$result['user_id'];
                $data_A['TYPE']=2;
                $data_A['CASH_MONEY']=20;
                $data_A['SERIAL_NO']=$serial_Num;
                $data_A['OPERATE_TIME']=$nowtime;
                $data_A['FREEZE_STATUS']=2;
                $data_A['UNFREEZE_TIME']=$nowtime;
                $data_A['REMARKS']="首投大于等于2000元 红包奖励";
                $data_A['ADD_USER']=$result['user_id'];
                $data_A['ADD_TIME']=$nowtime;
                $data_A['IS_DEL']=0;
                M("cq_user_finance_record")->add($data_A);
                //修改购买人的账号信息 更新购买的金额和返利金额
                 $uInfo=M("cq_user_finance")->where("USER_ID=".$result['user_id']." and IS_DEL=0")->field("CASH_AMOUNT")->find();
                 $new_Money=0;
                 $new_Money=bcadd($uInfo['cash_amount'], 20,2);
                 $data_B['CASH_AMOUNT']=$new_Money;
                 $data_B['UP_TIME']=$nowtime;
                 M("cq_user_finance")->where("USER_ID=".$result['user_id']." and IS_DEL=0")->save($data_B);
            }

            //添加购买记录
            $data_p['PRODUCT_ID']=$result['product_id'];
            $data_p['USER_ID']=$result['user_id'];
            $data_p['BUY_MONEY']=$result['buy_money'];
            $data_p['BUY_TIME']=$result['buy_time'];
            $data_p['SERIAL_NO']=$serial_no;
            $data_p['ADD_TIME']=$nowtime;
            $data_p['UP_TIME']=$nowtime;
            $data_p['IS_DEL']=0;
            $data_p['HANDLE_STATUS']=1;
            M("cq_product_buy")->add($data_p);

            //添加返利记录  返利金额冻结状态
            $data_r['USER_ID']=$result['user_id'];
            $data_r['TYPE']=2;
            $data_r['CASH_MONEY']=$finance_Money;
            $data_r['SERIAL_NO']=$serial_no;
            $data_r['OPERATE_TIME']=$nowtime;
            $data_r['FREEZE_STATUS']=1;
            $data_r['FREEZE_TIME']=$nowtime;
            $data_r['REMARKS']="购买(".$result['target_name'].")，返利".$finance_Money."元";
            $data_r['ADD_USER']=$result['user_id'];
            $data_r['ADD_TIME']=$nowtime;
            $data_r['IS_DEL']=0;
            M("cq_user_finance_record")->add($data_r);

            //修改购买人的账号信息 更新购买的金额和返利金额
             $userInfo=M("cq_user_finance")->where("USER_ID=".$result['user_id']." and IS_DEL=0")->field("BUY_AMONUT,FROZEN_AMOUNT")->find();
             $new_Money=$new_Frozen=0;
             $new_Money=bcadd($userInfo['buy_amonut'],$result['buy_money'],2);
             $new_Frozen=bcadd($userInfo['frozen_amount'], $finance_Money,2);
             $data_f['BUY_AMONUT']=$new_Money;
             $data_f['FROZEN_AMOUNT']=$new_Frozen;
             $data_f['UP_TIME']=$nowtime;
             M("cq_user_finance")->where("USER_ID=".$result['user_id']." and IS_DEL=0")->save($data_f);
        }
        if($m){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
    //订单查询
    public function orderInquiry(){
        $this->display();
    }
    //订单列表
    public function order_list(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        //传值接收
        $mobile=$_POST['mobile'];
        $user_name=$_POST['user_name'];
        $target_name=$_POST['target_name'];
        //查询条件
        $where = array();
        $where['a.IS_DEL'] = 0;
        $where['a.EXAMINE_STATUS'] = array("gt",0);
        if($target_name){
            $where['c.TARGET_NAME']=array("like","%".$target_name."%");
        }
        if($mobile){
            $where['b.MOBILE']=array("like","%".$mobile."%");
        }
        if($user_name){
            $where['b.USER_NAME']=array("like","%".$user_name."%");
        }
        $m=M('cq_order');
        /*总记录数*/
        $count = $m->table('cq_order a')->join('left join lc_user b on a.USER_ID=b.USER_ID')->join('left join cq_product c on a.PRODUCT_ID=c.PRODUCT_ID')->join('left join cq_back_user d on a.UP_USER=d.BACK_USER_ID')->where($where)->count();
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
        $list=$m->table('cq_order a')->join('left join lc_user b on a.USER_ID=b.USER_ID')->join('left join cq_product c on a.PRODUCT_ID=c.PRODUCT_ID')->join('left join cq_back_user d on a.ADD_USER=d.BACK_USER_ID')->join('left join cq_back_user e on a.UP_USER=e.BACK_USER_ID')->where($where)->field('a.ORDER_ID,a.PRODUCT_ID,b.MOBILE,b.USER_NAME,c.TARGET_NAME,a.BUY_MONEY,a.BUY_TIME,a.EXAMINE_STATUS,a.EXAMINE_REMARK,d.BACK_USER_NAME as add_user,a.ADD_TIME,e.BACK_USER_NAME as up_user,a.UP_TIME')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['mobile']=$value['mobile'];
            $responce->rows[$key]['user_name']=$value['user_name'];
            $responce->rows[$key]['target_name']=$value['target_name'];
            $responce->rows[$key]['buy_money']=$value['buy_money'];
            $responce->rows[$key]['buy_time']=$value['buy_time'];
            if($value['examine_status']==1){
                $value['examine_status']="审核成功";
            }elseif ($value['examine_status']==2) {
                $value['examine_status']="审核失败";
            }
            $responce->rows[$key]['examine_status']=$value['examine_status'];
            $responce->rows[$key]['examine_remark']=$value['examine_remark'];
            $responce->rows[$key]['add_user']=$value['add_user'];
            $responce->rows[$key]['add_time']=$value['add_time'];
            $responce->rows[$key]['up_user']=$value['up_user'];
            $responce->rows[$key]['up_time']=$value['up_time'];
            $responce->rows[$key]['check']="<a href='javascript:top.checkThis(".$value['product_id'].");'>查看</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
}