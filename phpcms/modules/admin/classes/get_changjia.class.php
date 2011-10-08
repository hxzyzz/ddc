<?php
defined('IN_PHPCMS') or exit('No permission resources.');

//定义在后台
define('IN_ADMIN',true);
class get_changjia {
	private $db,$db_var;
	public function __construct(){
		$this->db = pc_base::load_model('content_model');
		$this->db->set_model('12');
	}

	public function get_changjia($qiyeleixing){
			$data = $this->db->query("select c.id,c.title,cc.qiyeleixing from v9_changjia c,v9_changjia_data cc where c.id=cc.id and c.catid=9");//by cfp 取厂家列表
			$data = $this->db->fetch_array($data);
			foreach($data as $v) {
				if(strpos($v['qiyeleixing'],$qiyeleixing)==1){//by cfp 厂家类型为整车
					//$select='selected="selected"';
					$data .= "<option value='".$v[id]."' ".$select.">".$v[title]."</option>";
				}
			}
			return 	$data;	
	}
}
?>