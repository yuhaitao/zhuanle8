<?php
namespace Home\Controller;
use Think\Controller;

class BaseController extends Controller {

    public $session_id;
    /*
     * 初始化操作
     */
	
    public function _initialize() {
        $this->session_id = session_id(); // 当前的 session_id
        $user = session('user');
        if($user == '' || $user == null){
            
        }

        $model = M('lc_website');
        $list = $model->field('SITE_KEY,SITE_DESCRIPTION,WEBSITE_NUMBER,TELEPHONR,WEBSITE_DESCRIPTION,WORKING_TIME,WEBSITE_NAME,WEBSITE_QQ')->where('IS_DEL = 0')->find();

        $cur_user = session('user_info');

        if($cur_user){
            if($cur_user['nick_name'] == '' || $cur_user['nick_name'] == NULL){
                $cur_user['nick_name'] = $cur_user['mobile'];
            }
            if($cur_user['user_photo'] == '' || $cur_user['user_photo'] == NULL){
                $cur_user['user_photo'] = __ROOT__.'/Public/images/home/icon/user_head_o.png';
            }else{
                $cur_user['user_photo'] = __ROOT__.$cur_user['user_photo'];
            }
        }else{
                $cur_user['user_photo'] = __ROOT__.'/Public/images/home/icon/user_head.png';
                $cur_user['mobile'] = 0;
        }
      
        $this->assign('cur_user',$cur_user);
        $this->assign('website',$list);
        $this->webInfo();
    }

    public function webInfo(){
        $model = M('lc_website');
        $result = $model->field('WEBSITE_NUMBER,SITE_DESCRIPTION,SITE_KEY,WEBSITE_LOGO')->where("WEBSITE_ID = 1")->find();
        
        $this->assign('webInfo',$result);
        $this->assign('site_title',$result['website_number']);
		$result1 = M('lc_adsite')->field('AD_CONTENT,AD_ID')->where("AD_ID = 1")->find();
		$result2 = M('lc_adsite')->field('AD_CONTENT,AD_ID')->where("AD_ID = 2")->find();
		$result3 = M('lc_adsite')->field('AD_CONTENT,AD_ID')->where("AD_ID = 3")->find();
		
		$this->assign('home_ad',$result1['ad_content']);
		$this->assign('list_ad',$result2['ad_content']);
		$this->assign('news_ad',$result3['ad_content']);
    }
}