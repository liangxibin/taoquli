<?php
/**
 * ============================================================================
 * 版权所有 多多科技，保留所有权利。
 * 网站地址: http://soft.duoduo123.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/

if(!defined('DDROOT')){
	exit('Access Denied');
}

function _dd_string(&$arr){ //字符格式化
	foreach ($arr as $key => $value) {
		if (is_array($value)) {
			dd_string($arr[$key]);
		} else {
			$value = trim($value);
			$arr[$key] = (string)$value;
		}
	}
}

include(DDROOT.'/plugin/dd_chaofan/app.class.php');

$do=$_GET['do']?$_GET['do']:'my';
$page=(int)$_GET['page'];
$page=$page==0?1:$page;
$page_size=10;

if($do!='login' && $do!='register'){
	$uid = (int)($_GET['uid']);
	$ddpassword = $_GET['ddpassword'];
	$dduser = $duoduo->select('user','*','id="'.$uid.'" and ddpassword="'.$ddpassword.'"');
	$uid = $dduser['id'];
	if($uid==0){
		echo dd_json_encode(array('s'=>0,'r'=>'数据错误'));dd_exit();
	}
}


if($do=='my'){
	$a = $app_class->return_user_info($dduser);
	$user_webset['TBMONEY'] = TBMONEY;
	$user_webset['TBMONEYUNIT'] = TBMONEYUNIT;
	$user_webset['tx_limit_jifenbao'] = $webset['tixian']['tblimit'];
	$user_webset['txxz_jifenbao']=$webset['tixian']['tbtxxz'];
	$user_webset['tx_limit_money']=$webset['tixian']['limit'];
	$user_webset['txxz_money']=$webset['tixian']['txxz'];
	$json_data=array ("s" => 1,"r" => array('user'=>$a,'webset'=>$user_webset));
}elseif($do=='taobao'){
	$order=$duoduo->select_all('tradelist','*',"uid='".$uid."' order by id desc limit ".($page-1)*$page_size.','.$page_size);
	foreach($order as $k=>$r){
		$r['trade_id']=preg_replace('/_\d+/','',$r['trade_id']);
		$r['jifenbao']=(float)$r['jifenbao'];
		$order[$k]['title']=$r['item_title'];
		$order[$k]['subTitle']=$r['jifenbao'].TBMONEY.'['.($r['checked']==2?'已结算':'未结算').']';
		$order[$k]['des']='<p>'.$r['item_title'].'</p><p>'.$order[$k]['subTitle'].'</p><p>下单时间：'.$r['create_time'].'</p><p>订单号：'.$r['trade_id'].'</p>';
	}
	$json_data=array('s'=>1,'r'=>$order,'size'=>$page_size);
}elseif($do=='mall'){
	$order=$duoduo->select_all('mall_order','*',"uid='".$uid."' order by id desc limit ".($page-1)*$page_size.','.$page_size);
	foreach($order as $k=>$r){
		$r["order_code"]=preg_replace('/_\d+/','',$r["order_code"]);
		$order[$k]['title']='['.$r['mall_name'].'] '.$r['order_code'];
		$order[$k]['subTitle']=$r['fxje'].'元 ['.($r['status']==1?'已确认':'未确认').']';
		$order[$k]['des']='<p>商城:'.$r['mall_name'].'</p><p>'.$order[$k]['subTitle'].'</p><p>下单时间：'.date('Y-m-d H:i:s',$r['order_time']).'</p><p>订单号：'.$r['order_code'].'</p>';
	}
	$json_data=array('s'=>1,'r'=>$order,'size'=>$page_size);
}elseif($do=='paipai'){
	$order=$duoduo->select_all('paipai_order','id, commName, fxje, addtime',"uid='".$uid."' order by id desc limit ".($page-1)*$page_size.','.$page_size);
	foreach($order as $k=>$r){
		$order[$k]['addtime']=date('Y-m-d H:i:s',$r['addtime']);
	}
	$json_data=array('s'=>1,'r'=>$order,'size'=>$page_size);
}elseif($do=='mingxi_in'){
	$mingxi=$duoduo->select_all('mingxi','*',"uid='".$uid."' and (jifen>0 or money>0 or jifenbao>0) order by id desc limit ".($page-1)*$page_size.','.$page_size);
	$mingxi_tpl=include(DDROOT.'/data/mingxi.php'); //明细结构数据
	foreach($mingxi as $k=>$r){
		$m[$k]['title']=$mingxi_tpl[$r["shijian"]]['title'];
		$m[$k]['content']=mingxi_content($r,$mingxi_tpl[$r["shijian"]]['content']);
		$m[$k]['addtime']=$r['addtime'];
	}
	$json_data=array('s'=>1,'r'=>$m,'size'=>$page_size);
}elseif($do=='mingxi_out'){
	$mingxi=$duoduo->select_all('tixian','*',"uid='".$uid."' order by id desc limit ".($page-1)*$page_size.','.$page_size);
	$tixian_arr=array(0=>'<span style="color:#ff3300">提现待审核</span>',1=>'<span style="color:#009900">提现成功</span>',2=>'<span style="color:#333333">提现失败</span>');
	foreach($mingxi as $k=>$r){
		$m[$k]['status']=$tixian_arr[$r["status"]];
		$m[$k]['addtime']=date('Y-m-d H:i:s',$r['addtime']);
		if($r['money']==0){
			$r['money']=$r['money2'];
		}
		if($r['type']==1){
			$m[$k]['title']='提现'.TBMONEY.'：'.(float)$r['money'].TBMONEYUNIT;
			$m[$k]['subTitle']='['.$m[$k]['status'].']';
			$m[$k]['des']='<p>提现'.TBMONEY.'：'.(float)$r['money'].TBMONEYUNIT.'</p><p>'.$order[$k]['subTitle'].'</p><p>提交时间：'.$m[$k]['addtime'].'</p><p>状态:'.$m[$k]['status'].'</p>';
		}
		else{
			$m[$k]['title']='提现金额：'.(float)$r['money'].'元';
			$m[$k]['subTitle']='['.$m[$k]['status'].']';
			$m[$k]['des']='<p>提现'.TBMONEY.'：'.(float)$r['money'].TBMONEYUNIT.'</p><p>'.$order[$k]['subTitle'].'</p><p>提交时间：'.$m[$k]['addtime'].'</p><p>状态:'.$m[$k]['status'].'</p>';
		}	
	}
	$json_data=array('s'=>1,'r'=>$m,'size'=>$page_size);
}elseif($do=='msg_list'){
	$msg=$duoduo->select_all('msg','id,title,see,content as des,addtime','uid="'.$uid.'" order by see asc, id desc limit '.($page-1)*$page_size.','.$page_size);
	$data=array('see'=>1);
	foreach($msg as $k=>$row){
		$msg[$k]['title']=($row['see']==0?'[未读]':'').$row['title'];
		if($row['see']==0){
			$duoduo->update('msg',array('see'=>1),'id="'.$row['id'].'"');
		}
		$msg[$k]['subTitle']=date('m-d',strtotime($row['addtime']));
	}
	$json_data=array('s'=>1,'r'=>$msg,'size'=>$page_size);
}elseif($do=='msg_view'){
	$content=$_GET['content'];
	$field_arr=array('title'=>'站内消息','content'=>$content,'addtime'=>date('Y-m-d H:i:s'),'see'=>0,'uid'=>0,'senduser'=>$uid);
	$duoduo->insert('msg', $field_arr);
	$json_data=array('s'=>1,'r'=>'提交完成');
}elseif($do=='tixian'){
	$type=(int)$_GET['type'];
	$remark = htmlspecialchars($_GET['remark']);
	$data=array();
	
	if($_GET['money']>0){
		$type=2;
	}
	
	$user=$dduser;
	$user=$duoduo->freeze_user($user);
	if ((int) $uid == 0) {
		$json_data=array('s'=>0,'r'=>'您还没登录！');
		echo dd_json_encode($json_data);exit;
	}
	if ($user['alipay'] == '') {
		$json_data=array('s'=>0,'r'=>'请设置您的支付宝账号信息！');
		echo dd_json_encode($json_data);exit;
	}
	if ($webset['tixian']['level'] > 0 && $webset['tixian']['level'] > $user['level']) {
		$json_data=array('s'=>0,'r'=>'用户等级（' . $user['level'] . '）达到等级（' . $webset['tixian']['level'] . "）时方可提现");
		echo dd_json_encode($json_data);exit;
	}
	if ($user['realname'] == '') {
		$json_data=array('s'=>0,'r'=>'请设置您的真实姓名！');
		echo dd_json_encode($json_data);exit;
	}
	if ($type == 1) {
		$tipword = '您申请的提现我们会打入您的支付宝账户，请仔细填写您的支付宝和对应姓名！支付宝规定集分宝提现一次最多'.JFB_TX_MAX;
		$txxz = $webset['tixian']['tbtxxz'];
		$tixian_limit = $webset['tixian']['tblimit'] ? $webset['tixian']['tblimit'] : 1;
		$live_money = $user['live_jifenbao'];
		$money_name = TBMONEY;
		$unit = TBMONEYUNIT;
		$money=(float)$_GET['jifenbao'];
	}
	elseif ($type == 2) {
		$tipword = '您申请的提现我们会打入您的支付宝或者银行账户，请仔细填写您的汇款信息，以免出错！';
		$txxz = $webset['tixian']['txxz'];
		$tixian_limit = $webset['tixian']['limit'] ? $webset['tixian']['limit'] : 0.01;
		$live_money = $user['live_money'];
		$money_name = '金额';
		$unit = '元';
		$money=(float)$_GET['money'];
	}

	if ($txxz > 0) {
		$max_money = $live_money - $live_money % $txxz;
	} else {
		$max_money = $live_money;
	}
	if($type == 1 && $max_money>JFB_TX_MAX){
		$max_money=JFB_TX_MAX;
	}
	
	if($money > $live_money){
		$json_data=array('s'=>0,'r'=>'当前'.$money_name.'不足提现，联系网站查看是否冻结');
		echo dd_json_encode($json_data);exit;
	}
	
	if($money < $tixian_limit){
		$json_data=array('s'=>0,'r'=>'最低提现！'.$tixian_limit);
		echo dd_json_encode($json_data);exit;
	}
	
	
	if ($txxz > 0 && zhengchu($money, $txxz) == 0) {
		$json_data=array('s'=>0,'r'=>'提现必须是'.$txxz.'的整数倍');
		echo dd_json_encode($json_data);exit;
	}
	
	$data = array (
		'uid' => $uid,
		'addtime' => TIME,
		'ip' => get_client_ip(), 
		'realname' => $user['realname'], 
		'remark' => $remark, 
		'mobile' => $user['mobile'], 
		'status' => 0, 
		'tool' => 1, 
		'code'=>$user['alipay'],
		'type' => $type
	);
	if($type==1){
		if($user['tbtxstatus']!='0'){
			$json_data=array('s'=>0,'r'=>'该帐号还有提现未处理！');
			echo dd_json_encode($json_data);exit;
		}
		$money_field = 'jifenbao';
		$tx_field = 'tbtxstatus';
		$data['money'] = $money;
		$data['money2'] = (int) ($money * (100 / TBMONEYBL));
	}
	elseif($type==2){
		if($user['txstatus']!='0'){
			$json_data=array('s'=>0,'r'=>'该帐号还有提现未处理！');
			echo dd_json_encode($json_data);exit;
		}
		$money_field = 'money';
		$tx_field = 'txstatus';
		$data['money'] = $money;
		$data['money2'] = $money;
	}
	else{
		$json_data=array('s'=>0,'r'=>'数据错误！');
		echo dd_json_encode($json_data);exit;
	}
	$user_data[] = array (
		'f' => $tx_field,
		'v' => 1
	);
	$user_data[] = array (
		'f' => $money_field,
		'e' => '-',
		'v' => $money
	);
	$user_data[] = array (
		'f' => 'lasttixian',
		'e' => '=',
		'v' => TIME
	);
	
	$tixian_data=$duoduo->select('tixian','money,addtime','uid="'.$uid.'" order by id desc');
	if($tixian_data['money']==$data['money'] && TIME-$tixian_data['addtime']<30){
		$json_data=array('s'=>0,'r'=>'两次提现间隔太短！');
		echo dd_json_encode($json_data);exit;
	}
	$duoduo->update('user', $user_data, 'id="' . $uid . '"');
	$id=$duoduo->insert('tixian', $data);
	$json_data = array('s'=>1,'r'=>'申请提现成功！');
}elseif($do=='update'){
	$data['realname']=trim($_GET['realname']);
	$data['alipay']=trim($_GET['alipay']);
	if(isset($_GET['mobile'])){
		$data['mobile']=trim($_GET['mobile']);
	}
	if(isset($_GET['alipay'])&&$data['alipay']==''){
		$json_data=array('s'=>0,'r'=>'支付宝不能为空！');
		echo dd_json_encode($json_data);exit;
	}
	if(isset($_GET['realname'])&&$data['realname']==''){
		$json_data=array('s'=>0,'r'=>'姓名不能为空！');
		echo dd_json_encode($json_data);exit;
	}
	if(isset($_GET['mobile'])&&$data['mobile']==''){
		$json_data=array('s'=>0,'r'=>'手机号不能为空！');
		echo dd_json_encode($json_data);exit;
	}
	if($dduser['alipay']!=''){
		$data['alipay']=$dduser['alipay'];
	}
	if($dduser['realname']!=''){
		$data['realname']=$dduser['realname'];
	}
	if($dduser['alipay']!='' && $dduser['realname']!='' && !isset($_GET['mobile'])){
		$json_data=array('s'=>0,'r'=>'支付宝和姓名无法修改！');
		echo dd_json_encode($json_data);exit;
	}
	if (isset($_GET['alipay'])&&reg_alipay($data['alipay']) == 0) {
		$json_data=array('s'=>0,'r'=>'支付宝格式错误！');
		echo dd_json_encode($json_data);exit;
	}
	if (isset($_GET['alipay'])&&$duoduo -> check_my_field('alipay', $data['alipay'], $dduser['id']) > 0) {
		$json_data=array('s'=>0,'r'=>'支付宝已被使用！');
		echo dd_json_encode($json_data);exit;
	}
	if (isset($_GET['mobile'])&&reg_mobile($data['mobile']) == 0) {
		$json_data=array('s'=>0,'r'=>'手机格式错误！');
		echo dd_json_encode($json_data);exit;
	}
	$duoduo->update('user',$data,'id="'.$uid.'"');
	$user=$duoduo->select('user','*','id='.$uid);
	$user=$app_class->return_user_info($user);
	$json_data=array ("s" => 1,'r'=>$user);
}
elseif($do=='pwd'){
	$old_pwd=trim($_GET['old_pwd']);
	$ddpwd=trim($_GET['ddpwd']);
	$pwd_confirm=trim($_GET['pwd_confirm']);
	
	if ($ddpwd != $pwd_confirm) {
		$json_data=array('s'=>0,'r'=>'2次密码不相同！');
		echo dd_json_encode($json_data);exit;
	}
	if ($duoduo -> check_oldpass($old_pwd, $uid) == 'false') {
		$json_data=array('s'=>0,'r'=>'原密码错误！');
		echo dd_json_encode($json_data);exit;
	} 
	if (reg_password($ddpwd) == 0) { // 密码格式
		$json_data=array('s'=>0,'r'=>'密码格式错误！');
		echo dd_json_encode($json_data);exit;
	}
	
	$webset=$duoduo->webset;
	if ($webset['ucenter']['open'] == 1) {
		include DDROOT . '/comm/uc_define.php';
		include_once DDROOT . '/uc_client/client.php';
		$uc_name = iconv("utf-8", "utf-8", $dduser['ddusername']);
		$ucresult = uc_user_edit($uc_name, $old_pwd, $ddpwd);

		if ($ucresult == -1) {
			$json_data=array('s'=>0,'r'=>'密码错误！');
			echo dd_json_encode($json_data);exit;
		}
	}
	
	$data=array('ddpassword'=>md5($ddpwd));
	$duoduo->update('user',$data,'id="'.$uid.'"');
	$user=$duoduo->select('user','*','id='.$uid);
	$user=$app_class->return_user_info($user);
	$json_data=array ("s" => 1,'r'=>$user);
}
elseif($do=='register'){
	$_GET['type']='namereg';
	$re=$app_class->register(1);
	if($re['s']==1){
		$user=$duoduo->select('user','*','id="'.$re['r'].'"');
		$user=$app_class->return_user_info($user);
		$plugin_config=dd_get_cache('plugin/dd_chaofan');
		$url=str_replace('www','cf',DD_FANLICHENG_URL).'/app_n/?mod=api&act=site&url='.urlencode(URL);
		$site_info=dd_get($url,'get',0);
		$site_info=dd_json_decode($site_info,1);
		if($site_info['s']==1){
			$site_info=$site_info['r'];
			$json_data['cf_user']=array('siteinfo'=>$site_info,'sietid'=>$site_info['id'],'mobile'=>$user['mobile'],'ddusername'=>$user['name'],'dd_uid'=>$user['id'],'ddpassword'=>$user['pwd'],'id'=>0,'apiurl'=>$site_info['apiurl'],'app_sign'=>$site_info['app_sign'],'app_sign_jl'=>$site_info['app_sign_jl']);
			$user_webset['TBMONEY'] = TBMONEY;
			$user_webset['TBMONEYUNIT'] = TBMONEYUNIT;
			$user_webset['tx_limit_jifenbao'] = $webset['tixian']['tblimit'];
			$user_webset['txxz_jifenbao']=$webset['tixian']['tbtxxz'];
			$user_webset['tx_limit_money']=$webset['tixian']['limit'];
			$user_webset['txxz_money']=$webset['tixian']['txxz'];
			
			$json_data['dd_user']['user']=$user;
			$json_data['dd_user']['webset']=$user_webset;
			$json_data=array ("s" => 1,"r" => $json_data);
		}
		else{
			$json_data=array ("s" => 0,"r" => $site_info['r']);
		}
	}
	else{
		$json_data=array ("s" => 0,"r" => $re['r'],"id" => $re['id']);
	}
	
}
elseif($do=='login'){
	if($_GET['username']=='' && $_GET['uid']>0){
		$_user=$duoduo->select('user','*','id="'.(int)$_GET['uid'].'"');
		$_GET['username']=$_user['ddusername'];
	}
	$re=$app_class->login();
	if($re['s']==1){
		$user=$re['r'];
		$plugin_config=dd_get_cache('plugin/dd_chaofan');
		$url=str_replace('www','cf',DD_FANLICHENG_URL).'/app_n/?mod=api&act=site&url='.urlencode(URL);
		$site_info=dd_get($url,'get',0);
		$site_info=dd_json_decode($site_info,1);
		if($site_info['s']==1){
			$site_info=$site_info['r'];
			$json_data['cf_user']=array('siteinfo'=>$site_info,'sietid'=>$site_info['id'],'mobile'=>$user['mobile'],'ddusername'=>$user['name'],'dd_uid'=>$user['id'],'ddpassword'=>$user['pwd'],'id'=>0,'apiurl'=>$site_info['apiurl'],'app_sign'=>$site_info['app_sign'],'app_sign_jl'=>$site_info['app_sign_jl']);
			$user_webset['TBMONEY'] = TBMONEY;
			$user_webset['TBMONEYUNIT'] = TBMONEYUNIT;
			$user_webset['tx_limit_jifenbao'] = $webset['tixian']['tblimit'];
			$user_webset['txxz_jifenbao']=$webset['tixian']['tbtxxz'];
			$user_webset['tx_limit_money']=$webset['tixian']['limit'];
			$user_webset['txxz_money']=$webset['tixian']['txxz'];
			
			$json_data['dd_user']['user']=$re['r'];
			
			$webid=$duoduo->select('apilogin','webid','uid="'.$re['r']['id'].'"');
			$key_webid=dd_crc32(DDKEY.$webid);
			$key_md5webid=md5($key_webid);
			$md5webid=md5($webid);
			$md5pwd=$re['r']['pwd'];
		
			$default_pwd='';
			if($key_md5webid==$md5pwd){
				$default_pwd=$key_webid;
			}
			if($md5webid==$md5pwd){
				$default_pwd=$webid;
			}
			
			$json_data['dd_user']['user']['default_pwd']=$default_pwd;
			
			$json_data['dd_user']['webset']=$user_webset;
			$json_data=array ("s" => 1,"r" => $json_data);
		}
		else{
			$json_data=array ("s" => 0,"r" => $site_info['r']?$site_info['r']:'登录失败');
		}
	}
	else{
		$show_yzm=login_error('check');
		$json_data=array ("s" => 0,"r" => $re['r'],"id" => $re['id'],'show_yzm'=>$show_yzm);
	}
}
_dd_string($json_data);
echo dd_json_encode($json_data);
?>