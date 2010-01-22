<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pEdit_alias_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form name="mailbox" method="post">
         <?php print $PALANG['pEdit_alias_address'] . ":\n"; ?>
      </td>
      <td>
         <?php print $fAddress; ?>
      </td>
      <td>
         &nbsp;<br />&nbsp;
      </td>
   </Tr>
   <tr>
      <td valign="top">
         <?php print $PALANG['pEdit_alias_goto'] . ":\n"; ?>
      </td>
			<td>
<?php
$result = db_query ("SELECT username FROM mailbox, domain WHERE domain.domain='$fDomain' AND domain.id=mailbox.domain_id ORDER BY username");

$tGoto = ereg_replace(',,',',', $tGoto);
$tGoto = ereg_replace(',',' ', $tGoto);

$end_input = '';

while ($row = db_array ($result['result']))
{
  $checked = "";
  if ( ereg( $row[0], $tGoto ) )
	{
		$checked = "checked";
		$tGoto = ereg_replace($row[0],'',$tGoto);
	}
	if ( $row[0] == $fAddress ) {
		$end_input = '<span id="invisible" style="visibility: hidden"> <input type="checkbox" name="check_alias[]" value="'.$row[0].'" '.$checked.'>'.$row[0]."</span><br/>";
	}
	else{
		print '<input type="checkbox" name="check_alias[]" value="'.$row[0].'" '.$checked.'>'.$row[0]."<br />";
	}
}
$tGoto = ereg_replace(' ',',', $tGoto);
print $end_input;
?>

<textarea rows="10" cols="80" name="fGoto">
<?php
$array = preg_split ('/,/', $tGoto);
for ($i = 0 ; $i < sizeof ($array) ; $i++)
{
   if (empty ($array[$i])) continue;
   print "$array[$i]\n";
}
?>
</textarea>

			</td>
	 </tr>

   <tr>
      <td align="center" colspan="3">
         <input type="hidden" name="fDomain" value="<?php print $fDomain; ?>" />
         <input type="hidden" name="fAddress" value="<?php print $fAddress; ?>" />
         <input type="submit" name="submit" value="<?php print $PALANG['pEdit_alias_button']; ?>" />
         </form>
      </td>
   </tr>
</table>
