<?php

namespace Admin\Controller;

use Think\Controller;

class ProductController extends BaseController {

    public function index(){

    	

    }

    public function product(){

    	$this->display();

    }

    //产品编辑--天天返利

    public function dayProduct()

    {

        $this->display();

    }

    //产品编辑--限时返利

    public function limitProduct()

    {

        $this->display();

    }

    //产品满标--天天返利

    public function dayProduct_full(){

    	$this->display();

    }

    //产品满标--限时返利

    public function limitProduct_full(){

        $this->display();

    }

    //产品列表

    public function product_list(){

    	$page = $_POST['page']; //获取请求的页数   

        $limit = $_POST['rows']; //获取每页显示记录数   

        $sidx = $_POST['sidx']; //获取默认排序字段   

        $sord = $_POST['sord']; //获取排序方式

        if (!$sidx)    $sidx = 1;

        //if(!$sord) $sord = 'desc';//默认倒序

        //传值接收

        $product_type=$_POST['product_type'];

        $release_status=$_POST['release_status'];

        $target_name=$_POST['target_name'];

        $product_id=$_POST['product_id'];

        //查询条件

        $where = array();

        $where['a.IS_DEL'] = 0;

        $where['a.PRODUCT_TYPE']=$product_type;

        if( $release_status == 'shelve'){

            $where['a.RELEASE_STATUS']=array('in','1,6');

        }else{

            $where['a.RELEASE_STATUS']=$release_status;

        }

        if($target_name){

        	$where['a.TARGET_NAME']=array("like","%".$target_name."%");

        }

        if($product_id){

        	$where['a.PRODUCT_ID']=$product_id;

        }

        

        $m=M('cq_product');

        /*总记录数*/

        $count = $m->table('cq_product a')->join('left join cq_plat b on a.PLAT_SHORTNAME=b.PLAT_ID')->join('left join cq_back_user c on a.UP_USER=c.BACK_USER_ID')->where($where)->count();

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

        $responce->page = intval($page); //当前页   

        $responce->total = $total_pages; //总页数 

        $responce->records = $count; //总记录数

        $list = $m->table('cq_product a')->join('left join cq_plat b on a.PLAT_SHORTNAME=b.PLAT_ID')->join('left join cq_back_user c on a.UP_USER=c.BACK_USER_ID')->where($where)->field('a.PRODUCT_ID,a.TARGET_NAME,a.INVEST_MONTH,a.INVEST_DAY,b.PLAT_SHORTNAME,a.ONLINE_TIME,a.DOWN_TIME,c.BACK_USER_NAME,a.RELEASE_STATUS')->order($sidx.' '.$sord)->limit($start,$limit)->select();

        foreach ($list as $key => $value) {

        	$responce->rows[$key]['product_id']=$value['product_id'];

        	$responce->rows[$key]['target_name']=$value['target_name'];

        	$long="";

        	if($value['invest_month'] > 0){

        		$long=$value['invest_month']."月";

        	}elseif ($value['invest_day'] > 0) {

        		$long=$value['invest_day']."天";

        	}

        	$responce->rows[$key]['period']=$long;

        	$responce->rows[$key]['plat_shortname']=$value['plat_shortname'];

        	$responce->rows[$key]['online_time']=$value['online_time'];

        	$responce->rows[$key]['down_time']=$value['down_time'];

        	$responce->rows[$key]['back_user_name']=$value['back_user_name'];

        	$release_status="";

        	if ($value['release_status']==0) {

        		$release_status="发布中";

        	}elseif ($value['release_status']==1) {

        		$release_status="待发布";

        	}elseif ($value['release_status']==2) {

        		$release_status="下架";

        	}elseif ($value['release_status']==3) {

        		$release_status="满标";

        	}elseif ($value['release_status']==4) {

        		$release_status="待提交";

        	}elseif ($value['release_status']==5) {

        		$release_status="提交失败";

        	}elseif ($value['release_status']==6) {

        		$release_status="发布撤回";

        	}elseif ($value['release_status']==7) {

        		$release_status="产品审核";

        	}elseif ($value['release_status']==8) {

        		$release_status="审核失败";

        	}elseif ($value['release_status']==9) {

        		$release_status="流标";

        	}

        	$responce->rows[$key]['release_status']=$release_status;

        	$responce->rows[$key]['delete']="<a href='javascript:deleteProduct(".$value['product_id'].");'>删除</a>";

        	$responce->rows[$key]['review']="<a href='javascript:top.checkThis(".$value['product_id'].");'>查看</a>";

        	$responce->rows[$key]['jump_list']="<a href='javascript:top.productJump(".$value['product_id'].");'>跳转列表</a>";

        	$responce->rows[$key]['buy_list']="<a href='javascript:top.productBuy(".$value['product_id'].");'>购买清单</a>";

            $responce->rows[$key]['edit']="<a href='javascript:top.productEdit(".$value['product_id'].",".$product_type.");'>编辑</a>";

            $responce->rows[$key]['sendmsg']="<a href='javascript:;'>发送短信</a>";

        }

        $this->ajaxReturn($responce,'JSON');

    }

