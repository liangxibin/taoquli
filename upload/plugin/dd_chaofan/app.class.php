<?php
class app_class{
	public $duoduo;
	public $webset;
	
	function __construct($duoduo){
		$this->duoduo=$duoduo;
		$this->webset=$webset;
	}
	function get_url_contents($url){
		$c=fs('collect');
		if(function_exists('curl_exec')){
			$c->set_func='curl';
		}
		$c->get($url);
		$result=$c->val;
    	return $result;
	}
	
	function login(){
		$duoduo=$this->duoduo;
		$webset=$duoduo->webset;
		$ip=$_SERVER['REMOTE_ADDR'];
		$show_yzm=login_error('check');
		if($_GET['sub']==''){
			$json_data=array('s'=>1,'r'=>$show_yzm);
		}
		else{
			if (limit_ip('user_limit_ip')) {
				$json_data=array('s'=>0,'r'=>'您的IP禁止登陆');
				return $json_data;
			} 
			if($show_yzm==1){
				$yzm = trim($_GET['yzm']);
				$session_file=DDROOT.'/data/temp/session/'.date('Ymd').'/'.$yzm.'.yzm';
			
				if(!file_exists($session_file)){
					$json_data=array('s'=>0,'r'=>'验证码错误','id'=>'yzm');
					return $json_data;
				}
				else{
					$yzm_f=file_get_contents($session_file);
					$yzm_arr=explode('|',$yzm_f);
					if(TIME-$yzm_arr[1]>10*60 || $yzm_arr[0]!=$yzm){ //验证码5分钟过期
						$json_data=array('s'=>0,'r'=>'验证码错误','id'=>'yzm');
						return $json_data;
					}
					unlink($session_file);
				}
			}
			$username = trim($_GET['username']);
			$password = trim($_GET['password']);
			if(strlen($password)==32){
				$md5pwd = $password;
			}
			else{
				$md5pwd = md5($password);
			}
			
			if ($webset['ucenter']['open'] == 1) {
				include DDROOT . '/comm/uc_define.php';
				include_once DDROOT . '/uc_client/client.php';
				$uc_name = iconv("utf-8", "utf-8//IGNORE", $username);
				list ($ucid, $uc_name, $pwd, $email) = uc_user_login($uc_name, $password); //第一次查询用户名
						
				if ($ucid == -1) { // 如果失败在查询邮箱
					list ($ucid, $uc_name, $pwd, $email) = uc_user_login($username, $password, 2);
				} 
				if ($ucid > 0) {
					$duser = $duoduo -> select('user','*', 'ddusername="' . $username . '" or email="' . $username . '" and del=0');
					$id = $duser['id'];
					if (!$id) { // 不存在就插入多多
						$info['ddusername'] = $username;
						$info['ddpassword'] = $md5pwd;
						$info['email'] = $email;
						$info['regtime'] = SJ;
						$info['regip'] = get_client_ip();
						$info['lastlogintime'] = SJ;
						$info['loginnum'] = 1;
						$info['money'] = (float)$webset['user']['reg_money'];
						$info['jifen'] = (int)$webset['user']['reg_jifen'];
						$info['jifenbao'] = (float)$webset['user']['reg_jifenbao'];
						$info['ddpassword'] = $md5pwd;
						$info['tjr'] = 0;
						$info['ucid'] = $ucid;
						$info['jihuo'] = 1;
				
						$uid = $duoduo -> insert('user', $info, 0); //插入会员
						if ($uid <= 0) {
							echo '插入会员失败' . mysql_error();
							exit;
						} 
				
						if ($webset['user']['reg_money'] > 0 || $webset['user']['reg_jifen'] > 0) { // 注册送大于0时，发送明细和站内信
							unset ($info);
							$info['uid'] = $uid;
							$info['shijian'] = 1;
							$info['money'] = $webset['user']['reg_money'];
							$info['jifen'] = $webset['user']['reg_jifen'];
							$info['jifenbao'] = $webset['user']['reg_jifenbao'];
							$mingxi_id = $duoduo -> mingxi_insert($info);
							if (!$mingxi_id) {
								echo '插入明细失败';
								exit;
							} 
						} 
				
						$tg = $webset['tgbl'];
						unset($data);
						$data['uid'] = $uid;
						$data['username'] = $username;
						$data['tg'] = $tg;
						$data['webnick'] = $webset['webnick'];
						$data['email'] = $email;
						$duoduo -> msg_insert($data, 1); //1号站内信
					} elseif ($duser['ddpassword'] != md5($password)) { // 存在该会员，更新密码
						$data = array('ddpassword' => md5($password));
						$duoduo -> update('user', $data, 'id="' . $id . '"');
					}
				} 
				else {
					login_error('insert');
					$json_data=array('s'=>0,'r'=>'账号密码错误','id'=>'password');
					return $json_data;
				} 
			} 
			
			$shield_arr = dd_get_cache('no_words'); //屏蔽词语	
			$username_pass = reg_name($username, 3, 30, $shield_arr);
			if ($username_pass == -1) {
				$json_data=array('s'=>0,'r'=>'用户名不合法','id'=>'username');
				return $json_data;
			} elseif ($username_pass == -2) {
				$json_data=array('s'=>0,'r'=>'用户名不合法','id'=>'username');
				return $json_data;
			} 
			$password_pass = reg_password($password);
			if ($password_pass == 0) {
				$json_data=array('s'=>0,'r'=>'密码位数错误','id'=>'password');
				return $json_data;
			}
			$dduser = $duoduo -> select('user', '*', "(ddusername='" . $username . "' or email='" . $username . "') and ddpassword='" . $md5pwd . "' and del=0");
			$uid = $dduser['id'];
			if ($uid > 0) { // 如果会员存在
				$id = $dduser['id'];
				$username = $dduser['ddusername'];
				$email = $dduser['email'];
				$jihuo = $dduser['jihuo'];
				if ($jihuo == 0 && $webset['user']['jihuo']==1) {
					$json_data=array('s'=>0,'r'=>'您的账号需要激活');
					return $json_data;
				}
				login_error('delete');
				$json_data=array('s'=>1,'r'=>$this->return_user_info($dduser));
			}
			else {
				login_error('insert');
				$json_data=array('s'=>0,'r'=>'账号密码错误','id'=>'username');
			}
			return $json_data;
		}
	}
	
