<table>
<tr>
  <td>From</td><td>To</td><td>Subject</td><td>Spam Level</td><td>Date</td><td>Content</td>
</tr>


<?php
	 for ($i = 0; $i < sizeof ($result); $i++){
		 print "<tr>\n";
		 print "  <td>".$result[$i]['emailfrom']."</td>\n";
		 print "  <td>".$result[$i]['emailto']."</td>\n";
		 print "  <td>".$result[$i]['subject']."</td>\n";
		 print "  <td>".$result[$i]['spamlevel']."</td>\n";
		 print "  <td>".$result[$i]['date']."</td>\n";
		 print "  <td>".$result[$i]['content']."</td>\n";
		 print "</tr>\n";
	 }
?>



</table>