    //删除产品

    public function delete_product(){

    	$product_id=$_POST['product_id'];

    	$where['PRODUCT_ID']=$product_id;

        $data['IS_DEL']=1;

        $data['UP_USER']=session("back_user_id");

        $data['UP_TIME']=date("Y-m-d H:i:s",time());

        $m=M('cq_product')->where($where)->save($data);

        if($m){

            $this->ajaxReturn(1,'JSON');

        }else{

            $this->ajaxReturn(0,'JSON');

        }

    }

    //跳转列表

	public function jump_list(){

		$page = $_POST['page']; //获取请求的页数   

        $limit = $_POST['rows']; //获取每页显示记录数   

        $sidx = $_POST['sidx']; //获取默认排序字段   

        $sord = $_POST['sord']; //获取排序方式

        if (!$sidx)    $sidx = "a.JUMP_TIME";

        //if(!$sord) $sord = 'desc';//默认倒序

        //传值接收

        $product_id=$_POST['product_id'];

        //查询条件

        $where = array();

        $where['a.IS_DEL'] = 0;

        $where['a.PRODUCT_ID']=$product_id;

        $where['a.USER_ID'] = array("gt",0);

        $m=M('cq_product_jump');

        /*总记录数*/

        $count = $m->table('cq_product_jump a')->join('lc_user b on a.USER_ID=b.USER_ID')->where($where)->count();

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

        $responce->page = intval($page); //当前页   

        $responce->total = $total_pages; //总页数 

        $responce->records = $count; //总记录数

        $list = $m->table('cq_product_jump a')->join('lc_user b on a.USER_ID=b.USER_ID')->where($where)->field('b.MOBILE,b.USER_NAME,a.JUMP_TIME')->order($sidx.' '.$sord)->limit($start,$limit)->select();

        foreach ($list as $key => $value) {

        	$responce->rows[$key]['mobile']=$value['mobile'];

        	$responce->rows[$key]['user_name']=$value['user_name'];

        	$responce->rows[$key]['jump_time']=$value['jump_time'];

        }

        $this->ajaxReturn($responce,'JSON');

	}

    //购买清单

