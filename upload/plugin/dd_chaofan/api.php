<?php
/**
 * ============================================================================
 * 版权所有 2008-2013 多多科技，并保留所有权利。
 * 网站地址: http://soft.duoduo123.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
define('INDEX',1);
include ('../../comm/dd.config.php');
include (DDROOT.'/comm/checkpostandget.php');
dd_session_start();
if(file_exists(DDROOT.'/mod/inc/header.act.php')){
	include (DDROOT.'/mod/inc/header.act.php');
}else{
	include (DDROOT.'/mod/header.act.php');
}
if(!function_exists('is_mobile')){
	function is_mobile() {
		//如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
			return true;
		}
		//如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset ($_SERVER['HTTP_VIA'])) {
			//找不到为flase,否则为true
			$t=stristr($_SERVER['HTTP_VIA'], "wap");
			if($t){
				return true;
			}
		}
		//脑残法，判断手机发送的客户端标志,兼容性有待提高
		if (isset ($_SERVER['HTTP_USER_AGENT'])) {
			$clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile');
			//从HTTP_USER_AGENT中查找手机浏览器的关键字
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
				return true;
			}
		}
		//协议法，因为有可能不准确，放到最后判断
		if (isset ($_SERVER['HTTP_ACCEPT'])) {
			//如果只支持wml并且不支持html那一定是移动设备
			//如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
				return true;
			}
		}
		return false;
	}
}
$do=$_GET['do']?$_GET['do']:'check_login';
switch($do){
    case 'check_login':
	    $url=$_GET['url']?$_GET['url']:$_SERVER['HTTP_REFERER'];
		if($dduser['id']>0){
			$dduser["name"]=str_replace('_','',$dduser['name']);
			$dduser["name"]=str_replace('_','',$dduser['name']);
			$usertoken=ddStrCode($dduser["id"].'_'.$dduser["name"].'_'.$webset['siteid'].'_'.TIME.'_'.$dduser["ddpassword"].'_'.$dduser["mobile"].'_'.$dduser["mobile_test"].'_'.$dduser["email"],DDYUNKEY,'ENCODE');
			$url=$url.'&usertoken='.urlencode($usertoken);
			$_SESSION['api_login_reffrer']='';
		}else{
			//$from=SITEURL.'/plugin/dd_chaofan/api.php?url='.urlencode($url);
			if(is_mobile()==1){
				if(function_exists('iid_encode')){
					$url=wap_l('user','login',array('from'=>CUR_URL));
				}else{
					$url='plugin.php?mod=wap&m=lo&from='.urlencode(CUR_URL);
				}
			}else{
				$url=u('user','login',array('from'=>CUR_URL));
			}
			$_SESSION['api_login_reffrer']=CUR_URL;
		}
		jump($url);
	break;
}
dd_exit();
?>