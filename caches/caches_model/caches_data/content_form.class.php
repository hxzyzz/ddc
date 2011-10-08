<?php
class content_form {
	var $modelid;
	var $fields;
	var $id;
	var $formValidator;

    function __construct($modelid,$catid = 0,$categorys = array()) {
		$this->modelid = $modelid;
		$this->catid = $catid;
		$this->categorys = $categorys;
		$this->fields = getcache('model_field_'.$modelid,'model');
		$this->siteid = get_siteid();
    }

	function get($data = array()) {
		$_groupid = param::get_cookie('_groupid');
		$this->data = $data;
		if(isset($data['id'])) $this->id = $data['id'];
		$info = array();
		$this->content_url = $data['url'];
		foreach($this->fields as $field=>$v) {
			if(defined('IN_ADMIN')) {
				if($v['iscore'] || check_in($_SESSION['roleid'], $v['unsetroleids'])) continue;
			} else {
				if($v['iscore'] || !$v['isadd'] || check_in($_groupid, $v['unsetgroupids'])) continue;
			}
			$func = $v['formtype'];
			$value = isset($data[$field]) ? htmlspecialchars($data[$field], ENT_QUOTES) : '';
			if($func=='pages' && isset($data['maxcharperpage'])) {
				$value = $data['paginationtype'].'|'.$data['maxcharperpage'];
			}
			if(!method_exists($this, $func)) continue;
			$form = $this->$func($field, $value, $v);
			if($form !== false) {
				if(defined('IN_ADMIN')) {
					if($v['isbase']) {
						$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
						$info['base'][$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$star,'isomnipotent'=>$v['isomnipotent'],'formtype'=>$v['formtype']);
					} else {
						$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
						$info['senior'][$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$star,'isomnipotent'=>$v['isomnipotent'],'formtype'=>$v['formtype']);
					}
				} else {
					$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
					$info[$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$star,'isomnipotent'=>$v['isomnipotent'],'formtype'=>$v['formtype']);
				}
			}
		}
		return $info;
	}
	function text($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$setting = string2array($setting);
		$size = $setting['size'];
		if(!$value) $value = $defaultvalue;
		$type = $ispassword ? 'password' : 'text';
		$errortips = $this->fields[$field]['errortips'];
		if($errortips || $minlength) $this->formValidator .= '$("#'.$field.'").formValidator({onshow:"",onfocus:"'.$errortips.'"}).inputValidator({min:1,onerror:"'.$errortips.'"});';
		return '<input type="text" name="info['.$field.']" id="'.$field.'" size="'.$size.'" value="'.$value.'" class="input-text" '.$formattribute.' '.$css.'>';
	}
	function textarea($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$setting = string2array($setting);
		extract($setting);
		if(!$value) $value = $defaultvalue;
		$allow_empty = 'empty:true,';
		if($minlength || $pattern) $allow_empty = '';
		if($errortips) $this->formValidator .= '$("#'.$field.'").formValidator({'.$allow_empty.'onshow:"'.$errortips.'",onfocus:"'.$errortips.'"}).inputValidator({min:1,onerror:"'.$errortips.'"});';
		$value = empty($value) ? $setting[defaultvalue] : $value;
		$str = "<textarea name='info[{$field}]' id='$field' style='width:{$width}%;height:{$height}px;' $formattribute $css";
		if($maxlength) $str .= " onkeyup=\"strlen_verify(this, '{$field}_len', {$maxlength})\"";
		$str .= ">{$value}</textarea>";
		if($maxlength) $str .= L('can_enter').'<B><span id="'.$field.'_len">'.$maxlength.'</span></B> '.L('characters');
		return $str;
	}
	function editor($field, $value, $fieldinfo) {
		$grouplist = getcache('grouplist','member');
		$_groupid = param::get_cookie('_groupid');
		$grouplist = $grouplist[$_groupid];
		extract($fieldinfo);
		extract(string2array($setting));
		$disabled_page = isset($disabled_page) ? $disabled_page : 0;
		if(!$height) $height = 300;
		$allowupload = defined('IN_ADMIN') ? 1 : $grouplist['allowattachment'] ? 1: 0;
		if(!$value) $value = $defaultvalue;
		if($minlength || $pattern) $allow_empty = '';
		if($minlength) $this->formValidator .= '$("#'.$field.'").formValidator({'.$allow_empty.'onshow:"",onfocus:"'.$errortips.'"}).functionValidator({
	    fun:function(val,elem){
			var oEditor = CKEDITOR.instances.'.$field.';
			var data = oEditor.getData();
	        if($(\'#islink\').attr(\'checked\')){
			    return true;
		    } else if(($(\'#islink\').attr(\'checked\')==false) && (data==\'\')){
			    return "'.$errortips.'";
		    } else if (data==\'\' || $.trim(data)==\'\') {
				return "'.$errortips.'";
			}
			return true;
		}
	});';
		return "<div id='{$field}_tip'></div>".'<textarea name="info['.$field.']" id="'.$field.'" boxid="'.$field.'">'.$value.'</textarea>'.form::editor($field,$toolbar,'content',$this->catid,'',$allowupload,1,'',$height,$disabled_page);
	}
	function catid($field, $value, $fieldinfo) {
		if(!$value) $value = $this->catid;
		$publish_str = '';
		if(defined('IN_ADMIN') && ROUTE_A=='add') $publish_str = " <a href='javascript:;' onclick=\"omnipotent('selectid','?m=content&c=content&a=add_othors&siteid=".$this->siteid."','".L('publish_to_othor_category')."',1);return false;\" style='color:#B5BFBB'>[".L('publish_to_othor_category')."]</a><ul class='list-dot-othors' id='add_othors_text'></ul>";
		return '<input type="hidden" name="info['.$field.']" value="'.$value.'">'.$this->categorys[$value]['catname'].$publish_str;
	}
	function title($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$style_arr = explode(';',$this->data['style']);
		$style_color = $style_arr[0];
		$style_font_weight = $style_arr[1] ? $style_arr[1] : '';

		$style = 'color:'.$this->data['style'];
		if(!$value) $value = $defaultvalue;
		$errortips = $this->fields[$field]['errortips'];
		$errortips_max = L('title_is_empty');
		if($errortips) $this->formValidator .= '$("#'.$field.'").formValidator({onshow:"",onfocus:"'.$errortips.'"}).inputValidator({min:'.$minlength.',max:'.$maxlength.',onerror:"'.$errortips_max.'"});';
		$str = '<input type="text" style="width:400px;'.($style_color ? 'color:'.$style_color.';' : '').($style_font_weight ? 'font-weight:'.$style_font_weight.';' : '').'" name="info['.$field.']" id="'.$field.'" value="'.$value.'" style="'.$style.'" class="measure-input " onBlur="$.post(\'api.php?op=get_keywords&number=3&sid=\'+Math.random()*5, {data:$(\'#title\').val()}, function(data){if(data && $(\'#keywords\').val()==\'\') $(\'#keywords\').val(data); })" onkeyup="strlen_verify(this, \'title_len\', '.$maxlength.');"/><input type="hidden" name="style_color" id="style_color" value="'.$style_color.'">
		<input type="hidden" name="style_font_weight" id="style_font_weight" value="'.$style_font_weight.'">';
		if(defined('IN_ADMIN')) $str .= '<input type="button" class="button" id="check_title_alt" value="'.L('check_title','','content').'" onclick="$.get(\'?m=content&c=content&a=public_check_title&catid='.$this->catid.'&sid=\'+Math.random()*5, {data:$(\'#title\').val()}, function(data){if(data==\'1\') {$(\'#check_title_alt\').val(\''.L('title_repeat').'\');$(\'#check_title_alt\').css(\'background-color\',\'#FFCC66\');} else if(data==\'0\') {$(\'#check_title_alt\').val(\''.L('title_not_repeat').'\');$(\'#check_title_alt\').css(\'background-color\',\'#F8FFE1\')}})" style="width:73px;"/><img src="'.IMG_PATH.'icon/colour.png" width="15" height="16" onclick="colorpicker(\''.$field.'_colorpanel\',\'set_title_color\');" style="cursor:hand"/> 
		<img src="'.IMG_PATH.'icon/bold.png" width="10" height="10" onclick="input_font_bold()" style="cursor:hand"/> <span id="'.$field.'_colorpanel" style="position:absolute;" class="colorpanel"></span>';
		$str .= L('can_enter').'<B><span id="title_len">'.$maxlength.'</span></B> '.L('characters');
		return $str;
	}
	function box($field, $value, $fieldinfo) {

		$setting = string2array($fieldinfo['setting']);
		if($value=='') $value = $this->fields[$field]['defaultvalue'];
		$options = explode("\n",$this->fields[$field]['options']);
		foreach($options as $_k) {
			$v = explode("|",$_k);
			$k = trim($v[1]);
			$option[$k] = $v[0];
		}
		$values = explode(',',$value);
		$value = array();
		foreach($values as $_k) {
			if($_k != '') $value[] = $_k;
		}
		$value = implode(',',$value);
		switch($this->fields[$field]['boxtype']) {
			case 'radio':
				$string = form::radio($option,$value,"name='info[$field]' $fieldinfo[formattribute]",$setting['width'],$field);
			break;

			case 'checkbox':
				$string = form::checkbox($option,$value,"name='info[$field][]' $fieldinfo[formattribute]",1,$setting['width'],$field);
			break;

			case 'select':
				$string = form::select($option,$value,"name='info[$field]' id='$field' $fieldinfo[formattribute]");
			break;

			case 'multiple':
				$string = form::select($option,$value,"name='info[$field][]' id='$field ' size=2 multiple='multiple' style='height:60px;' $fieldinfo[formattribute]");
			break;
		}
		return $string;
	}
	function image($field, $value, $fieldinfo) {
		$setting = string2array($fieldinfo['setting']);
		extract($setting);
		if(!defined('IMAGES_INIT')) {
			$str = '<script type="text/javascript" src="'.JS_PATH.'swfupload/swf2ckeditor.js"></script>';
			define('IMAGES_INIT', 1);
		}
		$html = '';
		if (defined('IN_ADMIN')) {
			$html = "<input type=\"button\" style=\"width: 66px;\" class=\"button\" onclick=\"crop_cut_".$field."($('#$field').val());return false;\" value=\"".L('cut_the_picture','','content')."\"><input type=\"button\" style=\"width: 66px;\" class=\"button\" onclick=\"$('#".$field."_preview').attr('src','".IMG_PATH."icon/upload-pic.png');$('#".$field."').val(' ');return false;\" value=\"".L('cancel_the_picture','','content')."\"><script type=\"text/javascript\">function crop_cut_".$field."(id){
	if (id=='') { alert('".L('upload_thumbnails', '', 'content')."');return false;}
	window.top.art.dialog({title:'".L('cut_the_picture','','content')."', id:'crop', iframe:'index.php?m=content&c=content&a=public_crop&module=content&catid='+catid+'&picurl='+encodeURIComponent(id)+'&input=$field&preview=".($show_type && defined('IN_ADMIN') ? $field."_preview" : '')."', width:'680px', height:'480px'}, 	function(){var d = window.top.art.dialog({id:'crop'}).data.iframe;
	d.uploadfile();return false;}, function(){window.top.art.dialog({id:'crop'}).close()});
};</script>";
			}
		$authkey = upload_key("1,$upload_allowext,$isselectimage,$images_width,$images_height,$watermark");
		if($show_type && defined('IN_ADMIN')) {
			$preview_img = $value ? $value : IMG_PATH.'icon/upload-pic.png';
			return $str."<div class='upload-pic img-wrap'><input type='hidden' name='info[$field]' id='$field' value='$value'>
			<a href='javascript:void(0);' onclick=\"flashupload('{$field}_images', '".L('attachment_upload', '', 'content')."','{$field}',thumb_images,'1,{$upload_allowext},$isselectimage,$images_width,$images_height,$watermark','content','$this->catid','$authkey');return false;\">
			<img src='$preview_img' id='{$field}_preview' width='135' height='113' style='cursor:hand' /></a>".$html."</div>";
		} else {
			return $str."<input type='text' name='info[$field]' id='$field' value='$value' size='$size' class='input-text' />  <input type='button' class='button' onclick=\"flashupload('{$field}_images', '".L('attachment_upload', '', 'content')."','{$field}',submit_images,'1,{$upload_allowext},$isselectimage,$images_width,$images_height,$watermark','content','$this->catid','$authkey')\"/ value='".L('upload_pic', '', 'content')."'>".$html;
		}
	}
	function images($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$list_str = '';
		if($value) {
			$value = string2array(html_entity_decode($value,ENT_QUOTES));
			if(is_array($value)) {
				foreach($value as $_k=>$_v) {
				$list_str .= "<li id='image_{$field}_{$_k}' style='padding:1px'><input type='text' name='{$field}_url[]' value='{$_v[url]}' style='width:310px;' ondblclick='image_priview(this.value);' class='input-text'> <input type='text' name='{$field}_alt[]' value='{$_v[alt]}' style='width:160px;' class='input-text'> <a href=\"javascript:remove_div('image_{$field}_{$_k}')\">".L('remove_out', '', 'content')."</a></li>";
				}
			}
		} else {
			$list_str .= "<center><div class='onShow' id='nameTip'>".L('upload_pic_max', '', 'content')." <font color='red'>{$upload_number}</font> ".L('tips_pics', '', 'content')."</div></center>";
		}
		$string = '<input name="info['.$field.']" type="hidden" value="1">
		<fieldset class="blue pad-10">
        <legend>'.L('pic_list').'</legend>';
		$string .= $list_str;
		$string .= '<ul id="'.$field.'" class="picList"></ul>
		</fieldset>
		<div class="bk10"></div>
		';
		if(!defined('IMAGES_INIT')) {
			$str = '<script type="text/javascript" src="statics/js/swfupload/swf2ckeditor.js"></script>';
			define('IMAGES_INIT', 1);
		}
		$authkey = upload_key("$upload_number,$upload_allowext,$isselectimage");
		$string .= $str."<div class='picBut cu'><a herf='javascript:void(0);' onclick=\"javascript:flashupload('{$field}_images', '".L('attachment_upload')."','{$field}',change_images,'{$upload_number},{$upload_allowext},{$isselectimage}','content','$this->catid','{$authkey}')\"/> ".L('select_pic')." </a></div>";
		return $string;
	}	function number($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$setting = string2array($setting);
		$size = $setting['size'];		
		if(!$value) $value = $defaultvalue;
		return "<input type='text' name='info[$field]' id='$field' value='$value' class='input-text' size='$size' {$formattribute} {$css}>";
	}
	function datetime($field, $value, $fieldinfo) {
		extract(string2array($fieldinfo['setting']));
		$isdatetime = 0;
		if($fieldtype=='int') {
			if(!$value) $value = SYS_TIME;
			$format_txt = $format == 'm-d' ? 'm-d' : $format;
			$value = date($format_txt,$value);
			$isdatetime = strlen($format) > 6 ? 1 : 0;
		} elseif($fieldtype=='datetime') {
			$isdatetime = 1;
		}
		return form::date("info[$field]",$value,$isdatetime,1);
	}
	function posid($field, $value, $fieldinfo) {
		$setting = string2array($fieldinfo['setting']);
		$position = getcache('position','commons');
		if(empty($position)) return '';
		$array = array();
		foreach($position as $_key=>$_value) {
			if($_value['modelid'] && ($_value['modelid'] !=  $this->modelid) || ($_value['catid'] && strpos(','.$this->categorys[$_value['catid']]['arrchildid'].',',','.$this->catid.',')===false)) continue;
			$array[$_key] = $_value['name'];
		}
		$posids = array();
		if(ROUTE_A=='edit') {
			$this->position_data_db = pc_base::load_model('position_data_model');
			$result = $this->position_data_db->select(array('id'=>$this->id,'modelid'=>$this->modelid),'*','','','','posid');
			$posids = implode(',', array_keys($result));
		} else {
			$posids = $setting['defaultvalue'];
		}
		return "<input type='hidden' name='info[$field][]' value='-1'>".form::checkbox($array,$posids,"name='info[$field][]'",'',$setting['width']);
	}
	function keyword($field, $value, $fieldinfo) {
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		return "<input type='text' name='info[$field]' id='$field' value='$value' style='width:280px' {$formattribute} {$css} class='input-text'>";
	}
	function author($field, $value, $fieldinfo) {
		return '<input type="text" name="info['.$field.']" value="'.$value.'" size="30">';
	}
	function copyfrom($field, $value, $fieldinfo) {
		$value_data = '';
		if($value && strpos($value,'|')!==false) {
			$arr = explode('|',$value);
			$value = $arr[0];
			$value_data = $arr[1];
		}
		$copyfrom_array = getcache('copyfrom','admin');
		$copyfrom_datas = array(L('copyfrom_tips'));
		if(!empty($copyfrom_array)) {
			foreach($copyfrom_array as $_k=>$_v) {
				if($this->siteid==$_v['siteid']) $copyfrom_datas[$_k] = $_v['sitename'];
			}
		}
		return "<input type='text' name='info[$field]' value='$value' style='width: 400px;' class='input-text'>".form::select($copyfrom_datas,$value_data,"name='{$field}_data' ");
	}
	function groupid($field, $value, $fieldinfo) {
		extract(string2array($fieldinfo['setting']));
		$grouplist = getcache('grouplist','member');
		foreach($grouplist as $_key=>$_value) {
			$data[$_key] = $_value['name'];
		}
		return '<input type="hidden" name="info['.$field.']" value="1">'.form::checkbox($data,$value,'name="'.$field.'[]" id="'.$field.'"','','120');
	}
	function islink($field, $value, $fieldinfo) {
		if($value) {
			$url = $this->data['url'];
			$checked = 'checked';
			$_GET['islink'] = 1;
		} else {
			$disabled = 'disabled';
			$url = $checked = '';
			$_GET['islink'] = 0;
		}
		$size = $fieldinfo['size'] ? $fieldinfo['size'] : 25;
		return '<input type="hidden" name="info[islink]" value="0"><input type="text" name="linkurl" id="linkurl" value="'.$url.'" size="'.$size.'" maxlength="255" '.$disabled.' class="input-text"> <input name="info[islink]" type="checkbox" id="islink" value="1" onclick="ruselinkurl();" '.$checked.'> <font color="red">'.L('islink_url').'</font>';
	}
	function template($field, $value, $fieldinfo) {
		$sitelist = getcache('sitelist','commons');
		$default_style = $sitelist[$this->siteid]['default_style'];
		return form::select_template($default_style,'content',$value,'name="info['.$field.']" id="'.$field.'"','show');
	}
	function pages($field, $value, $fieldinfo) {
		extract($fieldinfo);
		if($value) {
			$v = explode('|', $value);
			$data = "<select name=\"info[paginationtype]\" id=\"paginationtype\" onchange=\"if(this.value==1)\$('#paginationtype1').css('display','');else \$('#paginationtype1').css('display','none');\">";
			$type = array(L('page_type1'), L('page_type2'), L('page_type3'));
			if($v[0]==1) $con = 'style="display:"';
			else $con = 'style="display:none"';
			foreach($type as $i => $val) {
				if($i==$v[0]) $tag = 'selected';
				else $tag = '';
				$data .= "<option value=\"$i\" $tag>$val</option>";
			}
			$data .= "</select><span id=\"paginationtype1\" $con><input name=\"info[maxcharperpage]\" type=\"text\" id=\"maxcharperpage\" value=\"$v[1]\" size=\"8\" maxlength=\"8\">".L('page_maxlength')."</span>";
			return $data;
		} else {
			return "<select name=\"info[paginationtype]\" id=\"paginationtype\" onchange=\"if(this.value==1)\$('#paginationtype1').css('display','');else \$('#paginationtype1').css('display','none');\">
                <option value=\"0\">".L('page_type1')."</option>
                <option value=\"1\">".L('page_type2')."</option>
                <option value=\"2\">".L('page_type3')."</option>
            </select>
			<span id=\"paginationtype1\" style=\"display:none\"><input name=\"info[maxcharperpage]\" type=\"text\" id=\"maxcharperpage\" value=\"10000\" size=\"8\" maxlength=\"8\">".L('page_maxlength')."</span>";
		}
	}
	function typeid($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$setting = string2array($setting);
		if(!$value) $value = $setting['defaultvalue'];
		if($errortips) {
			$errortips = $this->fields[$field]['errortips'];
			$this->formValidator .= '$("#'.$field.'").formValidator({onshow:"",onfocus:"'.$errortips.'"}).inputValidator({min:1,onerror:"'.$errortips.'"});';
		}
		$usable_type = $this->categorys[$this->catid]['usable_type'];
		$usable_array = array();
		if($usable_type) $usable_array = explode(',',$usable_type);
		
		$type_data = getcache('type_content','commons');
		foreach($type_data as $_key=>$_value) {
			if(in_array($_key,$usable_array)) $data[$_key] = $_value['name'];
		}
		return form::select($data,$value,'name="info['.$field.']" id="'.$field.'" '.$formattribute.' '.$css,L('copyfrom_tips'));
	}
	function readpoint($field, $value, $fieldinfo) {
		$paytype = $this->data['paytype'];
		if($paytype) {
			$checked1 = '';
			$checked2 = 'checked';
		} else {
			$checked1 = 'checked';
			$checked2 = '';
		}
		return '<input type="text" name="info['.$field.']" value="'.$value.'" size="5"><input type="radio" name="info[paytype]" value="0" '.$checked1.'> '.L('point').' <input type="radio" name="info[paytype]" value="1" '.$checked2.'>'.L('money');
	}
	function linkage($field, $value, $fieldinfo) {
		$setting = string2array($fieldinfo['setting']);
		$linkageid = $setting['linkageid'];
		return menu_linkage($linkageid,$field,$value);
	}
	function downfile($field, $value, $fieldinfo) {
		$list_str = $str = '';
		extract(string2array($fieldinfo['setting']));
		if($value){
			$value_arr = explode('|',$value);
			$value = $value_arr['0'];
			$sel_server = $value_arr['1'] ? explode(',',$value_arr['1']) : '';
			$edit = 1;
		} else {
			$edit = 0;
		}
		$server_list = getcache('downservers','commons');
		if(is_array($server_list)) {
			foreach($server_list as $_k=>$_v) {
				if (in_array($_v['siteid'],array(0,$fieldinfo['siteid']))) {
					$checked = $edit ? ((is_array($sel_server) && in_array($_k,$sel_server)) ? ' checked' : '') : ' checked';
					$list_str .= "<lable id='downfile{$_k}' class='ib lh24' style='width:25%'><input type='checkbox' value='{$_k}' name='{$field}_servers[]' {$checked}>  {$_v['sitename']}</lable>";
				}
			}
		}
	
		$string = '
		<fieldset class="blue pad-10">
        <legend>'.L('mirror_server_list').'</legend>';
		$string .= $list_str;
		$string .= '</fieldset>
		<div class="bk10"></div>
		';	
		if(!defined('IMAGES_INIT')) {
			$str = '<script type="text/javascript" src="'.JS_PATH.'swfupload/swf2ckeditor.js"></script>';
			define('IMAGES_INIT', 1);
		}	
		$authkey = upload_key("$upload_number,$upload_allowext,$isselectimage");	
		$string .= $str."<input type='text' name='info[$field]' id='$field' value='$value' class='input-text' style='width:80%'/>  <input type='button' class='button' onclick=\"javascript:flashupload('{$field}_downfield', '".L('attachment_upload')."','{$field}',submit_files,'{$upload_number},{$upload_allowext},{$isselectimage}','content','$this->catid','{$authkey}')\"/ value='".L('upload_soft')."'>";
		return $string;
	}
	function downfiles($field, $value, $fieldinfo) {
		extract(string2array($fieldinfo['setting']));
		$list_str = '';
		if($value) {
			$value = string2array(html_entity_decode($value,ENT_QUOTES));
			if(is_array($value)) {
				foreach($value as $_k=>$_v) {
				$list_str .= "<div id='multifile{$_k}'><input type='text' name='{$field}_fileurl[]' value='{$_v[fileurl]}' style='width:310px;' class='input-text'> <input type='text' name='{$field}_filename[]' value='{$_v[filename]}' style='width:160px;' class='input-text'> <a href=\"javascript:remove_div('multifile{$_k}')\">".L('remove_out')."</a></div>";
				}
			}
		}
		$string = '<input name="info['.$field.']" type="hidden" value="1">
		<fieldset class="blue pad-10">
        <legend>'.L('file_list').'</legend>';
		$string .= $list_str;
		$string .= '<ul id="'.$field.'" class="picList"></ul>
		</fieldset>
		<div class="bk10"></div>
		';
		
		if(!defined('IMAGES_INIT')) {
			$str = '<script type="text/javascript" src="'.JS_PATH.'swfupload/swf2ckeditor.js"></script>';
			define('IMAGES_INIT', 1);
		}
		$authkey = upload_key("$upload_number,$upload_allowext,$isselectimage");
		$string .= $str."<input type=\"button\"  class=\"button\" value=\"".L('multiple_file_list')."\" onclick=\"javascript:flashupload('{$field}_multifile', '".L('attachment_upload')."','{$field}',change_multifile,'{$upload_number},{$upload_allowext},{$isselectimage}','content','$this->catid','{$authkey}')\"/>    <input type=\"button\" class=\"button\" value=\"".L('add_remote_url')."\" onclick=\"add_multifile('{$field}')\">";
		return $string;
	}
	function map($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$setting = string2array($setting);
		$size = $setting['size'];
		$errortips = $this->fields[$field]['errortips'];
		$modelid = $this->fields[$field]['modelid'];
		$tips = $value ? L('editmark','','map') : L('addmark','','map');
		return '<input type="button" name="'.$field.'_mark" id="'.$field.'_mark" value="'.$tips.'" class="button" onclick="omnipotent(\'selectid\',\''.APP_PATH.'api.php?op=map&field='.$field.'&modelid='.$modelid.'\',\''.L('mapmark','','map').'\',1,700,420)"><input type="hidden" name="info['.$field.']" value="'.$value.'" id="'.$field.'" >';
	}
	function omnipotent($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$formtext = str_replace('{FIELD_VALUE}',$value,$formtext);
		$formtext = str_replace('{MODELID}',$this->modelid,$formtext);
		preg_match_all('/{FUNC\((.*)\)}/',$formtext,$_match);
		foreach($_match[1] as $key=>$match_func) {
			$string = '';
			$params = explode('~~',$match_func);
			$user_func = $params[0];
			$string = $user_func($params[1]);
			$formtext = str_replace($_match[0][$key],$string,$formtext);
		}
		$id  = $this->id ? $this->id : 0;
		$formtext = str_replace('{ID}',$id,$formtext);
		$errortips = $this->fields[$field]['errortips'];
		if($errortips) $this->formValidator .= '$("#'.$field.'").formValidator({onshow:"",onfocus:"'.$errortips.'"}).inputValidator({min:'.$minlength.',max:'.$maxlength.',onerror:"'.$errortips.'"});';

		if($errortips) $this->formValidator .= '$("#'.$field.'").formValidator({onshow:"'.$errortips.'",onfocus:"'.$errortips.'"}).inputValidator({min:1,onerror:"'.$errortips.'"});';
		return $formtext;
	}
	function linkfield($field, $value, $fieldinfo) {
        extract($fieldinfo);
        $setting = string2array($setting);
		if($setting['field_where']=="")$setting['field_where']="1=1";
		$sql = "SELECT `".$setting['field_value']."`, `".$setting['field_title']."` FROM `".$setting['table_name']."` WHERE ".$setting['field_where']."";
        $get_db = pc_base::load_model("get_model");
        $r= $get_db->query($sql);
        while(($s = $get_db->fetch_next()) != false) {
            $dataArr[] = $s;
        } 
        $value = str_replace('&amp;','&',$value);
		$data = '<select name="info['.$fieldinfo['field'].']" id="'.$fieldinfo['field'].'" '.$formattribute.'><option>请选择</option>';
		foreach($dataArr as $v) {            
			if($v[$setting['field_value']] == $value) $select = 'selected';
			else $select = '';
			$data .= "<option value='".$v[$fieldinfo['field_value']]."' ".$select.">".$v[$fieldinfo['field_title']]."</option>\n";
		}
		$data .= '</select>';
		return $data;
	}	function linkfield_liandong($field, $value, $fieldinfo) {
        extract($fieldinfo);
		$setting = string2array($setting);
		$dataArr = subcat($setting[field_catid],0,0,1);

        $value = str_replace('&amp;','&',$value);
		$data = '<select name="s1'.$fieldinfo['field'].'" id="'.$fieldinfo['field'].'" onChange="changeselect_'.$fieldinfo['field'].'_1(this.value);'.$formattribute.'"><option value="">品牌</option>';
		foreach($dataArr as $v) {            
			if($v[catid] == $value) $select = 'selected';
			else $select = '';
			$data .= "<option value='".$v[catid]."' ".$select.">".$v[catname]."</option>\n";
		}		
		$data .= '</select>';

		$data .= '<select name="s'.$fieldinfo['field'].'" onChange="changeselect_'.$fieldinfo['field'].'_2(this.value)"><option value="">系列</option></select>';

		$data .= '<select name="info['.$fieldinfo['field'].']" id="'.$fieldinfo['field'].'" '.$formattribute.'>';
		if($value!=""){
			$sql = "SELECT id,title FROM `".$setting['table_name']."` WHERE id=".$value."";
			$get_db = pc_base::load_model("get_model");
			$r= $get_db->query($sql);
			while(($s = $get_db->fetch_next()) != false) {
				$dataArr_select[] = $s;
			} 
			foreach($dataArr_select as $v) {            
				$data .= "<option value='".$v[id]."' ".$select.">".$v[title]."</option>\n";
			}
		}else{
			$data .= '<option value="">型号</option>';
		}
		$data .= '</select>';

		$data .= '<script language=JavaScript>';
		 
		//二级菜单数组
		$data .= 'var subcat'.$fieldinfo['field'].' = new Array();';
		$i=0;
		foreach($dataArr as $v) {
			$dataArr_sub = subcat($v[catid],0,0,1);
			foreach($dataArr_sub as $v) { 
				//var_dump();
				$data .= "subcat".$fieldinfo['field']."[".$i++."] = new Array('".$v["parentid"]."','".$v["catname"]."','".$v["catid"]."');\n";
			}
		}

		$data .= 'var subcat2'.$fieldinfo['field'].' = new Array();';
		
		foreach($dataArr as $v) {
			$dataArr_sub = subcat($v[catid],0,0,1);
			foreach($dataArr_sub as $v) { 
				//三级菜单数组
				$sql = "SELECT catid,title,id FROM `".$setting['table_name']."` WHERE catid=".$v["catid"]."";
				$get_db = pc_base::load_model("get_model");
				$r= $get_db->query($sql);
				while(($s = $get_db->fetch_next()) != false) {
					$dataArr_sub2[] = $s;
				}
				$y=0;
				foreach($dataArr_sub2 as $r) { 
					$data .= "subcat2".$fieldinfo['field']."[".$y++."] = new Array('".$r["catid"]."','".$r["id"]."','".$r["title"]."');\n";
				}
			}
		}
		$data .= 'function changeselect_'.$fieldinfo['field'].'_1(locationid)';
		$data .= '{';
		$data .= 'document.myform.s'.$fieldinfo['field'].'.length = 0;';
		$data .= 'document.myform.s'.$fieldinfo['field'].'.options[0] = new Option("选择系列","");';
		$data .= 'for (i=0; i<subcat'.$fieldinfo['field'].'.length; i++)';
		$data .= '{';
		$data .= 'if (subcat'.$fieldinfo['field'].'[i][0] == locationid)';
		$data .= '{';
		$data .= 'document.myform.s'.$fieldinfo['field'].'.options[document.myform.s'.$fieldinfo['field'].'.length] = new Option(subcat'.$fieldinfo['field'].'[i][1], subcat'.$fieldinfo['field'].'[i][2]);';
		$data .= '}';
		$data .= '}';
		$data .= '}';

		$data .= 'function changeselect_'.$fieldinfo['field'].'_2(locationid){';
		$data .= 'document.myform.elements["info['.$fieldinfo['field'].']"].length = 0;';
		$data .= 'document.myform.elements["info['.$fieldinfo['field'].']"].options[0] = new Option("选择型号","");';
		$data .= 'for (i=0; i<subcat2'.$fieldinfo['field'].'.length; i++)';
		$data .= '{';
		$data .= 'if (subcat2'.$fieldinfo['field'].'[i][0] == locationid)';
		$data .= '{';
		$data .= 'document.myform.elements["info['.$fieldinfo['field'].']"].options[document.myform.elements["info['.$fieldinfo['field'].']"].length] = new Option(subcat2'.$fieldinfo['field'].'[i][2], subcat2'.$fieldinfo['field'].'[i][1]);';
		$data .= '}';
		$data .= '}';
		$data .= '}';
		$data .= '</script>';

		return $data;
	}
	function letter($field, $value, $fieldinfo) {
		extract(string2array($fieldinfo['setting']));
		$is_big = $is_big ? 'myConvertToUpper' : 'myConvertToLower';
		$return = $return_all ? '$("#'.$field.'").val('.$is_big.'($(this).val()))' : '$("#'.$field.'").val('.$is_big.'($(this).val()).substr(0, 1))';
		$js = <<<EOT

		<script type="text/javascript" language="javascript" >
    $("document").ready(function(){
            $("#$fields").blur(function(){
				$return;
            });
    });
	function hash(_key,_value){
	this.key = _key; /* 拼音*/
	this.value = _value; /* ascii码*/
}
/* javascript 的自定义对象，用于存放汉字拼音数据字典*/
function dictionary(){
	this.items = [];
	this.add = function(_key,_value){
		this.items[this.items.length] = new hash(_key,_value);
	}
}
/*汉字拼音的数据字典-共396个-通过组合声母和韵母*/
var d = new dictionary();
d.add("a",-20319);
d.add("ai",-20317);
d.add("an",-20304);
d.add("ang",-20295);
d.add("ao",-20292);
d.add("ba",-20283);
d.add("bai",-20265);
d.add("ban",-20257);
d.add("bang",-20242);
d.add("bao",-20230);
d.add("bei",-20051);
d.add("ben",-20036);
d.add("beng",-20032);
d.add("bi",-20026);
d.add("bian",-20002);
d.add("biao",-19990);
d.add("bie",-19986);
d.add("bin",-19982);
d.add("bing",-19976);
d.add("bo",-19805);
d.add("bu",-19784);
d.add("ca",-19775);
d.add("cai",-19774);
d.add("can",-19763);
d.add("cang",-19756);
d.add("cao",-19751);
d.add("ce",-19746);
d.add("ceng",-19741);
d.add("cha",-19739);
d.add("chai",-19728);
d.add("chan",-19725);
d.add("chang",-19715);
d.add("chao",-19540);
d.add("che",-19531);
d.add("chen",-19525);
d.add("cheng",-19515);
d.add("chi",-19500);
d.add("chong",-19484);
d.add("chou",-19479);
d.add("chu",-19467);
d.add("chuai",-19289);
d.add("chuan",-19288);
d.add("chuang",-19281);
d.add("chui",-19275);
d.add("chun",-19270);
d.add("chuo",-19263);
d.add("ci",-19261);
d.add("cong",-19249);
d.add("cou",-19243);
d.add("cu",-19242);
d.add("cuan",-19238);
d.add("cui",-19235);
d.add("cun",-19227);
d.add("cuo",-19224);
d.add("da",-19218);
d.add("dai",-19212);
d.add("dan",-19038);
d.add("dang",-19023);
d.add("dao",-19018);
d.add("de",-19006);
d.add("deng",-19003);
d.add("di",-18996);
d.add("dian",-18977);
d.add("diao",-18961);
d.add("die",-18952);
d.add("ding",-18783);
d.add("diu",-18774);
d.add("dong",-18773);
d.add("dou",-18763);
d.add("du",-18756);
d.add("duan",-18741);
d.add("dui",-18735);
d.add("dun",-18731);
d.add("duo",-18722);
d.add("e",-18710);
d.add("en",-18697);
d.add("er",-18696);
d.add("fa",-18526);
d.add("fan",-18518);
d.add("fang",-18501);
d.add("fei",-18490);
d.add("fen",-18478);
d.add("feng",-18463);
d.add("fo",-18448);
d.add("fou",-18447);
d.add("fu",-18446);
d.add("ga",-18239);
d.add("gai",-18237);
d.add("gan",-18231);
d.add("gang",-18220);
d.add("gao",-18211);
d.add("ge",-18201);
d.add("gei",-18184);
d.add("gen",-18183);
d.add("geng",-18181);
d.add("gong",-18012);
d.add("gou",-17997);
d.add("gu",-17988);
d.add("gua",-17970);
d.add("guai",-17964);
d.add("guan",-17961);
d.add("guang",-17950);
d.add("gui",-17947);
d.add("gun",-17931);
d.add("guo",-17928);
d.add("ha",-17922);
d.add("hai",-17759);
d.add("han",-17752);
d.add("hang",-17733);
d.add("hao",-17730);
d.add("he",-17721);
d.add("hei",-17703);
d.add("hen",-17701);
d.add("heng",-17697);
d.add("hong",-17692);
d.add("hou",-17683);
d.add("hu",-17676);
d.add("hua",-17496);
d.add("huai",-17487);
d.add("huan",-17482);
d.add("huang",-17468);
d.add("hui",-17454);
d.add("hun",-17433);
d.add("huo",-17427);
d.add("ji",-17417);
d.add("jia",-17202);
d.add("jian",-17185);
d.add("jiang",-16983);
d.add("jiao",-16970);
d.add("jie",-16942);
d.add("jin",-16915);
d.add("jing",-16733);
d.add("jiong",-16708);
d.add("jiu",-16706);
d.add("ju",-16689);
d.add("juan",-16664);
d.add("jue",-16657);
d.add("jun",-16647);
d.add("ka",-16474);
d.add("kai",-16470);
d.add("kan",-16465);
d.add("kang",-16459);
d.add("kao",-16452);
d.add("ke",-16448);
d.add("ken",-16433);
d.add("keng",-16429);
d.add("kong",-16427);
d.add("kou",-16423);
d.add("ku",-16419);
d.add("kua",-16412);
d.add("kuai",-16407);
d.add("kuan",-16403);
d.add("kuang",-16401);
d.add("kui",-16393);
d.add("kun",-16220);
d.add("kuo",-16216);
d.add("la",-16212);
d.add("lai",-16205);
d.add("lan",-16202);
d.add("lang",-16187);
d.add("lao",-16180);
d.add("le",-16171);
d.add("lei",-16169);
d.add("leng",-16158);
d.add("li",-16155);
d.add("lia",-15959);
d.add("lian",-15958);
d.add("liang",-15944);
d.add("liao",-15933);
d.add("lie",-15920);
d.add("lin",-15915);
d.add("ling",-15903);
d.add("liu",-15889);
d.add("long",-15878);
d.add("lou",-15707);
d.add("lu",-15701);
d.add("lv",-15681);
d.add("luan",-15667);
d.add("lue",-15661);
d.add("lun",-15659);
d.add("luo",-15652);
d.add("ma",-15640);
d.add("mai",-15631);
d.add("man",-15625);
d.add("mang",-15454);
d.add("mao",-15448);
d.add("me",-15436);
d.add("mei",-15435);
d.add("men",-15419);
d.add("meng",-15416);
d.add("mi",-15408);
d.add("mian",-15394);
d.add("miao",-15385);
d.add("mie",-15377);
d.add("min",-15375);
d.add("ming",-15369);
d.add("miu",-15363);
d.add("mo",-15362);
d.add("mou",-15183);
d.add("mu",-15180);
d.add("na",-15165);
d.add("nai",-15158);
d.add("nan",-15153);
d.add("nang",-15150);
d.add("nao",-15149);
d.add("ne",-15144);
d.add("nei",-15143);
d.add("nen",-15141);
d.add("neng",-15140);
d.add("ni",-15139);
d.add("nian",-15128);
d.add("niang",-15121);
d.add("niao",-15119);
d.add("nie",-15117);
d.add("nin",-15110);
d.add("ning",-15109);
d.add("niu",-14941);
d.add("nong",-14937);
d.add("nu",-14933);
d.add("nv",-14930);
d.add("nuan",-14929);
d.add("nue",-14928);
d.add("nuo",-14926);
d.add("o",-14922);
d.add("ou",-14921);
d.add("pa",-14914);
d.add("pai",-14908);
d.add("pan",-14902);
d.add("pang",-14894);
d.add("pao",-14889);
d.add("pei",-14882);
d.add("pen",-14873);
d.add("peng",-14871);
d.add("pi",-14857);
d.add("pian",-14678);
d.add("piao",-14674);
d.add("pie",-14670);
d.add("pin",-14668);
d.add("ping",-14663);
d.add("po",-14654);
d.add("pu",-14645);
d.add("qi",-14630);
d.add("qia",-14594);
d.add("qian",-14429);
d.add("qiang",-14407);
d.add("qiao",-14399);
d.add("qie",-14384);
d.add("qin",-14379);
d.add("qing",-14368);
d.add("qiong",-14355);
d.add("qiu",-14353);
d.add("qu",-14345);
d.add("quan",-14170);
d.add("que",-14159);
d.add("qun",-14151);
d.add("ran",-14149);
d.add("rang",-14145);
d.add("rao",-14140);
d.add("re",-14137);
d.add("ren",-14135);
d.add("reng",-14125);
d.add("ri",-14123);
d.add("rong",-14122);
d.add("rou",-14112);
d.add("ru",-14109);
d.add("ruan",-14099);
d.add("rui",-14097);
d.add("run",-14094);
d.add("ruo",-14092);
d.add("sa",-14090);
d.add("sai",-14087);
d.add("san",-14083);
d.add("sang",-13917);
d.add("sao",-13914);
d.add("se",-13910);
d.add("sen",-13907);
d.add("seng",-13906);
d.add("sha",-13905);
d.add("shai",-13896);
d.add("shan",-13894);
d.add("shang",-13878);
d.add("shao",-13870);
d.add("she",-13859);
d.add("shen",-13847);
d.add("sheng",-13831);
d.add("shi",-13658);
d.add("shou",-13611);
d.add("shu",-13601);
d.add("shua",-13406);
d.add("shuai",-13404);
d.add("shuan",-13400);
d.add("shuang",-13398);
d.add("shui",-13395);
d.add("shun",-13391);
d.add("shuo",-13387);
d.add("si",-13383);
d.add("song",-13367);
d.add("sou",-13359);
d.add("su",-13356);
d.add("suan",-13343);
d.add("sui",-13340);
d.add("sun",-13329);
d.add("suo",-13326);
d.add("ta",-13318);
d.add("tai",-13147);
d.add("tan",-13138);
d.add("tang",-13120);
d.add("tao",-13107);
d.add("te",-13096);
d.add("teng",-13095);
d.add("ti",-13091);
d.add("tian",-13076);
d.add("tiao",-13068);
d.add("tie",-13063);
d.add("ting",-13060);
d.add("tong",-12888);
d.add("tou",-12875);
d.add("tu",-12871);
d.add("tuan",-12860);
d.add("tui",-12858);
d.add("tun",-12852);
d.add("tuo",-12849);
d.add("wa",-12838);
d.add("wai",-12831);
d.add("wan",-12829);
d.add("wang",-12812);
d.add("wei",-12802);
d.add("wen",-12607);
d.add("weng",-12597);
d.add("wo",-12594);
d.add("wu",-12585);
d.add("xi",-12556);
d.add("xia",-12359);
d.add("xian",-12346);
d.add("xiang",-12320);
d.add("xiao",-12300);
d.add("xie",-12120);
d.add("xin",-12099);
d.add("xing",-12089);
d.add("xiong",-12074);
d.add("xiu",-12067);
d.add("xu",-12058);
d.add("xuan",-12039);
d.add("xue",-11867);
d.add("xun",-11861);
d.add("ya",-11847);
d.add("yan",-11831);
d.add("yang",-11798);
d.add("yao",-11781);
d.add("ye",-11604);
d.add("yi",-11589);
d.add("yin",-11536);
d.add("ying",-11358);
d.add("yo",-11340);
d.add("yong",-11339);
d.add("you",-11324);
d.add("yu",-11303);
d.add("yuan",-11097);
d.add("yue",-11077);
d.add("yun",-11067);
d.add("za",-11055);
d.add("zai",-11052);
d.add("zan",-11045);
d.add("zang",-11041);
d.add("zao",-11038);
d.add("ze",-11024);
d.add("zei",-11020);
d.add("zen",-11019);
d.add("zeng",-11018);
d.add("zha",-11014);
d.add("zhai",-10838);
d.add("zhan",-10832);
d.add("zhang",-10815);
d.add("zhao",-10800);
d.add("zhe",-10790);
d.add("zhen",-10780);
d.add("zheng",-10764);
d.add("zhi",-10587);
d.add("zhong",-10544);
d.add("zhou",-10533);
d.add("zhu",-10519);
d.add("zhua",-10331);
d.add("zhuai",-10329);
d.add("zhuan",-10328);
d.add("zhuang",-10322);
d.add("zhui",-10315);
d.add("zhun",-10309);
d.add("zhuo",-10307);
d.add("zi",-10296);
d.add("zong",-10281);
d.add("zou",-10274);
d.add("zu",-10270);
d.add("zuan",-10262);
d.add("zui",-10260);
d.add("zun",-10256);
d.add("zuo",-10254);
/*通过查找字典得到与ascii码对应的拼音*/
function getKey(code){
	if((code>0)&&(code<160)){
		return String.fromCharCode(code);/* String.fromCharCode 就是把ascii码转成字符*/
	}else if((code<-20319)||(code>-10247)){
		return "";
	}else{
		for(var i=d.items.length-1;i>=0;i--){
			if(d.items[i].value<=code)break;
		}
	}
	return d.items[i].key;

}
/*转为大写*/
function myConvertToLower(str){
	var result = "" ;
	for (var i=1;i<=str.length;i++){
	/*执行指定语言的脚本代码：Mid(str,i,1)-指从str的第i个字符开始取长度为1的字符串asc(char)-指获取字符的acsii码*/
		execScript("ascCode=asc(mid(\"" + str + "\"," + i + ",1))", "vbscript");
		result = result   + getKey(ascCode);
	}
	return result.toLowerCase();
}
function myConvertToUpper(str){
	var result = "" ;
	for (var i=1;i<=str.length;i++){
	/*执行指定语言的脚本代码：Mid(str,i,1)-指从str的第i个字符开始取长度为1的字符串asc(char)-指获取字符的acsii码*/
		execScript("ascCode=asc(mid(\"" + str + "\"," + i + ",1))", "vbscript");
		result = result   + getKey(ascCode);
	}
	return result.toUpperCase();
}
  </script>
EOT;
		if($manual){
			$letter_data = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',0,1,2,3,4,5,6,7,8,9);
			$select = '<select onchange="$(\'#'.$field.'\').val($(this).val())"><option value="">请选择</option>';
			foreach($letter_data as $letter_v){
				$letter_v = $is_big == 'myConvertToUpper' ? strtoupper($letter_v) : $letter_v;
				$select .= '<option value="'.$letter_v.'">'.$letter_v.'</option>';
			}
			$select .='</select>';
		}
		return $js.'<input type="text" onkeydown="return false;" name="info['.$field.']" id="'.$field.'" size="'.$size.'" value="'.$value.'" class="input-text" '.$formattribute.' '.$css.'>'.$select;
	}

 } 
?>