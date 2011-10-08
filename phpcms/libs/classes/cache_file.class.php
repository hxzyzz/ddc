<?php

class cache_file {
	
	/*»º´æÄ¬ÈÏÅäÖÃ*/
	protected $_setting = array(
								'suf' => '.cache.php',	/*»º´æÎÄ¼şºó×º*/
								'type' => 'array',		/*»º´æ¸ñÊ½£ºarrayÊı×é£¬serializeĞòÁĞ»¯£¬null×Ö·û´®*/
							);
	
	/*»º´æÂ·¾¶*/
	protected $filepath = '';

	/**
	 * ¹¹Ôìº¯Êı
	 * @param	array	$setting	»º´æÅäÖÃ
	 * @return  void
	 */
	public function __construct($setting = '') {
		$this->get_setting($setting);
	}
	
	/**
	 * Ğ´Èë»º´æ
	 * @param	string	$name		»º´æÃû³Æ
	 * @param	mixed	$data		»º´æÊı¾İ
	 * @param	array	$setting	»º´æÅäÖÃ
	 * @param	string	$type		»º´æÀàĞÍ
	 * @param	string	$module		ËùÊôÄ£ĞÍ
	 * @return  mixed				»º´æÂ·¾¶/false
	 */

	public function set($name, $data, $setting = '', $type = 'data', $module = ROUTE_M) {
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		if(empty($module)) $module = ROUTE_M;
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $name.$this->_setting['suf'];
	    if(!is_dir($filepath)) {
			mkdir($filepath, 0777, true);
	    }
	    
	    if($this->_setting['type'] == 'array') {
	    	$data = "<?php\nreturn ".var_export($data, true).";\n?>";
	    } elseif($this->_setting['type'] == 'serialize') {
	    	$data = serialize($data);
	    }
	    if ($module == 'commons' || ($module == 'commons' && substr($name, 0, 16) != 'category_content')) {
		    $db = pc_base::load_model('cache_model');
		    $datas = new_addslashes($data);
		    if ($db->get_one(array('filename'=>$filename, 'path'=>'caches_'.$module.'/caches_'.$type.'/'), '`filename`')) {
		    	$db->update(array('data'=>$datas), array('filename'=>$filename, 'path'=>'caches_'.$module.'/caches_'.$type.'/'));
		    } else {
		    	$db->insert(array('filename'=>$filename, 'path'=>'caches_'.$module.'/caches_'.$type.'/', 'data'=>$datas));
		    }
	    }
	    
	    //ÊÇ·ñ¿ªÆô»¥³âËø
		if(pc_base::load_config('system', 'lock_ex')) {
			$file_size = file_put_contents($filepath.$filename, $data, LOCK_EX);
		} else {
			$file_size = file_put_contents($filepath.$filename, $data);
		}
	    
	    return $file_size ? $file_size : 'false';
	}
	
	/**
	 * »ñÈ¡»º´æ
	 * @param	string	$name		»º´æÃû³Æ
	 * @param	array	$setting	»º´æÅäÖÃ
	 * @param	string	$type		»º´æÀàĞÍ
	 * @param	string	$module		ËùÊôÄ£ĞÍ
	 * @return  mixed	$data		»º´æÊı¾İ
	 */
	public function get($name, $setting = '', $type = 'data', $module = ROUTE_M) {
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		if(empty($module)) $module = ROUTE_M;
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $name.$this->_setting['suf'];
		if (!file_exists($filepath.$filename)) {
			return false;
		} else {
		    if($this->_setting['type'] == 'array') {
		    	$data = @require($filepath.$filename);
		    } elseif($this->_setting['type'] == 'serialize') {
		    	$data = unserialize(file_get_contents($filepath.$filename));
		    }
		    
		    return $data;
		}
	}
	
	/**
	 * É¾³ı»º´æ
	 * @param	string	$name		»º´æÃû³Æ
	 * @param	array	$setting	»º´æÅäÖÃ
	 * @param	string	$type		»º´æÀàĞÍ
	 * @param	string	$module		ËùÊôÄ£ĞÍ
	 * @return  bool
	 */
	public function delete($name, $setting = '', $type = 'data', $module = ROUTE_M) {
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		if(empty($module)) $module = ROUTE_M;	
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $name.$this->_setting['suf'];
		if(file_exists($filepath.$filename)) {
			if ($module == 'commons' && substr($name, 0, 16) != 'category_content') {
				$db = pc_base::load_model('cache_model');
		    	$db->delete(array('filename'=>$filename, 'path'=>'caches_'.$module.'/caches_'.$type.'/'));
			}
			return @unlink($filepath.$filename) ? true : false;
		} else {
			return false;
		}
	}
	
	/**
	 * ºÍÏµÍ³»º´æÅäÖÃ¶Ô±È»ñÈ¡×Ô¶¨Òå»º´æÅäÖÃ
	 * @param	array	$setting	×Ô¶¨Òå»º´æÅäÖÃ
	 * @return  array	$setting	»º´æÅäÖÃ
	 */
	public function get_setting($setting = '') {
		if($setting) {
			$this->_setting = array_merge($this->_setting, $setting);
		}
	}

	public function cacheinfo($name, $setting = '', $type = 'data', $module = ROUTE_M) {
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		if(empty($module)) $module = ROUTE_M;
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $filepath.$name.$this->_setting['suf'];
		
		if(file_exists($filename)) {
			$res['filename'] = $name.$this->_setting['suf'];
			$res['filepath'] = $filepath;
			$res['filectime'] = filectime($filename);
			$res['filemtime'] = filemtime($filename);
			$res['filesize'] = filesize($filename);
			return $res;
		} else {
			return false;
		}
	}

}

?>