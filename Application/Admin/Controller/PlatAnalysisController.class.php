<?php
namespace Admin\Controller;

use Think\Controller;

class PlatAnalysisController extends BaseController
{

    public function index()
    {}
    
    // 查询统计数据
    public function analysisdata()
    {
        $result = M('lc_analysis_data')->field("ID,TOTAL_USER_EARN,BASE_TOTAL_DAYS,TOTAL_INVEST,USER_COUNT")->select();
        $this->assign("analysisData", $result[0]);
        $this->display();
    }

    public function saveAnalysis()
    {
        $total_user_earn = $_POST['total_user_earn'];
        $base_total_days = $_POST['base_total_days'];
        $user_count = $_POST['user_count'];
        
        $total_invest = $_POST['total_invest'];
        $model = M('lc_analysis_data');
        $where['ID'] = 1;
        $data['total_user_earn'] = $total_user_earn;
        $data['base_total_days'] = $base_total_days;
        $data['total_invest'] = $total_invest;
        $data['user_count'] = $user_count;
        $result = $model->where($where)->save($data);
        if ($result) {
            $this->ajaxReturn(1, 'JSON');
        } else {
            $this->ajaxReturn(0, 'JSON');
        }
    }
}