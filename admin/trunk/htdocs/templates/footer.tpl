<?php
  if ( isset($SESSID_USERNAME)){
?>

<script>

YUI().use("node-menunav", function(Y) {

    //    Use the "contentready" event to initialize the menu when the subtree of
    //    element representing the root menu (<div id="menu-1">) is ready to
    //    be scripted.

    Y.on("contentready", function () {

        //    The scope of the callback will be a Node instance representing
        //    the root menu (<div id="menu-1">).  Therefore, since "this"
        //    represents a Node instance, it is possible to just call "this.plug"
        //    passing in a reference to the MenuNav Node Plugin.

        this.plug(Y.Plugin.NodeMenuNav);

    }, "#menu-1");

});

</script>



<?php

}

if( (preg_match('/\blogin.php\b/', $_SERVER['SCRIPT_NAME'])) || (preg_match('/\bmain.php\b/', $_SERVER['SCRIPT_NAME'])) )
{
  print "<div class=\"submenu\">";
} else {
  print "<div id=\"submenu\">";
}
?>
<a target="_top" href="<?php print $_SERVER['PHP_SELF']; ?>"><?php print date("Y/m/d - H:i"); ?></a>&middot;
<?php
if (($CONF['show_footer_text'] == "YES") and ($CONF['footer_link']))
{
   print "<a target=\"_top\" href=\"" . $CONF['footer_link'] . "\">" . $CONF['footer_text'] . "</a>&middot;\n";
}
?>
<a target="_blank" href="http://www.oav.net/projects/openvisp-admin/">OpenVISP Admin <?php print $version; ?></a>
</div>
</center>

<?php
  if ( session_is_registered ("sessid") ){
?>
<?php
  }
?>

</body>
</html>
