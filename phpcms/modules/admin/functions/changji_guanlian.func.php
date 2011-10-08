<?php

/**
 * 调用关联菜单
 * @param $linkageid 联动菜单id
 * @param $id 生成联动菜单的样式id
 * @param $defaultvalue 默认值
 */
function menu_linkage_changjia($linkageid = 0, $id = 'linkid', $defaultvalue = 0) {
	$linkageid = intval($linkageid);
	$datas = array();
	$datas = getcache($linkageid,'linkage');
	$infos = $datas['data'];
	
	if($datas['style']=='1') {
		$title = $datas['title'];	
		$container = 'content'.random(3).date('is');
		if(!defined('DIALOG_INIT_1')) {
			define('DIALOG_INIT_1', 1);
			$string .= '<script type="text/javascript" src="'.JS_PATH.'dialog.js"></script>';
			$string .= '<link href="'.CSS_PATH.'dialog.css" rel="stylesheet" type="text/css">';
		}
		if(!defined('LINKAGE_INIT_1')) {
			define('LINKAGE_INIT_1', 1);
			$string .= '<script type="text/javascript" src="'.JS_PATH.'linkage/js/pop.js"></script>';
		}
		$var_div = $defaultvalue && (ROUTE_A=='edit' || ROUTE_A=='account_manage_info'  || ROUTE_A=='info_publish' || ROUTE_A=='orderinfo') ? menu_linkage_level($defaultvalue,$linkageid,$infos) : $datas['title'];
		$var_input = $defaultvalue && (ROUTE_A=='edit' || ROUTE_A=='account_manage_info'  || ROUTE_A=='info_publish') ? '<input type="hidden" name="info['.$id.']" value="'.$defaultvalue.'">' : '<input type="hidden" name="info['.$id.']" value="">';
		$string .= '<div name="'.$id.'" value="" id="'.$id.'" class="ib">'.$var_div.'</div>'.$var_input.' <input type="button" name="btn_'.$id.'" id="changjia_diqu" class="button" value="'.L('linkage_select').'" onclick="open_linkage(\''.$id.'\',\''.$title.'\','.$container.',\''.$linkageid.'\')">';				
		$string .= '<script type="text/javascript">';
		$string .= 'var returnid_'.$id.'= \''.$id.'\';';
		$string .= 'var returnkeyid_'.$id.' = \''.$linkageid.'\';';
		$string .=  'var '.$container.' = new Array(';
		foreach($infos AS $k=>$v) {
			if($v['parentid'] == 0) {
				$s[]='new Array(\''.$v['linkageid'].'\',\''.$v['name'].'\',\''.$v['parentid'].'\')';
			} else {
				continue;
			}
		}
		$s = implode(',',$s);
		$string .=$s;
		$string .= ')';
		$string .= '</script>';
	} else {
		$title = $defaultvalue ? $infos[$defaultvalue]['name'] : $datas['title'];	
		$colObj = random(3).date('is');
		$string = '';
		if(!defined('LINKAGE_INIT')) {
			define('LINKAGE_INIT', 1);
			$string .= '<script type="text/javascript" src="'.JS_PATH.'linkage/js/mln.colselect.js"></script>';
			if(defined('IN_ADMIN')) {
				$string .= '<link href="'.JS_PATH.'linkage/style/admin.css" rel="stylesheet" type="text/css">';
			} else {
				$string .= '<link href="'.JS_PATH.'linkage/style/css.css" rel="stylesheet" type="text/css">';
			}
		}
		$string .= '<input type="hidden" name="info['.$id.']" value="1"><div id="'.$id.'"></div>';
		$string .= '<script type="text/javascript">';
		$string .= 'var colObj'.$colObj.' = {"Items":[';
		
		foreach($infos AS $k=>$v) {
			$s .= '{"name":"'.$v['name'].'","topid":"'.$v['parentid'].'","colid":"'.$k.'","value":"'.$k.'","fun":function(){}},';
		}
	
		$string .= substr($s, 0, -1);
		$string .= ']};';
		$string .= '$("#'.$id.'").mlnColsel(colObj'.$colObj.',{';
		$string .= 'title:"'.$title.'",';
		$string .= 'value:"'.$defaultvalue.'",';
		$string .= 'width:100';
		$string .= '});';
		$string .= '</script>';
	}
	return $string;
}

/**
 * 调用厂家列表
 */
 function get_changjia(){
		
		$sql = query("select * form v9_changjia wher catid=9");
		var_dump($sql);
		
	return $result;
 }
?>