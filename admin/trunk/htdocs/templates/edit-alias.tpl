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
$result = db_query ("select username from mailbox where domain ='$fDomain' order by username");

$tGoto = ereg_replace(',,',',', $tGoto);
$tGoto = ereg_replace(',',' ', $tGoto);

while ($row = db_array ($result['result']))
{
  $checked = "";
  if ( ereg( $row[0], $tGoto ) )
	{
		$checked = "checked";
		$tGoto = ereg_replace($row[0],'',$tGoto);
	}
	print '<input type="checkbox" name="check_alias[]" value="'.$row[0].'" '.$checked.'>'.$row[0]."<br />";
}
$tGoto = ereg_replace(' ',',', $tGoto);
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
         <input type="submit" name="submit" value="<?php print $PALANG['pEdit_alias_button']; ?>" />
         </form>
      </td>
   </tr>
</table>
