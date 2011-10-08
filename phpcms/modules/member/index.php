<?php
/**
 * ��Աǰ̨�������ġ��˺Ź������ղز�����
 */

defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('foreground');
pc_base::load_sys_class('format', '', 0);
pc_base::load_sys_class('form', '', 0);

class index extends foreground {

	private $times_db;
	
	function __construct() {
		parent::__construct();
		$this->http_user_agent = str_replace('7.0' ,'8.0',$_SERVER['HTTP_USER_AGENT']);
	}

	public function init() {
		$memberinfo = $this->memberinfo;
		//��ʼ��phpsso
		$phpsso_api_url = $this->_init_phpsso();
		//��ȡͷ������
		$avatar = $this->client->ps_getavatar($this->memberinfo['phpssouid']);

		$grouplist = getcache('grouplist');
		$memberinfo['groupname'] = $grouplist[$memberinfo[groupid]]['name'];

		include template('member', 'index');
	}
	
	public function register() {
		$this->_session_start();
		//�����û�ģ������
		$member_setting = getcache('member_setting');
		if(!$member_setting['allowregister']) {
			showmessage(L('deny_register'), 'index.php?m=member&c=index&a=login');
		}
		
		//��ȡ�û�siteid
		$siteid = isset($_REQUEST['siteid']) && trim($_REQUEST['siteid']) ? intval($_REQUEST['siteid']) : 1;
		//����վ��id����
		if (!defined('SITEID')) {
		   define('SITEID', $siteid);
		}
		
		header("Cache-control: private");
		if(isset($_POST['dosubmit'])) {
			if (empty($_SESSION['connectid']) && $_SESSION['code'] != strtolower($_POST['code'])) {
				showmessage(L('code_error'));
			}
			$userinfo = array();
			$userinfo['encrypt'] = create_randomstr(6);

			$userinfo['username'] = (isset($_POST['username']) && is_username($_POST['username'])) ? $_POST['username'] : exit('0');
			$userinfo['nickname'] = (isset($_POST['nickname']) && is_username($_POST['nickname'])) ? $_POST['nickname'] : '';
			
			$userinfo['email'] = (isset($_POST['email']) && is_email($_POST['email'])) ? $_POST['email'] : exit('0');
			$userinfo['password'] = isset($_POST['password']) ? $_POST['password'] : exit('0');
			
			$userinfo['email'] = (isset($_POST['email']) && is_email($_POST['email'])) ? $_POST['email'] : exit('0');

			$userinfo['modelid'] = isset($_POST['modelid']) ? intval($_POST['modelid']) : 10;
			$userinfo['regip'] = ip();
			$userinfo['point'] = $member_setting['defualtpoint'] ? $member_setting['defualtpoint'] : 0;
			$userinfo['amount'] = $member_setting['defualtamount'] ? $member_setting['defualtamount'] : 0;
			$userinfo['regdate'] = $userinfo['lastdate'] = SYS_TIME;
			$userinfo['siteid'] = $siteid;
			$userinfo['connectid'] = isset($_SESSION['connectid']) ? $_SESSION['connectid'] : '';
			$userinfo['from'] = isset($_SESSION['from']) ? $_SESSION['from'] : '';
			unset($_SESSION['connectid'], $_SESSION['from']);
			
			if($member_setting['enablemailcheck']) {	//�Ƿ���Ҫ�ʼ���֤
				$userinfo['groupid'] = 7;
			} elseif($member_setting['registerverify']) {	//�Ƿ���Ҫ����Ա���
				$userinfo['modelinfo'] = isset($_POST['info']) ? array2string($_POST['info']) : '';
				$this->verify_db = pc_base::load_model('member_verify_model');
				unset($userinfo['lastdate'],$userinfo['connectid'],$userinfo['from']);
				$this->verify_db->insert($userinfo);
				showmessage(L('operation_success'), 'index.php?m=member&c=index&a=register&t=3');
			} else {
				$userinfo['groupid'] = $this->_get_usergroup_bypoint($userinfo['point']);
			}
			
			if(pc_base::load_config('system', 'phpsso')) {
				$this->_init_phpsso();
				$status = $this->client->ps_member_register($userinfo['username'], $userinfo['password'], $userinfo['email'], $userinfo['regip'], $userinfo['encrypt']);
				if($status > 0) {
					$userinfo['phpssouid'] = $status;
					//����phpssoΪ�������룬���ܺ����phpcms_v9
					$userinfo['password'] = password($userinfo['password'], $userinfo['encrypt']);
					$userid = $this->db->insert($userinfo, 1);
					if($member_setting['choosemodel']) {	//�������ѡ��ģ��
						//ͨ��ģ�ͻ�ȡ��Ա��Ϣ					
						require_once CACHE_MODEL_PATH.'member_input.class.php';
				        require_once CACHE_MODEL_PATH.'member_update.class.php';
						$member_input = new member_input($userinfo['modelid']);
						$user_model_info = $member_input->get($_POST['info']);
						$user_model_info['userid'] = $userid;
	
						//�����Աģ������
						$this->db->set_model($userinfo['modelid']);
						$this->db->insert($user_model_info);
					}
					
					if($userid > 0) {
						//ִ�е�½����
						if(!$cookietime) $get_cookietime = param::get_cookie('cookietime');
						$_cookietime = $cookietime ? intval($cookietime) : ($get_cookietime ? $get_cookietime : 0);
						$cookietime = $_cookietime ? TIME + $_cookietime : 0;
						
						if($userinfo['groupid'] == 7) {
							param::set_cookie('_username', $userinfo['username'], $cookietime);
							param::set_cookie('email', $userinfo['email'], $cookietime);							
						} else {
							$phpcms_auth_key = md5(pc_base::load_config('system', 'auth_key').$this->http_user_agent);
							$phpcms_auth = sys_auth($userid."\t".$userinfo['password'], 'ENCODE', $phpcms_auth_key);
							
							param::set_cookie('auth', $phpcms_auth, $cookietime);
							param::set_cookie('_userid', $userid, $cookietime);
							param::set_cookie('_username', $userinfo['username'], $cookietime);
							param::set_cookie('_nickname', $userinfo['nickname'], $cookietime);
							param::set_cookie('_groupid', $userinfo['groupid'], $cookietime);
							param::set_cookie('cookietime', $_cookietime, $cookietime);
						}
					}
					//�����Ҫ������֤
					if($member_setting['enablemailcheck']) {
						pc_base::load_sys_func('mail');
						$phpcms_auth_key = md5(pc_base::load_config('system', 'auth_key').$this->http_user_agent);
						$code = sys_auth($userid.'|'.md5($phpcms_auth_key), 'ENCODE', $phpcms_auth_key);
						$url = APP_PATH."index.php?m=member&c=index&a=register&code=$code&verify=1";
						$message = $member_setting['registerverifymessage'];
						$message = str_replace(array('{click}','{url}'), array('<a href="'.$url.'">'.L('please_click').'</a>',$url), $message);
						
						sendmail($userinfo['email'], L('reg_verify_email'), $message);
						showmessage(L('operation_success'), 'index.php?m=member&c=index&a=register&t=2');
					} else {
						//�������Ҫ������֤��ֱ�ӵ�¼����Ӧ��
						$synloginstr = $this->client->ps_member_synlogin($userinfo['phpssouid']);
						showmessage(L('operation_success').$synloginstr, 'index.php?m=member&c=index&a=init');
					}
					
				}
			} else {
				showmessage(L('enable_register').L('enable_phpsso'), 'index.php?m=member&c=index&a=login');
			}
			showmessage(L('operation_failure'), HTTP_REFERER);
		} else {
			if(!pc_base::load_config('system', 'phpsso')) {
				showmessage(L('enable_register').L('enable_phpsso'), 'index.php?m=member&c=index&a=login');
			}
			
			if(!empty($_GET['verify'])) {
				$code = isset($_GET['code']) ? trim($_GET['code']) : showmessage(L('operation_failure'), 'index.php?m=member&c=index');
				$phpcms_auth_key = md5(pc_base::load_config('system', 'auth_key').$this->http_user_agent);
				$code_res = sys_auth($code, 'DECODE', $phpcms_auth_key);
				$code_arr = explode('|', $code_res);
				$userid = isset($code_arr[0]) ? $code_arr[0] : '';
				$userid = is_numeric($userid) ? $userid : showmessage(L('operation_failure'), 'index.php?m=member&c=index');

				$this->db->update(array('groupid'=>$this->_get_usergroup_bypoint()), array('userid'=>$userid));
				showmessage(L('operation_success'), 'index.php?m=member&c=index');
			} elseif(!empty($_GET['protocol'])) {

				include template('member', 'protocol');
			} else {
				//���˷ǵ�ǰվ���Աģ��
				$modellist = getcache('member_model', 'commons');
				foreach($modellist as $k=>$v) {
					if($v['siteid']!=$siteid || $v['disabled']) {
						unset($modellist[$k]);
					}
				}
				if(empty($modellist)) {
					showmessage(L('site_have_no_model').L('deny_register'), HTTP_REFERER);
				}
				//�Ƿ���ѡ���Աģ��ѡ��
				if($member_setting['choosemodel']) {
					$first_model = array_pop(array_reverse($modellist));
					$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : $first_model['modelid'];

					if(array_key_exists($modelid, $modellist)) {
						//��ȡ��Աģ�ͱ���
						require CACHE_MODEL_PATH.'member_form.class.php';
						$member_form = new member_form($modelid);
						$this->db->set_model($modelid);
						$forminfos = $forminfos_arr = $member_form->get();

						//�����ֶι���
						foreach($forminfos as $field=>$info) {
							if($info['isomnipotent']) {
								unset($forminfos[$field]);
							} else {
								if($info['formtype']=='omnipotent') {
									foreach($forminfos_arr as $_fm=>$_fm_value) {
										if($_fm_value['isomnipotent']) {
											$info['form'] = str_replace('{'.$_fm.'}',$_fm_value['form'], $info['form']);
										}
									}
									$forminfos[$field]['form'] = $info['form'];
								}
							}
						}
						
						$formValidator = $member_form->formValidator;
					}
				}
				$description = $modellist[$modelid]['description'];
				include template('member', 'register');
			}
		}
	}

