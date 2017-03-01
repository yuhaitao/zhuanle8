<?php
namespace Admin\Controller;
use Think\Controller;
class InvestDeclareController extends BaseController {
    public function index(){
    	
    }
    //已提交
    public function investdeclare(){
    	$this->display();
    }
    //审核中 处理中
    public function declaring(){
        $this->display();
    }
    //审核通过
    public function declarsuccess(){
        $this->display();
    }
    //审核失败
    public function declarfail(){
        $this->display();
    }
    //申报列表
    public function investdeclare_list(){
    	$page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        //传值接收
        $check_type=$_POST['check_type'];
        //查询条件
        $where = array();
        $where['a.CHECK_TYPE']= $check_type;
        $where['a.IS_DEL']=0;
        $m=M('cq_invest_repair');
        /*总记录数*/
        $count = $m->table('cq_invest_repair a')->join('cq_plat b on a.PLAT_ID=b.PLAT_ID')->where($where)->count();
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
        $list = $m->table('cq_invest_repair a')->join('cq_plat b on a.PLAT_ID=b.PLAT_ID')->where($where)->field('a.INVEST_REPAIR_ID,b.PLAT_SHORTNAME,a.USER_NAME,a.MOBILE,a.TARGET_NAME,a.INVEST_AMOUNT,a.INVEST_TIME,a.ADD_TIME')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
        	$responce->rows[$key]['plat_shortname']=$value['plat_shortname'];
            $responce->rows[$key]['user_name']=$value['user_name'];
            $responce->rows[$key]['mobile']=$value['mobile'];
            $responce->rows[$key]['target_name']=$value['target_name'];
            $responce->rows[$key]['invest_amount']=$value['invest_amount'];
            $responce->rows[$key]['invest_time']=$value['invest_time'];
            $responce->rows[$key]['add_time']=$value['add_time'];
            $handle="";
            if($check_type==1){
                $handle="<a href='javascript:parent.submitDeclare(".$value['invest_repair_id'].");'>提交处理</a>";
            }elseif ($check_type==2) {
                $handle="<a href='javascript:parent.reviewDeclare(".$value['invest_repair_id'].");'>审核</a>";
            }else{
                $handle="<a href='javascript:parent.checkInvest(".$value['invest_repair_id'].");'>查看</a>";
            }
            $responce->rows[$key]['handle']=$handle;
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //提交审核
    public function updateInvest(){
        $invest_id = $_POST['invest_id'];
        $user_id=session('back_user_id');//添加人
        $time=date("Y-m-d H:i:s",time());//添加时间
        $where['INVEST_REPAIR_ID']=$invest_id;
        $data['CHECK_TYPE']=2;
        $data['UP_USER']=$user_id;
        $data['UP_TIME']=$time;
        $m = M('cq_invest_repair')->where($where)->save($data);
        if($m){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
    //审核
    public function declare_invest(){
        $invest_id = $_REQUEST['invest_id'];
        $where['INVEST_REPAIR_ID']= $invest_id;
        $m=M();
        $list = $m->table('cq_invest_repair a')->join('cq_plat b on a.PLAT_ID=b.PLAT_ID')->where($where)->field('a.INVEST_REPAIR_ID,b.PLAT_SHORTNAME,a.USER_NAME,a.MOBILE,a.TARGET_NAME,a.INVEST_AMOUNT,a.INVEST_TIME')->find();
        $this->assign("list",$list);
        $this->display();
    }
    //保存审核
    public function save_declare(){
        $invest_id = $_POST['invest_id'];
        $check_type = $_POST['check_type'];
        $check_remark = $_POST['check_remark'];
        $user_id=session('back_user_id');//添加人
        $time=date("Y-m-d H:i:s",time());//添加时间
        $where['INVEST_REPAIR_ID']=$invest_id;
        $data['CHECK_TYPE']=$check_type;
        $data['CHECK_REMARK']=$check_remark;
        $data['CHECK_USER']=$user_id;
        $data['CHECK_TIME']=$time;
        $m = M('cq_invest_repair')->where($where)->save($data);
        if($m){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
    //查看
    public function check_invest(){
        $invest_id = $_REQUEST['invest_id'];
        $where['a.INVEST_REPAIR_ID']= $invest_id;
        $where['a.IS_DEL'] = 0;
        $m=M();
        $list = $m->table('cq_invest_repair a')->join('cq_plat b on a.PLAT_ID=b.PLAT_ID')->where($where)->field('a.INVEST_REPAIR_ID,b.PLAT_SHORTNAME,a.USER_NAME,a.MOBILE,a.TARGET_NAME,a.INVEST_AMOUNT,a.INVEST_TIME,a.CHECK_TYPE,a.CHECK_REMARK')->find();
        $this->assign("list",$list);
        $this->display();
    }
}