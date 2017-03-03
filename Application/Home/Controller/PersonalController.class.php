<?php
namespace Home\Controller;

use Think\Controller;
use Think\Model;

class PersonalController extends BaseController
{

    public function index()
    {}
    // 账户总览
    public function accountView()
    {
        $this->lc_log("账户总览", "accountView");
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $personal = array();
        $level = 1; // 安全等级
        $where = array();
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO')
            ->find();
        if ($result['nick_name']) { // 取昵称 没设置昵称则显示手机号
            $personal['nick_name'] = $result['nick_name'];
        } else {
            $personal['nick_name'] = $result['mobile'];
        }
        if ($result['identity']) { // 是否实名
            $level ++;
            $personal['identity'] = 1;
        } else {
            $personal['identity'] = 0;
        }
        if ($result['email']) { // 是否绑定邮箱
            $level ++;
            $personal['email'] = 1;
        } else {
            $personal['email'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        
        $bank = M('cq_bank')->field('BANK_ID')
            ->where($where)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
        } else {
            $personal['bank'] = 0;
        }
        // 总资产 = 冻结金额 + 可用余额
        $amount = M('cq_user_finance')->where($where)
            ->field("FROZEN_AMOUNT,CASH_AMOUNT")
            ->find();
        $personal['assert'] = bcadd($amount['frozen_amount'], $amount['cash_amount'], 2);
        $personal['balance'] = number_format($amount['cash_amount'], 2);
        $params = $where;
        $params['TYPE'] = 2;
        $params['FREEZE_STATUS'] = 2;
        $finance = M('cq_user_finance_record')->where($params)
            ->field("count(*) as num,sum(CASH_MONEY) as sum")
            ->select();
        $personal['finance_num'] = intval($finance[0]['num']); // 已返利标的
        $personal['finance_sum'] = number_format($finance[0]['sum'], 2); // 返利累计收益
        $option = $where;
        $option['TYPE'] = array(
            "in",
            "2,4"
        );
        $option['FREEZE_STATUS'] = 1;
        $freeze = M('cq_user_finance_record')->where($option)
            ->field("count(*) as num,sum(CASH_MONEY) as sum")
            ->select();
        $personal['freeze_num'] = intval($freeze[0]['num']); // 冻结中标的
        $personal['freeze_sum'] = number_format($freeze[0]['sum'], 2); // 冻结金额
        
        $thisMonth = date("Y-m", time());
        // 本月投资
        $cond['USER_ID'] = $user_id;
        $cond['IS_DEL'] = 0;
        $cond['BUY_TIME'] = array(
            "gt",
            $thisMonth
        );
        $buyCount = M('cq_product_buy')->where($cond)->sum("BUY_MONEY");
        $personal['buycount'] = number_format($buyCount, 2);
        // 本月赚乐扒返利
        $condition['USER_ID'] = $user_id;
        $condition['IS_DEL'] = 0;
        $condition['TYPE'] = 2;
        $condition['FREEZE_STATUS'] = 2;
        $condition['ADD_TIME'] = array(
            "gt",
            $thisMonth
        );
        $financeCount = M('cq_user_finance_record')->where($condition)->sum("CASH_MONEY");
        $personal['financeCount'] = number_format($financeCount, 2);
        // 排行--虚拟
        $where_rank['IS_DEL'] = 0;
        $where_rank['ADD_TIME'] = array(
            "gt",
            $thisMonth
        );
        $rankArr = M('cq_invest_rank')->where($where_rank)
            ->field("sum(INVEST_AMOUNT) as invest_amount,MOBILE")
            ->group('MOBILE')
            ->select();
        // 排行--真实
        $where_buy['a.IS_DEL'] = 0;
        $where_buy['a.BUY_TIME'] = array(
            "gt",
            $thisMonth
        );
        $buyArr = M("cq_product_buy a")->join("lc_user b on a.USER_ID=b.USER_ID")
            ->where($where_buy)
            ->field("SUM(a.BUY_MONEY) as invest_amount,b.MOBILE")
            ->group("a.USER_ID")
            ->order("invest_amount desc")
            ->select();
        $allRank = array_merge($rankArr, $buyArr);
        $sortArr = array();
        foreach ($allRank as $key => $value) {
            $sortArr[] = $value['invest_amount'];
        }
        array_multisort($sortArr, SORT_DESC, SORT_NUMERIC, $allRank);
        
        $sortNum = ""; // 本月投资排名
        if ($buyCount) {
            foreach ($allRank as $key => $value) {
                if ($result['mobile'] == $value['mobile']) {
                    $sortNum = $key + 1;
                }
            }
            $personal['mysort'] = "第" . $sortNum . "名";
        } else {
            $personal['mysort'] = "未投资";
        }
        $this->assign("personal", $personal);
        // dump($personal);exit;
        // 前十排名
        $rankArr = array_slice($allRank, 0, 10);
        $this->assign("rankArr", $rankArr);
        // dump(M("cq_product_buy a")->getLastSql());exit;
        $this->display();
    }
    // ---------------------------账户信息
    public function accountInfo()
    {
        header('Content-type:text/html;charset=UTF-8');
        $this->lc_log("账户信息", "accountInfo");
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $personal = array();
        $level = 1; // 安全等级
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('USER_NAME,NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO,ADDRESS,TRADE_PASSWORD')
            ->find();
        // 手机号
        $personal['mobile'] = $result['mobile'];
        // 地址
        if ($result['address']) {
            $personal['place'] = 1;
            $personal['address'] = $result['address'];
        } else {
            $personal['place'] = 0;
        }
        // 交易密码
        if ($result['trade_password']) {
            $personal['trade_password'] = 1;
        } else {
            $personal['trade_password'] = 0;
        }
        
        // 取昵称 没设置昵称则显示手机号
        if ($result['nick_name']) {
            $personal['nick_name'] = $result['nick_name'];
            $personal['nick'] = 1;
        } else {
            $personal['nick_name'] = $result['mobile'];
            $personal['nick'] = 0;
        }
        // 是否实名认证
        if ($result['identity']) {
            $level ++;
            $personal['identity'] = 1;
            $personal['realname'] = mb_substr($result['user_name'], 0, 1, "utf-8") . "**," . substr($result['identity'], 0, 4) . "****" . substr($result['identity'], strlen($result['identity']) - 4);
        } else {
            $personal['identity'] = 0;
            $personal['realname'] = 0;
        }
        // 是否绑定邮箱
        if ($result['email']) {
            $level ++;
            $personal['email'] = $result['email'];
        } else {
            $personal['email'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        
        $bank = M('cq_bank')->field('BANK_ID')
            ->where($where)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
        } else {
            $personal['bank'] = 0;
        }
        $this->assign("personal", $personal);
        $this->display();
    }
    // 保存昵称
    public function saveNickName()
    {
        $nick_name = $_POST['nick_name'];
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $data['NICK_NAME'] = $nick_name;
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = date('Y-m-d H:i:s');
        $where['USER_ID'] = $user_id;
        $m = M('lc_user')->where($where)->save($data);
        if ($m) {
            $this->ajaxReturn('1', 'JSON');
        } else {
            $this->ajaxReturn('0', 'JSON');
        }
    }
    // 更改密码保存
    public function savePassWord()
    {
        $oldPwd = $_POST['oldPwd'];
        $newPwd = $_POST['newPwd'];
        $newPwd = strtoupper(md5("[" . $newPwd . "][" . $newPwd . "]"));
        $type = $_POST['type'];
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if ($oldPwd > 0) {
            $oldPwd = strtoupper(md5($oldPwd));
            if ($type == 1) {
                $result = M("lc_user")->where("USER_ID = $user_id and IS_DEL = 0")
                    ->field("PASSWORD")
                    ->find();
                $password = $result['password'];
                $data['PASSWORD'] = $newPwd;
            } else {
                $result = M("lc_user")->where("USER_ID = $user_id and IS_DEL = 0")
                    ->field("TRADE_PASSWORD")
                    ->find();
                $password = $result['trade_password'];
                $data['TRADE_PASSWORD'] = $newPwd;
            }
            if ($oldPwd != $password) {
                $this->ajaxReturn("输入的旧密码不正确", 'JSON');
                return false;
            }
        } else {
            $data['TRADE_PASSWORD'] = $newPwd;
        }
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = date('Y-m-d H:i:s');
        $where['USER_ID'] = $user_id;
        $m = M('lc_user')->where($where)->save($data);
        if ($m) {
            $this->ajaxReturn('1', 'JSON');
        } else {
            $this->ajaxReturn('修改失败', 'JSON');
        }
    }
    // 邮箱保存
    public function saveEmail()
    {
        $email = $_POST['email'];
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $data['EMAIL'] = $email;
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = date('Y-m-d H:i:s');
        $where['USER_ID'] = $user_id;
        $m = M('lc_user')->where($where)->save($data);
        if ($m) {
            $this->ajaxReturn('1', 'JSON');
        } else {
            $this->ajaxReturn('0', 'JSON');
        }
    }
    // 实名认证
    public function saveRealname()
    {
        $realname = $_POST['realname'];
        $card = $_POST['card'];
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $data['USER_NAME'] = $realname;
        $data['IDENTITY'] = $card;
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = date('Y-m-d H:i:s');
        $where['USER_ID'] = $user_id;
        $m = M('lc_user')->where($where)->save($data);
        if ($m) {
            $this->ajaxReturn('1', 'JSON');
        } else {
            $this->ajaxReturn('0', 'JSON');
        }
    }
    // 保存地址
    public function saveAddress()
    {
        $address = $_POST['address'];
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $data['ADDRESS'] = $address;
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = date('Y-m-d H:i:s');
        $where['USER_ID'] = $user_id;
        $m = M('lc_user')->where($where)->save($data);
        if ($m) {
            $this->ajaxReturn('1', 'JSON');
        } else {
            $this->ajaxReturn('0', 'JSON');
        }
    }
    // 保存头像
    public function saveIcon()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        // 保存头像
        $image = $_POST['image'];
        // $base64_image=$image;
        $base64_image = str_replace(' ', '+', $image);
        // post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)) {
            // 匹配成功
            if ($result[2] == 'jpeg') {
                $image_name = uniqid() . '.jpg';
                // 纯粹是看jpeg不爽才替换的
            } else {
                $image_name = uniqid() . '.' . $result[2];
            }
            $path = "upload/image/" . $user_id;
            if (! is_dir($path)) {
                mkdir($path, 0777);
                // $this->ajaxReturn($path,'JSON');
            }
            $image_file2 = "/upload/image/" . $user_id . "/{$image_name}";
            $image_file = dirname(THINK_PATH) . "/upload/image/" . $user_id . "/{$image_name}";
            // $this->ajaxReturn($result[1],'JSON');
            // 服务器文件存储路径
            if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))) {
                $data['USER_PHOTO'] = $image_file2;
                $data['UP_USER'] = $user_id;
                $data['UP_TIME'] = date('Y-m-d H:i:s');
                $where['USER_ID'] = $user_id;
                $m = M('lc_user')->where($where)->save($data);
                if ($m) {
                    $this->ajaxReturn('1', 'JSON');
                } else {
                    $this->ajaxReturn("保存出错0.", 'JSON');
                }
            } else {
                $this->ajaxReturn("保存出错1", 'JSON');
            }
        } else {
            $this->ajaxReturn("上传出错", 'JSON');
        }
    }
    // 提现申请--开始
    public function wdApply()
    {
        $this->lc_log("提现申请", "wdApply");
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $personal = array();
        $level = 1; // 安全等级
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO,TRADE_PASSWORD')
            ->find();
        if ($result['nick_name']) { // 取昵称 没设置昵称则显示手机号
            $personal['nick_name'] = $result['nick_name'];
        } else {
            $personal['nick_name'] = $result['mobile'];
        }
        if ($result['identity']) { // 是否实名
            $level ++;
            $personal['identity'] = 1;
        } else {
            $personal['identity'] = 0;
        }
        if ($result['email']) { // 是否绑定邮箱
            $level ++;
            $personal['email'] = 1;
        } else {
            $personal['email'] = 0;
        }
        // 交易密码
        if ($result['trade_password']) {
            $personal['trade_password'] = 1;
        } else {
            $personal['trade_password'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        // 银行卡
        $where_b['a.USER_ID'] = $user_id;
        $where_b['a.IS_DEL'] = 0;
        $where_b['b.IS_DEL'] = 0;
        $where_b['b.PARENT_ID'] = 19;
        $bank = M('cq_bank a')->join("left join lc_dictionary_small b on a.BANK_TYPE=b.DICSMALL_NO")
            ->field('a.BANK_ID,a.BANK_NUMBER,b.DICSMALL_NAME,b.REDUNDANCY3,b.REDUNDANCY4')
            ->where($where_b)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
            foreach ($bank as $key => $value) {
                $bank[$key]['bank_number'] = substr($value['bank_number'], 0, 4) . "*******" . substr($value['bank_number'], strlen($value['bank_number']) - 4);
            }
            $this->assign("bank", $bank);
        } else {
            $personal['bank'] = 0;
        }
        $amount = M('cq_user_finance')->where($where)
            ->field("CASH_AMOUNT")
            ->find();
        $personal['balance'] = number_format($amount['cash_amount'], 2);
        $this->assign("personal", $personal);
        $this->display();
    }
    // 提现申请--结束
    // 添加银行卡
    public function addBank()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        // 用户信息
        $user_data = M('lc_user')->where("USER_ID=$user_id and IS_DEL=0")
            ->field("USER_ID,USER_NAME")
            ->find();
        $this->assign("user_data", $user_data);
        // 银行列表
        $bank_data = M("lc_dictionary_small")->where("PARENT_ID=19 and IS_DEL=0")
            ->field("DICSMALL_NO,DICSMALL_NAME")
            ->select();
        $this->assign("bank_data", $bank_data);
        // 区域列表
        $area_data = M("cq_area")->where("PARENT_ID=1 and IS_DEL=0")
            ->field("AREA_ID,AREA_NAME")
            ->select();
        $this->assign("area_data", $area_data);
        $this->display();
    }
    // 保存添加的银行卡
    public function saveBank()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            $this->ajaxReturn("登录已失效，请重新登录", 'JSON');
        }
        $data['BANK_NUMBER'] = $_POST['bank_number'];
        $data['BANK_TYPE'] = $_POST['bank_type'];
        $data['BANK_PROVINCE'] = $_POST['bankProvince'];
        $data['BANK_CITY'] = $_POST['bankCity'];
        $data['BANK_ADDRESS'] = $_POST['bankAddress'];
        $msgCode = $_POST['msgCode'];
        $msg_code = session("msg_code");
        $timeLong = $msg_code['sendtime'] - time();
        if ($timeLong > 60) {
            $this->ajaxReturn("验证码超时", 'JSON');
        }
        if ($msgCode != $msg_code['msg_code']) {
            $this->ajaxReturn("验证码错误", 'JSON');
        }
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $data['USER_ID'] = $user_id;
        $data['ADD_USER'] = $user_id;
        $data['ADD_TIME'] = date("Y-m-d H:i:s", time());
        $data['IS_DEL'] = 0;
        $m = M("cq_bank")->add($data);
        if ($m) {
            $this->ajaxReturn("1", 'JSON');
        } else {
            $this->ajaxReturn("添加失败", 'JSON');
        }
    }
    // 删除银行卡
    public function deleteBank()
    {
        $this->assign("bankId", $_REQUEST['bankId']);
        $this->display();
    }
    // 保存删除银行卡
    public function saveDelBank()
    {
        $bank_id = $_POST['bankId'];
        $trade_pwd = $_POST['trade_pwd'];
        $trade_pwd = strtoupper(md5($trade_pwd));
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $trade_password = M("lc_user")->where("USER_ID=$user_id and IS_DEL=0")
            ->field('TRADE_PASSWORD')
            ->find();
        $t_password = $trade_password['trade_password'];
        if ($trade_pwd != $t_password) {
            $this->ajaxReturn("交易密码不正确", 'JSON');
        }
        $data['IS_DEL'] = 1;
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = date("Y-m-d H:i:s", time());
        $m = M("cq_bank")->where("BANK_ID=$bank_id")->save($data);
        if ($m) {
            $this->ajaxReturn("1", 'JSON');
        } else {
            $this->ajaxReturn("删除失败", 'JSON');
        }
    }
    
    // 获得区域
    public function getArea()
    {
        $area_id = $_POST['area_id'];
        $where['PARENT_ID'] = $area_id;
        $where['IS_DEL'] = 0;
        $area_data = M("cq_area")->where($where)
            ->field("AREA_ID,AREA_NAME")
            ->select();
        $this->ajaxReturn($area_data, 'JSON');
    }
    // 提现记录--开始
    public function wdRecode()
    {
        $this->lc_log("提现记录", "wdRecode");
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $personal = array();
        $level = 1; // 安全等级
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO')
            ->find();
        if ($result['nick_name']) { // 取昵称 没设置昵称则显示手机号
            $personal['nick_name'] = $result['nick_name'];
        } else {
            $personal['nick_name'] = $result['mobile'];
        }
        if ($result['identity']) { // 是否实名
            $level ++;
            $personal['identity'] = 1;
        } else {
            $personal['identity'] = 0;
        }
        if ($result['email']) { // 是否绑定邮箱
            $level ++;
            $personal['email'] = 1;
        } else {
            $personal['email'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        
        $bank = M('cq_bank')->field('BANK_ID')
            ->where($where)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
        } else {
            $personal['bank'] = 0;
        }
        $this->assign("personal", $personal);
        $this->display(); // 输出模板
    }
    // 提现列表
    public function wdrecode_list()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $page = intval($_GET['page']); // 当前页
        $start_time = $_GET['start_time'];
        $end_time = $_GET['end_time'];
        if ($start_time && $end_time) {
            $map['a.START_TIME'] = array(
                "between",
                array(
                    $start_time,
                    $end_time
                )
            );
        } else {
            if ($start_time) {
                $map['a.START_TIME'] = array(
                    "gt",
                    $start_time
                );
            }
            if ($end_time) {
                $map['a.START_TIME'] = array(
                    "lt",
                    $end_time
                );
            }
        }
        
        $status = $_GET['status'];
        if ($status >= 0) {
            $map['a.EXAMINE_STATUS'] = $status;
        }
        $map['a.IS_DEL'] = 0;
        $map['a.USER_ID'] = $user_id;
        $map['b.IS_DEL'] = 0;
        $map['b.PARENT_ID'] = 19;
        $User = M('cq_draw_cash a'); // 实例化User对象
        $total_num = $User->join("left join lc_dictionary_small b on a.DRAW_CODE=b.DICSMALL_NO")
            ->where($map)
            ->count(); // 总记录数
        $page_size = 10; // 每页数量
        $page_total = ceil($total_num / $page_size); // 总页数
        $page_start = $page * $page_size;
        $response->total_num = $total_num;
        $response->page_size = $page_size;
        $response->page_total_num = $page_total;
        // 查询满足要求的总记录数
        $list = $User->join("left join lc_dictionary_small b on a.DRAW_CODE=b.DICSMALL_NO")
            ->where($map)
            ->field("a.DRAW_CASH_ID,a.ADD_TIME,a.SERIAL_NO,b.DICSMALL_NAME,a.DRAW_MONEY,a.EXAMINE_STATUS,a.EXAMINE_REMARK")
            ->order('a.ADD_TIME desc')
            ->limit($page_start . ',' . $page_size)
            ->select();
        foreach ($list as $key => $value) {
            $response->list[$key]['add_time'] = $value['add_time'];
            $response->list[$key]['serial_no'] = $value['serial_no'];
            $response->list[$key]['dicsmall_no'] = $value['dicsmall_no'];
            $response->list[$key]['dicsmall_name'] = $value['dicsmall_name'];
            $response->list[$key]['draw_money'] = $value['draw_money'];
            if ($value['examine_status'] == 0) {
                $value['examine_status'] = "已提交";
            } elseif ($value['examine_status'] == 1) {
                $value['examine_status'] = "已成功";
            } else {
                $value['examine_status'] = "失败";
            }
            $response->list[$key]['examine_status'] = $value['examine_status'];
            $response->list[$key]['examine_remark'] = $value['examine_remark'];
            $response->list[$key]['draw_cash_id'] = $value['draw_cash_id'];
        }
        $this->ajaxReturn($response, 'JSON');
    }
    // 保存提现申请
    public function saveCash()
    {
        $trade_password = $_POST['trade_password']; // 交易密码
        $draw_cash = $_POST['draw_cash']; // 提现金额
        $bank_id = $_POST['bank_id']; // 银行id
        $trade_password = strtoupper(md5($trade_password));
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            $this->ajaxReturn("登录已失效，请重新登录", 'JSON');
        }
        $password = M("lc_user")->where("USER_ID=$user_id and IS_DEL=0")
            ->field('TRADE_PASSWORD')
            ->find();
        $t_password = $password['trade_password'];
        if ($trade_password != $t_password) {
            $this->ajaxReturn("交易密码不正确", 'JSON');
        }
        $balance = M('cq_user_finance')->where("USER_ID=$user_id and IS_DEL=0")
            ->field('FROZEN_AMOUNT,CASH_AMOUNT')
            ->find();
        $cash_amount = $balance['cash_amount'];
        if ($cash_amount < $draw_cash) {
            $this->ajaxReturn("可用余额不足", 'JSON');
        }
        
        $bankData = M("cq_bank")->where("BANK_ID=$bank_id and IS_DEL=0")
            ->field("BANK_NUMBER,BANK_TYPE")
            ->find();
        $row = $record = array();
        $row['CASH_MONEY'] = $record['DRAW_MONEY'] = $draw_cash; // 提现金额
        $row['TYPE'] = 4;
        $record['DRAW_TYPE'] = 2; // 提现类型
        $record['DRAW_NO'] = $bankData['bank_number']; // 卡号
        $record['DRAW_CODE'] = $bankData['bank_type']; // 银行
                                                       // 设置操作的用户;
        $row['USER_ID'] = $row['ADD_USER'] = $record['USER_ID'] = $record['ADD_USER'] = $user_id;
        $nowTime = date("Y-m-d H:i:s", time());
        $record['EXAMINE_STATUS'] = 0;
        // 设置记录相关的时间
        $row['OPERATE_TIME'] = $row['FREEZE_TIME'] = $row['ADD_TIME'] = $record['START_TIME'] = $record['ADD_TIME'] = $nowTime;
        $row['IS_DEL'] = $record['IS_DEL'] = 0;
        $row['FREEZE_STATUS'] = 1;
        $today = date("Y-m-d", time());
        $serial = M("cq_serial_rule")->where("SERIAL_TYPE='03' and SERIAL_DAY=$today")->find();
        // 查看和更新流水号
        if ($serial) {
            M("cq_serial_rule")->where("SERIAL_TYPE='03' and SERIAL_DAY=$today")->setInc("SERIAL_NUM");
            $serial_data = "03" . date("ymd", time()) . sprintf("%05d", $serial['serial_num']);
        } else {
            $serial_data = "03" . date("ymd", time()) . "00001";
            $newData['SERIAL_TYPE'] = '03';
            $newData['SERIAL_DAY'] = $today;
            $newData['SERIAL_NUM'] = 1;
            M("cq_serial_rule")->add($newData);
        }
        /* 修改账号信息 */
        $cash_amount = bcsub($cash_amount, $draw_cash, 2);
        $frozen_amount = bcadd($balance['frozen_amount'], $draw_cash, 2);
        $data = array();
        $data['FROZEN_AMOUNT'] = $frozen_amount; // 冻结金额
        $data['CASH_AMOUNT'] = $cash_amount; // 可用金额
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = date("Y-m-d H:i:s", time());
        
        // 流水号
        $row['SERIAL_NO'] = $record['SERIAL_NO'] = $serial_data;
        // 结余
        $row['BALANCE'] = $cash_amount;
        // 保存提现申请记录
        $B = M('cq_draw_cash')->add($record);
        // 添加一条记录
        $C = M('cq_user_finance_record')->add($row);
        // 修改账号信息
        $A = M('cq_user_finance')->where("USER_ID=$user_id and IS_DEL=0")->save($data);
        // 添加消息通知
        // 添加账户信息变动通知
        $datad = array();
        $datad['MES_CONTENT'] = "您于" . $today . "申请提现，提现金额为" . $draw_cash . "元，请注意查收信息。";
        $datad['MES_TYPE'] = 0;
        $datad['MES_TIME'] = $nowTime;
        $datad['USER_ID'] = $user_id;
        $datad['LOOK_TYPE'] = 0;
        $datad['ADD_TIME'] = $nowTime;
        $datad['IS_DEL'] = 0;
        M('lc_message')->add($datad);
        
        if ($A && $B && $C) {
            $this->ajaxReturn("1", 'JSON');
        } else {
            $this->ajaxReturn("提现出错", 'JSON');
        }
    }
    
    // 发送短信
    public function sendCode()
    {
        $mobile = $_POST['mobile'];
        $action = $_POST['action'];
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if ($user_id) {
            $data['USER_ID'] = $user_id;
        }
        if ($mobile == "") {
            $this->ajaxReturn('手机号不可为空', 'JSON');
        }
        // 检测最近发送验证码的时间
        $randcode = mt_rand(10, 99) . mt_rand(10, 99) . mt_rand(10, 99);
        
        $where['MOBILE'] = $mobile;
        if ($action == "addBank") {
            $where['SMS_TYPE'] = '002';
            $content = '验证码：' . $randcode . '， 此验证码为赚乐扒添加银行卡使用。  [赚乐扒]';
        } elseif ($action == "investRepair") {
            $where['SMS_TYPE'] = '004';
            $content = '验证码：' . $randcode . '， 此验证码为投资申报使用。  [赚乐扒]';
        } else {
            $this->ajaxReturn('发送异常', 'JSON');
        }
        
        $result = M('lc_sms')->field('SMS_DATE')
            ->where($where)
            ->order('SMS_ID desc')
            ->limit(1)
            ->find();
        $time_long = 60;
        if ($result['sms_date'] > 0) {
            $time_long = time() - strtotime($result['sms_date']);
        }
        // 限制一分钟可发送一次
        if ($time_long < 60) {
            $this->ajaxReturn('已发送请耐心等待', 'JSON');
        }
        
        $flag = 0;
        // 要post的数据
        $list = M('lc_website')->field('WEBSITE_SMSSN,WEBSITE_SMSPWD')->find();
        $sms_sn = $list['website_smssn'];
        $sms_pwd = $list['website_smspwd'];
        $argv = array(
            'sn' => $sms_sn, // //替换成您自己的序列号
            'pwd' => strtoupper(md5($sms_sn . $sms_pwd)), // 此处密码需要加密 加密方式为 md5(sn+password) 32位大写
            'mobile' => $mobile, // 手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
                                 // 'content'=>iconv( "gb2312", "UTF-8//IGNORE" ,'测试短信[WJKJ]'),//短信内容
            'content' => $content,
            'ext' => '',
            'stime' => '', // 定时时间 格式为2011-6-29 11:09:21
            'msgfmt' => '',
            'rrid' => ''
        );
        // 构造要post的字符串
        foreach ($argv as $key => $value) {
            if ($flag != 0) {
                $params .= "&";
                $flag = 1;
            }
            $params .= $key . "=";
            $params .= urlencode($value);
            $flag = 1;
        }
        $length = strlen($params);
        // 创建socket连接
        $fp = fsockopen("sdk.entinfo.cn", 8061, $errno, $errstr, 10) or exit($errstr . "--->" . $errno);
        // 构造post请求的头
        $header = "POST /webservice.asmx/mdsmssend HTTP/1.1\r\n";
        $header .= "Host:sdk.entinfo.cn\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . $length . "\r\n";
        $header .= "Connection: Close\r\n\r\n";
        // 添加post的字符串
        $header .= $params . "\r\n";
        // 发送post的数据
        fputs($fp, $header);
        $inheader = 1;
        while (! feof($fp)) {
            $line = fgets($fp, 1024); // 去除请求包的头只显示页面的返回数据
            if ($inheader && ($line == "\n" || $line == "\r\n")) {
                $inheader = 0;
            }
            if ($inheader == 0) {
                // echo $line;
            }
        }
        // <string xmlns="http://tempuri.org/">-5</string>
        $line = str_replace("<string xmlns=\"http://tempuri.org/\">", "", $line);
        $line = str_replace("</string>", "", $line);
        $result = explode("-", $line);
        // echo $line."-------------";
        if (count($result) > 1) {
            $this->ajaxReturn('发送失败', 'JSON');
            // echo '发送失败返回值为:'.$line.'。请查看webservice返回值对照表';
        } else {
            $data['SMS_CONTENT'] = $content;
            $data['MOBILE'] = $mobile;
            $data['SMS_DATE'] = date('Y-m-d H:i:s', time());
            $data['SMS_TYPE'] = '002';
            $m = M('lc_sms')->add($data);
            $sendcode['msg_code'] = $randcode;
            $sendcode['sendtime'] = time();
            session('msg_code', $sendcode);
            $this->ajaxReturn('1', 'JSON');
            // echo '发送成功 返回值为:'.$line;
        }
    }
    // //////////
    
    // 发送短信
    public function sendCode_bak()
    {
        $mobile = $_POST['mobile'];
        $action = $_POST['action'];
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if ($user_id) {
            $data['USER_ID'] = $user_id;
        }
        if ($mobile == "") {
            $this->ajaxReturn('手机号不可为空', 'JSON');
        }
        // 检测最近发送验证码的时间
        $randcode = mt_rand(10, 99) . mt_rand(10, 99) . mt_rand(10, 99);
        
        $where['MOBILE'] = $mobile;
        if ($action == "addBank") {
            $where['SMS_TYPE'] = '002';
            $content = '【赚乐扒】验证码:' . $randcode . '。此验证码为赚乐扒添加银行卡使用，如非本人操作可忽略。有效期为1分钟，请尽快验证。';
        } elseif ($action == "investRepair") {
            $where['SMS_TYPE'] = '004';
            $content = '【赚乐扒】验证码:' . $randcode . '。此验证码为投资申报使用，如非本人操作可忽略。有效期为1分钟，请尽快验证。';
        } else {
            $this->ajaxReturn('发送异常', 'JSON');
        }
        
        $result = M('lc_sms')->field('SMS_DATE')
            ->where($where)
            ->order('SMS_ID desc')
            ->limit(1)
            ->find();
        $time_long = 60;
        if ($result['sms_date'] > 0) {
            $time_long = time() - strtotime($result['sms_date']);
        }
        // 限制一分钟可发送一次
        if ($time_long < 60) {
            $this->ajaxReturn('已发送请耐心等待', 'JSON');
        }
        
        // 以下为核心代码部分
        
        $apikey = "595ebdb56cce86292eea428f9f22791d"; // 修改为您的apikey(https://www.yunpian.com)登录官网后获取
        
        $ch = curl_init();
        
        /* 设置验证方式 */
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept:text/plain;charset=utf-8',
            'Content-Type:application/x-www-form-urlencoded',
            'charset=utf-8'
        ));
        
        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        /* 设置超时时间 */
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // 发送短信
        
        $data = array(
            'text' => $content,
            'apikey' => $apikey,
            'mobile' => $mobile
        );
        
        curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/single_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $json_data = curl_exec($ch);
        if ($json_data != 0) {
            $this->ajaxReturn('发送失败', 'JSON');
            // echo '发送失败返回值为:'.$line.'。请查看webservice返回值对照表';
        } else {
            $data['SMS_CONTENT'] = $content;
            $data['MOBILE'] = $mobile;
            $data['SMS_DATE'] = date('Y-m-d H:i:s', time());
            $data['SMS_TYPE'] = '002';
            $m = M('lc_sms')->add($data);
            $sendcode['msg_code'] = $randcode;
            $sendcode['sendtime'] = time();
            session('msg_code', $sendcode);
            $this->ajaxReturn('1', 'JSON');
            // echo '发送成功 返回值为:'.$line;
        }
    }
    
    // 投资平台-------开始
    public function investPlat()
    {
        $this->lc_log("投资平台", "investPlat");
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $personal = array();
        $level = 1; // 安全等级
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO')
            ->find();
        if ($result['nick_name']) { // 取昵称 没设置昵称则显示手机号
            $personal['nick_name'] = $result['nick_name'];
        } else {
            $personal['nick_name'] = $result['mobile'];
        }
        if ($result['identity']) { // 是否实名
            $level ++;
            $personal['identity'] = 1;
        } else {
            $personal['identity'] = 0;
        }
        if ($result['email']) { // 是否绑定邮箱
            $level ++;
            $personal['email'] = 1;
        } else {
            $personal['email'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        
        $bank = M('cq_bank')->field('BANK_ID')
            ->where($where)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
        } else {
            $personal['bank'] = 0;
        }
        $this->assign("personal", $personal);
        // 统计投资平台总数
        // *查询投资的产品
        $product_id_arr = M("cq_product_buy")->where("USER_ID=$user_id and BUY_MONEY > 0 and IS_DEL=0")
            ->field("PRODUCT_ID")
            ->group("PRODUCT_ID")
            ->select();
        if ($product_id_arr) {
            $product_id_list = "";
            foreach ($product_id_arr as $key => $value) {
                $product_id_list .= $value['product_id'] . ",";
            }
            $product_id_list = substr($product_id_list, 0, - 1);
            $map['a.PRODUCT_ID'] = array(
                "in",
                $product_id_list
            );
            // **统计平台
            $plat_count = M("cq_product a")->join("left join cq_plat b on a.PLAT_SHORTNAME=b.PLAT_ID")
                ->where($map)
                ->group("a.PLAT_SHORTNAME")
                ->field("a.PLAT_SHORTNAME as plat_id,b.PLAT_SHORTNAME")
                ->select();
            $plat_num = count($plat_count);
        } else {
            $plat_num = 0;
            $plat_count = array();
        }
        // 统计冻结的标的
        $freeze_count = M('cq_user_finance_record')->where("USER_ID=$user_id and TYPE=2 and IS_DEL=0 and FREEZE_STATUS=1")->count();
        // 统计已返利的标的
        $finance_count = M('cq_user_finance_record')->where("USER_ID=$user_id and TYPE=2 and IS_DEL=0 and FREEZE_STATUS=2")->count();
        $this->assign("plat_arr", $plat_count); // 平台列表
        $this->assign("plat_count", $plat_num); // 投资平台总数
        $this->assign("freeze_count", $freeze_count);
        $this->assign("finance_count", $finance_count);
        $this->display();
    }
    // 投资平台列表
    public function investPlat_list()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $page = intval($_GET['page']); // 当前页
        $sql = "SELECT sum(c.BUY_MONEY) as invest_money,COUNT(c.PRODUCT_ID) as invest_count,c.PLAT_SHORTNAME as plat_id,d.PLAT_SHORTNAME from (SELECT sum(a.BUY_MONEY) as BUY_MONEY,a.PRODUCT_ID,b.PLAT_SHORTNAME from cq_product_buy a LEFT JOIN cq_product b on a.PRODUCT_ID=b.PRODUCT_ID where a.BUY_MONEY > 0 and a.IS_DEL =0 and a.USER_ID='" . $user_id . "' GROUP BY a.PRODUCT_ID) c LEFT JOIN cq_plat d on c.PLAT_SHORTNAME=d.PLAT_ID GROUP BY c.PLAT_SHORTNAME";
        $Model = new Model();
        $result = $Model->query($sql);
        $total_num = count($result); // 总记录数
        if ($total_num > 0) {
            $page_size = 10; // 每页数量
            $page_total = ceil($total_num / $page_size); // 总页数
            $page_start = $page * $page_size;
            $number = $total_num - $page_start;
            if ($number > 10) {
                $number = 10;
            }
            $response->total_num = $total_num;
            $response->page_size = $page_size;
            $response->page_total_num = $page_total;
            for ($i = 0; $i < $number; $i ++) {
                $k = $i + ($page) * $page_size;
                $response->list[$i]['plat_id'] = $result[$k]['plat_id']; // 平台id
                $response->list[$i]['plat_shortname'] = $result[$k]['plat_shortname']; // 平台名
                $response->list[$i]['invest_money'] = intval($result[$k]['invest_money']); // 投资金额
                $response->list[$i]['invest_count'] = $result[$k]['invest_count']; // 投标总数
                $sql_a = "SELECT SUM(CASH_MONEY) as cash_money,count(c.CASH_MONEY) as cash_count from cq_product a join cq_product_buy b on a.PRODUCT_ID = b.PRODUCT_ID LEFT JOIN cq_user_finance_record c on b.SERIAL_NO=c.SERIAL_NO  where PLAT_SHORTNAME='" . $result[$k]['plat_id'] . "' and b.USER_ID='" . $user_id . "' and b.BUY_MONEY > 0 and c.IS_DEL=0 and c.FREEZE_STATUS=2";
                $finance = $Model->query($sql_a);
                $response->list[$i]['cash_money'] = $finance[0]['cash_money']; // 返利金额
                $response->list[$i]['cash_count'] = $finance[0]['cash_count']; // 已返利
                $sql_b = "SELECT count(c.CASH_MONEY) as freeze_count from cq_product a join cq_product_buy b on a.PRODUCT_ID = b.PRODUCT_ID LEFT JOIN cq_user_finance_record c on b.SERIAL_NO=c.SERIAL_NO  where PLAT_SHORTNAME='" . $result[$k]['plat_id'] . "' and b.USER_ID='" . $user_id . "' and b.BUY_MONEY > 0 and c.IS_DEL=0 and c.FREEZE_STATUS=1";
                $freeze = $Model->query($sql_b);
                $response->list[$i]['freeze_count'] = $freeze[0]['freeze_count']; // 冻结中
            }
        } else {
            $response->total_num = 0;
            $response->page_size = 10;
            $response->page_total_num = 0;
        }
        $this->ajaxReturn($response, 'JSON');
    }
    
    // 投资记录
    public function investRecord()
    {
        $this->lc_log("投资记录", "investRecord");
        
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $personal = array();
        $level = 1; // 安全等级
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO')
            ->find();
        if ($result['nick_name']) { // 取昵称 没设置昵称则显示手机号
            $personal['nick_name'] = $result['nick_name'];
        } else {
            $personal['nick_name'] = $result['mobile'];
        }
        if ($result['identity']) { // 是否实名
            $level ++;
            $personal['identity'] = 1;
        } else {
            $personal['identity'] = 0;
        }
        if ($result['email']) { // 是否绑定邮箱
            $level ++;
            $personal['email'] = 1;
        } else {
            $personal['email'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        
        $bank = M('cq_bank')->field('BANK_ID')
            ->where($where)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
        } else {
            $personal['bank'] = 0;
        }
        $this->assign('personal', $personal);
        $this->display();
    }
    // 投资记录列表
    public function investrecord_list()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $page = intval($_GET['page']); // 当前页
        $start_time = $_GET['start_time'];
        $end_time = $_GET['end_time'];
        $plat_id = $_GET['plat_id'];
        $productStatus = $_GET['productStatus'];
        $handleStatus = $_GET['handleStatus'];
        $globalType = $_GET['globalType'];
        // 判断时间
        if ($start_time && $end_time) {
            $map['a.ADD_TIME'] = array(
                "between",
                array(
                    $start_time,
                    $end_time
                )
            );
        } else {
            if ($start_time) {
                $map['a.ADD_TIME'] = array(
                    "gt",
                    $start_time
                );
            }
            if ($end_time) {
                $map['a.ADD_TIME'] = array(
                    "lt",
                    $end_time
                );
            }
        }
        // 平台
        if ($plat_id > 0) {
            $map['c.PLAT_SHORTNAME'] = $plat_id;
        }
        $status = $_GET['status'];
        if ($status > 0) {
            $map['b.FREEZE_STATUS'] = $status;
        }
        // 产品状态
        if ($productStatus == "0") {
            $map['c.RELEASE_STATUS'] = 0;
        } elseif ($productStatus == "3,9") {
            $map['c.RELEASE_STATUS'] = array(
                "in",
                "3,9"
            );
        }
        // 已处理和未处理
        $map['a.HANDLE_STATUS'] = $handleStatus;
        $map['a.USER_ID'] = $user_id;
        
        $User = M('cq_product_buy a'); // 实例化User对象
        $total_num = $User->join("left join cq_user_finance_record b on a.SERIAL_NO=b.SERIAL_NO")
            ->join("left join cq_product c on a.PRODUCT_ID=c.PRODUCT_ID")
            ->join("left join cq_plat d on c.PLAT_SHORTNAME=d.PLAT_ID")
            ->where($map)
            ->count(); // 总记录数
        $page_size = 10; // 每页数量
        $page_total = ceil($total_num / $page_size); // 总页数
        $page_start = $page * $page_size;
        $response->total_num = $total_num;
        $response->page_size = $page_size;
        $response->page_total_num = $page_total;
        // 查询满足要求的总记录数
        $list = $User->join("left join cq_user_finance_record b on a.SERIAL_NO=b.SERIAL_NO")
            ->join("left join cq_product c on a.PRODUCT_ID=c.PRODUCT_ID")
            ->join("left join cq_plat d on c.PLAT_SHORTNAME=d.PLAT_ID")
            ->where($map)
            ->field("a.BUY_MONEY,a.ADD_TIME,a.BUY_TIME,a.SERIAL_NO,b.TYPE,b.FREEZE_STATUS,b.CASH_MONEY,c.PRODUCT_ID,c.CQ_RATE,c.TARGET_NAME,c.INVEST_MONTH,c.INVEST_DAY,c.ANNUAL_INCOME_RATE,c.JUMP_LINK,c.REBATE_TYPE,c.REBATE_OTHER,d.PLAT_LOGO")
            ->order('a.ADD_TIME desc')
            ->limit($page_start . ',' . $page_size)
            ->select();
        // $this->ajaxReturn($User->getLastSql(),'JSON');
        foreach ($list as $key => $value) {
            $response->list[$key]['product_id'] = $value['product_id']; // 标的ID
                                                                        // 投资金额
            if ($value['buy_money']) {
                $response->list[$key]['buy_money'] = intval($value['buy_money']) . "元"; // 返利金额
            } else {
                $response->list[$key]['buy_money'] = "--"; // 返利金额
            }
            $response->list[$key]['add_time'] = $value['add_time']; // 添加时间
            if ($value['buy_time']) {
                $response->list[$key]['buy_time'] = $value['buy_time']; // 购买时间
            } else {
                $response->list[$key]['buy_time'] = ""; // 购买时间
            }
            $response->list[$key]['serial_no'] = $value['serial_no']; // 流水号
            if ($value['cash_money']) {
                $response->list[$key]['cash_money'] = $value['cash_money'] . "元"; // 返利金额
            } else {
                $response->list[$key]['cash_money'] = "--"; // 返利金额
            }
            
            if ($value['freeze_status'] == 1) {
                $value['freeze_status'] = "返利冻结中";
            } elseif ($value['freeze_status'] == 2) {
                $value['freeze_status'] = "已返利";
            } else {
                $value['freeze_status'] = "--";
            }
            $response->list[$key]['freeze_status'] = $value['freeze_status']; // 返利状态
            $response->list[$key]['cq_rate'] = intval($value['cq_rate']) . "%"; // 赚乐扒返利
            $response->list[$key]['target_name'] = $value['target_name']; // 标的名称
            $limit = "";
            if ($value['invest_month']) { // 投资期限
                $limit .= $value['invest_month'] . "个月";
            }
            if ($value['invest_day']) {
                $limit .= $value['invest_day'] . "天";
            }
            $response->list[$key]['limit_time'] = $limit;
            $response->list[$key]['plat_rate'] = $value['annual_income_rate'] . "%"; // 平台年化
            if ($value['rebate_type'] == 1) {
                $rebate_type = "等额本息";
            } elseif ($value['rebate_type'] == 2) {
                $rebate_type = "每月付息，到期还本";
            } elseif ($value['rebate_type'] == 3) {
                $rebate_type = "到期还本付息";
            } elseif ($value['rebate_type'] == 4) {
                $rebate_type = $value['rebate_other'];
            }
            $response->list[$key]['rebate_type'] = $rebate_type; // 还款方式
            $response->list[$key]['jump_link'] = $value['jump_link'];
            $response->list[$key]['plat_logo'] = $value['plat_logo']; // 平台logo
        }
        $this->ajaxReturn($response, 'JSON');
    }
    
    // 收益明细
    public function incomeDet()
    {
        $this->lc_log("收益明细", "incomeDet");
        
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $personal = array();
        $level = 1; // 安全等级
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO')
            ->find();
        if ($result['nick_name']) { // 取昵称 没设置昵称则显示手机号
            $personal['nick_name'] = $result['nick_name'];
        } else {
            $personal['nick_name'] = $result['mobile'];
        }
        if ($result['identity']) { // 是否实名
            $level ++;
            $personal['identity'] = 1;
        } else {
            $personal['identity'] = 0;
        }
        if ($result['email']) { // 是否绑定邮箱
            $level ++;
            $personal['email'] = 1;
        } else {
            $personal['email'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        
        $bank = M('cq_bank')->field('BANK_ID')
            ->where($where)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
        } else {
            $personal['bank'] = 0;
        }
        $this->assign('personal', $personal);
        $this->display();
    }
    // 收益明细列表
    public function incomedet_list()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $page = intval($_GET['page']); // 当前页
        $start_time = $_GET['start_time'];
        $end_time = $_GET['end_time'];
        if ($start_time && $end_time) {
            $map['OPERATE_TIME'] = array(
                "between",
                array(
                    $start_time,
                    $end_time
                )
            );
        } else {
            if ($start_time) {
                $map['OPERATE_TIME'] = array(
                    "gt",
                    $start_time
                );
            }
            if ($end_time) {
                $map['OPERATE_TIME'] = array(
                    "lt",
                    $end_time
                );
            }
        }
        
        $status = $_GET['status'];
        if ($status > 0) {
            $map['TYPE'] = $status;
        }
        $map['USER_ID'] = $user_id;
        $map['IS_DEL'] = 0;
        $map['FREEZE_STATUS'] = 2;
        
        $User = M('cq_user_finance_record'); // 实例化User对象
        $total_num = $User->where($map)->count(); // 总记录数
        $page_size = 10; // 每页数量
        $page_total = ceil($total_num / $page_size); // 总页数
        $page_start = $page * $page_size;
        $response->total_num = $total_num;
        $response->page_size = $page_size;
        $response->page_total_num = $page_total;
        // 查询满足要求的总记录数
        $list = $User->field("OPERATE_TIME,TYPE,CASH_MONEY,REMARKS")
            ->where($map)
            ->order('OPERATE_TIME desc')
            ->limit($page_start . ',' . $page_size)
            ->select();
        
        foreach ($list as $key => $value) {
            $response->list[$key]['operate_time'] = $value['operate_time']; // 操作时间
            $response->list[$key]['cash_money'] = $value['cash_money']; // 金额
            if ($value['type'] == 1) {
                $value['type'] = "产品红包";
            }
            if ($value['type'] == 2) {
                $value['type'] = "投资返利";
            }
            if ($value['type'] == 3) {
                $value['type'] = "系统红包";
            }
            if ($value['type'] == 5) {
                $value['type'] = "平台红包";
            }
            if ($value['type'] == 6) {
                $value['type'] = "好友人数返利";
            }
            if ($value['type'] == 7) {
                $value['type'] = "月排名返利";
            }
            if ($value['type'] == 8) {
                $value['type'] = "好友金额返利";
            }
            if ($value['remarks'] == '' || $value['remarks'] == NULL) {
                $value['remarks'] = '无备注';
            }
            $response->list[$key]['type'] = $value['type']; // 收益类型
            $response->list[$key]['remarks'] = $value['remarks']; // 收益来源
        }
        $this->ajaxReturn($response, 'JSON');
    }
    
    // 跳转记录
    public function skipCode()
    {
        $this->lc_log("跳转记录", "skipCode");
        
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $personal = array();
        $level = 1; // 安全等级
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO')
            ->find();
        if ($result['nick_name']) { // 取昵称 没设置昵称则显示手机号
            $personal['nick_name'] = $result['nick_name'];
        } else {
            $personal['nick_name'] = $result['mobile'];
        }
        if ($result['identity']) { // 是否实名
            $level ++;
            $personal['identity'] = 1;
        } else {
            $personal['identity'] = 0;
        }
        if ($result['email']) { // 是否绑定邮箱
            $level ++;
            $personal['email'] = 1;
        } else {
            $personal['email'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        
        $bank = M('cq_bank')->field('BANK_ID')
            ->where($where)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
        } else {
            $personal['bank'] = 0;
        }
        $this->assign('personal', $personal);
        $this->display();
    }
    // 跳转记录列表
    public function skipcode_list()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $page = intval($_GET['page']); // 当前页
        $start_time = $_GET['start_time'];
        $end_time = $_GET['end_time'];
        if ($start_time && $end_time) {
            $map['JUMP_TIME'] = array(
                "between",
                array(
                    $start_time,
                    $end_time
                )
            );
        } else {
            if ($start_time) {
                $map['JUMP_TIME'] = array(
                    "gt",
                    $start_time
                );
            }
            if ($end_time) {
                $map['JUMP_TIME'] = array(
                    "lt",
                    $end_time
                );
            }
        }
        
        $status = $_GET['status'];
        if ($status > 0) {
            $map['TYPE'] = $status;
        }
        $map['a.USER_ID'] = $user_id;
        $map['a.IS_DEL'] = 0;
        
        $User = M('cq_product_jump a'); // 实例化User对象
        $total_num = $User->where($map)->count(); // 总记录数
        $page_size = 10; // 每页数量
        $page_total = ceil($total_num / $page_size); // 总页数
        $page_start = $page * $page_size;
        $response->total_num = $total_num;
        $response->page_size = $page_size;
        $response->page_total_num = $page_total;
        // 查询满足要求的总记录数
        $list = $User->join('left join cq_product b ON a.PRODUCT_ID = b.PRODUCT_ID')
            ->field("a.JUMP_TIME,b.PRODUCT_ID,b.TARGET_NAME,b.INVEST_MONTH,b.INVEST_DAY,b.ANNUAL_INCOME_RATE,b.CQ_RATE")
            ->where($map)
            ->order('JUMP_TIME desc')
            ->limit($page_start . ',' . $page_size)
            ->select();
        
        foreach ($list as $key => $value) {
            $response->list[$key]['jump_time'] = $value['jump_time']; // 跳转时间
            $response->list[$key]['target_name'] = $value['target_name']; // 标的名称
                                                                          // 期限
            $time = '';
            if (! $value['invest_month'] == '') {
                $time = $value['invest_month'] . '个月';
            } elseif (! $value['invest_day'] == '') {
                $time = $value['invest_day'] . '天';
            }
            // 平台年化
            if (! is_float($value['annual_income_rate'])) {
                $value['annual_income_rate'] = number_format($value['annual_income_rate'], 1);
            }
            // 赚乐扒加息
            if (! is_float($value['cq_rate'])) {
                $value['cq_rate'] = number_format($value['cq_rate'], 1);
            }
            $response->list[$key]['product_id'] = $value['product_id']; // 产品id
            $response->list[$key]['time'] = $time; // 投资期限
            $response->list[$key]['annual_income_rate'] = $value['annual_income_rate']; // 平台年化
            $response->list[$key]['cq_rate'] = $value['cq_rate']; // 赚乐扒加息
        }
        $this->ajaxReturn($response, 'JSON');
    }
    
    // 邀请好友
    public function inviteFriend()
    {
        $this->lc_log("邀请好友", "inviteFriend");
        
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $personal = array();
        $level = 1; // 安全等级
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO')
            ->find();
        if ($result['nick_name']) { // 取昵称 没设置昵称则显示手机号
            $personal['nick_name'] = $result['nick_name'];
        } else {
            $personal['nick_name'] = $result['mobile'];
        }
        if ($result['identity']) { // 是否实名
            $level ++;
            $personal['identity'] = 1;
        } else {
            $personal['identity'] = 0;
        }
        if ($result['email']) { // 是否绑定邮箱
            $level ++;
            $personal['email'] = 1;
        } else {
            $personal['email'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        
        $bank = M('cq_bank')->field('BANK_ID')
            ->where($where)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
        } else {
            $personal['bank'] = 0;
        }
        $this->assign('personal', $personal);
        $inviteCode = "";
        $invite_code = M('cq_invitation_code')->where("user_id=$user_id and IS_DEL=0")
            ->field("SHOUT_URL_MARK")
            ->find();
        if ($invite_code) {
            $inviteCode = $invite_code['shout_url_mark'];
        } else {
            $input = uniqid(md5(microtime(true)), true);
            $base32 = array(
                "2",
                "3",
                "4",
                "5",
                "6",
                "7",
                "8",
                "9",
                "a",
                "b",
                "c",
                "d",
                "e",
                "f",
                "g",
                "h",
                "j",
                "k",
                "m",
                "n",
                "p",
                "q",
                "r",
                "s",
                "t",
                "u",
                "v",
                "w",
                "x",
                "y",
                "z",
                "A",
                "B",
                "C",
                "D",
                "E",
                "F",
                "G",
                "H",
                "J",
                "K",
                "M",
                "N",
                "P",
                "Q",
                "R",
                "S",
                "T",
                "U",
                "V",
                "W",
                "X",
                "Y",
                "Z"
            );
            $hex = md5($input);
            $hexLen = strlen($hex);
            $subHexLen = $hexLen / 8;
            $output = array();
            for ($i = 0; $i < $subHexLen; $i ++) {
                $subHex = substr($hex, $i * 8, 8);
                $int = 0x3FFFFFFF & (1 * ('0x' . $subHex));
                $out = '';
                for ($j = 0; $j < 4; $j ++) {
                    $val = 0x0000001F & $int;
                    $out .= $base32[$val];
                    $int = $int >> 5;
                }
                $output[] = $out;
            }
            $inviteCode = $output[0];
            $data['USER_ID'] = $data['ADD_USER'] = $user_id;
            $data['INVITATION_CODE'] = $input;
            $data['IS_DEL'] = 0;
            $data['ADD_TIME'] = date("Y-m-d H:i:s", time());
            $data['SHOUT_URL_MARK'] = $inviteCode;
            M("cq_invitation_code")->add($data);
        }
        
        $url = $_SERVER['HTTP_HOST'] . "/index.php/s/" . $inviteCode;
        $this->assign("invite_url", $url);
        $this->assign("invite_code", $inviteCode);
        $this->display();
    }
    
    // 邀请记录
    public function inviteRecode()
    {
        $this->lc_log("邀请记录", "inviteRecode");
        
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $personal = array();
        $level = 1; // 安全等级
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO')
            ->find();
        if ($result['nick_name']) { // 取昵称 没设置昵称则显示手机号
            $personal['nick_name'] = $result['nick_name'];
        } else {
            $personal['nick_name'] = $result['mobile'];
        }
        if ($result['identity']) { // 是否实名
            $level ++;
            $personal['identity'] = 1;
        } else {
            $personal['identity'] = 0;
        }
        if ($result['email']) { // 是否绑定邮箱
            $level ++;
            $personal['email'] = 1;
        } else {
            $personal['email'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        
        $bank = M('cq_bank')->field('BANK_ID')
            ->where($where)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
        } else {
            $personal['bank'] = 0;
        }
        // 获取邀请码
        $cond = array();
        $model = M('cq_invitation_code');
        $cond['USER_ID'] = $user_id;
        $cond['IS_DEL'] = 0;
        $code = $model->field('INVITATION_CODE')
            ->where($cond)
            ->find();
        // 计算
        // begin
        $params = array();
        
        $params['INVITATION_CODE'] = $code['invitation_code'];
        $params['IS_DEL'] = 0;
        
        // 好友本月投资总额
        $monthTotalMoney = 0;
        
        // 总邀请已投资人
        $buyCnt = 0;
        
        // 总邀请注册人
        $regCnt = 0;
        
        // 本月提成
        $monthExtMoney = 0;
        
        // 1 看自己有多少邀请的人
        $listFriends = M('cq_invitation_friends')->where($params)->select();
        foreach ($listFriends as $key => $value) {
            
            // 总邀请注册人加1
            $regCnt ++;
            
            $map = array();
            $map['IS_DEL'] = 0;
            $map['USER_ID'] = $value['user_id'];
            $map['BUY_TIME'] = array(
                "gt",
                date('Y-m', time())
            );
            $UserBuyTotal = M('cq_product_buy')->where($map)->sum("BUY_MONEY");
            
            if ($UserBuyTotal) {
                // 本月提成汇总
                $monthTotalMoney += $UserBuyTotal;
                // 总邀请已投资人加1
                $buyCnt ++;
            }
            
            $map = array();
            $map['ADD_TIME'] = array(
                "gt",
                date('Y-m', time())
            );
            $map['IS_DEL'] = 0;
            $map['USER_ID'] = $value['user_id'];
            $map['FREEZE_STATUS'] = 2;
            $userExtMoney = M('cq_user_finance_record')->where($map)->sum('CASH_MONEY');
            if ($userExtMoney) {
                $monthExtMoney += $userExtMoney;
            }
            
            // 看此人有多少邀请的人
            // 查一级推广人的邀请码
            $cond = array();
            $cond['USER_ID'] = $value['user_id'];
            $cond['IS_DEL'] = 0;
            $code = M('cq_invitation_code')->field('INVITATION_CODE')
                ->where($cond)
                ->find();
            
            $params['INVITATION_CODE'] = $code['invitation_code'];
            $listSecFriends = M('cq_invitation_friends')->where($params)->select();
            foreach ($listSecFriends as $key1 => $value1) {
                $regCnt ++;
                
                $map = array();
                $map['IS_DEL'] = 0;
                $map['USER_ID'] = $value1['user_id'];
                $map['BUY_TIME'] = array(
                    "gt",
                    date('Y-m', time())
                );
                $UserBuyTotal1 = M('cq_product_buy')->where($map)->sum("BUY_MONEY");
                if ($UserBuyTotal1) {
                    // 本月提成汇总
                    $monthTotalMoney += $UserBuyTotal1;
                    // 总邀请已投资人加1
                    $buyCnt ++;
                }
                
                $map = array();
                $map['ADD_TIME'] = array(
                    "gt",
                    date('Y-m', time())
                );
                $map['IS_DEL'] = 0;
                $map['USER_ID'] = $value1['user_id'];
                $map['FREEZE_STATUS'] = 2;
                $userExtMoney1 = M('cq_user_finance_record')->where($map)->sum('CASH_MONEY');
                if ($userExtMoney1) {
                    $monthExtMoney += $userExtMoney1;
                }
            }
        }
        
        // end
        
        // 总邀请已投资人
        $personal['buyMan'] = $buyCnt;
        // 总邀请注册人
        $personal['regCnt'] = $regCnt;
        // 本月提成
        $personal['thisMonth_ext'] = $monthExtMoney == 0 ? "0.00" : $monthExtMoney;
        // 好友本月投资总额
        $personal['thisMonth_buy'] = number_format($monthTotalMoney == 0 ? "0.00" : $monthTotalMoney, 2);
        
        $this->assign('personal', $personal);
        
        $this->display();
    }
    
    // 邀请好友记录列表
    public function inviterecode_list()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $page = intval($_GET['page']); // 当前页
        
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $page = intval($_GET['page']); // 当前页
                                       
        // begin
        $model = M('cq_invitation_code');
        $where = array();
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $code = $model->field('INVITATION_CODE')
            ->where($where)
            ->find();
        // begin
        $params = array();
        
        $params['INVITATION_CODE'] = $code['invitation_code'];
        $params['IS_DEL'] = 0;
        
        $ind = 0;
        // 1 看自己有多少邀请的人
        $listFriends = M('cq_invitation_friends')->where($params)->select();
        foreach ($listFriends as $key => $value) {
            
            $user = M("lc_user")->where("IS_DEL = 0 and user_id = '" . $value['user_id'] . "'")->find();
            $response->list[$ind]['add_time'] = $user['add_time']; // 注册时间
            $response->list[$ind]['mobile'] = substr_replace($user['mobile'], '****', 3, 4); // 手机号
            $response->list[$ind]['level'] = '一级'; // 推广等级
            
            $map = array();
            $map['IS_DEL'] = 0;
            $map['USER_ID'] = $value['user_id'];
            $map['BUY_TIME'] = array(
                "gt",
                date('Y-m', time())
            );
            $UserBuyTotal = M('cq_product_buy')->where($map)->sum("BUY_MONEY");
            
            if ($UserBuyTotal) {
                $response->list[$ind]['tag'] = '是'; // 是否投资
                $response->list[$ind]['sum_money'] = $UserBuyTotal; // 本月投资额
            } else {
                $response->list[$ind]['tag'] = '否'; // 是否投资
                $response->list[$ind]['sum_money'] = '0.00'; // 本月投资额
            }
            
            $map = array();
            $map['ADD_TIME'] = array(
                "gt",
                date('Y-m', time())
            );
            $map['IS_DEL'] = 0;
            $map['USER_ID'] = $value['user_id'];
            $map['FREEZE_STATUS'] = 2;
            $userExtMoney = M('cq_user_finance_record')->where($map)->sum('CASH_MONEY');
            if ($userExtMoney) {
                $response->list[$ind]['sum_ext_money'] = $userExtMoney; // 本月提成
            } else {
                $response->list[$ind]['sum_ext_money'] = '0.00'; // 本月提成
            }
            $ind ++;
            // 看此人有多少邀请的人
            // 查一级推广人的邀请码
            $cond = array();
            $cond['USER_ID'] = $value['user_id'];
            $cond['IS_DEL'] = 0;
            $code = M('cq_invitation_code')->field('INVITATION_CODE')
                ->where($cond)
                ->find();
            
            $params['INVITATION_CODE'] = $code['invitation_code'];
            $listSecFriends = M('cq_invitation_friends')->where($params)->select();
            foreach ($listSecFriends as $key1 => $value1) {
                
                $user1 = M("lc_user")->where("IS_DEL = 0 and user_id = '" . $value1['user_id'] . "'")->find();
                $response->list[$ind]['add_time'] = $user1['add_time']; // 注册时间
                $response->list[$ind]['mobile'] = substr_replace($user1['mobile'], '****', 3, 4); // 手机号
                $response->list[$ind]['level'] = '二级'; // 推广等级
                
                $map = array();
                $map['IS_DEL'] = 0;
                $map['USER_ID'] = $value1['user_id'];
                $map['BUY_TIME'] = array(
                    "gt",
                    date('Y-m', time())
                );
                $UserBuyTotal1 = M('cq_product_buy')->where($map)->sum("BUY_MONEY");
                if ($UserBuyTotal1) {
                    $response->list[$ind]['tag'] = '是'; // 是否投资
                    $response->list[$ind]['sum_money'] = $UserBuyTotal1; // 本月投资额
                } else {
                    $response->list[$ind]['tag'] = '否'; // 是否投资
                    $response->list[$ind]['sum_money'] = '0.00'; // 本月投资额
                }
                
                $map = array();
                $map['ADD_TIME'] = array(
                    "gt",
                    date('Y-m', time())
                );
                $map['IS_DEL'] = 0;
                $map['USER_ID'] = $value1['user_id'];
                $map['FREEZE_STATUS'] = 2;
                $userExtMoney1 = M('cq_user_finance_record')->where($map)->sum('CASH_MONEY');
                if ($userExtMoney1) {
                    $response->list[$ind]['sum_ext_money'] = $userExtMoney1; // 本月提成
                } else {
                    $response->list[$ind]['sum_ext_money'] = '0.00'; // 本月提成
                }
                $ind ++;
            }
        }
        
        // end
        $response->total_num = $ind;
        $this->ajaxReturn($response, 'JSON');
    }
    
    // 消息中心
    public function inforMation()
    {
        $this->lc_log("消息中心", "inforMation");
        
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $personal = array();
        $level = 1; // 安全等级
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO')
            ->find();
        if ($result['nick_name']) { // 取昵称 没设置昵称则显示手机号
            $personal['nick_name'] = $result['nick_name'];
        } else {
            $personal['nick_name'] = $result['mobile'];
        }
        if ($result['identity']) { // 是否实名
            $level ++;
            $personal['identity'] = 1;
        } else {
            $personal['identity'] = 0;
        }
        if ($result['email']) { // 是否绑定邮箱
            $level ++;
            $personal['email'] = 1;
        } else {
            $personal['email'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        
        $bank = M('cq_bank')->field('BANK_ID')
            ->where($where)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
        } else {
            $personal['bank'] = 0;
        }
        $this->assign('personal', $personal);
        $this->display();
    }
    // 消息中心列表
    public function information_list()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $page = intval($_GET['page']); // 当前页
        
        $status = $_GET['status'];
        if ($status >= 0) {
            $map['LOOK_TYPE'] = $status;
        }
        $map['USER_ID'] = $user_id;
        $map['IS_DEL'] = 0;
        
        $User = M('lc_message'); // 实例化User对象
        $total_num = $User->where($map)->count(); // 总记录数
        $page_size = 10; // 每页数量
        $page_total = ceil($total_num / $page_size); // 总页数
        $page_start = $page * $page_size;
        $response->total_num = $total_num;
        $response->page_size = $page_size;
        $response->page_total_num = $page_total;
        // 查询满足要求的总记录数
        $list = $User->field("MES_ID,MES_CONTENT,MES_TYPE,MES_TIME,LOOK_TYPE")
            ->where($map)
            ->order('MES_TIME desc')
            ->limit($page_start . ',' . $page_size)
            ->select();
        
        foreach ($list as $key => $value) {
            $response->list[$key]['mes_id'] = $value['mes_id']; // 信息id
            $response->list[$key]['mes_content'] = $value['mes_content']; // 信息内容
            
            $model = M('lc_dictionary_small');
            $where = array();
            $where['PARENT_ID'] = 59;
            $where['DICSMALL_NO'] = $value['mes_type'];
            
            $result = $model->field('DICSMALL_NAME')
                ->where($where)
                ->find();
            $response->list[$key]['mes_type'] = $result['dicsmall_name'] . '消息'; // 信息类型
            $response->list[$key]['mes_time'] = $value['mes_time']; // 发送时间
            $response->list[$key]['look_type'] = $value['look_type']; // 是否查看
        }
        $this->ajaxReturn($response, 'JSON');
    }
    // 消息状态改变
    public function changeState()
    {
        $state = $_POST['changeId'];
        $model = M('lc_message');
        
        $data['LOOK_TYPE'] = 1;
        $where = array();
        $where['MES_ID'] = $state;
        $where['IS_DEL'] = 0;
        
        $result = $model->where($where)->save($data);
        
        if ($result) {
            $this->ajaxReturn(1, 'JSON');
        } else {
            $this->ajaxReturn(0, 'JSON');
        }
    }
    // 标记为已读
    public function signState()
    {
        $state = $_POST['changeId'];
        $str = explode(",", $state);
        $model = M('lc_message');
        
        $data['LOOK_TYPE'] = 1;
        $where = array();
        $where['MES_ID'] = array(
            'in',
            $str
        );
        $where['IS_DEL'] = 0;
        
        $result = $model->where($where)->save($data);
        if ($result) {
            $this->ajaxReturn(1, 'JSON');
        } else {
            $this->ajaxReturn(0, 'JSON');
        }
    }
    // 删除消息
    public function deleteState()
    {
        $state = $_POST['delId'];
        $model = M('lc_message');
        
        $data['IS_DEL'] = 1;
        $where = array();
        $where['MES_ID'] = $state;
        
        $result = $model->where($where)->save($data);
        
        if ($result) {
            $this->ajaxReturn(1, 'JSON');
        } else {
            $this->ajaxReturn(0, 'JSON');
        }
    }
    
    // 投资申报
    public function investRepair()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            redirect(U('Login/login'), 0, "正在跳转……");
        }
        $this->lc_log("投资申报", "investRepair");
        $personal = array();
        $level = 1; // 安全等级
        $where['USER_ID'] = $user_id;
        $where['IS_DEL'] = 0;
        $result = M("lc_user")->where($where)
            ->field('NICK_NAME,MOBILE,IDENTITY,EMAIL,USER_PHOTO')
            ->find();
        if ($result['nick_name']) { // 取昵称 没设置昵称则显示手机号
            $personal['nick_name'] = $result['nick_name'];
        } else {
            $personal['nick_name'] = $result['mobile'];
        }
        if ($result['identity']) { // 是否实名
            $level ++;
            $personal['identity'] = 1;
        } else {
            $personal['identity'] = 0;
        }
        if ($result['email']) { // 是否绑定邮箱
            $level ++;
            $personal['email'] = 1;
        } else {
            $personal['email'] = 0;
        }
        // 头像
        if ($result['user_photo']) {
            $personal['user_photo'] = __ROOT__ . $result['user_photo'];
        } else {
            $personal['user_photo'] = __ROOT__ . '/Public/images/home/icon/user_head_o.png';
        }
        if ($level == 1) { // 安全等级
            $personal['safe'] = "弱";
        } elseif ($level == 2) {
            $personal['safe'] = "中等";
        } elseif ($level == 3) {
            $personal['safe'] = "强";
        }
        
        $bank = M('cq_bank')->field('BANK_ID')
            ->where($where)
            ->select();
        if (count($bank) > 0) {
            $personal['bank'] = 1;
        } else {
            $personal['bank'] = 0;
        }
        $personal['mobile'] = $user_info['mobile'];
        $this->assign("personal", $personal);
        $plat_arr = M("cq_plat")->where("IS_DEL=0")
            ->field("PLAT_ID,PLAT_SHORTNAME")
            ->select();
        $this->assign("plat_arr", $plat_arr);
        $this->display();
    }
    // //保存投资申报
    public function addInvestRepair()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        if (! $user_id) {
            $this->ajaxReturn("登录失效，请重新登录", 'JSON');
        }
        $data['PLAT_ID'] = $_POST['plat_id'];
        $data['USER_NAME'] = $_POST['userName'];
        $data['MOBILE'] = $_POST['mobile'];
        $data['TARGET_NAME'] = $_POST['targetName'];
        $data['INVEST_AMOUNT'] = $_POST['investAmount'];
        $data['INVEST_TIME'] = $_POST['investTime'];
        $msgCode = $_POST['code'];
        // 验证验证码
        $msg_code = session("msg_code");
        $timeLong = $msg_code['sendtime'] - time();
        if ($timeLong > 60) {
            $this->ajaxReturn("验证码超时", 'JSON');
        }
        if ($msgCode != $msg_code['msg_code']) {
            $this->ajaxReturn("验证码错误", 'JSON');
        }
        $data['CHECK_TYPE'] = 1;
        $data['IS_DEL'] = 0;
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $data['ADD_USER'] = $user_id;
        $data['ADD_TIME'] = date("Y-m-d H:i:s", time());
        $m = M("cq_invest_repair")->add($data);
        if ($m) {
            $this->ajaxReturn("1", 'JSON');
        } else {
            $this->ajaxReturn("保存失败", 'JSON');
        }
    }
    // 申报记录
    public function investrepair_list()
    {
        $user_info = session("user_info");
        $user_id = $user_info['user_id'];
        $page = intval($_GET['page']); // 当前页
        $plat_id = $_GET['plat_id']; // 平台
        $map = array();
        $status = $_GET['status'];
        if ($status > 0) {
            $map['a.CHECK_TYPE'] = $status;
        }
        if ($plat_id > 0) {
            $map['a.PLAT_ID'] = $plat_id;
        }
        $map['a.ADD_USER'] = $user_id;
        $map['a.IS_DEL'] = 0;
        
        $User = M('cq_invest_repair a'); // 实例化User对象
        $total_num = $User->where($map)->count(); // 总记录数
        $page_size = 10; // 每页数量
        $page_total = ceil($total_num / $page_size); // 总页数
        $page_start = $page * $page_size;
        $response->total_num = $total_num;
        $response->page_size = $page_size;
        $response->page_total_num = $page_total;
        // 查询满足要求的总记录数
        $list = $User->join("left join cq_plat b on a.PLAT_ID=b.PLAT_ID")
            ->field("b.PLAT_LOGO,b.ADD_TIME,a.INVEST_REPAIR_ID,a.TARGET_NAME,a.INVEST_AMOUNT,a.INVEST_TIME,a.USER_NAME,a.CHECK_TYPE")
            ->where($map)
            ->order('a.INVEST_TIME desc')
            ->limit($page_start . ',' . $page_size)
            ->select();
        
        foreach ($list as $key => $value) {
            $response->list[$key]['plat_logo'] = $value['plat_logo']; // 平台logo
            $response->list[$key]['add_time'] = $value['add_time'];
            $response->list[$key]['invest_repair_id'] = $value['invest_repair_id'];
            $response->list[$key]['target_name'] = $value['target_name'];
            $response->list[$key]['invest_amount'] = intval($value['invest_amount']);
            $response->list[$key]['invest_time'] = $value['invest_time'];
            $response->list[$key]['user_name'] = $value['user_name'];
            $check_type = "";
            if ($value['check_type'] == 1) {
                $check_type = "已提交";
            } elseif ($value['check_type'] == 2) {
                $check_type = "处理中";
            } elseif ($value['check_type'] == 3) {
                $check_type = "审核通过";
            } elseif ($value['check_type'] == 4) {
                $check_type = "审核失败";
            }
            $response->list[$key]['check_type'] = $check_type;
        }
        $this->ajaxReturn($response, 'JSON');
    }
    // 添加 跳转记录
    public function lc_log($str, $str1)
    {
        $ip = get_client_ip(); // IP地址获取
        $user_info = session("user_info");
        $user_id = $user_info['user_id']; // 获取用户id
        if ($user_id) {
            $data['USER_ID'] = $user_id;
        }
        $data['USER_IP'] = $ip;
        $data['USER_LOG'] = $str;
        $data['LOG_DATE'] = date("Y-m-d H:i:s") . " " . substr(date("l"), 0, 3);
        $data['LOG_MTHOD'] = $str1;
        $data['LOG_URL'] = "www.caiqiwang.com/userinfo/" . $str1 . "html";
        M('lc_log')->add($data);
        $site_title = "个人中心-赚乐扒"; // 网站title
        $this->assign('site_title', $site_title);
    }
    
    // 邀请好友
    public function share()
    {
        $shareId = $_REQUEST['share_id'];
        
        $model = M('cq_invitation_code');
        $where = array();
        $where['SHOUT_URL_MARK'] = $shareId;
        $result = $model->field('INVITATION_CODE')
            ->where($where)
            ->find();
        
        $this->assign('share_code', $result['invitation_code']);
        $this->assign('sharId', $sharId);
        $this->display();
    }
    // 快速注册
    public function userSpeedReg()
    {
        $site_title = "快速注册-赚乐扒"; // 网站title
        $this->assign('site_title', $site_title);
        
        $fast_mobile = $_POST['mobile'];
        $fast_pass = $_POST['password'];
        $fast_code = $_POST['invitationCode'];
        $time = date('Y-m-d:H:i:s', time());
        // 检测该号码是否已经完成注册
        $params = array();
        $params['MOBILE'] = $fast_mobile;
        $params['IS_DEL'] = 0;
        $final = M('lc_user')->field('USER_ID')
            ->where($params)
            ->select();
        if ($final) {
            $this->ajaxReturn('该号码已完成注册');
        }
        
        $m = M('lc_user');
        
        $ip = get_client_ip();
        $info['MOBILE'] = $fast_mobile;
        $pwd = strtoupper(md5($fast_pass));
        $info['PASSWORD'] = $pwd;
        $info['USER_TYPE'] = 1;
        $info['ADD_TIME'] = date("Y-m-d H:i:s", time());
        $info['USER_REG_IP'] = $ip;
        $lc_user_id = $m->add($info);
        if ($lc_user_id) {
            // 注册成功
            $condition['SERIAL_TYPE'] = '04';
            $serial_day = date("Y-m-d", time());
            $condition['SERIAL_DAY'] = $serial_day;
            $serial_rule = M('cq_serial_rule')->field('SERIAL_RULE_ID,SERIAL_NUM')
                ->where($condition)
                ->find();
            $c_num = $serial_rule['serial_num'] + 1;
            if ($serial_rule['serial_num'] >= 1) {
                // 如果有记录则更改
                $dataa['SERIAL_NUM'] = $c_num;
                $con['SERIAL_RULE_ID'] = $serial_rule['serial_rule_id'];
                M('cq_serial_rule')->where($con)->save($dataa);
            } else {
                // 没有则添加
                $dataa['SERIAL_NUM'] = $c_num;
                $dataa['SERIAL_DAY'] = $serial_day;
                $dataa['SERIAL_TYPE'] = '04';
                M('cq_serial_rule')->add($dataa);
            }
            // 生成流水号 type+160729（年月日）+5位数
            $ymd = date("ymd", time());
            $num_five = sprintf("%05d", $c_num);
            $serial_no = "04" . $ymd . $num_five;
            $datab['USER_ID'] = $lc_user_id;
            $datab['TYPE'] = 3;
            $datab['CASH_MONEY'] = 10;
            $datab['SERIAL_NO'] = $serial_no;
            $n_time = date("Y-m-d H:i:s", time());
            $datab['OPERATE_TIME'] = $n_time;
            $datab['FREEZE_STATUS'] = 2;
            $datab['UNFREEZE_TIME'] = $n_time;
            $datab['REMARKS'] = '10元注册红包';
            $datab['ADD_USER'] = $lc_user_id;
            $datab['ADD_TIME'] = $n_time;
            $datab['IS_DEL'] = 0;
            $datab['REDUNDANCY1'] = 2;
            $datab['BALANCE'] = 10;
            M('cq_user_finance_record')->add($datab);
            $datac['USER_ID'] = $lc_user_id;
            $datac['CASH_AMOUNT'] = 10;
            $datac['ADD_TIME'] = $n_time;
            $datac['UP_TIME'] = $n_time;
            $datac['IS_DEL'] = 0;
            M('cq_user_finance')->add($datac);
            // 添加账户信息变动通知
            $datad = array();
            $datad['MES_CONTENT'] = "恭喜您成功注册赚乐扒，赠送10.00元红包，请到我的账户确认。";
            $datad['MES_TYPE'] = 0;
            $datad['MES_TIME'] = $n_time;
            $datad['USER_ID'] = $lc_user_id;
            $datad['LOOK_TYPE'] = 0;
            $datad['ADD_TIME'] = $n_time;
            $datad['IS_DEL'] = 0;
            M('lc_message')->add($datad);
        }
        $where = array();
        $where['MOBILE'] = $fast_mobile;
        
        $data['INVITATIONFRIENDS_TYPE'] = 2;
        $data['INVITATION_CODE'] = $fast_code;
        $data['ADD_TIME'] = $time;
        $data['IS_DEL'] = 0;
        $data['USER_ID'] = $lc_user_id;
        $data['ADD_USER'] = $lc_user_id;
        
        $model = M('cq_invitation_friends');
        $result = $model->add($data);
        if ($result) {
            $this->ajaxReturn(1, 'JSON');
        } else {
            $this->ajaxReturn("注册失败", 'JSON');
        }
    }

    public function cmVn()
    {
        $shareId = $_REQUEST['share_id'];
        if ($shareId) {
            redirect('http://' . $_SERVER['HTTP_HOST'] . '/index.php/share/' . $shareId, 0, '页面跳转中...');
        }
    }
}