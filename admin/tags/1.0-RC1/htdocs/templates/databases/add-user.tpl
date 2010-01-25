<script type="text/javascript" src="../lib/ajax_db.php"></script>

<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pDBCreate_user_mysql_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>

   <tr>
		 <td>
         <form name="add_user" method="post">
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
		  <input type="text" name="fUsernum" id="fUsernum" value="" onkeyup="check_dbuser_available()"/>
	    <div id="fMessage" style='display:inline'></div>
    </td>

   </tr>


   <tr>
      <td align="center" colspan="3">
         <br />
	       <input type="hidden" name="fUsername" value="<?php print $fUsername;?>">
	       <input type="hidden" name="fDBtype" value="<?php print $fDBtype;?>">
         <input class="button_inactive" type="submit" id="submit" name="submit" value="<?php print $PALANG['pDBCreate_db_create_user']; ?>" disabled/>
         </form>
      </td>
   </tr>
</table>
</center>
<br />
