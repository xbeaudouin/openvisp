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
      <td rowspan="3"><img src="./images/ico-password.png" width="64" height="64" class="png"></td>
      <td class="cell"><?php print $PALANG['pLogin_username'] . ": \n"; ?><input type="text" name="fUsername" value="<?php print $tUsername; ?>" /><br /></td>
      <td class="cell">&nbsp;</td>
   </tr>
   <tr>
      <td class="cell"><?php print $PALANG['pLogin_password'] . ":\n"; ?><input type="password" name="fPassword" /><br /></td>
<?php
   if (getcryptograph()) {
?>
      <td rowspan="2" class="cell"><?php dsp_crypt($CONF['cryptoconf'],1); ?></td>
<?php
   } else {
?>
      <td rowspan="2" class="cell">&nbsp;</td>
<?php
   }
?>
   </tr>
   <tr>
<?php
   if (getcryptograph()) {
?>
      <td class="cell"><?php print $PALANG['pLogin_crypto'] . ":\n"; ?><input type="code" name="fCode" /><br /></td>
<?php
   } else {
?>
      <td class="cell">&nbsp;</td>
<?php
   }
?>
   </tr>
   <tr>
     <td>&nbsp;</td><td class="cell"><input class="button" type="submit" name="submit" value="<?php print $PALANG['pLogin_button']; ?>" /></td>
   </tr>
 </table>
</form>
<br /><br />
</div>
