<?php
defined('IN_PHPCMS') or exit('No permission resources.');
pc_base::load_app_class('admin','admin',0);

class MY_category extends category {
		private $db;
		public $siteid;
	function __construct() {
		parent::__construct();
		$this->db = pc_base::load_model('category_model');
		$this->siteid = $this->get_siteid();
	}


	public function init_product() {
		$show_pc_hash = '';
		$tree = pc_base::load_sys_class('tree');
		$models = getcache('model','commons');
		$sitelist = getcache('sitelist','commons');
		$category_items = array();
		foreach ($models as $modelid=>$model) {
			$category_items[$modelid] = getcache('category_items_'.$modelid,'commons');
		}
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$categorys = array();
		//读取缓存
		$result = getcache('category_content_'.$this->siteid,'commons');
		$show_detail = count($result) < 500 ? 1 : 0;
		$parentid = $_GET['parentid'] ? $_GET['parentid'] : 0;
		$html_root = pc_base::load_config('system','html_root');
		$types = array(0 => L('category_type_system'),1 => L('category_type_page'),2 => L('category_type_link'));
		if(!empty($result)) {
			foreach($result as $r) {

				if($r['modelid']==1){}else{//排除新闻模块
					$r['modelname'] = $models[$r['modelid']]['name'];
					$r['modelid'] =$models[$r['modelid']]['modelid'];
					$r['str_manage'] = '';
					if(!$show_detail) {
						if($r['parentid']!=$parentid) continue;
						$r['parentid'] = 0;
						$r['str_manage'] .= '<a href="?m=admin&c=category&a=init&parentid='.$r['catid'].'&menuid='.$_GET['menuid'].'&s='.$r['type'].'&pc_hash='.$_SESSION['pc_hash'].'">'.L('manage_sub_category').'</a> | ';
					}
					$arrpar_count = substr_count($r['arrparentid'],",");
					if($r['modelid']=="12"){//厂家栏目

					}else if(($r['modelid']=="11" ||$r['modelid']=="13"||$r['modelid']=="14"||$r['modelid']=="15"||$r['modelid']=="16") && $arrpar_count==0 && $r['parentid']==0){//品牌
					
						$r['str_manage'] .= '<a href="?m=admin&c=category&a=add_product&parentid='.$r['catid'].'&menuid='.$_GET['menuid'].'&s='.$r['type'].'&pc_hash='.$_SESSION['pc_hash'].'">添加品牌</a> | ';

					}else if(($r['modelid']=="11" ||$r['modelid']=="13"||$r['modelid']=="14"||$r['modelid']=="15"||$r['modelid']=="16") && $arrpar_count==1){//系列
						$r['str_manage'] .= '<a href="?m=admin&c=category&a=add_product&parentid='.$r['catid'].'&menuid='.$_GET['menuid'].'&s='.$r['type'].'&pc_hash='.$_SESSION['pc_hash'].'">添加系列</a> | ';
					}else if(($r['modelid']=="11" ||$r['modelid']=="13"||$r['modelid']=="14"||$r['modelid']=="15"||$r['modelid']=="16") && $arrpar_count==2){//最后一级
					}else{
						$r['str_manage'] .= '<a href="?m=admin&c=category&a=add&parentid='.$r['catid'].'&menuid='.$_GET['menuid'].'&s='.$r['type'].'&pc_hash='.$_SESSION['pc_hash'].'">'.L('add_sub_category').'</a> | ';				
					}

					$r['str_manage'] .= '<a href="?m=admin&c=category&a=edit_product&catid='.$r['catid'].'&menuid='.$_GET['menuid'].'&type='.$r['type'].'&pc_hash='.$_SESSION['pc_hash'].'">'.L('edit').'</a> | <a href="javascript:confirmurl(\'?m=admin&c=category&a=delete&catid='.$r['catid'].'&menuid='.$_GET['menuid'].'\',\''.L('confirm',array('message'=>addslashes($r['catname']))).'\')">'.L('delete').'</a> ';
					$r['typename'] = $types[$r['type']];
					$r['display_icon'] = $r['ismenu'] ? '' : ' <img src ="'.IMG_PATH.'icon/gear_disable.png" title="'.L('not_display_in_menu').'">';
					if($r['type'] || $r['child']) {
						$r['items'] = '';
					} else {
						$r['items'] = $category_items[$r['modelid']][$r['catid']];
					}
					$r['help'] = '';
					$setting = string2array($r['setting']);
					if($r['url']) {
						if(preg_match('/^(http|https):\/\//', $r['url'])) {
							$catdir = $r['catdir'];
							$prefix = $r['sethtml'] ? '' : $html_root;
							if($this->siteid==1) {
								$catdir = $prefix.'/'.$r['parentdir'].$catdir;
							} else {
								$catdir = $prefix.'/'.$sitelist[$this->siteid]['dirname'].$html_root.'/'.$catdir;
							}
							if($r['type']==0 && $setting['ishtml'] && strpos($r['url'], '?')===false && substr_count($r['url'],'/')<4) $r['help'] = '<img src="'.IMG_PATH.'icon/help.png" title="'.L('tips_domain').$r['url'].'&#10;'.L('directory_binding').'&#10;'.$catdir.'/">';
						} else {
							$r['url'] = substr($sitelist[$this->siteid]['domain'],0,-1).$r['url'];
						}
						$r['url'] = "<a href='$r[url]' target='_blank'>".L('vistor')."</a>";
					} else {
						$r['url'] = "<a href='?m=admin&c=category&a=public_cache&menuid=43&module=admin'><font color='red'>".L('update_backup')."</font></a>";
					}
					$categorys[$r['catid']] = $r;
				}
			}
		}
		$str  = "<tr>
					<td align='center'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input-text-c'></td>
					<td align='center'>\$id</td>
					<td >\$spacer\$catname\$display_icon</td>
					<td align='center'>\$items</td>
					<td align='center'>\$url</td>
					<td align='center' >\$str_manage</td>
				</tr>";
		$tree->init($categorys);
		$categorys = $tree->get_tree(0, $str);
		include $this->admin_tpl('category_manage_product');
	}