	public function account_manage() {
		$memberinfo = $this->memberinfo;
		//��ʼ��phpsso
		$phpsso_api_url = $this->_init_phpsso();
		//��ȡͷ������
		$avatar = $this->client->ps_getavatar($this->memberinfo['phpssouid']);
	
		$grouplist = getcache('grouplist');
		$member_model = getcache('member_model', 'commons');

		//��ȡ�û�ģ������
		$this->db->set_model($this->memberinfo['modelid']);
		$member_modelinfo_arr = $this->db->get_one(array('userid'=>$this->memberinfo['userid']));
		$model_info = getcache('model_field_'.$this->memberinfo['modelid'], 'model');
		foreach($model_info as $k=>$v) {
			if($v['formtype'] == 'omnipotent') continue;
			if($v['formtype'] == 'image') {
				$member_modelinfo[$v['name']] = "<a href='$member_modelinfo_arr[$k]' target='_blank'><img src='$member_modelinfo_arr[$k]' height='40' widht='40' onerror=\"this.src='$phpsso_api_url/statics/images/member/nophoto.gif'\"></a>";
			} elseif($v['formtype'] == 'datetime' && $v['fieldtype'] == 'int') {	//���Ϊ�����ֶ�
				$member_modelinfo[$v['name']] = format::date($member_modelinfo_arr[$k], $v['format'] == 'Y-m-d H:i:s' ? 1 : 0);
			} elseif($v['formtype'] == 'images') {
				$tmp = string2array($member_modelinfo_arr[$k]);
				$member_modelinfo[$v['name']] = '';
				if(is_array($tmp)) {
					foreach ($tmp as $tv) {
						$member_modelinfo[$v['name']] .= " <a href='$tv[url]' target='_blank'><img src='$tv[url]' height='40' widht='40' onerror=\"this.src='$phpsso_api_url/statics/images/member/nophoto.gif'\"></a>";
					}
					unset($tmp);
				}
			} elseif($v['formtype'] == 'box') {	//box�ֶΣ���ȡ�ֶ����ƺ�ֵ������
				$tmp = explode("\n",$v['options']);
				if(is_array($tmp)) {
					foreach($tmp as $boxv) {
						$box_tmp_arr = explode('|', trim($boxv));
						if(is_array($box_tmp_arr) && isset($box_tmp_arr[1]) && isset($box_tmp_arr[0])) {
							$box_tmp[$box_tmp_arr[1]] = $box_tmp_arr[0];
							$tmp_key = intval($member_modelinfo_arr[$k]);
						}
					}
				}
				if(isset($box_tmp[$tmp_key])) {
					$member_modelinfo[$v['name']] = $box_tmp[$tmp_key];
				} else {
					$member_modelinfo[$v['name']] = $member_modelinfo_arr[$k];
				}
				unset($tmp, $tmp_key, $box_tmp, $box_tmp_arr);
			} elseif($v['formtype'] == 'linkage') {	//���Ϊ�����˵�
				$tmp = string2array($v['setting']);
				$tmpid = $tmp['linkageid'];
				$linkagelist = getcache($tmpid, 'linkage');
				$fullname = $this->_get_linkage_fullname($member_modelinfo_arr[$k], $linkagelist);

				$member_modelinfo[$v['name']] = substr($fullname, 0, -1);
				unset($tmp, $tmpid, $linkagelist, $fullname);
			} else {
				$member_modelinfo[$v['name']] = $member_modelinfo_arr[$k];
			}
		}

		include template('member', 'account_manage');
	}

