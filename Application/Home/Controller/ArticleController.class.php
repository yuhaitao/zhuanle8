<?php
namespace Home\Controller;
use Think\Controller;

class ArticleController extends BaseController {
    public function index(){
        
    }

    //查看赚乐扒公告
    public function SeeNotice(){

    }
    //赚乐扒公告列表
    public function SeeList(){
        header('Content-Type:text/html;charset=UTF-8');
        $ip = get_client_ip();//IP地址获取
        $user_info=session("user_info");
        $user_id=$user_info['user_id'];//获取用户id
        if ($user_id) {
            $data['USER_ID']=$user_id;
        }
        $data['USER_IP']=$ip;
        $data['USER_LOG']="平台公告";
        $data['LOG_DATE']=date("Y-m-d H:i:s")." ".substr(date("l"), 0,3);
        $data['LOG_MTHOD']="SeeList";
        $data['LOG_URL']="/aboutus/sitenotice.html";
        M('lc_log')->add($data);

        $model = M('lc_announ');

        $count = $model->where('IS_DEL = 0')->count();  //总记录数
        $page = getpage($count,10);

        $show = $page->show_(1);// 分页显示输出

        $noticeList = $model->field('ANNOUN_ID,ANNOUN_TITLE,ADD_TIME,ANNOUN_DESCRIBE')->where('IS_DEL = 0')->order('ADD_TIME desc')->limit($page->firstRow, $page->listRows)->select();
        // dump($responce);exit;
        $this->assign('noticeList',$noticeList);
        $this->assign('page',$show);// 赋值分页输出

        $site_title="平台公告-赚乐扒";//网站title
        $this->assign('site_title',$site_title); 
        $this->display();
    }
    //查看文章内容
    public function SeeDetail(){
        $article_id  = $_GET['article_id'];
        // var_dump($article_id);exit;
        $model = M('lc_announ');
        $where = array();
        $where['ANNOUN_ID'] = $article_id;
        $where['IS_DEL'] = 0;

        $result = $model->field('ANNOUN_TITLE,ANNOUN_CONTENT,ADD_TIME')->where($where)->find();
        $list_content = base64_decode($result['announ_content']);

        $next_info = '';
        $front_info = '';
        $where_b = array();
        $where_b['ANNOUN_ID'] = array('GT',$article_id) ;
        $where_b['IS_DEL'] = 0;

        $where_c = array();
        $where_c['ANNOUN_ID'] = array('LT',$article_id) ;
        $where_c['IS_DEL'] = 0;

        //上一篇  
        $front = $model->where($where_b)->order('ANNOUN_ID asc')->limit('1')->find(); 
        $front_info = !$front?"没有了":"<a class='bot_p3_a' href=".__APP__."/aboutus/sitenotice/".$front['announ_id'].".html>".$front['announ_title']."</a>";    
        //下一篇
        $after = $model->where($where_c)->order('ANNOUN_ID desc')->limit('1')->find();  

        $next_info = !$after?"没有了":"<a class='bot_p3_a' href=".__APP__."/aboutus/sitenotice/".$after['announ_id'].".html>".$after['announ_title']."</a>";  
        // dump($after);exit;
        $this->assign('next_info',$next_info);
        $this->assign('front_info',$front_info);
        $this->assign('article_list',$result);
        $this->assign('article_nr',$list_content);
		
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

    //公司简介
    public function CompanyInfo(){
        $ip = get_client_ip();//IP地址获取
        $user_info=session("user_info");
        $user_id=$user_info['user_id'];//获取用户id
        if ($user_id) {
            $data['USER_ID']=$user_id;
        }
        $data['USER_IP']=$ip;
        $data['USER_LOG']="公司简介";
        $data['LOG_DATE']=date("Y-m-d H:i:s")." ".substr(date("l"), 0,3);
        $data['LOG_MTHOD']="CompanyInfo";
        $data['LOG_URL']="www.zhuanle8.com/aboutus/companyinfo.html";
        M('lc_log')->add($data);

        $model = M('lc_article');
        $where = array();
        $where['IS_DEL'] = 0;
        $where['CODE'] = 107;

        $info = $model->field('CONTENT')->where($where)->find();

        $tranInfo = base64_decode($info['content']);
        $this->assign('tanInfo',$tranInfo);
        $site_title="公司介绍-赚乐扒";//网站title
        $this->assign('site_title',$site_title); 
        $this->display();
    }
    //加入我们
    public function JoinUs(){
        $ip = get_client_ip();//IP地址获取
        $user_info=session("user_info");
        $user_id=$user_info['user_id'];//获取用户id
        if ($user_id) {
            $data['USER_ID']=$user_id;
        }
        $data['USER_IP']=$ip;
        $data['USER_LOG']="加入我们";
        $data['LOG_DATE']=date("Y-m-d H:i:s")." ".substr(date("l"), 0,3);
        $data['LOG_MTHOD']="JoinUs";
        $data['LOG_URL']="www.zhuanle8.com/aboutus/join.html";
        M('lc_log')->add($data);

        $model = M('cq_joinus');
        $result = $model->field('JOINUS_ID,JOINUS_TITLE,JOINUS_CONTENT,JOINUS_PLACE')->where('IS_DEL = 0')->select();
        foreach ($result as $key => $value) {
           $result[$key]['joinus_content'] = base64_decode($value['joinus_content']);
        }
        // dump($result);exit;
        $this->assign('joblist',$result);
        $site_title="加入我们-赚乐扒";//网站title
        $this->assign('site_title',$site_title); 
        $this->display();
    }
    //联系我们
    public function ContactUs(){
        $ip = get_client_ip();//IP地址获取
        $user_info=session("user_info");
        $user_id=$user_info['user_id'];//获取用户id
        if ($user_id) {
            $data['USER_ID']=$user_id;
        }
        $data['USER_IP']=$ip;
        $data['USER_LOG']="联系我们";
        $data['LOG_DATE']=date("Y-m-d H:i:s")." ".substr(date("l"), 0,3);
        $data['LOG_MTHOD']="ContactUs";
        $data['LOG_URL']="www.zhuanle8.com/aboutus/contact.html";
        M('lc_log')->add($data);

        header('Content-Type:text/html;charset=UTF-8');
        $model = M('lc_dictionary_small');
        
        $address = $model->table('lc_dictionary_small a')->join('lc_dictionary_big as b ON a.PARENT_ID = b.DICBIG_ID')->field('a.DICSMALL_NAME,a.REDUNDANCY1,a.REDUNDANCY2,a.REDUNDANCY3,b.DICBIG_NAME')->where('b.DICBIG_ID = 74')->find();
        $emailInfo = $model->table('lc_dictionary_small a')->join('lc_dictionary_big as b ON a.PARENT_ID = b.DICBIG_ID')->field('a.DICSMALL_NAME,a.REDUNDANCY1,a.REDUNDANCY2,a.REDUNDANCY3,b.DICBIG_NAME')->where('b.DICBIG_ID = 75')->find();
        $bussInfo = $model->table('lc_dictionary_small a')->join('lc_dictionary_big as b ON a.PARENT_ID = b.DICBIG_ID')->field('a.DICSMALL_NAME,a.REDUNDANCY1,a.REDUNDANCY2,a.REDUNDANCY3,b.DICBIG_NAME')->where('b.DICBIG_ID = 76')->find();

        $this->assign('address',$address);
        $this->assign('emailInfo',$emailInfo);
        $this->assign('bussInfo',$bussInfo);

        $site_title="加入我们-赚乐扒";//网站title
        $this->assign('site_title',$site_title); 

        $this->display();
    }
    //理财资讯
    public function BusinessNews(){
        header('Content-Type:text/html;charset=UTF-8');
        $ip = get_client_ip();//IP地址获取
        $user_info=session("user_info");
        $user_id=$user_info['user_id'];//获取用户id
        if ($user_id) {
            $data['USER_ID']=$user_id;
        }
        $data['USER_IP']=$ip;
        $data['USER_LOG']="理财资讯";
        $data['LOG_DATE']=date("Y-m-d H:i:s")." ".substr(date("l"), 0,3);
        $data['LOG_MTHOD']="BusinessNews";
        $data['LOG_URL']="www.zhuanle8.com/aboutus/business.html";
        M('lc_log')->add($data);

        $model = M('cq_journalism');

        $count = $model->where('IS_DEL = 0')->count();  //总记录数
        $page = getpage($count,10);

        $show = $page->show_(1);// 分页显示输出

        $businessList = $model->field('JOURNALISM_ID,JOURNALISM_TITLE,JOURNALISM_DESCRIBE,JOURNALISM_PICTURE,ADD_TIME')->where('IS_DEL = 0')->order('ADD_TIME desc')->limit($page->firstRow, $page->listRows)->select();
        // dump($businessList);exit;
        $this->assign('businessList',$businessList);
        $this->assign('page',$show);// 赋值分页输出

        $site_title="新闻资讯资讯-赚乐扒";//网站title
        $this->assign('site_title',$site_title); 

        $this->display();
    }
    //理财资讯详情
    public function BusinessDetail(){
        $business_id  = $_GET['business_id'];
        // var_dump($business_id);exit;
        $model = M('cq_journalism');
        $where = array();
        $where['JOURNALISM_ID'] = $business_id;
        $where['IS_DEL'] = 0;

        $result = $model->field('JOURNALISM_TITLE,JOURNALISM_CONTENT,ADD_TIME')->where($where)->find();
        $list_content = base64_decode($result['journalism_content']);

        $next_info = '';
        $front_info = '';
        $where_b = array();
        $where_b['JOURNALISM_ID'] = array('GT',$business_id) ;
        $where_b['IS_DEL'] = 0;

        $where_c = array();
        $where_c['JOURNALISM_ID'] = array('LT',$business_id) ;
        $where_c['IS_DEL'] = 0;

        //上一篇  
        $front = $model->where($where_b)->order('JOURNALISM_ID asc')->limit('1')->find(); 
        $front_info = !$front?"没有了":"<a class='bot_p3_a' href=".__APP__."/aboutus/business/".$front['journalism_id'].".html>".$front['journalism_title']."</a>";    
        //下一篇
        $after = $model->where($where_c)->order('JOURNALISM_ID desc')->limit('1')->find();  
        // dump($model->getLastSql());exit;
        $next_info = !$after?"没有了":"<a class='bot_p3_a' href=".__APP__."/aboutus/business/".$after['journalism_id'].".html>".$after['journalism_title']."</a>";  
        // dump($after);exit;
        $this->assign('next_info',$next_info);
        $this->assign('front_info',$front_info);
        $this->assign('business',$result);
        $this->assign('business_nr',$list_content);
        $this->display();
    }

    //意见反馈
    public function View(){

        $user_info=session("user_info");
        $user_id=$user_info['user_id'];
        $this->assign('userId',$user_id);
        $site_title="意见反馈-赚乐扒";//网站title
        $this->assign('site_title',$site_title); 
        // dump($user_id);exit;
        $this->display();
    }
    //处理意见反馈
    public function ViewControl(){

        $model = M('cq_feedback');

        $user_info=session("user_info");
        $user_id=$user_info['user_id'];
        $time = date('Y-m-d H:i:s',time());
        $description = $_POST['description'];

        $data['DESCRIPTION'] = $description;
        $data['TYPE'] = 181;
        $data['DESCRIBE_USER'] = $user_id;
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
}