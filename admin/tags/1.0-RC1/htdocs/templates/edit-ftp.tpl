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
	         <input type="hidden" value="<?php print $PALANG['pEdit_ftp_login']?>">
	 .       <?php print $PALANG['pEdit_ftp_login'].":\n"; ?>
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
         <?php print $PALANG['pEdit_ftp_max_file_size'] . ":\n"; ?>
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
      <td align="center" colspan="3">
         <input type="submit" name="submit" value="<?php print $PALANG['pEdit_ftp_button']; ?>" />
         </form>
      </td>
   </tr>
   <tr>
     <td align="center" colspan="3">
     <a href="./gen-pdf.php?type=ftp&username=<?php echo $tLogin;?>&domain=<?php echo $fDomain;?>">PDF</a>
     </td>
   </tr> 
</table>


