<?php //多多
return array (
  'admin_nav' => 
  array (
    'index' => 
    array (
      'title' => '基本设置',
    ),
    'list' => 
    array (
      'title' => '追加订单',
    ),
  ),
  'table' => 
  array (
    'cf_trade' => 
    array (
      'id' => 'int(10) unsigned NOT NULL auto_increment',
      'trade_id' => 'varchar(40) default NULL',
      'uid' => 'int(11) default "0"',
      'addtime' => 'int(11)',
      'commission' => 'double(10,2) default "0.00"',
      'duoduo_table_index' => 'PRIMARY KEY  (`id`)',
    ),
  ),
  'install_sql' => "ALTER TABLE `{%BIAOTOU%}buy_log` ADD `is_cf` tinyint(1) DEFAULT '0';",
  'admin_auto' => 
  array (
    'index' => 1,
  ),
  'mingxi' => 1,
  'need_include' => 1, 
  'debug' => 0,
);
?>