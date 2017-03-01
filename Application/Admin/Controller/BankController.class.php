<?php
namespace Admin\Controller;
use Think\Controller;
class BankController extends BaseController {
    public function index(){
    	
    }
    public function bank(){
    	$this->display();
    }
    //用户绑卡列表
    public function banklist(){
    	$page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if(!$limit) $limit=20;   
        if (!$sidx)    $sidx = 'a.USER_ID';
        if($sidx=='user_id') $sidx = 'a.USER_ID';
        if(!$sord) $sord = 'desc';//默认倒序
        $m=M();
        /*总记录数*/
        $num = $m->query("select  count(USER_ID) as num  from lc_user a where exists (select c.BANK_ID from cq_bank c where a.USER_ID = c.USER_ID)");
        $count=$num[0]['num'];
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
        $result=$m->query("select  a.USER_ID,a.MOBILE,a.NICK_NAME,(select count(b.BANK_ID) from cq_bank b where a.USER_ID=b.USER_ID and b.IS_DEL=0) as bankcount  from lc_user a where exists (select c.BANK_ID from cq_bank c where a.USER_ID = c.USER_ID) order by $sidx $sord limit $start,$limit ");
        foreach ($result as $key => $value) {
        	$responce->rows[$key]['user_id']=$value['user_id'];
        	$responce->rows[$key]['mobile']=$value['mobile'];
        	$responce->rows[$key]['nick_name']=$value['nick_name'];
        	$responce->rows[$key]['bankcount']=$value['bankcount'];
        	$responce->rows[$key]['checkit']="<a href='javascript:top.checkit($value[user_id]);'>查看</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //用户绑卡列表
    public function userbank(){
    	$page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if(!$limit) $limit=20;   
        if (!$sidx)    $sidx = 'a.USER_ID';
        if($sidx=='user_id') $sidx = 'a.USER_ID';
        if(!$sord) $sord = 'desc';//默认倒序
        $user_id=$_POST['user_id'];//用户id
        $m = M('cq_bank');
        $where=$cond=array();
        $where['USER_ID']=$user_id;
        $cond['a.USER_ID']=$user_id;
        $cond['b.PARENT_ID']=19;
                /*总记录数*/
        $count = $m->where($where)->count();
        //查询条件结束
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
        $list=$m->table('cq_bank a')->join('lc_dictionary_small b on a.BANK_TYPE = b.DICSMALL_NO')->where($cond)->field('a.BANK_ID,b.DICSMALL_NAME,a.BANK_NUMBER,a.BANK_ADDRESS,a.REMARKS,a.IS_DEL,a.ADD_TIME')->order($sidx." ".$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            //$responce->rows[$key]['bank_id']=$value['bank_id'];
        	$responce->rows[$key]['dicsmall_name']=$value['dicsmall_name'];
        	$responce->rows[$key]['bank_number']=$value['bank_number'];
        	$responce->rows[$key]['bank_address']=$value['bank_address'];
        	$responce->rows[$key]['remarks']=$value['remarks'];
        	$responce->rows[$key]['add_time']=$value['add_time'];
        	$del=$value['is_del'];
        	if($del==0){
        		$responce->rows[$key]['del']='使用中';
        		$responce->rows[$key]['options']='<a href="javascript:top.updateBank('.$value[bank_id].')">修改</a>  <a href="javascript:top.deleteBank('.$value[bank_id].')">删除</a>';
        	}elseif ($del==1) {
        		$responce->rows[$key]['del']='已解除';
        		$responce->rows[$key]['options']='';
        	}
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //删除银行卡
    public function delete_bank(){
        $bank_id=$_POST['delete_id'];
        $user_id=session('back_user_id');
        $time=date("Y-m-d H:i:s",time());
        $m=M('cq_bank');
        $m->IS_DEL = 1;
        $m->UP_USER = $user_id;
        $m->UP_TIME = $time;
        $where=array('BANK_ID' => $bank_id);
        $model = $m ->where($where)->save();
        if($model){
            $this->ajaxReturn('1','JSon');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    } 
    //修改银行卡
    public function revise_bank(){
        $bank_id=$_REQUEST['revise_id'];
        $this->assign("bank_id",$bank_id);
        $where=array('BANK_ID' => $bank_id);
        $where['IS_DEL']=0;
        $bankMsg=M('cq_bank')->field('BANK_NUMBER,BANK_TYPE')->where($where)->find();
        $typeList=M('lc_dictionary_small')->field('DICSMALL_NO,DICSMALL_NAME')->where('PARENT_ID = 19 and IS_DEL=0')->select();
        $options="";
        foreach ($typeList as $key => $value) {
            if($value['dicsmall_no']==$bankMsg['bank_type']){
                $options.="<option value='$value[dicsmall_no]' selected>".$value['dicsmall_name']."</option>";
            }else{
                $options.="<option value='".$value['dicsmall_no']."'>".$value['dicsmall_name']."</option>";
            }
        }
        $this->assign("options",$options);
        $this->assign("bank_number",$bankMsg['bank_number']);
        $this->display();
    }
    //保存修改结果
    public function save_update(){
        $bank_id=$_POST['revise_id'];//主键 id
        $bank_type=$_POST['bank_type'];//银行类型
        $bank_num=$_POST['bank_num'];//银行卡号
        $user_id=session('back_user_id');//修改人
        $time=date("Y-m-d H:i:s",time());//修改时间
        $data['BANK_NUMBER']=$bank_num;
        $data['BANK_TYPE']=$bank_type;
        $data['UP_USER']=$user_id;
        $data['UP_TIME']=$time;
        $data['BANK_ADDRESS']=$bank_address;
        $data['BANK_PROVINCE']=$bank_province;
        $data['BANK_CITY']=$bank_city;
        $where = array('BANK_ID' => $bank_id);
        $model=M('cq_bank');
        $model->where($where)->save($data);
        if($model){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
}
