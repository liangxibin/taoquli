<?php
/**
 * ============================================================================
 * 版权所有 多多科技，保留所有权利。
 * 网站地址: http://soft.duoduo123.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/

if(!defined('DDROOT')){
	exit('Access Denied');
}

include(DDROOT.'/plugin/dd_chaofan/app.class.php');

function get_url_contents($url){
		$c=fs('collect');
		if(function_exists('curl_exec')){
			$c->set_func='curl';
		}
		$c->get($url);
		$result=$c->val;
    	return $result;
	}

function get_user_from_web($web,$webid,$webname,$duoduo){
		//echo $web.'--'.$webid.'--'.$webname.'___';
		global $app_class;
		if(strlen($webid)>20){
			$webid=substr($webid,0,20);
		}
		$row=$duoduo->select('apilogin as a,user as b', 'b.id,b.ddusername,b.ddpassword,b.ucid,a.webid,a.web,a.uid,a.id as apilogin_id', 'a.uid=b.id and a.webid="'.$webid.'" and a.web="'.$web.'"');
		if($row['id']>0){
			$set_con_arr=array(array('f'=>'lastlogintime','v'=>date('Y-m-d H:i:s')),array('f'=>'loginnum','e'=>'+','v'=>1));

			if($row['ddpassword']==''){
				$key_md5webid=md5(dd_crc32(DDKEY.$webid));
				$set_con_arr[]=array('f'=>'ddpassword','v'=>$key_md5webid,'e'=>'=');
				$row['ddpassword']=$key_md5webid;
			}
			$duoduo->update('user', $set_con_arr, 'id="' . $row['uid'].'"');
			$code_uid=$row['id'];
			$md5pwd=$row['ddpassword'];
		}
		else{
			$id=(float)$duoduo->select('user','id','ddusername="'.$webname.'"');
			if($id>0){
				$webname=$webname.rand(1,9999);
			}
			$email=$webid.'@'.$web.'.com';
			$id=(float)$duoduo->select('user','id','email="'.$email.'"');
			if($id>0){
				$email=$webid.'_'.rand(1,9999).'@'.$web.'.com';
			}
			$_GET['username']=$webname;
			$_GET['type']='namereg';
			$_GET['email']=$email;
			$_GET['password']=dd_crc32(DDKEY.$webid); 
			$_GET['password2']=$_GET['password']; 
			$data=$app_class->register(0);
			$code_uid=$data['r'];
			if($data['s']==0){
				exit('错误：'.$data['r']);
			}
			$apilogin_data=array('uid'=>$data['r'],'webid'=>$webid,'webname'=>$webname,'web'=>$web);
			$duoduo->insert('apilogin',$apilogin_data);

			$md5pwd=md5($_GET['password']);
		}
		show_code_uid($code_uid,$md5pwd);
	}

function show_code_uid($uid,$md5pwd){
	?>
    <script>
	window.uexOnload=function(type)
	{if(!type){
		uexWindow.publishChannelNotification('login', "<?=SITEURL?>/plugin.php?mod=dd_chaofan&act=user&do=login&sub=1&uid=<?=$uid?>&password=<?=$md5pwd?>");
		setTimeout(function(){uexWindow.evaluateScript('page_weblogin',0,'closePage()');},1000);
	}}
    </script>
    <?php
}

$web=$_GET['web'];
		$do=$_GET['do']?$_GET['do']:'go';
		$app = $duoduo->select('api', '`key`,secret,title,code,open', 'code="'.$web.'"');
		$callback=urlencode(SITEURL.'/plugin.php?mod=dd_chaofan&act=weblogin&web='.$web.'&do=back');
		if($web=='qq'){
			if($do=='go'){
				$url="https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=".$app["key"]."&redirect_uri=".$callback. "&scope=get_user_info&display=mobile&state=wap"; //这是标准登陆网址，但是在有些安卓机打不来
				header('Location:'.$url);
			}
			elseif($do=='back'){
				$url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"."client_id=" .$app["key"]. "&redirect_uri=" . $callback."&client_secret=" . $app["secret"]. "&code=" . $_GET["code"];
				$response=get_url_contents($url);
				if (strpos($response, "callback") !== false){
					$lpos = strpos($response, "(");
            		$rpos = strrpos($response, ")");
            		$response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            		$msg = json_decode($response);
            		if (isset($msg->error)){
                		echo "<h3>error:</h3>" . $msg->error;
                		echo "<h3>msg  :</h3>" . $msg->error_description;
                		exit();
            		}
				}
				$params = array();
        		parse_str($response, $params);
				$access_token = $params["access_token"];
				
				$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=".$access_token;
				$str  = get_url_contents($graph_url);
    			if (strpos($str, "callback") !== false){
        			$lpos = strpos($str, "(");
        			$rpos = strrpos($str, ")");
        			$str  = substr($str, $lpos + 1, $rpos - $lpos -1);
    			}

    			$user = json_decode($str);
    			if (isset($user->error)){
        			echo "<h3>error:</h3>" . $user->error;
        			echo "<h3>msg  :</h3>" . $user->error_description;
        			exit();
    			}
    			$openid = $user->openid;
				if($openid==''){
					print_r($user);exit('openid为空');
				}
				
				$get_user_info = "https://graph.qq.com/user/get_user_info?"."access_token=".$access_token."&oauth_consumer_key=".$app["key"]."&openid=".$openid."&format=json";

    			$info = get_url_contents($get_user_info);
    			$arr = json_decode($info, true);

    			$name=$arr['nickname'];
    			if($name==''){$name='qq'.rand(1000,9999);} //个别情况名字会为空
    			$urlname=str_replace('%E2%80%AD','',urlencode($name)); //个别名字会带有特殊的空格
				$name=urldecode($urlname);
				
				get_user_from_web($web,$openid,$name,$duoduo);
			}
		}
		elseif($web=='sina'){
			include( DDROOT.'/api/sina/saetv2.ex.class.php' );
			include_once( DDROOT.'/api/sina/weibooauth.php' );
			$o = new SaeTOAuthV2($app['key'],$app['secret']);
			if($do=='go'){
				$url='https://open.weibo.cn/oauth2/authorize?client_id='.$app["key"].'&redirect_uri='.$callback.'&response_type=code&display=mobile';
				header('Location:'.$url);
			}
			else{
				if (isset($_GET['code'])) {
	    			$keys = array();
					$keys['code'] = $_GET['code'];
	    			$keys['redirect_uri'] = urldecode($callback);
	    			try {
		    			$token = $o->getAccessToken( 'code', $keys ) ;
	    			} 
	    			catch (OAuthException $e) {
						print_r($e);exit;
	    			}
    			}
	
				if ($token) {
	    			$c = new SaeTClientV2($app['key'],$app['secret'] , $token['access_token'] );
        			$uid_get = $c->get_uid();
        			$uid = $uid_get['uid'];
        			$user_message = $c->show_user_by_id($uid);//根据ID获取用户等基本信息
	
	    			if ($user_message['id']>0) {//使用后不能在修改下面参数否则出错
		    			$webname=$user_message['name'];
						if($webname==''){$webname=ACT.rand(1000,9999);}
		    			$webid=$user_message['id'];
        			} else {
            			dd_exit('会员信息获取失败');
        			}
					get_user_from_web($web,$webid,$webname,$duoduo);
    			}
			}
		}
		elseif($web=='renren'){
			include( DDROOT.'/api/renren/RenRenOauth.class.php');
			$o = new RenRenOauth($app['key'],$app['secret'],urldecode($callback));
			if($do=='go'){
				$code_url = $o->getAuthorizeUrl( $callback );
   				header('Location:'.$code_url.'&display=touch');
			}
			else{
	    		$keys = array();
	   			$code = $_GET['code'];
	    		$token = $o->getAccessToken($code);
				$webname=$token['user']['name'];
				$webid=$token['user']['id'];
		
				if ($webid>0) {//使用后不能在修改下面参数否则出错
		    		if($webname==''){$webname=ACT.rand(1000,9999);}
	        		get_user_from_web($web,$webid,$webname,$duoduo);
        		} else {
            		dd_exit('会员信息获取失败');
        		}
			}
		}
		elseif($web=='tb'){
			if($do=='go'){
				$url = 'https://oauth.taobao.com/authorize?response_type=code&client_id='.$app['key'].'&redirect_uri='.$callback.'&view=wap';
   				header("Location:".$url);
			}
			else{
				$code=$_GET['code'];
				if($code==''){
					if(isset($_GET['error'])){
						if($_GET['error_description']=='authorize reject'){
							exit('取消授权');
						}
						exit($_GET['error_description']);
					}
					exit('miss code');
				}
				$postfields= array('grant_type' => 'authorization_code','client_id'  => $app['key'],'client_secret' => $app['secret'],'code'=> $code,'redirect_uri' => SITEURL);
	 
				$url = 'https://oauth.taobao.com/token?'.http_build_query($postfields);
 
				$token = json_decode(dd_get($url,'post'),1);
	
				if(empty($token)){
					if(function_exists('curl_exec')){
						$a=$this->dcurl($url,$postfields);
						$token = json_decode($a,1);
						if(empty($token)){
							exit('函数不支持！');
						}
					}
					else{
						exit('函数不支持');
					}
				}
	
				if(isset($token['error'])){
					exit($token['error_description']);
				}
	
				if(!isset($token['taobao_user_nick'])){
					print_r($token);exit;
				}
	
    			$webname=urldecode($token['taobao_user_nick']);
				$webid=dd_crc32($nick_taobao);
				
				$row=$duoduo->select('apilogin','id,uid,webid','webname="'.$webname.'" and web="'.$web.'"');
				if($row['id']>0){
					if($row['webid']!=$token['taobao_user_id']){
						$data=array('webid'=>$token['taobao_user_id']);
						$duoduo->update('apilogin',$data,'id="'.$row['id'].'"');
					}
				}
				get_user_from_web($web,$webid,$webname,$duoduo);
			}
		}