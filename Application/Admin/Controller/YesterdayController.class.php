<?php
namespace Admin\Controller;
use Think\Controller;
class YesterdayController extends BaseController {
    //header('Content-Type: application/json; charset=UTF-8');
    
    public function index(){
    	
    }
    public function showreg(){
        $this->display();
    }
    public function yesterday(){
        
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 1;  
    	/**/
    	$m = M("cq_count");
    	$where = array();
    	//$p=getpage($m,$where,$per_num);
        /*总记录数*/
        $count = $m->where($where)->count();
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

		$list=$m->field("COUNT_ID,REG_COUNT,BUY_USER_COUNT,BUY_MONEY_COUNT,ADD_TIME")->where($where)->order('COUNT_ID desc')->limit($start,$limit)->select();
        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数 
        foreach ($list as $key => $value) {
            $responce->rows[$key]['id'] = $value['count_id'];
            $responce->rows[$key]['add_time'] = date("Y-m-d",strtotime($value['add_time']));
            $reg_id = date("Y-m-d",strtotime($value['add_time']));
            $responce->rows[$key]['reg_count'] = "<a href='javascript:;' onclick = top.regCount('$reg_id')>".$value['reg_count']."</a>";
            $responce->rows[$key]['buy_user_count'] = "<a href='javascript:;' onclick= top.buyUserCount('$reg_id')>".$value['buy_user_count']."</a>";
            $responce->rows[$key]['buy_money_count'] = $value['buy_money_count'];
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //当天注册人数
    public function todayreg(){

        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 1;  
        $start_time = $_POST['add_time'];//当天开始时间和结束时间
        $end_time = date("Y-m-d",strtotime($start_time)+86400);
        $m = M('lc_user');
        $where = array();
        $where['ADD_TIME'] = array('between',array($start_time,$end_time));
        /*总记录数*/
        $count = $m->where($where)->count();
        //$this->ajaxReturn($start_time);exit;
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
        $list=$m->field("USER_ID,USER_NAME,MOBILE,ADD_TIME,USER_REG_IP")->where($where)->order('ADD_TIME desc')->limit($start,$limit)->select();
        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数
        foreach ($list as $key => $value) {
            $responce->rows[$key]['mobile'] = $value['mobile'];
            $responce->rows[$key]['user_name'] = $value['user_name'];
            $responce->rows[$key]['add_time'] = $value['add_time'];
            $result = M('cq_invitation_friends')->field('INVITATION_CODE')->where("USER_ID = ".$value['user_id'])->find();
            if(count($result) > 0){
                $i_code = $result['invitation_code'];//邀请码
                $u_id = M('cq_invitation_code')->field('USER_ID')->where('INVITATION_CODE = "'.$i_code.'"')->find();
                $mobile = M('lc_user')->field('MOBILE')->where('USER_ID = '.$u_id['user_id'])->find();
                $responce->rows[$key]['i_mobile'] = $mobile['mobile']; // 邀请人的手机号
            }else{
                $responce->rows[$key]['i_mobile'] = "";
            }
            
            $responce->rows[$key]['user_reg_ip'] = $value['user_reg_ip'];
        }
        $this->ajaxReturn($responce,'JSON');
    }

    //当天投资人数
    public function todaybuy(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 'UP_TIME';  
        $start_time = I('add_time');//当天开始时间和结束时间
        $end_time = date("Y-m-d",strtotime($start_time)+86400);
        $where = array();
        $where['UP_TIME'] = array('between',array($start_time,$end_time));
        $where['IS_DEL'] = 0;
        $where['HANDLE_STATUS'] = 1;
        $m = M('cq_product_buy');
        /*总记录数*/
        $count = $m->where($where)->count();
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
        $list = $m->field('PRODUCT_ID,USER_ID,BUY_MONEY,BUY_TIME')->where($where)->order($sidx,$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $r1=M('lc_user')->field('USER_NAME,MOBILE')->where('USER_ID = '.$value['user_id'])->find();
            $responce->rows[$key]['mobile'] = $r1['mobile'];//手机号
            $responce->rows[$key]['user_name'] = $r1['user_name'];//用户名
            $r2 = M('cq_product')->field('TARGET_NAME,PLAT_SHORTNAME')->where('PRODUCT_ID = '.$value['product_id'])->find();
            $r3 = M('cq_plat')->field('PLAT_SHORTNAME')->where('PLAT_ID = '.$r2['plat_shortname'])->find();
            $responce->rows[$key]['plat_shortname']=$r3['plat_shortname'];
            $responce->rows[$key]['target_name']=$r2['target_name'];
            $responce->rows[$key]['buy_money']= $value['buy_money'];
            $responce->rows[$key]['buy_time']=$value['buy_time'];
        }
        $this->ajaxReturn($responce,'JSON');
    }
}