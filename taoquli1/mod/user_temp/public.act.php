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

$qunfa_arr=array('sms'=>'短信');
$do=isset($_GET['do'])?$_GET['do']:'sms';
?>