	public function init_news() {
		$show_pc_hash = '';
		$tree = pc_base::load_sys_class('tree');
		$models = getcache('model','commons');
		$sitelist = getcache('sitelist','commons');
		$category_items = array();
		foreach ($models as $modelid=>$model) {
			$category_items[$modelid] = getcache('category_items_'.$modelid,'commons');
		}
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$categorys = array();
		//读取缓存
		$result = getcache('category_content_'.$this->siteid,'commons');
		$show_detail = count($result) < 500 ? 1 : 0;
		$parentid = $_GET['parentid'] ? $_GET['parentid'] : 0;
		$html_root = pc_base::load_config('system','html_root');
		$types = array(0 => L('category_type_system'),1 => L('category_type_page'),2 => L('category_type_link'));
		if(!empty($result)) {
			foreach($result as $r) {

				if($r['modelid']==1){//新闻模块
					$r['modelname'] = $models[$r['modelid']]['name'];
					$r['modelid'] =$models[$r['modelid']]['modelid'];
					$r['str_manage'] = '';
					if(!$show_detail) {
						if($r['parentid']!=$parentid) continue;
						$r['parentid'] = 0;
						$r['str_manage'] .= '<a href="?m=admin&c=category&a=init&parentid='.$r['catid'].'&menuid='.$_GET['menuid'].'&s='.$r['type'].'&pc_hash='.$_SESSION['pc_hash'].'">111'.L('manage_sub_category').'</a> | ';
					}
						$r['str_manage'] .= '<a href="?m=admin&c=category&a=add&parentid='.$r['catid'].'&menuid='.$_GET['menuid'].'&s='.$r['type'].'&pc_hash='.$_SESSION['pc_hash'].'">'.L('add_sub_category').'</a> | ';				

					$r['str_manage'] .= '<a href="?m=admin&c=category&a=edit&catid='.$r['catid'].'&menuid='.$_GET['menuid'].'&type='.$r['type'].'&pc_hash='.$_SESSION['pc_hash'].'">'.L('edit').'</a> | <a href="javascript:confirmurl(\'?m=admin&c=category&a=delete&catid='.$r['catid'].'&menuid='.$_GET['menuid'].'\',\''.L('confirm',array('message'=>addslashes($r['catname']))).'\')">'.L('delete').'</a> ';
					$r['typename'] = $types[$r['type']];
					$r['display_icon'] = $r['ismenu'] ? '' : ' <img src ="'.IMG_PATH.'icon/gear_disable.png" title="'.L('not_display_in_menu').'">';
					if($r['type'] || $r['child']) {
						$r['items'] = '';
					} else {
						$r['items'] = $category_items[$r['modelid']][$r['catid']];
					}
					$r['help'] = '';
					$setting = string2array($r['setting']);
					if($r['url']) {
						if(preg_match('/^(http|https):\/\//', $r['url'])) {
							$catdir = $r['catdir'];
							$prefix = $r['sethtml'] ? '' : $html_root;
							if($this->siteid==1) {
								$catdir = $prefix.'/'.$r['parentdir'].$catdir;
							} else {
								$catdir = $prefix.'/'.$sitelist[$this->siteid]['dirname'].$html_root.'/'.$catdir;
							}
							if($r['type']==0 && $setting['ishtml'] && strpos($r['url'], '?')===false && substr_count($r['url'],'/')<4) $r['help'] = '<img src="'.IMG_PATH.'icon/help.png" title="'.L('tips_domain').$r['url'].'&#10;'.L('directory_binding').'&#10;'.$catdir.'/">';
						} else {
							$r['url'] = substr($sitelist[$this->siteid]['domain'],0,-1).$r['url'];
						}
						$r['url'] = "<a href='$r[url]' target='_blank'>".L('vistor')."</a>";
					} else {
						$r['url'] = "<a href='?m=admin&c=category&a=public_cache&menuid=43&module=admin'><font color='red'>".L('update_backup')."</font></a>";
					}
					$categorys[$r['catid']] = $r;
				}
			}
		}
		$str  = "<tr>
					<td align='center'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input-text-c'></td>
					<td align='center'>\$id</td>
					<td >\$spacer\$catname\$display_icon</td>
					<td align='center'>\$items</td>
					<td align='center'>\$url</td>
					<td align='center' >\$str_manage</td>
				</tr>";
		$tree->init($categorys);
		$categorys = $tree->get_tree(0, $str);
		include $this->admin_tpl('category_manage_product');
	}
	/**
	 * 添加栏目
	 */
	public function add_product() {
		if(isset($_POST['dosubmit'])) {
			pc_base::load_sys_func('iconv');
			$_POST['info']['type'] = intval($_POST['type']);
			
			if(isset($_POST['batch_add']) && empty($_POST['batch_add'])) {
				if($_POST['info']['catname']=='') showmessage(L('input_catname'));
				if($_POST['info']['type']!=2) {
					if($_POST['info']['catdir']=='') showmessage(L('input_dirname'));
					if(!$this->public_check_catdir(0,$_POST['info']['catdir'])) showmessage(L('catname_have_exists'));
				}
			}
			
			$_POST['info']['siteid'] = $this->siteid;
			$_POST['info']['module'] = 'content';
			$setting = $_POST['setting'];
			if($_POST['info']['type']!=2) {
				//栏目生成静态配置
				if($setting['ishtml']) {
					$setting['category_ruleid'] = $_POST['category_html_ruleid'];
				} else {
					$setting['category_ruleid'] = $_POST['category_php_ruleid'];
					$_POST['info']['url'] = '';
				}
			}
			
			//内容生成静态配置
			if($setting['content_ishtml']) {
				$setting['show_ruleid'] = $_POST['show_html_ruleid'];
			} else {
				$setting['show_ruleid'] = $_POST['show_php_ruleid'];
			}
			if($setting['repeatchargedays']<1) $setting['repeatchargedays'] = 1;
			$_POST['info']['sethtml'] = $setting['create_to_html_root'];
			$_POST['info']['setting'] = array2string($setting);
			
			$end_str = $old_end =  '<script type="text/javascript">window.top.art.dialog({id:"test"}).close();window.top.art.dialog({id:"test",content:\'<h2>'.L("add_success").'</h2><span style="fotn-size:16px;">'.L("following_operation").'</span><br /><ul style="fotn-size:14px;"><li><a href="?m=admin&c=category&a=public_cache&menuid=43&module=admin" target="right"  onclick="window.top.art.dialog({id:\\\'test\\\'}).close()">'.L("following_operation_1").'</a></li><li><a href="'.HTTP_REFERER.'" target="right" onclick="window.top.art.dialog({id:\\\'test\\\'}).close()">'.L("following_operation_2").'</a></li></ul>\',width:"400",height:"200"});</script>';
			if(!isset($_POST['batch_add']) || empty($_POST['batch_add'])) {
				$catname = CHARSET == 'gbk' ? $_POST['info']['catname'] : iconv('utf-8','gbk',$_POST['info']['catname']);
				$letters = gbk_to_pinyin($catname);
				$_POST['info']['letter'] = strtolower(implode('', $letters));
				$catid = $this->db->insert($_POST['info'], true);
				$this->update_priv($catid, $_POST['priv_roleid']);
				$this->update_priv($catid, $_POST['priv_groupid'],0);
			} else {
				$end_str = '';
				$batch_adds = explode("\n", $_POST['batch_add']);
				foreach ($batch_adds as $_v) {
					if(trim($_v)=='') continue;
					$names = explode('|', $_v);
					$catname = $names[0];
					$_POST['info']['catname'] = $names[0];
					$letters = gbk_to_pinyin($catname);
					$_POST['info']['letter'] = strtolower(implode('', $letters));
					$_POST['info']['catdir'] = trim($names[1]) ? trim($names[1]) : trim($_POST['info']['letter']);
					if(!$this->public_check_catdir(0,$_POST['info']['catdir'])) {
						$end_str .= $end_str ? ','.$_POST['info']['catname'].'('.$_POST['info']['catdir'].')' : $_POST['info']['catname'].'('.$_POST['info']['catdir'].')';
						continue;
					}
					$catid = $this->db->insert($_POST['info'], true);
					$this->update_priv($catid, $_POST['priv_roleid']);
					$this->update_priv($catid, $_POST['priv_groupid'],0);
				}
				$end_str = $end_str ? L('follow_catname_have_exists').$end_str : $old_end;
				echo $ent_str;
			}
			$this->cache();
			showmessage(L('add_success').$end_str);
		} else {
			//获取站点模板信息

			pc_base::load_app_func('global');
			
			$template_list = template_list($this->siteid, 0);
			foreach ($template_list as $k=>$v) {
				$template_list[$v['dirname']] = $v['name'] ? $v['name'] : $v['dirname'];
				unset($template_list[$k]);
			}
			$show_validator = '';
			if(isset($_GET['parentid'])) {
				$parentid = $_GET['parentid'];

				$r = $this->db->get_one(array('catid'=>$parentid));
				if($r) extract($r,EXTR_SKIP);
				$setting = string2array($setting);
			}

			if(($r['modelid']=="11" ||$r['modelid']=="13"||$r['modelid']=="14"||$r['modelid']=="15"||$r['modelid']=="16") && $arrpar_count==0 && $r['parentid']==0){//增加品牌时加载厂家
			$this->get_changjia=pc_base::load_app_class('get_changjia');
				if($r['modelid']=="11")$changjia_arr=$this->get_changjia->get_changjia('1');
				if($r['modelid']=="13")$changjia_arr=$this->get_changjia->get_changjia('2');
				if($r['modelid']=="14")$changjia_arr=$this->get_changjia->get_changjia('3');
				if($r['modelid']=="15")$changjia_arr=$this->get_changjia->get_changjia('4');
				if($r['modelid']=="16")$changjia_arr=$this->get_changjia->get_changjia('5');
				
				
			}
			pc_base::load_sys_class('form','',0);
			$type = $_GET['s'];
			if($type==0) {
				$exists_model = false;
				$models = getcache('model','commons');
				foreach($models as $_m) {
					if($this->siteid == $_m['siteid']) {
						$exists_model = true;
						break;
					}
				}
				if(!$exists_model) showmessage(L('please_add_model'),'?m=content&c=sitemodel&a=init&menuid=59',5000);
				include $this->admin_tpl('category_add_product');
			} elseif ($type==1) {
				include $this->admin_tpl('category_page_add');
			} else {
				include $this->admin_tpl('category_link');
			}
		}
	}
	public function edit_product() {
		
		if(isset($_POST['dosubmit'])) {
			pc_base::load_sys_func('iconv');
			$catid = 0;
			$catid = intval($_POST['catid']);
			$setting = $_POST['setting'];
			//栏目生成静态配置
			if($_POST['type'] != 2) {
				if($setting['ishtml']) {
					$setting['category_ruleid'] = $_POST['category_html_ruleid'];
				} else {
					$setting['category_ruleid'] = $_POST['category_php_ruleid'];
					$_POST['info']['url'] = '';
				}
			}
			//内容生成静态配置
			if($setting['content_ishtml']) {
				$setting['show_ruleid'] = $_POST['show_html_ruleid'];
			} else {
				$setting['show_ruleid'] = $_POST['show_php_ruleid'];
			}
			if($setting['repeatchargedays']<1) $setting['repeatchargedays'] = 1;
			$_POST['info']['sethtml'] = $setting['create_to_html_root'];
			$_POST['info']['setting'] = array2string($setting);
			$_POST['info']['module'] = 'content';
			$catname = CHARSET == 'gbk' ? $_POST['info']['catname'] : iconv('utf-8','gbk',$_POST['info']['catname']);
			$letters = gbk_to_pinyin($catname);
			$_POST['info']['letter'] = strtolower(implode('', $letters));

			//应用权限设置到子栏目
			if($_POST['priv_child']) {
				$arrchildid = $this->db->get_one(array('catid'=>$catid), 'arrchildid');
				if(!empty($arrchildid['arrchildid'])) {
					$arrchildid_arr = explode(',', $arrchildid['arrchildid']);
					if(!empty($arrchildid_arr)) {
						foreach ($arrchildid_arr as $arr_v) {
							$this->update_priv($arr_v, $_POST['priv_groupid'], 0);
						}
					}
				}
				
			}
			
			$this->db->update($_POST['info'],array('catid'=>$catid,'siteid'=>$this->siteid));
			$this->update_priv($catid, $_POST['priv_roleid']);
			$this->update_priv($catid, $_POST['priv_groupid'],0);
			$this->cache();
			//更新附件状态
			if($_POST['info']['image'] && pc_base::load_config('system','attachment_stat')) {
				$this->attachment_db = pc_base::load_model('attachment_model');
				$this->attachment_db->api_update($_POST['info']['image'],'catid-'.$catid,1);
			}
			showmessage(L('operation_success').'<script type="text/javascript">window.top.art.dialog({id:"test"}).close();window.top.art.dialog({id:"test",content:\'<h2>'.L("operation_success").'</h2><span style="fotn-size:16px;">'.L("edit_following_operation").'</span><br /><ul style="fotn-size:14px;"><li><a href="?m=admin&c=category&a=public_cache&menuid=43&module=admin" target="right"  onclick="window.top.art.dialog({id:\\\'test\\\'}).close()">'.L("following_operation_1").'</a></li></ul>\',width:"400",height:"200"});</script>','?m=admin&c=category&a=init_product&module=admin&menuid=43');
		} else {
			//获取站点模板信息
			pc_base::load_app_func('global');
			$template_list = template_list($this->siteid, 0);
			foreach ($template_list as $k=>$v) {
				$template_list[$v['dirname']] = $v['name'] ? $v['name'] : $v['dirname'];
				unset($template_list[$k]);
			}
			
			
			$show_validator = $catid = $r = '';
			$catid = intval($_GET['catid']);
			pc_base::load_sys_class('form','',0);

			$r = $this->db->get_one(array('catid'=>$catid));
			if($r) extract($r);
			

			if($changjia>0){//增加品牌时加载厂家
				$this->get_changjia=pc_base::load_app_class('get_changjia');
				if($r['modelid']=="11")$changjia_arr=$this->get_changjia->get_changjia('1');
				if($r['modelid']=="13")$changjia_arr=$this->get_changjia->get_changjia('2');
				if($r['modelid']=="14")$changjia_arr=$this->get_changjia->get_changjia('3');
				if($r['modelid']=="15")$changjia_arr=$this->get_changjia->get_changjia('4');
				if($r['modelid']=="16")$changjia_arr=$this->get_changjia->get_changjia('5');
			}
			$setting = string2array($setting);
			
			$this->priv_db = pc_base::load_model('category_priv_model');
			$this->privs = $this->priv_db->select(array('catid'=>$catid));
			
			$type = $_GET['type'];
			if($type==0) {
				include $this->admin_tpl('category_edit_product');
			} elseif ($type==1) {
				include $this->admin_tpl('category_page_edit');
			} else {
				include $this->admin_tpl('category_link');
			}
		}	
	}
	/**
	 * 更新权限
	 * @param  $catid
	 * @param  $priv_datas
	 * @param  $is_admin
	 */
	private function update_priv($catid,$priv_datas,$is_admin = 1) {
		$this->priv_db = pc_base::load_model('category_priv_model');
		$this->priv_db->delete(array('catid'=>$catid,'is_admin'=>$is_admin));
		if(is_array($priv_datas) && !empty($priv_datas)) {
			foreach ($priv_datas as $r) {
				$r = explode(',', $r);
				$action = $r[0];
				$roleid = $r[1];
				$this->priv_db->insert(array('catid'=>$catid,'roleid'=>$roleid,'is_admin'=>$is_admin,'action'=>$action,'siteid'=>$this->siteid));
			}
		}
	}


