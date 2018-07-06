<?php //多多
$dd_chaofan=dd_get_cache('plugin/dd_chaofan');
$dd_chaofan['max_id']=$dd_chaofan['max_id']-10000;
$dd_chaofan['max_id']=$dd_chaofan['max_id']>0?$dd_chaofan['max_id']:0;
dd_set_cache('plugin/dd_chaofan',$dd_chaofan);

$duoduo->query('ALTER TABLE `'.BIAOTOU.'buy_log` ADD `is_cf` tinyint(1) DEFAULT \'0\';');
$duoduo->query("CREATE TABLE `".BIAOTOU."plugin_cf_trade` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trade_id` varchar(40) DEFAULT NULL,
  `uid` int(11) DEFAULT '0',
  `addtime` int(11) DEFAULT NULL,
  `commission` double(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;");
$data=array();
$data['plugin_name']='dd_chaofan';
$data['filename']='cron_cf_tradelist.php';
$cun=$duoduo->select('cron','id',"plugin_name='dd_chaofan' and filename='cron_cf_tradelist.php'");
$data['title']='追加返利';
$data['status']=1;
$data['leixing']=1;
$data['fangshi']=2;
$data['execute_time']=0;
$data['last_time']='2015-11-09 21:12:51';
$data['jindu']=0;
$data['msg']='';
$data['dev']='';
$data['interval_time']=10;
$data['random']=0;
$data['sys']=0;
if(empty($cun)){
	$duoduo->insert('cron',$data);
}
?>