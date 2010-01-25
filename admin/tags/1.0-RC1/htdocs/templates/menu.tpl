<div id="menu">
<?php
 if (isSet($SESSID_USERNAME) && check_admin($SESSID_USERNAME))
 {
    //print "<a target=\"_top\" href=\"".$CONF['baseurl']."/users/main.php\">" . $PALANG['pMenu_logout'] . "</a>\n";
    print "<a target=\"_top\" href=\"".getabsoluteuri()."/users/main.php\">" . $PALANG['pMenu_logout'] . "</a>\n";
 }
 else
 {
    //print "<a target=\"_top\" href=\"".$CONF['baseurl']."/users/main.php\">" . $PALANG['pMenu_logout'] . "</a>\n";
    print "<a target=\"_top\" href=\"".getabsoluteuri()."/users/main.php\">" . $PALANG['pMenu_logout'] . "</a>\n";
 }
?>
</div>
<p>