    public function buy_list(){

        $page = $_POST['page']; //获取请求的页数   

        $limit = $_POST['rows']; //获取每页显示记录数   

        $sidx = $_POST['sidx']; //获取默认排序字段   

        $sord = $_POST['sord']; //获取排序方式

        if (!$sidx)    $sidx = "a.BUY_TIME";

        //if(!$sord) $sord = 'desc';//默认倒序

        //传值接收

        $product_id=$_POST['product_id'];

        //查询条件

        $where = array();

        $where['a.IS_DEL'] = 0;

        $where['a.PRODUCT_ID']=$product_id;

        $where['a.USER_ID'] = array("gt",0);

        $m=M('cq_product_buy');

        /*总记录数*/

        $count = $m->table('cq_product_buy a')->join('lc_user b on a.USER_ID=b.USER_ID')->where($where)->count();

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

        $responce->page = intval($page); //当前页   

        $responce->total = $total_pages; //总页数 

        $responce->records = $count; //总记录数

        $list = $m->table('cq_product_buy a')->join('lc_user b on a.USER_ID=b.USER_ID')->where($where)->field('b.MOBILE,b.USER_NAME,a.BUY_MONEY,a.BUY_TIME,a.HANDLE_STATUS')->order($sidx.' '.$sord)->limit($start,$limit)->select();

        foreach ($list as $key => $value) {

            $responce->rows[$key]['mobile']=$value['mobile'];

            $responce->rows[$key]['user_name']=$value['user_name'];

            $responce->rows[$key]['buy_money']=$value['buy_money'];

            $responce->rows[$key]['buy_time']=$value['buy_time'];

            $handle_status=$value['handle_status'];

            if($handle_status==1){

                $handle_status="购买成功";

            }elseif ($handle_status==2) {

                $handle_status="暂未处理";

            }

            $responce->rows[$key]['handle_status']=$handle_status;

        }

        $this->ajaxReturn($responce,'JSON');

    }

    //添加产品

    public function add_product()

    {

        $product_type=$_REQUEST['product_type'];

        $this->assign("product_type",$product_type);

        $platlist=M('cq_plat')->field('PLAT_ID,PLAT_SHORTNAME')->where('IS_DEL=0')->select();

        $this->assign("platlist",$platlist);

        $this->display();

    }

    //判断是否存在 该标名

    public function have_target_name()

    {

        $target_name=$_POST['target_name'];

        $where['TARGET_NAME']=$target_name;

        $where['IS_DEL']=0;

        $result=M('cq_product')->where($where)->find();

        if($result){

            $this->ajaxReturn(1,'JSON');

        }else{

            $this->ajaxReturn(0,'JSON');

        }

    }

    //保存添加的新的产品

    public function save_new_product()

    {

        $data['PLAT_SHORTNAME']=$_POST['plat_shortname'];//平台简称：

        $data['JUMP_LINK']=$_POST['jump_link'];//跳转链接：

        $data['TARGET_NAME']=$_POST['target_name'];//标的名称：

        $data['ANNUAL_INCOME_RATE']=$_POST['annual_income_rate'];//标的年化：

        $data['INVEST_MONTH']=$_POST['investMonth'];//投标期限：

        $data['INVEST_DAY']=$_POST['investDay'];//

        $data['START_INVEST_AMOUNT']=$_POST['start_invest_amount'];//起投金额：

        $data['END_INVEST_AMOUNT']=$_POST['end_invest_amount'];//限投金额：

        $data['PRODUCT_SUM']=$_POST['product_sum'];//标的总额：

        $data['CQ_REBATE_RATE']=$_POST['cq_rebate_rate'];

        $data['CQ_RATE']=$_POST['cq_rate'];//赚乐扒加息：

        $data['UNIT_RATE']=$_POST['unit_rate'];//综合年化：

        $data['CQ_RED']=$_POST['cq_red'];//赚乐扒红包：

        $data['RED_INFO']=$_POST['red_info'];//红包介绍：

        if ($_POST['online_time']) {

            $data['ONLINE_TIME']=$_POST['online_time'];//上线时间：

        }

        if ($_POST['down_time']) {

            $data['DOWN_TIME']=$_POST['down_time'];//下线时间：

        }

        $data['THAWING_METHOD']=$_POST['thawing_method'];//解冻方式：

        $data['FLOW_REBATE']=$_POST['flow_rebate'];//流标返利：

        $data['RE_CAST']=$_POST['re_cast'];//复投方式：

        $data['BID_SECURITY_TYPE']=$_POST['bid_security_type'];//投资保障：

        $data['BID_SECURITY_OTHER']=$_POST['bid_security_other'];//投资保障：其他

        $data['INTEREST_TYPE']=$_POST['interest_type'];//计息方式：

        $data['INTEREST_OTHER']=$_POST['interest_other'];//计息方式：其他

        $data['REBATE_TYPE']=$_POST['rebate_type'];//还款方式：

        $data['REBATE_OTHER']=$_POST['rebate_other'];//还款方式：

        $user_id=session('back_user_id');//添加人

        $time=date("Y-m-d H:i:s",time());//添加时间

        $data['ADD_USER']=$user_id;

        $data['ADD_TIME']=$time;

        $data['PRODUCT_TYPE']=$_POST['product_type'];

        $data['RELEASE_STATUS']=1;

        $data['IS_DEL']=0;

        $m=M('cq_product')->add($data);

        if ($m) {

            $data_a['RELEASE_USER']=$user_id;

            $data_a['RELEASE_TIME']=$time;

            $data_a['PRODUCT_ID']=$m;

            $data_a['RELEASE_STATUS']=1;

            M('cq_product_release')->add($data_a);

            $this->ajaxReturn(1,'JSON');

        }else{

            $this->ajaxReturn(0,'JSON');

        }

        

    }

