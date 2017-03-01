<?php
namespace Admin\Controller;
use Think\Controller;
class InvestRankController extends BaseController {
    public function index(){
    	
    }
    public function investRank(){
        $this->display();
    }
    public function add_invest(){
        $mobile_id = $_REQUEST['invest_id'];
        $this->assign('invest_id',$mobile_id);
        $this->display();
    }
    //编辑投资排行
    public function editRank(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式

        $model = M('cq_invest_rank');
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        $count = $model->where('IS_DEL = 0')->count();  //总记录数
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

        $list = $model->table('cq_invest_mobile a')->field('a.MOBILE,(select sum(INVEST_AMOUNT) from cq_invest_rank b where b.MOBILE = a.MOBILE) as SUM_INVEST_AMOUNT')->where('a.IS_DEL = 0')->order($sidx.' '.$sord)->limit($start,$limit)->select();

        foreach ($list as $key => $value) {
            $responce->rows[$key]['mobile'] = $value['mobile'];  //投资手机号
            $responce->rows[$key]['sum_invest_amount'] = $value['sum_invest_amount'];   //投资金额
            $responce->rows[$key]['add_invest'] = "<a href='javascript:top.editInvestRank($value[mobile]);'>添加金额</a>";
            $responce->rows[$key]['delete_invest'] = "<a href='javascript:top.deleteInvestRank($value[mobile]);'>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }

    //添加金额
    public function addRank(){
        $userId = session('back_user_id');
        $time = date("Y-m-d:H:i:s",time());
        $invest_mobile = $_POST['mobile_id'];
        $invest_money = $_POST['investCount'];
        $data['MOBILE'] = $invest_mobile;
        $data['INVEST_AMOUNT'] = $invest_money;
        $data['IS_DEL'] = 0;
        $data['ADD_USER'] = $userId;
        $data['ADD_TIME'] = $time;
        $model = M('cq_invest_rank');
        $m = $model->add($data);

        if($m){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
        $this->ajaxReturn($invest_money,'JSON');
    }
    //删除金额
    public function deleteRank(){
        $delRank_id = $_POST['delRank_id'];
        $model = M('cq_invest_mobile');
        $data['IS_DEL'] = 1;
        $data['MOBILE'] = $delRank_id;
        $data['UP_USER'] = session('back_user_id');
        $time = date("Y-m-d H:i:s",time());
        $data['UP_TIME'] = $time;
        $m = $model->save($data);

        if($m){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
    //添加投资手机号
    public function addInvestMobile(){
        $investMobile = $_POST['add_investMobile'];

        $data['IS_DEL'] = 0;
        $data['MOBILE'] = $investMobile;
        $data['ADD_USER'] = session('back_user_id');
        $time = date("Y-m-d H:i:s",time());
        $data['ADD_TIME'] = $time;
        $model = M('cq_invest_mobile');
        $m = $model->add($data);
        if($m){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }

    //Top10投资奖励
    public function topInvest(){
        $this->display();
    }
    public function topInvestList(){
        $time = time();
        $cur_time = date('Y',$time).'0'.(date('m',$time)-1);   //时间
        $model = M('cq_invest_payment');
        $where = array();
        $where['YEARMONTH'] = $cur_time;
        $where['IS_DEL'] = 0;

        $list = $model->field('MOBILE,INVEST_AMOUNT,PAYMENT_STATUS,YEARMONTH,INVEST_TYPE')->where($where)->select();
        $state = '';
        $tag = '';
        foreach ($list as $key => $value) {
            $responce->rows[$key]['paiming'] = '第'.($key+1).'名';
            $responce->rows[$key]['mobile']  = $value['mobile'];
            $responce->rows[$key]['invest_amount'] = $value['invest_amount'];
            $responce->rows[$key]['yearmonth'] = $value['yearmonth'];
            $state = $value['payment_status'];
            if($state == 1){
                $state = '已发放';
            }elseif ($state == 2) {
                $state = '未发放';
            }
            $responce->rows[$key]['state'] = $state;

            $tag = $value['invest_type'];
            if($tag == 1){
                $tag = '已发放';
            }elseif ($tag == 2) {
                $tag = '虚假排行,不能操作';
            }
            $responce->rows[$key]['tag'] = $tag;

        } 
        $this->ajaxReturn($responce,'JSON');
    }
    //投资排行记录
    public function investRecordRank(){
        $model = M('cq_invest_payment');
        $list = $model->field('YEARMONTH')->group('YEARMONTH')->where('IS_DEL = 0')->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['rank_id'] = $value['invest_payment_id'];
            $responce->rows[$key]['yearmonth'] = $value['yearmonth'];
        }
        $this->ajaxReturn($responce,'JSON');
    }
    public function invetRecordList(){
        $time_id = $_POST['time_show'];
        $model = M('cq_invest_payment');
        $where = array();
        $where['IS_DEL'] = 0;
        $where['YEARMONTH'] = $time_id;

        $list = $model->field('MOBILE,INVEST_AMOUNT,YEARMONTH,PAYMENT_STATUS,INVEST_TYPE')->where($where)->order('INVEST_AMOUNT desc')->select();

        $state = '';
        $tag = '';
        foreach ($list as $key => $value) {
            $responce->rows[$key]['number'] = '第'.($key+1).'名';
            $responce->rows[$key]['mobile']  = $value['mobile'];
            $responce->rows[$key]['invest_amount'] = $value['invest_amount'];
            $responce->rows[$key]['yearmonth'] = $value['yearmonth'];
            $state = $value['payment_status'];
            if($state == 1){
                $state = '已发放';
            }elseif ($state == 2) {
                $state = '未发放';
            }
            $responce->rows[$key]['state'] = $state;

            $tag = $value['invest_type'];
            if($tag == 1){
                $tag = '已发放';
            }elseif ($tag == 2) {
                $tag = '虚假排行,不能操作';
            }
            $responce->rows[$key]['tag'] = $tag;
        }
        $this->ajaxReturn($responce,'JSON');
    }
}