	public function account_manage_avatar() {
		$memberinfo = $this->memberinfo;
		//��ʼ��phpsso
		$phpsso_api_url = $this->_init_phpsso();
		$ps_auth_key = pc_base::load_config('system', 'phpsso_auth_key');
		$auth_data = $this->client->auth_data(array('uid'=>$this->memberinfo['phpssouid'], 'ps_auth_key'=>$ps_auth_key), '', $ps_auth_key);
		$upurl = base64_encode($phpsso_api_url.'/index.php?m=phpsso&c=index&a=uploadavatar&auth_data='.$auth_data);
		//��ȡͷ������
		$avatar = $this->client->ps_getavatar($this->memberinfo['phpssouid']);
		
		include template('member', 'account_manage_avatar');
	}

	public function account_manage_security() {
		$memberinfo = $this->memberinfo;
		include template('member', 'account_manage_security');
	}
	
	public function account_manage_info() {
		if(isset($_POST['dosubmit'])) {
			//�����û��ǳ�
			$nickname = isset($_POST['nickname']) && trim($_POST['nickname']) ? trim($_POST['nickname']) : '';
			if($nickname) {
				$this->db->update(array('nickname'=>$nickname), array('userid'=>$this->memberinfo['userid']));
				if(!isset($cookietime)) {
					$get_cookietime = param::get_cookie('cookietime');
				}
				$_cookietime = $cookietime ? intval($cookietime) : ($get_cookietime ? $get_cookietime : 0);
				$cookietime = $_cookietime ? TIME + $_cookietime : 0;
				param::set_cookie('_nickname', $nickname, $cookietime);
			}
			require_once CACHE_MODEL_PATH.'member_input.class.php';
			require_once CACHE_MODEL_PATH.'member_update.class.php';
			$member_input = new member_input($this->memberinfo['modelid']);
			$modelinfo = $member_input->get($_POST['info']);

			$this->db->set_model($this->memberinfo['modelid']);
			$membermodelinfo = $this->db->get_one(array('userid'=>$this->memberinfo['userid']));
			if(!empty($membermodelinfo)) {
				$this->db->update($modelinfo, array('userid'=>$this->memberinfo['userid']));
			} else {
				$modelinfo['userid'] = $this->memberinfo['userid'];
				$this->db->insert($modelinfo);
			}
			
			showmessage(L('operation_success'), HTTP_REFERER);
		} else {
			$memberinfo = $this->memberinfo;
			//��ȡ��Աģ�ͱ���
			require CACHE_MODEL_PATH.'member_form.class.php';
			$member_form = new member_form($this->memberinfo['modelid']);
			$this->db->set_model($this->memberinfo['modelid']);
			
			$membermodelinfo = $this->db->get_one(array('userid'=>$this->memberinfo['userid']));
			$forminfos = $forminfos_arr = $member_form->get($membermodelinfo);

			//�����ֶι���
			foreach($forminfos as $field=>$info) {
				if($info['isomnipotent']) {
					unset($forminfos[$field]);
				} else {
					if($info['formtype']=='omnipotent') {
						foreach($forminfos_arr as $_fm=>$_fm_value) {
							if($_fm_value['isomnipotent']) {
								$info['form'] = str_replace('{'.$_fm.'}',$_fm_value['form'], $info['form']);
							}
						}
						$forminfos[$field]['form'] = $info['form'];
					}
				}
			}
						
			$formValidator = $member_form->formValidator;

			include template('member', 'account_manage_info');
		}
	}
	
	public function account_manage_password() {
		if(isset($_POST['dosubmit'])) {
			if(!is_password($_POST['info']['password'])) {
				showmessage(L('password_format_incorrect'), HTTP_REFERER);
			}
			if($this->memberinfo['password'] != password($_POST['info']['password'], $this->memberinfo['encrypt'])) {
				showmessage(L('old_password_incorrect'), HTTP_REFERER);
			}
			//�޸Ļ�Ա����
			if($this->memberinfo['email'] != $_POST['info']['email'] && is_email($_POST['info']['email'])) {
				$email = $_POST['info']['email'];
				$updateinfo['email'] = $_POST['info']['email'];
			} else {
				$email = '';
			}
			$newpassword = password($_POST['info']['newpassword'], $this->memberinfo['encrypt']);
			$updateinfo['password'] = $newpassword;
			
			$this->db->update($updateinfo, array('userid'=>$this->memberinfo['userid']));
			if(pc_base::load_config('system', 'phpsso')) {
				//��ʼ��phpsso
				$this->_init_phpsso();
				$res = $this->client->ps_member_edit('', $email, $_POST['info']['password'], $_POST['info']['newpassword'], $this->memberinfo['phpssouid'], $this->memberinfo['encrypt']);
			}

			showmessage(L('operation_success'), HTTP_REFERER);
		} else {
			$show_validator = true;
			$memberinfo = $this->memberinfo;
			
			include template('member', 'account_manage_password');
		}
	}
	
