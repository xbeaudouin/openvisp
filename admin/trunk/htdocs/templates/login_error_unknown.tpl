<?php 

print "<div id=\"toppage\">\n";
if ($CONF['logo'] == "YES")
{
   print "<div class=\"menu\"><img src=\"images/openvisp-admin.png\" /></div>\n";
}
else
{
   print "<div class=\"menu\">" . $CONF['header_text'] . "</div>\n";
}

print $tMessage . "\n"; 

?>
<br /><br />
<form name="login" method="post">
<input type="hidden" name="fCookie" value="<?php print crsf_key('login'); ?>" />
 <table class="login">
   <tr>
    <td class="cell"><?php print $PALANG['pLogin_update_error_unknown']; ?></td>
   </tr>
 </table>
</form>
<br /><br />
</div>
