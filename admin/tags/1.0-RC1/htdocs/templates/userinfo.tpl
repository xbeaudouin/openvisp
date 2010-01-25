<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pUserinfo_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form enctype="multipart/form-data" name="mailbox" method="post">
	    </td>
	 </tr>

   <tr>
      <td>
         <?php print $PALANG['pUserinfo_company']; ?> :
	    </td>
      <td>
			  <input type="text" name="fCompany" value="<?php print $tCompany; ?>">
	    </td>

	 </tr>

   <tr>
      <td>
         <?php print $PALANG['pUserinfo_address']; ?> :
	    </td>
      <td>
			  <textarea name="fAddress"><?php print $tAddress; ?></textarea>
	    </td>

	 </tr>
   <tr>
      <td>
			   <?php print $PALANG['pUserinfo_city']; ?> :
	    </td>
      <td>
			  <input type="text" name="fCity" value="<?php print $tCity; ?>">
	    </td>

	 </tr>
   <tr>
      <td>
         <?php print $PALANG['pUserinfo_postal_code']; ?> :
	    </td>
      <td>
			  <input type="text" name="fPostalCode" value="<?php print $tPostalCode; ?>">
	    </td>

	 </tr>
   <tr>
      <td>
         <?php print $PALANG['pUserinfo_weburl']; ?> :
	    </td>
      <td>
			  <input type="text" name="fWeburl" value="<?php print $tWeburl; ?>">
	    </td>

	 </tr>
   <tr>
      <td>
         <?php print $PALANG['pUserinfo_email']; ?> :
	    </td>
      <td>
			  <input type="text" name="fEmail" value="<?php print $tEmail; ?>">
	    </td>

	 </tr>
   <tr>
      <td>
         <?php print $PALANG['pUserinfo_phone']; ?> :
	    </td>
      <td>
			  <input type="text" name="fPhone" value="<?php print $tPhone; ?>">
	    </td>

	 </tr>
   <tr>
      <td>
         <?php print $PALANG['pUserinfo_fax']; ?> :
	    </td>
      <td>
			  <input type="text" name="fFax" value="<?php print $tFax; ?>">
	    </td>

	 </tr>
   <tr>
      <td>
      	 <?php print $PALANG['pUserinfo_logo']; ?> : (160x97 | JPEG / GIF)
	    </td>
      <td>
			  <input type="file" name="flogo">
	    </td>

	 </tr>

   <tr>
      <td>
         <?php print $PALANG['pUserinfo_emailsupport']; ?> :
	    </td>
      <td>
			  <input type="text" name="fEmailsupport" value="<?php print $tEmailsupport; ?>">
	    </td>

	 </tr>

   <tr>
      <td>
         <?php print $PALANG['pUserinfo_phonesupport']; ?> :
	    </td>
      <td>
			  <input type="text" name="fPhonesupport" value="<?php print $tPhonesupport; ?>">
	    </td>

	 </tr>

   <tr>
      <td>
         <?php print $PALANG['pUserinfo_webfaq']; ?> :
	    </td>
      <td>
			  <input type="text" name="fWebfaq" value="<?php print $tWebfaq; ?>">
	    </td>

	 </tr>


   <tr>
      <td>
         <input type="submit" name="submit" value="<?php print $PALANG['pUserinfo_button']; ?>" />
         </form>
      </td>
   </tr>
</table>
