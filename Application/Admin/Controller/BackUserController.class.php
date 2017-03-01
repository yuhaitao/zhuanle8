<?php
namespace Admin\Controller;
use Think\Controller;
class BackUserController extends BaseController {
    public function index(){
    	
    }
    public function backuser(){
    	$this->display();
    }
    public function backuserlist(){
    	$page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = '1';
        //查询条件 开始
        $username=$_POST['username'];
        $identity=$_POST['identity'];
        $mobile = $_POST['mobile'];
        $where=array();
        if($username){
            $where['BACK_USER_NAME']=array("like","%".$username."%");
        }
        if($identity){
            $where['BACK_USER_IDENTITY']=array("like","%".$identity."%");
        }
        if($mobile){
            $where['BACK_USER_MOBILE']=array("like","%".$mobile."%");
        }
        $where['IS_DEL']=0;
        //查询条件结束
        $m = M('cq_back_user');
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
        $list=$m->where($where)->field('BACK_USER_ID,BACK_USER_NAME,BACK_USER_MOBILE,BACK_USER_IDENTITY,BACK_USER_TYPE')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
        	//$responce->rows[$key]['back_user_id']=$value['back_user_id'];
        	$responce->rows[$key]['back_user_name']=$value['back_user_name'];
        	$responce->rows[$key]['back_user_mobile']=$value['back_user_mobile'];
        	$responce->rows[$key]['back_user_identity']=$value['back_user_identity'];
        	$user_type=$value['back_user_type'];
        	if($user_type==1){
        		$user_type="后台一般用户";
        	}elseif($user_type==0){
        		$user_type="后台管理员";
        	}
        	$responce->rows[$key]['back_user_type']=$user_type;
        	$responce->rows[$key]['revise_pwd']="<a href='javascript:top.OpenUpdatePassword(".$value['back_user_id'].");'>修改密码</a>";
        	$responce->rows[$key]['back_user_del']="<a href='javascript:deleteBackUser(".$value['back_user_id'].");'>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //修改密码
    public function revise_user(){
    	$revise_id=$_REQUEST['revise_id'];
       	$this->assign('revise_id',$revise_id);
    	$this->display();
    }
    //保存修改
    public function save_user(){
    	$revise_id=$_POST['revise_id'];
    	$pwd=$_POST['pwd'];
    	$password=md5($pwd);
    	$user_id=session('back_user_id');//修改人
        $time=date("Y-m-d H:i:s",time());//修改时间
    	$data['BACK_USER_PASSWORD']=$password;
    	$data['UP_USER']=$user_id;
    	$data['UP_TIME']=$time;
    	$where['BACK_USER_ID']=$revise_id;
    	$m=M('cq_back_user')->where($where)->save($data);
    	if($m){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }

    }
    //删除用户
    public function delete_user(){
    	$delete_id=$_POST['delete_id'];
        $user_id=session('back_user_id');
        $time=date("Y-m-d H:i:s",time());
        $m=M('cq_back_user');
        $m->IS_DEL = 1;
        $m->UP_USER = $user_id;
        $m->UP_TIME = $time;
        $where=array('BACK_USER_ID' => $delete_id);
        $model = $m ->where($where)->save();
        if($model){
            $this->ajaxReturn('1','JSon');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
    //检测身份证号
    public function checkidentity(){
        $identity=$_POST['identity'];
        $m=M('cq_back_user');
        $where['BACK_USER_IDENTITY']=$identity;
        $where['IS_DEL']=0;
        $list=$m->where($where)->find();
        if(count($list) > 0){
            $this->ajaxReturn('0','JSon');
        }else{
            $this->ajaxReturn('1','JSON');
        }
    }
    //检测手机号是否重复
    public function checkphone(){
        $cur_phone = $_POST['cur_phone'];
        $model = M('cq_back_user');
        $where = array();
        $where['BACK_USER_MOBILE'] = $cur_phone;
        $where['IS_DEL']=0;
        $old_phone = $model->where($where)->find();
        if($old_phone){
            $this->ajaxReturn(0,'JSON');
        }else{
            $this->ajaxReturn(1,'JSON');
        }
    }
    //保存添加
    public function insert_user(){
        $username = $_POST['username'];
        $identity = $_POST['identity'];
        $user_password = $_POST['user_password'];
        $phone = $_POST['phone'];
        $user_type = $_POST['user_type'];
        $data['BACK_USER_TYPE']=$user_type;
        $data['BACK_USER_NAME']=$username;
        $data['BACK_USER_IDENTITY']=$identity;
        $user_password=md5($user_password);
        $data['BACK_USER_PASSWORD']=$user_password;
        $data['BACK_USER_MOBILE']=$phone;
        
        $time=date("Y-m-d H:i:s",time());
        $data['ADD_TIME']=$time;
        $data['UP_TIME']=$time;
        $user_id=session('back_user_id');
        $data['ADD_USER']=$user_id;
        $data['UP_USER']=$user_id;
        
        $model = M('cq_back_user');
        $model_id = $model->add($data);
        if($model_id){
            $this->ajaxReturn($model_id,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }

    //--员工分组
    public function staffgroup(){
        $m=M('cq_staff');
        $list=$m->table('cq_staff a')->join('cq_back_user b on a.USER_ID=b.BACK_USER_ID')->where('a.IS_DEL=0')->field('a.USER_ID,a.STAFF_PARENT,a.PID,b.BACK_USER_NAME,b.BACK_USER_MOBILE')->select();
        $staff = json_encode($list);
        $this->assign("staff",$staff);
        $this->display();
    }

    public function addstafflist(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = '1';
        $result = M('cq_staff')->field('USER_ID')->where('IS_DEL=0')->select();
        $u_id="";
        foreach ($result as $key => $value) {
            $u_id.=$value['user_id'].",";
        }
        $u_id=substr($u_id, 0,-1);
        $m=M('cq_back_user');
        $where=array();
        $where['BACK_USER_ID']=array('not in',$u_id);
        $where['IS_DEL']=0;
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
        $list=$m->table('cq_back_user')->field('BACK_USER_ID,BACK_USER_NAME,BACK_USER_MOBILE')->where($where)->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['back_user_id']=$value['back_user_id'];
            $responce->rows[$key]['back_user_name']=$value['back_user_name'];
            $responce->rows[$key]['back_user_mobile']=$value['back_user_mobile'];
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //保存添加的分组
    public function saveadd(){
        $pid=$_POST['pid'];
        $idlist=$_POST['idlist'];
        $idlist=substr($idlist,0,-1);
        $idarr=explode(",", $idlist);
        $where['IS_DEL']=0;
        $where['USER_ID']=$pid;
        $list=M('cq_staff')->where($where)->field('STAFF_PARENT')->find();
        $staff_parent=$list['staff_parent'].$pid.",";
        $data['STAFF_PARENT']=$staff_parent;
        $data['PID']=$pid;
        $data['ADD_USER']=session('back_user_id');
        $data['ADD_TIME']=date("Y-m-d H:i:s",time());
        $allData=array();
        for ($i=0; $i < count($idarr); $i++) { 
            $data['USER_ID']=$idarr[$i];
            $allData[]=$data;
        }
        $result=M('cq_staff')->addAll($allData);
        if($result){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
    //删除
    public function delete_staff(){
        $delete_id=$_POST['delete_id'];
        $model=M('cq_staff');
        $model->IS_DEL = 1; 
        $where = array('USER_ID' => $delete_id);
        $model ->where($where)->save();
        if($model){
            $this->ajaxReturn('1','JSon');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
} 