	public function account_manage_upgrade() {
		$memberinfo = $this->memberinfo;
		$grouplist = getcache('grouplist');
		if(empty($grouplist[$memberinfo['groupid']]['allowupgrade'])) {
			showmessage(L('deny_upgrade'), HTTP_REFERER);
		}
		if(isset($_POST['upgrade_type']) && intval($_POST['upgrade_type']) < 0) {
			showmessage(L('operation_failure'), HTTP_REFERER);
		}

		if(isset($_POST['upgrade_date']) && intval($_POST['upgrade_date']) < 0) {
			showmessage(L('operation_failure'), HTTP_REFERER);
		}

		if(isset($_POST['dosubmit'])) {
			$groupid = isset($_POST['groupid']) ? intval($_POST['groupid']) : showmessage(L('operation_failure'), HTTP_REFERER);
			
			$upgrade_type = isset($_POST['upgrade_type']) ? intval($_POST['upgrade_type']) : showmessage(L('operation_failure'), HTTP_REFERER);
			$upgrade_date = !empty($_POST['upgrade_date']) ? intval($_POST['upgrade_date']) : showmessage(L('operation_failure'), HTTP_REFERER);

			//�������ͣ����ꡢ���¡����գ��۸�
			$typearr = array($grouplist[$groupid]['price_y'], $grouplist[$groupid]['price_m'], $grouplist[$groupid]['price_d']);
			//�������ͣ����ꡢ���¡����գ�ʱ��
			$typedatearr = array('366', '31', '1');
			//���ѵļ۸�
			$cost = $typearr[$upgrade_type]*$upgrade_date;
			//����ʱ��
			$buydate = $typedatearr[$upgrade_type]*$upgrade_date*86400;
			$overduedate = $memberinfo['overduedate'] > SYS_TIME ? ($memberinfo['overduedate']+$buydate) : (SYS_TIME+$buydate);

			if($memberinfo['amount'] >= $cost) {
				$this->db->update(array('groupid'=>$groupid, 'overduedate'=>$overduedate, 'vip'=>1), array('userid'=>$memberinfo['userid']));
				//���Ѽ�¼
				pc_base::load_app_class('spend','pay',0);
				spend::amount($cost, L('allowupgrade'), $memberinfo['userid'], $memberinfo['username']);
				showmessage(L('operation_success'), 'index.php?m=member&c=index&a=init');
			} else {
				showmessage(L('operation_failure'), HTTP_REFERER);
			}

		} else {
			
			$groupid = isset($_GET['groupid']) ? intval($_GET['groupid']) : '';
			//��ʼ��phpsso
			$phpsso_api_url = $this->_init_phpsso();
			//��ȡͷ������
			$avatar = $this->client->ps_getavatar($this->memberinfo['phpssouid']);
			
			
			$memberinfo['groupname'] = $grouplist[$memberinfo[groupid]]['name'];
			$memberinfo['grouppoint'] = $grouplist[$memberinfo[groupid]]['point'];
			unset($grouplist[$memberinfo['groupid']]);
			include template('member', 'account_manage_upgrade');
		}
	}
	
	public function login() {
		$this->_session_start();
		//��ȡ�û�siteid
		$siteid = isset($_REQUEST['siteid']) && trim($_REQUEST['siteid']) ? intval($_REQUEST['siteid']) : 1;
		//����վ��id����
		if (!defined('SITEID')) {
		   define('SITEID', $siteid);
		}
		
		if(isset($_POST['dosubmit'])) {
			if(empty($_SESSION['connectid'])) {
				//�ж���֤��
				$code = isset($_POST['code']) && trim($_POST['code']) ? trim($_POST['code']) : showmessage(L('input_code'), HTTP_REFERER);
				if ($_SESSION['code'] != strtolower($code)) {
					showmessage(L('code_error'), HTTP_REFERER);
				}
			}
			
			$username = isset($_POST['username']) && trim($_POST['username']) ? trim($_POST['username']) : showmessage(L('username_empty'), HTTP_REFERER);
			$password = isset($_POST['password']) && trim($_POST['password']) ? trim($_POST['password']) : showmessage(L('password_empty'), HTTP_REFERER);
			$synloginstr = ''; //ͬ����½js����
			
			if(pc_base::load_config('system', 'phpsso')) {
				$this->_init_phpsso();
				$status = $this->client->ps_member_login($username, $password);
				$memberinfo = unserialize($status);
				
				if(isset($memberinfo['uid'])) {
					//��ѯ�ʺ�
					$r = $this->db->get_one(array('phpssouid'=>$memberinfo['uid']));
					if(!$r) {
						//�����Ա��ϸ��Ϣ����Ա������ �����Ա
						$info = array(
									'phpssouid'=>$memberinfo['uid'],
						 			'username'=>$memberinfo['username'],
						 			'password'=>$memberinfo['password'],
						 			'encrypt'=>$memberinfo['random'],
						 			'email'=>$memberinfo['email'],
						 			'regip'=>$memberinfo['regip'],
						 			'regdate'=>$memberinfo['regdate'],
						 			'lastip'=>$memberinfo['lastip'],
						 			'lastdate'=>$memberinfo['lastdate'],
						 			'groupid'=>$this->_get_usergroup_bypoint(),	//��ԱĬ����
						 			'modelid'=>10,	//��ͨ��Ա
									);
									
						//�����connect�û�
						if(!empty($_SESSION['connectid'])) {
							$userinfo['connectid'] = $_SESSION['connectid'];
						}
						if(!empty($_SESSION['from'])) {
							$userinfo['from'] = $_SESSION['from'];
						}
						unset($_SESSION['connectid'], $_SESSION['from']);
						
						$this->db->insert($info);
						unset($info);
						$r = $this->db->get_one(array('phpssouid'=>$memberinfo['uid']));
					}
					$password = $r['password'];
					$synloginstr = $this->client->ps_member_synlogin($r['phpssouid']);
				} else {
					if($status == -1) {	//�û�������
						showmessage(L('user_not_exist'), 'index.php?m=member&c=index&a=login');
					} elseif($status == -2) { //�������
						showmessage(L('password_error'), 'index.php?m=member&c=index&a=login');
					} else {
						showmessage(L('login_failure'), 'index.php?m=member&c=index&a=login');
					}
				}
				
			} else {
				//�������ʣ�����Դ���
				$this->times_db = pc_base::load_model('times_model');
				$rtime = $this->times_db->get_one(array('username'=>$username));
				if($rtime['times'] > 4) {
					$minute = 60 - floor((SYS_TIME - $rtime['logintime']) / 60);
					showmessage(L('wait_1_hour', array('minute'=>$minute)));
				}
				
				//��ѯ�ʺ�
				$r = $this->db->get_one(array('username'=>$username));

				if(!$r) showmessage(L('user_not_exist'),'index.php?m=member&c=index&a=login');
				
				//��֤�û�����
				$password = md5(md5(trim($password)).$r['encrypt']);
				if($r['password'] != $password) {				
					$ip = ip();
					if($rtime && $rtime['times'] < 5) {
						$times = 5 - intval($rtime['times']);
						$this->times_db->update(array('ip'=>$ip, 'times'=>'+=1'), array('username'=>$username));
					} else {
						$this->times_db->insert(array('username'=>$username, 'ip'=>$ip, 'logintime'=>SYS_TIME, 'times'=>1));
						$times = 5;
					}
					showmessage(L('password_error', array('times'=>$times)), 'index.php?m=member&c=index&a=login', 3000);
				}
				$this->times_db->delete(array('username'=>$username));
			}
			
			//����û�������
			if($r['islock']) {
				showmessage(L('user_is_lock'));
			}
			
			$userid = $r['userid'];
			$groupid = $r['groupid'];
			$username = $r['username'];
			$nickname = empty($r['nickname']) ? $username : $r['nickname'];
			
			$updatearr = array('lastip'=>ip(), 'lastdate'=>SYS_TIME);
			//vip���ڣ�����vip�ͻ�Ա��
			if($r['overduedate'] < SYS_TIME) {
				$updatearr['vip'] = 0;
			}		

			//����û����֣��������û��飬��ȥ������֤����ֹ���ʡ��ο����û���vip�û�
			if($r['point'] >= 0 && !in_array($r['groupid'], array('1', '7', '8')) && empty($r[vip])) {
				$check_groupid = $this->_get_usergroup_bypoint($r['point']);

				if($check_groupid != $r['groupid']) {
					$updatearr['groupid'] = $groupid = $check_groupid;
				}
			}

			//�����connect�û�
			if(!empty($_SESSION['connectid'])) {
				$updatearr['connectid'] = $_SESSION['connectid'];
			}
			if(!empty($_SESSION['from'])) {
				$updatearr['from'] = $_SESSION['from'];
			}
			unset($_SESSION['connectid'], $_SESSION['from']);
						
			$this->db->update($updatearr, array('userid'=>$userid));
			
			if(!isset($cookietime)) {
				$get_cookietime = param::get_cookie('cookietime');
			}
			$_cookietime = $cookietime ? intval($cookietime) : ($get_cookietime ? $get_cookietime : 0);
			$cookietime = $_cookietime ? TIME + $_cookietime : 0;
			
			$phpcms_auth_key = md5(pc_base::load_config('system', 'auth_key').$this->http_user_agent);
			$phpcms_auth = sys_auth($userid."\t".$password, 'ENCODE', $phpcms_auth_key);
			
			param::set_cookie('auth', $phpcms_auth, $cookietime);
			param::set_cookie('_userid', $userid, $cookietime);
			param::set_cookie('_username', $username, $cookietime);
			param::set_cookie('_groupid', $groupid, $cookietime);
			param::set_cookie('_nickname', $nickname, $cookietime);
			param::set_cookie('cookietime', $_cookietime, $cookietime);
			$forward = isset($_POST['forward']) && !empty($_POST['forward']) ? urldecode($_POST['forward']) : 'index.php?m=member&c=index';
			showmessage(L('login_success').$synloginstr, $forward);
		} else {
			$setting = pc_base::load_config('system');
			$forward = isset($_GET['forward']) && trim($_GET['forward']) ? urlencode($_GET['forward']) : '';
			
			if(!empty($setting['connect_enable'])) {
				$snda_res = $this->_snda_get_appid();
				$appid = $snda_res['appid'];
				$secretkey = $snda_res['secretkey'];
				$sid = md5("appArea=0appId=".$appid."service=".urlencode(APP_PATH.'index.php?m=member&c=index&a=public_snda_login&forward='.$forward).$secretkey);
				$sndaurl = "https://cas.sdo.com/cas/login?gateway=true&service=".urlencode(APP_PATH.'index.php?m=member&c=index&a=public_snda_login&forward='.$forward)."&appId=".$appid."&appArea=0&sid=".$sid;
			}
			$siteid = isset($_REQUEST['siteid']) && trim($_REQUEST['siteid']) ? intval($_REQUEST['siteid']) : 1;
			$siteinfo = siteinfo($siteid);

			include template('member', 'login');
		}
	}
	
