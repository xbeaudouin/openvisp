<script type="text/javascript" src="../lib/ajax_db.php"></script>

<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pDBCreate_db_mysql_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>

   <tr>
     <td>
        <form name="add_db" method="post">
        <?php print $PALANG['pDBCreate_db_choose_server'] . "\n"; ?>
     </td>
     <td>
		   <select name="fServer_id" id="fServer_id">
         <option value="-1">--
<?php

	 if ((is_array ($list_database_server) and sizeof ($list_database_server) > 0)) {
		 for ($i = 0; $i < sizeof ($list_database_server); $i++)
			 {
				 $db_server_name  = $list_database_server[$i]['name']." (".$list_database_server[$i]['apps'];
				 $db_server_name .= " ".$list_database_server[$i]['version']." port ".$list_database_server[$i]['port'].")";
				 print '<option value="'.$list_database_server[$i]['id'].'-'.$list_database_server[$i]['apps_id'].'-'.$list_database_server[$i]['model_id'].'-'.$list_database_server[$i]['port'].'-'.$list_database_server[$i]['server_ip_id'].'">'.$db_server_name." " ;
			 }
	 }
?>
       </select>

     </td>

   </tr>


   <tr>
		 <td>
         <?php print $PALANG['pDBCreate_db_domain'] . ":\n"; ?>
		 </td>
     <td>
		   <select name="fDomain" id="fDomain">
         <option value="-1">--
<?php

	 if ((is_array ($list_domains) and sizeof ($list_domains) > 0)) {
		 for ($i = 0; $i < sizeof ($list_domains); $i++)
			 {
				 print '<option value="'.$list_domains[$i].'">'.$list_domains[$i];
			 }
	 }
?>
       </select>

     </td>
   </tr>

	 <tr>
		<td>
      <?php print $PALANG['pDBCreate_db_number_to_create'] . ":\n"; ?>
    </td>
		<td>
		  <input type="text" name="fDBnum" id="fDBnum" value="" onkeyup="check_db_available()"/>
	    <div id="fMessage" style='display:inline'></div>
    </td>

   </tr>

	 <tr>
		<td>
      <?php print $PALANG['pDBCreate_db_create_associated_user'] . ":\n"; ?>
    </td>
		<td>
		  <input type="checkbox" name="fCreateuser" checked>
    </td>

   </tr>

   <tr>
      <td align="center" colspan="3">
         <br />
	       <input type="hidden" name="fUsername" value="<?php print $fUsername;?>">
	       <input type="hidden" name="fDBtype" value="<?php print $fDBtype;?>">
         <input class="button_inactive" type="submit" id="submit" name="submit" value="<?php print $PALANG['pDBCreate_db_create_database']; ?>" disabled/>
         </form>
      </td>
   </tr>
</table>
</center>
<br />
