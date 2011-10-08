	function linkfield_liandong($field, $value, $fieldinfo) {
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
