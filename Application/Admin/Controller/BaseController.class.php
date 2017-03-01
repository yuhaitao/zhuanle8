<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller {

	/*
	 * 析构函数
	 */
	function __construct(){
		parent::__construct();
		//用户中心面包屑
		$navigate_admin = navigate_admin();
		$this->assign('navigate_admin',$navigate_admin);
		$this->checkAdminSession();
		if(!session("back_user_id")){
    		redirect(U('Login/login'),0,"正在跳转……");
    	}
    	header("Content-type:text/html;charset=utf-8");
		$admin_name = session('back_user_name');
		$this->assign('admin_name',$admin_name);
	}
	
	//判断超时
	public function checkAdminSession() {
		//设置超时为一小时
		$nowtime = time();
		$s_time = $_SESSION['logintime'];
		if ($s_time != null && ($nowtime - $s_time) > 3600) {
			unset($_SESSION['logintime']);
			unset($_SESSION['back_user_id']);
			redirect(U('Login/login'),0,"正在跳转……");
		} else {
			$_SESSION['logintime'] = $nowtime;
		}
	}
}