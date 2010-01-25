<script language="javascript">
      <!--

function checkAll(master){
  var checked = document.spamform.master.checked;
  var col = document.spamform.getElementsByTagName("INPUT");
  for (var i=0;i<col.length;i++) {
    col[i].checked= checked;
  }
}

//-->
</script>

<h3><center>Bonjour <?php print $username['username'];?></center></h3>
<?php

print $tMessage;
if ( (is_array ($date_quarantine)) && ( sizeof($date_quarantine) > 0 ) ) {

	 ?>
	 <form method="post">

		 <select name="fDate" onChange="this.form.submit()";>

		 <?php
				 for ($i = 0; $i < sizeof($date_quarantine); $i++)
					 {
						 if ($tDate == $date_quarantine[$i])
							 {
								 print "<option value=\".$date_quarantine[$i]\" selected>$date_quarantine[$i]</option>\n";
							 }
						 else
							 {
								 print "<option value=\"$date_quarantine[$i]\">$date_quarantine[$i]</option>\n";
							 }
					 }
	 ?>
				 </select>
						 <input type="hidden" name="key" value="<?php print $tKey;?>">
						 <input type="hidden" name="key2" value="<?php print $tKey2;?>">
				 </form>

<?php


	 if  ( (is_array($spam_list_date)) && (sizeof($spam_list_date) >= 1 ) ){

?>				 
				 <form name="spamform" method="post">
				 <br>Liste des spams pour la date du <?php print $tDate; ?>
				 <table>
						
						<tr>
						<td>Date</td>
						<td>From</td>
						<td>Sujet</td>
						<td>Taille</td>
						<td>Note</td>
						<td>Size</td>
						<td>Selection <input type="checkbox" name="master" onclick="checkAll()"></td>
						<td></td>
						</tr>
						
<?php

for ($i = 0; $i < sizeof($spam_list_date); $i++){
	
	print '<tr>';
  print '<td>'.$spam_list_date[$i]['time_mail'].'</td>';
  print '<td>'.$spam_list_date[$i]['from_addr'].'</td>';
  print '<td>'.$spam_list_date[$i]['subject'].'</td>';
  print '<td>'.$spam_list_date[$i]['size'].'</td>';
  print '<td>'.$spam_list_date[$i]['spam_level'].'</td>';
  print '<td>'.$spam_list_date[$i]['size'].'</td>';
	print '<td>';
// 	print '<select name="">';
// 	print '  <option value="nothing" selected>--</option>';
// 	print '  <option value="delete">Suppression</option>';
// 	print '  <option value="release">Relacher</option>';
// 	print '/<select>';
	print '<input type="checkbox" name="fMailid[]" value="'.urlencode($spam_list_date[$i]['mail_id']).'">'."\n";
	print '</td>';
	//  print '<td>'.$spam_list_date[$i]['size'].'</td>';
	// 	print '<input type="hidden" value="'.$spam_list_date[$i]['mail_id'].'" name="mailid">';
	// 	print '<input type="hidden" value="'.$spam_list_date[$i]['secret_id'].'" name="secret_id">';
	print '</tr>';

}

?>

</table>
<select name="fAction">
  <option value="delete">Suppression</option>
  <option value="release">Relacher</option>
</select>
<input type="hidden" name="key" value="<?php print $tKey;?>">
<input type="hidden" name="key2" value="<?php print $tKey2;?>">
<input type="hidden" name="fDate" value="<?php print $tDate;?>">
<input type="submit">
<input type="reset">

</form>

<?php
		}
 }
 else{
	print $PALANG['pAmavis_Nospam'];
 }
?>