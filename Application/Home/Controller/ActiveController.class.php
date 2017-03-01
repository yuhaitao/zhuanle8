<?php
namespace Home\Controller;
use Think\Controller;

class ActiveController extends BaseController {
    public function index(){
        $this->display();
    }
    
    //活动中心列表
    public function activeCenter(){
        $ip = get_client_ip();//IP地址获取
        $user_info=session("user_info");
        $user_id=$user_info['user_id'];//获取用户id
        if ($user_id) {
            $data['USER_ID']=$user_id;
        }
        $data['USER_IP']=$ip;
        $data['USER_LOG']="活动中心";
        $data['LOG_DATE']=date("Y-m-d H:i:s")." ".substr(date("l"), 0,3);
        $data['LOG_MTHOD']="activeCenter";
        $data['LOG_URL']="www.zhuanle8.com/activecenter.html";
        $site_title="活动专区";//网站title
        $this->assign('site_title',$site_title); 
        M('lc_log')->add($data);
        $this->display();
    }
    public function activeList(){

        $more=$_POST['more'];
        $upId = $_POST['upId'];

        $model = M('cq_active');
        $where = array();
        $where['IS_DEL'] = 0;

        $limit=4*($more+1);

        if($upId == 1 || $upId == 2){
            $where['ACTIVE_ENABLED'] = $upId;
        }
        $list = $model->field('ACTIVE_PIC,ACTIVE_NAME,ACTIVE_ENABLED,START_DATE,END_DATE,ACTIVE_URL')->where($where)->order('ACTIVE_ID desc')->limit($limit)->select();
            foreach ($list as $key => $value) {

               if($value['active_enabled'] == 1){
                 $response->rows[$key]['active_enabled'] = "<img src='".__ROOT__."/Public/images/home/icon/activeing.png' />";
                 $response->rows[$key]['active_jump'] = "<a href='".$value['active_url']."' target='_blank' class='actdetBtn'>查看活动页面</a>";
               }else{
                 $response->rows[$key]['active_enabled'] = "<img src='".__ROOT__."/Public/images/home/icon/activeend.png' />";
                 $response->rows[$key]['active_jump'] = "<a href='javascript:void;' target='_blank' class='actdetBtn2'>活动结束</a>";
               }
               $response->rows[$key]['start_date'] = date("Y-m-d", strtotime($value['start_date']));
               $response->rows[$key]['end_date'] = date("Y-m-d", strtotime($value['end_date']));
               $response->rows[$key]['active_pic'] = $value['active_pic'];
               $response->rows[$key]['active_name'] = $value['active_name'];

            }
            // $this->ajaxReturn($model->getLastSql(),"JSON");
            $this->ajaxReturn($response,'JSON');
        
    }
    public function activeupdate(){

           
        
    }
}