	function return_user_info($dduser){
		$duoduo=$this->duoduo;
		$webset=$duoduo->webset;
		if(is_numeric($dduser)){
			$dduser=$duoduo->select('user','*','id="'.$dduser.'"');
		}
		$a['id']=$dduser['id'];
		$a['name']=$dduser['ddusername'];
		$a['level']=$dduser['level'];
		$a['pwd']=$dduser['ddpassword'];
		$a['jifenbao']=(float)$dduser['jifenbao'];
		$a['money']=$dduser['money'];
		$a['jifen']=$dduser['jifen'];
		$a['email']=$dduser['email'];
		$a['mobile']=$dduser['mobile']==0?'':$dduser['mobile'];
		$a['qq']=$dduser['qq'];
		$a['alipay']=$dduser['alipay']==''?'':$dduser['alipay'];
		$a['tbnick']=$dduser['tbnick'];
		$a['realname']=$dduser['realname']==''?'':$dduser['realname'];
		$a['signnum']=$dduser['signnum'];
		$a['signtime']=date('Ymd',$dduser['signtime']);
		if($a['signtime']<date('Ymd',strtotime('-1 day')) && $a['signnum']>0){
			$a['signnum']=0;
			$data=array('signnum'=>$a['signnum']);
			$duoduo->update('user',$data,'id="'.$a['id'].'"');
		}
		
		if(strpos(a($a['id']),'http')===0){
			$avatar=a($a['id']);
		}
		else{
			$avatar=SITEURL.'/'.a($a['id']);
		}
		$a['avatar']=$avatar;
		$a['code_uid']=authcode($a['id'],'ENCODE');
		$a=$duoduo->freeze_user($a);
		$msg_num=$duoduo->sum('msg','uid="'.$a['id'].'" and see=0');
		$a['msg_num']=$msg_num;
		
		$web_level=back_arr($webset['level']);
		$m=WEB_USER_LEVEL-1;
		foreach($web_level as $k=>$v){
			if($a['level']>=$k){
				$dengji_img = SITEURL."/images/v".$m.".gif";
				$dengji=$m;
				break;
			}
			$m--;
		}
		$a['dengji']=$dengji;
		$a['dengji_img']=$dengji_img;
		
		$webid=$duoduo->select('apilogin','webid','uid="'.$a['id'].'"');
		$key_webid=dd_crc32(DDKEY.$webid);
		$key_md5webid=md5($key_webid);
		$md5webid=md5($webid);
		$md5pwd=$dduser['ddpassword'];
	
		$default_pwd='';
		if($key_md5webid==$md5pwd){
			$default_pwd=$key_webid;
		}
		if($md5webid==$md5pwd){
			$default_pwd=$webid;
		}
		$a['default_pwd']=$default_pwd;
	
		if($webset['tixian']['tbtxxz']>0){
			$a['tb_most'] = floor($a['live_jifenbao']/$webset['tixian']['tbtxxz'])*$webset['tixian']['tbtxxz'];
		}else{
			$a['tb_most'] = $a['live_jifenbao'];
		}
		if($a['tb_most']<$webset['tixian']['tblimit']){
			$a['tb_most']=0;
		}
		
		if($webset['tixian']['txxz']>0){
			$a['money_most'] = floor($a['live_money']/$webset['tixian']['txxz'])*$webset['tixian']['txxz'];
		}else{
			$a['money_most'] = $a['live_money'];
		}
		if($a['money_most']<$webset['tixian']['limit']){
			$a['money_most']=0;
		}
		$a['tixian']['limit']=$webset['tixian']['limit'];
		$a['tixian']['txxz']=$webset['tixian']['txxz'];
		$a['tixian']['tblimit']=$webset['tixian']['tblimit'];
		$a['tixian']['tbtxxz']=$webset['tixian']['tbtxxz'];
		
		unset($duoduo);
		unset($webset);
		return $a;
	}
	
