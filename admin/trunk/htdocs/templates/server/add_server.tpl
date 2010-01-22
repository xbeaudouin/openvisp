<script type="text/javascript" src="../lib/ajax_server.php"></script> 
<center>

<form method="post">

	<input type="hidden" id="formname" name="formname" value="add-server">
	<input type="hidden" id="fServer_id" name="fServer_id" value="">

  <table class="subauto">

    <tr>
    	<td><?php print $PALANG['pAdd_server_name']; ?></td>
      <td><input type="text" size="30" name="fServer_name" id="fServer_name" onkeyup="check_servername()">
	      <div id='server_name_status' style='display:inline'></div>
	    </td>
	    <td class="subauto"></td>
    </tr>

    <tr>
    	<td><?php print $PALANG['pAdd_server_fqdn']; ?></td>
      <td colspan="2"><input type="text" size="30" name="fServer_fqdn" id="fServer_fqdn" onkeyup="check_server_fqdn()">
	      <div id='server_fqdn_status' style='display:inline'></div>
	    </td>
    </tr>

    <tr>
      <td><?php print $PALANG['pAdd_server_desc']; ?></td>
      <td colspan="2"><textarea rows="10" size="50" name="fServer_desc" id="fServer_desc"></textarea></td>
    </tr>

    <tr>
      <td>Active</td>
      <td><input type="checkbox" name="fServer_active"></td>
    </tr>


    <tr align="center">
      <td colspan="2">
         <input type="hidden" name="fType" value="server">
         <input class="button_inactive" type="submit" id="submit" name="submit" value="<?php print $PALANG['pAdd_server_name_ok']; ?>" disabled/> 
<!--	 <input class="button_inactive" type="submit" id="submit" name="submit" value="<?php print $PALANG['pAdd_server_name_ok']; ?>" onSubmit="valider_formulaire(this)" >  -->

	 	     <div id='server_status' style='display:inline'></div>
      </td>



    </tr>

  </table>

</form>
</div>
</center>