    //产品编辑

    public function product_edit()

    {

        $product_id=$_REQUEST['product_id'];

        $this->assign("product_id",$product_id);

        $product_type=$_REQUEST['product_type'];
        // dump($product_type);exit;
        $this->assign("product_type",$product_type);

        $platlist=M('cq_plat')->field('PLAT_ID,PLAT_SHORTNAME')->where('IS_DEL=0')->select();

        $this->assign("platlist",$platlist);

        $where['PRODUCT_ID']=$product_id;

        $product=M('cq_product')->where($where)->field("PLAT_SHORTNAME,JUMP_LINK,TARGET_NAME,ANNUAL_INCOME_RATE,INVEST_MONTH,INVEST_DAY,START_INVEST_AMOUNT,END_INVEST_AMOUNT,PRODUCT_SUM,CQ_REBATE_RATE,CQ_RATE,UNIT_RATE,CQ_RED,RED_INFO,ONLINE_TIME,DOWN_TIME,THAWING_METHOD,FLOW_REBATE,RE_CAST,BID_SECURITY_TYPE,BID_SECURITY_OTHER,REBATE_TYPE,REBATE_OTHER,INTEREST_TYPE,INTEREST_OTHER")->find();

        $this->assign("product",$product);

        $this->display();

    }

    //保存产品编辑

    public function save_edit_product()

