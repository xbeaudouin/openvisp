<?php
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
</body>
</html>
