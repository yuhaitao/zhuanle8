<?php
namespace Home\Controller;
use Think\Controller;

class HelpController extends BaseController {

    public function index(){
        $site_title="新手指引-赚乐扒";//网站title
        $this->assign('site_title',$site_title);
        $this->display();
    }
    //帮助中心列表
    public function help(){
    	header('Content-Type:text/html;charset=UTF-8');
        $ip = get_client_ip();//IP地址获取
        $user_info=session("user_info");
        $user_id=$user_info['user_id'];//获取用户id
        if ($user_id) {
            $data['USER_ID']=$user_id;
        }
        $data['USER_IP']=$ip;
        $data['USER_LOG']="帮助中心列表";
        $data['LOG_DATE']=date("Y-m-d H:i:s")." ".substr(date("l"), 0,3);
        $data['LOG_MTHOD']="help";
        $data['LOG_URL']="www.zhuanle8.com/doorReg/help.html";
        M('lc_log')->add($data);

    	$m = M('lc_dictionary_small');
    	$where= array();
    	$where['IS_DEL'] = 0;
    	$where['PARENT_ID']  = 41;
    	$result = $m->field('DICSMALL_NO,DICSMALL_NAME')->where($where)->select();
    	
    	// dump($result);
    	$this->assign('helplist',$result);

        $site_title="帮助中心-赚乐扒";//网站title
        $this->assign('site_title',$site_title); 
    	$this->display();
    }
    public function helpList(){

    	$typeId = $_POST['type'];
    	$where = array();
    	$where['TYPE'] = $typeId;
    	$where['IS_DEL'] = 0;

    	$model = M('cq_feedback');
    	$total_num = $model->where($where)->count(); //总记录数

    	$list = $model->field('DESCRIPTION,REPLY')->where($where)->select();
    	foreach ($list as $key => $value) {
    		
    		$response->list[$key]['description'] = $value['description'];
            $response->list[$key]['reply'] = $value['reply'];
    	}
    	$response->total_num=$total_num;

    	$this->ajaxReturn($response,"JSON");
    }
}