	/**
	 * 
	 * ��ȡ�������appid��secretkey,Ĭ��appidΪ200037400
	 */
	private function _snda_get_appid() {
		$snda_res = pc_base::load_config('snda', 'snda_status');
		if(strstr($snda_res, '|')) {
			$snda_res_arr = explode('|', $snda_res);
			$appid = isset($snda_res_arr[0]) ? $snda_res_arr[0] : '';
			$secretkey = isset($snda_res_arr[1]) ? $snda_res_arr[1] : '';
		} else {
			$appid = 200037400;
			$secretkey = '';
		}
		return array('appid'=>$appid, 'secretkey'=>$secretkey);	
	}
	
	public function logout() {
		$setting = pc_base::load_config('system');
		//snda�˳�
		if($setting['snda_enable'] && param::get_cookie('_from')=='snda') {
			param::set_cookie('_from', '');
			$forward = isset($_GET['forward']) && trim($_GET['forward']) ? urlencode($_GET['forward']) : '';
			$logouturl = 'https://cas.sdo.com/cas/logout?url='.urlencode(APP_PATH.'index.php?m=member&c=index&a=logout&forward='.$forward);
			header('Location: '.$logouturl);
		} else {
			$synlogoutstr = '';	//ͬ���˳�js����
			if(pc_base::load_config('system', 'phpsso')) {
				$this->_init_phpsso();
				$synlogoutstr = $this->client->ps_member_synlogout();			
			}
			
			param::set_cookie('auth', '');
			param::set_cookie('_userid', '');
			param::set_cookie('_username', '');
			param::set_cookie('_groupid', '');
			param::set_cookie('_nickname', '');
			param::set_cookie('cookietime', '');
			$forward = isset($_GET['forward']) && trim($_GET['forward']) ? $_GET['forward'] : 'index.php?m=member&c=index&a=login';
			showmessage(L('logout_success').$synlogoutstr, $forward);
		}
	}

	/**
	 * �ҵ��ղ�
	 * 
	 */
	public function favorite() {
		$this->favorite_db = pc_base::load_model('favorite_model');
		$memberinfo = $this->memberinfo;
		if(isset($_GET['id']) && trim($_GET['id'])) {
			$this->favorite_db->delete(array('userid'=>$memberinfo['userid'], 'id'=>intval($_GET['id'])));
			showmessage(L('operation_success'), HTTP_REFERER);
		} else {
			$page = isset($_GET['page']) && trim($_GET['page']) ? intval($_GET['page']) : 1;
			$favoritelist = $this->favorite_db->listinfo(array('userid'=>$memberinfo['userid']), 'id DESC', $page, 10);
			$pages = $this->favorite_db->pages;

			include template('member', 'favorite_list');
		}
	}
	
