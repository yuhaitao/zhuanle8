<?php
namespace Home\Controller;
use Think\Controller;

class NewsController extends BaseController {
    public function index(){
    	$this->display();
    }
    public function rightList(){
         //媒体报道
        $m = M('lc_media');
        $mediaList = $m->field('MEDIA_ID,MEDIA_TITLE,MEDIA_DESCRIBE,MEDIA_PICTURE')->where('IS_DEL=0')->order('MEDIA_ID desc')->limit(6)->select();
        //热门标签
        $tag = M('cq_news_tags');
        $tagList = $tag->field('NEWS_TAGS_ID,NEWS_TAGS_NAME')->where('IS_HOT=1')->limit(12)->select();


        $this->assign('tagList',$tagList);

        $this->assign('mediaList',$mediaList);

         //热门排行
        $mod = M();
        ////本日排行
        $nowtime=time();
        $stime=date("Y-m-d",$nowtime);
        $etime=date("Y-m-d",$nowtime+86400);
        $option=array();
        $option['NEWS_READ_TIME']=array("between",array($stime,$etime));
        $subSql=$mod->field("count(NEWS_READ_ID) as count,NEWS_ID")->table("cq_news_read")->group("NEWS_ID")->where($option)->order("count desc")->limit(11)->buildSql();
        $dayData=$mod->table($subSql." a")->join("left join cq_news b on a.NEWS_ID=b.NEWS_ID")->where("b.IS_DEL=0")->field("a.count,a.NEWS_ID,b.NEWS_NAME")->select();
        $this->assign("dayData",$dayData);
        /////本周排行
        $where_week=array();
        $where_week['NEWS_READ_TIME']=array("between",array(date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"))),date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y")))));
        $subSql_W=$mod->field("count(NEWS_READ_ID) as count,NEWS_ID")->table("cq_news_read")->group("NEWS_ID")->where($where_week)->order("count desc")->limit(11)->buildSql();
        $weekData=$mod->table($subSql_W." a")->join("left join cq_news b on a.NEWS_ID=b.NEWS_ID")->where("b.IS_DEL=0")->field("a.count,a.NEWS_ID,b.NEWS_NAME")->select();
        $this->assign("weekData",$weekData);
        ////本月排行
        $where_month=array();
        $where_month['NEWS_READ_TIME']=array("between",array(date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y"))),date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y")))));
        $subSql_M=$mod->field("count(NEWS_READ_ID) as count,NEWS_ID")->table("cq_news_read")->group("NEWS_ID")->where($where_month)->order("count desc")->limit(11)->buildSql();
        $monthData=$mod->table($subSql_M." a")->join("left join cq_news b on a.NEWS_ID=b.NEWS_ID")->where("b.IS_DEL=0")->field("a.count,a.NEWS_ID,b.NEWS_NAME")->select();
        $this->assign("monthData",$monthData);

    }
    //新闻列表
    public function newsvw(){
    	header('Content-Type:text/html;charset=UTF-8');
        $site_title="新闻资讯-赚乐扒";//网站title
        $this->assign('site_title',$site_title);
    
        $model=M("cq_news");
		$cond = array();
		$cond['IS_DEL']=0;
        $count= $model->where($cond)->count();// 查询满足要求的总记录数
		$page = getpage($count,8);
        $show= $page->show_(1);// 分页显示输出
        $newshot=$model->where($cond)->order("NEWS_ID desc")->limit($page->firstRow.','.$page->listRows)->select();
		 foreach ($newshot as $ke => $value) {
            $tags_id=$value['news_tags_id'];
            $tags_id=substr($tags_id,1,-1);

            $param = array();

            $param['NEWS_TAGS_ID']=array("in",$tags_id);
            //标签数组
            $tagsArr=M('cq_news_tags')->where($param)->field("NEWS_TAGS_NAME")->select();
            //放入上级数组中
            $newshot[$ke]['tags']=$tagsArr;
            $endtime=time();
            $starttime=strtotime($value['add_time']);
            //设置时间显示
            $timediff = $endtime - $starttime;
            $days = intval( $timediff / 86400 );
            $remain = $timediff % 86400;
            $hours = intval( $remain / 3600 );
            $remain = $remain % 3600;
            $mins = intval( $remain / 60 );
            $secs = $remain % 60;
            if ($days>0 && $days < 7) {
                $newshot[$ke]['add_time']=$days."天前";
            }elseif ($days==0 && $hours >0) {
                $newshot[$ke]['add_time']=$hours."小时前";
            }elseif ($days==0 && $hours==0 && $mins > 0) {
                $newshot[$ke]['add_time']=$mins."分钟前";
            }elseif ($days==0 && $hours==0 && $mins==0 && $secs > 0) {
                $newshot[$ke]['add_time']="刚刚";
            }
            if($value['picture_link'] == ''){
                $newshot[$ke]['picture_link'] = '/Public/images/home/icon/unnew.png';
            }
        }
		$this->assign("newshot",$newshot);
		
		$this->assign('page',$show);// 赋值分页输出
        $this->display(); // 输出模板
    }
   
    //幻灯新闻详情
    public function slideInfo(){
    	$article_id  = $_GET['slide_id'];
    	$model = M('cq_slide');
    	$where = array();
    	$where['IS_DEL'] = 0;
    	$where['SLIDE_ID'] = $article_id;
    	$result = $model->field('SLIDE_ID,SLIDE_TITLE,SLIDE_CONTENT,SLIDE_IMAGE,ADD_TIME,READ_TIME')->where($where)->find();
    	$content = base64_decode($result['slide_content']);
    	$info = $model->where($where)->setInc('READ_TIME');
        $this->rightList();
    	$this->assign('newscontent',$content);
    	$this->assign('articleInfo',$result);

        $site_title=$result['slide_title']."-赚乐扒";//网站title
        $this->assign('site_title',$site_title);

        /*相关阅读*/
        $idRange=$model->field("min(SLIDE_ID) as min,max(SLIDE_ID) as max")->select();
        $idList="";
        for($i=0;$i < 10; $i++){
            $rand=rand($idRange[0]['min'],$idRange[0]['max']);
            $idList.=$rand.",";
        }
        $idList=substr($idList, 0,-1);
        $opt=array();
        $cond=array();
        $cond['_string']='SLIDE_ID <> '.$article_id.' and SLIDE_ID in ('.$idList.') ';
        $cond['IS_DEL'] = 0;
        $slideArr=$model->where($cond)->field("SLIDE_ID,SLIDE_TITLE,SLIDE_DESCRIPTION,SLIDE_IMAGE,ADD_TIME")->limit(2)->order("READ_TIME desc")->select();
        $this->assign("slideArr",$slideArr);
    	$this->display();
    }
    
    //理财专栏列表页
    public function financelist(){
        $site_title="理财专栏-赚乐扒";//网站title
        $this->assign('site_title',$site_title);

        $model = M('cq_slide');
        $where = array();
        $where['IS_DEL'] = 0;

        $count  = $model->where($where)->count();// 查询满足要求的总记录数
        $page = getpage($count,5);
        $show = $page->show_(1);// 分页显示输出
        $media = $model->where($where)->order("ADD_TIME desc")->limit($page->firstRow, $page->listRows)->select();

        $this->assign('financeInfo',$media);
        $this->assign('page',$show);// 赋值分页输出

        $this->display();
    }
    //理财专栏详情
    public function financeDetail(){

        $news_id  = $_GET['news_id'];
        $model = M('cq_slide');
        $where = array();
        $where['IS_DEL'] = 0;
        $where['SLIDE_ID'] = $news_id;
        $result = $model->where($where)->find();
        $content = base64_decode($result['slide_content']);
        $site_title=$result['slide_title']."-赚乐扒";//网站title
        $this->assign('site_title',$site_title);
        $this->assign('flcontent',$content);
        $this->assign('slidenews',$result);
		
		$where_d = array();
        $where_d['SLIDE_ID'] = array('GT',$news_id) ;
        $where_d['IS_DEL'] = 0;

        $where_c = array();
        $where_c['SLIDE_ID'] = array('LT',$news_id) ;
        $where_c['IS_DEL'] = 0;
		
		//上一篇  
        $front = $model->where($where_d)->order('SLIDE_ID asc')->limit('1')->find(); 
        $front_info = !$front?"没有了":"<a class='bot_p3_a' href=".__APP__."/financeview/".$front['slide_id'].".html>".$front['slide_title']."</a>";    
        //下一篇
        $after = $model->where($where_c)->order('SLIDE_ID desc')->limit('1')->find();  
        $next_info = !$after?"没有了":"<a class='bot_p3_a' href=".__APP__."/financeview/".$after['slide_id'].".html>".$after['slide_title']."</a>";  
        // dump($after);exit;
        $this->assign('next_info',$next_info);
        $this->assign('front_info',$front_info);
		
		$datas = M('cq_news');
		$noList = $datas->field('NEWS_ID,NEWS_NAME')->where('IS_DEL = 0')->order('READ_TIME desc')->limit('10')->select();
        $this->assign('artview_a',$noList);	
		$where_c = array();
        $where_c['IS_TOP'] = 1 ;
        $where_c['IS_DEL'] = 0;
		$nwList = $datas->field('NEWS_ID,NEWS_NAME')->where($where_c)->order('NEWS_ID desc')->limit('10')->select();
        $this->assign('artview_b',$nwList);	
		
		
		
        $this->display();
    }
    //新闻列表页
    public function newsList(){
        $listId = $_REQUEST['tag_id'];
        if ($listId) {
            $tg=M('cq_news_tags')->where("NEWS_TAGS_ID = $listId")->field("NEWS_TAGS_NAME")->find();
            $this->assign("tag_name",$tg['news_tags_name']);
        }
        $model=M('cq_news');
        $cond['IS_DEL']=0;
        //根据标签查询
     
        $count      = $model->where($cond)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show_(2);// 分页显示输出
        $newsArr=$model->where($cond)->field("NEWS_ID,NEWS_NAME,NEWS_DESCRIPTION,NEWS_TAGS_ID,PICTURE_LINK,ADD_TIME")->order("ADD_TIME desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach ($newsArr as $ke => $value) {
            $tags_id=$value['news_tags_id'];
            $tags_id=substr($tags_id,1,-1);

            $param = array();

            $param['NEWS_TAGS_ID']=array("in",$tags_id);
            //标签数组
            $tagsArr=M('cq_news_tags')->where($param)->field("NEWS_TAGS_NAME")->select();
            //放入上级数组中
            $newsArr[$ke]['tags']=$tagsArr;
            $endtime=time();
            $starttime=strtotime($value['add_time']);
            //设置时间显示
            $timediff = $endtime - $starttime;
            $days = intval( $timediff / 86400 );
            $remain = $timediff % 86400;
            $hours = intval( $remain / 3600 );
            $remain = $remain % 3600;
            $mins = intval( $remain / 60 );
            $secs = $remain % 60;
            if ($days>0 && $days < 7) {
                $newsArr[$ke]['add_time']=$days."天前";
            }elseif ($days==0 && $hours >0) {
                $newsArr[$ke]['add_time']=$hours."小时前";
            }elseif ($days==0 && $hours==0 && $mins > 0) {
                $newsArr[$ke]['add_time']=$mins."分钟前";
            }elseif ($days==0 && $hours==0 && $mins==0 && $secs > 0) {
                $newsArr[$ke]['add_time']="刚刚";
            }
            if($value['picture_link'] == ''){
                $newsArr[$ke]['picture_link'] = '/Public/images/home/icon/unnew.png';
            }
        }
        $this->assign("newsArr",$newsArr);
        $this->assign('page',$show);// 赋值分页输出
        $this->display(); // 输出模板
    }
    //分类新闻列表
    public function classify()
    {
        $listId = $_REQUEST['cid'];
        $newsListId = "'".$listId."'";
        $cy=M('cq_news_classify')->where("NEWS_CLASSIFY_NICK = $newsListId AND IS_DEL = 0")->field("NEWS_CLASSIFY_ID,NEWS_CLASSIFY_TITLE,NEWS_CLASSIFY_NAME")->find();
        // dump($cy);exit;
        $site_title=$cy['news_classify_title']."-赚乐扒";//网站title
        $this->assign('site_title',$site_title);
        $this->assign("classify_name",$cy['news_classify_name']);
        $model=M('cq_news');
        $cond['IS_DEL']=0;
        //根据标签查询
        $cond['NEWS_CLASSIFY_ID']= $cy['news_classify_id'];
        $count      = $model->where($cond)->count();// 查询满足要求的总记录数
        $Page       = getpage($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show_(2);// 分页显示输出
        $newsArr=$model->where($cond)->field("NEWS_ID,NEWS_NAME,NEWS_DESCRIPTION,NEWS_TAGS_ID,PICTURE_LINK,ADD_TIME")->order("ADD_TIME desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach ($newsArr as $ke => $value) {
            $tags_id=$value['news_tags_id'];
            $tags_id=substr($tags_id,1,-1);

            $param = array();

            $param['NEWS_TAGS_ID']=array("in",$tags_id);
            //标签数组
            $tagsArr=M('cq_news_tags')->where($param)->field("NEWS_TAGS_NAME")->select();
            //放入上级数组中
            $newsArr[$ke]['tags']=$tagsArr;
            $endtime=time();
            $starttime=strtotime($value['add_time']);
            //设置时间显示
            $timediff = $endtime - $starttime;
            $days = intval( $timediff / 86400 );
            $remain = $timediff % 86400;
            $hours = intval( $remain / 3600 );
            $remain = $remain % 3600;
            $mins = intval( $remain / 60 );
            $secs = $remain % 60;
            if ($days > 0 && $days < 7) {
                $newsArr[$ke]['add_time']=$days."天前";
            }elseif ($days==0 && $hours > 0) {
                $newsArr[$ke]['add_time']=$hours."小时前";
            }elseif ($days==0 && $hours==0 && $mins > 0) {
                $newsArr[$ke]['add_time']=$mins."分钟前";
            }elseif ($days==0 && $hours==0 && $mins==0 && $secs > 0) {
                $newsArr[$ke]['add_time']="刚刚";
            }else{
                $newsArr[$ke]['add_time']=$value['add_time'];
            }
            if($value['picture_link'] == ''){
                $newsArr[$ke]['picture_link'] = '/Public/images/home/icon/unnew.png';
            }
        }

        $this->rightList();
        $this->assign("newsArr",$newsArr);
        $this->assign('page',$show);// 赋值分页输出
        $this->display(); // 输出模板

    }
    //新闻详情
    public function newsInfo(){
        $newID = $_GET['news_id'];
        /*记录阅读记录*/
        $data['NEWS_READ_IP'] = get_client_ip();
        $user_info=session("user_info");
        $data['NEWS_READ_USER']=$user_info['user_id'];
        $data['NEWS_READ_TIME']=date("Y-m-d H:i:s",time());
        $data['NEWS_ID']=$newID;
        M('cq_news_read')->add($data);

        /*新闻详情*/
        $model = M('cq_news');
        $where = array();
        $where['a.IS_DEL'] = 0;
        $where['a.NEWS_ID'] = $newID;
        $result = $model->table('cq_news a')->join('left join cq_news_classify b ON a.NEWS_CLASSIFY_ID = b.NEWS_CLASSIFY_ID')->field('a.NEWS_ID,a.NEWS_NAME,a.NEWS_DESCRIPTION,a.NEWS_CONTENT,a.NEWS_TAGS_ID,a.ADD_TIME,a.READ_TIME,b.NEWS_CLASSIFY_NAME,b.NEWS_CLASSIFY_NICK')->where($where)->find();
        $encode = mb_detect_encoding($result['news_content'], array("UTF-8","GB2312","GBK"));

        //判断字符串编码 并根据不同编码转换成utf-8
        if($encode == "GB2312"){
            $content = iconv("GB2312","UTF-8",base64_decode($result['news_content']));
        }elseif($encode == "GBK"){
            $content = iconv("GBK","UTF-8",base64_decode($result['news_content']));
        }else{
            $content = base64_decode($result['news_content']);
        }
        $where_b = array();
        $where_b['IS_DEL'] = 0;
        $where_b['NEWS_ID'] = $newID;
        $info = $model->where($where_b)->setInc('READ_TIME');
        $this->assign('newsNr',$content);
        $this->assign('newsInfo',$result);

        $site_title=$result['news_name']."-赚乐扒";//网站title
        $this->assign('site_title',$site_title);
		
		$where_d = array();
        $where_d['NEWS_ID'] = array('GT',$newID) ;
        $where_d['IS_DEL'] = 0;

        $where_c = array();
        $where_c['NEWS_ID'] = array('LT',$newID) ;
        $where_c['IS_DEL'] = 0;
		
		//上一篇  
        $front = $model->where($where_d)->order('NEWS_ID asc')->limit('1')->find(); 
        $front_info = !$front?"没有了":"<a class='bot_p3_a' href=".__APP__."/newsview/".$front['news_id'].".html>".$front['news_name']."</a>";    
        //下一篇
        $after = $model->where($where_c)->order('NEWS_ID desc')->limit('1')->find();  
        $next_info = !$after?"没有了":"<a class='bot_p3_a' href=".__APP__."/newsview/".$after['news_id'].".html>".$after['news_name']."</a>";  
        // dump($after);exit;
        $this->assign('next_info',$next_info);
        $this->assign('front_info',$front_info);
		
		$datas = M('cq_news');
		$noList = $datas->field('NEWS_ID,NEWS_NAME')->where('IS_DEL = 0')->order('READ_TIME desc')->limit('10')->select();
        $this->assign('artview_a',$noList);	
		$where_c = array();
        $where_c['IS_TOP'] = 1 ;
        $where_c['IS_DEL'] = 0;
		$nwList = $datas->field('NEWS_ID,NEWS_NAME')->where($where_c)->order('NEWS_ID desc')->limit('10')->select();
        $this->assign('artview_b',$nwList);	
        $this->display();
    }
    
}