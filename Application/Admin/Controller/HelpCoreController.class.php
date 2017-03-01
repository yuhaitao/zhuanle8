<?php
/**
* 意见反馈控制类
*/
namespace Admin\Controller;
use Think\Controller;
class HelpCoreController extends BaseController {
    public function index(){
    	
    }
    public function feedback(){
    	$this->display();
    }
    //回复
    public function reply(){
    	$reply_id = $_REQUEST['reply_id'];	//获取reply_id
        $this->assign("reply_id",$reply_id);
    	$model = M('cq_feedback');
    	$where = array();
    	$where['a.FEEDBACK_ID'] = $reply_id;
    	$where['a.IS_DEL'] = 0;
    	$list = $model->table('cq_feedback a')->join('lc_user b ON a.DESCRIBE_USER = b.USER_ID')->field('a.DESCRIBE_USER,b.USER_NAME,a.DESCRIPTION,a.REPLY')->where($where)->find();
        $this->assign('reply_list',$list);
    	$this->display();
    }
    //保存
    public function save_reply(){
        $reply_id = $_POST['revise_id'];  //获取reply_id
        $reply = $_POST['reply'];
        $user_id=session('back_user_id');//修改人
        $time=date("Y-m-d H:i:s",time());//修改时间
        $where['FEEDBACK_ID']=$reply_id;
        $data['REPLY']=$reply;
        $data['REPLY_USER']=$user_id;
        $data['UP_USER']=$user_id;
        $data['UP_TIME']=$time;
        $model=M('cq_feedback');
        $m=$model->where($where)->save($data);
        if($m){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn($model->getLastSql(),'JSON');
        }
    }
    public function feedback_list(){
    	$page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        //查询条件
        $nickname=$_POST['nickname'];
        $mobile = $_POST['mobile'];
        $where = array();
        $where_b = array();

        $where['IS_DEL']=0;
        if($nickname){
            $where['NICK_NAME']=array("like","%".$nickname."%");
            $where_b['b.NICK_NAME']=array("like","%".$nickname."%");

        }
        if($mobile){
            $where['MOBILE']=array("like","%".$mobile."%");
            $where_b['b.MOBILE']=array("like","%".$mobile."%");
        }
        $where_b['a.IS_DEL']=0;

        $model = M('cq_feedback');
        $count = $model->where($where)->count(); //总记录数
        if($count > 0){
            $total_pages = ceil($count/$limit);
        }else{
            $total_pages = 0;
        }
        if($page > $total_pages)
            $page = $total_pages;
        $start = $limit * $page - $limit;
        if($start < 0)
            $start = 0;
        $responce->page = intval($page); //当前页
        $responce->total = $total_pages; //总页数
        $responce->records = $count;     //总记录数
        $list = $model->table('cq_feedback a')->join('lc_user b ON a.DESCRIBE_USER = b.USER_ID')->field('a.FEEDBACK_ID,b.MOBILE,b.NICK_NAME,a.DESCRIBE_USER,a.ADD_TIME,a.DESCRIPTION,a.REPLY')->where($where_b)->order($sidx." ".$sord)->limit($start,$limit)->select();

        foreach ($list as $key => $value) {
        	$responce->rows[$key]['describe_user'] = $value['describe_user'];	//用户ID
        	$responce->rows[$key]['mobile'] = $value['mobile'];	//用户手机号
        	$responce->rows[$key]['nick_name'] = $value['nick_name'];	//用户昵称
        	$responce->rows[$key]['add_time'] = $value['add_time'];		//提问时间
        	$responce->rows[$key]['description'] = $value['description'];	//问题描述
        	$responce->rows[$key]['reply']	=	$value['reply'];	//问题回复
        	$responce->rows[$key]['huifu'] = "<a href='javascript:parent.openUpdate(".$value['feedback_id'].");'>回复</a>";
        	$responce->rows[$key]['delete'] = "<a href='javascript:del_Feedback(".$value['feedback_id'].");'>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }

    //删除-意见回馈
    public function delete_feedback(){
        $delete_id = $_POST['delete_id'];
        $model = M('cq_feedback');
        $model->IS_DEL = 1; 
        $where = array('FEEDBACK_ID' => $delete_id);
        $model ->where($where)->save();
        if($model){
            $this->ajaxReturn('1','JSon');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }

    //意见反馈-类型分类
    public function feedbackType(){

        $model = M('lc_dictionary_small');
        $where = array();
        $where['IS_DEL'] = '0';
        $where['PARENT_ID'] = '41';
        $list = $model->field('DICSMALL_NAME,DICSMALL_NO')->where($where)->select();
        $this->assign('type_list',$list);
        $this->display();
    }
    public function feedback_show(){
        $show_id = $_POST['choose_id'];
        $where = array();
        $where['a.TYPE'] = $show_id;
        $where['a.IS_DEL'] = 0;
        $model = M('cq_feedback');
        $list = $model->table('cq_feedback a')->join('cq_back_user b ON a.REPLY_USER = b.BACK_USER_ID')->field('a.FEEDBACK_ID,a.DESCRIPTION,a.REPLY,a.UP_TIME,b.BACK_USER_NAME')->where($where)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['description'] = $value['description'];   //问题描述
            $responce->rows[$key]['back_user_name'] = $value['back_user_name'];   //修改人
            $responce->rows[$key]['up_time'] = $value['up_time'];   //修改时间
            $responce->rows[$key]['huifu'] = "<a href='javascript:updateFeed(".$value['feedback_id'].");'>修改</a>";
            $responce->rows[$key]['delete'] = "<a href='javascript:deleteFeedback(".$value['feedback_id'].");'>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //意见反馈-类型分类-修改
    public function updateFeed(){
        $updateFeed_id = $_REQUEST['updateFeed_id'];
        $model = M('cq_feedback');
        $where = array();
        $where['a.FEEDBACK_ID'] = $updateFeed_id;
        $where['a.IS_DEL'] = 0;

        $list = $model->table('cq_feedback a')->join('lc_dictionary_small b ON a.TYPE = b.DICSMALL_NO')->field('a.DESCRIPTION,a.FEEDBACK_ID,a.REPLY,b.DICSMALL_NAME,b.PARENT_ID')->where($where)->find();
        $list['reply'] = strip_tags($list['reply']);

        $m = M('lc_dictionary_small');
        $parent_id = $list['parent_id'];
        $where_b = array();
        $where_b['PARENT_ID'] = $parent_id;
        $list_m = $m->field('DICSMALL_NAME,DICSMALL_NO')->where($where_b)->select();
        $options = "";
        foreach ($list_m as $key => $value) {
            if($value['dicsmall_name'] == $list['dicsmall_name'] ){
                $options.="<option value='".$value['dicsmall_no']."' selected>".$value['dicsmall_name']."</option>";
            }else{
                 $options.="<option value='".$value['dicsmall_no']."'>".$value['dicsmall_name']."</option>";
            }
        }
        $this->assign('options',$options);
        $this->assign('updatelist',$list);
        $this->display();
    }
    //意见反馈-类型分类-更新
    public function save_feed(){
        $save_id = $_POST['update_id'];
        $save_reply = $_POST['reply'];
        $save_type = $_POST['question_type'];
        $user_id=session('back_user_id');
        $time=date("Y-m-d H:i:s",time());
        $data['REPLY'] = $save_reply;
        $data['TYPE'] = $save_type;
        $data['REPLY_USER'] = $user_id;
        $data['UP_TIME'] = $time;
        $where = array('FEEDBACK_ID' => $save_id);
        $model = M('cq_feedback')->where($where)->save($data);
        if($model){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
    public function add_feedback(){
        $model = M('lc_dictionary_small');
        $where = array();
        $where['IS_DEL'] = '0';
        $where['PARENT_ID'] = '41';
        $list = $model->field('DICSMALL_NAME,DICSMALL_NO')->where($where)->select();

        foreach ($list as $key => $value) {
             $options.="<option value='".$value['dicsmall_no']."'>".$value['dicsmall_name']."</option>";
        }

        $this->assign('options',$options);
        $this->display();
    }
    //意见反馈-类型分类-添加
    public function add_new_feedback(){
        //获取提交变量
        $new_type = $_POST['question_type'];
        $new_description = $_POST['description'];
        $new_reply = $_POST['reply'];
        $time = date("Y-m-d H:i:s",time());
        $user_id = session('back_user_id');

        $m = M('cq_back_user');
        $conditon = array();
        $conditon['a.BACK_USER_ID'] = $user_id;
        $conditon['a.IS_DEL'] = 0;
        $mlist = $m->table('cq_back_user a')->join('lc_user b ON a.BACK_USER_MOBILE = b.MOBILE')->field('b.USER_ID')->where($conditon)->find();

        $data['DESCRIPTION'] = $new_description;
        $data['TYPE'] = $new_type;
        $data['REPLY'] = $new_reply;
        $data['DESCRIBE_USER'] = $mlist['user_id'];
        $data['REPLY_USER'] = $user_id;
        $data['ADD_USER'] = $mlist['user_id'];
        $data['ADD_TIME'] = $time;
        $data['IS_DEL'] = 0;
        // dump($data);exit;
        
        $insert = M('cq_feedback');
        $result = $insert->add($data);
        if($result){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
        
    }
}