<?php 
/**
 * ============================================================================ * 版权所有 金创科技，保留所有权利。
 * 网站地址: http://www.taoquli.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/

if(!defined('ADMIN')){
	exit('Access Denied');
}

if($_POST['sub']!=''){
	if($_POST['leixing']!=2){
		unset($_POST['dev']);
	}
	
	$_POST['interval_time']=(float)$_POST['interval_time'];
	$_POST['random']=(float)$_POST['random'];
}

include(ADMINROOT.'/mod/public/addedi.act.php');