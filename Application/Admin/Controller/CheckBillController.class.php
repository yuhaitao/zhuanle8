<?php
namespace Admin\Controller;

use Think\Controller;
use Think\Log;

class CheckBillController extends BaseController
{

    public function index()
    {}

    public function checkbill()
    {
        $this->display();
    }
    // 对账管理-对账审核
    public function billManage()
    {
        $page = $_POST['page']; // 获取请求的页数
        $limit = $_POST['rows']; // 获取每页显示记录数
        $sidx = $_POST['sidx']; // 获取默认排序字段
        $sord = $_POST['sord']; // 获取排序方式
        $billSu_id = $_POST['billSu_id']; // 对账成功
        
        if (! $sidx)
            $sidx = 'a.ADD_TIME';
        if (! $sord)
            $sord = 'desc'; // 默认倒序
                                // 查询条件
        $where = array();
        $where['a.IS_DEL'] = 0;
        $where['a.RELEASE_STATUS'] = $billSu_id;
        // $where_b = array();
        // $where_b['IS_DEL'] = 0;
        // $where_b['RELEASE_STATUS'] = 0;
        
        $model = M('cq_product a');
        $count = $model->where($where)->count(); // 总记录数
                                                 // 根据记录数分页
        if ($count > 0) {
            $total_pages = ceil($count / $limit);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages)
            $page = $total_pages;
        $start = $limit * $page - $limit;
        if ($start < 0)
            $start = 0;
        $responce->page = intval($page); // 当前页
        $responce->total = $total_pages; // 总页数
        $responce->records = $count; // 总记录数
        
        $list = $model->table('cq_product a')
            ->join('cq_plat b ON a.PLAT_SHORTNAME = b.PLAT_ID')
            ->field('a.PRODUCT_ID,a.TARGET_NAME,b.PLAT_SHORTNAME,b.PLAT_ID')
            ->where($where)
            ->order($sidx . ' ' . $sord)
            ->limit($start, $limit)
            ->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['product_id'] = $value['product_id']; // 产品ID
            $responce->rows[$key]['plat_id'] = $value['plat_id']; // 平台ID
            $responce->rows[$key]['target_name'] = $value['target_name']; // 产品名称
            $responce->rows[$key]['plat_shortname'] = $value['plat_shortname']; // 平台名称
            $responce->rows[$key]['review'] = "<a href='javascript:openCheckAll($value[product_id],$value[plat_id]);'>审核</a>";
        }
        $this->ajaxReturn($responce, 'JSON');
    }
    // 对账记录
    public function billRecord()
    {
        $bill_id = $_POST['bill_id']; // 获取传值
        
        $page = $_POST['page']; // 获取请求的页数
        $limit = $_POST['rows']; // 获取每页显示记录数
        $sidx = $_POST['sidx']; // 获取默认排序字段
        $sord = $_POST['sord']; // 获取排序方式
        if (! $sidx)
            $sidx = 'a.ADD_TIME';
        if (! $sord)
            $sord = 'desc'; // 默认倒序
        
        $where = array();
        $where['c.IS_DEL'] = 0;
        $where['a.PRODUCT_ID'] = $bill_id;
        $model = M('cq_product');
        
        $count = $model->table('cq_product a')
            ->join('left join cq_product_buy b ON a.PRODUCT_ID = b.PRODUCT_ID')
            ->join('left join lc_user c ON b.USER_ID = c.USER_ID')
            ->where($where)
            ->count();
        
        // 根据记录数分页
        if ($count > 0) {
            $total_pages = ceil($count / $limit);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages)
            $page = $total_pages;
        $start = $limit * $page - $limit;
        if ($start < 0)
            $start = 0;
        $responce->page = intval($page); // 当前页
        $responce->total = $total_pages; // 总页数
        $responce->records = $count; // 总记录数
        
        $list = $model->table('cq_product a')
            ->join('left join cq_product_buy b ON a.PRODUCT_ID = b.PRODUCT_ID')
            ->join('left join lc_user c ON b.USER_ID = c.USER_ID')
            ->join('left join cq_back_user d ON b.UP_USER = d.BACK_USER_ID')
            ->field('b.ADD_TIME,a.PLAT_SHORTNAME,b.PRODUCT_BUY_ID,b.BUY_MONEY,b.SERIAL_NO,b.HANDLE_STATUS,b.BUY_TIME,c.MOBILE,c.USER_NAME,d.BACK_USER_NAME')
            ->where($where)
            ->order($sidx . ' ' . $sord)
            ->limit($start, $limit)
            ->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['mobile'] = $value['mobile']; // 手机号
            $responce->rows[$key]['serial_no'] = $value['serial_no']; // 流水号
            $responce->rows[$key]['add_time'] = $value['add_time']; // 上线时间
            $handle_type = $value['handle_status'];
            if ($handle_type == 1) {
                $handle_type = '已处理';
                $responce->rows[$key]['xiugai'] = "<a href='javascript:openEditCheck($value[product_buy_id],$value[plat_shortname],$value[handle_status]);'>修改</a>";
                $responce->rows[$key]['luru'] = "<a style='display:none' href='javascript:openEditCheck($value[product_id],$value[plat_id]);'>录入</a>";
            } elseif ($handle_type == 2) {
                $handle_type = '未处理';
                $responce->rows[$key]['xiugai'] = "<a style='display:none' href='javascript:openEditCheck($value[product_buy_id],$value[plat_shortname],$value[handle_status]);'>修改</a>";
                $responce->rows[$key]['luru'] = "<a href='javascript:openEditCheck($value[product_buy_id],$value[plat_shortname],$value[handle_status]);'>录入</a>";
            }
            $responce->rows[$key]['product_buy_id'] = $value['product_buy_id']; // 购买产品ID
            $responce->rows[$key]['plat_shortname'] = $value['plat_shortname']; // 平台ID
            $responce->rows[$key]['handle_type'] = $handle_type; // 处理状态
            $responce->rows[$key]['back_user_name'] = $value['back_user_name']; // 处理人
            $responce->rows[$key]['buy_money'] = $value['buy_money']; // 购买金额
            $responce->rows[$key]['buy_time'] = $value['buy_time']; // 购买时间
        }
        $this->ajaxReturn($responce, 'JSON');
    }
    
    // 对账-审核
    public function billReviewed()
    {
        $reviewed_id = $_REQUEST['reviewed_id'];
        $plat_id = $_REQUEST['plat_id'];
        $this->assign('reviewed_id', $reviewed_id);
        $this->assign('plat_id', $plat_id);
        $this->display();
    }

    public function billCheck()
    {
        $reviewed_id = $_POST['reviewed_id'];
        $plat_id = $_POST['plat_id'];
        $mark_id = $_POST['mark'];
        $user_id = session('back_user_id'); // 用户ID
        $time = date("Y-m-d H:i:s", time());
        
        // 满标/流标 解冻
        $where = array();
        $where['PRODUCT_ID'] = $reviewed_id;
        $where['PLAT_SHORTNAME'] = $plat_id;
        
        $data['RELEASE_STATUS'] = $mark_id;
        $data['IS_THAW'] = 1;
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = $time;
        $model = M('cq_product');
        
        // 添加产品满标下架记录
        $group['RELEASE_USER'] = $user_id;
        $group['RELEASE_TIME'] = $time;
        $group['SHELEVS_REMARKS'] = "满标下架";
        $group['PRODUCT_ID'] = $reviewed_id;
        $group['RELEASE_STATUS'] = $mark_id;
        
        // 满标-解冻返利
        // 查询该产品购买记录和返利相关的内容
        $what = array();
        $what['a.PRODUCT_ID'] = $reviewed_id;
        $what['a.BUY_MONEY'] = array(
            "gt",
            0
        );
        $what['a.IS_DEL'] = 0;
        $result = M("cq_product_buy a")->join("join cq_user_finance_record b on a.SERIAL_NO=b.SERIAL_NO")
            ->where($what)
            ->field("a.USER_ID,a.BUY_MONEY,b.USER_FINANCE_RECORE_ID,b.CASH_MONEY")
            ->select();
        foreach ($result as $key => $value) {
            $u_id = $value['user_id'];
            $u_f_id = $value['user_finance_recore_id'];
            $cash_money = $value['cash_money'];
            $nowtime = date("Y-m-d H:i:s", time());
            // 修改状态为解冻
            $note['FREEZE_STATUS'] = 2;
            $note['UNFREEZE_TIME'] = $nowtime;
            $note['UP_USER'] = $user_id;
            $note['UP_TIME'] = $nowtime;
            M('cq_user_finance_record')->where("USER_FINANCE_RECORE_ID=" . $u_f_id)->save($note);
            
            // 更改资产字段值
            $userInfo = M("cq_user_finance")->where("USER_ID=" . $u_id . " and IS_DEL=0")
                ->field("FROZEN_AMOUNT,CASH_AMOUNT")
                ->find();
            
            $new_Money = $new_Frozen = 0;
            // 账户余额 增
            $new_Money = bcadd($userInfo['cash_amount'], $cash_money, 2);
            // 冻结金额 减
            $new_Frozen = bcsub($userInfo['frozen_amount'], $cash_money, 2);
            $data_f['CASH_AMOUNT'] = $new_Money;
            $data_f['FROZEN_AMOUNT'] = $new_Frozen;
            $data_f['UP_TIME'] = $nowtime;
            M("cq_user_finance")->where("USER_ID=" . $u_id . " and IS_DEL=0")->save($data_f);
            /*
             * 好友返利：
             * **判断投资人是否是被邀请的
             * ***否--到此结束
             * ***是--判断邀请奖励是否已发放
             * ******否--发放邀请奖励 然后发放邀请投资的返利
             * ******是--发放邀请投资的返利
             */
            $useTime = date("Y-m-d", time());
            $inviteInfo = M("cq_invitation_friends")->where("USER_ID=" . $u_id . " and IS_DEL=0")
                ->field("INVITATION_CODE,IS_REWARD")
                ->find();
            if ($inviteInfo) {
                $thisUser = M('cq_invitation_code')->where("INVITATION_CODE='" . $inviteInfo['invitation_code'] . "' and IS_DEL=0")->getField("USER_ID");
                // 一级邀请人存在
                if ($thisUser) {
                    if ($inviteInfo['IS_REWARD'] == 0) {
                        //
                        $rule = M('cq_serial_rule')->where("SERIAL_TYPE='06' and SERIAL_DAY='" . $useTime . "'")
                            ->field("SERIAL_NUM")
                            ->find();
                        // 如果没有记录
                        $number = "";
                        if (! $rule['serial_num']) {
                            $param = array();
                            $param['SERIAL_TYPE'] = '06';
                            $param['SERIAL_DAY'] = $useTime;
                            $param['SERIAL_NUM'] = '1';
                            M('cq_serial_rule')->add($param);
                            $number = 1;
                        } else {
                            M('cq_serial_rule')->where("SERIAL_TYPE='06' and SERIAL_DAY='" . $useTime . "'")->setInc("SERIAL_NUM");
                            $number = intval($rule['serial_num']) + 1;
                        }
                        $serial_no = "06" . date("ymd") . sprintf("%05d", $number);
                        $option = array();
                        $option['USER_ID'] = $thisUser;
                        $option['TYPE'] = '06';
                        $option['CASH_MONEY'] = 2;
                        $option['SERIAL_NO'] = $serial_no;
                        $option['OPERATE_TIME'] = $nowtime;
                        $option['FREEZE_STATUS'] = 2;
                        $option['UNFREEZE_TIME'] = $nowtime;
                        $option['REMARKS'] = "依据平台：《邀请好友人数返利》返利规则";
                        $option['ADD_USER'] = $thisUser;
                        $option['ADD_TIME'] = $nowtime;
                        $option['REBATE_LEVEL'] = 1;
                        
                        // 添加返利记录
                        M("cq_user_finance_record")->add($option);
                        // 修改资产信息
                        $nowCash = M("cq_user_finance")->where("USER_ID=" . $thisUser . " and IS_DEL=0")->getField("CASH_AMOUNT");
                        $newCash = bcadd($nowCash, 2, 2);
                        M("cq_user_finance")->where("USER_ID=" . $thisUser . " and IS_DEL=0")->setField("CASH_AMOUNT", $newCash);
                        // 修改状态 表示已返利
                        M("cq_invitation_friends")->where("USER_ID=" . $u_id . " and IS_DEL=0")->setField("IS_REWARD", 1);
                    }
                    // 好友投资返利 :: 开始
                    $rule = M('cq_serial_rule')->where("SERIAL_TYPE='06' and SERIAL_DAY='" . $useTime . "'")
                        ->field("SERIAL_NUM")
                        ->find();
                    // 如果没有记录
                    $number = "";
                    if (! $rule['serial_num']) {
                        $param = array();
                        $param['SERIAL_TYPE'] = '06';
                        $param['SERIAL_DAY'] = $useTime;
                        $param['SERIAL_NUM'] = '1';
                        M('cq_serial_rule')->add($param);
                        $number = 1;
                    } else {
                        M('cq_serial_rule')->where("SERIAL_TYPE='06' and SERIAL_DAY='" . $useTime . "'")->setInc("SERIAL_NUM");
                        $number = intval($rule['serial_num']) + 1;
                    }
                    $serial_no = "06" . date("ymd") . sprintf("%05d", $number);
                    // 判断返利率
                    // 1 统计$thisUser一级推广人的返利累计收益
                    
                    $whereFinance = array();
                    $whereFinance['USER_ID'] = $thisUser;
                    $whereFinance['IS_DEL'] = 0;
//                     $whereFinance['TYPE'] = 2;
                    $whereFinance['FREEZE_STATUS'] = 2;
                    $finance = M('cq_user_finance_record')->where($whereFinance)
                        ->field("sum(CASH_MONEY) as sum")
                        ->select();
                    $firstTotal = number_format($finance[0]['sum'], 2); // 返利累计收益
                                                                        
                    // 2 如果累计收益超过3000元，则给$u_id的比例为15%，不超过则10%
                    $rate = 0;
                    if ($firstTotal > 0 && $firstTotal < 3000) {
                        $rate = 0.1;
                    } elseif ($firstTotal >= 3000) {
                        $rate = 0.15;
                    }
                    // 计算返利金额
                    $cashMoney = number_format($result['buy_money'] * $rate, 2);
                    $option = array();
                    $option['USER_ID'] = $thisUser;
                    $option['TYPE'] = '08';
                    $option['CASH_MONEY'] = $cashMoney;
                    $option['SERIAL_NO'] = $serial_no;
                    $option['OPERATE_TIME'] = $nowtime;
                    $option['FREEZE_STATUS'] = 2;
                    $option['UNFREEZE_TIME'] = $nowtime;
                    $option['REMARKS'] = "依据平台：《邀请好友投资得返利》返利<一级提成>规则";
                    $option['ADD_USER'] = $thisUser;
                    $option['ADD_TIME'] = $nowtime;
                    $option['REBATE_LEVEL'] = 2;
                    // 添加返利记录
                    M("cq_user_finance_record")->add($option);
                    // 更改账户金额
                    $nowCash = M("cq_user_finance")->where("USER_ID=" . $thisUser . " and IS_DEL=0")->getField("CASH_AMOUNT");
                    $newCash = bcadd($nowCash, $cashMoney, 2);
                    M("cq_user_finance")->where("USER_ID=" . $thisUser . " and IS_DEL=0")->setField("CASH_AMOUNT", $newCash);
                }
                
                // 计算二级推广人返利
                $inviteSecond = M("cq_invitation_friends")->where("USER_ID=" . $thisUser . " and IS_DEL=0")
                    ->field("INVITATION_CODE,IS_REWARD")
                    ->find();
                
                if ($inviteSecond) {
                    $thisSecUser = M('cq_invitation_code')->where("INVITATION_CODE='" . $inviteSecond['invitation_code'] . "' and IS_DEL=0")->getField("USER_ID");
                    // 二级邀请人存在
                    if ($thisSecUser) {
                        // 好友投资返利 :: 开始
                        $rule = M('cq_serial_rule')->where("SERIAL_TYPE='06' and SERIAL_DAY='" . $useTime . "'")
                            ->field("SERIAL_NUM")
                            ->find();
                        // 如果没有记录
                        $number = "";
                        if (! $rule['serial_num']) {
                            $param = array();
                            $param['SERIAL_TYPE'] = '06';
                            $param['SERIAL_DAY'] = $useTime;
                            $param['SERIAL_NUM'] = '1';
                            M('cq_serial_rule')->add($param);
                            $number = 1;
                        } else {
                            M('cq_serial_rule')->where("SERIAL_TYPE='06' and SERIAL_DAY='" . $useTime . "'")->setInc("SERIAL_NUM");
                            $number = intval($rule['serial_num']) + 1;
                        }
                        $serial_no = "06" . date("ymd") . sprintf("%05d", $number);
                        // 判断返利率
                        // 1 统计$thisUser一级推广人的返利累计收益
                        
                        $whereFinanceSec = array();
                        $whereFinanceSec['USER_ID'] = $thisSecUser;
                        $whereFinanceSec['IS_DEL'] = 0;
//                         $whereFinanceSec['TYPE'] = 2;
                        $whereFinanceSec['FREEZE_STATUS'] = 2;
                        $financeSec = M('cq_user_finance_record')->where($whereFinanceSec)
                            ->field("sum(CASH_MONEY) as sum")
                            ->select();
                        $secTotal = number_format($financeSec[0]['sum'], 2); // 返利累计收益
                                                                             
                        // 2 如果累计收益超过3000元，则比例为10%，不超过则5%
                        $secRate = 0;
                        if ($secTotal > 0 && $secTotal < 3000) {
                            $secRate = 0.05;
                        } elseif ($secTotal >= 3000) {
                            $secRate = 0.1;
                        }
                        // 计算返利金额
                        $cashMoneySec = number_format($result['buy_money'] * $secRate, 2);
                        $option = array();
                        $option['USER_ID'] = $thisSecUser;
                        $option['TYPE'] = '09';
                        $option['CASH_MONEY'] = $cashMoneySec;
                        $option['SERIAL_NO'] = $serial_no;
                        $option['OPERATE_TIME'] = $nowtime;
                        $option['FREEZE_STATUS'] = 2;
                        $option['UNFREEZE_TIME'] = $nowtime;
                        $option['REMARKS'] = "依据平台：《邀请好友得返利》返利<二级提成>规则";
                        $option['ADD_USER'] = $thisSecUser;
                        $option['ADD_TIME'] = $nowtime;
                        // 添加返利记录
                        M("cq_user_finance_record")->add($option);
                        // 更改账户金额
                        $nowCashSec = M("cq_user_finance")->where("USER_ID=" . $thisSecUser . " and IS_DEL=0")->getField("CASH_AMOUNT");
                        $newCashSec = bcadd($nowCashSec, $cashMoneySec, 2);
                        M("cq_user_finance")->where("USER_ID=" . $thisSecUser . " and IS_DEL=0")->setField("CASH_AMOUNT", $newCashSec);
                    }
                }
                // 计算二级返利结束
            }
            /* 首次投资并且金额超过两千 返利20元 */
        }
        M("cq_product_release")->add($group);
        $m = $model->where($where)->save($data);
        if ($m) {
            $this->ajaxReturn('1', 'JSON');
        } else {
            $this->ajaxReturn('0', 'JSON');
        }
    }
    // 对账管理-修改 录入
    public function billChange()
    {
        $pro_id = $_REQUEST['pro_id'];
        $plat_id = $_REQUEST['plat_id'];
        $type_id = $_REQUEST['type_id'];
        $this->assign("type_id", $type_id);
        $options = '';
        if ($type_id == 1) {
            $options = '修改';
        } elseif ($type_id == 2) {
            $options = '录入';
        }
        $model = M('cq_product_buy');
        $where = array();
        $where['PRODUCT_BUY_ID'] = $pro_id;
        $list = $model->field('BUY_MONEY,BUY_TIME')
            ->where($where)
            ->find();
        
        $this->assign('product_buy_id', $pro_id);
        $this->assign('pro_list', $list);
        $this->assign('option', $options); // 修改录入判断
        $this->display();
    }

    public function billXiugai()
    {
        $product_buy_id = $_POST['product_buy_id'];
        $buy_money = $_POST['buy_money'];
        $buy_time = $_POST['buy_time'];
        $type_id = $_POST['type_id'];
        $user_id = session('back_user_id'); // 用户ID
        $time = date("Y-m-d H:i:s", time());
        // 修改购买记录
        $where = array();
        $where['PRODUCT_BUY_ID'] = $product_buy_id;
        $data['BUY_MONEY'] = $buy_money;
        $data['BUY_TIME'] = $buy_time;
        $data['HANDLE_STATUS'] = 1;
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = $time;
        
        $model = M('cq_product_buy');
        $m = $model->where($where)->save($data);
        // 进行返利记录处理
        // 查询产品相关信息
        $result = M("cq_product_buy a")->join("cq_product b on a.PRODUCT_ID=b.PRODUCT_ID")
            ->where("a.PRODUCT_BUY_ID=$product_buy_id and a.IS_DEL=0")
            ->field("a.USER_ID,a.BUY_MONEY,a.BUY_TIME,a.SERIAL_NO,b.TARGET_NAME,b.INVEST_MONTH,b.INVEST_DAY,b.CQ_RATE")
            ->find();
        // 修改
        if ($type_id == 1) {
            // 计算返利
            // 最新的返利 因为利率是百分比 所以要除以100 这里直接合并在月数和天数那里一块除了
            $new_Finance = 0;
            if ($result['invest_month'] > 0) {
                $new_Finance = bcdiv(bcmul(bcmul($result['buy_money'], $result['cq_rate'], 2), $result['invest_month'], 2), 1200, 2);
            } elseif ($result['invest_day'] > 0) {
                $new_Finance = bcdiv(bcmul(bcmul($result['buy_money'], $result['cq_rate'], 2), $result['invest_day'], 2), 36500, 2);
            }
            // 之前的返利
            $finance_data = M("cq_user_finance_record")->where("USER_ID='" . $result['user_id'] . "' and SERIAL_NO='" . $result['serial_no'] . "' and IS_DEL=0")
                ->field('CASH_MONEY')
                ->find();
            $finance_Money = $finance_data['cash_money'];
            // 返利差值
            $finance_Sub = bcsub($new_Finance, $finance_Money, 2);
            // 购买金额差值
            $money_Sub = bcsub($buy_money, $result['buy_money'], 2);
            // 修改资产表
            $userInfo = M("cq_user_finance")->where("USER_ID=" . $result['user_id'] . " and IS_DEL=0")
                ->field("BUY_AMONUT,FROZEN_AMOUNT")
                ->find();
            $new_Money = $new_Frozen = 0;
            $new_Money = bcadd($userInfo['buy_amonut'], $money_Sub, 2);
            $new_Frozen = bcadd($userInfo['frozen_amount'], $finance_Sub, 2);
            $data_f['BUY_AMONUT'] = $new_Money;
            $data_f['FROZEN_AMOUNT'] = $new_Frozen;
            $data_f['UP_TIME'] = $nowtime;
            M("cq_user_finance")->where("USER_ID=" . $result['user_id'] . " and IS_DEL=0")->save($data_f);
            // 修改返利记录
            $dataFR = array();
            $dataFR['CASH_MONEY'] = $new_Finance;
            $dataFR['UP_USER'] = $user_id;
            $dataFR['UP_TIME'] = $time;
            M("cq_user_finance_record")->where("USER_ID='" . $result['user_id'] . "' and SERIAL_NO='" . $result['serial_no'] . "' and IS_DEL=0")->save($dataFR);
            // 录入
        } elseif ($type_id == 2) {
            // **关于首投超2000 返20红包**
            $Tips['USER_ID'] = $result['user_id'];
            $Tips['BUY_MONEY'] = array(
                "gt",
                0
            );
            $Tips['IS_DEL'] = 0;
            $user_buy = M("cq_product_buy")->where($Tips)->select();
            if (! $user_buy && $result['buy_money'] >= 2000) {
                // 获取另外一个流水号
                $rule_A = M('cq_serial_rule')->where("SERIAL_TYPE='02' and SERIAL_DAY='" . $time . "'")
                    ->field("SERIAL_NUM")
                    ->find();
                // 如果没有记录
                $number_A = "";
                if (! $rule_A['serial_num']) {
                    $op['SERIAL_TYPE'] = '02';
                    $op['SERIAL_DAY'] = $time;
                    $op['SERIAL_NUM'] = '1';
                    M('cq_serial_rule')->add($op);
                    $number_A = 1;
                } else {
                    // 字段 +1
                    M('cq_serial_rule')->where("SERIAL_TYPE='02' and SERIAL_DAY='" . $time . "'")->setInc("SERIAL_NUM");
                    $number_A = intval($rule['serial_num']) + 1;
                }
                $serial_Num = "02" . date("ymd") . sprintf("%05d", $number);
                // 添加返利记录 返利金额非冻结状态
                $data_A['USER_ID'] = $result['user_id'];
                $data_A['TYPE'] = 2;
                $data_A['CASH_MONEY'] = 20;
                $data_A['SERIAL_NO'] = $serial_Num;
                $data_A['OPERATE_TIME'] = $nowtime;
                $data_A['FREEZE_STATUS'] = 2;
                $data_A['UNFREEZE_TIME'] = $nowtime;
                $data_A['REMARKS'] = "首投大于等于2000元 红包奖励";
                $data_A['ADD_USER'] = $result['user_id'];
                $data_A['ADD_TIME'] = $nowtime;
                $data_A['IS_DEL'] = 0;
                M("cq_user_finance_record")->add($data_A);
                // 修改购买人的账号信息 更新购买的金额和返利金额
                $uInfo = M("cq_user_finance")->where("USER_ID=" . $result['user_id'] . " and IS_DEL=0")
                    ->field("CASH_AMOUNT")
                    ->find();
                $new_Money = 0;
                $new_Money = bcadd($uInfo['cash_amount'], 20, 2);
                $data_B['CASH_AMOUNT'] = $new_Money;
                $data_B['UP_TIME'] = $nowtime;
                M("cq_user_finance")->where("USER_ID=" . $result['user_id'] . " and IS_DEL=0")->save($data_B);
            }
            // 计算返利
            $new_Finance = 0;
            if ($result['invest_month'] > 0) {
                // 因为利率是百分比 所以要除以100 这里直接合并在月数和天数那里一块除了
                $new_Finance = bcdiv(bcmul(bcmul($result['buy_money'], $result['cq_rate'], 2), $result['invest_month'], 2), 1200, 2);
            } elseif ($result['invest_day'] > 0) {
                $new_Finance = bcdiv(bcmul(bcmul($result['buy_money'], $result['cq_rate'], 2), $result['invest_day'], 2), 36500, 2);
            }
            // 添加返利记录
            $data_r = array();
            $data_r['USER_ID'] = $result['user_id'];
            $data_r['TYPE'] = 2;
            $data_r['CASH_MONEY'] = $new_Finance;
            $data_r['SERIAL_NO'] = $result['serial_no'];
            $data_r['OPERATE_TIME'] = $time;
            $data_r['FREEZE_STATUS'] = 1;
            $data_r['FREEZE_TIME'] = $time;
            $data_r['REMARKS'] = "购买(" . $result['target_name'] . ")，返利" . $new_Finance . "元";
            $data_r['ADD_USER'] = $result['user_id'];
            $data_r['ADD_TIME'] = $time;
            $data_r['IS_DEL'] = 0;
            M("cq_user_finance_record")->add($data_r);
            // 修改资产表 数据
            $userInfo = M("cq_user_finance")->where("USER_ID=" . $result['user_id'] . " and IS_DEL=0")
                ->field("BUY_AMONUT,FROZEN_AMOUNT")
                ->find();
            $new_Money = $new_Frozen = 0;
            $new_Money = bcadd($userInfo['buy_amonut'], $buy_money, 2);
            $new_Frozen = bcadd($userInfo['frozen_amount'], $new_Finance, 2);
            $data_f['BUY_AMONUT'] = $new_Money;
            $data_f['FROZEN_AMOUNT'] = $new_Frozen;
            $data_f['UP_TIME'] = $time;
            M("cq_user_finance")->where("USER_ID=" . $result['user_id'] . " and IS_DEL=0")->save($data_f);
        }
        if ($m) {
            $this->ajaxReturn('1', 'JSON');
        } else {
            $this->ajaxReturn('0', 'JSON');
        }
    }
    
    // 对账成功
    public function checkSuccess()
    {
        $this->display();
    }
}