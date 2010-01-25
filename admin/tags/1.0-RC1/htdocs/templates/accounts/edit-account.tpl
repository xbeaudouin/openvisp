<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="2">
         <?php print $PALANG['pAccountEdit_account_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
			<td align="center">
	       <form name="block" method="post" action="block-account.php">
           <?php
	 $block_action = "unblock";
   $button_text = $PALANG['pAccountEdit_account_unblock'];
	 if ( $account_information['paid'] == 1 ){
		 $block_action = "block";
		 $button_text = $PALANG['pAccountEdit_account_block'];
	 }
           ?>
           <input type="hidden" name="action" value="<?php print $block_action;?>">
           <input type="hidden" name="username" value="<?php print $username; ?>">
           <input class="button" type="submit" value="<?php print $button_text;?>">
				 </form>
         <br />
      </td>
   </tr>
   <tr>
     <td>
        <form name="alias" method="post">
        <?php print $PALANG['pAccountEdit_account_username'] . ":\n"; ?>
     </td>
     <td colspan="2" align="right">
        <?php print "<b>" . $username . "</b>"; ?>
         <input type="hidden" name="username" value="<?php print $username; ?>">
     </td>
   </tr>
   <tr>
      <td>
          <?php print $PALANG['pAccountEdit_account_password'] . ":\n"; ?>
      </td>
      <td colspan="2">
          <input type="password" name="fPassword1" />
      </td>
   </tr>
   <tr>
      <td>
          <?php print $PALANG['pAccountEdit_account_password2'] . ":\n"; ?>
      </td>
      <td colspan="2">
          <input type="password" name="fPassword2" />
      </td>
   </tr>
   <tr>
     <td>
       <?php print $PALANG['pUserinfo_company']; ?>
     </td>
     <td colspan="2">
       <input type="text" name="fCompany" value="<?php print $account_information['company'];?>">
     </td>

   </tr>
   <tr>
     <td>
       <?php print $PALANG['pUserinfo_address']; ?>
     </td>
     <td colspan="2">
       <textarea name="fAddress"><?php print $account_information['address'];?></textarea>
     </td>
   </tr>
   <tr>
     <td>
       <?php print $PALANG['pUserinfo_city']; ?>
     </td>
     <td colspan="2">
       <input type="text" name="fCity" value="<?php print $account_information['city'];?>">
     </td>

   </tr>
   <tr>
     <td>
       <?php print $PALANG['pUserinfo_postal_code']; ?>
     </td>
     <td colspan="2">
       <input type="text" name="fPostalCode" value="<?php print $account_information['postal_code'];?>">
     </td>

   </tr>
    <tr>
     <td>
       <?php print $PALANG['pUserinfo_weburl']; ?>
     </td>
     <td colspan="2">
       <input type="text" name="fWeburl" value="<?php print $account_information['weburl'];?>">
     </td>

   </tr>
   <tr>
     <td>
       <?php print $PALANG['pUserinfo_email']; ?>
     </td>
     <td colspan="2">
       <input type="text" name="fEmail" value="<?php print $account_information['email'];?>">
     </td>

   </tr>
   <tr>
     <td>
       <?php print $PALANG['pUserinfo_phone']; ?>
     </td>
     <td colspan="2">
       <input type="text" name="fPhone" value="<?php print $account_information['phone'];?>">
     </td>

   </tr>
   <tr>
     <td>
       <?php print $PALANG['pUserinfo_fax']; ?>
     </td>
     <td colspan="2">
       <input type="text" name="fFax" value="<?php print $account_information['fax'];?>">
     </td>

   </tr>
   <tr>
     <td>
       <?php print $PALANG['pUserinfo_logo']; ?>
     </td>
     <td colspan="2">
       <input type="file" name="fLogo">
     </td>

   </tr>
   <tr>
     <td>
       <?php print $PALANG['pUserinfo_emailsupport']; ?>
     </td>
     <td colspan="2">
       <input type="text" name="fEmailsupport" value="<?php print $account_information['emailsupport'];?>">
     </td>

   </tr>
    <tr>
     <td>
       <?php print $PALANG['pUserinfo_phonesupport']; ?>
     </td>
     <td colspan="2">
       <input type="text" name="fPhonesupport" value="<?php print $account_information['phonesupport'];?>">
     </td>

   </tr>
   <tr>
     <td>
       <?php print $PALANG['pUserinfo_websupport']; ?>
     </td>
     <td colspan="2">
       <input type="text" name="fWebsupport" value="<?php print $account_information['websupport'];?>">
     </td>

   </tr>
   <tr>
     <td>
       <?php print $PALANG['pUserinfo_webfaq']; ?>
     </td>
     <td colspan="2">
       <input type="text" name="fWebfaq" value="<?php print $account_information['webfaq'];?>">
     </td>

   </tr>

   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_admin_active'] . ":\n"; ?>
       </td>
      <td>
         <?php $checked = (!empty ($account_information['enabled'])) ? 'checked' : ''; ?>
         <input type="checkbox" name="fActive" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>

   <tr>
			<td colspan="3">&nbsp;</td>
   </tr>

   <tr>
			<td colspan="3">&nbsp;</td>
   </tr>

   <tr>
      <td>Services:</td>
      <td>&nbsp;</td>
      <td align="right">Nb / Max :</td>
   </tr>
   <tr>
      <td>Email Mailbox</td>
      <td align="right">
        <?php $checked = (!empty ($account_rights['mail'])) ? 'checked' : ''; ?>
        <input type="checkbox" name="fMail" <?php print $checked;?> />
      </td>
      <td align="right">
        <?php print $total_used['emails'];?> / <input type="text" name="fNbmail" value="<?php print $account_quota['emails'];?>" size="7">
			</td>
   </tr>
   <tr>
      <td>Email Aliases</td>
      <td>&nbsp;</td>
      <td align="right">
        <?php print $total_used['emailsaliases'];?> / <input type="text" name="fNbmailalias" value="<?php print $account_quota['emails_alias'];?>" size="7">
			</td>
   </tr>
   <tr>
      <td>Datacenter</td>
      <td align="right">
        <?php $checked = (!empty ($account_rights['datacenter'])) ? 'checked' : ''; ?>
        <input type="checkbox" name="fDatacenter" <?php print $checked; ?> />
      </td>
      <td align="right">
        &nbsp;
			</td>
   </tr>
   <tr>
      <td>FTP</td>
      <td align="right">
        <?php $checked = (!empty ($account_rights['ftp'])) ? 'checked' : ''; ?>
        <input type="checkbox" name="fFtp" <?php print $checked; ?> />
      </td>
      <td align="right">
	 <?php print $total_used['ftp'];?>  / <input type="text" name="fNftpaccount" value="<?php print $account_quota['ftp'];?>" size="7">
			</td>
   </tr>
   <tr>
      <td>Web Site</td>
      <td align="right">
        <?php $checked = (!empty ($account_rights['http'])) ? 'checked' : ''; ?>
        <input type="checkbox" name="fWebsite" <?php print $checked; ?> />
      </td>
      <td align="right">
	 <?php print $total_used['http'];?> / <input type="text" name="fNwebsite" value="<?php print $account_quota['http'];?>" size="7">
			</td>
   </tr>

   <tr>
      <td>Web Site Alias</td>
      <td>&nbsp;</td>
      <td align="right">
       <?php print $total_used['http_alias'];?> / <input type="text" name="fNwebsitealias" value="<?php print $account_quota['http_alias'];?>" size="7">
			</td>
   </tr>

   <tr>
      <td>Domains</td>
      <td align="right">
        <?php $checked = (!empty ($account_rights['domain'])) ? 'checked' : ''; ?>
	 <input type="checkbox" name="fDomains" <?php print $checked; ?> />
      </td>
      <td align="right">
	 <?php print $total_used['domains'];?> / <input type="text" name="fNdomains" value="<?php print $account_quota['domains'];?>" size="7">
			</td>
   </tr>

   <tr>
      <td>Mysql Databases</td>
      <td align="right">
        <?php $checked = (!empty ($account_rights['mysql'])) ? 'checked' : ''; ?>
        <input type="checkbox" name="fMysql" <?php print $checked; ?> />
      </td>
      <td align="right">
	 <?php print $total_used['mysqldb'];?> / <input type="text" name="fNmysqldb" value="<?php print $account_quota['mysqldb'];?>" size="7">
			</td>
   </tr>

   <tr>
      <td>Mysql Users</td>
      <td>&nbsp;</td>
      <td align="right">
	 <?php print $total_used['mysqlusers'];?> / <input type="text" name="fNmysqlusers" value="<?php print $account_quota['mysqlusers'];?>" size="7">
			</td>
   </tr>

   <tr>
      <td>Postgresql Databases</td>
      <td align="right">
        <?php $checked = (!empty ($account_rights['postgresql'])) ? 'checked' : ''; ?>
        <input type="checkbox" name="fPostgresql" <?php print $checked; ?> />
      </td>
      <td align="right">
	 <?php print $total_used['postgresqldb'];?> / <input type="text" name="fNpostgresqldb" value="<?php print $account_quota['postgresqldb'];?>" size="7">
			</td>
   </tr>

   <tr>
      <td>Mysql Users</td>
      <td>&nbsp;</td>
      <td align="right">
	 <?php print $total_used['postgresqlusers'];?> / <input type="text" name="fNpostgresqlusers" value="<?php print $account_quota['postgresqlusers'];?>" size="7">
			</td>
   </tr>

   <tr>
      <td>Super Admin</td>
      <td align="right">
        <?php $checked = (!empty ($account_rights['manage'])) ? 'checked' : ''; ?>
        <input type="checkbox" name="fManage" <?php print $checked; ?> />
      </td>
      <td>&nbsp;</td>
   </tr>

   <tr>
      <td>DataCenter Super Admin</td>
      <td align="right">
        <?php $checked = (!empty ($account_rights['datacenter_manage'])) ? 'checked' : ''; ?>
        <input type="checkbox" name="fDCManage" <?php print $checked; ?> />
      </td>
      <td>&nbsp;</td>
   </tr>
   <tr>
			<td colspan="3">&nbsp;</td>
   </tr>

   <tr>
      <td>
        <?php
				//          print $PALANG['pAdminEdit_admin_select_domains'] . " :";
        ?>
       </td>
			 <td colspan="2" align="center">
<select name="fDomainslist[]" size="10" multiple="multiple">
<?php
/* if (check_admin($username)) print "<option value=\"ALL\" selected=\"selected\">ALL</option\n"; */
/* elseif ( $tDomains['0'] == "ALL" ) print "<option value=\"ALL\" selected=\"selected\">ALL</option\n"; */
/* else print "<option value=\"ALL\">ALL</option>\n"; */

for ($i = 0; $i < sizeof ($list_domains); $i++)
{  
	if (in_array ($list_domains[$i]['domain'], $tDomains) and ($tDomains['0'] != "ALL" ) and (!(check_admin($username))) )
   {
      print "<option value=\"" . $list_domains[$i]['id'] . "\" selected=\"selected\">" . $list_domains[$i]['domain'] . "</option>\n";
   }
   else
   {
      print "<option value=\"" . $list_domains[$i]['id'] . "\">" . $list_domains[$i]['domain'] . "</option>\n";
   }
}
?>
</select>

      </td>
   </tr>


   <tr>
     <td align="center" colspan="3">
        
        <input class="button" type="submit" name="submit" value="<?php print $PALANG['pAccountEdit_account_button']; ?>" />
        </form>
     </td>
   </tr>
</table>
<p />
