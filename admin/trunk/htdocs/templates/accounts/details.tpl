<?php

  print $PALANG['pAccountList_details'] . ": <b>" . $fUsername . "</b><br>\n";
  
  // domain for user
  //
    print "<table>\n";
    print "   <tr class=\"header2\">\n";
    print "       <td colspan=\"9\">DOMAINS</td>\n";
    print "   </tr>\n";
    if ((is_array($user_domains)) && (sizeof($user_domains) > 0))
    {
      print " <tr class=\"header\">\n";
      print "     <td>1</td>\n";
      print "     <td>2</td>\n";
      print "     <td>3</td>\n";
      print "     <td>4</td>\n";
      print "     <td>5</td>\n";
      print " </tr>\n";
			if ( ( $is_big_admin != 1 ) && (sizeof($user_domains) >= 4 ) ){
				for ( $i = 0; $i < sizeof($user_domains); $i++ ){
					print "<tr>\n";
					if ( $user_domains[$i] == "" ){ $i++; }
					print "     <td>" . $user_domains[$i] . "</td>\n";
					$i++;
					print "     <td>" . $user_domains[$i] . "</td>\n";
					$i++;
					print "     <td>" . $user_domains[$i] . "</td>\n";
					$i++;
					print "     <td>" . $user_domains[$i] . "</td>\n";
					$i++;
					print "     <td>" . $user_domains[$i] . "</td>\n";
					print "</tr>\n";
				}
			}
			elseif ( ( $is_big_admin != 1) && (sizeof($user_domains) < 4 ) ){
				print "<tr>\n";

 				for ( $i = 0; $i < sizeof($user_domains); $i++ ){
					if ( $i != (sizeof($user_domains) - 1) ){
						print "     <td>" . $user_domains[$i] . "</td>\n";
					}
					else{
						print '     <td colspan="'. (5 - $i) .'">' . $user_domains[$i] . "</td>\n";
					}
				}
				print "</tr>\n";


			}
			else{
				print "<tr>\n";
				print "     <td> ALL </td>\n";
				print "     <td></td>\n";
				print "     <td></td>\n";
				print "     <td></td>\n";
				print "     <td></td>\n";
				print "</tr>\n";
			}

    }
    else
    {
      print "<tr><td colspan=\"5\"><center>- No domain available- </center></td></tr>";
    }
    print "</table>\n";
    print "<br />";
  
  // mail details
  //
  if ($account_properties['mail'] == 1)
  {
    print "<table>\n";
    print "   <tr class=\"header2\">\n";
    print "       <td colspan=\"9\">MAIL</td>\n";
    print "   </tr>";
    if ((is_array($mailbox_accounts)) && (sizeof($mailbox_accounts) > 0))
    {
      print " <tr class=\"header\">\n";
      print "     <td>" . $PALANG['pOverview_mailbox_username'] . "</td>\n";
      print "     <td>" . $PALANG['pOverview_mailbox_name'] . "</td>\n";
      print "     <td>" . $PALANG['pOverview_mailbox_quota'] . "</td>\n";
      print "     <td>" . $PALANG['pOverview_get_domain'] . "</td>\n";
      print "     <td>" . $PALANG['pAccountList_ftp_created'] . "</td>\n";
      print "     <td>" . $PALANG['pAccountList_ftp_modified'] . "</td>\n";
      print "     <td>" . $PALANG['pAccountList_ftp_enabled'] . "</td>\n";
      print "     <td>&nbsp;</td>\n";
      print "     <td>&nbsp;</td>\n";
      print " </tr>\n";

      for ($i = 0; $i < sizeof ($mailbox_accounts); $i++)
      {

				print " <tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
				print "     <td>".$mailbox_accounts[$i]['username']."</td>\n";
				print "     <td>".$mailbox_accounts[$i]['name']."</td>\n";
				if($mailbox_accounts[$i]['quota'] <= "0") {
					print "     <td>&infin;</td>\n";
				} else {
					print "     <td>".$mailbox_accounts[$i]['quota'] / $CONF['quota_multiplier']."</td>\n";
				}

				print "     <td>".$mailbox_accounts[$i]['domain']."</td>\n";
				print "     <td>".$mailbox_accounts[$i]['created']."</td>\n";
				print "     <td>".$mailbox_accounts[$i]['modified']."</td>\n";
				print "     <td>".$mailbox_accounts[$i]['active']."</td>\n";
      //
      // Need to be complete
      //
				print "     <td><a href=\"../mail/edit-mailbox.php?username=" . urlencode ($mailbox_accounts[$i]['username']) . "&domain=" . $mailbox_accounts[$i]['domain'] . "\">" . $PALANG['edit'] . "</a></td>\n";
				print "     <td><a href=\"?login=" . $mailbox_accounts[$i]['username'] . "\" onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</td>\n";
				print "</tr>\n";
			}

    }
    else
    {
      print "<tr><td colspan=\"7\"><center>- No mail available- </center></td></tr>";
    }
    print "</table>\n";
    print "<br />";
  }
  
  // datacenter details
  //
  if ($account_properties['datacenter'] == 1)
  {
    print "<table>\n";
    print "   <tr class=\"header2\">\n";
    print "       <td colspan=\"9\">DATACENTER</td>\n";
    print "   </tr>";
    if ((is_array($datacenter_accounts)) && ($datacenter_accounts['counter'] > 0))
    {
      print " <tr class=\"header\">\n";
      print "     <td>" . $PALANG['pAccountList_ftp_login'] . "</td>\n";
      print "     <td>" . $PALANG['pAccountList_ftp_dir'] . "</td>\n";
      print "     <td>" . $PALANG['pAccountList_ftp_bandwidthul'] . "</td>\n";
      print "     <td>" . $PALANG['pAccountList_ftp_bandwidthdl'] . "</td>\n";
      print "     <td>" . $PALANG['pAccountList_ftp_created'] . "</td>\n";
      print "     <td>" . $PALANG['pAccountList_ftp_modified'] . "</td>\n";
      print "     <td>" . $PALANG['pAccountList_ftp_enabled'] . "</td>\n";
      print "     <td>&nbsp;</td>\n";
      print "     <td>&nbsp;</td>\n";
      print " </tr>\n";
      print " <tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
      print "     <td>&nbsp;</td>\n";
      //
      // Need completion
      //
      print "     <td><a href=\"datacenter-edit.php?login=" . $ftp_accounts['login'] . "\">" . $PALANG['edit'] . "</a></td>\n";
      print "     <td><a href=\"datacenter-del.php?login=" . $ftp_accounts['login'] . "\" onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</td>\n";

    }
    else
    {
      print "<tr><td colspan=\"7\"><center>- No account available- </center></td></tr>";
    }
    print "</table>\n";
    print "<br />";
  }
  
  // ftp details
  //
  if ($account_properties['ftp'] == 1)
  {
    print "<table>\n";
    print "   <tr class=\"header2\">\n";
    print "       <td colspan=\"10\">FTP</td>\n";
    print "   </tr>";
    if ((is_array($ftp_accounts)) && (sizeof($ftp_accounts) > 0))
    {
        print " <tr class=\"header\">\n";
        print "     <td>" . $PALANG['pAccountList_ftp_login'] . "</td>\n";
        print "     <td>" . $PALANG['pAccountList_ftp_dir'] . "</td>\n";
        print "     <td>" . $PALANG['pAccountList_ftp_quota'] . "</td>\n";
        print "     <td>" . $PALANG['pAccountList_ftp_bandwidthul'] . "</td>\n";
        print "     <td>" . $PALANG['pAccountList_ftp_bandwidthdl'] . "</td>\n";
        print "     <td>" . $PALANG['pAccountList_ftp_created'] . "</td>\n";
        print "     <td>" . $PALANG['pAccountList_ftp_modified'] . "</td>\n";
        print "     <td>" . $PALANG['pAccountList_ftp_enabled'] . "</td>\n";
        print "     <td>&nbsp;</td>\n";
        print "     <td>&nbsp;</td>\n";
        print " </tr>\n";

      for ($i = 0; $i < sizeof ($ftp_accounts); $i++)
      {
        print " <tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
        print "     <td>" . $ftp_accounts[$i]['login'] . "</td>\n";
        print "     <td>" . $ftp_accounts[$i]['dir'] . "</td>\n";
        print "     <td>" . $ftp_accounts[$i]['quotasz'] . "</td>\n";
        print "     <td>" . $ftp_accounts[$i]['bandwidthul'] . "</td>\n";
        print "     <td>" . $ftp_accounts[$i]['bandwidthdl'] . "</td>\n";
        print "     <td>" . $ftp_accounts[$i]['created'] . "</td>\n";
        print "     <td>" . $ftp_accounts[$i]['modified'] . "</td>\n";
          $actif = ($ftp_accounts[$i]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
        print "     <td>" . $actif . "</td>\n";
        print "     <td><a href=\"../hosting/edit-ftp.php?ftpaccount=" . $ftp_accounts[$i]['login'] . "&domain=" . $ftp_accounts[$i]['domain'] . "\">" . $PALANG['edit'] . "</a></td>\n";
        print "     <td><a href=\"../hosting/delete.php?account=" . $ftp_accounts[$i]['login'] . "&domain=" . $ftp_accounts[$i]['domain'] . "\" onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</td>\n";
        print " </tr>\n";
      }
    }
    else
    {
      print "<tr><td colspan=\"7\"><center>- No account available-</center></td></tr>";
    }
    print "</table>\n";
    print "<br />";
  }
  
  // Web hosting details
  //
  if ($account_properties['http'] == 1)
  {
    print "<table>\n";
    print "   <tr class=\"header2\">\n";
    print "       <td colspan=\"9\">HTTP</td>\n";
    print "   </tr><tr class=\"header\">\n";
    print "       <td>" . $PALANG['pAccountList_ftp_login'] . "</td>\n";
    print "       <td>" . $PALANG['pAccountList_ftp_dir'] . "</td>\n";
    print "       <td>" . $PALANG['pAccountList_ftp_bandwidthul'] . "</td>\n";
    print "       <td>" . $PALANG['pAccountList_ftp_bandwidthdl'] . "</td>\n";
    print "       <td>" . $PALANG['pAccountList_ftp_created'] . "</td>\n";
    print "       <td>" . $PALANG['pAccountList_ftp_modified'] . "</td>\n";
    print "       <td>" . $PALANG['pAccountList_ftp_enabled'] . "</td>\n";
    print "       <td>&nbsp;</td>\n";
    print "       <td>&nbsp;</td>\n";
    print "   </tr>\n";
    print "<tr><td colspan=\"7\"><center>- No account available-</center></td></tr>";
    print "</table>\n";
    print "<br />";
  }
  
?>
