<?php
namespace Admin\Controller;
use Think\Controller;
class SystemController extends BaseController {
    public function index(){
    	
    }
    //角色列表
    public function role()
    {
    	$this->display();
    }
    //角色列表
    public function rolelist()
    {
    	$page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 1;
        //if(!$sord) $sord = 'desc';//默认倒序
        //查询条件
        $where = array();
        $where['a.IS_DEL'] = 0;
        $role_name=$_POST['role_name'];
        if($role_name){
        	$where['a.ROLE_NAME']=$role_name;
        }
        $m=M('lc_role');
        /*总记录数*/
        $count = $m->table('lc_role a')->join('left join cq_back_user b on a.ADD_USER=b.BACK_USER_ID')->join('left join cq_back_user c on a.UP_USER=c.BACK_USER_ID')->where($where)->count();
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
        $list=$m->table('lc_role a')->join('left join cq_back_user b on a.ADD_USER=b.BACK_USER_ID')->join('left join cq_back_user c on a.UP_USER=c.BACK_USER_ID')->where($where)->field('a.ROLE_ID,a.ROLE_NAME,a.ROLE_MARK,b.BACK_USER_NAME as add_user,a.ADD_TIME,c.BACK_USER_NAME as up_user,a.UP_TIME')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
        	$responce->rows[$key]['role_name']=$value['role_name'];
        	$responce->rows[$key]['role_mark']=$value['role_mark'];
        	$responce->rows[$key]['add_user']=$value['add_user'];
        	$responce->rows[$key]['add_time']=$value['add_time'];
        	$responce->rows[$key]['up_user']=$value['up_user'];
        	$responce->rows[$key]['up_time']=$value['up_time'];
        	$responce->rows[$key]['edit']="<a href='javascript:top.editRole(".$value['role_id'].");'>修改</a>";
        	$responce->rows[$key]['delete']="<a href='javascript:deleteRole(".$value['role_id'].");'>删除</a>";
        	$responce->rows[$key]['add_man']="<a href='javascript:addRoleUser(".$value['role_id'].");'>添加用户</a>";
        	$responce->rows[$key]['edit_power']="<a href='javascript:top.editPower(".$value['role_id'].");'>修改权限</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //删除角色
    public function delete_role()
    {
    	$role_id=$_POST['role_id'];
    	$where['ROLE_ID']=$role_id;
        $data['IS_DEL']=1;
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $m=M('lc_role')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //修改角色
    public function role_edit()
    {
    	$role_id=$_REQUEST['role_id'];
    	$this->assign("role_id",$role_id);
    	$where['ROLE_ID']=$role_id;
    	$role=M('lc_role')->where($where)->field("ROLE_NAME,ROLE_MARK")->find();
    	$this->assign("role",$role);
    	$this->display();
    }
    //判断角色名称是否存在
    public function have_role_name()
    {
        $role_name=$_POST['role_name'];
        $where['ROLE_NAME']=$role_name;
        $where['IS_DEL']=0;
        $result=M('lc_role')->where($where)->find();
        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //保存修改角色名称
    public function save_edit_role()
    {
    	$role_id=$_POST['role_id'];
    	$role_name=$_POST['role_name'];
    	$role_mark=$_POST['role_mark'];
    	$where['ROLE_ID']=$role_id;
    	$data['ROLE_NAME']=$role_name;
    	$data['ROLE_MARK']=$role_mark;
    	$data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
    	$m=M('lc_role')->where($where)->save($data);
    	if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //保存添加的角色名称
    public function save_new_role()
    {
    	$role_name=$_POST['role_name'];
    	$role_mark=$_POST['role_mark'];
    	$data['ROLE_NAME']=$role_name;
    	$data['ROLE_MARK']=$role_mark;
    	$data['ADD_USER']=session("back_user_id");
        $data['ADD_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=0;
        $m=M('lc_role')->add($data);
    	if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //角色列表-- --添加用户
    public function addRoleUser()
    {
        $role_id=$_REQUEST['role_id'];
        $this->assign("role_id",$role_id);
        $this->display();
    }
    //角色列表-- --添加用户-- --未添加用户
    public function noRoleList()
    {
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 1;
        //if(!$sord) $sord = 'desc';//默认倒序
        //查询条件
        $role_id=$_POST['role_id'];
        $countsql="SELECT count(*) as count from cq_back_user WHERE IS_DEL=0 and BACK_USER_TYPE=1 and BACK_USER_ID not in (SELECT user_id from lc_role_user where ROLE_ID=".$role_id." and IS_DEL=0)";
        $m=M('cq_back_user');
        /*总记录数*/
        $num = $m->query($countsql);
        $count = $num[0]['count'];
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
        $sql="SELECT BACK_USER_ID,BACK_USER_NAME,BACK_USER_MOBILE from cq_back_user WHERE IS_DEL=0 and BACK_USER_TYPE=1 and BACK_USER_ID not in (SELECT user_id from lc_role_user where ROLE_ID=".$role_id." and IS_DEL=0) order by $sidx $sord limit $start,$limit";
        $list = $m->query($sql);
        foreach ($list as $key => $value) {
            $responce->rows[$key]['back_user_id']=$value['back_user_id'];
            $responce->rows[$key]['back_user_name']=$value['back_user_name'];
            $responce->rows[$key]['back_user_mobile']=$value['back_user_mobile'];
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //角色列表-- --添加用户-- --已添加用户
    public function hadRoleList()
    {
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 1;
        //if(!$sord) $sord = 'desc';//默认倒序
        //查询条件
        $role_id=$_POST['role_id'];
        $countsql="SELECT count(*) as count from cq_back_user WHERE IS_DEL=0 and BACK_USER_TYPE=1 and BACK_USER_ID in (SELECT user_id from lc_role_user where ROLE_ID=".$role_id." and IS_DEL=0)";
        $m=M('cq_back_user');
        /*总记录数*/
        $num = $m->query($countsql);
        $count = $num[0]['count'];
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
        $sql="SELECT BACK_USER_ID,BACK_USER_NAME,BACK_USER_MOBILE from cq_back_user WHERE IS_DEL=0 and BACK_USER_TYPE=1 and BACK_USER_ID in (SELECT user_id from lc_role_user where ROLE_ID=".$role_id." and IS_DEL=0) order by $sidx $sord limit $start,$limit";
        $list = $m->query($sql);
        foreach ($list as $key => $value) {
            $responce->rows[$key]['back_user_id']=$value['back_user_id'];
            $responce->rows[$key]['back_user_name']=$value['back_user_name'];
            $responce->rows[$key]['back_user_mobile']=$value['back_user_mobile'];
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //角色列表-- --添加用户-- --添加
    public function saveAddUser()
    {
        $idlist = $_POST['idlist'];
        $role_id = $_POST['role_id'];
        $idlist=substr($idlist, 0,-1);
        $idarr=explode(",", $idlist);
        $back_user_id=session("back_user_id");
        $time=date("Y-m-d H:i:s",time());
        $data=array();
        for ($i=0; $i < count($idarr); $i++) { 
            $data[]=array("ROLE_ID"=>$role_id,"USER_ID"=>$idarr[$i],"ADD_USER"=>$back_user_id,"ADD_TIME"=>$time,"IS_DEL"=>0);
        }
        $m=M('lc_role_user')->addAll($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //角色列表-- --添加用户-- --移除
    public function saveRemoveUser()
    {
        $idlist = $_POST['idlist'];
        $role_id = $_POST['role_id'];
        $idlist=substr($idlist, 0,-1);
        $where["ROLE_ID"]=$role_id;
        $where["USER_ID"]=array("in",$idlist);
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=1;
        $m=M('lc_role_user')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //角色列表-- --修改权限
    
    public function editPower()
    {
        $role_id = $_REQUEST['role_id'];
        $this->assign("role_id",$role_id);
        $cond['ROLE_ID']=$role_id;
        $cond['IS_DEL']=0;
        $result=M('lc_role_right')->field('RIGHT_ID')->where($cond)->select();
        $ridArr=array();
        foreach ($result as $key => $value) {
            $ridArr[]=$value['right_id'];
        }
        $where['IS_DEL']=0;
        $m=M('lc_right');
        $list=$m->field('RIGHT_ID,PID,RIGHT_NAME')->where($where)->order('ORDER_NUM asc')->select();
        $result=array();
        foreach ($list as $key => $value) {
            if(in_array($value['right_id'], $ridArr)){
                $result[$key]['checked']=true;
                $result[$key]['open']=true;
            }
            $result[$key]['id']=$value['right_id'];
            $result[$key]['pId']=$value['pid'];
            $result[$key]['name']=$value['right_name'];
        }
        $responce=json_encode($result);
        $this->assign("responce",$responce);
        $this->display();
    }
    //角色列表-- --保存修改后的权限
    public function saveAddPower()
    {
        $role_id=$_POST['role_id'];
        $r_id_list=$_POST['r_id_list'];
        $idarr=explode(",", $r_id_list);
        $where['ROLE_ID']=$role_id;
        M('lc_role_right')->where($where)->delete();
        $back_user_id=session("back_user_id");
        $time=date("Y-m-d H:i:s",time());
        $data=array();
        for ($i=0; $i < count($idarr); $i++) { 
            $data[]=array("ROLE_ID"=>$role_id,"RIGHT_ID"=>$idarr[$i],"ADD_USER"=>$back_user_id,"ADD_TIME"=>$time,"IS_DEL"=>0);
        }
        $m=M('lc_role_right')->addAll($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    /***********************/
    //权限管理----页面元素
    public function right()
    {
        $this->display();
    }
    
    //权限管理----页面元素--列表
    public function rightlist()
    {
        $where['IS_DEL']=0;
        $where['PID']=0;
        $m=M('lc_right');
        $list=$m->field('RIGHT_ID,RIGHT_NAME,RIGHT_PARENT,RIGHT_URL,LEVEL,ORDER_NUM,PID,MARK')->where($where)->order('ORDER_NUM asc')->select();
        $responce="";
        F("treePage",null);
        foreach ($list as $key => $value) {
            $reValue=F("treePage");
            if(!$reValue){
                $reValue=array();
            }
            $right_parent=$value['right_parent'];
            $right_parent=substr($right_parent, 2);
            $right_parent=str_replace(",", "_", $right_parent);
            $right_parent.=$value['right_id'];
            $value['right_parent']=$right_parent;
            $value['count_id']=count($reValue)+1;
            $reValue[]=$value;
            F("treePage",$reValue);
            $this->haschild($value['right_id']);
        }
        $tee=$treePage=F("treePage");
        foreach ($treePage as $key => $value) {
            $right_parent=$value['right_parent'];
            $responce[$key]['id']=$right_parent;
            $responce[$key]['right_id']=$value['right_id'];
            $responce[$key]['right_name']=$value['right_name'];
            $responce[$key]['level']=$value['level']-1;
            $responce[$key]['loaded']=true;
            $responce[$key]['expanded']=true;
            $responce[$key]['parent']=null;
            for ($i=0; $i < count($treePage); $i++) { 
                if ($treePage[$i]['right_id']==$value['pid']) {
                    $responce[$key]['parent']=$treePage[$i]['right_parent'];
                }
            }
            $responce[$key]['isLeaf']=true;
            for ($i=0; $i < count($treePage); $i++) { 
                if ($treePage[$i]['pid']==$value['right_id']) {
                    $responce[$key]['isLeaf']=false;
                }
            }
            $responce[$key]['right_url']=$value['right_url'];
            $responce[$key]['level']=$value['level']-1;
            $responce[$key]['lv']=$value['level'];
            $responce[$key]['order_num']=$value['order_num'];
            if($value['pid']==0){
                $value['pid']="";
            }
            
            $responce[$key]['mark']=$value['mark'];
            
            if ($value['mark']!="looked") {
                $responce[$key]['edit']="<a href='javascript:top.editRight(".$value['right_id'].");'>修改</a>";
                $responce[$key]['delete']="<a href='javascript:deleteRight(".$value['right_id'].");'>删除</a>";
            }
        }
        $this->ajaxReturn($responce,'JSON');
    }
    
    //
    public function haschild($pid)
    {
        $where['IS_DEL']=0;
        $where['PID']=$pid;
        $m=M('lc_right');
        $list=$m->field('RIGHT_ID,RIGHT_NAME,RIGHT_PARENT,RIGHT_URL,LEVEL,ORDER_NUM,PID,MARK')->where($where)->order('ORDER_NUM asc')->select();
        foreach ($list as $key => $value) {
            $reValue=F("treePage");
            $right_parent=$value['right_parent'];
            $right_parent=substr($right_parent, 2);
            $right_parent=str_replace(",", "_", $right_parent);
            $right_parent.=$value['right_id'];
            $value['right_parent']=$right_parent;
            $value['count_id']=count($reValue)+1;
            $reValue[]=$value;
            F("treePage",$reValue);
            $this->haschild($value['right_id']);
        }
    }
    //页面元素--修改
    public function editRight()
    {
        $right_id=$_REQUEST['right_id'];
        $this->assign("right_id",$right_id);
        $where['RIGHT_ID']=$right_id;
        $list=M('lc_right')->field("RIGHT_NAME,RIGHT_URL,MARK,ICON_CLS")->where($where)->find();
        $this->assign("list",$list);
        $this->display();
    }
    //页面元素--修改--保存
    public function save_editRight()
    {
        $right_id=$_POST['right_id'];
        $data['RIGHT_NAME']=$_POST['right_name'];
        $data['RIGHT_URL']=$_POST['right_url'];
        $data['MARK']=$_POST['mark'];
        $data['ICON_CLS']=$_POST['icon_cls'];
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $where['RIGHT_ID']=$right_id;
        $m=M('lc_right')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //页面元素--删除
    public function deleteRight()
    {
        $right_id=$_POST['right_id'];
        $where['RIGHT_ID']=$right_id;
        $data['IS_DEL']=1;
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $m=M('lc_right')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //页面元素------添加
    public function add_right()
    {
        $right_id=$_REQUEST['right_id'];
        $this->assign("pid",$right_id);
        $this->display();
    }
    //页面元素------添加---保存
    public function save_newRight()
    {
        $pid=$_POST['pid'];
        $right_name=$_POST['right_name'];
        $right_url=$_POST['right_url'];
        $mark=$_POST['mark'];
        $icon_cls=$_POST['icon_cls'];
        $where['PID']=$pid;
        $where['IS_DEL']=0;
        $m=M('lc_right');
        $num=$m->where($where)->count();
        $num=$num+1;
        $cond['RIGHT_ID']=$pid;
        $p_data=$m->field("RIGHT_PARENT,LEVEL")->where($cond)->find();
        $data['RIGHT_NAME']=$right_name;
        $data['RIGHT_URL']=$right_url;
        $data['RIGHT_PARENT']=$p_data['right_parent'].$pid.",";
        $data['STATE']=1;
        $data['PID']=$pid;
        $data['ADD_USER']=session("back_user_id");
        $data['ADD_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=0;
        $data['LEVEL']=$p_data['level']+1;
        $data['MARK']=$_POST['mark'];
        $data['ICON_CLS']=$_POST['icon_cls'];
        $data['ORDER_NUM']=$num;
        $n=$m->add($data);
        if($n){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
/*
****发送管理****
*/
//短信
    public function sms()
    {
        $this->display();
    }
    public function smslist(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'SMS_DATE';
        if(!$sord) $sord = 'desc';//默认倒序
        //传值接收
        $mobile=$_POST['mobile'];
        //查询条件
        $where=array();
        if($mobile){
            $where['MOBILE']=$mobile;
        }
        $m=M('lc_sms');
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
        $list=$m->where($where)->field('SMS_CONTENT,SMS_DATE,MOBILE')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['sms_content']=$value['sms_content'];
            $responce->rows[$key]['sms_date']=$value['sms_date'];
            $responce->rows[$key]['mobile']=$value['mobile'];
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //////邮箱
     public function email()
    {
        $this->display();
    }
    public function emaillist(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'EMAIL_DATE';
        if(!$sord) $sord = 'desc';//默认倒序
        //传值接收
        $to_emails=$_POST['to_emails'];
        //查询条件
        $where=array();
        if($to_emails){
            $where['TO_EMAILS']=array("like","%".$to_emails."%");
        }
        $m=M('lc_email');
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
        $list=$m->where($where)->field('SUBJECT,CONTENT,EMAIL_DATE,TO_EMAILS,EMAILTYPE')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['subject']=$value['subject'];
            $responce->rows[$key]['content']=$value['content'];
            $responce->rows[$key]['email_date']=$value['email_date'];
            $responce->rows[$key]['to_emails']=$value['to_emails'];
            $responce->rows[$key]['emailtype']=$value['emailtype'];
        }
        $this->ajaxReturn($responce,'JSON');
    }
    /***
    ****合作渠道
    ***/
    //---合作伙伴
    public function partner()
    {
        $this->display();
    }
    //---合作伙伴---列表
    public function partnerList()
    {
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        //
        $fl_type=$_POST['fl_type'];
        //查询条件
        $where=array();
        $where['a.IS_DEL']=0;
        $where['a.FL_TYPE']=$fl_type;
        $m=M('lc_friendly_link');
        /*总记录数*/
        $count = $m->table('lc_friendly_link a')->join('left join cq_back_user b on a.ADD_USER=b.BACK_USER_ID')->join('left join cq_back_user c on a.UP_USER=c.BACK_USER_ID')->where($where)->count();
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
        $list=$m->table('lc_friendly_link a')->join('left join cq_back_user b on a.ADD_USER=b.BACK_USER_ID')->join('left join cq_back_user c on a.UP_USER=c.BACK_USER_ID')->where($where)->field('a.FRIENDLY_LINK_ID,a.FRIENDLY_LINK,a.FL_NAME,b.BACK_USER_NAME as add_user,a.ADD_TIME,c.BACK_USER_NAME as up_user,a.UP_TIME')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['friendly_link_id']=$value['friendly_link_id'];
            $responce->rows[$key]['friendly_link']=$value['friendly_link'];
            $responce->rows[$key]['fl_name']=$value['fl_name'];
            $responce->rows[$key]['add_user']=$value['add_user'];
            $responce->rows[$key]['add_time']=$value['add_time'];
            $responce->rows[$key]['up_user']=$value['up_user'];
            $responce->rows[$key]['up_time']=$value['up_time'];
            $responce->rows[$key]['edit']="<a href='javascript:editFLink(".$value['friendly_link_id'].");'>修改</a>";
            $responce->rows[$key]['delete']="<a href='javascript:deleteFLink(".$value['friendly_link_id'].");'>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //合作渠道---------刪除
    public function deleteFLink()
    {
        $link_id=$_POST['link_id'];
        $where['FRIENDLY_LINK_ID']=$link_id;
        $data['IS_DEL']=1;
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $m=M('lc_friendly_link')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //合作渠道----添加-----
    public function addFLink()
    {
        $fl_type=$_REQUEST['fl_type'];
        $this->assign("fl_type",$fl_type);
        $this->display();
    }
    //合作渠道----添加-----保存
    public function save_newFLink()
    {
        $data['FL_NAME']=$_POST['fl_name'];
        $data['FL_TYPE']=$_POST['fl_type'];
        $data['FRIENDLY_LINK']=$_POST['friendly_link'];
        $back_user_id=session("back_user_id");
        $data['ADD_USER']=$back_user_id;
        $data['ADD_TIME']=date("Y-m-d H:i:s",time());
        $data['USER_ID']=$back_user_id;
        $data['IS_DEL']=0;
        if(!empty($_FILES['fl_pic']['tmp_name'])){
            $upload = new \Think\Upload();// 实例化上传类    
            $upload->maxSize   =     3145728 ;// 设置附件上传大小 3M  
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->savePath  =      '/image/'.$back_user_id.'/'; // 设置附件上传目录    
            // 上传单个文件     
            $info   =   $upload->uploadOne($_FILES['fl_pic']);    
            if(!$info) {// 上传错误提示错误信息        
                $this->error($upload->getError());    
            }
            $data['FL_PIC']=$info['savepath'].$info['savename'];
        }
        $m=M('lc_friendly_link')->add($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //合作渠道----修改-----
    public function editFLink()
    {
        $fl_type=$_REQUEST['fl_type'];
        $this->assign("fl_type",$fl_type);
        $fl_link=$_REQUEST['fl_link'];
        $this->assign("fl_link",$fl_link);
        $where['FRIENDLY_LINK_ID']=$fl_link;
        $list=M('lc_friendly_link')->field('FL_NAME,FRIENDLY_LINK')->where($where)->find();
        $this->assign("list",$list);
        $this->display();
    }
    //合作渠道----修改--保存
    public function save_editFLink()
    {
        $where['FRIENDLY_LINK_ID']=$_POST['fl_link'];
        $data['FL_NAME']=$_POST['fl_name'];
        $data['FL_TYPE']=$_POST['fl_type'];
        $data['FRIENDLY_LINK']=$_POST['friendly_link'];
        $back_user_id=session("back_user_id");
        $data['UP_USER']=$back_user_id;
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        if(!empty($_FILES['fl_pic']['tmp_name'])){
            $upload = new \Think\Upload();// 实例化上传类    
            $upload->maxSize   =     3145728 ;// 设置附件上传大小 3M  
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->savePath  =      '/image/'.$back_user_id.'/'; // 设置附件上传目录    
            // 上传单个文件     
            $info   =   $upload->uploadOne($_FILES['fl_pic']);    
            if(!$info) {// 上传错误提示错误信息        
                $this->error($upload->getError());    
            }
            $data['FL_PIC']=$info['savepath'].$info['savename'];
        }
        $m=M('lc_friendly_link')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    /*
    *其他设置
    */
    //大类字典
    public function dicBig()
    {
        $this->display();
    }
    //大类字典列表
    public function dicBigList()
    {
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = '1';
        //if(!$sord) $sord = 'desc';//默认倒序
        //传值接收
        $dicbig_id=$_POST['dicbig_id'];
        $dicbig_name=$_POST['dicbig_name'];
        //查询条件
        $where=array();
        $where['IS_DEL']=0;
        if ($dicbig_id) {
            $where['DICBIG_ID']=$dicbig_id;
        }
        if($dicbig_name){
            $where['DICBIG_NAME']=array("like","%".$dicbig_name."%");
        }
        $m=M('lc_dictionary_big');
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
        $list=$m->where($where)->field('DICBIG_ID,DICBIG_NAME')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['dicbig_id']=$value['dicbig_id'];
            $responce->rows[$key]['dicbig_name']=$value['dicbig_name'];
            $responce->rows[$key]['edit']="<a href='javascript:editDicBig(".$value['dicbig_id'].");'>修改</a>";
            $responce->rows[$key]['delete']="<a href='javascript:delDicBig(".$value['dicbig_id'].");'>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //大类字典--添加
    public function save_newDicBig()
    {
        $data['DICBIG_NAME']=$_POST['dicbig_name'];
        $data['ADD_USER']=session("back_user_id");
        $data['ADD_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=0;
        $m=M('lc_dictionary_big')->add($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //大类字典--删除
    public function delDicBig()
    {
        $where['DICBIG_ID']=$_POST['dicbig_id'];
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=1;
        $m=M('lc_dictionary_big')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //大类字典--修改
    public function editDicBig()
    {
        $dicbig_id=$_REQUEST['dicbig_id'];
        $where['DICBIG_ID']=$dicbig_id;
        $list=M('lc_dictionary_big')->where($where)->field("DICBIG_NAME")->find();
        $this->assign("dicbig_id",$dicbig_id);
        $this->assign("dicbig_name",$list['dicbig_name']);
        $this->display();
    }
    //大类字典--修改--保存
    public function save_editDicBig()
    {
        $where['DICBIG_ID']=$_POST['dicbig_id'];
        $data['DICBIG_NAME']=$_POST['dicbig_name'];
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $m=M('lc_dictionary_big')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    /*
    ****************************************************
    */
    //--------主页轮播---------
    public function banner()
    {
        $this->display();
    }
    //---------主页轮播-列表-----------
    public function bannerList()
    {
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        //查询条件
        $where=array();
        $where['a.IS_DEL']=0;
        
        $m=M('lc_banner');
        /*总记录数*/
        $count = $m->table('lc_banner a')->join('left join cq_back_user b on a.ADD_USER=b.BACK_USER_ID')->join('left join cq_back_user c on a.UP_USER=c.BACK_USER_ID')->where($where)->count();
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
        $list=$m->table('lc_banner a')->join('left join cq_back_user b on a.ADD_USER=b.BACK_USER_ID')->join('left join cq_back_user c on a.UP_USER=c.BACK_USER_ID')->where($where)->field('a.BANNER_ID,a.BANNER_JUMP,b.BACK_USER_NAME as add_user,a.ADD_TIME,c.BACK_USER_NAME as up_user,a.UP_TIME')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['banner_jump']=$value['banner_jump'];
            $responce->rows[$key]['add_user']=$value['add_user'];
            $responce->rows[$key]['add_time']=$value['add_time'];
            $responce->rows[$key]['up_user']=$value['up_user'];
            $responce->rows[$key]['up_time']=$value['up_time'];
            $responce->rows[$key]['edit']="<a href='javascript:editBanner(".$value['banner_id'].");'>修改</a>";
            $responce->rows[$key]['delete']="<a href='javascript:delBanner(".$value['banner_id'].");'>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //主页轮播-----------添加 
    public function save_newBanner()
    {
        $data['BANNER_JUMP']=$_POST['banner_jump'];
        $back_user_id=session("back_user_id");
        $data['ADD_USER']=$back_user_id;
        $data['ADD_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=0;
        if(!empty($_FILES['banner_img']['tmp_name'])){
            $upload = new \Think\Upload();// 实例化上传类    
            $upload->maxSize   =     3145728 ;// 设置附件上传大小 3M  
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->savePath  =      '/image/'.$back_user_id.'/'; // 设置附件上传目录    
            // 上传单个文件     
            $info   =   $upload->uploadOne($_FILES['banner_img']);    
            if(!$info) {// 上传错误提示错误信息        
                $this->error($upload->getError());    
            }
            $data['BANNER_IMG']='/upload'.$info['savepath'].$info['savename'];
        }
        $m=M('lc_banner')->add($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //主页轮播-----------删除
    public function delBanner()
    {
        $where['BANNER_ID']=$_POST['banner_id'];
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=1;
        $m=M('lc_banner')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //主页轮播-----------修改
    public function editBanner()
    {
        $banner_id=$_REQUEST['banner_id'];
        $where['BANNER_ID']=$banner_id;
        $list=M('lc_banner')->where($where)->field("BANNER_JUMP")->find();
        $this->assign("banner_id",$banner_id);
        $this->assign("banner_jump",$list['banner_jump']);
        $this->display();
    }
    //主页轮播-----------修改--保存
    public function save_editBanner()
    {
        $banner_id=$_POST['banner_id'];
        $where['BANNER_ID']=$banner_id;
        $data['BANNER_JUMP']=$_POST['banner_jump'];
        $back_user_id=session("back_user_id");
        $data['UP_USER']=$back_user_id;
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        if(!empty($_FILES['banner_img']['tmp_name'])){
            $upload = new \Think\Upload();// 实例化上传类    
            $upload->maxSize   =     3145728 ;// 设置附件上传大小 3M  
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->savePath  =      '/image/'.$back_user_id.'/'; // 设置附件上传目录    
            // 上传单个文件     
            $info   =   $upload->uploadOne($_FILES['banner_img']);    
            if(!$info) {// 上传错误提示错误信息        
                $this->error($upload->getError());    
            }
            $data['BANNER_IMG']='/upload'.$info['savepath'].$info['savename'];
        }
        $m=M('lc_banner')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    /*-----------------短信模板--------------------------*/
    public function template()
    {
        $this->display();
    }
    //短信模板---列表
    public function templateList()
    {
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = '1';
        //if(!$sord) $sord = 'desc';//默认倒序
        $template_type=$_POST['template_type'];
        //查询条件
        $where=array();
        $where['a.IS_DEL']=0;
        $where['b.PARENT_ID']=44;
        if ($template_type) {
            $where['a.TEMPLATE_TYPE']=$template_type;
        }
        $m=M('lc_sms_template');
        /*总记录数*/
        $count = $m->table('lc_sms_template a')->join('left join lc_dictionary_small b on a.TEMPLATE_TYPE=b.DICSMALL_NO')->where($where)->count();
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
        $list=$m->table('lc_sms_template a')->join('left join lc_dictionary_small b on a.TEMPLATE_TYPE=b.DICSMALL_NO')->where($where)->field('a.TEMPLATE_ID,b.DICSMALL_NAME,a.TEMPLATE_DESCRIBE')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['dicsmall_name']=$value['dicsmall_name'];
            $responce->rows[$key]['template_describe']=$value['template_describe'];
            $responce->rows[$key]['edit']="<a href='javascript:editTemplate(".$value['template_id'].");'>修改</a>";
            $responce->rows[$key]['delete']="<a href='javascript:delTemplate(".$value['template_id'].");'>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //短信模板---添加
    public function addTemplate()
    {
        $where['PARENT_ID']=44;
        $where['IS_DEL']=0;
        $small_no=M('lc_sms_template')->where("IS_DEL=0")->field("TEMPLATE_TYPE")->select();
        $no_list="";
        foreach ($small_no as $key => $value) {
            $no_list.=$value['template_type'].",";
        }
        $where['DICSMALL_NO']=array("not in",$no_list);
        $list=M('lc_dictionary_small')->where($where)->field('DICSMALL_NO,DICSMALL_NAME')->select();
        $this->assign("list",$list);
        $this->display();
    }
    //短信模板---添加--保存
    public function save_newTemplate()
    {
        $data['TEMPLATE_TYPE']=$_POST['template_type'];
        $data['TEMPLATE_DESCRIBE']=$_POST['template_describe'];
        $data['ADD_USER']=session("back_user_id");
        $data['ADD_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=0;
        $m=M('lc_sms_template')->add($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //短信模板-----------删除
    public function delTemplate()
    {
        $where['TEMPLATE_ID']=$_POST['template_id'];
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=1;
        $m=M('lc_sms_template')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //短信模板-----------修改
    public function editTemplate()
    {
         $template_id=$_REQUEST['template_id'];
         $where['TEMPLATE_ID']=$template_id;
         $temp=M('lc_sms_template')->where($where)->field('TEMPLATE_DESCRIBE')->find();
         $template_describe=$temp['template_describe'];
         $this->assign("template_describe",$template_describe);
         $this->assign("template_id",$template_id);
         $this->display();
    }
    //
    public function save_editTemplate()
    {
        $where['TEMPLATE_ID']=$_POST['template_id'];
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $data['TEMPLATE_DESCRIBE']=$_POST['template_describe'];
        $m=M('lc_sms_template')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //website 网站设置
    public function website()
    {
        $list=M("lc_website a")->join("left join cq_back_user b on a.UP_USER=b.BACK_USER_ID")->field("a.WEBSITE_ID,a.WEBSITE_NAME,a.WORKING_TIME,a.TELEPHONR,a.WEBSITE_QQ,a.WEBSITE_NUMBER,a.SITE_KEY,a.SITE_DESCRIPTION,a.WEBSITE_DESCRIPTION,b.BACK_USER_NAME,a.UP_TIME,a.WEBSITE_LOGO")->where("a.IS_DEL=0")->select();
        $this->assign("list",$list[0]);
        $this->display();
    }
    public function saveWebsite()
    {
        $where['WEBSITE_ID']=$_POST['website_id'];
        $data['WEBSITE_NAME']=$_POST['website_name'];
        $data['WORKING_TIME']=$_POST['working_time'];
        $data['TELEPHONR']=$_POST['telephonr'];
        $data['WEBSITE_QQ']=$_POST['website_qq'];
        $data['WEBSITE_NUMBER']=$_POST['website_number'];
        $data['SITE_KEY']=$_POST['site_key'];
        $data['SITE_DESCRIPTION']=$_POST['site_description'];
        $data['WEBSITE_DESCRIPTION']=$_POST['website_description'];
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
		
		if(!empty($_FILES['news_img']['tmp_name'])){
            $upload = new \Think\Upload();// 实例化上传类    
            $upload->maxSize   =     3145728 ;// 设置附件上传大小 3M  
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->savePath  =      '/image/'.$userId.'/'; // 设置附件上传目录    
            // 上传单个文件     
            $info   =   $upload->uploadOne($_FILES['news_img']);    
            if(!$info) {// 上传错误提示错误信息        
                $this->error($upload->getError());    
            }
            $data['WEBSITE_LOGO']='/upload'.$info['savepath'].$info['savename'];
        }
		
		
        $m=M('lc_website')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    ///-*-*-*-*-*-*-*-小类字典-*-*-*-*-*-*-*-*-*-*
    public function dicSmall()
    {
        $dicbig_list=M('lc_dictionary_big')->where('IS_DEL=0')->field('DICBIG_ID,DICBIG_NAME')->select();
        $this->assign("dicbig_list",$dicbig_list);
        $this->display();
    }
    //小类字典----列表
    public function dicSmall_list()
    {
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = '1';
        //if(!$sord) $sord = 'desc';//默认倒序
        $choose_id=$_POST['choose_id'];
        $dicsmall_no=$_POST['dicsmall_no'];
        $dicsmall_name=$_POST['dicsmall_name'];
        //查询条件
        $where=array();
        $where['IS_DEL']=0;
        $where['PARENT_ID']=$choose_id;
        if ($dicsmall_no) {
            $where['DICSMALL_NO']=$dicsmall_no;
        }
        if ($dicsmall_name) {
            $where['DICSMALL_NAME']=$dicsmall_name;
        }
        $m=M('lc_dictionary_small');
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
        $list=$m->where($where)->field('DICSMALL_ID,DICSMALL_NO,DICSMALL_NAME')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['dicsmall_no']=$value['dicsmall_no'];
            $responce->rows[$key]['dicsmall_name']=$value['dicsmall_name'];
            $responce->rows[$key]['edit']="<a href='javascript:editDicSmall(".$value['dicsmall_id'].");'>修改</a>";
            $responce->rows[$key]['delete']="<a href='javascript:delDicSmall(".$value['dicsmall_id'].");'>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //小类字典-----添加
    public function addDicSmall()
    {
        $big_id=$_REQUEST['big_id'];
        $this->assign("big_id",$big_id);
        $this->display();
    }
    //小类字典----添加---保存
    public function save_newDicSmall()
    {
        $data['PARENT_ID']=$_POST['big_id'];
        $data['DICSMALL_NO']=$_POST['dicsmall_no'];
        $data['DICSMALL_NAME']=$_POST['dicsmall_name'];
        $data['REDUNDANCY1']=$_POST['redundancy1'];
        $data['REDUNDANCY2']=$_POST['redundancy2'];
        $data['REDUNDANCY3']=$_POST['redundancy3'];
        $data['REDUNDANCY4']=$_POST['redundancy4'];
        $data['ADD_USER']=session("back_user_id");
        $data['ADD_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=0;
        $m=M('lc_dictionary_small')->add($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //小类字典----修改
    public function editDicSmall()
    {
        $dicsmall_id=$_REQUEST['dicsmall_id'];
        $this->assign("dicsmall_id",$dicsmall_id);
        $where['DICSMALL_ID']=$dicsmall_id;
        $list=M('lc_dictionary_small')->where($where)->field('DICSMALL_NO,DICSMALL_NAME,REDUNDANCY1,REDUNDANCY2,REDUNDANCY3,REDUNDANCY4')->find();
        $this->assign("list",$list);
        $this->display();
    }
    //小类字典----修改--保存
    public function save_editDicSmall()
    {
        $where['DICSMALL_ID']=$_POST['dicsmall_id'];
        $data['DICSMALL_NO']=$_POST['dicsmall_no'];
        $data['DICSMALL_NAME']=$_POST['dicsmall_name'];
        $data['REDUNDANCY1']=$_POST['redundancy1'];
        $data['REDUNDANCY2']=$_POST['redundancy2'];
        $data['REDUNDANCY3']=$_POST['redundancy3'];
        $data['REDUNDANCY4']=$_POST['redundancy4'];
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $m=M('lc_dictionary_small')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //小类字典----删除
    public function delDicSmall()
    {
        $where['DICSMALL_ID']=$_POST['dicsmall_id'];
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=1;
        $m=M('lc_dictionary_small')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    /*---***---***---区域字典---***---***---*/
    public function area()
    {
        $this->display();
    }
    //-----------------区域列表----------列表
    public function areaList()
    {
        $where['IS_DEL']=0;
        $where['PARENT_ID']=array("gt",0);
        $list=M('cq_area')->field("AREA_ID,PARENT_ID,AREA_NAME,IS_OPEN")->where($where)->order('AREA_ID asc')->select();
        $responce="";
        $topArr=array();
        $midArr=array();
        $lastArr=array();
        $allArr=array();
        foreach ($list as $key => $value) {
            $reValue=F("areaArr");
            if(!$reValue){
                $reValue=array();
            }
            if ($value['parent_id']==1) {
                $value['isLeaf']=false;
                $value['parent_id']=0;
                $value['level']=0;
                $topArr[]=$value;
            }elseif ($value['is_open']=="closed") {
                $value['isLeaf']=false;
                $value['level']=1;
                $midArr[]=$value;
            }else{
                $value['isLeaf']=true;
                $value['level']=2;
                $lastArr[]=$value;
            }
        }
        foreach ($topArr as $key => $value) {
            $allArr[]=$value;
            foreach ($midArr as $key => $val) {
                if ($val['parent_id']==$value['area_id']) {
                    $allArr[]=$val;
                    foreach ($lastArr as $key => $v) {
                        if ($v['parent_id']==$val['area_id']) {
                            $allArr[]=$v;
                        }
                    }
                }
            }
        }
        foreach ($allArr as $key => $value) {
            $responce[$key]['id']=$value['area_id'];
            $responce[$key]['area_id']=$value['area_id'];
            $responce[$key]['area_name']=$value['area_name'];
            $responce[$key]['level']=$value['level'];
            $responce[$key]['loaded']=true;
            $responce[$key]['expanded']=true;
            $responce[$key]['parent']=$value['parent_id'];
            $responce[$key]['isLeaf']=$value['isLeaf'];
            $responce[$key]['edit']="<a href='javascript:editArea(".$value['area_id'].");'>修改</a>";
            $responce[$key]['delete']="<a href='javascript:deleteArea(".$value['area_id'].");'>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //-----------区域字典------添加
    public function add_area()
    {
        $area_id=$_REQUEST['area_id'];
        $this->assign("area_id",$area_id);
        $this->display();
    }
    //-----区域字典------添加----保存
    public function save_newArea()
    {
        $data['PARENT_ID']=$_POST['area_id'];
        $data['AREA_NAME']=$_POST['area_name'];
        $data['IS_OPEN']=1;
        $data['ADD_USER']=session("back_user_id");
        $data['ADD_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=0;
        $m=M('cq_area')->add($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }

    }
    //-----区域字典------修改
    public function edit_area()
    {
        $area_id=$_REQUEST['area_id'];
        $this->assign("area_id",$area_id);
        $where['AREA_ID']=$area_id;
        $list=M('cq_area')->field('AREA_NAME')->where($where)->find();
        $this->assign("area_name",$list['area_name']);
        $this->display();
    }
    //-----区域字典------修改--保存
    public function save_editArea()
    {
        $where['AREA_ID']=$_POST['area_id'];
        $data['AREA_NAME']=$_POST['area_name'];
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $m=M('cq_area')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //----------区域字典------删除
    public function deleteArea()
    {
        $where['AREA_ID']=$_POST['area_id'];
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $data['IS_DEL']=1;
        $m=M('cq_area')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
	public function smsmang()
    {
		 $flag = 0; 
         //要post的数据 
         $argv = array( 
         'sn'=>'DXX-WSS-14M-08229', //替换成您自己的序列号
		 'pwd'=>'336178',//替换成您自己的密码
		
		 ); 
        //构造要post的字符串 
         foreach ($argv as $key=>$value) { 
          if ($flag!=0) { 
                         $params .= "&"; 
                         $flag = 1; 
          } 
         $params.= $key."="; $params.= urlencode($value); 
         $flag = 1; 
          } 
         $length = strlen($params); 
                 //创建socket连接 
        $fp = fsockopen("sdk2.entinfo.cn",8060,$errno,$errstr,10) or exit($errstr."--->".$errno); 
         //构造post请求的头 
         $header = "POST /webservice.asmx/GetBalance HTTP/1.1\r\n"; 
         $header .= "Host:sdk2.entinfo.cn\r\n"; 
         $header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
         $header .= "Content-Length: ".$length."\r\n"; 
         $header .= "Connection: Close\r\n\r\n"; 
         //添加post的字符串 
         $header .= $params."\r\n"; 
         //发送post的数据 
         fputs($fp,$header); 
         $inheader = 1; 
          while (!feof($fp)) { 
                         $line = fgets($fp,1024); //去除请求包的头只显示页面的返回数据 
                         if ($inheader && ($line == "\n" || $line == "\r\n")) { 
                                 $inheader = 0; 
                          } 
                          if ($inheader == 0) { 
                                // echo $line; 
                          } 
          } 
		  //<string xmlns="http://tempuri.org/">-5</string>
	       $line=str_replace("<string xmlns=\"http://tempuri.org/\">","",$line);
	       $line=str_replace("</string>","",$line);
		   $result=explode("-",$line);
		    if(count($result)>1)
			$strliness="查询失败".$line;
			else
			$strliness=$line." 条";
		 
		 
		 
		  $list=M('lc_website')->field('WEBSITE_SMSSN,WEBSITE_SMSPWD,WEBSITE_ID')->find();
          $this->assign("sms_sn",$list['website_smssn']);
	   	  $this->assign("sms_pwd",$list['website_smspwd']);
		  $this->assign("website_id",$list['website_id']);
		  $this->assign("mysms",$strliness);
		 
          $this->display();
	}
	
	public function saveSmsmang()
	{
		$where['WEBSITE_ID']=$_POST['website_id'];
        $data['WEBSITE_SMSSN']=$_POST['smssn'];
        $data['WEBSITE_SMSPWD']=$_POST['smspwd'];
      
        $m=M('lc_website')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
	}
	//修改首页广告位
	public function adsite(){
		
		$list=M('lc_adsite')->field("AD_ID,AD_CONTENT,AD_TITLE")->order('AD_ID asc')->select();
		$this->assign("adlist",$list);
		$this->display();
	} 
	 public function adEditInfo(){
		 $adid = $_REQUEST['adid'];
		 $where =array();
         $where['AD_ID'] = $adid;
		 $list=M('lc_adsite')->field("AD_ID,AD_CONTENT,AD_TITLE")->where($where)->order('AD_ID asc')->find();
		 $this->assign("adcontent",$list['ad_content']);
		 $this->assign("ad_id",$list['ad_id']);
    	 $this->display();
    }
	 public function save_adInfo(){
    	$newContent = $_POST['plat_brief'];
    	$model = M('lc_adsite');
		$where['AD_ID']=$_POST['ad_id'];
        $data['AD_CONTENT'] = $newContent;
        $result = $model->where($where)->save($data);
        if($result){
        	$this->ajaxReturn(1,'JSON');
        }else{
        	$this->ajaxReturn(0,'JSON');
        }
		
		
    }
}