	/**
	 * �ҵĺ���
	 */
	public function friend() {
		$memberinfo = $this->memberinfo;
		$this->friend_db = pc_base::load_model('friend_model');
		if(isset($_GET['friendid'])) {
			$this->friend_db->delete(array('userid'=>$memberinfo['userid'], 'friendid'=>intval($_GET['friendid'])));
			showmessage(L('operation_success'), HTTP_REFERER);
		} else {
			//��ʼ��phpsso
			$phpsso_api_url = $this->_init_phpsso();
	
			//�ҵĺ����б�userid
			$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$friendids = $this->friend_db->listinfo(array('userid'=>$memberinfo['userid']), '', $page, 10);
			$pages = $this->friend_db->pages;
			foreach($friendids as $k=>$v) {
				$friendlist[$k]['friendid'] = $v['friendid'];
				$friendlist[$k]['avatar'] = $this->client->ps_getavatar($v['phpssouid']);
				$friendlist[$k]['is'] = $v['is'];
			}
			include template('member', 'friend_list');
		}
	}
	
	/**
	 * ���ֶһ�
	 */
	public function change_credit() {
		$memberinfo = $this->memberinfo;
		//�����û�ģ������
		$member_setting = getcache('member_setting');
		$this->_init_phpsso();
		$setting = $this->client->ps_getcreditlist();
		$outcredit = unserialize($setting);
		$setting = $this->client->ps_getapplist();
		$applist = unserialize($setting);
		
		if(isset($_POST['dosubmit'])) {
			//��ϵͳ���ֶһ���
			$fromvalue = intval($_POST['fromvalue']);
			//��ϵͳ��������
			$from = $_POST['from'];
			$toappid_to = explode('_', $_POST['to']);
			//Ŀ��ϵͳappid
			$toappid = $toappid_to[0];
			//Ŀ��ϵͳ��������
			$to = $toappid_to[1];
			if($from == 1) {
				if($memberinfo['point'] < $fromvalue) {
					showmessage(L('need_more_point'), HTTP_REFERER);
				}
			} elseif($from == 2) {
				if($memberinfo['amount'] < $fromvalue) {
					showmessage(L('need_more_amount'), HTTP_REFERER);
				}
			} else {
				showmessage(L('credit_setting_error'), HTTP_REFERER);
			}

			$status = $this->client->ps_changecredit($memberinfo['phpssouid'], $from, $toappid, $to, $fromvalue);
			if($status == 1) {
				if($from == 1) {
					$this->db->update(array('point'=>"-=$fromvalue"), array('userid'=>$memberinfo['userid']));
				} elseif($from == 2) {
					$this->db->update(array('amount'=>"-=$fromvalue"), array('userid'=>$memberinfo['userid']));
				}
				showmessage(L('operation_success'), HTTP_REFERER);
			} else {
				showmessage(L('operation_failure'), HTTP_REFERER);
			}
		} elseif(isset($_POST['buy'])) {
			if(!is_numeric($_POST['money']) || $_POST['money'] < 0) {
				showmessage(L('money_error'), HTTP_REFERER);
			} else {
				$money = intval($_POST['money']);
			}
			
			if($memberinfo['amount'] < $money) {
				showmessage(L('short_of_money'), HTTP_REFERER);
			}
			//�˴����ʶ�ȡ�û�����
			$point = $money*$member_setting['rmb_point_rate'];
			$this->db->update(array('point'=>"+=$point"), array('userid'=>$memberinfo['userid']));
			//�������Ѽ�¼��ͬʱ�۳���Ǯ
			pc_base::load_app_class('spend','pay',0);
			spend::amount($money, L('buy_point'), $memberinfo['userid'], $memberinfo['username']);
			showmessage(L('operation_success'), HTTP_REFERER);
		} else {
			$credit_list = pc_base::load_config('credit');
			
			include template('member', 'change_credit');
		}
	}
	
	//mini��½��
	public function mini() {
		$_username = param::get_cookie('_username');
		$_userid = param::get_cookie('_userid');
		$siteid = isset($_GET['siteid']) ? intval($_GET['siteid']) : '';
		//����վ��id����
		if (!defined('SITEID')) {
		   define('SITEID', $siteid);
		}
		
		$snda_enable = pc_base::load_config('system', 'snda_enable');
		include template('member', 'mini');
	}
	
	/**
	 * ��ʼ��phpsso
	 * about phpsso, include client and client configure
	 * @return string phpsso_api_url phpsso��ַ
	 */
	private function _init_phpsso() {
		pc_base::load_app_class('client', '', 0);
		define('APPID', pc_base::load_config('system', 'phpsso_appid'));
		$phpsso_api_url = pc_base::load_config('system', 'phpsso_api_url');
		$phpsso_auth_key = pc_base::load_config('system', 'phpsso_auth_key');
		$this->client = new client($phpsso_api_url, $phpsso_auth_key);
		return $phpsso_api_url;
	}
	
	protected function _checkname($username) {
		$username =  trim($username);
		if ($this->db->get_one(array('username'=>$username))){
			return false;
		}
		return true;
	}
	
	private function _session_start() {
		$session_storage = 'session_'.pc_base::load_config('system','session_storage');
		pc_base::load_sys_class($session_storage);
	}
	
	/*
	 * ͨ��linkageid��ȡ����·��
	 */
	protected function _get_linkage_fullname($linkageid,  $linkagelist) {
		$fullname = '';
		if($linkagelist['data'][$linkageid]['parentid'] != 0) {
			$fullname = $this->_get_linkage_fullname($linkagelist['data'][$linkageid]['parentid'], $linkagelist);
		}
		//���ڵ�������
		$return = $fullname.$linkagelist['data'][$linkageid]['name'].'>';
		return $return;
	}
	
	/**
	 *���ݻ�������û���
	 * @param $point int ������
	 */
	protected function _get_usergroup_bypoint($point=0) {
		$groupid = 2;
		if(empty($point)) {
			$member_setting = getcache('member_setting');
			$point = $member_setting['defualtpoint'] ? $member_setting['defualtpoint'] : 0;
		}
		$grouplist = getcache('grouplist');
		
		foreach ($grouplist as $k=>$v) {
			$grouppointlist[$k] = $v['point'];
		}
		arsort($grouppointlist);

		//��������û������������Ϊ������ߵ��û���
		if($point > max($grouppointlist)) {
			$groupid = key($grouppointlist);
		} else {
			foreach ($grouppointlist as $k=>$v) {
				if($point >= $v) {
					$groupid = $tmp_k;
					break;
				}
				$tmp_k = $k;
			}
		}
		return $groupid;
	}
				
