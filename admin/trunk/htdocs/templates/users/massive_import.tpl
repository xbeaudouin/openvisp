<?php


if ( $user_info->rights['manage'] == 0 ){
print $PALANG['pOverview_alias_mailbox_count'] . ": " . $user_info->data_managed['mailbox'] . " / ". $user_info->data_quota['emails'];
print "<br/>";
print $PALANG['pOverview_alias_alias_count'] . ": " . $user_info->data_managed['mail_alias'] . " / ". $user_info->data_quota['emails_alias'];
print "<br/>";
print $PALANG['pAdminList_admin_domain'] . ": " . $user_info->data_managed_ . " / ". $user_info->data_quota['emails_alias'];
}
?>

<center>

  <form enctype="multipart/form-data" name="import_file_alias" method="post">
  <?php print $PALANG['pImport_Alias_file'] . ":\n<br>"; ?>
  <input type="hidden" name="massive_type" value="mail_alias">
  <input type="file" name="user_file">
	<br>
  <input type="submit">
  </form>
	<br>

  <?php print $PALANG['pImport_Alias_help'] . "\n<br/>"; ?>
  <?php print $PALANG['pImport_Alias_help2'] . "\n<br/><br/>"; ?>
  <?php print $PALANG['pGlobalImport_Alias_help3'] . "\n<br/>"; ?>
  <?php print $PALANG['pGlobalImport_Alias_help4'] . "\n<br/><br/>"; ?>

<hr width="50%">

  <form enctype="multipart/form-data" name="import_file_domain" method="post">
  <?php print $PALANG['pAdminImport_file'] . ":\n<br>"; ?>
  <input type="hidden" name="massive_type" value="domains">
  <input type="file" name="domain_file">
	<br>
  <input type="submit">
  </form>
	<br>

  <?php print $PALANG['pAdminImport_help'] . "\n<br /><br />"; ?>
  <?php print $PALANG['pAdminImport_help2'] . "\n<br /><br />"; ?>
  <?php print $PALANG['pAdminImport_help3'] . "\n"; ?>


<hr width="50%">


</center>

