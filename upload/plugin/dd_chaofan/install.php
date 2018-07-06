<?php
include('../../comm/dd.config.php');

$dir=$_SERVER['SCRIPT_FILENAME'];
$dir=str_replace('\\','/',$dir);
$a=explode('/',$dir);

$code=$a[count($a)-2]; //应用标识码
$re=add_plugin_test($code);
$data=array('title'=>'超级返利');
$duoduo->update('plugin',$data,'code="'.$code.'"');
if($re==1){
	echo "安装成功";
}
else{
	echo $re;
}
?>