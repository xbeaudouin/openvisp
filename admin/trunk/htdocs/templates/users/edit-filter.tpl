<center>

<?php print $tMessage . "\n"; ?>

<form method="post">
  <table class="auto">

    <tr>
      <td><?php print $PALANG['pUsersFilter_name']; ?></td>
      <td><input type="text" name="fFilterName" value="<?php print $table_filter[0]['filtername'] ?> ">
          <input type="hidden" name="fFilterNum" value="<?php print $table_filter[0]['id'] ?> ">
	    </td>
    </tr>
	 
    <tr>
      <td><?php print $PALANG['pUsersFilter_order']; ?></td>
      <td><input type="text" name="fExecOrder" size="5" value="<?php print $table_filter[0]['exec_order'] ?> "></td>
    </tr>
	 
    <tr>
      <td><?php print $PALANG['pUsersFilter_field']; ?></td>
      <td>
        <select name="fField">
	 <?php

	 for ($i = 0; $i < sizeof ($table_fieldfilter); $i++)
		 {
			 $default = "";
			 if ( $table_filter[0]['fieldname'] == $table_fieldfilter[$i]['fieldname']){ $default="selected"; }
			 print "<option value=".$table_fieldfilter[$i]['id']." $default>".$table_fieldfilter[$i]['fieldname']."</option>\n         ";
		 }
	 ?>

        </select>
      </td>
    </tr>

    <tr>
      <td><?php print $PALANG['pUsersFilter_value']; ?></td>
      <td><input type="text" name="fFieldValue" size="50" value="<?php print $table_filter[0]['fieldvalue'] ?>"></td>
    </tr>

    <tr>
      <td><?php print $PALANG['pUsersFilter_action']; ?></td>
      <td><select name="fAction">
	 <?php

	 for ($i = 0; $i < sizeof ($table_fieldaction); $i++)
		 {
			 $default = "";
			 if ( $table_filter[0]['filteraction_id'] == $table_fieldaction[$i]['id']){ $default="selected"; }
			 print '<option value="'.$table_fieldaction[$i]['id'].'"'.$default.'>'.$table_fieldaction[$i]['actionname']."</option>\n";
		 }
	 ?>

      </select></td>
    </tr>

<?php
			if ( ($fActionname == "archive") or ($fActionname == "deliver") ){

?>
    <tr>
      <td><?php print $PALANG['pUsersFilter_destination_folder']; ?></td>

<?php
  if ( $tImapsrv != "" ){
?>
		<td><php print $tImapsrv; !! <select name="fDestination">

<?php

  	$imap= new Net_IMAP($tImapsrv,'143');

		if ( PEAR::isError( $ret = $imap->login( $fUsername , $tPassuser  ) ) ) {
			echo "Unable to login! reason:" . $ret->getMessage() . "\n";
			exit();
		}
		
		$imap->selectMailbox('inbox');
		$list=$imap->getMailboxes('inbox');
		asort($list);
		 
		 $pattern[0] = '/INBOX./';
		 $pattern[1] = '/{'.$tImapsrv.'}/';
		 $pattern[2] = '/\./';
		 $replacement[0] = '';
		 $replacement[1] = '';
		 $replacement[2] = '/';
		 
		 if (is_array($list)) {
			 foreach ($list as $val) {
				 $folder = preg_replace($pattern, $replacement, $val);
				 $default = "";
				 if ( $table_filter[0]['destination'] == $folder ){
					 $default="selected";
					 $is_selected=TRUE;
				 }
				 echo '<option value="'.$folder.'" '.$default.'>'.$folder."</option>\n";
				 
			 }
			 if ( $is_selected != TRUE ){
				 echo '<option value="--" selected>--</option>'."\n";
			 }
			 
		 } else {
			 echo "imap_list failed: \n";
		 }
		 
		 $imap->disconnect();

?>

      </select></td>
<?php
	 }
	else{
		print '<td><input type="text" name="fDestination" value="'.$table_filter[0]['destination'].'"></td>';
	}
?>

	 </tr>

	 <tr>
    <td><?php print $PALANG['pUsersFilter_archive']; ?></td>
    <td><input type="text" name="fArchive_format"></td>
   </tr>

<?php
					}

if ($table_filter[0]['filteraction_id'] == "2") {
?>
	 <tr>
    <td><?php print $PALANG['pUsersFilter_destination_email'];?></td>
    <td><textarea name="fDestination_email" rows="10" cols="25"><?php print $table_filter[0]['destination'];?></textarea></td>
   </tr>

<?php 
  }
?>

	 <tr>
    <td><?php print $PALANG['pUsersFilter_comment']; ?></td>
    <td><textarea name="fComments" rows="10" cols="25"><?php print $table_filter[0]['comment'] ?></textarea></td>
   </tr>

	 <tr>
    <td><?php print $PALANG['pUsersFilter_active']; ?></td>
    <td><input type="checkbox" name="fActive" value="1" <?php if ($table_filter[0]['active'] == "1" ) print 'checked';  ?>></td>
   </tr>


   <tr>
     <td></td>
     <td><input type="submit" value="<?php print $PALANG['pUsersFilter_modsubmit']; ?>" ></td>
   </tr>

	 
  </table>
</form>

</center>