<table cellpadding="2" cellspacing="1" onclick="javascript:$('#minlength').val(0);$('#maxlength').val(255);">
	<tr> 
      <td>关联表</td>
      <td><input type="text" name="setting[table_name]" value="<?php echo $setting['table_name'];?>" size="40"></td>
    </tr>
	<tr> 
      <td>关联栏目</td>
      <td><input type="text" name="setting[field_catid]" value="<?php echo $setting['field_catid'];?>" size="40"></td>
    </tr>
</table>