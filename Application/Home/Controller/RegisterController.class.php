<?php
namespace Home\Controller;

use Think\Controller;

class RegisterController extends BaseController
{

    public function index()
    {}

    public function register()
    {
        $site_title = "用户注册-赚乐扒"; // 网站title
        $this->assign('site_title', $site_title);
        $this->display();
    }

    /* 验证码，用于登录和注册 */
    public function verify()
    {
        $verify = new \Think\Verify();
        $verify->expire = 300;
        $verify->length = 4;
        $verify->fontSize = 20;
        $verify->imageW = 150;
        $verify->imageH = 40;
        $verify->useCurve = false;
        $verify->useNoise = false;
        $verify->entry(1);
    }

    /* 检测手机号是否可用 */
    public function checkmobile()
    {
        $cur_phone = $_POST['cur_phone'];
        $model = M('lc_user');
        $where = array();
        $where['MOBILE'] = $cur_phone;
        $where['IS_DEL'] = 0;
        $old_phone = $model->where($where)->find();
        if ($old_phone) {
            $this->ajaxReturn(0, 'JSON');
        } else {
            $this->ajaxReturn(1, 'JSON');
        }
    }
    
    // 邀请码校验
    public function checkshare()
    {
        $shareId = $_REQUEST['share_id'];
        
        $model = M('cq_invitation_code');
        $where = array();
        $where['SHOUT_URL_MARK'] = $shareId;
        $result = $model->field('SHOUT_URL_MARK')
            ->where($where)
            ->find();
        
        if ($result['shout_url_mark'] == $shareId) {
            $this->ajaxReturn(1, 'JSON');
        } else {
            $this->ajaxReturn(0, 'JSON');
        }
    }
    /* 发送短信 new */
    public function sendcode_bak()
    {
        $mobile = $_POST['mobile'];
        if ($mobile == "") {
            $this->ajaxReturn('手机号不可为空', 'JSON');
        }
        // 检测最近发送验证码的时间
        $where['MOBILE'] = $mobile;
        $where['SMS_TYPE'] = '001';
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
        
        $randcode = mt_rand(10, 99) . mt_rand(10, 99) . mt_rand(10, 99);
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
        
        $text = "【赚乐扒】验证码:" . $randcode . "。此验证码为赚乐扒注册使用，如非本人操作可忽略。有效期为1分钟，请尽快验证。";
        
        $data = array(
            'text' => $text,
            'apikey' => $apikey,
            'mobile' => $mobile
        );
        
        curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/single_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $json_data = curl_exec($ch);
        if ($json_data != 0) {
            $this->ajaxReturn('0', 'JSON');
        } else {
            $data['SMS_CONTENT'] = $text;
            $data['MOBILE'] = $mobile;
            $data['SMS_DATE'] = date('Y-m-d H:i:s', time());
            $data['SMS_TYPE'] = '001';
            $m = M('lc_sms')->add($data);
            session('msg_code', $randcode);
            $this->ajaxReturn('1', 'JSON');
        }
    }

