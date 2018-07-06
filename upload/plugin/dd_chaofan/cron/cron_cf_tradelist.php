<?php
/**
这个计划任务是自动获取淘宝超返订单
**/
if(function_exists('iid_encode')){  //是v83
	define('V83',1);
	$iid=iid_encode($iid);
}
else{
	include ('../../../comm/dd.config.php');
	include (DDROOT.'/comm/checkpostandget.php');
	define('V83',0);
}
if(!defined('DDROOT')){
	exit('Access Denied');
}
$page=$_GET['page']?(int)$_GET['page']:1;
$shijian_id=30;//超返明细事件ID
$dd_chaofan=dd_get_cache('plugin/dd_chaofan');
$page_size=$dd_chaofan['page_size']?$dd_chaofan['page_size']:20;
if($dd_chaofan['fanli_bl']<=0){
	return $this->error('请设置超返比例！');
}
$fanli_bl=round($dd_chaofan['fanli_bl']/100,2);
//获取从哪个订单ID开始
$max_id=$dd_chaofan['max_id'];
if($max_id<=0){
	//第一种根据cf_trade查
	$trade_id=$duoduo->select('plugin_cf_trade','trade_id','1 order by id desc');
	if($trade_id){
		$max_id=$duoduo->select('tradelist','id',"trade_id='".$trade_id."'");
	}
	if(empty($trade_id)||empty($max_id)){
		//如果都没有那就从当前最大订单开始
		$dd_chaofan['max_id']=$duoduo->select('tradelist','id','status=5 and uid>0 order by id desc');
		dd_set_cache('plugin/dd_chaofan',$dd_chaofan);
		if($admin_run==1){
			PutInfo('全部执行完毕');
		}
		if(V83==1){
			return $this->over('全部执行完毕');
		}else{
			dd_exit('全部执行完毕');
		}
	}
}
//获取新订单
if($dd_chaofan['days']>0){
	$where=" and pay_time<='".date('Y-m-d 23:59:59',strtotime("-".$dd_chaofan['days'].' days'))."'";
}
$tradelist=$duoduo->select_all('tradelist','id,trade_id,commission,status,uid,num_iid,fxje,item_title,create_time,trade_id_former','status=5 and uid>0 and id>'.$max_id.$where.' ORDER BY id ASC limit '.$page_size);
if(empty($tradelist)){
	if($admin_run==1){
		PutInfo('没有订单，执行完毕');
	}
	if(V83==1){
		$this->error('');
		return $this->over('没有订单，执行完毕');
	}else{
		dd_exit('没有订单，执行完毕');
	}
}

foreach($tradelist as $vo){
	$max_id=$vo['id'];
	$cun=$duoduo->select('plugin_cf_trade','id',"trade_id='".$vo['trade_id']."'");
	if($cun){
		continue;
	}
	$cf_commission=$vo['commission']*$fanli_bl;
	$cf_commission_zong=round($cf_commission,2);
	$cf_commission=$cf_commission_zong-$vo['fxje'];//补差价，超凡比例总佣金-已返佣金
	if($cf_commission<0.01){
		//低于0.01的不返利
		continue;
	}
	$where='uid="'.$vo['uid'].'" and iid="'.$vo['num_iid'].'"';
	$st=date("Y-m-d 0:0:0",$vo['create_time']?strtotime($vo['create_time']):TIME);
	$et=date("Y-m-d 23:59:59",$vo['create_time']?strtotime($vo['create_time']):TIME);
	$where.=' and day>="'.$st.'" and day<="'.$et.'"';
	$buy_log=$duoduo->select('buy_log','id,iid,keyword,uid,is_cf',$where);
	if(empty($buy_log)||$buy_log['is_cf']==0){
		//没记录的不超返
		continue;
	}
	$cf_trade_id=$duoduo->insert('plugin_cf_trade',array('trade_id'=>$vo['trade_id'],'uid'=>$vo['uid'],'commission'=>jfb_data_type($cf_commission*TBMONEYBL),'addtime'=>TIME));
	if($cf_trade_id<=0){
		continue;
	}
	$update_user_data=array();
	$update_user_data[]=array('f'=>'jifenbao','v'=>jfb_data_type($cf_commission*TBMONEYBL),'e'=>'+');
	$duoduo->update_user_mingxi($update_user_data,$vo['uid'],'dd_chaofan_butie',$vo['trade_id_former'],0,0,'',$vo['id']);
	$duoduo->update('tradelist',array('jifenbao'=>jfb_data_type($cf_commission_zong*TBMONEYBL),'item_title'=>'[超返]'.$vo['item_title']),"id='".$vo['id']."'");
	echo mysql_error();
}
$dd_chaofan['max_id']=$max_id;
dd_set_cache('plugin/dd_chaofan',$dd_chaofan);
$page++;
if($admin_run==1){
	$url=$this->base_url.'&page='.$page.$this->admin_run_param;
	PutInfo('执行第'.$page.'页（最大订单ID'.$max_id.'）。。。<br/><br/><img src="images/wait2.gif" />',$url);
}
if(V83==1){
	return $this->over('执行完毕');
}else{
	dd_exit('一次执行完毕');
}
?>