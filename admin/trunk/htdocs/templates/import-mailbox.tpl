<center>

  <form enctype="multipart/form-data" name="import_file_user" method="post">
  <?php print $PALANG['pImport_Users_file'] . ":\n<br>"; ?>
  <input type="hidden" name="fDomain" value="<?php print $_GET['domain'];?>">
  <input type="file" name="user_file">
	<br>
  <input type="submit">
  </form>
	<br>

  <?php print $PALANG['pImport_Users_help'] . "\n<br /><br />"; ?>
  <?php print $PALANG['pImport_Users_help2'] . "\n<br /><br />"; ?>
  <?php print $PALANG['pImport_Users_help3'] . "\n"; ?>


</center>
