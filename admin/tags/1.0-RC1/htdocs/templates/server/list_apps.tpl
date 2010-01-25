<?php
   print "<table>\n";
   print "   <tr class=\"header\">\n";
   print "      <td>" . $PALANG['pApplication_name']. "</td>\n";
   print "      <td>" . $PALANG['pApplication_version'] . "</td>\n";
   print "      <td>" . $PALANG['pApplication_group'] . "</td>\n";
   print "      <td colspan=\"2\">&nbsp;</td>\n";
   print "   </tr>\n";

   for ($i = 0; $i < sizeof ($list_apps); $i++)
		 {

			 print "   <tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
			 print "      <td>" . $list_apps[$i]['apps'] . "</td>\n";
			 print "      <td>" . $list_apps[$i]['version'] . "</td>\n";
			 print "      <td>" . $list_apps[$i]['role'] . "</td>\n";
			 print "      <td></td>\n";
			 print "      <td></td>\n";
			 print "   </tr>\n";

		 }

   print "</table>\n";
   print "<p />\n";



	for ($i = 0; $i < sizeof ($list_apps); $i++)
	{
		
		print "<h3>".$list_apps[$i]['role']."</h3>\n";

		

	}
?>