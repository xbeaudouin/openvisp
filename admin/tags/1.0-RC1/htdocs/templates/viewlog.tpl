<form name="overview" method="post">
<select name="fDomain" onChange="this.form.submit()";>
<?php
for ($i = 0; $i < sizeof ($list_domains); $i++)
{
   if ($fDomain == $list_domains[$i])
   {
      print "<option value=\"$list_domains[$i]\" selected>$list_domains[$i]</option>\n";
   }
   else
   {
      print "<option value=\"$list_domains[$i]\">$list_domains[$i]</option>\n";
   }
}
?>
<option value="ova.local">ova.local</option>
</select>
<input type="submit" name="go" value="<?php print $PALANG['pViewlog_button']; ?>" />
</form>
<p />
<?php 

print "<b>". $PALANG['pViewlog_welcome'] . $fDomain . "</b><br />\n";
print "<p />\n";

if (sizeof ($tLog) > 0)
{
   print "<table>\n";
   print "   <tr class=\"header\">\n";
   print "      <td>" . $PALANG['pViewlog_timestamp'] . "</td>\n";
   print "      <td>" . $PALANG['pViewlog_username'] . "</td>\n";
   print "      <td>" . $PALANG['pViewlog_domain'] . "</td>\n";
   print "      <td>" . $PALANG['pViewlog_action'] . "</td>\n";
   print "      <td>" . $PALANG['pViewlog_data'] . "</td>\n";
   print "   </tr>\n";

   for ($i = 0; $i < sizeof ($tLog); $i++)
   {
      if ((is_array ($tLog) and sizeof ($tLog) > 0))
      {
         $log_data = $tLog[$i]['data'];
         $data_length = strlen ($log_data);
         if ($data_length > 35) $log_data = substr ($log_data, 0, 35) . " ...";
         
         print "   <tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
         print "      <td>" . $tLog[$i]['timestamp'] . "</td>\n";
         print "      <td>" . $tLog[$i]['username'] . "</td>\n";
         print "      <td>" . $tLog[$i]['domain'] . "</td>\n";
         print "      <td>" . $tLog[$i]['action'] . "</td>\n";
         print "      <td>" . $log_data . "</td>\n";
         print "   </tr>\n";
      }
   }

   print "</table>\n";
   print "<p />\n";
}
?>