    {

        $where['PRODUCT_ID']=$_POST['product_id'];

        $data['PLAT_SHORTNAME']=$_POST['plat_shortname'];//平台简称：

        $data['JUMP_LINK']=$_POST['jump_link'];//跳转链接：

        $data['TARGET_NAME']=$_POST['target_name'];//标的名称：

        $data['ANNUAL_INCOME_RATE']=$_POST['annual_income_rate'];//标的年化：

        $data['INVEST_MONTH']=$_POST['investMonth'];//投标期限：

        $data['INVEST_DAY']=$_POST['investDay'];//

        $data['START_INVEST_AMOUNT']=$_POST['start_invest_amount'];//起投金额：

        $data['END_INVEST_AMOUNT']=$_POST['end_invest_amount'];//限投金额：

        $data['PRODUCT_SUM']=$_POST['product_sum'];//标的总额：

        $data['CQ_RATE']=$_POST['cq_rate'];//赚乐扒加息：

        $data['UNIT_RATE']=$_POST['unit_rate'];//综合年化：

        $data['CQ_REBATE_RATE']=$_POST['cq_rebate_rate'];

        $data['CQ_RED']=$_POST['cq_red'];//赚乐扒红包：

        $data['RED_INFO']=$_POST['red_info'];//红包介绍：

        if ($_POST['online_time']) {

            $data['ONLINE_TIME']=$_POST['online_time'];//上线时间：

        }

        if ($_POST['down_time']) {

            $data['DOWN_TIME']=$_POST['down_time'];//下线时间：

        }

        $data['THAWING_METHOD']=$_POST['thawing_method'];//解冻方式：

        $data['FLOW_REBATE']=$_POST['flow_rebate'];//流标返利：

        $data['RE_CAST']=$_POST['re_cast'];//复投方式：

        $data['BID_SECURITY_TYPE']=$_POST['bid_security_type'];//投资保障：

        $data['BID_SECURITY_OTHER']=$_POST['bid_security_other'];//投资保障：其他

        $data['INTEREST_TYPE']=$_POST['interest_type'];//计息方式：

        $data['INTEREST_OTHER']=$_POST['interest_other'];//计息方式：其他

        $data['REBATE_TYPE']=$_POST['rebate_type'];//还款方式：

        $data['REBATE_OTHER']=$_POST['rebate_other'];//还款方式：

        $user_id=session('back_user_id');//添加人

        $time=date("Y-m-d H:i:s",time());//添加时间

        $data['UP_USER']=$user_id;

        $data['UP_TIME']=$time;

        $data['PRODUCT_TYPE']=$_POST['product_type'];

        //$data['RELEASE_STATUS']=1;

        $data['IS_DEL']=0;

        $m=M('cq_product')->where($where)->save($data);

        if ($m) {

            $this->ajaxReturn(1,'JSON');

        }else{

            $this->ajaxReturn(0,'JSON');

        }

    }

    //发布

    public function saveRelease()

    {

        $idlist=$_POST['idlist'];

        $idlist=substr($idlist, 0,-1);

        $where['PRODUCT_ID']=array("in",$idlist);

        $data['RELEASE_STATUS']=0;

        $m=M('cq_product')->where($where)->save($data);

        if ($m) {

            $this->ajaxReturn(1,'JSON');

        }else{

            $this->ajaxReturn(0,'JSON');

        }

    }

    //下架编辑

    public function stateShelve(){

        $shelveId = $_REQUEST['idlist'];

        $this->assign('shelveId',$shelveId);

        $this->display();

    }

    public function saveShelve(){

        $idlist = $_POST['shelve_id'];

        $typeid = $_POST['releaseStatus'];

        $remark = $_POST['shelvesRemarks'];

        $time = date('Y-m-d:H:i:s',time());

        $userId = session('back_user_id');







        $model= M('cq_product_release');



        $data['RELEASE_USER'] = $userId;

        $data['RELEASE_TIME'] = $time;

        $data['SHELVES_REMARKS'] = $remark;

        $data['PRODUCT_ID'] = $idlist;

        $data['RELEASE_STATUS'] = $typeid;



        $result = $model->add($data);



        $m = M('cq_product');

        $where = array();

        $where['IS_DEL'] = 0;

        $where['PRODUCT_ID'] = $idlist;

        $info['RELEASE_STATUS'] = $typeid;



        $backInfo = $m->where($where)->save($info);

         if ($backInfo) {

            $this->ajaxReturn(1,'JSON');

        }else{

            $this->ajaxReturn(0,'JSON');

        }

    }

    //下架

    // public function saveShelve()

    // {

    //     $idlist=$_POST['idlist'];

    //     $idlist=substr($idlist, 0,-1);

    //     $where['PRODUCT_ID']=array("in",$idlist);

    //     $data['RELEASE_STATUS']=2;

    //     $m=M('cq_product')->where($where)->save($data);

    //     if ($m) {

    //         $this->ajaxReturn(1,'JSON');

    //     }else{

    //         $this->ajaxReturn(0,'JSON');

    //     }

    // }

    //

    public function toHomepage()

