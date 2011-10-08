<?php
$url = $_SERVER['HTTP_REFERER'];
preg_match("/modelid=(\d+)&/iU", $url, $modelid_data);
$modelid_data = $modelid_data[1];
$fields_data = getcache('model_field_'.$modelid_data, 'model');
?>
<table cellpadding="2" cellspacing="1" width="98%">
<tr> 
      <td width="120">监控字段</td>
      <td><input type="text" name="setting[fields]"  id="setting_field" onkeydown="return false;" value="<?php echo $setting['fields'];?>" size="10" class="input-text">请选择要监控的字段名
	  <?php
	  if(is_array($fields_data)){
		echo '<select onchange="$(\'#setting_field\').val($(this).val())">';
		echo '<option value="">请选择字段</option>';
		foreach($fields_data as $k=>$v){
			echo '<option value="'.$k.'">'.$k.'</option>';
		}
		echo '</select>';
	  }
	  
	  ?>
	  </td>
    </tr>
	<tr> 
      <td width="120">是否显示手动输入框</td>
      <td><input type="radio" name="setting[manual]" value="1" <?php if($setting['manual']) echo 'checked' ?> >是  <input type="radio" name="setting[manual]" value="0" <?php if(!$setting['manual']) echo 'checked' ?> >否 </td>
    </tr>
	<tr> 
      <td width="120">默认大小写</td>
      <td><input type="radio" name="setting[is_big]" value="1" <?php if($setting['is_big']) echo 'checked' ?>  > 大写 <input type="radio" name="setting[is_big]" value="0" <?php if(!$setting['is_big']) echo 'checked' ?>  > 小写</td>
    </tr>
	<tr> 
      <td width="120">默认返回字母长度</td>
      <td><input type="radio" name="setting[return_all]" value="1" <?php if($setting['return_all']) echo 'checked' ?> > 返回完整的拼音 <input type="radio" name="setting[return_all]" value="0" <?php if(!$setting['return_all']) echo 'checked' ?> >返回首字母</td>
    </tr>
</table>