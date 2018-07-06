<?php
if(!defined('DDROOT')){
	exit('Access Denied');
}
$dd_cron_url=SITEURL."/plugin/dd_chaofan/cron/cron_cf_tradelist.php";
$dd_cron['dd_chaofan']=array($dd_cron_url,'rate'=>5);
unset($dd_cron_url);
?>