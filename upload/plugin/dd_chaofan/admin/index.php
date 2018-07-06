<?php 
/**
 * ============================================================================
 * 版权所有 2008-2012 多多科技，并保留所有权利。
 * 网站地址: http://soft.duoduo123.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/

if(!defined('ADMIN')){
	exit('Access Denied');
}

$file=file_get_contents(DDROOT.'/mod/jump.act.php');
if(function_exists('iid_encode')){  //是v83
	$daima="\$iid=iid_decode(\$_GET['iid']);";
}
else{
	$daima="\$iid=(float)\$_GET['iid'];";
}

$qianzhui="if(!defined('INDEX')){
	exit('Access Denied');
}";

$wancheng=$qianzhui.$daima;
if(strpos($file,$wancheng)===false){
	if(!iswriteable(DDROOT.'/mod/jump.act.php',$file)){
		exit('请给文件：'.DDROOT.'/mod/jump.act.php可写权限');
	}
	$file=str_replace($qianzhui,$wancheng,$file);
	file_put_contents(DDROOT.'/mod/jump.act.php',$file);
}

$jiaocheng=$duoduo->select('plugin','jiaocheng','id="'.$plugin_id.'"');
?>
<script>
$(function(){
	if(parseInt($('input[name="status"]:checked').val())==0){
		$('.status_guanlian').hide();
	}
	else{
		$('.status_guanlian').show();
	}
	$('input[name="status"]').click(function(){
        if($(this).val()==1){
		    $('.status_guanlian').show();
		}
		else if($(this).val()==0){
			$('.status_guanlian').hide();
		}
	});
});
</script>
<form action="index.php?mod=<?=MOD?>&act=<?=ACT?>&do=<?=$do?>&plugin_id=<?=$plugin_id?>" method="post" name="form1">
<table id="addeditable" border=1 cellpadding=0 cellspacing=0 bordercolor="#dddddd"  bgcolor="#FFFFFF">
  <?php include(ADMINROOT.'/template/plugin/dd_set.tpl.php');?>
  <tr class="status_guanlian" style="display:none">
    <td align="right">设置入口：</td>
    <td>&nbsp;<a href="http://cf.fanlicheng.com/admin" target="_blank">http://cf.fanlicheng.com/admin</a> <span class="zhushi"><a href="<?=$jiaocheng?>" target="_blank">教程</a></span></td>
  </tr>
  <tr >
    <td align="right">追加返利时间：</td>
    <td>&nbsp;<input name="days" value="<?=$plugin_data['days']?>"/>天后<?php if($plugin_data['days']>0){?>，当前追加的是<?=date('Y-m-d',strtotime("-".$plugin_data['days']." days"))?>之前的订单<?php }?>，即会员正常返利后多少天再追加返利，0为实时追加，建议16天后追加。</td>
  </tr>
  <?php if($_GET['debug']){?>
  <tr title="订单ID是<?=(int)$plugin_data['max_id']?>" >
    <td align="right">超返总比例：</td>
    <td>&nbsp;<input name="fanli_bl" value="<?=$plugin_data['fanli_bl']>0?(float)$plugin_data['fanli_bl']:78?>"/>%，如设置78%即返给会员总佣金的78%给会员。以追加返利形式返现金。追加金额=总佣金*超返比例-账户已返佣金。</td>
  </tr>
  <tr>
    <td align="right">当前最大订单ID：</td>
    <td>&nbsp;<input name="max_id" value="<?=$plugin_data['max_id']?>"/>，正式上线不显示的，测试的时候改成1就可以检测全部订单了，不能为0</td>
  </tr>
  <?php }?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;<input type="submit" name="sub" value=" 保 存 " /><?php if(!isset($_GET['debug'])){?><input type="hidden" name="fanli_bl" value="<?=$plugin_data['fanli_bl']>0?(float)$plugin_data['fanli_bl']:78?>"/><input type="hidden" name="max_id" value="<?=$plugin_data['max_id']?>"/><?php }?></td>
  </tr>
</table>
</form>