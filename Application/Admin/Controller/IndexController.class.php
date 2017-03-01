<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends BaseController {

	public function index(){
		$this->assign('top_li','首页');
		$u_type=session("back_user_type");
		$param=array();
		$option=array();
		if ($u_type!=0) {
			$rightArr=F("roleArr");
			$option['IS_DEL']=$param['IS_DEL']=0;
			$param['LEVEL']=1;
			$param['PID']=0;
			$param['RIGHT_ID']=array("in",$rightArr);
			$option['LEVEL']=2;
			$option['PID']=11;
			$option['RIGHT_ID']=array("in",$rightArr);
		}else{
			$option['IS_DEL']=$param['IS_DEL']=0;
			$param['LEVEL']=1;
			$param['PID']=0;
			$option['LEVEL']=2;
			$option['PID']=11;
		}
		$m=M('lc_right');
		/*头部*/
		$list=$m->field('RIGHT_ID,RIGHT_NAME,ICON_CLS')->where($param)->order('ORDER_NUM ASC')->select();
		$this->assign('header_list',$list);
		/*left*/
		$leftArr=array();
		$result=$m->field('RIGHT_ID,RIGHT_NAME')->where($option)->order('ORDER_NUM ASC')->select();
		foreach ($result as $key => $value) {
			$leftArr[$key]['right_id']=$value['right_id'];
			$leftArr[$key]['right_name']=$value['right_name'];
			$where=array();
			if ($u_type!=0) {
				$where['IS_DEL']=0;
				$where['PID']=$value['right_id'];
				$where['LEVEL']=3;
				$where['RIGHT_ID']=array("in",$rightArr);
			}else{
				$where['IS_DEL']=0;
				$where['PID']=$value['right_id'];
				$where['LEVEL']=3;
			}
			$menu=$m->field('RIGHT_ID,RIGHT_NAME,RIGHT_URL')->where($where)->order('ORDER_NUM ASC')->select();
			$leftArr[$key]['sub_menu']=$menu;
		}
		$this->assign("left_list",$leftArr);
		$this->display();
	}
    public function welcome(){
        
        $this->display();
    }
	/*左侧数据*/
    function left_list(){
    	$mid=$_POST['mid'];
    	$u_type=session("back_user_type");
    	$m=M('lc_right');
    	$where=array();
    	if ($u_type!=0) {
    		$rightArr=F("roleArr");
			$where['IS_DEL']=0;
			$where['PID']=$mid;
			$where['LEVEL']=2;
			$where['RIGHT_ID']=array("in",$rightArr);
		}else{
			$where['IS_DEL']=0;
			$where['PID']=$mid;
			$where['LEVEL']=2;
		}

		$leftArr=array();
		$result=$m->field('RIGHT_ID,RIGHT_NAME')->where($where)->order('ORDER_NUM ASC')->select();
		foreach ($result as $key => $value) {
			$leftArr[$key]['right_id']=$value['right_id'];
			$leftArr[$key]['right_name']=$value['right_name'];
			$where=array();
			if ($u_type!=0) {
				$where['IS_DEL']=0;
				$where['PID']=$value['right_id'];
				$where['LEVEL']=3;
				$where['RIGHT_ID']=array("in",$rightArr);
			}else{
				$where['IS_DEL']=0;
				$where['PID']=$value['right_id'];
				$where['LEVEL']=3;
			}
			$menu=$m->field('RIGHT_ID,RIGHT_NAME,RIGHT_URL')->where($where)->order('ORDER_NUM ASC')->select();
			$leftArr[$key]['sub_menu']=$menu;
		}
		$this->ajaxReturn($leftArr,'JSON');
    }

    
}