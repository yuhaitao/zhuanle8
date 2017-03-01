<?php
namespace Admin\Controller;
use Think\Controller;
class PlatController extends BaseController {
    public function index(){
    	
    }
    public function plat(){
    	$this->display();
    }
    public function platlist(){
    	$page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        //传值接收
        $plat_shortname=$_POST['plat_shortname'];
        $plat_id=$_POST['plat_id'];
        $company_name=$_POST['company_name'];
        //查询条件
        $where = array();
        $where['a.IS_DEL'] = 0;
        if($plat_shortname){
        	$where['a.PLAT_SHORTNAME']=array("like","%".$plat_shortname."%");
        }
        if($plat_id){
        	$where['a.PLAT_ID']=$plat_id;
        }
        if($company_name){
        	$where['a.COMPANY_NAME']=array("like","%".$company_name."%");
        }
        $m=M('cq_plat');
        /*总记录数*/
        $count = $m->table('cq_plat a')->join('left join cq_back_user b on a.UP_USER=b.BACK_USER_ID')->where($where)->count();
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
        $list=$m->table('cq_plat a')->join('left join cq_back_user b on a.UP_USER=b.BACK_USER_ID')->where($where)->field('a.PLAT_ID,a.PLAT_SHORTNAME,a.COMPANY_NAME,(select COUNT(1) from cq_product c where c.PLAT_SHORTNAME=a.PLAT_ID AND c.IS_DEL=0) as product_count,(select count(1) from cq_product d where d.PLAT_SHORTNAME=a.PLAT_ID AND d.IS_DEL=0 and d.RELEASE_STATUS=0) as product_saling,a.PLAT_LOGO,a.UP_TIME,b.BACK_USER_NAME')->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
        	$responce->rows[$key]['plat_id']=$value['plat_id'];
        	$responce->rows[$key]['plat_shortname']=$value['plat_shortname'];
        	$responce->rows[$key]['company_name']=$value['company_name'];
        	$responce->rows[$key]['product_count']=$value['product_count'];
        	$responce->rows[$key]['product_saling']=$value['product_saling'];
        	$plat_logo=$value['plat_logo'];
        	if($plat_logo){
        		$plat_logo="有";
        	}else{
        		$plat_logo="无";
        	}
        	$responce->rows[$key]['plat_logo']=$plat_logo;
        	$responce->rows[$key]['up_time']=$value['up_time'];
        	$responce->rows[$key]['user_name']=$value['back_user_name'];
        	$responce->rows[$key]['review']="<a href='javascript:top.review_plat(".$value['plat_id'].");'>查看</a>";
        	$responce->rows[$key]['edit']="<a href='javascript:top.edit_plat(".$value['plat_id'].");'>编辑</a>";
        	$responce->rows[$key]['delete']="<a href='javascript:top.delete_plat(".$value['plat_id'].");'>删除</a>";
        	$responce->rows[$key]['product']="<a href='javascript:top.plat_product(".$value['plat_id'].");'>产品</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //查看平台
    public function review_plat(){
    	$plat_id=$_REQUEST['plat_id'];
    	$where['a.PLAT_ID']=$plat_id;
    	$list=M('cq_plat a')->join('left join cq_area b on a.REGION_PROVINCE=b.AREA_ID')->join('left join cq_area c on a.REGION_CITY=c.AREA_ID')->where($where)->field('a.PLAT_SHORTNAME,a.PLAT_TYPE,a.PLAT_LEVEL,a.PLAT_LOGO,a.ONLINE_TIME,a.REGISTER_MONEY,a.COMPANY_NAME,a.COR_REPRESENT,a.COMPANY_ADDRESS,b.AREA_NAME as province,c.AREA_NAME as city,a.PLATFORM_SITE,a.PLAT_USER,a.CHECK_ACCOUNT,a.RE_CAST,a.RECHARGE_COST,a.CASH_COST,a.TRANFER,a.RISK_MONEY,a.FINANCE_DEPOSIT,a.CQ_RED,a.INVEST_GUIDE,a.PLAT_BRIEF')->find();
        $list['invest_guide']=base64_decode($list['invest_guide']);
        $list['plat_brief']=base64_decode($list['plat_brief']);
    	$this->assign("list",$list);
    	$this->display();
    }
    //编辑平台 
    public function edit_plat(){
        $plat_id=$_REQUEST['plat_id'];
        $this->assign("plat_id",$plat_id);
        $where['PLAT_ID']=$plat_id;
        $list=M('cq_plat')->where($where)->field('PLAT_SHORTNAME,PLAT_LOGO,ONLINE_TIME,REGISTER_MONEY,COMPANY_NAME,COR_REPRESENT,COMPANY_ADDRESS,REGION_PROVINCE ,REGION_CITY,PLATFORM_SITE,PLAT_USER,CHECK_ACCOUNT,RE_CAST,RECHARGE_COST,CASH_COST,TRANFER,RISK_MONEY,FINANCE_DEPOSIT,CQ_RED,INVEST_GUIDE,PLAT_BRIEF,PLAT_TYPE,PLAT_LEVEL')->find();
        $list['invest_guide']=base64_decode($list['invest_guide']);
        $list['plat_brief']=base64_decode($list['plat_brief']);
        $province=M('cq_area')->where("PARENT_ID=1 AND IS_DEL=0")->field("AREA_ID,AREA_NAME")->select();
        $where['PARENT_ID']=$list['region_province'];
        $where['IS_DEL']=0;
        $city=M('cq_area')->where($where)->field("AREA_ID,AREA_NAME")->select();
        $this->assign("list",$list);
        $this->assign("province",$province);
        $this->assign("city",$city);
        $this->display();
    }
    //根据省 查询市
    public function itscity(){
        $area_id=$_POST['area_id'];
        $where['PARENT_ID']=$area_id;
        $where['IS_DEL']=0;
        $city=M('cq_area')->where($where)->field("AREA_ID,AREA_NAME")->select();
        $this->ajaxReturn($city,'JSON');
    }
    //保存编辑平台
    public function save_edit_plat(){
        $plat_id=$_POST['plat_id'];
        $where['PLAT_ID']=$plat_id;
        $data['PLAT_SHORTNAME']=$_POST['plat_shortname'];
        $data['ONLINE_TIME']=$_POST['online_time'];
        $data['REGISTER_MONEY']=$_POST['register_money'];
        $data['COMPANY_NAME']=$_POST['company_name'];
        $data['COR_REPRESENT']=$_POST['cor_represent'];
        $data['COMPANY_ADDRESS']=$_POST['company_address'];
        $data['REGION_PROVINCE']=$_POST['region_province'];
        $data['REGION_CITY']=$_POST['region_city'];
        $data['PLATFORM_SITE']=$_POST['platform_site'];
        $data['CHECK_ACCOUNT']=$_POST['check_account'];
        $data['RE_CAST']=$_POST['re_cast'];
        $data['RECHARGE_COST']=$_POST['recharge_cost'];
        $data['CASH_COST']=$_POST['cash_cost'];
        $data['TRANFER']=$_POST['tranfer'];
        $data['RISK_MONEY']=$_POST['risk_money'];
        $data['FINANCE_DEPOSIT']=$_POST['finance_deposit'];
        $data['CQ_RED']=$_POST['cq_red'];
        $data['INVEST_GUIDE']=base64_encode($_POST['invest_guide']);
        $data['PLAT_BRIEF']=base64_encode($_POST['plat_brief']);
        
        $data['PLAT_TYPE']=$_POST['plat_type'];
        $data['PLAT_LEVEL']=$_POST['plat_level'];
        
        $back_user_id=session("back_user_id");
        $data['UP_USER']=$back_user_id;
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        if(!empty($_FILES['plat_logo']['tmp_name'])){
            $upload = new \Think\Upload();// 实例化上传类    
            $upload->maxSize   =     3145728 ;// 设置附件上传大小 3M  
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
            $upload->savePath  =      '/image/'.$back_user_id.'/'; // 设置附件上传目录    
            // 上传单个文件     
            $info   =   $upload->uploadOne($_FILES['plat_logo']);    
            if(!$info) {// 上传错误提示错误信息        
                $this->error($upload->getError());    
            }
            $data['PLAT_LOGO']='/upload'.$info['savepath'].$info['savename'];
        }

        $m=M('cq_plat')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //删除平台
    public function delete_plat(){
        $plat_id=$_POST['plat_id'];
        $where['PLAT_ID']=$plat_id;
        $data['IS_DEL']=1;
        $data['UP_USER']=session("back_user_id");
        $data['UP_TIME']=date("Y-m-d H:i:s",time());
        $m=M('cq_plat')->where($where)->save($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //添加平台
    public function add_plat(){
        $province=M('cq_area')->where("PARENT_ID=1 AND IS_DEL=0")->field("AREA_ID,AREA_NAME")->select();
        $this->assign("province",$province);
        $this->display();
    }
    //保存添加的平台
    public function save_new_plat(){
        $data['PLAT_SHORTNAME']=$_POST['plat_shortname'];
        $data['ONLINE_TIME']=$_POST['online_time'];
        $data['REGISTER_MONEY']=$_POST['register_money'];
        $data['COMPANY_NAME']=$_POST['company_name'];
        $data['COR_REPRESENT']=$_POST['cor_represent'];
        $data['COMPANY_ADDRESS']=$_POST['company_address'];
        $data['REGION_PROVINCE']=$_POST['region_province'];
        $data['REGION_CITY']=$_POST['region_city'];
        $data['PLATFORM_SITE']=$_POST['platform_site'];
        $data['CHECK_ACCOUNT']=$_POST['check_account'];
        $data['RE_CAST']=$_POST['re_cast'];
        $data['RECHARGE_COST']=$_POST['recharge_cost'];
        $data['CASH_COST']=$_POST['cash_cost'];
        $data['TRANFER']=$_POST['tranfer'];
        $data['RISK_MONEY']=$_POST['risk_money'];
        $data['FINANCE_DEPOSIT']=$_POST['finance_deposit'];
        $data['CQ_RED']=$_POST['cq_red'];
        $data['PLAT_TYPE']=$_POST['plat_type'];
        $data['PLAT_LEVEL']=$_POST['plat_level'];
        $data['INVEST_GUIDE']=base64_encode($_POST['invest_guide']);
        $data['PLAT_BRIEF']=base64_encode($_POST['plat_brief']);
        $data['IS_DEL']=0;
        $back_user_id=session("back_user_id");
        $data['ADD_USER']=$back_user_id;
        $data['ADD_TIME']=date("Y-m-d H:i:s",time());
        if(!empty($_FILES['plat_logo']['tmp_name'])){
            $upload = new \Think\Upload();// 实例化上传类    
            $upload->maxSize   =     3145728 ;// 设置附件上传大小 3M  
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型   
            $path = '/image/'.$back_user_id.'/';
            $upload->savePath  = $path; // 设置附件上传目录    
            //$this->ajaxReturn($path,'JSON');
            // 上传单个文件     
            $info   =   $upload->uploadOne($_FILES['plat_logo']);    
            if(!$info) {// 上传错误提示错误信息        
                $this->error($upload->getError());    
            }
            $data['PLAT_LOGO']=$info['savepath'].$info['savename'];
        }

        $m=M('cq_plat')->add($data);
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }
    //是否存在平台名
    public function have_shortname(){
        $where['PLAT_SHORTNAME'] = $_POST['plat_shortname'];
        $where['IS_DEL'] = 0;
        $m=M('cq_plat')->where($where)->find();
        if($m){
            $this->ajaxReturn(1,'JSON');
        }else{
            $this->ajaxReturn(0,'JSON');
        }
    }

    // 平台产品
    public function plat_product(){
        $plat_proID = $_REQUEST['plat_proId'];
        $this->assign('plat_proID',$plat_proID);
        $this->display();
    }
    public function plat_showPro(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        //传值接收

        $plat_showID = $_POST['platpro_id'];
        $where = array();
        $where['PLAT_SHORTNAME'] = $plat_showID;
        $where['IS_DEL'] = 0;

        $model = M('cq_product');
        //总记录数
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
        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数

        $list = $model->field('PRODUCT_ID,PRODUCT_TYPE,TARGET_NAME,INVEST_MONTH,INVEST_DAY')->where($where)->order($sidx.' '.$sord)->limit($start,$limit)->select();
        $pro_type = '';
        foreach ($list as $key => $value) {
            $responce->rows[$key]['product_id'] = $value['product_id'];     //产品ID
            $responce->rows[$key]['target_name']  = $value['target_name'];      //产品名称
            $info = '';
            $pro_type = $value['product_type'];

            if($pro_type == 1){
                $pro_type = '天天返利';
            }
            if($pro_type == 2){
                $pro_type = '限时返利';
            }
            if($pro_type == 3){
                $pro_type = '超级返利';
            }

            if(!empty($value['invest_month'])){
                $info .= $value['invest_month']."个月";
            }
            if(!empty($value['invest_day'])){
                $info .= $value['invest_day']."天";
            }
            $responce->rows[$key]['product_type'] = $pro_type;     //产品类型
            $responce->rows[$key]['invest_time'] = $info;   //投资期限
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //平台产品跳转记录
    public function plat_JumpRecord(){
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序

        //传值接收
        $pro_jumpId= $_POST['record_id'];   //  获取记录ID
        $where = array();
        $where['a.PRODUCT_ID'] = $pro_jumpId;
        $where['b.IS_DEL'] = 0;

        $model = M('cq_product_jump');
         //总记录数
        $count = $model->table('cq_product_jump a')->join('lc_user b ON a.USER_ID = b.USER_ID')->join('cq_product_buy c ON a.PRODUCT_ID = c.PRODUCT_ID AND a.USER_ID = c.USER_ID')->field('b.USER_NAME,b.MOBILE,c.BUY_MONEY,c.BUY_TIME')->where($where)->count();
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


        $list = $model->table('cq_product_jump a')->join('lc_user b ON a.USER_ID = b.USER_ID')->join('cq_product_buy c ON a.PRODUCT_ID = c.PRODUCT_ID AND a.USER_ID = c.USER_ID')->field('b.USER_NAME,b.MOBILE,c.BUY_MONEY,c.BUY_TIME')->where($where)->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['user_name'] = $value['user_name'];     //用户名
            $responce->rows[$key]['mobile'] = $value['mobile'];   //手机号码
            $responce->rows[$key]['buy_money'] = $value['buy_money'];   //购买金额
            $responce->rows[$key]['buy_time'] = $value['buy_time'];   //购买时间
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //跳转操作
    public function jump_opear(){
        $this->display();
    }
    public function jump_opearList(){
        $model = M('cq_plat');
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'a.ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序

        //总记录数
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
        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数

        $list = $model->table('cq_plat a')->join('cq_plat_handle b ON a.PLAT_ID = b.PLAT_ID')->field('a.PLAT_ID,a.PLAT_SHORTNAME,a.COMPANY_NAME,b.UP_TIME')->where('a.IS_DEL = 0')->order($sidx.' '.$sord)->limit($start,$limit)->select();

        foreach ($list as $key => $value) {
            $responce->rows[$key]['plat_shortname'] = $value['plat_shortname'];     //平台名称
            $responce->rows[$key]['company_name'] = $value['company_name'];   //公司名称
            $responce->rows[$key]['up_time'] = $value['up_time'];   //最后修改时间
            $responce->rows[$key]['edit'] = "<a href='javascript:openPlatUpdate(".$value['plat_id'].");'>编辑</a>";   //编辑
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //编辑跳转操作
    public function edit_jumpOpear(){
        $plat_id = $_REQUEST['jump_opearId'];
        $where = array();
        $where['a.IS_DEL'] = 0;
        $where['a.PLAT_ID'] = $plat_id;
        $model = M('cq_plat');
        $list = $model->table('cq_plat a')->join('cq_plat_handle b ON a.PLAT_ID = b.PLAT_ID')->field('a.PLAT_ID,a.PLAT_SHORTNAME,b.HANDLE_TYPE,b.HANDLE_CONTROLLER')->where($where)->find();
        $options = '';
        
        $m = M('lc_dictionary_small');
        $result = $m->field('DICSMALL_NAME,DICSMALL_NO')->where('PARENT_ID = 47')->select();

        foreach ($result as $key => $value) {
            if($value['dicsmall_no'] == $list['handle_type'] ){
                $options.="<option value='".$value['dicsmall_no']."' selected>".$value['dicsmall_name']."</option>";
            }else{
                 $options.="<option value='".$value['dicsmall_no']."'>".$value['dicsmall_name']."</option>";
            }
        }
        $this->assign('options',$options);
        $this->assign('editJump',$list);
        $this->display();
    }

    public function save_jumpOpear(){

        $jump_type = $_POST['jump_type'];
        $jump_links = $_POST['jump_links'];
        $saveId = $_POST['save_id'];

        $user_id=session('back_user_id');
        $time=date("Y-m-d H:i:s",time());

        $model = M('cq_plat_handle');
        $data['HANDLE_TYPE'] = $jump_type;
        $data['HANDLE_CONTROLLER'] = $jump_links;
        $data['UP_USER'] = $user_id;
        $data['UP_TIME'] = $time;
        $where = array('PLAT_ID' => $saveId);

        $result = $model->where($where)->save($data);
        if($result){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }

    //跳转操作
    public function platHandle()
    {
        $this->display();
    }
    //跳转操作==========列表
    public function platHandle_list()
    {
        $page = $_POST['page']; //获取请求的页数   
        $limit = $_POST['rows']; //获取每页显示记录数   
        $sidx = $_POST['sidx']; //获取默认排序字段   
        $sord = $_POST['sord']; //获取排序方式
        if (!$sidx)    $sidx = 'ADD_TIME';
        if(!$sord) $sord = 'desc';//默认倒序
        //传值接收
        $plat_shortname=$_POST['plat_shortname'];
        $plat_id=$_POST['plat_id'];
        $company_name=$_POST['company_name'];
        //查询条件
        $where = array();
        $where['IS_DEL'] = 0;
        if($plat_shortname){
            $where['PLAT_SHORTNAME']=array("like","%".$plat_shortname."%");
        }
        if($plat_id){
            $where['PLAT_ID']=$plat_id;
        }
        if($company_name){
            $where['COMPANY_NAME']=array("like","%".$company_name."%");
        }
        $m=M('cq_plat');
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
        $responce->page = intval($page); //当前页   
        $responce->total = $total_pages; //总页数 
        $responce->records = $count; //总记录数
        $list=$m->where($where)->field("PLAT_ID,PLAT_SHORTNAME,COMPANY_NAME,ADD_TIME,UP_TIME")->order($sidx.' '.$sord)->limit($start,$limit)->select();
        foreach ($list as $key => $value) {
            $responce->rows[$key]['plat_shortname']=$value['plat_shortname'];
            $responce->rows[$key]['company_name']=$value['company_name'];
            if (!$value['up_time']) {
                $value['up_time']=$value['add_time'];
            }
            $responce->rows[$key]['up_time']=$value['up_time'];
            $responce->rows[$key]['edit']="<a href='javascript:editPlatHandle(".$value['plat_id'].",\"".$value['plat_shortname']."\");'>编辑</a>";
        }
        $this->ajaxReturn($responce,'JSON');
    }
    //编辑
    public function editPlatHandle()
    {
        $plat_id=$_REQUEST['plat_id'];
        $plat_name=$_REQUEST['plat_name'];
        $this->assign("plat_id",$plat_id);
        $this->assign("plat_name",$plat_name);
        $result=M("cq_plat_handle")->where("PLAT_ID=$plat_id")->field("HANDLE_CONTROLLER")->find();
        if ($result) {
            $this->assign("handle_controller",$result['handle_controller']);
        }else{
            $data['PLAT_ID']=$plat_id;
            $data['ADD_USER']=session('back_user_id');
            $data['ADD_TIME']=date("Y-m-d H:i:s",time());
            M("cq_plat_handle")->add($data);
            $this->assign("handle_controller","");
        }
        $this->display();
    }
    //保存
    public function savePlatHandle()
    {
        $plat_id=$_POST['plat_id'];
        $user = session('back_user_id');
        $time = date("Y-m-d H:i:s",time());

        $data['HANDLE_TYPE']=$_POST['handle_type'];
        $data['HANDLE_CONTROLLER']=$_POST['handle_controller'];
        $data['UP_USER']=$user;
        $data['UP_TIME']=$time;
        $data['IS_DEL']=0;

        $where['PLAT_ID']=$plat_id;
        $m = M("cq_plat_handle")->where($where)->save($data);
        if($m){
            $this->ajaxReturn('1','JSON');
        }else{
            $this->ajaxReturn('0','JSON');
        }
    }
}
