<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pEdit_ftp_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
				 <form name="ftp" method="post">
	         <input type="hidden" name="ftpaccount" value="<?php print $tLogin?>">
	         <input type="hidden" name="domain" value="<?php print $fDomain?>">
	        <?php print $PALANG['pEdit_ftp_login'].":\n"; ?>
      </td>

      <td>
         <?php print $tLogin; ?>
      </td>
      <td>
         &nbsp;
      </td>
   </tr>

   <tr>
      <td>
         <?php print $PALANG['pEdit_ftp_active'] . ":\n"; ?>
       </td>
      <td>
         <?php $checked = (!empty ($tActive)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fActive" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pEdit_ftp_quota_disk'] . ":\n"; ?>
       </td>
      <td>
         <input type="text" name="fQuotadisk" value="<?php print $tQuotafs; ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pEdit_ftp_max_num_file'] . ":\n"; ?>
       </td>
      <td>
         <input type="text" name="fMaxfilesize" value="<?php print $tQuotasz; ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pEdit_ftp_bwul'] . ":\n"; ?>
       </td>
      <td>
         <input type="text" name="fBwul" value="<?php print $tBwul; ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pEdit_ftp_bwdl'] . ":\n"; ?>
       </td>
      <td>
         <input type="text" name="fBwdl" value="<?php print $tBwdl; ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pEdit_ftp_ratioul'] . ":\n"; ?>
       </td>
      <td>
         <input type="text" name="fRtul" value="<?php print $tRatioul; ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pEdit_ftp_ratiodl'] . ":\n"; ?>
       </td>
      <td>
         <input type="text" name="fRtdl" value="<?php print $tRatiodl; ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td align="center" colspan="3">
         <input type="submit" name="submit" value="<?php print $PALANG['pEdit_ftp_button']; ?>" />
         </form>
      </td>
   </tr>
   <?php
   if ($CONF['encrypt'] == "cleartext")
   {
     print "<tr>\n";
     print "  <td align=\"center\" colspan=\"3\">\n";
     print "  <a href=\"../gen-pdf.php?type=ftp&username=" . $tLogin . "&domain=" . $fDomain . "\">PDF</a>\n";
     print '<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&gpass=true">'.$PALANG['pGenerate_new_password'].'</a>';
     print "  </td>\n";
     print "</tr>\n";
   }
   ?>
</table>