	/**
	 * ����û���
	 * @param string $username	�û���
	 * @return $status {-4���û�����ֹע��;-1:�û����Ѿ����� ;1:�ɹ�}
	 */
	public function public_checkname_ajax() {
		$username = isset($_GET['username']) && trim($_GET['username']) ? trim($_GET['username']) : exit(0);
		if(CHARSET != 'utf-8') {
			$username = iconv('utf-8', CHARSET, $username);
			$username = addslashes($username);
		}
		
		//�����жϻ�Ա��˱�
		$this->verify_db = pc_base::load_model('member_verify_model');
		if($this->verify_db->get_one(array('username'=>$username))) {
			exit('0');
		}
	
		$this->_init_phpsso();
		$status = $this->client->ps_checkname($username);
			
		if($status == -4 || $status == -1) {
			exit('0');
		} else {
			exit('1');
		}
	}
	
	/**
	 * ����û��ǳ�
	 * @param string $nickname	�ǳ�
	 * @return $status {0:�Ѵ���;1:�ɹ�}
	 */
	public function public_checknickname_ajax() {
		$nickname = isset($_GET['nickname']) && trim($_GET['nickname']) ? trim($_GET['nickname']) : exit('0');
		if(CHARSET != 'utf-8') {
			$nickname = iconv('utf-8', CHARSET, $nickname);
			$nickname = addslashes($nickname);
		}

		//�����жϻ�Ա��˱�
		$this->verify_db = pc_base::load_model('member_verify_model');
		if($this->verify_db->get_one(array('nickname'=>$nickname))) {
			exit('0');
		}
		
		$res = $this->db->get_one(array('nickname'=>$nickname));
		if($res) {
			exit('0');
		} else {
			exit('1');
		}
	}
	
	/**
	 * �������
	 * @param string $email
	 * @return $status {-1:email�Ѿ����� ;-5:�����ֹע��;1:�ɹ�}
	 */
	public function public_checkemail_ajax() {
		$this->_init_phpsso();
		$email = isset($_GET['email']) && trim($_GET['email']) ? trim($_GET['email']) : exit(0);
		
		$status = $this->client->ps_checkemail($email);
		if($status == -5) {	//��ֹע��
			exit('0');
		} elseif($status == -1) {	//�û����Ѵ��ڣ������޸��û���ʱ����Ҫ�ж������Ƿ��ǵ�ǰ�û���
			if(isset($_GET['phpssouid'])) {	//�޸��û�����phpssouid
				$status = $this->client->ps_get_member_info($email, 3);
				if($status) {
					$status = unserialize($status);	//�ӿڷ������л��������ж�
					if (isset($status['uid']) && $status['uid'] == intval($_GET['phpssouid'])) {
						exit('1');
					} else {
						exit('0');
					}
				} else {
					exit('0');
				}
			} else {
				exit('0');
			}
		} else {
			exit('1');
		}
	}
	
	public function public_sina_login() {
		define('WB_AKEY', pc_base::load_config('system', 'sina_akey'));
		define('WB_SKEY', pc_base::load_config('system', 'sina_skey'));
		pc_base::load_app_class('weibooauth', '' ,0);
		$this->_session_start();
					
		if(isset($_GET['callback']) && trim($_GET['callback'])) {
			$o = new WeiboOAuth(WB_AKEY, WB_SKEY, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);
			$_SESSION['last_key'] = $o->getAccessToken($_REQUEST['oauth_verifier']);
			$c = new WeiboClient(WB_AKEY, WB_SKEY, $_SESSION['last_key']['oauth_token'], $_SESSION['last_key']['oauth_token_secret']);
			//��ȡ�û���Ϣ
			$me = $c->verify_credentials();
			if(CHARSET != 'utf-8') {
				$me['name'] = iconv('utf-8', CHARSET, $me['name']);
				$me['location'] = iconv('utf-8', CHARSET, $me['location']);
				$me['description'] = iconv('utf-8', CHARSET, $me['description']);
				$me['screen_name'] = iconv('utf-8', CHARSET, $me['screen_name']);
			}
			if(!empty($me['id'])) {
				//���connect��Ա�Ƿ�󶨣��Ѱ�ֱ�ӵ�¼��δ����ʾע��/��ҳ��
				$where = array('connectid'=>$me['id'], 'from'=>'sina');
				$r = $this->db->get_one($where);
				
				//connect�û��Ѿ��󶨱�վ�û�
				if(!empty($r)) {
					//��ȡ��վ�û���Ϣ��ִ�е�¼����
					$password = $r['password'];
					$this->_init_phpsso();
					$synloginstr = $this->client->ps_member_synlogin($r['phpssouid']);
					$userid = $r['userid'];
					$groupid = $r['groupid'];
					$username = $r['username'];
					$nickname = empty($r['nickname']) ? $username : $r['nickname'];
					$this->db->update(array('lastip'=>ip(), 'lastdate'=>SYS_TIME, 'nickname'=>$me['name']), array('userid'=>$userid));
					
					if(!$cookietime) $get_cookietime = param::get_cookie('cookietime');
					$_cookietime = $cookietime ? intval($cookietime) : ($get_cookietime ? $get_cookietime : 0);
					$cookietime = $_cookietime ? TIME + $_cookietime : 0;
					
					$phpcms_auth_key = md5(pc_base::load_config('system', 'auth_key').$this->http_user_agent);
					$phpcms_auth = sys_auth($userid."\t".$password, 'ENCODE', $phpcms_auth_key);
					
					param::set_cookie('auth', $phpcms_auth, $cookietime);
					param::set_cookie('_userid', $userid, $cookietime);
					param::set_cookie('_username', $username, $cookietime);
					param::set_cookie('_groupid', $groupid, $cookietime);
					param::set_cookie('cookietime', $_cookietime, $cookietime);
					param::set_cookie('_nickname', $nickname, $cookietime);
					$forward = isset($_GET['forward']) && !empty($_GET['forward']) ? $_GET['forward'] : 'index.php?m=member&c=index';
					showmessage(L('login_success').$synloginstr, $forward);
					
				} else {				
					//������ע��ҳ��
					$_SESSION['connectid'] = $me['id'];
					$_SESSION['from'] = 'sina';
					$connect_username = $me['name'];
					include template('member', 'connect');
				}
			} else {
				showmessage(L('login_failure'), 'index.php?m=member&c=index&a=login');
			}
		} else {
			$o = new WeiboOAuth(WB_AKEY, WB_SKEY);
			$keys = $o->getRequestToken();
			$aurl = $o->getAuthorizeURL($keys['oauth_token'] ,false , APP_PATH.'index.php?m=member&c=index&a=public_sina_login&callback=1');
			$_SESSION['keys'] = $keys;
			include template('member', 'connect_sina');
		}
	}
	
