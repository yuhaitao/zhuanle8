<?php
namespace Admin\Controller;
use Think\Controller;
class ActiveController extends BaseController {

	public function index(){
		$this->display();
	}
	//活动管理
    public function activeManager(){
        $this->display();
    }
    //活动列表
    public function activeManagerList(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 1;  
        /**/
        $model= M("cq_active");
        $where = array();
        $where['IS_DEL'] = 0;
        /*总记录数*/
        $count = $model->where($where)->count();
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
        $list = $model->field('ACTIVE_ID,ACTIVE_NAME,ACTIVE_ENABLED,START_DATE,END_DATE,ACTIVE_URL')->where('IS_DEL =0')->order('ACTIVE_ID desc')->limit($start,$limit)->select();

        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数 

        foreach ($list as $key => $value) {
            $responce->rows[$key]['active_id'] = $value['active_id'];
            $responce->rows[$key]['active_name'] = $value['active_name'];
            $responce->rows[$key]['active_url'] = $value['active_url']; 
            $responce->rows[$key]['start_date'] = $value['start_date']; 
            $responce->rows[$key]['end_date'] = $value['end_date']; 

            if($value['active_enabled'] == 1){
                $responce->rows[$key]['state'] = '进行中';
            }else{
                $responce->rows[$key]['state'] = '已结束';
            }

            $responce->rows[$key]['change'] = "<a href='javascript:;' onclick = top.openActiveForm('".$value['active_id']."')>修改</a>";
        }


        $this->ajaxReturn($responce,"JSON");
    }

    //修改活动
    public function updateActive(){

        $activeId = $_REQUEST['activeId'];
        $model = M('cq_active');
        $where = array();
        $where['IS_DEL'] = 0;
        $where['ACTIVE_ID'] = $activeId;

        $result = $model->field('ACTIVE_ID,ACTIVE_NAME,ACTIVE_PIC,START_DATE,END_DATE,ACTIVE_URL,ACTIVE_ENABLED')->where($where)->find();
        $state = '';
        if($result['active_enabled'] == 1){
            $result['active_enabled'] = '进行中';
            $state = '<option selected value=1>进行中</option>
                      <option value=2 >已结束</option>';
        }else{
             $state = '<option selected value=2>已结束</option>
                       <option value=1 >进行中</option>';
        }
        // dump($result);exit;
        $this->assign('active',$result); 
        $this->assign('state',$state); 
        $this->display();
    }
    public function changeActive(){

        $activeId = $_POST['active_id'];
        $activeTitle = $_POST['active_title'];
        $imgUrl = $_POST['photo'];
        $startTime = $_POST['start_time'];
        $endTime = $_POST['end_time'];
        $activeUrl = $_POST['active_url'];
        $state = $_POST['state'];
        $back_user_id=session("back_user_id");
        $model = M('cq_active');

       if(!empty($_FILES['active_img']['tmp_name'])){
            $upload = new \Think\Upload();// 实例化上传类    
            $upload->maxSize   =     3145728 ;// 设置附件上传大小 3M  
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->savePath  =      '/image/'.$back_user_id.'/'; // 设置附件上传目录    
            // 上传单个文件     
            $info   =   $upload->uploadOne($_FILES['active_img']);    
            if(!$info) {// 上传错误提示错误信息        
                $this->error($upload->getError());    
            }
            $data['ACTIVE_PIC']='/upload'.$info['savepath'].$info['savename'];
        }

        $data['ACTIVE_NAME'] = $activeTitle;
        $data['START_DATE'] = $startTime;
        $data['END_DATE'] = $endTime;
        $data['ACTIVE_URL'] = $activeUrl;
        $data['ACTIVE_ENABLED'] = $state;
        $data['UP_USER'] = $back_user_id;
        
        $where = array();
        $where['ACTIVE_ID'] = $activeId;

        $result = $model->where($where)->save($data);
        // $this->ajaxReturn($model->getLastSql(),"JSON");
        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //添加活动
    public function addActive(){
        $this->display();
    }
    public function addActiveList(){

        $activeTitle = $_POST['active_title'];
        $imgUrl = $_POST['photo'];
        $startTime = $_POST['start_time'];
        $endTime = $_POST['end_time'];
        $activeUrl = $_POST['active_url'];
        $state = $_POST['state'];
        $add_iser=session("back_user_id");
        $time = date("Y-m-d H:i:s",time());

        $data['ACTIVE_PIC'] =  $imgUrl;
        $data['ACTIVE_NAME'] =  $activeTitle;
        $data['ACTIVE_ENABLED'] =  $state;
        $data['ADD_USER'] =  $add_iser;
        $data['ADD_TIME'] =  $time;
        $data['START_DATE'] =  $startTime;
        $data['END_DATE'] =  $endTime;
        $data['ACTIVE_URL'] =  $activeUrl;
        $data['IS_DEL'] =  0;

        $model = M('cq_active');
        $result = $model->add($data);

        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
}