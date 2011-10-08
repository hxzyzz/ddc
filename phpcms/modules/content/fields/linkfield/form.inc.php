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
		$data = '<select name="info['.$fieldinfo['field'].']" id="'.$fieldinfo['field'].'" '.$formattribute.'><option>«Î—°‘Ò</option>';
		foreach($dataArr as $v) {            
			if($v[$setting['field_value']] == $value) $select = 'selected';
			else $select = '';
			$data .= "<option value='".$v[$fieldinfo['field_value']]."' ".$select.">".$v[$fieldinfo['field_title']]."</option>\n";
		}
		$data .= '</select>';
		return $data;
	}