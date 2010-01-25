<script type="text/javascript" src="../lib/ajax_hosting.php"></script> 

<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pWhostCreate_ftp_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>

   <tr>
		 <td>
         <form name="add_ftp" method="post">
         <?php print $PALANG['pWhostCreate_ftp_website_domain'] . ":\n"; ?>
		 </td>
     <td>
		   <select name="fDomain" id="fDomain" onChange="go_virtual_domain()">
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
			 <?php print "Server" . ":\n"; ?>
    </td>
		<td>
   		<div id='fServerid' style='display:inline'> 
        <select name="fServerid" id="fServerid">
          <option value='-1'>All
<?php

	 if ((is_array ($list_ftp_server) and sizeof ($list_ftp_server) > 0)) {
		 for ($i = 0; $i < sizeof ($list_ftp_server); $i++)
			 {
				 print '<option value="'.$list_ftp_server[$i]['id'].'">'.$list_ftp_server[$i]['name']."\n";
			 }
	 }
?>
       </select>

        </select>
      </div>
    </td>

   </tr>


	 <tr>
		<td>
      <?php print $PALANG['pWhostCreate_ftp_website'] . ":\n"; ?>
    </td>
		<td>
   		<div id='fVirtual' style='display:inline'> 
        <select name="fVirtualid" id="fVirtualid">
          <option value='-1'>--</option> 
        </select>
      </div>
    </td>

   </tr>

   <tr>
      <td>
        <?php print $PALANG['pWhostCreate_ftp_path'] . ":\n"; ?>
      </td>
      <td>  
        <input type="text" name="fPath" value="" />
      </td>
   </tr>
   <tr>
      <td>
        <?php print $PALANG['pWhostCreate_ftp_username'] . ":\n"; ?>
      </td>
      <td>
        <input type="text" name="fLogin" id="fLogin" value="" onkeyup="check_ftpaccount()"/>
	      <div id='login_status' style='display:inline'>
account
	      </div>
      </td>
   </tr>

<?php
	 if ( $CONF['force_password'] != 'YES' ){
?>
   <tr>
      <td>
        <?php print $PALANG['pWhostCreate_ftp_password'] . ":\n"; ?>
      </td>
      <td>
        <input type="password" name ="fPassword" value="" />
      </td>
   </tr>
   <tr>
      <td>
        <?php print $PALANG['pWhostCreate_ftp_password2'] . ":\n"; ?>
      </td>
      <td>
        <input type="password" name ="fPassword2" value="" />
      </td>
   </tr>
<?php

	 }
?>

   <tr>
      <td align="center" colspan="3">
         <br />
         <input class="button_inactive" type="submit" id="submit" name="submit" value="<?php print $PALANG['pWhostCreate_ftp_button']; ?>" disabled/>
         </form>
      </td>
   </tr>
</table>
</center>
<br />
