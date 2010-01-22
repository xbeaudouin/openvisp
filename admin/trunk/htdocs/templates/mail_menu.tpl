<div id="menu">
<?php
	 if ( isset($template) && ($template == "edit-filter.tpl" || $template == "list-filter.tpl" || $template == "add-filter.tpl") ){
?>
<a target="_top" href="add-filter.php"><?php print $PALANG['pMenu_addfilter']; ?></a>&middot;
<a target="_top" href="list-filter.php"><?php print $PALANG['pMenu_listfilter']; ?></a>&middot;
<?php
	 }
?>
<a target="_top" href="overview.php"><?php print $PALANG['pMenu_overview']; ?></a>&middot;
<a target="_top" href="sendmail.php"><?php print $PALANG['pMenu_sendmail']; ?></a>&middot;
<a target="_top" href="viewlog.php"><?php print $PALANG['pMenu_viewlog']; ?></a>&middot;
<?php
if (check_admin($SESSID_USERNAME))
{
?>
  <a target="_top" href="../users/main.php"><?php print $PALANG['pMenu_logout']; ?></a>
<?php
}
else
{
?>
  <a target="_top" href="main.php"><?php print $PALANG['pMenu_logout']; ?></a>
<?php
}
?>
</div>
<p>
