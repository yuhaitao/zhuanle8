<?php 
/*
* 公共函数
*/

function check_verify($code,$id = 1){
    $config = array(
    'reset' => false // 验证成功后是否重置，这里才是有效的。
    );
    $verify = new \Think\Verify($config);
	return $verify->check($code, $id);
}

/*
 * 面包屑导航 用于后台管理
 * 根据当前控制器名称和action方法
 */
function navigate_admin(){
    $navigate = include APP_PATH.'Common/Conf/navigate.php';
    $location = strtolower('Admin/'.CONTROLLER_NAME);

    $arr = array(
        '首页' => 'javascript:;',
        $navigate[$location]['name'] => 'javascript:;',
        $navigate[$location]['action'][ACTION_NAME]=> 'javascript:;',
        );
     //dump($arr);exit;
    return $arr;
}

/**
 *   实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length) {
      if(mb_strlen($string,'utf-8')>$length){
          $str = mb_substr($string, $start, $length,'utf-8');
          return $str.'...';
      }else{
          return $string;
      }
}

/**
 * TODO 基础分页的相同代码封装，使前台的代码更少
 * @param $count 要分页的总记录数
 * @param int $pagesize 每页查询条数
 * @return \Think\Page
 */
function getpage($count, $pagesize = 10) {
    $p = new Think\Page($count, $pagesize);
    $p->setConfig('prev', '上一页');
    $p->setConfig('next', '下一页');
    $p->setConfig('last', '末页');
    $p->setConfig('first', '首页');
    $p->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
    $p->lastSuffix = false;//最后一页不显示为总页数
    return $p;
}

/*
 * 金额转换
 */
function getMoneyFormt($money)
{
    if ($money >= 100000 && $money <= 100000000) {
        $res = getFloatValue(($money / 10000), 2) . "万";
    } else if ($money >= 100000000) {
        $res = getFloatValue(($money / 100000000), 2) . "亿";
    } else {
        $res = getFloatValue($money, 2);
    }
    return $res;
}
/*
 * 字符串替换
 */
function replaceStr($str){
    $s = preg_replace("/(\d{3})\d{5}/","$1****",$str);
    return $s;
}

/*
 * 判断是否登录状态
 */
function isLogin(){
    $user_info=session("user_info");
    $user=$user_info['user_id'];
    if($user == '' || $user == null){
        return false;
    }else{
        return true;
    }
}
