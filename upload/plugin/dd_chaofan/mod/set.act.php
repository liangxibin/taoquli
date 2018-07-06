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
$appwebset=array('need_alipay'=>$webset['user']['need_alipay'],'need_qq'=>$webset['user']['need_qq'],'need_tbnick'=>$webset['user']['need_tbnick'],'need_tjr'=>$webset['user']['need_tjr']);
$apilogin=$duoduo->select_all('api','*','open=1');
foreach($apilogin as $row){
	$appwebset['apilogin'][$row['code']]=$row['title'];
}
$appwebset['TBMONEY'] = TBMONEY;
$appwebset['TBMONEYUNIT'] = TBMONEYUNIT;
$appwebset['tx_limit_jifenbao'] = $webset['tixian']['tblimit'];
$appwebset['txxz_jifenbao']=$webset['tixian']['tbtxxz'];
$appwebset['tx_limit_money']=$webset['tixian']['limit'];
$appwebset['txxz_money']=$webset['tixian']['txxz'];
$appwebset['log_show_yzm']=login_error('check');
$regnum=$duoduo->count('user','regtime>"'.date('Y-m-d 00:00:00').'"');
$appwebset['reg_show_yzm']=$regnum>=3?1:0;
$json_data=array('s'=>1,'r'=>$appwebset);

echo dd_json_encode($json_data);