	/**
	 * 检查栏目权限
	 * @param $action 动作
	 * @param $roleid 角色
	 * @param $is_admin 是否为管理组
	 */
	private function check_category_priv($action,$roleid,$is_admin = 1) {
		$checked = '';
		foreach ($this->privs as $priv) {
			if($priv['is_admin']==$is_admin && $priv['roleid']==$roleid && $priv['action']==$action) $checked = 'checked';
		}
		return $checked;
	}
	/**
	 * 重新统计栏目信息数量
	 */
	public function count_items() {
		$this->content_db = pc_base::load_model('content_model');
		$result = getcache('category_content_'.$this->siteid,'commons');
		foreach($result as $r) {
			if($r['type'] == 0) {
				$modelid = $r['modelid'];
				$this->content_db->set_model($modelid);
				$number = $this->content_db->count(array('catid'=>$r['catid']));
				$this->db->update(array('items'=>$number),array('catid'=>$r['catid']));
			}
		}
		showmessage(L('operation_success'),HTTP_REFERER);
	}
	/**
	 * json方式加载模板
	 */
	public function public_tpl_file_list() {
		$style = isset($_GET['style']) && trim($_GET['style']) ? trim($_GET['style']) : exit(0);
		$catid = isset($_GET['catid']) && intval($_GET['catid']) ? intval($_GET['catid']) : 0;
		$batch_str = isset($_GET['batch_str']) ? '['.$catid.']' : '';
		if ($catid) {
			$cat = getcache('category_content_'.$this->siteid,'commons');
			$cat = $cat[$catid];
			$cat['setting'] = string2array($cat['setting']);
		}
		pc_base::load_sys_class('form','',0);
		if($_GET['type']==1) {
			$html = array('page_template'=>form::select_template($style, 'content',(isset($cat['setting']['page_template']) && !empty($cat['setting']['page_template']) ? $cat['setting']['page_template'] : 'category'),'name="setting'.$batch_str.'[page_template]"','page'));
		} else {
			$html = array('category_template'=> form::select_template($style, 'content',(isset($cat['setting']['category_template']) && !empty($cat['setting']['category_template']) ? $cat['setting']['category_template'] : 'category'),'name="setting'.$batch_str.'[category_template]"','category'), 
				'list_template'=>form::select_template($style, 'content',(isset($cat['setting']['list_template']) && !empty($cat['setting']['list_template']) ? $cat['setting']['list_template'] : 'list'),'name="setting'.$batch_str.'[list_template]"','list'),
				'show_template'=>form::select_template($style, 'content',(isset($cat['setting']['show_template']) && !empty($cat['setting']['show_template']) ? $cat['setting']['show_template'] : 'show'),'name="setting'.$batch_str.'[show_template]"','show')
			);
		}
		if ($_GET['module']) {
			unset($html);
			if ($_GET['templates']) {
				$templates = explode('|', $_GET['templates']);
				if ($_GET['id']) $id = explode('|', $_GET['id']);
				if (is_array($templates)) {
					foreach ($templates as $k => $tem) {
						$t = $tem.'_template';
						if ($id[$k]=='') $id[$k] = $tem;
						$html[$t] = form::select_template($style, $_GET['module'], $id[$k], 'name="'.$_GET['name'].'['.$t.']" id="'.$t.'"', $tem);
					}
				}
			}
			
		}
		if (CHARSET == 'gbk') {
			$html = array_iconv($html, 'gbk', 'utf-8');
		}
		echo json_encode($html);
	}

