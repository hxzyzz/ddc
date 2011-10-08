<table cellpadding="2" cellspacing="1" onclick="javascript:$('#minlength').val(0);$('#maxlength').val(255);">
	<tr> 
      <td>关联表名</td>
      <td><input type="text" name="setting[table_name]" value="<?php echo $setting['table_name'];?>" size="40"></td>
    </tr>
	<tr> 
      <td>数值字段</td>
      <td><input type="text" name="setting[field_value]" value="<?php echo $setting['field_value'];?>" size="40"></td>
    </tr>
	<tr> 
      <td>标题字段</td>
      <td><input type="text" name="setting[field_title]" value="<?php echo $setting['field_title'];?>" size="40"></td>
    </tr>
	<tr> 
      <td>关联条件</td>
      <td><input type="text" name="setting[field_where]" value="<?php echo $setting['field_where'];?>" size="40"></td>
    </tr>
</table>