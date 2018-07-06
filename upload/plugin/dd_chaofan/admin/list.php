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

$page = !($_GET['page'])?'1':intval($_GET['page']);
$pagesize=20;
$frmnum=($page-1)*$pagesize;
$where=" 1 ";
if($_GET['trade_id']){
	$where.=" and a.trade_id='".$_GET['trade_id']."'";
}
$total=$duoduo->count('plugin_cf_trade as a left join '.BIAOTOU.'user as b on b.id=a.uid left join '.BIAOTOU.'tradelist as c on c.trade_id=a.trade_id',$where);
$data=$duoduo->select_all('plugin_cf_trade as a left join '.BIAOTOU.'user as b on b.id=a.uid left join '.BIAOTOU.'tradelist as c on c.trade_id=a.trade_id','a.*,b.ddusername,c.item_title',$where.' ORDER BY a.id DESC limit '.$frmnum.','.$pagesize);
echo mysql_error();
if(function_exists('iid_encode')){  //是v83
	$cun=$duoduo->select('cron','id',"plugin_name='dd_chaofan' and filename='cron_cf_tradelist.php'");
	$update_url=SITEURL."/index.php?mod=cron&act=run&cron_id=".$cun;
}
else{
	$update_url=SITEURL."/plugin/dd_chaofan/cron/cron_cf_tradelist.php";
}
?>
<form name="form1" action="" method="get">
<table cellspacing="0" width="100%" style="border:1px  solid #DCEAF7; border-bottom:0px; background:#E9F2FB">
        <tr>
              <td width="20%">&nbsp;<img src="images/arrow.gif" width="16" height="22" align="absmiddle" />&nbsp;<a href="<?=$update_url?>">更新追加订单</a>&nbsp;&nbsp;<a href="http://bbs.duoduo123.com/read-1-1-204721.html" target="_blank">说明</a></td>
              <td width="" align="right">订单号：<input type="text" name="trade_id" value="<?=$_GET['trade_id']?>" />&nbsp;<input type="submit" value="搜索" /></td>
              <td width="150px" align="right">共有 <b><?=$total?></b> 条记录&nbsp;&nbsp;</td>
            </tr>
      </table>
      <input type="hidden" name="mod" value="<?=MOD?>" />
      <input type="hidden" name="act" value="<?=ACT?>" />
      <input type="hidden" name="do" value="<?=$do?>" />
      <input type="hidden" name="plugin_id" value="<?=$plugin_id?>" />
      </form>
      <form name="form2" method="get" action="" style="margin:0px; padding:0px">
      <table id="listtable" border=1 cellpadding=0 cellspacing=0 bordercolor="#dddddd">
                    <tr>
                      <td width="3%"><input type="checkbox" onClick="checkAll(this,'ids[]')" /></td>
                      <td width="">id</td>
                      <td width="">会员名</td>
					  <td width="">商品名</td>
                      <td width="">订单号</td>
                      <td width=""><?=TBMONEY?></td>
                      <td width="">补贴时间</td>
                    </tr>
					<?php foreach ($data as $r){?>
					  <tr>
                        <td><input type='checkbox' name='ids[]' value='<?=$r["id"]?>' id='content_<?=$r["id"]?>' /></td>
                        <td><?=$r["id"]?></td>
						<td><?=$r["ddusername"]?></td>
                        <td><?=$r["item_title"]?></td>
                        <td><?=$r["trade_id"]?></td>
                        <td><?=(float)$r["commission"]?></td>
                        <td><?=date('Y-m-d H:i:s',$r["addtime"])?></td>
					  </tr>
					<?php }?>
		</table>
        <div style="position:relative; padding-bottom:10px">
            <input type="hidden" name="mod" value="<?=MOD?>" />
      <input type="hidden" name="act" value="<?=ACT?>" />
      <input type="hidden" name="do" value="del" />
      <input type="hidden" name="plugin_id" value="<?=$plugin_id?>" />
            <div style="position:absolute; left:5px; top:5px"><input type="submit" value="删除" class="myself" onclick='return confirm("确定要删除?")'/></div>
            <div class="megas512" style=" margin-top:5px;"><?=pageft($total,$pagesize,u(MOD,'admin',array('do'=>'list','plugin_id'=>$plugin_id,'trade_id'=>$_GET['trade_id'])));?></div>
            </div>
       </form>