	/**
	 * ʢ��ͨ��֤��½
	 */
	public function public_snda_login() {
		$this->_session_start();
		$ticket = $_GET['ticket'];
		if(!empty($ticket)) {
			$callback_url = urlencode(APP_PATH.'index.php?m=member&c=index&a=public_snda_login');
			
			$snda_res = $this->_snda_get_appid();
			$appid = $snda_res['appid'];
			$cas_url ="http://gw.sdo.com/cas/validate/?service=$callback_url&ticket=$ticket&appid=$appid&appArea=0";

			$result = @file_get_contents($cas_url);
			$result = json_decode($result, 1);

			if(isset($result['error']) && $result['error'] == 0 && is_numeric($result['data']['uid'])) {
				$userid = $result['data']['uid'];
			} elseif(isset($result['error']) && $result['errno'] == -1) {
				showmessage(L('invalid_appid'), 'index.php?m=member&c=index&a=login');
			} else {
				showmessage(L('login_failure'), 'index.php?m=member&c=index&a=login');
			}

			if(!empty($userid)) {
				
				//���connect��Ա�Ƿ�󶨣��Ѱ�ֱ�ӵ�¼��δ����ʾע��/��ҳ��
				$where = array('connectid'=>$userid, 'from'=>'snda');
				$r = $this->db->get_one($where);
				
				//connect�û��Ѿ��󶨱�վ�û�
				if(!empty($r)) {
					//��ȡ��վ�û���Ϣ��ִ�е�¼����
					$password = $r['password'];
					$this->_init_phpsso();
					$synloginstr = $this->client->ps_member_synlogin($r['phpssouid']);
					$userid = $r['userid'];
					$groupid = $r['groupid'];
					$username = $r['username'];
					$nickname = empty($r['nickname']) ? $username : $r['nickname'];
					$this->db->update(array('lastip'=>ip(), 'lastdate'=>SYS_TIME, 'nickname'=>$me['name']), array('userid'=>$userid));
					if(!$cookietime) $get_cookietime = param::get_cookie('cookietime');
					$_cookietime = $cookietime ? intval($cookietime) : ($get_cookietime ? $get_cookietime : 0);
					$cookietime = $_cookietime ? TIME + $_cookietime : 0;
					
					$phpcms_auth_key = md5(pc_base::load_config('system', 'auth_key').$this->http_user_agent);
					$phpcms_auth = sys_auth($userid."\t".$password, 'ENCODE', $phpcms_auth_key);
					
					param::set_cookie('auth', $phpcms_auth, $cookietime);
					param::set_cookie('_userid', $userid, $cookietime);
					param::set_cookie('_username', $username, $cookietime);
					param::set_cookie('_groupid', $groupid, $cookietime);
					param::set_cookie('cookietime', $_cookietime, $cookietime);
					param::set_cookie('_nickname', $nickname, $cookietime);
					param::set_cookie('_from', 'snda');
					$forward = isset($_GET['forward']) && !empty($_GET['forward']) ? $_GET['forward'] : 'index.php?m=member&c=index';
					showmessage(L('login_success').$synloginstr, $forward);
				} else {				
					//������ע��ҳ��
					$_SESSION['connectid'] = $userid;
					$_SESSION['from'] = 'snda';
					$connect_username = $userid;
					include template('member', 'connect');
				}
			}	
		} else {
			showmessage(L('login_failure'), 'index.php?m=member&c=index&a=login');
		}
		
	}
	
	/**
	 * �һ�����
	 */
	public function public_forget_password () {
		
		$email_config = getcache('common', 'commons');
		if(empty($email_config['mail_user']) || empty($email_config['mail_password'])) {
			showmessage(L('email_config_empty'), HTTP_REFERER);
		}
			
		$this->_session_start();
		$member_setting = getcache('member_setting');
		if(isset($_POST['dosubmit'])) {
			if ($_SESSION['code'] != strtolower($_POST['code'])) {
				showmessage(L('code_error'), HTTP_REFERER);
			}
			
			$memberinfo = $this->db->get_one(array('email'=>$_POST['email']));
			if(!empty($memberinfo['email'])) {
				$email = $memberinfo['email'];
			} else {
				showmessage(L('email_error'), HTTP_REFERER);
			}
			
			pc_base::load_sys_func('mail');
			$phpcms_auth_key = md5(pc_base::load_config('system', 'auth_key').$this->http_user_agent);

			$code = sys_auth($memberinfo['userid']."\t".SYS_TIME, 'ENCODE', $phpcms_auth_key);

			$url = APP_PATH."index.php?m=member&c=index&a=public_forget_password&code=$code";
			$message = $member_setting['forgetpassword'];
			$message = str_replace(array('{click}','{url}'), array('<a href="'.$url.'">'.L('please_click').'</a>',$url), $message);
			//��ȡվ������
			$sitelist = getcache('sitelist', 'commons');
			
			if(isset($sitelist[$memberinfo['siteid']]['name'])) {
				$sitename = $sitelist[$memberinfo['siteid']]['name'];
			} else {
				$sitename = 'PHPCMS_V9_MAIL';
			}
			sendmail($email, L('forgetpassword'), $message, '', '', $sitename);
			showmessage(L('operation_success'), 'index.php?m=member&c=index&a=login');
		} elseif($_GET['code']) {
			$phpcms_auth_key = md5(pc_base::load_config('system', 'auth_key').$this->http_user_agent);
			$hour = date('y-m-d h', SYS_TIME);
			$code = sys_auth($_GET['code'], 'DECODE', $phpcms_auth_key);
			$code = explode("\t", $code);

			if(is_array($code) && is_numeric($code[0]) && date('y-m-d h', SYS_TIME) == date('y-m-d h', $code[1])) {
				$memberinfo = $this->db->get_one(array('userid'=>$code[0]));
				
				if(empty($memberinfo['phpssouid'])) {
					showmessage(L('operation_failure'), 'index.php?m=member&c=index&a=login');
				}
				
				$password = random(8);
				$updateinfo['password'] = password($password, $memberinfo['encrypt']);
				
				$this->db->update($updateinfo, array('userid'=>$code[0]));
				if(pc_base::load_config('system', 'phpsso')) {
					//��ʼ��phpsso
					$this->_init_phpsso();
					$this->client->ps_member_edit('', $email, '', $password, $memberinfo['phpssouid'], $memberinfo['encrypt']);
				}
	
				showmessage(L('operation_success').L('newpassword').':'.$password);

			} else {
				showmessage(L('operation_failure'), 'index.php?m=member&c=index&a=login');
			}

		} else {
			$siteid = isset($_REQUEST['siteid']) && trim($_REQUEST['siteid']) ? intval($_REQUEST['siteid']) : 1;
			$siteinfo = siteinfo($siteid);
			
			include template('member', 'forget_password');
		}
	}
}
?>