    /* 发送短信 */
    public function sendcode()
    {
        $mobile = $_POST['mobile'];
        if ($mobile == "") {
            $this->ajaxReturn('手机号不可为空', 'JSON');
        }
        // 检测最近发送验证码的时间
        $where['MOBILE'] = $mobile;
        $where['SMS_TYPE'] = '001';
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
        $randcode = mt_rand(10, 99) . mt_rand(10, 99) . mt_rand(10, 99);
        $content = '验证码：' . $randcode . '， 此验证码为赚乐扒注册使用，如非本人操作可忽略。  [赚乐扒]';
        $flag = 0;
        $list = M('lc_website')->field('WEBSITE_SMSSN,WEBSITE_SMSPWD')->find();
        $sms_sn = $list['website_smssn'];
        $sms_pwd = $list['website_smspwd'];
        
        // 要post的数据
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
            $this->ajaxReturn('0', 'JSON');
            // echo '发送失败返回值为:'.$line.'。请查看webservice返回值对照表';
        } else {
            $data['SMS_CONTENT'] = $content;
            $data['MOBILE'] = $mobile;
            $data['SMS_DATE'] = date('Y-m-d H:i:s', time());
            $data['SMS_TYPE'] = '001';
            $m = M('lc_sms')->add($data);
            session('msg_code', $randcode);
            $this->ajaxReturn('1', 'JSON');
            // echo '发送成功 返回值为:'.$line;
        }
    }

    /* 验证图行码 */
    public function check_code()
    {
        $verify = $_POST['verify_code'];
        /* 检测图片验证码 */
        // var_dump(check_verify($verify));
        // exit;
        if (! check_verify($verify)) {
            $this->ajaxReturn(1, 'JSON');
        } else {
            $this->ajaxReturn(0, 'JSON');
        }
    }

    /* 注册保存 */
    public function save_register()
    {
        $mobile = $_POST['mobile'];
        $verify = $_POST['verify'];
        $code = $_POST['code'];
        $password = $_POST['password'];
        $repassword = $_POST['repassword'];
        /* 检测图片验证码 */
        if (! check_verify($verify)) {
            $this->ajaxReturn('图形验证码输入错误！');
        }
        /* 检测手机验证码 */
        // 检测最近发送验证码的时间
        $where['MOBILE'] = $mobile;
        $where['SMS_TYPE'] = '001';
        $result = M('lc_sms')->field('SMS_DATE')
            ->where($where)
            ->order('SMS_ID desc')
            ->limit(1)
            ->find();
        $time_long = 65;
        if ($result['sms_date'] > 0) {
            $time_long = time() - strtotime($result['sms_date']);
        }
        // 限制两分钟可发送一次
        if ($time_long > 60) {
            $this->ajaxReturn('手机验证码已过期');
        }
        
        if ($code != session('msg_code')) {
            $this->ajaxReturn('手机验证码输入错误！');
        }
        // 检测该号码是否已经完成注册
        $params = array();
        $params['MOBILE'] = $mobile;
        $params['IS_DEL'] = 0;
        $final = M('lc_user')->field('USER_ID')
            ->where($params)
            ->select();
        if ($final) {
            $this->ajaxReturn('该号码已完成注册');
        }
        /* 检测密码 */
        if (strlen($password) < 8 || strlen($password) > 23) {
            $this->ajaxReturn('密码格式不正确');
        }
        
        $ip = get_client_ip();
        $data['MOBILE'] = $mobile;
        $pwd = strtoupper(md5($password));
        $data['PASSWORD'] = $pwd;
        $data['USER_TYPE'] = 1;
        $data['ADD_TIME'] = date("Y-m-d H:i:s", time());
        $data['USER_REG_IP'] = $ip;
        $lc_user_id = M('lc_user')->add($data);
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
            
            // 取邀请码
            $shareId = $_POST['share_id'];
            
            $model = M('cq_invitation_code');
            $where = array();
            $where['SHOUT_URL_MARK'] = $shareId;
            
            $resultIcode = $model->field('INVITATION_CODE,SHOUT_URL_MARK')
                ->where($where)
                ->find();
            
            if ($resultIcode['shout_url_mark'] == $shareId) {
                $data['INVITATION_CODE'] = $resultIcode['invitation_code'];
                $where = array();
                $where['MOBILE'] = $mobile;
                
                $data['INVITATIONFRIENDS_TYPE'] = 2;
                
                $data['ADD_TIME'] = date("Y-m-d H:i:s", time());
                $data['IS_DEL'] = 0;
                $data['USER_ID'] = $lc_user_id;
                $data['ADD_USER'] = $lc_user_id;
                
                $model = M('cq_invitation_friends');
                $model->add($data);
            }
            
            $this->ajaxReturn('1', 'JSON');
        } else {
            $this->ajaxReturn('注册失败');
        }
    }
    // 同意注册协议
    public function agreement()
    {
        $this->display();
    }
    // 注册成功
    public function regSuc()
    {
        $site_title = "注册成功-赚乐扒"; // 网站title
        $this->assign('site_title', $site_title);
        $this->display();
    }
    // 扫描添加微信
    public function attention()
    {
        $this->display();
    }
}