<?php
$url = $_SERVER['HTTP_REFERER'];
preg_match("/modelid=(\d+)&/iU", $url, $modelid_data);
$modelid_data = $modelid_data[1];
$fields_data = getcache('model_field_'.$modelid_data, 'model');
?>
<table cellpadding="2" cellspacing="1" width="98%">
<tr> 
      <td width="120">����ֶ�</td>
      <td><input type="text" name="setting[fields]"  id="setting_field" onkeydown="return false;" value="<?php echo $setting['fields'];?>" size="10" class="input-text">��ѡ��Ҫ��ص��ֶ���
	  <?php
	  if(is_array($fields_data)){
		echo '<select onchange="$(\'#setting_field\').val($(this).val())">';
		echo '<option value="">��ѡ���ֶ�</option>';
		foreach($fields_data as $k=>$v){
			echo '<option value="'.$k.'">'.$k.'</option>';
		}
		echo '</select>';
	  }
	  
	  ?>
	  </td>
    </tr>
	<tr> 
      <td width="120">�Ƿ���ʾ�ֶ������</td>
      <td><input type="radio" name="setting[manual]" value="1" <?php if($setting['manual']) echo 'checked' ?> >��  <input type="radio" name="setting[manual]" value="0" <?php if(!$setting['manual']) echo 'checked' ?> >�� </td>
    </tr>
	<tr> 
      <td width="120">Ĭ�ϴ�Сд</td>
      <td><input type="radio" name="setting[is_big]" value="1" <?php if($setting['is_big']) echo 'checked' ?>  > ��д <input type="radio" name="setting[is_big]" value="0" <?php if(!$setting['is_big']) echo 'checked' ?>  > Сд</td>
    </tr>
	<tr> 
      <td width="120">Ĭ�Ϸ�����ĸ����</td>
      <td><input type="radio" name="setting[return_all]" value="1" <?php if($setting['return_all']) echo 'checked' ?> > ����������ƴ�� <input type="radio" name="setting[return_all]" value="0" <?php if(!$setting['return_all']) echo 'checked' ?> >��������ĸ</td>
    </tr>
</table>