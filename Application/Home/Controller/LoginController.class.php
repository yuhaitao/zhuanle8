<?php
namespace Home\Controller;

use Think\Controller;

class LoginController extends BaseController
{

    public function index()
    {
        $this->display();
    }

    public function login()
    {
        $site_title = "用户登录-赚乐扒"; // 网站title
        $this->assign('site_title', $site_title);
        $this->display();
    }

    public function check_login()
    {
        $login_name = $_POST['loginuser'];
        $login_pwd = $_POST['loginpwd'];
        $md5_pwd = strtoupper(md5($login_pwd)); // 转换成大写
        $where = array();
        $where['MOBILE'] = $login_name;
        // $where['PASSWORD'] = $md5_pwd;
        $where['IS_DEL'] = 0;
        
        $model = M('lc_user');
        $result = $model->field("USER_ID,PASSWORD,NICK_NAME,USER_PHOTO,MOBILE")
            ->where($where)
            ->find();
        // $this->ajaxReturn($model->getLastSql(),'JSON');
        if ($result) {
            if ($result['password'] == $md5_pwd) {
                $_SESSION['user_info'] = $result;
                
                $this->ajaxReturn(0, 'JSON');
            } else {
                $this->ajaxReturn("密码不正确", 'JSON');
            }
        } else {
            $this->ajaxReturn("用户名不存在", 'JSON');
        }
    }
    
    // 忘记密码
    public function passwords()
    {
        $this->display();
    }

    /* 退出登录 */
    public function logout()
    {
        session("user_info", null);
        redirect('login', 0);
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
        $content = '验证码：' . $randcode . '， 此验证码为赚乐扒找回密码使用。  [赚乐扒]';
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
            $this->ajaxReturn('0', 'JSON');
            // echo '发送失败返回值为:'.$line.'。请查看webservice返回值对照表';
        } else {
            $data['SMS_CONTENT'] = $content;
            $data['MOBILE'] = $mobile;
            $data['SMS_DATE'] = date('Y-m-d H:i:s', time());
            $data['SMS_TYPE'] = '005';
            $m = M('lc_sms')->add($data);
            session('msg_code', $randcode);
            $this->ajaxReturn('1', 'JSON');
            // echo '发送成功 返回值为:'.$line;
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
        
        $randcode = mt_rand(10, 99) . mt_rand(10, 99) . mt_rand(10, 99);
        $text = "【赚乐扒】验证码:" . $randcode . "。此验证码为赚乐扒找回密码使用，如非本人操作可忽略。有效期为1分钟，请尽快验证。";
        
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
            $data['SMS_TYPE'] = '005';
            $m = M('lc_sms')->add($data);
            session('msg_code', $randcode);
            $this->ajaxReturn('1', 'JSON');
        }
    }
    
    // 保存修改的密码
    public function changePassword()
    {
        $mobile = $_POST['mobile'];
        $verify = $_POST['imgCode'];
        $code = $_POST['mobileCode'];
        $password = $_POST['password'];
        $repassword = $_POST['repassword'];
        /* 检测图片验证码 */
        if (! check_verify($verify)) {
            $this->ajaxReturn('图形验证码输入错误！');
        }
        /* 检测手机验证码 */
        // 检测最近发送验证码的时间
        $where['MOBILE'] = $mobile;
        $where['SMS_TYPE'] = '005';
        $result = M('lc_sms')->field('SMS_DATE')
            ->where($where)
            ->order('SMS_ID desc')
            ->limit(1)
            ->find();
        $time_long = 65;
        if ($result['sms_date'] > 0) {
            $time_long = time() - strtotime($result['sms_date']);
        }
        // 限制1分钟可发送一次
        if ($time_long > 60) {
            $this->ajaxReturn('手机验证码已过期');
        }
        
        if ($code != session('msg_code')) {
            $this->ajaxReturn('手机验证码输入错误！');
        }
        /* 检测密码 */
        if (strlen($password) < 8 || strlen($password) > 23) {
            $this->ajaxReturn('密码格式不正确');
        }
        $pwd = strtoupper(md5($password));
        $data['PASSWORD'] = $pwd;
        $data['UP_TIME'] = date("Y-m-d H:i:s", time());
        $m = M('lc_user')->where("MOBILE = $mobile and IS_DEL=0")->save($data);
        if ($m) {
            $this->ajaxReturn('1', 'JSON');
        } else {
            $this->ajaxReturn('找回密码失败，请稍后重试', 'JSON');
        }
    }
    
    // gotoTradePWD 找回交易密码
    public function gotoTradePWD()
    {
        $this->display();
    }
    // 保存 新的交易密码
    public function saveTrade()
    {
        $mobile = $_POST['mobile'];
        $verify = $_POST['imgCode'];
        $code = $_POST['mobileCode'];
        $password = $_POST['password'];
        $repassword = $_POST['repassword'];
        /* 检测图片验证码 */
        if (! check_verify($verify)) {
            $this->ajaxReturn('图形验证码输入错误！');
        }
        /* 检测手机验证码 */
        // 检测最近发送验证码的时间
        $where['MOBILE'] = $mobile;
        $where['SMS_TYPE'] = '005';
        $result = M('lc_sms')->field('SMS_DATE')
            ->where($where)
            ->order('SMS_ID desc')
            ->limit(1)
            ->find();
        $time_long = 65;
        if ($result['sms_date'] > 0) {
            $time_long = time() - strtotime($result['sms_date']);
        }
        // 限制1分钟可发送一次
        if ($time_long > 60) {
            $this->ajaxReturn('手机验证码已过期');
        }
        
        if ($code != session('msg_code')) {
            $this->ajaxReturn('手机验证码输入错误！');
        }
        /* 检测密码 */
        if (strlen($password) < 8 || strlen($password) > 23) {
            $this->ajaxReturn('密码格式不正确');
        }
        $pwd = strtoupper(md5($password));
        $data['TRADE_PASSWORD'] = $pwd;
        $data['UP_TIME'] = date("Y-m-d H:i:s", time());
        $m = M('lc_user')->where("MOBILE = $mobile and IS_DEL=0")->save($data);
        if ($m) {
            $this->ajaxReturn('1', 'JSON');
        } else {
            $this->ajaxReturn('找回密码失败，请稍后重试', 'JSON');
        }
    }
}