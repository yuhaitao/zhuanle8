<?php
namespace Admin\Controller;
use Think\Controller;
class NewsController extends BaseController {

	public function index(){
		$this->display();
	}
	
    //幻灯新闻
    public function slideNews(){

        $this->display();
    }
    public function slideNewsList(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 1;  

        $model = M('cq_slide');
        $where = array();
        $where['a.IS_DEL'] = 0;
        /*总记录数*/
        $count = $model->where("IS_DEL = 0")->count();
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

        $list = $model->table('cq_slide a')->join('left join cq_back_user b ON a.USER_ID = b.BACK_USER_ID')->join('left join cq_back_user c ON a.UP_USER = c.BACK_USER_ID')->field('a.SLIDE_ID,a.SLIDE_TITLE,a.SLIDE_DESCRIPTION,b.BACK_USER_NAME,c.BACK_USER_NAME as update_user,a.ADD_TIME,a.UP_USER,a.UP_TIME')->where($where)->order('a.SLIDE_ID desc')->limit($start,$limit)->select();

        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数 

        foreach ($list as $key => $value) {

            $responce->rows[$key]['slide_id'] = $value['slide_id'];
            $responce->rows[$key]['slide_title'] = $value['slide_title'];
            $responce->rows[$key]['slide_description'] = $value['slide_description'];
            $responce->rows[$key]['add_user'] = $value['back_user_name']; 
            $responce->rows[$key]['add_time'] = $value['add_time']; 
            $responce->rows[$key]['up_user'] = $value['update_user']; 
            $responce->rows[$key]['up_time'] = $value['up_time']; 
            $responce->rows[$key]['change'] = "<a href='javascript:;' onclick = top.openNewsForm('".$value['slide_id']."')>修改</a>";
            $responce->rows[$key]['delete'] = "<a href='javascript:;' onclick = top.deleteNewsForm('".$value['slide_id']."')>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //添加幻灯新闻
    public function addSlideNews(){
        $this->display();
    }
    public function addSlideNewsList(){

        $userId = session('back_user_id');  //添加者
        $time=date("Y-m-d H:i:s",time());   //添加时间

        $slide_title = $_POST['news_title'];
        $slide_description = $_POST['news_describe'];   
        $slide_content = $_POST['slidenews_brief'];   
        $newstr = base64_encode($slide_content);

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
            $data['SLIDE_IMAGE']='/upload'.$info['savepath'].$info['savename'];
        }

        $data['SLIDE_TITLE'] =  $slide_title;
        $data['SLIDE_DESCRIPTION'] =  $slide_description;
        $data['SLIDE_CONTENT'] =  $newstr;
        $data['USER_ID'] =  $userId;
        $data['ADD_USER'] =  $userId;
        $data['ADD_TIME'] =  $time;
        $data['IS_DEL'] =  0;

        $model = M('cq_slide');

        $result = $model->add($data);
        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }

    //修改幻灯新闻
    public function updateSlideNews(){

        $slideNews_id = $_REQUEST['slideNewId'];
        $where= array();
        $where['IS_DEL'] = 0;
        $where['SLIDE_ID'] = $slideNews_id;

        $model = M('cq_slide');
        $result = $model->field('SLIDE_ID,SLIDE_TITLE,SLIDE_DESCRIPTION,SLIDE_CONTENT')->where($where)->find();
        $content = base64_decode($result['slide_content']);
        $this->assign('slideInfo',$result);
        $this->assign('slide_content',$content); 

        $this->display();
    }

    public function newSlideNews(){
        $newslide = $_POST['news_id'];
        $up_user = session('back_user_id');
        $time = date('Y-m-d:H:i:s',time());

        $slide_title = $_POST['snews_title'];
        $slide_description = $_POST['snews_describe'];   
        $slide_content = $_POST['sslidenews_brief'];   
        $newstr = base64_encode($slide_content);

        if(!empty($_FILES['snews_img']['tmp_name'])){
            $upload = new \Think\Upload();// 实例化上传类    
            $upload->maxSize   =     3145728 ;// 设置附件上传大小 3M  
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->savePath  =      '/image/'.$up_user.'/'; // 设置附件上传目录    
            // 上传单个文件     
            $info   =   $upload->uploadOne($_FILES['snews_img']);    
            if(!$info) {// 上传错误提示错误信息        
                $this->error($upload->getError());    
            }
            $data['SLIDE_IMAGE']='/upload'.$info['savepath'].$info['savename'];
        }

        $data['SLIDE_TITLE'] =  $slide_title;
        $data['SLIDE_DESCRIPTION'] =  $slide_description;
        $data['SLIDE_CONTENT'] =  $newstr;
        $data['UP_USER'] =  $up_user;
        $data['UP_TIME'] =  $time;

        $model = M('cq_slide');
        $where = array();
        $where['SLIDE_ID'] = $newslide;

        $result = $model->where($where)->save($data);
        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }

    //删除幻灯新闻
    public function deleteSlideNews(){
        $slideNewId = $_POST['slideNewId'];
        $where = array();
        $where['SLIDE_ID'] = $slideNewId;
        $data['IS_DEL'] = 1;

        $model = M('cq_slide');
        $result = $model->where($where)->save($data);
       if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }

    //理财新闻
    public function news(){
        $this->display();
    }
    //理财新闻列表
    public function newsList(){
        
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 1;  

        $model = M('cq_news');
        $where = array();
        $where['a.IS_DEL'] = 0;

        $serarch_title=$_POST['serarch_title'];
        if($serarch_title){
            $where['a.NEWS_NAME'] = array('like','%'.$serarch_title.'%');
        }

        /*总记录数*/
        $count = $model->where("IS_DEL = 0")->count();
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

        $list = $model->table('cq_news a')->join('left join cq_back_user b ON a.ADD_USER = b.BACK_USER_ID')->join('left join cq_back_user c ON a.UP_USER = c.BACK_USER_ID')->field('a.NEWS_ID,a.NEWS_NAME,a.NEWS_DESCRIPTION,a.IS_HEADLINE,a.IS_HOT,a.IS_HOME,a.IS_TOP,b.BACK_USER_NAME,c.BACK_USER_NAME as update_user,a.ADD_TIME,a.UP_TIME')->where($where)->order('a.NEWS_ID desc')->limit($start,$limit)->select();

        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数 

        foreach ($list as $key => $value) {

            $responce->rows[$key]['news_id'] = $value['news_id'];
            $responce->rows[$key]['news_name'] = $value['news_name'];
            $responce->rows[$key]['news_description'] = $value['news_description'];
            $responce->rows[$key]['add_user'] = $value['back_user_name']; 
            $responce->rows[$key]['add_time'] = $value['add_time']; 
            $responce->rows[$key]['up_user'] = $value['update_user']; 
            $responce->rows[$key]['up_time'] = $value['up_time']; 

            $isheadline = '';
            if($value['is_headline'] == 0){
                $isheadline = '否';
            }
            if($value['is_headline'] == 1){
                $isheadline = '是';
            }
            $ishot = '';
            if($value['is_hot'] == 0){
                $ishot = '否';
            }
            if($value['is_hot'] == 1){
                $ishot = '是';
            }
            $istop = '';
            if($value['is_top'] == 0){
                $istop = '否';
            }
            if($value['is_top'] == 1){
                $istop = '是';
            }
            $ishome = '';
            if($value['is_home'] == 0){
                $ishome = '否';
            }
            if($value['is_home'] == 1){
                $ishome = '是';
            }


            $responce->rows[$key]['is_headline'] = $isheadline; 
            $responce->rows[$key]['is_hot'] = $ishot; 
            $responce->rows[$key]['is_top'] = $istop; 
            $responce->rows[$key]['is_home'] = $ishome; 
            $responce->rows[$key]['change'] = "<a href='javascript:;' onclick = top.openNews('".$value['news_id']."')>修改</a>";
            $responce->rows[$key]['delete'] = "<a href='javascript:;' onclick = top.deleteNews('".$value['news_id']."')>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
        // $this->ajaxReturn($model->getLastSql(),'JSON');

    }
    //添加理财新闻
    public function addNews(){
        $model = M('cq_news_classify');
        $classify = $model->field('NEWS_CLASSIFY_ID,NEWS_CLASSIFY_NAME,ORDER_NUM')->where("IS_DEL = 0")->select();

        $this->assign('classify',$classify);
        $this->display();
    }
    public function addNewsList(){
        $userId = session('back_user_id');  //添加者
        $time=date("Y-m-d H:i:s",time());   //添加时间

        $news_title = $_POST['news_title'];
        $isheadline = $_POST['isHeadLine'];   
        $isHot = $_POST['isHot'];   
        $isTop = $_POST['isTop'];   
        $isHome = $_POST['isHome'];   
        $news_describe = $_POST['news_describe'];   
        $news_classify = $_POST['news_classify'];
        $news_brief = $_POST['news_brief'];   
        $newstr = base64_encode($news_brief);

        
        $m = M('cq_news_tags');
        $news_tags = $_POST['news_tags'];
        $str_news = explode(',',$news_tags);
        $tags_id =',';
        foreach ($str_news as $key => $value) {
            $where = array();
            $where['NEWS_TAGS_NAME'] = $value;

            $info = $m->field('NEWS_TAGS_ID')->where($where)->find();
            if($info){
                $m->where($where)->setInc('TAGS_USE_TIME');
                $tags_id.= $info['news_tags_id'].',';
            }else{
                $str['NEWS_TAGS_NAME'] = $value;
                $str['TAGS_USE_TIME'] =  1;
                $vet = $m->add($str);
                $tags_id.= $vet.',';
            }
        }

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
            $data['PICTURE_LINK']='/upload'.$info['savepath'].$info['savename'];
        }

        $data['NEWS_NAME'] =  $news_title;
        $data['NEWS_DESCRIPTION'] =  $news_describe;
        $data['NEWS_CONTENT'] =  $newstr;
        
        $data['IS_HEADLINE'] =  $isheadline;
        $data['IS_HOT'] =  $isHot;
        $data['IS_TOP'] =  $isTop;
        $data['IS_HOME'] =  $isHome;
        $data['NEWS_CLASSIFY_ID'] =  $news_classify;
        $data['NEWS_TAGS_ID'] =  $tags_id;
        $data['ADD_TIME'] =  $time;
        $data['ADD_USER'] =  $userId;
        $data['IS_DEL'] =  0;

        $model = M('cq_news');

        $result = $model->add($data);

        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
        
        
    }

    //修改新闻
    public function updateNews(){

        $news_id = $_REQUEST['newsId'];

        $model = M('cq_news');
        $where =array();
        $where['a.NEWS_ID'] = $news_id;
        $result = $model->table('cq_news a')->join('left join cq_news_classify b ON a.NEWS_CLASSIFY_ID = b.NEWS_CLASSIFY_ID')->field('a.NEWS_ID,a.NEWS_NAME,a.NEWS_DESCRIPTION,a.NEWS_CONTENT,a.NEWS_TAGS_ID,a.IS_HOME,a.IS_HOT,a.IS_HEADLINE,a.IS_TOP,b.NEWS_CLASSIFY_NAME')->where($where)->find();
        //是否头条
        if($result['is_headline'] == 1){
            $result['is_headline'] = '<label class="radio-inline">
                      <input type="radio" name="isHeadLine" id="isHeadLine1" value="1" checked>是
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="isHeadLine" id="isHeadLine2" value="0">否
                    </label>';
        }else{
            $result['is_headline'] = '<label class="radio-inline">
                      <input type="radio" name="isHeadLine" id="isHeadLine1" value="1" >是
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="isHeadLine" id="isHeadLine2" value="0" checked>否
                    </label>';
        }
        //是否要闻
        if($result['is_hot'] == 1){
            $result['is_hot'] = '<label class="radio-inline">
                      <input type="radio" name="isHot" id="isHot1" value="1" checked>是
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="isHot" id="isHot2" value="0">否
                    </label>';
        }else{
            $result['is_hot'] = '<label class="radio-inline">
                      <input type="radio" name="isHot" id="isHot1" value="1" >是
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="isHot" id="isHot2" value="0" checked>否
                    </label>';
        }
        //是否置顶
        if($result['is_top'] == 1){
            $result['is_top'] = '<label class="radio-inline">
                      <input type="radio" name="isTop" id="isTop1" value="1" checked>是
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="isTop" id="isTop2" value="0">否
                    </label>';
        }else{
            $result['is_top'] = '<label class="radio-inline">
                      <input type="radio" name="isTop" id="isTop1" value="1" checked>是
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="isTop" id="isTop2" value="0" checked>否
                    </label>';
        }
        //是否首页
        if($result['is_home'] == 1){
            $result['is_home'] = '<label class="radio-inline">
                      <input type="radio" name="isHome" id="isHome1" value="1" checked>是
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="isHome" id="isHome2" value="0">否
                    </label>';
        }else{
            $result['is_home'] = '<label class="radio-inline">
                      <input type="radio" name="isHome" id="isHome1" value="1" >是
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="isHome" id="isHome2" value="0" checked>否
                    </label>';
        }
        $content = base64_decode($result['news_content']);

        $m = M('cq_news_classify');
		$where = array();
        $where['IS_DEL'] = 0;
        $fenlei = $m->field('NEWS_CLASSIFY_ID,NEWS_CLASSIFY_NAME')->where($where)->select();
        $fenleistr = '';
        foreach ($fenlei as $key => $value) {
            if($value['news_classify_name'] == $result['news_classify_name']){
                $fenleistr .= '<option value="'.$value['news_classify_id'].'" selected>'.$value['news_classify_name'].'</option>';
            }else{
                $fenleistr .= '<option value="'.$value['news_classify_id'].'">'.$value['news_classify_name'].'</option>';
            }
        }
        $tags_id = explode(',',$result['news_tags_id']);
        $x = M('cq_news_tags');
        $where_b = array();
        $where_b['NEWS_TAGS_ID'] = array('in',$tags_id);
        $tags = $x->field('NEWS_TAGS_NAME')->where($where_b)->select();
        $newstags = '';
        foreach ($tags as $key => $value) {
            $newstags.= $value['news_tags_name'].',';
        }
        $tag = substr($newstags,0,-1);
        $this->assign('fenlei',$fenleistr);
        $this->assign('tags',$tag);
        $this->assign('content',$content);
        $this->assign('newsinfo',$result);
        $this->display();
    }

    public function saveNews(){
        $userId = session('back_user_id');  //修改者
        $time=date("Y-m-d H:i:s",time());   //修改时间

        $news_title = $_POST['news_title'];
        $news_id = $_POST['news_id'];
        $where_c = array();
        $where_c['NEWS_ID'] = $news_id;
        $isheadline = $_POST['isHeadLine'];   
        $isHot = $_POST['isHot'];   
        $isTop = $_POST['isTop'];   
        $isHome = $_POST['isHome'];   
        $news_describe = $_POST['news_describe'];   
        $news_classify = $_POST['news_classify'];
        $news_brief = $_POST['news_brief'];   
        $newstr = base64_encode($news_brief);


        $m = M('cq_news_tags');
        $news_tags = $_POST['news_tags'];
        $str_news = explode(',',$news_tags);
        $tags_id =',';
        foreach ($str_news as $key => $value) {
            $where = array();
            $where['NEWS_TAGS_NAME'] = $value;

            $info = $m->field('NEWS_TAGS_ID')->where($where)->find();
            if($info){
                $m->where($where)->setInc('TAGS_USE_TIME');
                $tags_id.= $info['news_tags_id'].',';
            }else{
                $str['NEWS_TAGS_NAME'] = $value;
                $str['TAGS_USE_TIME'] =  1;
                $vet = $m->add($str);
                $tags_id.= $vet.',';
            }
        }

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
            $data['PICTURE_LINK']='/upload'.$info['savepath'].$info['savename'];
        }

        $data['NEWS_NAME'] =  $news_title;
        $data['NEWS_DESCRIPTION'] =  $news_describe;
        $data['NEWS_CONTENT'] =  $newstr;
        
        $data['IS_HEADLINE'] =  $isheadline;
        $data['IS_HOT'] =  $isHot;
        $data['IS_TOP'] =  $isTop;
        $data['IS_HOME'] =  $isHome;
        $data['NEWS_CLASSIFY_ID'] =  $news_classify;
        $data['NEWS_TAGS_ID'] =  $tags_id;
        $data['UP_TIME'] =  $time;
        $data['UP_USER'] =  $userId;
        $data['IS_DEL'] =  0;

        $model = M('cq_news');

        $result = $model->where($where_c)->save($data);
        // $this->ajaxReturn($model->getLastSql(),'JSON');
        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //删除理财新闻
    public function deleteNews(){
        $newId = $_POST['newId'];
        $where = array();
        $where['NEWS_ID'] = $newId;
        $data['IS_DEL'] = 1;

        $model = M('cq_news');
        $result = $model->where($where)->save($data);
       if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }

    //媒体报道
    public function media(){
        $this->display();
    }
    public function mediaList(){

        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 1;  
        /**/
        $model= M("lc_media");
        $where = array();
        $where['a.IS_DEL'] = 0;

        $serarch_title=$_POST['serarch_title'];
        if($serarch_title){
            $where['a.MEDIA_TITLE'] = array('like','%'.$serarch_title.'%');
        }
        /*总记录数*/
        $count = $model->where('IS_DEL = 0')->count();
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
        $list = $model->table('lc_media a')->join('left join cq_back_user b ON a.ADD_USER = b.BACK_USER_ID')->join('left join cq_back_user c ON a.UP_USER = c.BACK_USER_ID')->field('a.MEDIA_ID,a.MEDIA_TITLE,a.MEDIA_DESCRIBE,b.BACK_USER_NAME,a.ADD_TIME,c.BACK_USER_NAME as up_user,a.UP_TIME')->where($where)->order('a.MEDIA_ID desc')->limit($start,$limit)->select();

        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数 

        foreach ($list as $key => $value) {
            $responce->rows[$key]['media_id'] = $value['media_id'];
            $responce->rows[$key]['media_title'] = $value['media_title'];
            $responce->rows[$key]['media_describe'] = $value['media_describe']; 
            $responce->rows[$key]['add_user'] = $value['back_user_name']; 
            $responce->rows[$key]['add_time'] = $value['add_time']; 
            $responce->rows[$key]['up_user'] = $value['up_user']; 
            $responce->rows[$key]['up_time'] = $value['up_time']; 

            $responce->rows[$key]['change'] = "<a href='javascript:;' onclick = top.openMediaForm('".$value['media_id']."')>修改</a>";
            $responce->rows[$key]['delete'] = "<a href='javascript:;' onclick = top.deleteMediaForm('".$value['media_id']."')>删除</a>";
        }


        $this->ajaxReturn($responce,"JSON");
    }

    //修改媒体报道
    public function updateMedia(){
        $mediaId = $_REQUEST['mediaId'];
        $model = M('lc_media');
        $where = array();
        $where['IS_DEL'] = 0;
        $where['MEDIA_ID'] = $mediaId;

        $result = $model->field('MEDIA_ID,MEDIA_TITLE,MEDIA_DESCRIBE,MEDIA_CONTENT')->where($where)->find();
        $content = base64_decode($result['media_content']);

        $this->assign('media',$result); 
        $this->assign('content',$content); 
        $this->display();
    }
    public function updateMediaList(){

        $mediaId = $_POST['media_id'];
        $mediaTitle = $_POST['media_title'];

        $mediaDescribe = $_POST['media_describe'];
        $mediaBrief = $_POST['media_brief'];

        $back_user_id=session("back_user_id");
        $time = date('Y-m-d:H:i:s',time());
        $model = M('lc_media');

       if(!empty($_FILES['media_img']['tmp_name'])){
            $upload = new \Think\Upload();// 实例化上传类    
            $upload->maxSize   =     3145728 ;// 设置附件上传大小 3M  
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->savePath  =      '/image/'.$back_user_id.'/'; // 设置附件上传目录    
            // 上传单个文件     
            $info   =   $upload->uploadOne($_FILES['media_img']);    
            if(!$info) {// 上传错误提示错误信息        
                $this->error($upload->getError());    
            }
            $data['MEDIA_PICTURE']='/upload'.$info['savepath'].$info['savename'];
        }

        $data['MEDIA_TITLE'] = $mediaTitle;
        $data['MEDIA_DESCRIBE'] = $mediaDescribe;
        $data['MEDIA_CONTENT'] = base64_encode($mediaBrief);
        $data['UP_TIME'] = $time;
        $data['UP_USER'] = $back_user_id;

        $where = array();
        $where['MEDIA_ID'] = $mediaId;

        $result = $model->where($where)->save($data);
        // $this->ajaxReturn($model->getLastSql(),"JSON");
        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //添加媒体报道
    public function addMedia(){
        $this->display();
    }
    public function addMediaList(){
        $userId = session('back_user_id');  //添加者
        $time=date("Y-m-d H:i:s",time());   //添加时间

        $media_title = $_POST['media_title'];
        $media_description = $_POST['media_describe'];   
        $media_content = $_POST['media_brief'];   
        $mediastr = base64_encode($media_content);

        if(!empty($_FILES['media_img']['tmp_name'])){
            $upload = new \Think\Upload();// 实例化上传类    
            $upload->maxSize   =     3145728 ;// 设置附件上传大小 3M  
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->savePath  =      '/image/'.$userId.'/'; // 设置附件上传目录    
            // 上传单个文件     
            $info   =   $upload->uploadOne($_FILES['media_img']);    
            if(!$info) {// 上传错误提示错误信息        
                $this->error($upload->getError());    
            }
            $data['MEDIA_PICTURE']='/upload'.$info['savepath'].$info['savename'];
        }

        $data['MEDIA_TITLE'] =  $media_title;
        $data['MEDIA_DESCRIBE'] =  $media_description;
        $data['MEDIA_CONTENT'] =  $mediastr;
        $data['USER_ID'] =  $userId;
        $data['ADD_USER'] =  $userId;
        $data['ADD_TIME'] =  $time;
        $data['IS_DEL'] =  0;

        $model = M('lc_media');

        $result = $model->add($data);
        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }

    //删除媒体报道
    public function deleteMedia(){
        $deId = $_POST['demediaId'];
        $where = array();
        $where['MEDIA_ID'] = $deId;
        $data['IS_DEL'] = 1;

        $model = M('lc_media');
        $result = $model->where($where)->save($data);
       if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }

    //热门标签
    public function hotTags(){
        
        $this->display();
    }
    public function hotTagsList(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 1;  

        $model = M('cq_news_tags');

         /*总记录数*/
        $count = $model->where()->count();
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
        $where = array();
        $serarch_tag=$_POST['serarch_tag'];
        if($serarch_tag){
            $where['NEWS_TAGS_NAME'] = array('like','%'.$serarch_tag.'%');
        }

        $list = $model->field('NEWS_TAGS_ID,NEWS_TAGS_NAME,IS_HOT')->where($where)->order('NEWS_TAGS_ID desc')->limit($start,$limit)->select();

        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $hot = '';
        foreach ($list as $key => $value) {
            $responce->rows[$key]['news_tags_id'] = $value['news_tags_id'];
            $responce->rows[$key]['news_tags_name'] = $value['news_tags_name'];
            if($value['is_hot'] == 1){
                $hot = '<font color=red>是</font>';
            }else{
                $hot = '否';
            }
            $responce->rows[$key]['is_hot'] = $hot;
            $responce->rows[$key]['change'] = "<a href='javascript:;' onclick = top.openTagsForm('".$value['news_tags_id']."')>修改</a>"
            ;$responce->rows[$key]['delete'] = "<a href='javascript:;' onclick = top.deleteTagsForm('".$value['news_tags_id']."')>删除</a>";

        }
        $this->ajaxReturn($responce,"JSON");
    }

    //修改标签
    public function updateTag(){
        $tagId = $_REQUEST['tagId'];

        $where = array();
        $where['NEWS_TAGS_ID'] = $tagId;
        $model = M('cq_news_tags');
        $result = $model->field('NEWS_TAGS_ID,NEWS_TAGS_NAME,IS_HOT')->where($where)->find();

        if($result['is_hot'] == 1){
            $result['is_hot'] = '<label class="radio-inline">
                      <input type="radio" name="isHot" id="isHot1" value="1" checked>是
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="isHot" id="isHot2" value="0">否
                    </label>';
        }else{
            $result['is_hot'] = '<label class="radio-inline">
                      <input type="radio" name="isHot" id="isHot1" value="1">是
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="isHot" id="isHot2" value="0" checked>否
                    </label>';
        }

        $this->assign('tagsInfo',$result);
        $this->display();
    }

    public function saveTag(){
        $tagId = $_POST['tag_id'];
        $tagName = $_POST['tag_name'];
        $isHot = $_POST['isHot'];

        $model = M('cq_news_tags');

        $data['NEWS_TAGS_NAME'] = $tagName;
        $data['IS_HOT'] = $isHot;

        $where = array();
        $where['NEWS_TAGS_ID'] = $tagId;

        $result = $model->where($where)->save($data);

        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //添加热门标签
    public function addTag(){
        $this->display();
    }
    public function addTagList(){

        $tagName = $_POST['tag_name'];
        $isHot = $_POST['isHot'];

        $model = M('cq_news_tags');

        $data['NEWS_TAGS_NAME'] = $tagName;
        $data['IS_HOT'] = $isHot;

        $result = $model->add($data);

        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //删除热门标签
    public function deleteTags(){
        $de_tagId = $_REQUEST['delete_id'];
        $model = M('cq_news_tags');
        $where = array();
        $where['NEWS_TAGS_ID'] = $de_tagId;
        $result = $model->where($where)->delete();

        if($result){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //新闻分类
    public function classify()
    {
        $this->display();
    }
    //新闻分类列表
    public function classifyList()
    {
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式   
        if (!$sidx)    $sidx = 1;  

        $model = M('cq_news_classify');
        $where=array();
        $where['IS_DEL']=0;
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

        $list = $model->field('NEWS_CLASSIFY_ID,NEWS_CLASSIFY_NAME,ORDER_NUM')->where($where)->order($sidx.' '.$sord)->limit($start,$limit)->select();

        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        foreach ($list as $key => $value) {
            $responce->rows[$key]['news_classify_id']=$value['news_classify_id'];
            $responce->rows[$key]['news_classify_name']=$value['news_classify_name'];
            $responce->rows[$key]['order_num']=$value['order_num'];
            $responce->rows[$key]['change']="<a href='javascript:;' onclick = top.openClassifyForm('".$value['news_classify_id']."')>修改</a>";
            $responce->rows[$key]['delete'] = "<a href='javascript:;' onclick = top.deleteClassifyForm('".$value['news_classify_id']."')>删除</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //保存新的分类
    public function saveClassify()
    {
        $classify_name=$_POST['classify_name'];
        $order_num=$_POST['order_num'];
        $classify_title=$_POST['classify_title'];
        $classify_tag=$_POST['classify_tag'];
        $classify_id=M("cq_news_classify")->where("IS_DEL=0 and ORDER_NUM=$order_num")->getField("NEWS_CLASSIFY_ID");
        if ($classify_id){
            $this->ajaxReturn("该序号已存在",'JSON');
        }
        $userId = session('back_user_id');  //添加者
        $time=date("Y-m-d H:i:s",time());   //添加时间
        $data=array();
        $data['NEWS_CLASSIFY_NAME']=$classify_name;
        $data['ORDER_NUM']=$order_num;
        $data['NEWS_CLASSIFY_TITLE']=$classify_title;
        $data['NEWS_CLASSIFY_NICK']=$classify_tag;
        $data['ADD_USER']=$userId;
        $data['ADD_TIME']=$time;
        $m=M('cq_news_classify')->add($data);
        if ($m) {
            $this->ajaxReturn("1",'JSON');
        }else{
            $this->ajaxReturn("添加失败",'JSON');
        }
    }
    //保存删除的分类
    public function deleteClassify()
    {
        $userId = session('back_user_id');  //添加者
        $time=date("Y-m-d H:i:s",time());   //添加时间
        $classify_id=$_POST['classifyId'];
        $data=array('IS_DEL'=>1,'UP_USER'=>$userId,'UP_TIME'=>$time);
        $m=M("cq_news_classify")->where("NEWS_CLASSIFY_ID=$classify_id")->setField($data);
        if ($m) {
            $this->ajaxReturn("1",'JSON');
        }else{
            $this->ajaxReturn("0",'JSON');
        }
    }
    //修改新闻分类
    public function updateClassify()
    {
        $classify_id=$_GET['classifyId'];
        $this->assign("classifyId",$classify_id);
        $classify=M('cq_news_classify')->where("NEWS_CLASSIFY_ID=$classify_id and IS_DEL=0")->field("NEWS_CLASSIFY_NAME,ORDER_NUM,NEWS_CLASSIFY_TITLE,NEWS_CLASSIFY_NICK")->find();
        $this->assign("classify",$classify);
        $this->display();
    }
    //保存修改
    public function saveUpClassify()
    {
        $classify_id=$_POST['classifyId'];
        $classify_name=$_POST['classify_name'];
        $order_num=$_POST['order_num'];
        $classify_title=$_POST['classify_title'];
        $classify_tag=$_POST['classify_tag'];

        $where['IS_DEL']=0;
        $where['ORDER_NUM']=$order_num;
        $where['NEWS_CLASSIFY_ID']=array("neq",$classify_id);
        $classify=M("cq_news_classify")->where($where)->getField("NEWS_CLASSIFY_ID");
        if ($classify){
            $this->ajaxReturn("该序号已存在",'JSON');
        }
        $userId = session('back_user_id');  //添加者
        $time=date("Y-m-d H:i:s",time());   //添加时间
        $data['NEWS_CLASSIFY_NAME']=$classify_name;
        $data['ORDER_NUM']=$order_num;
        $data['NEWS_CLASSIFY_TITLE']=$classify_title;
        $data['NEWS_CLASSIFY_NICK']=$classify_tag;
        $data['UP_USER']=$userId;
        $data['UP_TIME']=$time;
        $option['NEWS_CLASSIFY_ID']=$classify_id;
        $option['IS_DEL']=0;
        $m=M('cq_news_classify')->where($option)->save($data);
        if ($m) {
            $this->ajaxReturn("1",'JSON');
        }else{
            $this->ajaxReturn("修改失败",'JSON');
        }
    }
}