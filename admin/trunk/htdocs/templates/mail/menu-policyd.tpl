<div id="menu">

<?php
if ( check_admin($SESSID_USERNAME) == false )
{
?>
<a target="_top" href="../users/main.php"><?php print $PALANG['pMenu_logout']; ?></a>

<?php
}
else
{
	print '<a target="_top" href="../mail/policyd_bl.php">'.$PALANG['pPolicyd_manage_ip_bl'].'</a>';
	print '<a target="_top" href="../mail/policyd_bl_helo.php">'.$PALANG['pPolicyd_manage_helo'].'</a>';
	print '<a target="_top" href="../mail/policyd_bl_sender.php">'.$PALANG['pPolicyd_manage_sender_bl'].'</a>';
	print '<a target="_top" href="../mail/policyd_wl.php">'.$PALANG['pPolicyd_manage_ip_wl'].'</a>';

}


?>

</div>
<p>
