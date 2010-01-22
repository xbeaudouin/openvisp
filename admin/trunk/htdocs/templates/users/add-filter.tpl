<center>
<?php print $tMessage . "\n"; ?>
<form method="post">
  <table class="auto">

    <tr>
	 <td><?php print $PALANG['pUsersFilter_name']; ?></td>
      <td><input type="text" name="fFilterName"></td>
    </tr>
	 
    <tr>
	 <td><?php print $PALANG['pUsersFilter_order']; ?></td>
      <td><input type="text" name="fExecOrder" size="5"></td>
    </tr>
	 
    <tr>
      <td><?php print $PALANG['pUsersFilter_field']; ?></td>
      <td>
        <select name="fField">
	 <?php

	 for ($i = 0; $i < sizeof ($table_fieldfilter); $i++)
		 {
			 print "<option value=".$table_fieldfilter[$i]['num'].">".$table_fieldfilter[$i]['fieldname']."</option>\n         ";
		 }
	 ?>
        </select>
      </td>
    </tr>

    <tr>
	 <td><?php print $PALANG['pUsersFilter_value']; ?></td>
      <td><input type="text" name="fFieldValue"></td>
    </tr>

    <tr>
      <td><?php print $PALANG['pUsersFilter_action']; ?></td>
      <td>
        <select name="fAction">
	 <?php

	 for ($i = 0; $i < sizeof ($table_fieldaction); $i++)
		 {
			 print '<option value="'.$table_fieldaction[$i]['num'].'">'.$table_fieldaction[$i]['actionname']."</option>\n";
		 }
	 ?>
        </select>
      </td>
    </tr>

	 <td><?php print $PALANG['pUsersFilter_destination_folder']; ?></td>

<?php
  if ( $tImapsrv != "" ){
?>
     <td>
        <select name="fDestination">
          <option value="--" selected>--</option>

<?php

			$imap= new  Net_IMAP($tImapsrv,'143','0');

		if ( PEAR::isError( $ret = $imap->login( $fUsername , $tPassuser  ) ) ) {
			echo "Unable to login! reason:" . $ret->getMessage() . "\n";
			exit();
		}
		
		$imap->selectMailbox('inbox');
		$mailboxes=$imap->getMailboxes('inbox');
		asort($mailboxes);

		$pattern[0] = '/INBOX./';
		$pattern[1] = '/{'.$tImapsrv.'}/';
		$pattern[2] = '/\./';
		$replacement[0] = '';
		$replacement[1] = '';
		$replacement[2] = '/';

		if (is_array($mailboxes)) {
			foreach ($mailboxes as $val) {
				$folder = preg_replace($pattern, $replacement, $val);
				echo '<option value="'.$folder.'">'.$folder."</option>\n";
			}
		} else {
			echo "imap_list failed: "."\n";
		}

		$imap->disconnect();
?>

      </select></td>
<?php
	 }
	else{
		print '<td><input type="text" name="fDestination"></td>';
	}
?>
    </tr>

	 <tr>
    <td><?php print $PALANG['pUsersFilter_archive']; ?></td>
    <td><input type="text" name="fArchive_format"></td>
   </tr>


	 <tr>
    <td><?php print $PALANG['pUsersFilter_destination_email']; ?></td>
    <td><textarea name="fDestination_email" rows="10" cols="25"></textarea></td>
   </tr>


	 <td><?php print $PALANG['pUsersFilter_comment']; ?></td>
      <td><textarea cols="25" rows="10" name="fComment"></textarea></td>
    </tr>
 
    <tr>
      <td></td>
      <td><input type="submit" value="<?php print $PALANG['pUsersFilter_addsubmit']; ?>" ></td>
    </tr>

	 
  </table>
</form>

</center>