    {

        $product_type=$_REQUEST['product_type'];

        $this->assign("product_type",$product_type);

        $idlist=$_REQUEST['idlist'];

        if($idlist){

            $idlist=substr($idlist, 0,-1);

            $idarr=explode(",", $idlist);

            $where['PRODUCT_TYPE']=$product_type;

            $where['IS_DEL']=0;

            $num=M('cq_product_homepage')->where($where)->field('NUMBER')->order("NUMBER desc")->limit("0,1")->select();

            $number=$num[0]['number'];

            $datalist=array();

            for ($i=0; $i < count($idarr); $i++) {

                $data['PRODUCT_ID']=$idarr[$i];

                $data['NUMBER']=$number+$i+1;

                $data['IS_DEL']=0;

                $data['PRODUCT_TYPE']=$product_type;

                $datalist[]=$data;

            }

            M('cq_product_homepage')->addAll($datalist);

        }

        $this->display();

    }

    //

    public function homepagelist($value='')

    {

        $page = $_POST['page']; //获取请求的页数   

        $limit = $_POST['rows']; //获取每页显示记录数   

        $sidx = $_POST['sidx']; //获取默认排序字段   

        $sord = $_POST['sord']; //获取排序方式

        if (!$sidx)    $sidx = "a.ADD_TIME";

        //if(!$sord) $sord = 'desc';//默认倒序

        //传值接收

        $product_type=$_POST['product_type'];

        //查询条件

        $where = array();

        $where['a.IS_DEL'] = 0;

        $where['b.RELEASE_STATUS']=0;

        $where['a.PRODUCT_TYPE']=$product_type;

        $m=M('cq_product_homepage');

        /*总记录数*/

        $count = $m->table('cq_product_homepage a')->join('cq_product b on a.PRODUCT_ID=b.PRODUCT_ID')->where($where)->count();

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

        $responce->page = intval($page); //当前页   

        $responce->total = $total_pages; //总页数 

        $responce->records = $count; //总记录数

        $list = $m->table('cq_product_homepage a')->join('cq_product b on a.PRODUCT_ID=b.PRODUCT_ID')->where($where)->field('a.HOMEPAGE_ID,b.TARGET_NAME,a.NUMBER')->order($sidx.' '.$sord)->limit($start,$limit)->select();

        foreach ($list as $key => $value) {

            $responce->rows[$key]['target_name']=$value['target_name'];

            $responce->rows[$key]['number']=$value['number'];

            $responce->rows[$key]['delete']="<a href='javascript:deleteHomepage(".$value['homepage_id'].");'>删除</a>";

        }

        $this->ajaxReturn($responce,'JSON');

    }

    //主页展示 =========删除

    public function delHomepage()

    {

        $where['HOMEPAGE_ID']=$_POST['homepage_id'];

        $data['UP_USER']=session("back_user_id");

        $data['UP_TIME']=date("Y-m-d H:i:s",time());

        $data['IS_DEL']=1;

        $m=M('cq_product_homepage')->where($where)->save($data);

        if($m){

            $this->ajaxReturn(1,'JSON');

        }else{

            $this->ajaxReturn(0,'JSON');

        }

    }

    //检测主页展示

    public function checkHomepage()

    {

        $idlist=$_POST['idlist'];

        $idlist=substr($idlist, 0,-1);

        $where['a.PRODUCT_ID']=array("in",$idlist);

        $where['a.IS_DEL']=0;

        $list=M('cq_product_homepage a')->join("cq_product b on a.PRODUCT_ID=b.PRODUCT_ID")->field("b.TARGET_NAME")->where($where)->select();

        $result="";

        foreach ($list as $key => $value) {

            $result.=$value['target_name'].",";

        }

        if ($result) {

            $this->ajaxReturn($result);

        }else{

            $this->ajaxReturn(1,'JSON');

        }

    }

    //限时抢购
    public function flashSale()
    {
        $this->display();
    }
}