<?php
if(!defined('DDROOT')){
	exit('Access Denied');
}
include(DDROOT.'/plugin/dd_chaofan/comm.func.php');
$do=$_GET['do'];
$is_cf=(int)$_GET['is_cf'];
$is_tg=(int)$_GET['is_tg'];
$cf_url=$_GET['cf_url'];
$cur_url=$_GET['cur_url'];
if(function_exists('iid_encode')){  //是v83
	define('V83',1);
	$iid=iid_encode($iid);
}
else{
	define('V83',0);
}
if($do=='go_user'){
	if(is_mobile()==1){
		if(V83==1){
			$url=wap_l('user','index');
		}else{
			$url='plugin.php?mod=wap&m=user_index';
		}
	}else{
		$url=u('user','index');
	}
	jump($url);
}
if($dduser['id']<=0){
	if(is_mobile()==1){
		if(function_exists('iid_encode')){
			$url=wap_l('user','login',array('from'=>CUR_URL));
		}else{
			$url='plugin.php?mod=wap&m=lo&from='.urlencode(CUR_URL);
		}
	}else{
		$url=u('user','login',array('from'=>CUR_URL));
	}
	jump($url);
}
//有来源就跳回去
if($cur_url){
	$fxbl=$webset['fxbl'][$dduser['type']];
	$dd_user_str=$dduser['id'].'|'.$dduser['name'].'|'.$fxbl;
	$dd_user_str=urlencode(new_ddStrCode($dd_user_str,DDYUNKEY,'ENCODE'));
	if(strpos($cur_url,'?')===false){
		jump($cur_url.'?dd_user='.$dd_user_str);
	}else{
		jump($cur_url.'&dd_user='.$dd_user_str);
	}
	
}
$yl_iid=$iid=new_ddStrCode($_GET['iid'],DDYUNKEY,'DECODE');
if($iid==false){
	$yl_iid=$iid=ddStrCode($_GET['iid'],'duoduo123','DECODE');
	if(empty($iid)){
		jump(-1,'非法访问，请重新访问！');
	}
}
$url=$_GET['url'];
if($url){
	$domain=get_domain($url);
	if($domain=='jd.com'){
		if(is_mobile()==1){
			//手机京东跳转
			if(V83==1){
				$url=wap_l('jump','index',array('is_cf'=>$is_cf,'a'=>'mall','url'=>$url));
			}else{
				$url="plugin.php?mod=wap&m=jump&a=mall&is_cf=".$is_cf."&url=".urlencode($url);
			}
		}else{
			//京东跳转
			$url=u('jump','mall',array('url'=>$url,'is_cf'=>$is_cf));
		}
	}else{
		if($is_tg==1){
			//v83淘宝跳转
			$url="http://".$cf_url."/index.php?mod=jump&act=s8&url=".base64_encode($url).'&dduserid='.$dduser['id'].'&iid='.urlencode(new_ddStrCode($iid,YUNKEY,'ENCODE')).'&is_cf='.$is_cf.'&ddusername='.urlencode($dduser['name']);
		}else{
			//s8不区分手机还是pc
			if(V83==1){
				//v83淘宝跳转
				$url=u('tao','view',array('iid'=>iid_encode($iid),'is_cf'=>$is_cf));	
			}else{
				//v82淘宝跳转
				$url=u('tao','view',array('iid'=>$iid,'web'=>1,'is_cf'=>$is_cf));
			}
		}
	}
}else{
	if(is_mobile()==1){
		if(V83==1){
			//手机淘宝iid跳转
			$url=wap_l('tao','view',array('iid'=>iid_encode($iid),'is_cf'=>$is_cf));
		}else{
			//v82淘宝跳转
			$url='plugin.php?mod=tao&m=view&iid='.$iid.'&is_cf='.$is_cf;
		}
	}else{
		if(V83==1){
			//v83淘宝跳转
			$url=u('tao','view',array('iid'=>iid_encode($iid),'is_cf'=>$is_cf));
		}else{	
			$url=u('tao','view',array('iid'=>$iid,'is_cf'=>$is_cf));	
		}
	}
}
//添加浏览记录,只有wap有效，pc必须是登录的才可以
if($yl_iid&&$dduser['id']>0){
	$buy_log_data['is_cf']=$is_cf;
	$buy_log_data['iid']=$yl_iid;
	$buy_log_data['uid']=$dduser['id'];
	$buy_log_data['day']=date('Y-m-d H:i:s');
	$buy_log_data['keyword']='';
	//10分钟内重复的数据不让提交
	if($buy_log_data['uid']>0){
		$where=' and iid="'.$buy_log_data['iid'].'"';
		$buy_log_id=(int)$duoduo->select('buy_log','id','uid="'.$buy_log_data['uid'].'" '.$where.' and day>"'.date("Y-m-d H:i:s",strtotime("-10 min")).'"');
		if($buy_log_id==0 && ($buy_log_data['iid']>0 || $buy_log_data['keyword']!='')){
			$duoduo->insert('buy_log',$buy_log_data);
		}
	}
}
jump($url);
?>