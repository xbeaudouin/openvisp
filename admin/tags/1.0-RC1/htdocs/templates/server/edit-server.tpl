<script type="text/javascript" src="../lib/ajax_server.php"></script> 
<center>

<form method="post">

	<input type="hidden" id="formname" name="formname" value="edit-server">
	<input type="hidden" id="fServer_id" name="fServer_id" value="<?php print $server_info['id'];?>">

  <table class="auto">

    <tr>
    	<td><?php print $PALANG['pAdd_server_name']; ?></td>
      <td><input type="text" size="30" name="fServer_name" id="fServer_name" onkeyup="check_servername()" value="<?php print $server_info['name'];?>">
	      <div id='server_name_status' style='display:inline'></div>
	    </td>
    </tr>

    <tr>
    	<td><?php print $PALANG['pAdd_server_fqdn']; ?></td>
      <td><input type="text" size="30" name="fServer_fqdn" id="fServer_fqdn" onkeyup="check_server_fqdn()" value="<?php print $server_info['public_name'];?>">
	      <div id='server_fqdn_status' style='display:inline'></div>
	    </td>
    </tr>

    <tr>
      <td><?php print $PALANG['pAdd_server_desc']; ?></td>
      <td><textarea rows="10" size="50" name="fServer_desc" id="fServer_desc"><?php print $server_info['description'];?></textarea></td>
    </tr>

    <tr>
	    <td><?php print $PALANG['pAdd_server_private_ip']; ?></td>
      <td><input type="text" size="20" name="fServer_prv_ip" id="fServer_prv_ip" desa_onkeyup="check_server_prv_ip()" value="<?php print $server_info['private'];?>">
	      <div id='server_prv_ip_status' style='display:inline'></div>
	    </td>
    </tr>

    <tr>
	    <td><?php print $PALANG['pAdd_server_public_ip']; ?></td>
      <td><input type="text" size="20" name="fServer_pub_ip" id="fServer_pub_ip" desa_onkeyup="check_server_pub_ip()" value="<?php print $server_info['public'];?>">
	      <div id='server_pub_ip_status' style='display:inline'></div>
      </td>
    </tr>

    <tr>
	    <td><?php print $PALANG['pAdd_server_role']; ?></td>
      <td><select name="fServer_role[]" multiple size="5">

<?php
for ($i = 0; $i < sizeof ($list_role); $i++)
	{
		$selected = "";
		for ($j = 0; $j < sizeof ($server_role); $j++){

			if ( $list_role[$i]['role'] == $server_role[$j]['role'] ){
				$selected="selected";
			}			
		}

		print "<option value=\"".$list_role[$i]['id']."\" $selected>".$list_role[$i]['role']."</option>\n";
	}
?>
   	  </select>
      </td>
    </tr>

    <tr>
      <td>Active</td>
      <td><input type="checkbox" name="fServer_active" <?php print $server_info['active']; ?>></td>
    </tr>


    <tr align="center">
      <td colspan="2">
         <input class="button_inactive" type="submit" id="submit" name="submit" value="<?php print $PALANG['pModify_server_name_ok']; ?>" disabled/> 
<!--	 <input class="button_inactive" type="submit" id="submit" name="submit" value="<?php print $PALANG['pAdd_server_name_ok']; ?>" onSubmit="valider_formulaire(this)" >  -->

	 	     <div id='server_status' style='display:inline'></div>
         <input type="hidden" name="fType" value="server">
      </td>



    </tr>

  </table>

</form>
</div>
</center>