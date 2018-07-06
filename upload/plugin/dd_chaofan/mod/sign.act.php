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
$uid = trim($_GET['uid']);
$jifenbao = trim($_GET['jifenbao']);
$mingxi = $duoduo->select('mingxi','*','shijian="dd_chaofan_sign" and uid="'.$uid.'" order by id desc');
if(!empty($mingxi) && strtotime($mingxi['addtime'])>=strtotime(date('Y-m-d'))){
	$json_data = array('s'=>0,'r'=>'您今天已经签到！');
}else{
	$data=array(array('f'=>'jifenbao','e'=>'+','v'=>$jifenbao));
	$duoduo->update('user',$data,'id="'.$uid.'"');
	$data=array('uid'=>$uid,'shijian'=>'dd_chaofan_sign','jifenbao'=>$jifenbao);
 	$duoduo->mingxi_insert($data);
	$json_data = array('s'=>1,'r'=>'签到成功！');
}
echo dd_json_encode($json_data);dd_exit();
?>