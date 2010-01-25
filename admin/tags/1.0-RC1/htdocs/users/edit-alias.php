<?php
//
// File: edit-alias.php
//
// Template File: users/edit-alias.tpl
//
// Template Variables:
//
// tMessage
// tGoto
//
// Form POST \ GET Variables:
//
// fAddress
// fDomain
// fGoto
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();
$tmp = preg_split ('/@/', $SESSID_USERNAME);
$USERID_DOMAIN = $tmp[1];

$result = db_query("SELECT mailbox.allowchangefwd, mailbox.allowchangepwd, domain.allowchangepwd as domallowchangepwd, domain.allowchangefwd as domallowchangefwd FROM mailbox,domain WHERE mailbox.username='$SESSID_USERNAME' and mailbox.domain=domain.domain ");

if ($result['rows'] == 1)
	{
		$row = db_array($result['result']);

		if ( ($row['allowchangefwd'] == 1 ) && ($row['domallowchangefwd']) && ($CONF["usermanagefwd"] == "YES") )
			{ 
				
				if ($_SERVER['REQUEST_METHOD'] == "GET")
					{
						$result = db_query ("SELECT * FROM alias WHERE address='$SESSID_USERNAME'");
						if ($result['rows'] == 1)
							{
								$row = db_array ($result['result']);
								$tGoto = $row['goto'];
							}
						else
							{
								$tMessage = $PALANG['pEdit_alias_address_error'];
							}
						
						include ("../templates/header.tpl");
						include ("../templates/users/menu.tpl");
						include ("../templates/users/edit-alias.tpl");
						include ("../templates/footer.tpl");
					}
				
				if ($_SERVER['REQUEST_METHOD'] == "POST")
					{
						$pEdit_alias_goto = $PALANG['pEdit_alias_goto'];
						
						$fGoto = get_post ('fGoto');
						$fGoto = strtolower ($fGoto);
						
						$goto = preg_replace ('/\r\n/', ',', $fGoto);
						$goto = preg_replace ('/[\s]+/i', '', $goto);
						$goto = preg_replace ('/\,*$/', '', $goto);
						$array = preg_split ('/,/', $goto);
						
						for ($i = 0; $i < sizeof ($array); $i++) {
							if (in_array ("$array[$i]", $CONF['default_aliases'])) continue;
							if (empty ($array[$i])) continue;
							if (!check_email ($array[$i]))
								{
									$error = 1;
									$tGoto = $goto;
									$tMessage = $PALANG['pEdit_alias_goto_text_error2'] . "$array[$i]</font>";
								}
						}
						
						if ($error != 1)
							{
								if (empty ($fGoto))
									{
										$goto = $SESSID_USERNAME;
									}
								else
									{
										$goto = $SESSID_USERNAME . "," . $goto;
									}
								
								$result = db_query ("UPDATE alias SET goto='$goto',modified=NOW() WHERE address='$SESSID_USERNAME'");
								if ($result['rows'] != 1)
									{
										$tMessage = $PALANG['pEdit_alias_result_error'];
									}
								else
									{
										db_log ($SESSID_USERNAME, $USERID_DOMAIN, "edit alias", "$SESSID_USERNAME -> $goto");
										
										header ("Location: main.php");
										exit;
									 }
							}
						
						include ("../templates/header.tpl");
						include ("../templates/users/menu.tpl");
						include ("../templates/users/edit-alias.tpl");
						include ("../templates/footer.tpl");
					}
				
				
			}
		
		else
			{               
				header ("Location: main.php");
				exit;
			}
		
	}



?>
