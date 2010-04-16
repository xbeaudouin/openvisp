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

	 print "Stats globales des mails sur les 3 derniers mois \n</br>";

	 $sql_query = "SELECT DATE_FORMAT(FROM_UNIXTIME(stats_mail_user.last_date),'%Y-%m') as month,
	 SUM(spam) as spam, SUM(clean) as clean, SUM(blocked) as blocked , SUM(malformed) as malformed,
	 SUM(virus) as virus, SUM(bytes_in) as bytes_in, SUM(bytes_out) as bytes_out, SUM(mail_out) as mail_out
FROM stats_mail_user, domain, mailbox
WHERE domain.domain = '$fDomain'
AND domain.id = mailbox.domain_id
AND mailbox.id = stats_mail_user.mailbox_id
AND DATE_FORMAT(FROM_UNIXTIME(stats_mail_user.last_date),'%Y-%m')  BETWEEN DATE_SUB('". $year."-".$month."-01', INTERVAL $nb_month MONTH) AND '". $year."-".$month."-01'
GROUP BY month
";


	 $result = db_query($sql_query);
	 if ($result['rows'] > 0)
		 {

			 print "<table>\n";

			 print "<tr>\n";
			 print "<td>month</td>\n";
			 print "<td>bytes in</td>\n";
			 print "<td>bytes out</td>\n";
			 print "<td>total mail out</td>\n";
			 print "<td>spam</td>\n";
			 print "<td>clean</td>\n";
			 print "<td>blocked</td>\n";
			 print "<td>malformed</td>\n";
			 print "<td>virus</td>\n";
			 print "<td>Total</td>\n";
			 print "</tr>\n";

			 while ( $row = db_array ($result['result']) )
				 {
					 print "<tr>\n";
					 print "<td>".$row['month']."</td>\n";
					 print "<td>".convert_number_size_string($row['bytes_in'])."</td>\n";
					 print "<td>".convert_number_size_string($row['bytes_out'])."</td>\n";
					 print "<td>".$row['mail_out']."</td>\n";
					 print "<td>".$row['spam']."</td>\n";
					 print "<td>".$row['clean']."</td>\n";
					 print "<td>".$row['blocked']."</td>\n";
					 print "<td>".$row['malformed']."</td>\n";
					 print "<td>".$row['virus']."</td>\n";
					 print "<td>" . ($row['spam'] + $row['clean'] + $row['blocked'] + $row['malformed'] + $row['virus']) . "</td>\n";
					 print "</tr>\n";
				 }
			 print "</table>\n";
		 }


print "Stats par utilisateurs";

	 $sql_query = "SELECT mailbox.username, DATE_FORMAT(FROM_UNIXTIME(stats_mail_user.last_date),'%Y-%m') as month, SUM(spam) as spam, SUM(clean) as clean, SUM(blocked) as blocked , SUM(malformed) as malformed, SUM(virus) as virus, SUM(bytes_in) as bytes_in, SUM(bytes_out) as bytes_out, SUM(mail_out) as mail_out
FROM stats_mail_user, domain, mailbox
WHERE domain.domain = '$fDomain'
AND domain.id = mailbox.domain_id
AND mailbox.id = stats_mail_user.mailbox_id
AND DATE_FORMAT(FROM_UNIXTIME(stats_mail_user.last_date),'%Y-%m')  BETWEEN DATE_SUB('". $year."-".$month."-01', INTERVAL $nb_month MONTH) AND '". $year."-".$month."-01'
GROUP BY month, mailbox.username
";

	 $result = db_query($sql_query);
	 if ($result['rows'] > 0)
		 {

			 print "<table>\n";

			 print "<tr>\n";
			 print "<td>month</td>\n";
			 print "<td>username</td>\n";
			 print "<td>bytes in</td>\n";
			 print "<td>bytes out</td>\n";
			 print "<td>total mail out</td>\n";
			 print "<td>spam</td>\n";
			 print "<td>clean</td>\n";
			 print "<td>blocked</td>\n";
			 print "<td>malformed</td>\n";
			 print "<td>virus</td>\n";
			 print "<td>Total</td>\n";
			 print "</tr>\n";

			 while ( $row = db_array ($result['result']) )
				 {
					 print "<tr>\n";
					 print "<td>".$row['month']."</td>\n";
					 print "<td>".$row['username']."</td>\n";
					 print "<td>".convert_number_size_string($row['bytes_in'])."</td>\n";
					 print "<td>".convert_number_size_string($row['bytes_out'])."</td>\n";
					 print "<td>".$row['mail_out']."</td>\n";
					 print "<td>".$row['spam']."</td>\n";
					 print "<td>".$row['clean']."</td>\n";
					 print "<td>".$row['blocked']."</td>\n";
					 print "<td>".$row['malformed']."</td>\n";
					 print "<td>".$row['virus']."</td>\n";
					 print "<td>" . ($row['spam'] + $row['clean'] + $row['blocked'] + $row['malformed'] + $row['virus']) . "</td>\n";
					 print "</tr>\n";
				 }
			 print "</table>\n";
		 }


	 print "<img src=\"./stats-domain-img.php?domain=$fDomain&year=".$year."&month=".$month."\">";


/* print "<b>". $PALANG['pStats_welcome'] . $fDomain . "</b><br />\n"; */

/* 	 $query = "select msgs.time_iso, msgs.size, msgs.subject, maddr.email */
/* from msgs,msgrcpt,maddr */
/* where msgs.mail_id = msgrcpt.mail_id */
/* and msgrcpt.rid = maddr.id */
/* and ( msgs.time_iso BETWEEN '20080901T000000Z' AND '20081001T000000Z' ) */
/* "; */

/* 	 $query = "select sum(size) */
/* from zz_vw_mails */
/* where email like '%".$tDomain."' */
/* and ( time_iso BETWEEN '20080701T000000Z' AND '20080801T000000Z' ) */
/* "; */

/* print $query; */

/* create view zz_vw_mails AS */
/* select msgs.mail_id, msgs.secret_id, msgs.time_num, msgs.time_iso, msgs.sid, maddr_to.email, msgs.size, msgs.content */
/* from msgs,msgrcpt,maddr as maddr_to */
/* where msgs.mail_id = msgrcpt.mail_id */
/* and msgrcpt.rid = maddr_to.id */


/* 	 $result = db_query ("$query"); */
/* 	 $row = db_row ($result['result']); */
/* 	 print "<br />Total : ".$row[0]." Octets<br />"; */
/* 	 print "<br />Total : ".( $row[0] / 1024 / 1024 )." Mega Octets<br />"; */

/* 	 $query = "select count(msgs.size) */
/* from msgs,msgrcpt,maddr */
/* where msgs.mail_id = msgrcpt.mail_id */
/* and msgrcpt.rid = maddr.id */
/* and maddr.email like '%".$tDomain."' */
/* and ( msgs.time_iso BETWEEN '20080701T000000Z' AND '20080801T000000Z' ) */
/* "; */

/* 	 print $query; */

/* 	 $result = db_query ("$query"); */
/* 	 $row = db_row ($result['result']); */
/* 	 print "<br />Total : ".$row[0]." Mails<br />"; */

print "<br />\n";


print "</table>\n";
print "<p />\n";

?>
