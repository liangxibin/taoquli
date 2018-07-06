<?php
if($_GET['key']!=md5(DDYUNKEY)){
	echo dd_json_encode(array('s'=>0,'r'=>'，超返比例同步验证不通过'));
	exit();
}
if(empty($_GET['fanli_bl'])){
	echo dd_json_encode(array('s'=>0,'r'=>'，超返比例不能空'));
	exit();
}
$dd_chaofan=dd_get_cache('plugin/dd_chaofan');
if(empty($dd_chaofan)){
	echo dd_json_encode(array('s'=>0,'r'=>'，请先安装超返插件'));
	exit();
}
$dd_chaofan['fanli_bl']=(float)$_GET['fanli_bl'];
dd_set_cache('plugin/dd_chaofan',$dd_chaofan);
echo dd_json_encode(array('s'=>1,'r'=>'，超返比例同步成功'));
exit();
?>