	function register($need_yzm){
		$duoduo=$this->duoduo;
		$webset=$duoduo->webset;
		$type=$_GET['type'];
		$yzm=(int)trim($_GET['yzm']);
		if($type=='namereg'){
			$username=trim($_GET['username']);
			$email=trim($_GET['email']);
			$yzm_file_name=$yzm;
		}
		else{
			$mobile=$_GET['mobile'];
			$info['mobile'] = $mobile;
			$username=$mobile;
			$email=$mobile.'@mobile.com';
			$yzm_file_name=$mobile;
		}
		$password = trim($_GET['password']);
		$password2 = trim($_GET['password2']);
		if($password!=$password2){
			$json_data=array('s'=>0,'r'=>'2次密码不同','id'=>'password');
			return $json_data;
		}
		$md5pwd = md5($password);
		$qq = trim($_GET['qq']);
		$alipay = trim($_GET['alipay']);
		$tjrname = trim($_GET['tjrname']);
		$tbnick = trim($_GET['tbnick']);
		$ip = get_client_ip();
		if($webset['user']['need_tbnick']==1 && $tbnick==''){
			$json_data=array('s'=>0,'r'=>'请填写淘宝账号或订单号','id'=>'tbnick');
			return $json_data;
		}
		$tjr=0;
		if($tjrname!=''){
			$tjr=$duoduo->select('user','id','ddusername="'.$tjrname.'" or email="'.$tjrname.'"');
		}
		if (limit_ip('user_limit_ip', $ip)) {
			$json_data=array('s'=>0,'r'=>'禁用IP');
			return $json_data;
		}
		
		$regnum=$duoduo->count('user','regtime>"'.date('Y-m-d 00:00:00').'"');
		if($need_yzm==1 && $regnum>=3){
			$session_file=DDROOT.'/data/temp/session/'.date('Ymd').'/'.$yzm_file_name.'.yzm';
			
			if(!file_exists($session_file)){
				$json_data=array('s'=>0,'r'=>'验证码错误','id'=>'yzm');
				return $json_data;
			}
			else{
				$yzm_f=file_get_contents($session_file);
				$yzm_arr=explode('|',$yzm_f);
				if(TIME-$yzm_arr[1]>10*60 || $yzm_arr[0]!=$yzm){ //验证码5分钟过期
					$json_data=array('s'=>0,'r'=>'验证码错误','id'=>'yzm');
					return $json_data;
				}
				unlink($session_file);
			}
		}
		
		$username_pass = reg_name($username, 3, 15, $shield_arr);
		if ($username_pass == -1) {
			$json_data=array('s'=>0,'r'=>'用户名不合法','id'=>'username');
			return $json_data;
		}
		elseif ($username_pass == -2) {
			$json_data=array('s'=>0,'r'=>'包含非法词汇','id'=>'username');
			return $json_data;
		}
		elseif ($duoduo->check_user($username) == 'false') {
			$json_data=array('s'=>0,'r'=>'用户名已存在','id'=>'username');
			return $json_data;
		}
		$password_pass = reg_password($password);
		if ($password_pass == 0) {
			$json_data=array('s'=>0,'r'=>'密码位数错误','id'=>'password');
			return $json_data;
		}
		$email_pass = reg_email($email);
		if ($email_pass == 0) {
			$json_data=array('s'=>0,'r'=>'邮箱格式错误','id'=>'email');
			return $json_data;
		}
		elseif ($duoduo->check_email($email) == 'false') {
			$json_data=array('s'=>0,'r'=>'邮箱已存在','id'=>'email');
			return $json_data;
		}
		if ($webset['user']['need_qq'] == 1) {
			$qq_pass = reg_qq($qq);
			if ($qq_pass == 0) {
				$json_data=array('s'=>0,'r'=>'QQ格式错误','id'=>'qq');
				return $json_data;
			}
		}
		if ($webset['user']['need_alipay'] == 1) {
			$alipay_pass = reg_alipay($alipay);
			if ($alipay_pass == 0) {
				$json_data=array('s'=>0,'r'=>'支付宝格式错误','id'=>'alipay');
				return $json_data;
			}
			elseif ($duoduo->check_alipay($alipay) == 'false') {
				$json_data=array('s'=>0,'r'=>'支付宝已存在','id'=>'alipay');
				return $json_data;
			}
		}
		if ($webset['user']['reg_between'] > 0) {
			$regtime = $duoduo->select('user', 'regtime', 'regip="' . $ip . '" order by id desc');
			$regtime = strtotime($regtime);
			if ($regtime > 0 && TIME - $regtime < $webset['user']['reg_between'] * 3600) {
				$json_data=array('s'=>0,'r'=>'注册受限');
				return $json_data;
			}
		}
		
		if ($webset['ucenter']['open'] == 1) {
			include DDROOT . '/comm/uc_define.php';
			include_once DDROOT . '/uc_client/client.php';
			$uc_name = iconv("utf-8", "utf-8//IGNORE", $username);
			$ucid = uc_user_register($uc_name, $password, $email);
		
			if ($ucid == -1) {
				jump(-1, '用户名不合法');
			}
			elseif ($ucid == -2) {
				jump(-1, '包含非法词汇');
			}
			elseif ($ucid == -3) {
				jump(-1, '用户名已存在');
			}
			elseif ($ucid == -4) {
				jump(-1, '邮箱格式错误');
			}
			elseif ($ucid == -5) {
				jump(-1, '邮箱格式错误');
			}
			elseif ($ucid == -6) {
				jump(-1, '邮箱已存在');
			}
			elseif ($ucid <= 0) {
				jump(-1, '邮箱已存在');
			}
		} else {
			$ucid = 0;
		}
		
		$info['ddusername'] = $username;
		$info['ddpassword'] = $md5pwd;
		$info['email'] = $email;
		$info['qq'] = $qq;
		$info['tbnick'] = $tbnick;
		$info['alipay'] = $alipay;
		$info['mobile'] = $mobile;
		$info['tjr'] = $tjr;
		$info['regtime'] = SJ;
		$info['regip'] = $ip;
		$info['lastlogintime'] = SJ;
		$info['loginnum'] = 1;
		$info['money'] = (float) $webset['user']['reg_money'];
		$info['jifenbao'] = (float) $webset['user']['reg_jifenbao'];
		$info['jifen'] = (int) $webset['user']['reg_jifen'];
		$info['level'] = (int) $webset['user']['reg_level'];
		$info['ddpassword'] = $md5pwd;
		$info['ddusername'] = $username;
		$info['tjr'] = $tjr;
		$info['ucid'] = $ucid;
		
		if ($webset['user']['jihuo'] == 1) { //如果需要激活，会员初始的激活状态为0
			$info['jihuo'] = 0;
		} else {
			$info['jihuo'] = 1;
		}
		$uid = $duoduo->insert('user', $info, 0); //插入会员
		if ($uid <= 0) {
			$json_data=array('s'=>0,'r'=>'插入会员失败');
			return $json_data;
		}
		
		if($webset['user']['need_tbnick']==1 && $tbnick!=''){
			$trade_uid=get_4_tradeid($tbnick);
			if($trade_uid[0]>0){
				$duoduo->trade_uid($uid,$trade_uid[0]);
			}
		}
		
		$tg = $webset['tgbl'];
		if ($webset['user']['jihuo'] == 0) { //如果需要激活，会员初始的激活状态为0
			unset ($data);
			$data['uid'] = $uid;
			$data['username'] = $username;
			$data['tg'] = $tg;
			$data['webnick'] = WEBNAME;
			$data['email'] = $email;
			$msg_zhuce = $duoduo->msg_insert($data, 1); //1号站内信
		}
		
		if ($webset['user']['reg_money'] > 0 || $webset['user']['reg_jifen'] > 0 || $webset['user']['reg_jifenbao'] > 0) { //注册送大于0时，发送明细
			unset ($info);
			$info['uid'] = $uid;
			$info['shijian'] = 1;
			$info['money'] = (float) $webset['user']['reg_money'];
			$info['jifenbao'] = (float) $webset['user']['reg_jifenbao'];
			$info['jifen'] = (int) $webset['user']['reg_jifen'];
			$mingxi_id = $duoduo->mingxi_insert($info);
			if (!$mingxi_id) {
				$json_data=array('s'=>0,'r'=>'插入明细失败');
				return $json_data;
			}
		}
		return array('s'=>1,'r'=>$uid);
	}
}
$app_class=new app_class($duoduo);
