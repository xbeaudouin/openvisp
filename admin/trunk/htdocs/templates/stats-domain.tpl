<form name="stats-domain" method="post">
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
</select>
<input type="hidden" name="limit" value="0">
<input type="submit" name="go" value="<?php print $PALANG['pOverview_button']; ?>" />
</form>
<p />
<?php 

print "<b>". $PALANG['pStats_welcome'] . $fDomain . "</b><br />\n";

	 $query = "select msgs.time_iso, msgs.size, msgs.subject, maddr.email
from msgs,msgrcpt,maddr
where msgs.mail_id = msgrcpt.mail_id
and msgrcpt.rid = maddr.id
and msgs.time_iso like '200511%'
";

	 $query = "select sum(msgs.size)
from msgs,msgrcpt,maddr
where msgs.mail_id = msgrcpt.mail_id
and msgrcpt.rid = maddr.id
and maddr.email like '%".$tDomain."'
and msgs.time_iso like '200511%'
";

	 $result = db_query ("$query");
	 $row = db_row ($result['result']);
	 print "<br />Total : ".$row[0]." Octets<br />";
	 print "<br />Total : ".( $row[0] / 1024 / 1024 )." Mega Octets<br />";

	 $query = "select count(msgs.size)
from msgs,msgrcpt,maddr
where msgs.mail_id = msgrcpt.mail_id
and msgrcpt.rid = maddr.id
and maddr.email like '%".$tDomain."'
and msgs.time_iso like '200511%'
";

	 print $query;

	 $result = db_query ("$query");
	 $row = db_row ($result['result']);
	 print "<br />Total : ".$row[0]." Mails<br />";

print "<br />\n";


print "</table>\n";
print "<p />\n";

?>
