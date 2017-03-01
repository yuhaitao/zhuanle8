<?php
namespace Admin\Controller;
use Think\Controller;
class ArticleController extends BaseController {

	public function index(){
		$this->display();
	}
	//公司介绍
	public function companyInfo(){
		$model = M('lc_article');
		$where = array();
        $where['IS_DEL'] = 0;
        $where['CODE'] = 107;

        $info = $model->field('CONTENT')->where($where)->find();

        $tranInfo = base64_decode($info['content']);
        $companyInfo = strip_tags($tranInfo);	//过滤html标签
        $this->assign('companyInfo',$tranInfo);
        // dump($tranInfo);exit;
		$this->display();
	} 
    public function editCompanyInfo(){
    	$this->companyInfo();
    }
    //修改公司介绍
    public function save_companyInfo(){
    	$newContent = $_POST['plat_brief'];

    	$model = M('lc_article');
    	$where= array();
        $where['IS_DEL'] = 0;
        $where['CODE'] = 107;
        $data['CONTENT'] = base64_encode($newContent);
        $result = $model->where($where)->save($data);
        // $this->ajaxReturn($model->getLastSql(),'JSON');
        if($result){
        	$this->ajaxReturn(1,'JSON');
        }else{
        	$this->ajaxReturn(0,'JSON');
        }
    	
    }
    //赚乐扒公告列表
    public function caiqiAnnoun(){
        $this->display();
    }
    public function caiqiNoticeList(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 1;  
        /**/
        $m = M("lc_announ a");
        $where = array();
        $where['a.IS_DEL'] = 0;
        $serarch_announ=$_POST['serarch_announ'];
        if($serarch_announ){
            // $where['a.JOINUS_TITLE']=$serarch_title;
            $where['a.ANNOUN_TITLE'] = array('like','%'.$serarch_announ.'%');
        }

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

        $list=$m->join('left join cq_back_user b ON a.USER_ID = b.BACK_USER_ID')->join('left join cq_back_user c ON a.UP_USER = c.BACK_USER_ID')->field("a.ANNOUN_ID,a.ANNOUN_TITLE,a.ADD_TIME,a.ADD_USER,a.UP_USER,a.UP_TIME,a.ANNOUN_CONTENT,a.ANNOUN_PICTURE,b.BACK_USER_NAME,c.BACK_USER_NAME as up_user")->where($where)->order('a.ANNOUN_ID desc')->limit($start,$limit)->select();
        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数 

        foreach ($list as $key => $value) {
            $responce->rows[$key]['announ_id'] = $value['announ_id'];
            $responce->rows[$key]['announ_title'] = $value['announ_title'];
            $responce->rows[$key]['add_time'] = $value['add_time'];
            $responce->rows[$key]['back_user_name'] = $value['back_user_name'];
            $responce->rows[$key]['up_user'] = $value['up_user'];
            $responce->rows[$key]['up_time'] = $value['up_time'];
            $responce->rows[$key]['announ_content'] = $value['announ_content'];
            $responce->rows[$key]['announ_picture'] = $value['announ_picture'];
            $responce->rows[$key]['rechange'] = "<a href='javascript:;' onclick= top.openUpdateAnnoun(".$value['announ_id'].")>修改</a>";
            $responce->rows[$key]['deleteAnnoun'] = "<a href='javascript:;' onclick= top.deleteAnnoun(".$value['announ_id'].")>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }


    //修改赚乐扒公告
    public function updateAnnoun(){
        $announId = $_REQUEST['announId'];
        $model = M('lc_announ');
        $where = array();
        $where['IS_DEL'] = 0;
        $where['ANNOUN_ID'] = $announId;

        $result = $model->field('ANNOUN_ID,ANNOUN_TITLE,ANNOUN_CONTENT,ANNOUN_DESCRIBE')->where($where)->find();
        $content = base64_decode($result['announ_content']);
        $this->assign('announ',$result); 
        $this->assign('announ_content',$content); 
		$this->assign('announ_describe',$result); 
        $this->display();
    }
    public function editAnnoun(){
        $anounId = $_POST['announCur'];
        $announTitle = $_POST['announ_title'];
		$news_describe= $_POST['news_describe'];
        $announContent = base64_encode($_POST['announ_brief']);
        $user_id = session('back_user_id');
        $time=date("Y-m-d H:i:s",time());
        $where = array();
        $where['ANNOUN_ID'] = $anounId;

        $data['ANNOUN_TITLE'] = $announTitle;
		$data['ANNOUN_DESCRIBE'] = $news_describe;
        $data['ANNOUN_CONTENT'] = $announContent;
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = $time;

        $model = M('lc_announ');
        $result = $model->where($where)->save($data);
        // $this->ajaxReturn($model->getLastSql(),"JSON");
       if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }

    //删除赚乐扒公告
    public function deleteAnnoun(){
        $deleteAnnounId = $_POST['deleteAnnounId'];
        $where = array();
        $where['ANNOUN_ID'] = $deleteAnnounId;
        $data['IS_DEL'] = 1;

        $model = M('lc_announ');
        $result = $model->where($where)->save($data);
        // $this->ajaxReturn($model->getLastSql(),"JSON");
       if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //添加赚乐扒公告
    public function addAnnoun(){
        $this->display();
    }
    public function addAnnounList(){

        $ann_title = $_POST['announ_title'];
		$news_describe= $_POST['news_describe'];
        $ann_content = base64_encode($_POST['addannoun_brief']);
        $user_id = session('back_user_id');
        $time=date("Y-m-d H:i:s",time());

        $model = M('lc_announ');

        $data['ANNOUN_TITLE'] = $ann_title;
        $data['ANNOUN_DESCRIBE'] = $news_describe;
		$data['ANNOUN_CONTENT'] = $ann_content;
        $data['TYPE'] = 1;
        $data['USER_ID'] = $user_id;
        $data['ADD_USER'] = $user_id;
        $data['ADD_TIME'] = $time;
        $data['IS_DEL'] = 0;

        $result = $model->add($data);

        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }

    }

    //加入我们
    public function joinus(){
        $this->display();
    }
    //加入我们列表
    public function joinUsList(){

        $model = M('cq_joinus');
        $where = array();
        $where['a.IS_DEL'] = 0;
        $serarch_title=$_POST['serarch_title'];
        if($serarch_title){
            // $where['a.JOINUS_TITLE']=$serarch_title;
            $where['a.JOINUS_TITLE'] = array('like','%'.$serarch_title.'%');
        }

        $list = $model->table('cq_joinus a')->join('left join cq_back_user b ON a.ADD_USER = b.BACK_USER_ID')->join('left join cq_back_user c ON a.UP_USER = c.BACK_USER_ID')->field('a.JOINUS_ID,a.JOINUS_TITLE,a.JOINUS_CONTENT,a.ADD_TIME,a.UP_TIME,a.JOINUS_PLACE,b.BACK_USER_NAME,c.BACK_USER_NAME as up_user')->where($where)->select();

        foreach ($list as $key => $value) {

            $responce->rows[$key]['joinus_id'] = $value['joinus_id'];
            $responce->rows[$key]['joinus_title'] = $value['joinus_title'];
            $responce->rows[$key]['joinus_content'] = $value['joinus_content'];
            $responce->rows[$key]['add_user'] = $value['back_user_name'];
            $responce->rows[$key]['add_time'] = $value['add_time'];
            $responce->rows[$key]['up_user'] = $value['up_user'];
            $responce->rows[$key]['up_time'] = $value['up_time'];
            $responce->rows[$key]['joinus_place'] = $value['joinus_place'];
            $responce->rows[$key]['editJoinus'] = "<a href='javascript:;' onclick= top.editJoinus(".$value['joinus_id'].")>修改</a>";
            $responce->rows[$key]['deleteJoinus'] = "<a href='javascript:;' onclick= top.deleteJoinus(".$value['joinus_id'].")>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }

    //添加 加入我们
    public function addJoinUs(){
        $this->display();
    }
    //添加 加入我们列表
    public function addJoinUsList(){
       
        $join_title = $_POST['joinus_title'];
        $join_place = $_POST['joinus_place'];
        $join_content = base64_encode($_POST['addjoinus_brief']);

        $user_id = session('back_user_id');
        $time=date("Y-m-d H:i:s",time());

        $model = M('cq_joinus');

        $data['JOINUS_TITLE'] = $join_title;
        $data['JOINUS_PLACE'] = $join_place;
        $data['JOINUS_CONTENT'] = $join_content;
        $data['ADD_USER'] = $user_id;
        $data['ADD_TIME'] = $time;
        $data['IS_DEL'] = 0;

        $result = $model->add($data);
       
        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //修改加入我们
    public function updateJoinUs(){
        $joinusId = $_REQUEST['joinusId'];
        $model = M('cq_joinus');
        $where = array();
        $where['IS_DEL'] = 0;
        $where['JOINUS_ID'] = $joinusId;

        $result = $model->field('JOINUS_ID,JOINUS_TITLE,JOINUS_CONTENT,JOINUS_PLACE')->where($where)->find();
        $content = base64_decode($result['joinus_content']);

        $this->assign('joinus',$result); 
        $this->assign('joinus_content',$content); 
        $this->display();
    }
    public function editJoinus(){
        $joinusId = $_POST['joinusCur'];
        $joinusTitle = $_POST['joinus_title'];
        $joinuspPlace = $_POST['joinus_place'];
        $joinusContent = base64_encode($_POST['addjoinus_brief']);
        $user_id = session('back_user_id');
        $time=date("Y-m-d H:i:s",time());
        $where = array();
        $where['JOINUS_ID'] = $joinusId;

        $data['JOINUS_TITLE'] = $joinusTitle;
        $data['JOINUS_PLACE'] = $joinuspPlace;
        $data['JOINUS_CONTENT'] = $joinusContent;
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = $time;

        $model = M('cq_joinus');
        $result = $model->where($where)->save($data);
   
       if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }

    //删除加入我们
    public function deleteJoinus(){
        $deleteJoinusId = $_POST['deleteJoinusId'];
        $where = array();
        $where['JOINUS_ID'] = $deleteJoinusId;
        $data['IS_DEL'] = 1;

        $model = M('cq_joinus');
        $result = $model->where($where)->save($data);
       if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
}