	/**
	 * 快速进入搜索
	 */
	public function public_ajax_search() {
		if($_GET['catname']) {
			if(preg_match('/([a-z]+)/i',$_GET['catname'])) {
				$field = 'letter';
				$catname = strtolower(trim($_GET['catname']));
			} else {
				$field = 'catname';
				$catname = trim($_GET['catname']);
				if (CHARSET == 'gbk') $catname = iconv('utf-8','gbk',$catname);
			}
			$result = $this->db->select("$field LIKE('$catname%') AND siteid='$this->siteid' AND child=0",'catid,type,catname,letter',10);
			if (CHARSET == 'gbk') {
				$result = array_iconv($result, 'gbk', 'utf-8');
			}
			echo json_encode($result);
		}
	}
	/**
	 * json方式读取风格列表，推送部分调用
	 */
	public function public_change_tpl() {
		pc_base::load_sys_class('form','',0);
		$models = getcache('model','commons');
		$modelid = intval($_GET['modelid']);
		if($_GET['modelid']) {
			$style = $models[$modelid]['default_style'];
			$category_template = $models[$modelid]['category_template'];
			$list_template = $models[$modelid]['list_template'];
			$show_template = $models[$modelid]['show_template'];
			$html = array(
				'template_list'=> $style, 
				'category_template'=> form::select_template($style, 'content',$category_template,'name="setting[category_template]"','category'), 
				'list_template'=>form::select_template($style, 'content',$list_template,'name="setting[list_template]"','list'),
				'show_template'=>form::select_template($style, 'content',$show_template,'name="setting[show_template]"','show')
			);
			if (CHARSET == 'gbk') {
				$html = array_iconv($html, 'gbk', 'utf-8');
			}
			echo json_encode($html);
		}
	}
	/**
	 * 批量修改
	 */
	public function batch_edit() {
		$categorys = getcache('category_content_'.$this->siteid,'commons');
		if(isset($_POST['dosubmit'])) {
			
			pc_base::load_sys_func('iconv');	
			$catid = intval($_POST['catid']);
			$post_setting = $_POST['setting'];
			//栏目生成静态配置
			$infos = $info = array();
			$infos = $_POST['info'];
			if(empty($infos)) showmessage(L('operation_success'));
			$this->attachment_db = pc_base::load_model('attachment_model');
			foreach ($infos as $catid=>$info) {
				$setting = string2array($categorys[$catid]['setting']);
				if($_POST['type'] != 2) {
					if($post_setting[$catid]['ishtml']) {
						$setting['category_ruleid'] = $_POST['category_html_ruleid'][$catid];
					} else {
						$setting['category_ruleid'] = $_POST['category_php_ruleid'][$catid];
						$info['url'] = '';
					}
				}
				foreach($post_setting[$catid] as $_k=>$_setting) {
					$setting[$_k] = $_setting;
				}
				//内容生成静态配置
				if($post_setting[$catid]['content_ishtml']) {
					$setting['show_ruleid'] = $_POST['show_html_ruleid'][$catid];
				} else {
					$setting['show_ruleid'] = $_POST['show_php_ruleid'][$catid];
				}
				if($setting['repeatchargedays']<1) $setting['repeatchargedays'] = 1;
				$info['sethtml'] = $post_setting[$catid]['create_to_html_root'];
				$info['setting'] = array2string($setting);
				
				$info['module'] = 'content';
				$catname = CHARSET == 'gbk' ? $info['catname'] : iconv('utf-8','gbk',$info['catname']);
				$letters = gbk_to_pinyin($catname);
				$info['letter'] = strtolower(implode('', $letters));
				$this->db->update($info,array('catid'=>$catid,'siteid'=>$this->siteid));

				//更新附件状态
				if($info['image'] && pc_base::load_config('system','attachment_stat')) {
					$this->attachment_db->api_update($info['image'],'catid-'.$catid,1);
				}
			}
			$this->public_cache();
			showmessage(L('operation_success'),'?m=admin&c=category&a=init&module=admin&menuid=43');
		} else {
			if(isset($_POST['catids'])) {
				//获取站点模板信息
				pc_base::load_app_func('global');
				$template_list = template_list($this->siteid, 0);
				foreach ($template_list as $k=>$v) {
					$template_list[$v['dirname']] = $v['name'] ? $v['name'] : $v['dirname'];
					unset($template_list[$k]);
				}
				
				$show_validator = $show_header = '';
				$catid = intval($_GET['catid']);
				$type = $_POST['type'] ? intval($_POST['type']) : 0;
				pc_base::load_sys_class('form','',0);
				
				if(empty($_POST['catids'])) showmessage(L('illegal_parameters'));
				$batch_array = $workflows = array();
				foreach ($categorys as $catid=>$cat) {
					if($cat['type']==$type && in_array($catid, $_POST['catids'])) {
						$batch_array[$catid] = $cat;
					}
				}
				if(empty($batch_array)) showmessage(L('please_select_category')); 
				$workflows = getcache('workflow_'.$this->siteid,'commons');
				if($workflows) {
					$workflows_datas = array();
					foreach($workflows as $_k=>$_v) {
						$workflows_datas[$_v['workflowid']] = $_v['workname'];
					}
				}
				
				if($type==1) {
					include $this->admin_tpl('category_batch_edit_page');
				} else {
					include $this->admin_tpl('category_batch_edit');
				}
			} else {
				$type = isset($_GET['select_type']) ? intval($_GET['select_type']) : 0;
				
				$tree = pc_base::load_sys_class('tree');
				$tree->icon = array('&nbsp;&nbsp;│ ','&nbsp;&nbsp;├─ ','&nbsp;&nbsp;└─ ');
				$tree->nbsp = '&nbsp;&nbsp;';
				$category = array();
				foreach($categorys as $catid=>$r) {
					if($this->siteid != $r['siteid'] || ($r['type']==2 && $r['child']==0)) continue;
					$category[$catid] = $r;
				}
				$str  = "<option value='\$catid' \$selected>\$spacer \$catname</option>";
	
				$tree->init($category);
				$string .= $tree->get_tree(0, $str);
				include $this->admin_tpl('category_batch_select');
			}
		}	
	}

}
?>