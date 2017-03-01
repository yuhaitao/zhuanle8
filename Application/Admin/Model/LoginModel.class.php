<?php
namespace Admin\Model;
 use Think\Model;
 class LoginModel extends Model {
 	
 	  protected $autoCkeckFields = false; //关闭检测字段
 	  protected $tableName='cq_back_user';
    /**
     * 自动验证
     * self::EXISTS_VALIDATE 或者0 存在字段就验证（默认）
     * self::MUST_VALIDATE 或者1 必须验证
     * self::VALUE_VALIDATE或者2 值不为空的时候验证
     */
    protected $_validate = array(
        array('USER_NAME', 'require', '用户名不能为空！'), //默认情况下用正则进行验证
        array('USER_NAME', '/^1[3-8]\d{9}$/', '手机号码格式不正确', 0),
        array('PASSWORD', '/^([a-zA-Z0-9@*#]{6,22})$/', '密码格式不正确,请重新输入！', 0)
    );
    
    protected $_auto = array(
        //array('PASSWORD', 'md5', 3, 'function'), // 对password字段在新增和编辑的时候使md5函数处理
        //array('loginip', 'get_client_ip', 1, 'function') // 对regip字段在新增的时候写入当前注册ip地址
        array('loginip', 'get_client_ip', 1, 'function')
    );
 }