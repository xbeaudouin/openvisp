<script type="text/javascript" src="../lib/ajax_server.php"></script> 
<center>

<form method="post">

  <table class="auto">


    <tr>
      <td><?php print $PALANG['pApplication_group']; ?></td>
      <td><select name="fApp_model">
			<?php
			for ($i = 0; $i < sizeof ($list_job_model); $i++)
				{
					print "<option value=\"".$list_job_model[$i]['id']."\">".$list_job_model[$i]['role']."</option>\n";
				}
?>
			    </select>
	      <div id='version_status' style='display:inline'> </div>
	    </td>
    </tr>
 

    <tr>
		 <td><?php print $PALANG['pApplication_name']; ?></td>
      <td><input type="text" size="30" name="fApp_name" id="fApp_name">
	      <div id='application_status' style='display:inline'> </div>
	    </td>
    </tr>

    <tr>
      <td><?php print $PALANG['pApplication_version']; ?></td>
      <td><input type="text" size="30" name="fApp_version" id="fApp_version" onkeyup="check_application()">
	      <div id='version_status' style='display:inline'> </div>
	    </td>
    </tr>
 
    <tr>
      <td><?php print $PALANG['pApplication_desc']; ?></td>
      <td><textarea rows="10" size="50" name="fApp_desc"></textarea></td>
    </tr>

    <tr>
      <td>Active</td>
      <td><input type="checkbox" name="fApp_active"></td>
    </tr>

    <tr align="center">
      <td colspan="2">
         <input class="button_inactive" type="submit" id="submit" name="submit" value="<?php print $PALANG['pAdd_app']; ?>" disabled/>
         <input type="hidden" name="fType" value="model">
      </td>


    </tr>

  </table>

</form>
</div>
</center>