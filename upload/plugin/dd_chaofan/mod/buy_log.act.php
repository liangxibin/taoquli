<?php
if(!defined('DDROOT')){
	exit('Access Denied');
}
include(DDROOT.'/plugin/dd_chaofan/comm.func.php');
$is_cf=$_GET['is_cf'];
$keyword=trim($_GET['keyword']);
$buy_log_data['uid']=trim($_GET['uid']);
$buy_log_data['iid']=trim(ddStrCode($_GET['iid'],DDYUNKEY,'DECODE'));
if($is_cf==-1){
	$buy_log_data['is_cf']=0;
}
else{
	$buy_log_data['is_cf']=1;
}

$buy_log_data['day']=date('Y-m-d H:i:s');
$buy_log_data['keyword']=$keyword?$keyword:'';

//10分钟内重复的数据不让提交
if($buy_log_data['uid']>0){
	if($buy_log_data['iid']>0){
		$where=' and iid="'.$buy_log_data['iid'].'"';
	}
	else{
		$where=' and keyword="'.$buy_log_data['keyword'].'"';
	}
	$buy_log_id=(int)$duoduo->select('buy_log','id','uid="'.$buy_log_data['uid'].'" '.$where.' and day>"'.date("Y-m-d H:i:s",strtotime("-10 min")).'"');
	
	if($buy_log_id==0 && ($buy_log_data['iid']>0 || $buy_log_data['keyword']!='')){
		$duoduo->insert('buy_log',$buy_log_data);
	}
}
?>