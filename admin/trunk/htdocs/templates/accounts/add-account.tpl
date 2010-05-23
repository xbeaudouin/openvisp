<center>
<?php print $tMessage . "<br />\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pAccountCreate_account_welcome'] . "<br />\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form name="alias" method="post">
         <?php print $PALANG['pAccountCreate_account_username'] . ":\n"; ?>
      </td>
      <td colspan="2">
         <input type="text" name="fUsername" value="<?php print $PALANG['pAccountCreate_account_username_ex']; ?>" />
      </td>
      <td>
         <?php print $pAdminCreate_admin_username_text . "\n"; ?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAccountCreate_account_password'] . ":\n"; ?>
      </td>
      <td colspan="2">
         <input type="password" name="fPassword" />
      </td>
      <td>&nbsp;</td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAccountCreate_account_password2'] . ":\n"; ?>
      </td>
      <td colspan="2">
         <input type="password" name="fPassword2" />
      </td>
      <td>&nbsp;</td>
   </tr>

   <tr>
			<td colspan="3">&nbsp;</td>
   </tr>

   <tr>
      <td>Services:</td>
      <td>&nbsp;</td>
			<td align="right">Max</td>
   </tr>

   <tr>
      <td>Diskspace</td>
      <td align="right">&nbsp;</td>
      <td align="right">
        <input type="text" name="fDiskspace" value="" size="7">
			</td>
   </tr>
   <tr>
      <td>Email</td>
      <td align="right">
        <input type="checkbox" name="fMail">
      </td>
      <td align="right">
        <input type="text" name="fNbmail" value="" size="7">
			</td>
   </tr>
   <tr>
      <td>Alias Email</td>
      <td align="right">
        <input type="checkbox" name="fAliasmail">
      </td>
      <td align="right">
        <input type="text" name="fNbaliasmail" value="" size="7">
			</td>
   </tr>
	 <tr>
      <td>Datacenter</td>
      <td align="right">
        <input type="checkbox" name="fDatacenter">
      </td>
	    <td align="right">
        &nbsp;
			</td>
   </tr>
	 <tr>
      <td>FTP</td>
      <td align="right">
        <input type="checkbox" name="fFtp">
      </td>
      <td align="right">
	      <input type="text" name="fNftpaccount" value="" size="7">
			</td>
   </tr>
	 <tr>
      <td>Web Site</td>
      <td align="right">
        <input type="checkbox" name="fWebsite">
      </td>
      <td align="right">
	      <input type="text" name="fNwebsite" value="" size="7">
			</td>
   </tr>
	 <tr>
      <td>Domains</td>
      <td align="right">
        <input type="checkbox" name="fDomains">
      </td>
      <td align="right">
	      <input type="text" name="fNdomains" value="" size="7">
			</td>
   </tr>
	 <tr>
      <td>Mysql</td>
      <td align="right">
        <input type="checkbox" name="fMysql">
      </td>
      <td align="right">
	      <input type="text" name="fNmysql" value="" size="7">
			</td>
   </tr>
	 <tr>
      <td>Postgresql</td>
      <td align="right">
        <input type="checkbox" name="fPostgresql">
      </td>
      <td align="right">
	      <input type="text" name="fNpostgresql" value="" size="7">
			</td>
   </tr>

   <tr>
      <td align="center" colspan="3">
         <br /><br />
         <input class="button" type="submit" name="submit" value="<?php print $PALANG['pAccountCreate_account_button']; ?>" />
         <br /><br />
         </form>
      </td>
   </tr>
</table>
<p />
