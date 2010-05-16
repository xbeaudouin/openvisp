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

$end_input = '';

for ( $i = 0; $i < sizeof($domain_info->list_mailboxes); $i++){
  $checked = "";
  if ( ereg( $domain_info->list_mailboxes[$i]['username'], $mail_info->data_alias['goto'] ) )
	{
		$checked = "checked";
		$mail_info->data_alias['goto'] = ereg_replace($domain_info->list_mailboxes[$i]['username'],'',$mail_info->data_alias['goto']);
	}
	if ( $domain_info->list_mailboxes[$i]['username'] == $fAddress ) {
		$end_input = '<span id="invisible" style="visibility: hidden"> <input type="checkbox" name="check_alias[]" value="'.$domain_info->list_mailboxes[$i]['username'].'" '.$checked.'>'.$domain_info->list_mailboxes[$i]['username']."</span><br/>";
	}
	else{
		print '<input type="checkbox" name="check_alias[]" value="'.$domain_info->list_mailboxes[$i]['username'].'" '.$checked.'>'.$domain_info->list_mailboxes[$i]['username']."<br />";
	}
}

$mail_info->data_alias['goto'] = ereg_replace(' ',',', $mail_info->data_alias['goto']);
print $end_input;
?>

<textarea rows="10" cols="80" name="fGoto">
<?php
$array = preg_split ('/,/', $mail_info->data_alias['goto']);
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
         <input type="reset" name="cancel"/>
         </form>
      </td>
   </tr>
</table>
