<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pUsersVacation_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form name="vacation" method="post">
         <?php print $PALANG['pUsersVacation_subject'] . ":\n"; ?>
      </td>
			<?php if ( isset ($_GET['username'])) print '<input type="hidden" name="fUsername" value="'.$fUsername.'">'; ?>
			<?php if ( isset ($_GET['domain'])) print '<input type="hidden" name="fDomain" value="'.$fDomain.'">'; ?>
      <td>
         <input type="text" name="fSubject" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pUsersVacation_body'] . ":\n"; ?>
      </td>
      <td>
<textarea rows="10" cols="80" name="fBody">

</textarea>
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td align="center" colspan="3">
<?php

    if ( $fActive == 1 ){
			print $PALANG['pOverview_mailbox_responder_msg_status_active'];
			print '<input type="radio" name="fStatus" value="1" checked><br/>';
			print $PALANG['pOverview_mailbox_responder_msg_status_inactive'];
			print '<input type="radio" name="fStatus" value="0" >';
		}
		else{
			print $PALANG['pOverview_mailbox_responder_msg_status_active'];
			print '<input type="radio" name="fStatus" value="1"><br/>';
			print $PALANG['pOverview_mailbox_responder_msg_status_inactive'];
			print '<input type="radio" name="fStatus" value="0" checked>';
		}

    print '<input type="hidden" name="fType" value="'.$fType.'"';

?>

      </td>
   </tr>
   <tr>
      <td align="center" colspan="3">
         <input type="submit" name="fAway" value="<?php print $PALANG['pUserinfo_button']; ?>" />
         </form>
      </td>
   </tr>
</table>
