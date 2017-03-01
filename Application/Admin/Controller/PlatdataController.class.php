<?php
namespace Admin\Controller;
use Think\Controller;
class PlatdataController extends BaseController {
    public function index(){
    	
    }
    public function platdata(){
    	/**/
    	$this->display();
    }
    //平台数据
    public function platlist(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 1;  
        $m = M("cq_plat");//平台
        $where = array();
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

        $list=$m->field("PLAT_ID,PLAT_SHORTNAME")->where($where)->limit($start,$limit)->select();
        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数
        foreach ($list as $key => $value) {
            $i=$key;
            //@@@id
            $responce->rows[$i]['id'] = $value['plat_id'];
            //平台id
            $plat_id=$value['plat_id'];
            //@@@平台名
            $responce->rows[$i]['plat_name'] = $value['plat_shortname'];
            /*操作产品表*/
            $m_product = M("cq_product");
            $condition = array();//查询条件添加
            $condition["PLAT_SHORTNAME"] = $plat_id;
            $product_arr=$m_product->field("PRODUCT_ID")->where($condition)->select();
            $pro_arr=array();
            foreach ($product_arr as $key => $value) {
                array_push($pro_arr, $value['product_id']);
            }
            /*产品表结束---查询产品购买表*/
            $cond = array();
            //条件：所有购买这些产品的记录
            $cond['PRODUCT_ID'] = array('in',implode(',', $pro_arr));
            $cond['HANDLE_STATUS'] = 1;//已处理的
            $m_p_buy=M("cq_product_buy");
            $p_buy=$m_p_buy->field("USER_ID,BUY_MONEY,SERIAL_NO")->where($cond)->select();
            $u_arr=$s_arr=array();
            $buy_money=0;
            foreach ($p_buy as $key => $value) {
                $u_id=$value['user_id'];
                $buy_money+=$value['buy_money'];
                $s_no=$value['serial_no'];
                if(!in_array($u_id, $u_arr)){//统计投资人数
                    array_push($u_arr, $u_id);
                }
                array_push($s_arr, $s_no);
            }
            //投资人数
            $responce->rows[$i]['user_buy_count']="<a href='javascript:;' onclick= top.platUserCount('$plat_id')>".count($u_arr)."</a>";

            //投资金额
            $responce->rows[$i]['buy_money']=$buy_money;

            /*产品购买表结束-----查询返利*/
            $m_finance=M("cq_user_finance_record");
            $term = array();
            $term['SERIAL_NO'] = array('in',implode(',', $s_arr));
            /*统计返利金额*/
            $f_money=$m_finance->where($term)->sum('CASH_MONEY');
            $responce->rows[$i]['finance_money']=$f_money*1;
        }
        //返回值
        $this->ajaxReturn($responce,'JSON');
    }
    //投资人数列表
    public function platinvest(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 'BUY_TIME';
        $plat_id = $_POST['plat_id'];
        //查询产品编号 和 名称
        $result = M('cq_product')->field('PRODUCT_ID,TARGET_NAME')->where('PLAT_SHORTNAME = '.$plat_id)->select();
        $platidarr=$platnamearr=array();
        foreach ($result as $key => $value) {
            $platidarr[$key] = $value['product_id'];
            $platnamearr[$value['product_id']] = $value['target_name'];
        }
        $m = M("cq_product_buy");//平台
        $where = array();
        $where['PRODUCT_ID'] = array("in",implode(",", $platidarr));
        $where['HANDLE_STATUS'] = 1;
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
        $list = $m->field('PRODUCT_ID,USER_ID,BUY_MONEY,BUY_TIME,SERIAL_NO')->where($where)->order($sidx." ".$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $lcuser=M('lc_user')->field('USER_NAME,MOBILE')->where('USER_ID = "'.$value['user_id'].'"')->find();
            $finance = M('cq_user_finance_record')->field('CASH_MONEY')->where('TYPE = 2 AND SERIAL_NO = "'.$value['serial_no'].'"')->find();
            $responce->rows[$key]['mobile']=$lcuser['mobile'];
            $responce->rows[$key]['user_name']=$lcuser['user_name'];
            $responce->rows[$key]['target_name']=$platnamearr[$value['product_id']];//标名
            $responce->rows[$key]['buy_money']=$value['buy_money'];
            $responce->rows[$key]['finance_money']=$finance['cash_money'];
            $responce->rows[$key]['buy_time']=$value['buy_time'];
        }
        $this->ajaxReturn($responce,'JSON');
    }
}