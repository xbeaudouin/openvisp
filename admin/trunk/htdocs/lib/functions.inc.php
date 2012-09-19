<?php
//
// File: functions.inc.php
//
error_reporting  (E_NOTICE | E_ERROR | E_WARNING | E_PARSE);

if (ereg ("functions.inc.php", $_SERVER['PHP_SELF']))
{
	redirect_login();
	exit;
}

// Version of this software
$version = "1.0-RC1";

//
// check_session
// Action: Check if a session already exists, if not redirect to login.php
// Call: check_session ()
//
function check_session ()
{
   session_start ();
   if (!session_is_registered ("sessid"))
   {
      redirect_login();
      exit;
   }
   $SESSID_USERNAME = $_SESSION['sessid']['username'];
   return $SESSID_USERNAME;
}

//
// check_admin_session
// Action: Check if an admin (eg big admin) session already exist, if not redirect
//         to login.php if it is not logged in otherwise logout the user.
// Call: check_admin_session()
function check_admin_session()
{
  $SESSID_USERNAME = check_user_session();
  if (!check_admin($SESSID_USERNAME)) {
		redirect_logout();
    exit;
  }
  return $SESSID_USERNAME;
}

// 
// check_user_session
// Action: Check if a user session already exist, if not redirect to login.php
//
function check_user_session ()
{
   //session_start ();
   $USERID_USERNAME = check_session();
   if (!session_is_registered ("userid"))
   {
		 	redirect_login();
      exit;
   }
   $USERID_USERNAME = $_SESSION['userid']['username'];
   return $USERID_USERNAME;
}

//
// check_service_admin
// Action: Check if a user or an admin can manage one or more services
// Call: check_service_admin();
//
function check_service_session()
{
    $USERID_USERNAME = check_session();
    $SESSID_USERNAME = $USERID_USERNAME;
    return $SESSID_USERNAME;
} 

//
// check_domain_admin
// Action: Check if a user is allowed to manage domains.
// Call: check_domains_admin($username)
//
function check_domain_admin($username)
{
    $result = db_query ("SELECT rights.domain FROM rights,accounts WHERE accounts.username='$username' AND accounts.enabled='1' AND accounts.id=rights.accounts_id");
    $row = db_array($result['result']);
    if ($row['domain'] == 1 ) {
      return true;
    } else {
      return false;
    }
}

//
// check_mail_admin
// Action: Check if an user is allowed to manage mail.
// Call: check_mail_admin($username, $nologout)
//
function check_mail_admin($username)
{
	global $CONF;
	$nologout = NULL;
	$numargs = func_num_args();

	if ( $numargs == 2) {
		$nologout = func_get_arg(1);
	}

	if ( check_mailhosting() ) {
		$result = db_query ("SELECT rights.mail FROM rights LEFT JOIN accounts ON rights.accounts_id=accounts.id WHERE accounts.username='$username' AND accounts.enabled='1'");
		$row = db_array($result['result']);
		if ($row['mail'] == 1 ) {
			return true;
		} else {
			if($nologout == NULL) {
				header ("Location: ../logout.php");
				exit;
			} else {
				return false;
			}
		}
	} else {
		if($nologout == NULL) {
			header ("Location: ../logout.php");
			exit;
		} else {
			return false;
		}
	}
}

//
// check_ftp_admin
// Action: Check if a user is allowed to manage ftp.
// Call: check_ftp_admin($username)
//
function check_ftp_admin($username)
{
    global $CONF;
    if ( check_ftphosting() ) {
			$result = db_query ("SELECT rights.ftp FROM rights LEFT JOIN accounts ON rights.accounts_id=accounts.id WHERE accounts.username='$username' AND accounts.enabled='1'");
    	$row = db_array($result['result']);
    	if ($row['ftp'] == 1 ) {
      		return true;
    	} else {
      		return false;
    	}
    }
    else{
	return false;
    }
}


//
// check_mysql_database_admin
// Action: Check if a user is allowed to manage mysql database.
// Call: check_mysql_database_admin($username)
//
function check_mysql_database_admin($username)
{
    global $CONF;
    if ( check_dbhosting()  ) {
			$result = db_query ("SELECT rights.mysql FROM rights LEFT JOIN accounts ON rights.accounts_id=accounts.id WHERE accounts.username='$username' AND accounts.enabled='1'");
    	$row = db_array($result['result']);
    	if ($row['mysql'] == 1 ) {
      		return true;
    	} else {
      		return false;
    	}
    }
    else{
			return false;
    }
}

//
// check_postgresql_database_admin
// Action: Check if a user is allowed to manage postgresql database.
// Call: check_postgresql_database_admin($username)
//
function check_postgresql_database_admin($username)
{
    global $CONF;

    if ( check_dbhosting() ) {
   	$result = db_query ("SELECT rights.postgresql FROM rights LEFT JOIN accounts ON rights.accounts_id=accounts.id WHERE accounts.username='$username' AND accounts.enabled='1'");
    	$row = db_array($result['result']);
    	if ($row['postgresql'] == 1 ) {
      		return true;
    	} else {
      		return false;
    	}
    }
    else{
	return false;
    }
}

//
// check_database_admin
// Action: Check if a user is allowed to manage database.
// Call: check_database_admin($username)
//
function check_database_admin($username)
{
    global $CONF;

    if ( check_dbhosting() ) {
   	$result = db_query ("SELECT rights.mysql, rights.postgresql FROM rights LEFT JOIN accounts ON rights.accounts_id=accounts.id WHERE accounts.username='$username' AND accounts.enabled='1'");
    	$row = db_array($result['result']);
    	if ($row['mysql'] == 1 || $row['postgresql'] == 1 ) {
      		return true;
    	} else {
      		return false;
    	}
    }
    else{
	return false;
    }
}


//
// check_http_admin
// Action: Check if a user is allowed to manage http.
// Call: check_http_admin($username)
//
function check_http_admin($username)
{
    global $CONF;

    if ( check_webhosting()) {
		$result = db_query ("SELECT rights.http FROM rights LEFT JOIN accounts ON rights.accounts_id=accounts.id WHERE accounts.username='$username' AND accounts.enabled='1'");
    	$row = db_array($result['result']);
    	if ($row['http'] == 1 ) {
      		return true;
    	} else {
      		return false;
    	}
    }
    else{
	return false;
    }
}

//
// check_datacenter_admin
// Action: Check if a user is allowed to manage datacenter.
// Call: check_datacenter_admin($username)
//
function check_datacenter_admin($username)
{
	global $CONF;

	if ($CONF['datacenter'] == 'YES') {
		$result = db_query ("SELECT rights.datacenter FROM rights LEFT JOIN accounts ON rights.accounts_id=accounts.id WHERE accounts.username='$username' AND accounts.enabled='1'");
		$row = db_array($result['result']);
		if ($row['datacenter'] == 1 ) {
			return true;
		} else {
			return false;
		}
	}
	else {
		return false;
	}
}


//
// check_admin
// Action: Check if the admin is a "big" admin (eg user with wilcard powers !)
// Call: check_admin (string admin)
// 
function check_admin ($username)
{
		$result = db_query ("SELECT rights.manage FROM rights LEFT JOIN accounts ON rights.accounts_id=accounts.id WHERE accounts.username='$username' AND accounts.enabled='1'");
    $row = db_array($result['result']);
    if ($row['manage'] == 1 ) {
      return true;
    } else {
      return false;
    }

}

//
// check_little_admin
// Action: Check if the admin is a "little" admin (eg user with admin powers on at last one thing !)
// Call: check_admin (string admin)
// 
function check_little_admin ($username)
{
		$result = db_query ("SELECT enabled FROM accounts WHERE username='$username' AND enabled='1'");
    $row = db_array($result['result']);
    if ($row['enabled'] == 1 ) {
      return true;
    } else {
      return false;
    }

}

//
// get_admin_id
// Action: Fetch the admin id
// Call: get_admin_id (string admin)
// 
function get_admin_id ($username)
{
		$result = db_query ("SELECT id FROM accounts WHERE username='$username'");
    $row = db_array($result['result']);
		return $row['id'];

}



//
// check_language
// Action: checks what language the browser uses
// Call: check_language
//
function check_language ()
{
   global $CONF;
   $supported_languages = array ('ca', 'cn', 'cs', 'da', 'de', 'en', 'es', 'eu', 'fi', 'fo', 'fr', 'fr-ca', 'hu', 'is', 'it', 'nl', 'nn', 'pl', 'pt-br', 'ru', 'sv', 'tr','tw');
   if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $lang_array = preg_split ('/(\s*,\s*)/', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
   } else {
    $lang_array = "";
   }
   if (is_array ($lang_array))
   {
      $lang_first = strtolower ((trim (strval ($lang_array[0]))));
      $lang_first = substr ($lang_first, 0, 2);
      if (in_array ($lang_first, $supported_languages))
      {
         $lang = $lang_first;
      }
      else
      {
         $lang = $CONF['default_language'];
      }
   }
   else
   {
      $lang = $CONF['default_language'];
   }
   if ($CONF['default_language_forced'] == 'NO')
   {
		 return $lang;
   }
   else
   {
		 return $CONF['default_language'];
   }
}

//
// get_username_key
// Action: get the username associated with a key in spamreport table
// Call: get_username_key(string key)
//
function get_username_key($key)
{
	$query = "SELECT mailbox.username, mailbox.id FROM spamreport, mailbox WHERE spamreport.id='$key' AND spamreport.mailbox_id=mailbox.id";
	$result = db_query($query);
	$row = db_array($result['result']);
	return ($row);
}



//
// get_all_spam_key
// Action: get the keys for a user spam quarantine
// Call: get_all_spam_key($username)
//
function get_all_spam_key($username)
{
	
	$sql_query = "SELECT spamreport.id, spamreport.key2, spamreport.created FROM spamreport, mailbox WHERE mailbox.username='$username' AND mailbox.id=spamreport.mailbox_id";

	$result = db_query ($sql_query);
	$row = db_array($result['result']);
	return $row;

}

//
// check_user_greylisting
// Action: check if a user have greylisting activated or not.
// Call: check_user_greylisting(string username)
//
function check_user_greylisting($username)
{

	
	
	$result = db_query ("select _optin from policy where _rcpt='$username'","1","policyd");
	if ( $result['rows'] > 0 ){
		$row = db_array($result['result']);
		return $row['_optin'];
	}
	else{
		$domain = explode('@', $username, 2);
		return check_domain_greylisting($domain[1]);
	}

}


//
// check_greylisting_domain_desc
// Action: check if a domain have greylisting activated or not in the domain desc.
// Call: check_greylisting_domain_desc(string domain)
//
function check_greylisting_domain_desc($domain)
{
	
	$result = db_query ("select greylist from domain where domain='$domain'");
	if ( $result['rows'] > 0 ){
		$row = db_array($result['result']);
		return $row['greylist'];
	}
	else{
		return 0;
	}

}


//
// get_overquota_user_list
// Action: Get the list of all user wich are overquota
// Call: get_overquota_user_list(string username)
//
function get_overquota_user_list($username)
{

	$list = array();

  if ( check_admin($username) ){

		$result = db_query ("SELECT *
    FROM overquota");		

	}
	else{

		$username_id = get_account_id($username);

		$result = db_query ("SELECT overquota.*
    FROM overquota, domain_admins, mailbox
    WHERE domain_admins.accounts_id = '$username_id'
      AND domain_admins.domain_id = mailbox.domain_id
      AND mailbox.username = overquota.user");
		/* la requete doit etre affiner a la date d'aujourd'hui) */

	}

	if ( $result['rows'] > 0 ){
		while ( $row = db_array($result['result']) ){
			$list[] = $row;
		}
	}

	
	return $list;

}


//
// check_domain_greylisting
// Action: check if a domain have greylisting activated or not.

// 
// check_domain_greylisting
// Action: get the greylisting status of a domain  
// Call: check_domain_greylisting(string domain)
//
function check_domain_greylisting($domain)
{
	
	$result = db_query ("select _optin from policy where _rcpt='@".$domain."'","1","policyd");
	if ( $result['rows'] > 0 ){
		$row = db_array($result['result']);
		return $row['_optin'];
	}
	else{
		return 0;
	}

}

// 
// add_domain_mailbox
// Action: add a new mailbox in a domain
// Call: add_domain_mailbox(string domain, string username, string password, string name, int quota, int smtpauth, int active, int sendmail, int spamreport, int pop3_enabled, int imap_enabled)
//
function add_domain_mailbox ($domain, $username, $password="", $name, $quota, $smtpauth, $active, $sendmail, $spamreport=2, $pop3_enabled=0, $imap_enabled=0)
{
	
	GLOBAL $CONF;
	GLOBAL $SESSID_USERNAME;
	GLOBAL $PALANG;
	$message = "";
	$error = 0;

	if ( $spamreport == 2 && $CONF['spamreport'] == "YES" ) 
		{ $spamreport = 1;}
	else
		{ $spamreport = 0;}

	$domain_policy = get_domain_policy ($domain);

	if (!check_owner ($SESSID_USERNAME, $domain))
		{
			$error = 1;
			$message .= $name ."/". $username ."=>". $PALANG['pCreate_mailbox_username_text_error1'] . "<br />" ;
		}
			
	if (!check_mailbox ($domain))
		{
			$error = 1;
			$message .= $name ."/". $username ."=>". $PALANG['pCreate_mailbox_username_text_error3'] . "<br />" ;
		}
			
	if (empty ($username) or ($username == NULL) or !check_email ($username))
		{
			$error = 1;
			$message .= $name ."/". $username ."=>". $PALANG['pCreate_mailbox_username_text_error1'] . "<br />" ;
		}

			
	if (!check_quota ($quota, $domain))
		{
			$error = 1;
			$message .= $PALANG['pCreate_mailbox_quota_text_error'] . "<br />" ;
		}
			

	if ( $error == 0 )
		{

			if (empty ($password) )
				{
					if ($CONF['generate_password'] == "YES")
						{
							if ($CONF['password_generator'] == ""){	$password = generate_password ();	}
							else {	$password = exec($CONF['password_generator']);		}
						}
					else
						{
							$error = 1;
							$message .= $name ."/". $username ."=>". $PALANG['pCreate_mailbox_password_text_error'] . "<br />" ;
						}
				}
	
			if ( $error == 0 ){
				
				if ($CONF['domain_path'] == "YES")
					{

						if ($CONF['domain_in_mailbox'] == "YES")
							{
								$maildir = $domain . "/" . $username . "/";
							}

						else 
							{
								$maildir = $domain . "/" . escape_string($username) . "/";
							}

					}

				else

					{
						$maildir = $username . "/";
					}
				
				if (!empty ($quota)) $quota = $quota * $CONF['quota_multiplier'];
				if ($active == "on") $active = 1;
				if ($pop3_enabled == "on") $pop3_enabled = 1;
				if ($imap_enabled == "on") $imap_enabled = 1;
				if ($smtpauth == "on") $smtpauth = 1;

				$add_mailbox_alias_return = add_mailbox_alias( $domain, $username, $username );
				$message .= $add_mailbox_alias_return['message'];
				
				if ( $add_mailbox_alias_return['status'] == 0 ){
					$sql_query = "INSERT INTO mailbox (username,password,name,maildir,quota,domain_id,created,active,smtp_enabled,imap_enabled,pop3_enabled,spamreport)
VALUES ('$username','".pacrypt($password)."','$name','$maildir','$quota',".$domain_policy['domain_id'].",NOW(),'$active','$smtpauth','$pop3_enabled','$imap_enabled','$spamreport')";
					$result = db_query ( $sql_query );

					if ($result['rows'] != 1)
						{
							$tMessage .= "<br />" . $PALANG['pCreate_mailbox_result_error'] . "<br />($username)<br />";
						}
			
					else
						{
							$message .= $PALANG['pCreate_mailbox_result_succes'] . "<br />($username";
							if ($CONF['generate_password'] == "YES")
								{
									$message .= " / $password)<br />";
								}
							else
								{
									$message .= ")<br />";
								}

							db_log ($SESSID_USERNAME, "$domain", "create mailbox", "$username");

							if ($sendmail == "on")
								{
									$fTo = $username;
									$fFrom = $SESSID_USERNAME;
									
									require("../lib/send_mail.inc.php");
									send_mail($fFrom, $fTo, escape_string($PALANG['pSendmail_subject_text']), escape_string($PALANG['pSendmail_body_text'])); 
									$message .= "<br />" . $PALANG['pSendmail_result_succes'] . "<br />";
									
								}
						}

				}

			}

		}


	return $message;

}

// 
// add_mailbox_alias
// Action: add a new alias in mailbox alias table
// Call: add_mailbox_alias (string domain, string from, string to, int greylisting)
//
function add_mailbox_alias($domain, $from, $to, $greylisting=2)
{

	GLOBAL $CONF;
	GLOBAL $PALANG;
	GLOBAL $SESSID_USERNAME;

	$domain_policy = get_domain_policy($domain);
	$message = "";

	if ( check_policyhosting() ){

		if ( $greylisting == 2 && $CONF['greylisting'] == 'YES' )
			{ $greylisting = 1;}
		else 
			{ $greylisting = 0;}
	}

	$error = 0;

	if (!check_owner ($SESSID_USERNAME, $domain))
		{
      $error = 1;
      $message .= $PALANG['pCreate_alias_address_text_error1'];
		 }


   if (!check_alias ($domain))
   {
      $error = 1;
      $message .= $PALANG['pCreate_alias_address_text_error3'];
   }

	 if (!preg_match ('/@/',$to)) $to = $to . "@" . $domain;
	 if (!preg_match ('/@/',$from)) $from = $from . "@" . $domain;

	 if (empty ($from) or !check_email ($from))
		 {
					 $error = 1;
					 $message .= $PALANG['pCreate_alias_address_text_error1']." $from";
		 }
	 
	 if (preg_match ('/^\*@(.*)$/', $to, $match)) $to = "@" . $match[1];


	 if (empty ($to) or !check_email ($to))
		 {
			 $error = 1;
			 $message .= $PALANG['pCreate_alias_goto_text_error']. " $to";
		 }



	 if ( $error == 0 ){

		 if ( check_policyhosting() &&  (!preg_match ('/^@/',$from)) ){
			 $result = db_query("INSERT INTO policy(_rcpt,_optin,_priority) VALUES ('".$from."','".$greylisting."','50')","1","policyd");
				 if ($result['rows'] != 1){
					 $message .= $PALANG['pCreate_alias_policy_fail'] . "<br />($from)<br />\n";
				 }
				 else{
					 $message .= $PALANG['pCreate_alias_policy_ok'] . "<br />($from)<br />\n";
				 }

		 }


		 $result = db_query ("SELECT * FROM alias WHERE address='$from' AND goto='$to' AND policy_id='".$domain_policy['id']."'");
		 if ($result['rows'] == 0 )
			 {

				 $result = db_query ("INSERT INTO alias (address,goto,policy_id,created,active) VALUES ('$from','$to','".$domain_policy['id']."',NOW(),'1')");
				 if ($result['rows'] != 1)
					 {
						 $error = 1;
						 $message .= "<br />" . $PALANG['pCreate_alias_result_error'] . "<br />($from -> $to)</br />";
					 }
				 else
					 {
						 $error = 0;
						 $message .= "<br />" . $PALANG['pCreate_alias_result_succes'] . "<br />($from -> $to)</br />";
						 db_log ($SESSID_USERNAME, "$domain", "create alias", "$from -> $to");
					 }
			 }
		 else
			 {
				 $error = 1;
				 $message .= "<br />" . $PALANG['pCreate_alias_result_error_exist'] . "<br />($from -> $to)</br />";
			 }

	 }
	$array['status'] = $error;
	$array['message'] = $message;

	return $array;

}

//
// associate_domain_admin
// Action: associate a domain and an admin
// Call: associate_domain_admin (string domain, string admin)
//
function associate_domain_admin ($domain, $admin)
{

	GLOBAL $CONF;
	GLOBAL $PALANG;
	$admin_id = get_admin_id($admin);
	$domain_id = get_domain_id($domain);
	$error = 0;
	$list = "";
	$message = "";

	if ( ! check_admin($admin) )
		{

			$sql_query = "INSERT INTO domain_admins (accounts_id,domain_id,created,active)
VALUES ('".$admin_id."','".$domain_id."',NOW(),'1')";

			$result = db_query ();
			if ($result['rows'] != 1)
				{
					$error = 1;
					$message .= $PALANG['pAdminCreate_domain_result_error3'] . "<br />($domain)<br />";
				}
			else
				{
					db_log ("$SESSID_USERNAME", "ova.local", "Domains", "Attach domain $domain to admin $admin");
				}
		}

	$list['message'] = $message;
	$list['error'] = $error;
	return $list;

}



//
// add_domain
// Action: add a new domain
// Call: add_domain ( string domain, string description, int aliases, int mailboxes, int maxquota, int backupmx, int antivirus, int vrfydomain, int vrfysender, int greylisting, int spf array forbidden_helo)
// 
function add_domain ( $domain, $description, $aliases='-1', $mailboxes='-1', $maxquota='-1', $backupmx='0', $antivirus='0', $vrfydomain='0', $vrfysender='0', $greylisting = 2, $spf='0', $forbidden_helo = FALSE, $imap_enabled=0, $pop3_enabled=0, $smtp_enabled=0)
{

	GLOBAL $CONF;
	GLOBAL $SESSID_USERNAME;
	GLOBAL $PALANG;

	$domain_id = "";
	$message = "";
	$error = 0;

	if ( $aliases == NULL ) $CONF['aliases'];
	if ( $mailboxes == NULL ) $CONF['mailboxes'];
	if ( $maxquota == NULL ) $CONF['maxquota'];

	if ( $greylisting == 2 && $CONF['greylisting'] == "YES" )
		{ $greylisting = 1; }
	else
		{ $greylisting = 0; }

	if ($backupmx == "1")
		{
			$aliases = 0;
			$mailboxes = 0;
			$maxquota = 0;
		}

	
	if ( check_domain_quota() != TRUE ){
		$message .= $PALANG['pAdminList_domain_overquota'];
		$error = 1;
	}


	if (($domain != NULL) && domain_exist($domain) != false)
		{
			$error = 1;
			$message .= $PALANG['pAdminCreate_domain_domain_text_error'];
		}

	if ( $error == 0 )
		{

			$sql_query = "INSERT INTO domain (domain,description,aliases,mailboxes,maxquota,backupmx,antivirus,vrfydomain,vrfysender,greylist,spf,smtp_enabled,imap_enabled,pop3_enabled,created)
VALUES ('$domain','$description','$aliases','$mailboxes','$maxquota','$backupmx','$antivirus','$vrfydomain','$vrfysender','$greylisting','$spf','$smtp_enabled','$imap_enabled','$pop3_enabled',NOW())";

			$result = db_query ($sql_query);
			if ($result['rows'] != 1)
				{
					$error = 1;
					$message = $PALANG['pAdminCreate_domain_result_error'] . "<br />($domain)<br />";
				}

			if ( $error == 0 )
				{

					$associate = associate_domain_admin($domain, $SESSID_USERNAME);

					$domain_id = $result['inserted_id'];


					if ( $CONF['forbidden_helo'] == 'YES' && check_policyhosting() && $forbidden_helo != FALSE){
					
						$goto = preg_replace ('/\r\n/', ',', $forbidden_helo);
						$array = preg_split ('/,/', $goto);
					
						for ($i = 0; $i < sizeof ($array); $i++)
							{
								add_bl_helo($array[$i], $domain);
							}
					}

				}
		}
	
	$list['message'] = $message;
	$list['error'] = $error;
	$list['domain_id'] = $domain_id;

	return $list;

}

// 
// add_domain_greylisting
// Action: add the greylisting status of a domain  
// Call: add_domain_greylisting(string domain, int status)
//
function add_domain_greylisting($domain, $status)
{
	
	$result = db_query ("INSERT INTO POLICY(_rcpt, _optin, _priority) VALUES('@".$domain."','".$status."','10')","1","policyd");
	return $result;

}


//
// get_spam_key
// Action: get the static key for a user
// Call: get_spam_key(int username_id)
//
function get_spam_key($username_id)
{
	
		$result = db_query ("select key2, created from spamreport where mailbox_id='$username_id'");
    $row = db_array($result['result']);

		return $row;

}


//
// list_date_quarantine
// Action: get list of date with mail in quarantine
// Call: list_date_quarantine(string username)
//
function list_date_quarantine($username)
{

	$list = array();

	$query = "SELECT DISTINCT LEFT(msgs.time_iso,8) as date ";
	$query .= " FROM msgs, msgrcpt, maddr, alias, policy ";
	$query .= " where maddr.email = alias.address ";
  $query .= " and alias.goto like '%".$username."%' ";
	$query .= " and maddr.id = msgrcpt.rid";
	$query .= " and alias.policy_id = policy.id";
  $query .= " and maddr.email = '".$username."'";
	$query .= " and msgrcpt.mail_id = msgs.mail_id";
	$query .= " and msgrcpt.rs = '' ";
	$query .= " and msgs.content = 'S'";
	$query .= " and msgs.spam_level >= policy.spam_kill_level";
	$query .= " ORDER BY msgs.time_iso";

	$result = db_query($query);
	if ( $result['rows'] > 0 ){
		while ( $row = db_array($result['result']) ){
			$list[] = $row['date'];
		}
	}
	
	return $list;

}


//
// search_email
// Action: search email between 2 people and 2 date.
// search_email (string user_from, string user_to, date begin_date, date end_date)
//
function search_email ($user_from, $user_to, $begin_date, $end_date){

	$list = array();

	$sql_query = "	
SELECT maddr.email as emailto, msgs.subject as subject, msgs.quar_type as quarantine, msgs.spam_level as spamlevel , msgs.time_iso as date, msgs.quar_loc, maddr_sender.email as emailfrom, msgs.content, msgrcpt.rs
FROM msgrcpt, maddr, msgs, maddr as maddr_sender
WHERE maddr.email like '".$user_to."'
AND msgrcpt.rid = maddr.id
AND msgrcpt.mail_id = msgs.mail_id
AND msgs.time_iso  BETWEEN '".$begin_date."T000000Z' AND '".$end_date."T000000Z' 
AND msgs.sid = maddr_sender.id
AND maddr_sender.email like '".$user_from."'
";

	$result = db_query($sql_query);
	if ( $result['rows'] > 0 ){
		while ( $row = db_array($result['result']) ){
			$list[] = $row;
		}
	}
	
	return $list;


}

//
// clean_quarantine
// Action: mark all mail in quarantine as deleted.
// clean_quarantine (string user)
//
function clean_quarantine($user){

	$sql_query="UPDATE msgrcpt, maddr
SET msgrcpt.rs = 'D'
WHERE msgrcpt.rid = maddr.id 
AND maddr.email = '".$user."'
";

	$result = db_query ($sql_query);
	return $result;

}

//
// reset_quarantine_status
// Action: remove all the flag on suspicious mail between this date.
// reset_quarantine_status(string user, date begin_date, date end_date)
//
function reset_quarantine_status($user, $begin_date, $end_date){

	$sql_query ="
UPDATE msgrcpt, maddr, msgs
SET msgrcpt.rs = ''
WHERE msgrcpt.rs != ''
AND msgrcpt.mail_id = msgs.mail_id 
AND msgs.time_iso  BETWEEN '".$begin_date."T000000Z' AND '".$end_date."T000000Z' 
AND msgrcpt.rid = maddr.id 
AND maddr.email = '".$user."'
";

	$result = db_query ($sql_query);
	return $result;

}

//
// list_spam_date
// Action: get all the quarantine spam for an user and a date
// Call: list_spam_date (date date, string username)
//
function list_spam_date($date, $username){

	$list = array();

	$query  = " SELECT msgs.from_addr, msgs.spam_level, msgs.size, msgs.subject, msgs.mail_id, msgs.secret_id, msgrcpt.rid, FROM_UNIXTIME(msgs.time_num) as time_mail, msgrcpt.rs";
	$query .= " FROM msgs, msgrcpt, maddr, alias, policy";
	$query .= " where maddr.email = alias.address ";
  $query .= " and alias.goto like '%".$username."%' ";
	$query .= " and alias.policy_id = policy.id";
	$query .= " and maddr.id = msgrcpt.rid";
  $query .= " and maddr.email = '".$username."'";
	$query .= " and msgrcpt.mail_id = msgs.mail_id";
	$query .= " and msgrcpt.rs = '' ";
	$query .= " and msgs.content = 'S'";
	$query .= " and msgs.spam_level >= policy.spam_kill_level";
	$query .= " and msgs.time_iso like '%".$date."%'";

	$result = db_query($query);
	if ( $result['rows'] > 0 ){
		while ( $row = db_array($result['result']) ){
			$list[] = $row;
		}
	}
	return $list;

}

//
// get_list_email_spamreport
// Action: get the whole email list of mailbox that will receive spam quarantine code.
// Call: get_list_email_spamreport ()
//
function get_list_email_spamreport()
{
	$list = array();
	$query = "SELECT username, id FROM mailbox WHERE active='1' AND spamreport='1'";

	$result = db_query($query);
	if ( $result['rows'] > 0 ){
		while ( $row = db_array($result['result']) ){
			$list[] = $row;
		}
	}
	return $list;

}

//
// check_mailbox_exist
// Action check if the mailbox already exist
// Call: check_mailbox_exist (string mailbox, string domain)
//
function check_mailbox_exist($mailbox, $domain){

	$result = db_query ("SELECT * FROM mailbox WHERE username='".$mailbox."@".$domain."'");
	if ($result['rows'] > 0){
		return 1;
	}
	else{
		return 0;
	}
}

//
// check_string
// Action: checks if a string is valid and returns TRUE is this is the case.
// Call: check_string (string var)
//
function check_string ($var)
{
   if (preg_match ('/^([A-Za-z0-9 ]+)+$/', $var))
   {
      return true;
   }
   else
   {
      return false;
   }
} 

//
// check_email
// Action: Checks if email is valid and returns TRUE if this is the case.
// Call: check_email (string email)
//
function check_email ($email)
{
	$regexp  = '/';
	$regexp .= '^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+' . '@' . '([-0-9A-Z]+\.)+' . '([0-9A-Z]){2,4}$';
	$regexp .= '|';
	$regexp .= '^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+' . '@' . '([-0-9A-Z]+\.)+' . '([0-9A-Z]){2,4}';
	$regexp .= '(, [-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+' . '@' . '([-0-9A-Z]+\.)+' . '([0-9A-Z]){2,4})+';
	$regexp .= '$';
	$regexp .= '/i';
   if (preg_match ( $regexp, trim ($email)))
   {
      return true;
   }
   else
   {
      return false;
   }
}

//
// escape_string
// Action: Escape a string
// Call: escape_string (string string)
//
function escape_string ($string)
{
   if (get_magic_quotes_gpc () == 0)
   {
      $search = array ("/'/", "/\"/", "/;/");
      $replace = array ("\\\'", "\\\"", "\\;");
      $escaped_string = preg_replace ($search, $replace, $string); 
   }
   else
   {
       $escaped_string = $string;
   }
   return $escaped_string;
}

// 
// get_post
// Action: Get a variable from POST
// Call: get_post('var');
//
function get_post ($string)
{
  if (($_SERVER['REQUEST_METHOD'] == "POST") && isset($_POST[$string]))
  {
    return  escape_string($_POST[$string]);
  }
  else
  { 
    return NULL;
  }
}

//
// get_get
// Action: Get a variable from GET
// Call: get_get('var');
//
function get_get ($string)
{
  if (($_SERVER['REQUEST_METHOD'] == "GET") && isset($_GET[$string]))
  {
    return  escape_string($_GET[$string]);
  } 
  else
  { 
    return NULL;
  } 
} 



//
// delete_domain_alias
// Action: Delete a domain alias
// Call: delete_domain_alias (string domain)
//
function delete_domain_alias ($fDomain)
{

	global $CONF;
	global $SESSID_USERNAME;
	global $PALANG;

	$tMessage = "";
	$error = 0;
	$list = "";

	$result = db_query ("SELECT * FROM alias WHERE address='@$fDomain'");
	if ($result['rows'] > 0 ) {
		$result2 = db_query ("DELETE FROM alias WHERE address='@$fDomain'");
		if ($result['rows'] <= 0)
			{
				$error = 1;
				$tMessage .= $PALANG['pDelete_delete_error'] . "<b> alias of domain alias</b> </div>";
			}
		else
			{
				db_log ($SESSID_USERNAME, "ova.local", "delete mailbox aliases", "mailbox aliases $fDomain");
			}
	}
	
	$result = db_query ("SELECT domain.domain FROM domain_alias,domain  WHERE domain_alias.dalias='$fDomain' AND domain_alias.domain_id=domain.id");
	if ($result['rows'] > 0 ) {

		$result2 = db_query ("DELETE FROM domain_alias WHERE dalias='$fDomain'");
		if ($result['rows'] <= 0)
			{
				$error = 1;
				$tMessage .= $PALANG['pDelete_delete_error'] . "<b> domain alias </b> </div>";
			}
		else
			{
				db_log ($SESSID_USERNAME, "ova.local", "delete domain alias", "$fDomain");
			}
	}


	$list['error'] = $error;
	$list['message'] = $tMessage;
	return $list;

}

//
// delete_domain
// Action: Delete a domain
// Call: delete_domain (string domain)
//
function delete_domain ($fDomain)
{

	global $CONF;
	global $SESSID_USERNAME;
	global $PALANG;

	$list = "";
	$tMessage = "";
	$error = 0;
	
	if ( $CONF['forbidden_helo'] == "YES" && check_policyhosting() ){

//  		$result = db_query ("SELECT * FROM blacklist_helo WHERE _helo like '%$fDomain'", 1, "policyd");
// 		if ($result['rows'] > 0 ) {
// 			$result = db_query ("DELETE FROM blacklist_helo WHERE _helo like '%$fDomain'", 1,"policyd");
// 			if ($result['rows'] <= 0)
// 				{
// 					$error = 1;
// 					$tMessage = $PALANG['pDelete_delete_error'] . "<b>$fDomain</b> (Forbidden Helo)! </div>";
// 				}
// 			else
// 				{
// 					db_log ($SESSID_USERNAME, "ova.local", "policyd", "$fDomain : delete Forbidden Helo");
// 				}
// 		}

		$result = db_query ("SELECT * FROM spamtrap WHERE _rcpt like '%$fDomain'", 1, "policyd");
		if ($result['rows'] > 0 ) {
			$result = db_query ("DELETE FROM spamtrap WHERE _rcpt like '%$fDomain'", 1, "policyd");
			if ($result['rows'] <= 0)
				{
					$error = 1;
					$tMessage = $PALANG['pDelete_delete_error'] . "<b>$fDomain</b> (Forbidden Helo)! </div>";
				}
			else
				{
					db_log ($SESSID_USERNAME, "ova.local", "policyd", "$fDomain : delete spamtrap");
				}
		}

	}
	if ( check_policyhosting() ){
		$result = db_query ("SELECT * FROM policy WHERE _rcpt like '%$fDomain'", 1, "policyd");
		if ($result['rows'] > 0 ) {
			$result = db_query ("DELETE FROM policy WHERE _rcpt like '%$fDomain'", 1, "policyd");
			if ($result['rows'] <= 0)
				{
					$error = 1;
					$tMessage = $PALANG['pDelete_delete_error'] . "<b>$fDomain</b> (policyd email rules)! </div>";
				}
			else
				{
					db_log ($SESSID_USERNAME, "ova.local", "policyd", "$fDomain : delete domain policy");
				}
		}
	}


	$result = db_query ("DELETE FROM domain WHERE domain='$fDomain'");
	if ($result['rows'] != 1)
		{
			$error = 1;
			$tMessage = $PALANG['pDelete_delete_error'] . "<b>Delete $fDomain</b> (domain)!</div>";
		}
	else
		{
			db_log ($SESSID_USERNAME, "ova.local", "deleted domain", "$SESSID_USERNAME delete $fDomain");
		}

	$list['error'] = $error;
	$list['message'] = $tMessage;

	return $list;

}


//
// get_domain_policy
// Action: Get all policy values of a domain.
// Call: get_domain_policy (string domain)
//
function get_domain_policy ($domain)
{
	global $CONF;
	$list = "";

	$result = db_query ("SELECT policy.* FROM policy,domain WHERE domain.domain='$domain' AND domain.id=policy.domain_id");
	$row = db_array ($result['result']);
	
	if (is_array($row) or is_object($row))
	 {
		 reset($row);
		 while (list($key, $value) = each($row)) {
			 $list[$key] = $value;
		 }
	 }
	return $list;

}


//
// get_domain_id
// Action: Get domain's id
// Call: get_domain_id (string domain)
//
function get_domain_id ($domain)
{
	global $CONF;
	$list = "";

	$result = db_query ("SELECT domain.id as id FROM domain WHERE domain.domain='$domain'");

	if ( $result['rows'] == 1 ){
		$row = db_array($result['result']);
		return($row['id']);
	}
}

//
// get_mailbox_id
// Action: fetch user's mailbox id
// Call: get_mailbox_id (string username)
//
function get_mailbox_id ($username)
{
	global $CONF;
	$list = "";

	$result = db_query ("SELECT mailbox.id FROM mailbox WHERE mailbox.username='$username'");

	if ( $result['rows'] == 1 ){
		$row = db_array($result['result']);
		return($row['id']);
	}	
}


//
// check_filter_owner
// Action: Checks if the user is the owner of the filter.
// Call: check_owner (string username, int filter)
//
function check_filter_owner ($username, $fnum)
{

	$sql_query = "SELECT DISTINCT mailbox.username
FROM filter, mailbox
WHERE mailbox.username='$username'
AND mailbox.id=filter.mailbox_id
AND filter.id='$fnum'";

   $result = db_query ($sql_query);
   if ($result['rows'] != 1)
   {
      return false;
   }
   else
   {
      return true;
   }
}


//
// get_email_filter
// Action: Get all filter from a email account and his num (not necessary)
// Call: get_email_filter (string email, int num)
//
function get_email_filter ($email, $num = '' )
{
	global $CONF;
	$list = array();
	$sql_query_add = "";
	if ( $num > 0 ){
		$sql_query_add = "                 AND filter.id = '$num'";
	}

	$sql_query = "SELECT filter.*, filter_field.fieldname, filteraction_field.actionname
FROM filter,filter_field, filteraction_field, mailbox
WHERE mailbox.username='$email'
AND mailbox.id = filter.mailbox_id
$sql_query_add
AND filter.filter_field_id=filter_field.id
AND filter.filteraction_id=filteraction_field.id
order by exec_order"; 

	$result = db_query ($sql_query);
	if ( $result['rows'] > 0 ){

		while ($row = db_array($result['result'])) {
			$list[] = $row;
		}
		return $list;
	}
	else{
		return false;
	}


}

//
// add_email_filter
// Action: Add a new mail filter for a user
// Call: add_email_filter ( string username, int ffield, string ffieldvalue, int fexecorder, string filtername)
//
function add_email_filter ( $username, $ffield, $ffieldvalue, $fexecorder, $filtername, $faction, $fdestination, $fcomment)
{
	$result = db_query ("INSERT INTO filter (email,exec_order,filtername, fk_fieldnum, fieldvalue, fk_actionnum, destination, active, comment, creationdate, modificationdate)
VALUES ('$username','$fexecorder','$filtername','$ffield','$ffieldvalue','$faction','$fdestination','1','$fcomment',NOW(),NOW())");
	return $result;
}

//
// get_mail_action
// Action: get the name of the mail action number
// Call get_mail_action (int number)
//
function get_mail_action($num){
	$result = db_query("SELECT actionname FROM filteraction_field where id='$num'");
	if ( $result['rows'] > 0 ){
		$row = db_array($result['result']);
		return($row['actionname']);
	}
	else{
		return false;
	}
}

// update_filter
// Action: Update a filter
// Call: update_filter(int num, int execorder, string filtername, int field, string fieldvalue, int action, int active, string destination)
//
function update_filter ($num, $execorder, $filtername, $field, $fieldvalue, $action, $destination, $active, $comment){

	$sql_query = "UPDATE filter
	SET exec_order='$execorder', filtername='$filtername', filter_field_id='$field', fieldvalue='$fieldvalue', filteraction_id='$action',
    destination='$destination', active='$active', comment='$comment', modified=NOW()
	WHERE id=$num";

	$result = db_query ($sql_query);
	return $result;

}

// update_email_filter_status
// Action: Update the field update_filter for a mailbox
// Call: update_filter(string username,)
//
function update_email_filter_status ($username){

	$sql_query = "UPDATE mailbox
	SET update_filter='1'
	WHERE username='$username'";

	$result = db_query ($sql_query);
	return $result;

}


// update_mailbox_password
// Action: Update the password for a mailbox
// Call: update_mailbox_password(string username,string password)
//
function update_mailbox_password ($username, $password){

	$password_crypted = pacrypt ($password);

	$result = db_query ("UPDATE mailbox SET password='$password',modified=NOW() WHERE username='$username'");

	return $result;

}

// update_admin_password
// Action: Update the password for a admin
// Call: update_admin_password(string username,string password)
//
function update_admin_password ($username, $password){

	$password_crypted = pacrypt ($password);

	$result = db_query ("UPDATE accounts SET password='$password_crypted',modified=NOW() WHERE username='$username'");

	return $result;

}


//
// get_field_filter
// Action: Get all field filter available
// Call: get_field_filter ()
//
function get_field_filter ()
{
	global $CONF;
	$list = array();

	$sql_query = "SELECT *
                FROM filter_field
                order by fieldname"; 

	$result = db_query ($sql_query);
	if ( $result['rows'] > 0 ){

		while ($row = db_array($result['result'])) {
			$list[] = $row;
		}

	}

	return $list;

}

//
// get_bl_host
// Action: Get all host blacklisted
// Call: get_bl_host
//
function get_bl_host ()
{
	global $CONF;
	$list = array();

	$sql_query = "SELECT *
                FROM blacklist
                order by _blacklist"; 

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){

		while ($row = db_array($result['result'])) {
			$list[] = $row;
		}

	}

	return $list;

}



//
// get_wl_host
// Action: Get all host whitelisted
// Call: get_wl_host
//
function get_wl_host ()
{
	global $CONF;
	$list = array();

	$sql_query = "SELECT *
                FROM whitelist
                order by _whitelist"; 

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){

		while ($row = db_array($result['result'])) {
			$list[] = $row;
		}

	}

	return $list;

}

//
// del_wl_host
// Action: delete a whitelisted host
// Call: del_wl_host (string  ip)
//
function del_wl_host ($ip)
{
	$sql_query = "DELETE FROM whitelist
                where _whitelist='$ip'"; 
	global $SESSID_USERNAME;

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){
		db_log ($SESSID_USERNAME, 'policyd', "remove ip from whitelist", "Delete $ip from whitelist");
		return "ok";
	}
	else{
		return "ko";
	}

}

//
// del_bl_host
// Action: delete a whitelisted host
// Call: del_bl_host (string  ip)
//
function del_bl_host ($ip)
{
	$sql_query = "DELETE FROM blacklist
                where _blacklist='$ip'"; 
	global $SESSID_USERNAME;

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){
		db_log ($SESSID_USERNAME, 'policyd', "remove ip from blacklist", "Delete $ip from blacklist");
		return "ok";
	}
	else{
		return "ko";
	}
}


//
// add_bl_host
// Action: add a host in the blacklist table
// Call: add_bl_host (string ip, string desc, int expire)
//
function add_bl_host ($fIp, $fDesc, $fExp)
{
	$sql_query = "INSERT INTO blacklist (_blacklist, _description, _expire)
                VALUES ('$fIp', '$fDesc', '$fExp')"; 
	global $SESSID_USERNAME;

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){
		db_log ($SESSID_USERNAME, 'policyd', "add host into blacklist", "add $fIp into blacklist, $fDesc, expires : $fExp");
		return "ok";
	}
	else{
		return "ko";
	}
}


//
// add_bl_sender
// Action: add a email in the blacklist table
// Call: add_bl_sender (string email, string desc, int expire)
//
function add_bl_sender ($fSender, $fDesc, $fExp)
{
	$sql_query = "INSERT INTO blacklist_sender (_blacklist, _description, _expire)
                VALUES ('$fSender', '$fDesc', '$fExp')"; 
	global $SESSID_USERNAME;

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){
		db_log ($SESSID_USERNAME, 'policyd', "add email into blacklist", "add $fSender into blacklist, $fDesc, expires : $fExp");
		return "ok";
	}
	else{
		return "ko";
	}
}


//
// del_bl_sender
// Action: remove a email from the blacklist table
// Call: del_bl_sender (string email)
//
function del_bl_sender ($fEmail)
{
	$sql_query = "DELETE FROM blacklist_sender WHERE _blacklist = '$fEmail'"; 
	global $SESSID_USERNAME;

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){
		db_log ($SESSID_USERNAME, 'policyd', "remove email from blacklisted", "Delete $fEmail from blacklist");
		return "ok";
	}
	else{
		return "ko";
	}
}

//
// add_wl_host
// Action: add a host in the whitelist table
// Call: add_wl_host (string ip, string desc, int expire)
//
function add_wl_host ($fIp, $fDesc, $fExp)
{
	$sql_query = "INSERT INTO whitelist (_whitelist, _description, _expire)
                VALUES ('$fIp', '$fDesc', '$fExp')"; 
	global $SESSID_USERNAME;

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){
		db_log ($SESSID_USERNAME, 'policyd', "add host into whitelist", "add $fIp into whitelist, $fDesc, expires : $fExp");
		return "ok";
	}
	else{
		return "ko";
	}
}


//
// add_bl_helo
// Action: add a helo 
// Call: add_bl_helo (string helo, string domain)
//
function add_bl_helo ($helo, $domain)
{
	$sql_query = "INSERT INTO blacklist_helo (_helo)
                VALUES ('$helo.$domain')"; 
	global $SESSID_USERNAME;

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){
		db_log ($SESSID_USERNAME, "$domain", "policyd", "add helo $helo into blacklist");
		return TRUE;
	}
	else{
		return FALSE;
	}
}

//
// del_bl_helo
// Action: delete a helo 
// Call: del_bl_helo (string helo)
//
function del_bl_helo ($helo)
{

	$sql_query = "DELETE FROM blacklist_helo
                where _helo='$helo'"; 
	global $SESSID_USERNAME;

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){
		db_log ($SESSID_USERNAME, 'ova.local', "policyd", "remove helo $helo from blacklist");
		return TRUE;
	}
	else{
		return FALSE;
	}
}


//
// get_bl_helo
// Action: Get all helo forbidden
// Call: get_bl_helo
//
function get_bl_helo ()
{
	global $CONF;
	$list = array();

	$sql_query = "SELECT *
                FROM blacklist_helo
                order by _helo"; 

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){

		while ($row = db_array($result['result'])) {
			$list[] = $row;
		}

	}

	return $list;

}


//
// get_bl_sender
// Action: Get all sender blacklisted
// Call: get_bl_sender
//
function get_bl_sender ()
{
	global $CONF;
	$list = array();

	$sql_query = "SELECT *
                FROM blacklist_sender
                order by _blacklist"; 

	$result = db_query ($sql_query,"1","policyd");
	if ( $result['rows'] > 0 ){

		while ($row = db_array($result['result'])) {
			$list[] = $row;
		}

	}

	return $list;

}


//
// get_field_action
// Action: Get all action filter available
// Call: get_field_action ()
//
function get_field_action ()
{
	global $CONF;
	$list = array();

	$sql_query = "SELECT *
                FROM filteraction_field
                order by actionname"; 

	$result = db_query ($sql_query);
	if ( $result['rows'] > 0 ){

		while ($row = db_array($result['result'])) {
			$list[] = $row;
		}

	}

	return $list;

}

// convert_number_size
// Action: Convert a number in Mbytes or GigaBytes...
// Call: convert_number_size(int number)
function convert_number_size($number){

	GLOBAL $PALANG;
	$list = "";

	if ( $number < 1000 ){
		$list['num'] = $number;
		$list['unit'] = $PALANG['pBytes'];
	}
	elseif ( ($number < 1000000) && ($number >= 1000 ) ){
		$number2 = $number / 1024;
		$list['num'] = $number2;
		$list['unit'] = $PALANG['pBytes_kilo'];
	}
	elseif ( ($number < 1000000000) &&  ($number >= 1000000) ){
		$number2 = $number / 1024 / 1024;
		$list['num'] = round($number2,0);
		$list['unit'] = $PALANG['pBytes_mega'];
	}
	elseif ( ($number < 1000000000000) && ($number >= 1000000000) ){
		$number2 = $number / 1024 / 1024 / 1024;
		$list['num'] = round($number2,0);
		$list['unit'] = $PALANG['pBytes_giga'];
	}
	elseif ( ($number < 1000000000000000) && ($number >= 1000000000000) ){
		$number2 = $number / 1024 / 1024 / 1024 / 1024;
		$list['num'] = round($number2,0);
		$list['unit'] = $PALANG['pBytes_tera'];
	}

	return $list;

}

// convert_number_size
// Action: Convert a number in Mbytes or GigaBytes...
// Call: convert_number_size(int number)
function convert_number_size_string($number){

	GLOBAL $PALANG;
	$list = convert_number_size($number);
	return round($list['num'],0)." ".$list['unit'];

}

 
//
// get_domain_properties
// Action: Get all the properties of a domain.
// Call: get_domain_properties (string domain)
//
function get_domain_properties ($domain)
{
   global $CONF;
   $list = "";

   $sql_query = "SELECT COUNT(*)
FROM alias, policy, domain
WHERE domain.domain='$domain'
AND domain.id=policy.domain_id
AND policy.id=alias.policy_id";

   $result = db_query ($sql_query);
   $row = db_row ($result['result']);
   $list['alias_count'] = $row[0];
   
	 $sql_query = "SELECT COUNT(*)
FROM mailbox,domain
WHERE domain.domain='$domain'
AND domain.id=mailbox.domain_id";

   $result = db_query ($sql_query);
   $row = db_row ($result['result']);
   $list['mailbox_count'] = $row[0];
   $list['alias_count'] = $list['alias_count'] - $list['mailbox_count'];

	 $sql_query = "SELECT COUNT(*)
FROM ftpaccount, domain
WHERE domain.domain='$domain'
AND domain.id=ftpaccount.domain_id";
	 
   $result = db_query ($sql_query);
   $row = db_row ($result['result']);
   $list['ftp_count'] = $row[0];

	 $sql_query = "SELECT COUNT(*)
FROM whost,domain
WHERE domain.domain='$domain'
AND domain.id=whost.domain_id";

   $result = db_query ($sql_query);
   $row = db_row ($result['result']);
   $list['web_count'] = $row[0];

	 $sql_query = "SELECT COUNT(*)
FROM dbname, domain
WHERE domain.domain='$domain'
AND dbname.domain_id=domain.id";

   $result = db_query ($sql_query);
   $row = db_row ($result['result']);
   $list['dbused_count'] = $row[0];

	 $sql_query = "SELECT policy.id
FROM policy,domain
WHERE domain.domain='$domain'
AND domain.id=policy.domain_id";

   $result = db_query ($sql_query);
   $row = db_row ($result['result']);
   $list['policy_id'] = $row[0];

   $result = db_query ("SELECT * FROM domain WHERE domain='$domain'");
   $row = db_array ($result['result']);

	 reset($row);
	while (list($key, $value) = each($row)) {
		if ($CONF['database_type'] == "pgsql") {
			if ($value=="t") $value=="1";
			if ($value=="f") $value=="0";
		}
		$list[$key] = $value;
	}


   return $list;
}


//
// get_domain_alias_properties
// Action: Get all the properties of a domain alias
// Call: get_domain_alias_properties (string domain)
//
function get_domain_alias_properties ($domain)
{
  $list = array();
  
  $result = db_query ("SELECT domain_alias.*, domain.domain FROM domain_alias,domain WHERE dalias='$domain' AND domain_alias.domain_id=domain.id");
  $row = db_array ($result['result']);
	//	$list[] = $row;

  return $row;
}

//
// check_alias
// Action: Checks if the domain is still able to create aliases.
// Call: check_alias (string domain)
//
function check_alias ($domain)
{
   $limit = get_domain_properties ($domain);
   if ($limit['aliases'] >= 0)
   {
      if ($limit['alias_count'] >= $limit['aliases'])
      {
         return false;
      }
   }
   return true;
}

//
// check_mailbox
// Action: Checks if the domain is still able to create mailboxes.
// Call: ceck_mailbox (string domain)
//
function check_mailbox ($domain)
{
   $limit = get_domain_properties ($domain);
   if ($limit['mailboxes'] >= 0)
   {
      if ($limit['mailbox_count'] >= $limit['mailboxes'])
      {
         return false;
      }
   }
   return true;
}

//
// check_quota
// Action: Checks if the user is creating a mailbox with the correct quota
// Call: check_quota (string domain)
//
function check_quota ($quota, $domain)
{
   if ($quota < -1 || ( $quota > -1 && $quota < 0 ) )
   {
      return false;
   }

   $limit = get_domain_properties ($domain);
   if ($limit['maxquota'] >= 0)
   {
      if ($quota > $limit['maxquota'])
      {
         return false;
      }
      if (($quota == 0) && ($limit['maxquota'] != 0))
      {
         return false;
      }
   }
   return true;
}


//
// is_enduser_mailbox_account
// Action: Checks if the user is a mailbox account
// Call: is_enduser_mailbox_account (string username)
//
function is_enduser_mailbox_account ($username)
{

	$result = db_query("SELECT username FROM mailbox WHERE username='".$username."'");
	if ($result['rows'] == 1){
		return true;
	}
	else{
		return false;
	}
}




//
// check_quota_user
// Action: Checks the mailbox quota used by the user
// Call: check_quota_user ( string user)
//
function check_quota_user ($username)
{
	$result = db_query("SELECT stats_mailbox.size FROM stats_mailbox,mailbox WHERE mailbox.username='".$username."' AND mailbox.id=stats_mailbox.mailbox_id ORDER BY date DESC LIMIT 1 ");
	if ($result['rows'] == 1){
		$row = db_array ($result['result']);
		$mailbox_size = $row['size'];
	}
	else{
		$mailbox_size = 0;
	}
	return $mailbox_size;

}



//
// check_overquota_user
// Action: Checks if the user account is overquota
// Call: check_overquota_user ( string user)
//
function check_overquota_user ($username)
{
	$result = db_query("SELECT * FROM overquota WHERE user='".$username."'");
	if ($result['rows'] == 1){
		$row = db_array ($result['result']);
		$date = $row['createdate'];
	}
	else{
		$date = NULL;
	}
	return $date;

}


//
// total_quota_mailbox_domain
// Action: Get the sum of all domain mailbox disk space used
// Call: total_quota_mailbox_domain ( string domain)
// TODO : Remove all direct call to be change by domain object call
//
function total_quota_mailbox_domain ($domain)
{
	$sql_query = "SELECT sum(stats_mailbox.size) as size
FROM stats_mailbox, mailbox, domain
WHERE domain.domain='$domain'
AND domain.id=mailbox.domain_id
AND mailbox.id=stats_mailbox.mailbox_id
GROUP BY date
ORDER BY date DESC
LIMIT 1";

	$result = db_query($sql_query);
	if ($result['rows'] == 1){
		$row = db_array ($result['result']);
		$domain_mailbox_size = $row['size'];
	}
	else{
		$domain_mailbox_size = 0;
	}
	return $domain_mailbox_size;

}

//
// get_domain_mailbox
// Action: Get the whole list of mailbox in a domain
// Call: get_domain_mailbox ( string domain)
//
function get_domain_mailbox ($domain)
{

	$list=array();

	$result = db_query("SELECT mailbox.username FROM mailbox, domain WHERE domain.domain='".$domain."' AND domain.id=mailbox.domain_id ORDER BY mailbox.username");
	if ($result['rows'] > 0)
		{
			while ($row = db_array ($result['result']))
        {
					$list[] = $row['username'];
        }
		}
	return $list;
}

//
// get_domain_aliases
// Action: Get the whole list of aliases in a domain without mailbox
// Call: get_domain_aliases ( string domain)
//
function get_domain_aliases ($domain)
{

// print "

// SELECT alias.address
// FROM alias
// LEFT JOIN mailbox
// ON mailbox.username=alias.address
// WHERE mailbox.username IS NULL
// AND alias.domain='fox-informatique.com'
// AND alias.address not like '@%'

// ";

	$list = array();

	$sql_query = "SELECT alias.address
FROM domain, policy, alias
LEFT JOIN mailbox
ON mailbox.username=alias.address
WHERE mailbox.username IS NULL
AND domain.domain='".$domain."'
AND domain.id=policy.domain_id
AND policy.id=alias.policy_id
AND alias.address not like '@%'
";


	$result = db_query($sql_query);
	if ($result['rows'] > 0)
		{
			while ($row = db_array ($result['result']))
        {
					$list[] = $row['address'];
        }
		}
	return $list;
}


//
// get_domain_mail_log
// Action: Get the list of email that the user receive from a contact
// Call: get_domain_mail_log ( string user, string contact, date begindate, date enddate)
//
function get_domain_mail_log ($user, $contact = NULL, $begindate, $enddate)
{
	$query = "
SELECT maddr.email as to, msgs.subject as subject,  msgs.spam_level as spamlevel , msgs.time_iso as date, maddr_sender.email as from, msgs.content as content
FROM msgrcpt, maddr, msgs, maddr as maddr_sender
WHERE maddr.email like '%$user%'
AND msgrcpt.rid = maddr.id
AND msgrcpt.mail_id = msgs.mail_id
AND msgs.time_iso  BETWEEN '$begindate' AND '$enddate' 
AND msgs.content = 'S'
AND msgs.sid = maddr_sender.id
";

	if ( $contact != NULL ){
		$query .= "AND maddr_sender.email like '%$contact%'";
	}

	$result = db_query($query);
	if ($result['rows'] > 0)
		{
			$row = db_array ($result['result']);
		}
	return $row;
}

//
// check_owner
// Action: Checks if the admin is the owner of the domain.
// Call: check_owner (string admin, string domain)
//
function check_owner ($username, $domain)
{

	$sql_query = "SELECT DISTINCT username
FROM domain_admins, accounts, domain
WHERE accounts.username='$username'
AND accounts.id=domain_admins.accounts_id
AND domain_admins.domain_id=domain.id
AND domain.domain = '$domain'
AND domain.active='1'";
	
   $result = db_query ($sql_query);
   if ($result['rows'] != 1)
   {
      return check_admin($username);
   }
   else
   {
      return true;
   }
}

//
// list_domains_for_admin
// Action: Lists all the domains for an admin.
// Call: list_domains_for_admin (string admin)
//
function list_domains_for_admin ($username)
{
   $list = "";
   if(check_admin($username)) {
     $list = list_domains();
   } else {

		 $account_id = get_account_id($username);

		 $sql_query = "SELECT domain.*
FROM domain, domain_admins
WHERE domain_admins.accounts_id='$account_id'
AND domain_admins.domain_id=domain.id
AND domain.active='1'
AND domain!='ova.local'
ORDER BY domain";

     $result = db_query ($sql_query);
     if ($result['rows'] > 0)
     {
        $i = 0;
        while ($row = db_array ($result['result']))
        {
           $list[$i] = $row['domain'];
           $i++;
        }
     }
   }
   return $list;
}

//
// list_local_domains_for_admin
// Action: Lists all the domains for an admin that can deliver in local.
// Call: list_local_domains_for_admin (string admin)
//
function list_local_domains_for_admin ($username)
{
   $list = "";

   if(check_admin($username)) {
     $list = list_domains_local();
   } else {
		 $username_id = get_account_id($username);

		 $sql_query = "SELECT domain.domain
FROM domain, domain_admins
WHERE domain_admins.accounts_id='$username_id'
AND domain_admins.domain_id=domain.id
AND domain.active='1'
AND domain.backupmx='0'
ORDER BY domain.domain";

     $result = db_query ($sql_query);
     if ($result['rows'] > 0)
     {
        $i = 0;
        while ($row = db_array ($result['result']))
        {
           $list[$i] = $row['domain'];
           $i++;
        }
     }
   }
   return $list;
}

//
// list_domains_alias
// Action: List all available domains aliases.
// Call: list_domains_alias ( string ORDER)
//
function list_domains_alias ($ORDER="dalias")
{
	$list = array();
  
   $result = db_query ("SELECT id, dalias, domain_id FROM domain_alias ORDER BY $ORDER");
   if ($result['rows'] > 0)
   {
      while ($row = db_array ($result['result']))
      {
         $list[] = $row;
      }
   }
   return $list;
}

//
// list_domains_alias_for_admin ($username)
// Action: Lists all available domains aliases for an admin
// Call: list_domains_alias_for_admin (string admin)
//
function list_domains_alias_for_admin ($username)
{
  $list = "";
  
  if(check_admin($username)) {
    $list = list_domains_alias();
  }
  else 
  {

		$sql_query = "SELECT domain_alias.dalias, domain_alias.domain_id
FROM domain_alias, domain_admins, accounts
WHERE accounts.username = '$username'
AND accounts.id=domain_admins.accounts_id
AND domain_admins.domain_id=domain_alias.domain_id
ORDER BY domain_alias.dalias";

    $result = db_query($sql_query);
    if ($result['rows'] > 0 )
    {
      $i = 0;
      while ($row = db_array ($result['result']))
      {
        $list[$i] = $row;
        $i++;
      }
    }
  }
  return $list;
}


//
// list_domains
// Action: List all available domains.
// Call: list_domains ()
//
function list_domains ()
{
   $list = "";
   
   $result = db_query ("SELECT domain FROM domain WHERE domain != 'ova.local' ORDER BY domain");
   if ($result['rows'] > 0)
   {
      $i = 0;
      while ($row = db_array ($result['result']))
      {
         $list[$i] = $row['domain'];
         $i++;
      }
   }
   return $list;
}

//
// list_domains_local
// Actions: Lists all available domains that is localy accepted (eg not backup MX)
// 
function list_domains_local ()
{
  $list = "";
 
  $result = db_query ("SELECT id, domain FROM domain WHERE backupmx = 0 ORDER BY domain");
  if ($result['rows'] > 0)
  {
    $i = 0;
    while ($row = db_array ($result['result']))
    {
      $list[$i] = $row;
      $i++;
    }
  }
  return $list;
}

//
// admin_exist
// Action: Checks if the admin already exists.
// Call: admin_exist (string admin)
//
function admin_exist ($username)
{
   $result = db_query ("SELECT username FROM admin WHERE username='$username'");
   if ($result['rows'] != 1)
   {
      return false;
   }
   else
   {
      return true;
   }
}

//
// domain_exist
// Action: Checks if the domain already exists.
// Call: domain_exist (string domain)
//
function domain_exist ($domain)
{
	$result = db_query ("SELECT domain FROM domain WHERE domain='$domain'");
   if ($result['rows'] != 1)
   {
      return false;
   }
   else
   {
      return true;
   }
}

//
// list_admins
// Action: Lists all the admins
// Call: list_admins ()
//
function list_admins ()
{
   $list = "";
   
   $result = db_query ("SELECT username FROM accounts ORDER BY username");
   if ($result['rows'] > 0)
   {
      $i = 0;
      while ($row = db_array ($result['result']))
      {
         $list[$i] = $row['username'];
         $i++;
      }
   }
   return $list;
}

//
// get_admin_properties
// Action: Get all the admin properties.
// Call: get_admin_properties (string admin)
function get_admin_properties ($username)
{
   $list = "";
   
   $result = db_query ("SELECT COUNT(*) FROM domain_admins WHERE username='$username'");
   $row = db_row ($result['result']);
   $list['domain_count'] = $row[0];
   
   $result = db_query ("SELECT * FROM admin WHERE username='$username'");
   $row = db_array ($result['result']);
   $list['created'] = $row['created'];
   $list['modified'] = $row['modified'];
   $list['active'] = $row['active'];

   return $list;
}

// 
// encode_header
// Action: Encode a string according to RFC 1522 for use in headers if it contains 8-bit characters.
// Call: encode_header (string header, string charset)
//
function encode_header ($string, $default_charset) 
{
   if (strtolower ($default_charset) == 'iso-8859-1')
   {
      $string = str_replace ("\240",' ',$string);
   }

   $j = strlen ($string);
   $max_l = 75 - strlen ($default_charset) - 7;
   $aRet = array ();
   $ret = '';
   $iEncStart = $enc_init = false;
   $cur_l = $iOffset = 0;

   for ($i = 0; $i < $j; ++$i)
   {
      switch ($string{$i})
      {
         case '=':
         case '<':
         case '>':
         case ',':
         case '?':
         case '_':
         if ($iEncStart === false)
         {
            $iEncStart = $i;
         }
         $cur_l+=3;
         if ($cur_l > ($max_l-2))
         {
            $aRet[] = substr ($string,$iOffset,$iEncStart-$iOffset);
            $aRet[] = "=?$default_charset?Q?$ret?=";
            $iOffset = $i;
            $cur_l = 0;
            $ret = '';
            $iEncStart = false;
         }
         else
         {
            $ret .= sprintf ("=%02X",ord($string{$i}));
         }
         break;
         case '(':
         case ')':
         if ($iEncStart !== false)
         {
            $aRet[] = substr ($string,$iOffset,$iEncStart-$iOffset);
            $aRet[] = "=?$default_charset?Q?$ret?=";
            $iOffset = $i;
            $cur_l = 0;
            $ret = '';
            $iEncStart = false;
         }
         break;
         case ' ':
         if ($iEncStart !== false)
         {
            $cur_l++;
            if ($cur_l > $max_l)
            {
               $aRet[] = substr ($string,$iOffset,$iEncStart-$iOffset);
               $aRet[] = "=?$default_charset?Q?$ret?=";
               $iOffset = $i;
               $cur_l = 0;
               $ret = '';
               $iEncStart = false;
            }
            else
            {
               $ret .= '_';
            }
         }
         break;
         default:
         $k = ord ($string{$i});
         if ($k > 126)
         {
            if ($iEncStart === false)
            {
               // do not start encoding in the middle of a string, also take the rest of the word.
               $sLeadString = substr ($string,0,$i);
               $aLeadString = explode (' ',$sLeadString);
               $sToBeEncoded = array_pop ($aLeadString);                  
               $iEncStart = $i - strlen ($sToBeEncoded);
               $ret .= $sToBeEncoded;
               $cur_l += strlen ($sToBeEncoded);
            }
            $cur_l += 3;
            // first we add the encoded string that reached it's max size
            if ($cur_l > ($max_l-2))
            {
               $aRet[] = substr ($string,$iOffset,$iEncStart-$iOffset);
               $aRet[] = "=?$default_charset?Q?$ret?= ";
               $cur_l = 3;
               $ret = '';
               $iOffset = $i;
               $iEncStart = $i;
            }
            $enc_init = true;
            $ret .= sprintf ("=%02X", $k);
            }
            else
            {
            if ($iEncStart !== false)
            {
               $cur_l++;
               if ($cur_l > $max_l)
               {
                  $aRet[] = substr ($string,$iOffset,$iEncStart-$iOffset);
                  $aRet[] = "=?$default_charset?Q?$ret?=";
                  $iEncStart = false;
                  $iOffset = $i;
                  $cur_l = 0;
                  $ret = '';
                  }
                  else
                  {
                     $ret .= $string{$i};
                  }
               }
            }
            break;
         }
      }
      if ($enc_init)
      {
         if ($iEncStart !== false)
         {
            $aRet[] = substr ($string,$iOffset,$iEncStart-$iOffset);
            $aRet[] = "=?$default_charset?Q?$ret?=";
         }
         else
         {
            $aRet[] = substr ($string,$iOffset);
         }
         $string = implode ('',$aRet);
      }
   return $string;
}

//
// generate_password
// Action: Generates a random password
// Call: generate_password ()
//
function generate_password ()
{
   $password = substr (md5 (mt_rand ()), 0, 8);
   return $password;
}

//
// pacrypt
// Action: Encrypts password based on config settings
// Call: pacrypt (string cleartextpassword)
//
function pacrypt ($pw, $pw_db="")
{
   global $CONF;
   $password = "";
   $salt = "";

   if ($CONF['encrypt'] == 'md5crypt')
   {
      $split_salt = preg_split ('/\$/', $pw_db);
      if (isset ($split_salt[2])) $salt = $split_salt[2];

      $password = md5crypt ($pw, $salt);
   }

   if ($CONF['encrypt'] == 'md5')
   {
      $password = md5($pw);
   }

   if ($CONF['encrypt'] == 'system')
   {
      if (ereg ("\$1\$", $pw_db))
      {
         $split_salt = preg_split ('/\$/', $pw_db);
         $salt = $split_salt[2];
      }
      else
      {
         $salt = substr ($pw_db, 0, 2);
      }
      $password = crypt ($pw, $salt);
   }

   if (($CONF['encrypt'] == 'cleartext') || ($CONF['encrypt'] == 'clear'))
   {
      $password = $pw;
   }

   return $password;
}

//
// md5crypt
// Action: Creates MD5 encrypted password
// Call: md5crypt (string cleartextpassword)
//
$MAGIC = "$1$";
$ITOA64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

function md5crypt ($pw, $salt="", $magic="")
{
   global $MAGIC;

   if ($magic == "") $magic = $MAGIC;
   if ($salt == "") $salt = create_salt (); 
   $slist = explode ("$", $salt);
   if ($slist[0] == "1") $salt = $slist[1];

   $salt = substr ($salt, 0, 8);
   $ctx = $pw . $magic . $salt;
   $final = hex2bin (md5 ($pw . $salt . $pw));

   for ($i=strlen ($pw); $i>0; $i-=16)
   {
      if ($i > 16)
      {
         $ctx .= substr ($final,0,16);
      }
      else
      {
         $ctx .= substr ($final,0,$i);
      }
   }
   $i = strlen ($pw);
   
   while ($i > 0)
   {
      if ($i & 1) $ctx .= chr (0);
      else $ctx .= $pw[0];
      $i = $i >> 1;
   }
   $final = hex2bin (md5 ($ctx));

   for ($i=0;$i<1000;$i++)
   {
      $ctx1 = "";
      if ($i & 1)
      {
         $ctx1 .= $pw;
      }
      else
      {
         $ctx1 .= substr ($final,0,16);
      }
      if ($i % 3) $ctx1 .= $salt;
      if ($i % 7) $ctx1 .= $pw;
      if ($i & 1)
      {
         $ctx1 .= substr ($final,0,16);
      }
      else
      {
         $ctx1 .= $pw;
      }
      $final = hex2bin (md5 ($ctx1));
   }
   $passwd = "";
   $passwd .= to64 (((ord ($final[0]) << 16) | (ord ($final[6]) << 8) | (ord ($final[12]))), 4);
   $passwd .= to64 (((ord ($final[1]) << 16) | (ord ($final[7]) << 8) | (ord ($final[13]))), 4);
   $passwd .= to64 (((ord ($final[2]) << 16) | (ord ($final[8]) << 8) | (ord ($final[14]))), 4);
   $passwd .= to64 (((ord ($final[3]) << 16) | (ord ($final[9]) << 8) | (ord ($final[15]))), 4);
   $passwd .= to64 (((ord ($final[4]) << 16) | (ord ($final[10]) << 8) | (ord ($final[5]))), 4);
   $passwd .= to64 (ord ($final[11]), 2);

   return "$magic$salt\$$passwd";
}

function create_salt ()
{
   srand ((double) microtime ()*1000000);
   $salt = substr (md5 (rand (0,9999999)), 0, 8);
   return $salt;
}

function hex2bin ($str)
{
   $len = strlen ($str);
   $nstr = "";
   for ($i=0;$i<$len;$i+=2)
   {
      $num = sscanf (substr ($str,$i,2), "%x");
      $nstr.=chr ($num[0]);
   }
   return $nstr;
}

function to64 ($v, $n)
{
   global $ITOA64;
   $ret = "";
   while (($n - 1) >= 0)
   {
      $n--;
      $ret .= $ITOA64[$v & 0x3f];
      $v = $v >> 6;
   }
   return $ret;
}

$DEBUG_TEXT = "\n
<p />\n
Please check the documentation and website for more information.\n
<p />\n
<a href=\"http://www.oav.net/projects/openvisp-admin/\">OpenVISP Admin</a>\n
";

$DEBUG_TEXT_TXT = "
Please check the documentation and website for more information.
<a href=\"http://www.oav.net/projects/openvisp-admin/\">OpenVISP Admin</a>
";

//
// db_connect
// Action: Makes a connection to the database if it doesn't exist
// Call: db_connect (string database_name)
//
function db_connect ($database, $DB_TYPE, $DB_HOST, $DB_USER, $DB_PASS, $DB_PORT=3306, $DB_NAME)
{
	//   global $CONF;
   global $DEBUG_TEXT;
   $link = "";

/* 	 if ( $database == "openvisp" ){ */
/* 		 $DB_HOST = $CONF['database_host']; */
/* 		 $DB_USER = $CONF['database_user']; */
/* 		 $DB_PASS = $CONF['database_password']; */
/* 		 $DB_NAME = $CONF['database_name']; */
/* 		 $DB_TYPE = $CONF['database_type']; */
/* 	 } */
/* 	 elseif ( $database == "policyd" ){ */
/* 		 $DB_TYPE = $CONF['policy_db_type']; */
/* 		 $DB_HOST = $CONF['policy_db_host']; */
/* 		 $DB_USER = $CONF['policy_db_user']; */
/* 		 $DB_PASS = $CONF['policy_db_password']; */
/* 		 $DB_NAME = $CONF['policy_db_name']; */
/* 	 } */
/* 	 elseif ( $database == "mysqld" ){ */
/* 		 $DB_TYPE = $CONF['mysql_db_type']; */
/* 		 $DB_HOST = $CONF['mysql_db_host']; */
/* 		 $DB_USER = $CONF['mysql_db_user']; */
/* 		 $DB_PASS = $CONF['mysql_db_password']; */
/* 		 $DB_NAME = $CONF['mysql_db_name']; */
/* 	 } */


   if ($DB_TYPE == "mysql")
		 {
			 $link = @mysql_connect ($DB_HOST.':'.$DB_PORT, $DB_USER, $DB_PASS) or die ("<p />DEBUG INFORMATION:<br />Connect: " .  mysql_error () . "$DEBUG_TEXT" );
			 $succes = @mysql_select_db ($DB_NAME, $link) or die ("<p />DEBUG INFORMATION:<br />MySQL Select Database: " .  mysql_error () . "$DEBUG_TEXT");
		 }

   if ($DB_TYPE == "mysqli")
		 {
			 $link = @mysqli_connect ($DB_HOST.':'.$DB_PORT, $DB_USER, $DB_PASS) or die ("<p />DEBUG INFORMATION:<br />Connect: " .  mysqli_connect_error () . "$DEBUG_TEXT");
			 $succes = @mysqli_select_db ($link, $DB_NAME) or die ("<p />DEBUG INFORMATION:<br />MySQLi Select Database: " .  mysqli_error ($link) . "$DEBUG_TEXT");
		 }

   if ($DB_TYPE == "pgsql")
   {
      $connect_string = "host=" . $DB_HOST . " dbname=" . $DB_NAME . " user=" . $DB_USER . " password=" . $DB_PASS;
      $link = @pg_connect ($connect_string) or die ("<p />DEBUG INFORMATION:<br />Connect: " .  pg_last_error () . "$DEBUG_TEXT");
   }


   if ($link)
   {
      return $link;
   }
   else
   {
      print "DEBUG INFORMATION:<br />\n";
      print "Connect: Unable to connect to database<br />\n";
      print "<br />\n";
      print "Make sure that you have set the correct database type in the config.inc.php file<br />\n";
      print $DEBUG_TEXT;
      die;
   }
}

//
// db_query
// Action: Sends a query to the database and returns query result and number of rows
// Call: db_query (string query, int die, string database_name)
// int die should have
//    0= don't die on error 
//    1= die on error and send debug test (DEFAULT)
//    2= don't die, but throw error
//
// FIXME: UGLY code -> have to fix this using pear-MDB2
function db_query ($query, $die_on_error=1, $database="", $db_type="", $db_host="", $db_user="", $db_pass="", $db_port="")
{
   global $CONF;
   global $DEBUG_TEXT;
   $result = "";
   $number_rows = "";
   $inserted_id = "";

	 if ( $CONF['SQL_DEBUG'] == "YES" ) { 	file_put_contents('php://stderr', "DEBUG OVA \n\n$query \n\n"); }

	 if ( $db_host == "" ) { $db_host = $CONF['database_host']; }
	 if ( $db_user == "" ) { $db_user = $CONF['database_user']; }
	 if ( $db_pass == "" ) { $db_pass = $CONF['database_password']; }
	 if ( $db_type == "" ) { $db_type = $CONF['database_type']; }
	 if ( $database == "" ) { $database = $CONF['database_name']; }

	 if ( $database == "" ){
		 $database = $CONF['database_name'];
		 $db_host = $CONF['database_host'];
		 $db_user = $CONF['database_user'];
		 $db_pass = $CONF['database_password'];
		 $db_type = $CONF['database_type'];
	 }


	 if ( $db_type == "" )
		 { $db_type = $CONF['database_type'] ; }

   $link = db_connect ($database, $db_type, $db_host, $db_user, $db_pass, $db_port, $database);

   if ($die_on_error == 1) {
     if ( $db_type == "mysql" ) 
     	$result = @mysql_query ($query, $link) or die ("<p />DEBUG INFORMATION:<br />Invalid query: " . mysql_error() . "<br/>Query <b>\"$query\"</b><br/>$DEBUG_TEXT" );
     if ( $db_type == "mysqli") 
     	$result = @mysqli_query ($link, $query) or die ("<p />DEBUG INFORMATION:<br />Invalid query: " . mysqli_error() . "<br/>Query <b>\"$query\"</b><br/>$DEBUG_TEXT");
     if ( $db_type == "pgsql") {
       if (eregi ("LIMIT", $query))
        {
         $search = "/LIMIT (\w+), (\w+)/";
         $replace = "LIMIT \$2 OFFSET \$1";
         $query = preg_replace ($search, $replace, $query);
        }
        $result = @pg_query ($link, $query) or die ("<p />DEBUG INFORMATION:<br />Invalid query: " . pg_last_error() . "$DEBUG_TEXT");
     }
   } elseif ($die_on_error == 2) {
     if ( $db_type == "mysql") {
     	$result = @mysql_query ($query, $link);
     	if ($result == FALSE) {
     		print "<b>SQL Query failed </b>: ".mysql_error() ."<br/>Query :<b>".$query."</b><br/>$DEBUG_TEXT";
     	}
     }
     if ($db_type == "mysqli") {
     	$result = @mysqli_query ($link, $query);
     	if ($result == FALSE) {
     		print "<b>SQL Query failed </b>: ".mysqli_error() ."<br/>Query :<b>".$query."</b><br/>$DEBUG_TEXT";
     	}
     }
     if ($db_type == "pgsql") {
       if (eregi ("LIMIT", $query))
        {
         $search = "/LIMIT (\w+), (\w+)/";
         $replace = "LIMIT \$2 OFFSET \$1";
         $query = preg_replace ($search, $replace, $query);
        }
        $result = @pg_query ($link, $query);
       	if ($result == FALSE) {
     		print "<b>SQL Query failed </b>: ".pg_last_error() ."<br/>Query :<b>".$query."</b><br/>$DEBUG_TEXT";
     	}
     }
   } else {
     if ( $db_type == "mysql") $result = @mysql_query ($query, $link);
     if ( $db_type == "mysqli") $result = @mysqli_query ($link, $query);
     if ( $db_type == "pgsql") {
       if (eregi ("LIMIT", $query))
        {
         $search = "/LIMIT (\w+), (\w+)/";
         $replace = "LIMIT \$2 OFFSET \$1";
         $query = preg_replace ($search, $replace, $query);
        }
         $result = @pg_query ($link, $query);
     }
   }
   
   if (eregi ("^select", $query))
   {
      // if $query was a select statement check the number of rows with mysql_num_rows ().
      if ($db_type == "mysql") $number_rows = mysql_num_rows ($result);
      if ($db_type == "mysqli") $number_rows = mysqli_num_rows ($result);      
      if ($db_type == "pgsql") $number_rows = pg_num_rows ($result);
   }
   else
   {
      // if $query was something else, UPDATE, DELETE or INSERT check the number of rows with
      // mysql_affected_rows ().
      if ( $db_type == "mysql") {
				$number_rows = mysql_affected_rows ($link);
				$inserted_id = mysql_insert_id ($link);
			}
      if ( $db_type == "mysqli") $number_rows = mysqli_affected_rows ($link);
      if ( $db_type == "pgsql") $number_rows = pg_affected_rows ($result);
			
   }

   if ($db_type == "mysql") mysql_close ($link);
   if ($db_type == "mysqli") mysqli_close ($link);
   if ($db_type == "pgsql") pg_close ($link);   


	 if ( $CONF['SQL_DEBUG'] == "YES" ) { 	file_put_contents('php://stderr', "DEBUG OVA ".$number_rows." result(s) \n"); }

   $return = array (
      "result" => $result,
      "rows" => $number_rows,
			"inserted_id" => $inserted_id
   );
   return $return;
}

// db_row
// Action: Returns a row from a table
// Call: db_row (int result)
//
function db_row ($result)
{
   global $CONF;
   $row = "";
   if ($CONF['database_type'] == "mysql") $row = mysql_fetch_row ($result);
   if ($CONF['database_type'] == "mysqli") $row = mysqli_fetch_row ($result);
   if ($CONF['database_type'] == "pgsql") $row = pg_fetch_row ($result);
   return $row;
}

// db_array
// Action: Returns a row from a table
// Call: db_array (int result)
//
function db_array ($result)
{
   global $CONF;
   $row = "";

   if ($CONF['database_type'] == "mysql") $row = mysql_fetch_array ($result);
   if ($CONF['database_type'] == "mysqli") $row = mysqli_fetch_array ($result);
   if ($CONF['database_type'] == "pgsql") $row = pg_fetch_array ($result);

   return $row;
}

// db_assoc
// Action: Returns a row from a table
// Call: db_assoc(int result)
//
function db_assoc ($result)
{
   global $CONF;
   $row = "";
   if ($CONF['database_type'] == "mysql") $row = mysql_fetch_assoc ($result);
   if ($CONF['database_type'] == "mysqli") $row = mysqli_fetch_assoc ($result);
   if ($CONF['database_type'] == "pgsql") $row = pg_fetch_assoc ($result);   
   return $row;
}

//
// db_delete
// Action: Deletes a row from a specified table
// Call: db_delete (string table, string where, string delete)
//
function db_delete ($table,$where,$delete)
{
   $result = db_query ("DELETE FROM $table WHERE $where='$delete'");
   if ($result['rows'] >= 1)
   {
      return $result['rows'];
   }
   else
   {
      return true;
   }
}

//
// db_log
// Action: Logs actions from admin
// Call: db_log (string username, string domain, string action, string data, string domain2)
//
function db_log ($username,$domain,$action,$data, $domain2="")
{
	global $CONF;
	global $SESSID_USERNAME;
	$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
	$username_id = get_admin_id($SESSID_USERNAME);
	$domain_id  =  get_domain_id($domain);

	if ($CONF['logging'] == 'YES')
		{

			$sql_query = "INSERT INTO log (accounts_id, domain_id, domain_name, ip, action, data) VALUES ('$username_id','$domain_id', '$domain2', '$REMOTE_ADDR','$action','$data')";
		 
      $result = db_query ($sql_query);
      if ($result['rows'] != 1)
				{
					return false;
				}
      else
				{
					return true;
				}
		}

}


// 
// crsf_key
// Action: create a key from php session to avoid CSRF problems
// call: crsf_key("page.php")
function crsf_key($page) 
{
  return md5crypt(session_id(), $page, "php");
}

//
// getrelativepath()
// Action: gets the relative path from path (for websites)
// call: getrelativepath(string path)
//
function getrelativepath($path)
{
    $npath = str_replace('\\', '/', $path);
    return str_replace($_SERVER['DOCUMENT_ROOT'], '', $npath);
}

//
// redirect_login()
// Action: redirect to login page and exit
// call: redirect_login();
//
function redirect_login()
{
	global $CONF;
	header ("Location: ".getabsoluteuri()."/login.php");
	exit;
}

//
// redirect_logout()
// Action: redirect to login page and exit
// call: redirect_login();
//
function redirect_logout()
{
   // $path = getrelativepath(dirname(__FILE__));   
   header ("Location: ".getabsoluteuri()."/logout.php");
   exit;
}

//
// getabsoluteuri()
// Action: get the absolute uri from session
// return: the absolute uri
//
function getabsoluteuri() {
  if (isset($_SESSION['absoluteuri'])) {
  	return $_SESSION['absoluteuri'];
  } else { 
  	// Usualy we should have an index.php that
	// redirect to the right file even if there is
	// several redirect. This is ugly, but it should work.
   	header ("Location: index.php");
  	print ("Session is expired. Please login again.\n");
	exit;
  }
}

// print_menu()
// Action: print a menu link with Absolute URI system
// Call: print_menu(string $uri,string $text)
// Returns: html text : <a target="_top" href=absoluteuri()+$uri>$text</a>
//
function print_menu($uri, $text) {
  print "<a target=\"_top\" href=\"".getabsoluteuri().$uri."\">".$text."</a>";
}

//
// print_dot()
// Action: return a &middot;
//
function print_dot() {
  print "&middot;";
}

//
// getcryptograph
// return: true if the config option is set 
//
function getcryptograph () {
   global $CONF;
   if ($CONF['cryptograph'] == 'YES') {
     return TRUE;
   } else {
     return FALSE;
   }
}


//
// modify_domain_mailbox
// Action: Used to block / unblock all mailbox account on a domain
// Call: modify_domain_mailbox (int domain_id, int action)
//
function modify_domain_mailbox($domain_id, $action)
{
	GLOBAL $PALANG;
	$result = db_query ("UPDATE mailbox SET paid='$action',modified=NOW() WHERE domain_id='$domain_id'");

	if ($result['rows'] >= 1){
		if ( $action == 0 ){
			return $PALANG['lock_domain_mailbox_account_successfull'];
		}
		else{
			return $PALANG['unlock_domain_mailbox_account_successfull'];
		}
	}
	else{
		if ( $action == 0 ){
			return $PALANG['lock_domain_mailbox_account_unsuccessfull'];
		}
		else{
			return $PALANG['unlock_domain_mailbox_account_unsuccessfull'];
		}
	}

}

//
// modify_domain_whost
// Action: Used to block / unblock all web hosting in a domain
// Call: modify_domain_whost(int domain_id, int action)
//
function modify_domain_whost($domain_id, $action)
{
	GLOBAL $PALANG;
	$result = db_query ("UPDATE whost SET paid='$action',modified=NOW() WHERE domain_id='$domain_id'");

	if ($result['rows'] >= 1){
		if ( $action == 0 ){
			return $PALANG['lock_domain_whost_successfull'];
		}
		else{
			return $PALANG['unlock_domain_whost_successfull'];
		}
	}
	else{
		if ( $action == 0 ){
			return $PALANG['lock_domain_whost_unsuccessfull'];
		}
		else{
			return $PALANG['unlock_domain_whost_unsuccessfull'];
		}
	}

}


//
// modify_domain_lock
// Action: Used to lock / unlock the domain
// Call: modify_domain_lock(int domain_id, int action)
//
function modify_domain_lock($domain_id, $action)
{
	GLOBAL $PALANG;
	$result = db_query ("UPDATE domain SET paid='$action',modified=NOW() WHERE id='$domain_id'");

	if ($result['rows'] >= 1){
		if ( $action == 0 ){
			return $PALANG['lock_domain_successfull'];
		}
		else{
			return $PALANG['unlock_domain_successfull'];
		}
	}
	else{
		if ( $action == 0 ){
			return $PALANG['lock_domain_unsuccessfull'];
		}
		else{
			return $PALANG['unlock_domain_unsuccessfull'];
		}
	}

}

//
// is_technical
// Action: Used to know if an admin has to see technical information of hosting
// Call: is_technical (string $username)
//
function is_technical ($username)
{

	$result = db_query ("SELECT tech FROM accounts WHERE username='$username'");
	$row = db_array($result['result']);
	return $row['tech'];

}

//
// is_in_vacation
// Action: Used to know if an email got an autoresponder
// Call: is_in_vacation (string $username)
//
function is_in_vacation ($username)
{

	$result = db_query ("SELECT vacation.* FROM vacation, mailbox WHERE mailbox.username='$username' AND vacation.mailbox_id=mailbox.id AND vacation.active = 1 ");
	return  db_array($result['result']);

}

//
// display_domain_status
// Action: return a field with color of the domain status
// Call: display_domain_status(int domain_status)
//
function display_domain_status($domain_status)
{

	
	switch ($domain_status) {
	case 1:
		$class="domain_not_exist";
		break;
	case 2:
		$class="domain_mx_problem";
		break;
	case 3:
		$class="domain_backup_problem";
		break;
	case 0:
		$class="domain_ok";
		break;
	}


  print "<td class=\"$class\">" . $domain_status . "</td>";

}


// Following functions has being taken from phpMyAdmin. Thanks to their
// Authors for their marvelous work !
//
// All theses functions has being prependent with OVA instead of PMA

//
// OVA_getenv()
// 
// Action: try to find the values for the given environment variable name
//         search in $_SERVER, $_ENV, then trys getenv() and apache_getenv()
//         in this order, same function as PMA_getenv() in phpmyadmin.
// call: OVA_getenv(string $var_name)
// return: value of $var or empty string
//
function OVA_getenv($var_name) {
    if (isset($_SERVER[$var_name])) {
        return $_SERVER[$var_name];
    } elseif (isset($_ENV[$var_name])) {
        return $_ENV[$var_name];
    } elseif (getenv($var_name)) {
        return getenv($var_name);
    } elseif (function_exists('apache_getenv')
     && apache_getenv($var_name, true)) {
        return apache_getenv($var_name, true);
    }

    return '';
}

//
// OVA_ishttps
//
// Check if we are using https connection or not.
//
function OVA_ishttps() {
        $is_https = false;

        $url = array();

        // At first we try to parse REQUEST_URI, it might contain full URL,
        if (OVA_getenv('REQUEST_URI')) {
            $url = @parse_url(OVA_getenv('REQUEST_URI')); // produces E_WARNING if it cannot get parsed, e.g. '/foobar:/'
            if($url === false) {
                $url = array();
            }
        }

        // If we don't have scheme, we didn't have full URL so we need to
        // dig deeper
        if (empty($url['scheme'])) {
            // Scheme
            if (OVA_getenv('HTTP_SCHEME')) {
                $url['scheme'] = OVA_getenv('HTTP_SCHEME');
            } else {
                $url['scheme'] =
                    OVA_getenv('HTTPS') && strtolower(OVA_getenv('HTTPS')) != 'off'
                        ? 'https'
                        : 'http';
            }
        }

        if (isset($url['scheme'])
          && $url['scheme'] == 'https') {
            $is_https = true;
        } else {
            $is_https = false;
        }

        return $is_https;
}

//
// OVA_getabsoluteuri()
// 
// Heavyly inspired from phpMyadmin PmaAbsoluteUri(), permit to find the 
// absolute URI of where is located OpenVISP Admin.
//
// return: the absolute URI
//
function OVA_getabsoluteuri() {
	global $CONF;
        // Setup a default value to let the people and lazy sysadmin work anyway,
        // they'll get an error if autodetect code don't work
        $absolute_uri = $CONF['baseurl'];
        $is_https = OVA_ishttps();

        if (strlen($absolute_uri) < 5
            // needed to catch http/https switch
            || ($is_https && substr($absolute_uri, 0, 6) != 'https:')
            || (!$is_https && substr($absolute_uri, 0, 5) != 'http:')
        ) {
            $url = array();

            // At first we try to parse REQUEST_URI, it might contain full URL
            if (OVA_getenv('REQUEST_URI')) {
                $url = @parse_url(OVA_getenv('REQUEST_URI')); // produces E_WARNING if it cannot get parsed, e.g. '/foobar:/'
                if ($url === false) {
                    $url = array( 'path' => $_SERVER['REQUEST_URI'] );
                }
            }

            // If we don't have scheme, we didn't have full URL so we need to
            // dig deeper
            if (empty($url['scheme'])) {
                // Scheme
                if (OVA_getenv('HTTP_SCHEME')) {
                    $url['scheme'] = OVA_getenv('HTTP_SCHEME');
                } else {
                    $url['scheme'] =
                        OVA_getenv('HTTPS') && strtolower(OVA_getenv('HTTPS')) != 'off'
                            ? 'https'
                            : 'http';
                }

                // Host and port
                if (OVA_getenv('HTTP_HOST')) {
                    if (strpos(OVA_getenv('HTTP_HOST'), ':') !== false) {
                        list($url['host'], $url['port']) =
                            explode(':', OVA_getenv('HTTP_HOST'));
                    } else {
                        $url['host'] = OVA_getenv('HTTP_HOST');
                    }
                } elseif (OVA_getenv('SERVER_NAME')) {
                    $url['host'] = OVA_getenv('SERVER_NAME');
                } else {
                    //$this->error_pma_uri = true;
                    return false;
                }

                // If we didn't set port yet...
                if (empty($url['port']) && OVA_getenv('SERVER_PORT')) {
                    $url['port'] = OVA_getenv('SERVER_PORT');
                }

                // And finally the path could be already set from REQUEST_URI
                if (empty($url['path'])) {
                    if (OVA_getenv('PATH_INFO')) {
                        $path = parse_url(OVA_getenv('PATH_INFO'));
                    } else {
                        // PHP_SELF in CGI often points to cgi executable, so use it
                        // as last choice
                        $path = parse_url(OVA_getenv('PHP_SELF'));
                    }
                    $url['path'] = $path['path'];
                }
            }

            // Make url from parts we have
            $absolute_uri = $url['scheme'] . '://';
            // Was there user information?
            if (!empty($url['user'])) {
                $absolute_uri .= $url['user'];
                if (!empty($url['pass'])) {
                    $absolute_uri .= ':' . $url['pass'];
                }
                $absolute_uri .= '@';
            }
            // Add hostname
            $absolute_uri .= $url['host'];
            // Add port, if it not the default one
            if (! empty($url['port'])
              && (($url['scheme'] == 'http' && $url['port'] != 80)
                || ($url['scheme'] == 'https' && $url['port'] != 443))) {
                $absolute_uri .= ':' . $url['port'];
            }
            // And finally path, without script name, the 'a' is there not to
            // strip our directory, when path is only /pmadir/ without filename.
            // Backslashes returned by Windows have to be changed.
            // Only replace backslashes by forward slashes if on Windows,
            // as the backslash could be valid on a non-Windows system.
            //if ($this->get('PMA_IS_WINDOWS') == 1) {
            //    $path = str_replace("\\", "/", dirname($url['path'] . 'a'));
            //} else {
                $path = dirname($url['path'] . 'a');
            //}

            // To work correctly within transformations overview:
            //if (defined('PMA_PATH_TO_BASEDIR') && PMA_PATH_TO_BASEDIR == '../../') {
            //    if ($this->get('PMA_IS_WINDOWS') == 1) {
            //        $path = str_replace("\\", "/", dirname(dirname($path)));
            //    } else {
            //        $path = dirname(dirname($path));
            //    }
            //}
            // in vhost situations, there could be already an ending slash
            if (substr($path, -1) != '/') {
                $path .= '/';
            }
            $absolute_uri .= $path;

            // We used to display a warning if PmaAbsoluteUri wasn't set, but now
            // the autodetect code works well enough that we don't display the
            // warning at all. The user can still set PmaAbsoluteUri manually.
            // See
            // http://sf.net/tracker/?func=detail&aid=1257134&group_id=23067&atid=377411

        } else {
            // The URI is specified, however users do often specify this
            // wrongly, so we try to fix this.

            // Adds a trailing slash et the end of the phpMyAdmin uri if it
            // does not exist.
            if (substr($absolute_uri, -1) != '/') {
                $absolute_uri .= '/';
            }

            // If URI doesn't start with http:// or https://, we will add
            // this.
            if (substr($absolute_uri, 0, 7) != 'http://'
              && substr($absolute_uri, 0, 8) != 'https://') {
                $absolute_uri =
                    (OVA_getenv('HTTPS') && strtolower(OVA_getenv('HTTPS')) != 'off'
                        ? 'https'
                        : 'http')
                    . ':' . (substr($absolute_uri, 0, 2) == '//' ? '' : '//')
                    . $absolute_uri;
            }
        }

        return $absolute_uri;
}

//
// check_mailhosting
// Action: Check if we got some smtp server.
// Call: check_mailhosting ()
//
function check_mailhosting()
{

	$sql_query = "SELECT count(*) as total_smtpd_server
FROM server_jobmodel, server_job
WHERE server_jobmodel.role='smtpd'
AND server_jobmodel.id=server_job.server_jobmodel_id";

	$result = db_query ($sql_query);
	$row = db_array($result['result']);
	if ($row['total_smtpd_server'] > 0 )
		{
			return TRUE;
		}
	else
		{
			return FALSE;
		}
}

//
// check_ftphosting
// Action: Check if we got some ftp server.
// Call: check_ftphosting ()
//
function check_ftphosting()
{

	$sql_query = "SELECT count(*) as total_ftp_server
FROM server_jobmodel, server_job
WHERE server_jobmodel.role='ftpd'
AND server_jobmodel.id=server_job.server_jobmodel_id";

	$result = db_query ($sql_query);
	$row = db_array($result['result']);
	if ($row['total_ftp_server'] > 0 )
		{
			return TRUE;
		}
	else
		{
			return FALSE;
		}
}


//
// check_webhosting
// Action: Check if we got some http server.
// Call: check_webhosting ()
//
function check_webhosting()
{

	$sql_query = "SELECT count(*) as total_http_server
FROM server_jobmodel, server_job
WHERE server_jobmodel.role='httpd'
AND server_jobmodel.id=server_job.server_jobmodel_id";

	$result = db_query ($sql_query);
	$row = db_array($result['result']);
	if ($row['total_http_server'] > 0 )
		{
			return TRUE;
		}
	else
		{
			return FALSE;
		}
}


//
// check_dbhosting
// Action: Check if we got some database server.
// Call: check_dbhosting (string dbtype)
//
function check_dbhosting($db_type="mysql")
{

	$sql_query = "SELECT count(*) as total_database_server
FROM server_jobmodel, server_job, server_apps
WHERE server_jobmodel.role='database'
AND server_jobmodel.id=server_job.server_jobmodel_id
AND server_apps.server_jobmodel_id=server_jobmodel.id
AND server_apps.apps='$db_type'
";

	$result = db_query ($sql_query);
	$row = db_array($result['result']);
	if ($row['total_database_server'] > 0 )
		{
			return TRUE;
		}
	else
		{
			return FALSE;
		}
}

//
// check_policyhosting
// Action: Check if we got some policyd server.
// Call: check_policyhosting ()
//
function check_policyhosting ()
{

	$sql_query = "SELECT count(*) as total_policyd_server
FROM server_jobmodel, server_job
WHERE server_jobmodel.role='policy'
AND server_jobmodel.id=server_job.server_jobmodel_id";

	$result = db_query ($sql_query);
	$row = db_array($result['result']);
	if ($row['total_policyd_server'] > 0 )
		{
			return TRUE;
		}
	else
		{
			return FALSE;
		}
}

//
// load_js
// Action: load a javascript
// Call: load_js (string jsfile_path)
//
function load_js ($js_file)
{
	return '<script type="text/javascript" src="'.OVA_getabsoluteuri().$js_file.'"></script>'."\n";
}

//
// load_css
// Action: load a css
// Call: load_css (string cssfile_path)
//
function load_css ($css_file)
{
	return '<link rel="stylesheet" type="text/css" href="'.OVA_getabsoluteuri().$css_file.'">'."\n";
}


//
// list_alias_domain
// Action: List all domain alias for a domain
// Call: list_alias_domain (string domain)
//
function list_alias_domain ($domain)
{
   $list = "";
	 $list['rows'] = 0;
   
	 $sql_query = "SELECT dalias
FROM domain , domain_alias
WHERE domain.domain = '$domain'
AND domain.id=domain_alias.domain_id
ORDER BY dalias";

   $result = db_query ($sql_query);
   if ($result['rows'] > 0)
   {
      while ($row = db_array ($result['result']))
				{ $list['rows'] ++;
					$list['data'][] = $row['dalias']; }
   }
   return $list;
}


//
// get_alias_domain_id
// Action: get id for a domain alias
// Call: get_alias_domain_id (string domain)
//
function get_alias_domain_id ($domain)
{
   $list = "";
   
	 $sql_query = "SELECT id, domain_id
FROM domain_alias
WHERE dalias = '$domain'
";

   $result = db_query ($sql_query);
   if ($result['rows'] > 0)
   {
		 $list = db_array ($result['result']);
   }
	 else
		 {
			 $list['id'] = 0;
			 $list['domain_id'] = 0;
		 }

	 return $list;
}


//
// list_array_domains_alias
// Action: List all available domains aliases into an array with id 
// Call: list_array_domains_alias ( string ORDER)
//
function list_array_domains_alias ($ORDER="dalias")
{
	$list = array();
  
   $result = db_query ("SELECT id, dalias, domain_id FROM domain_alias ORDER BY $ORDER");
   if ($result['rows'] > 0)
   {
      while ($row = db_array ($result['result']))
      {
         $list[$row['id']] = $row;
      }
   }
   return $list;
}


//
// check_domain_is_alias 
// Action: check if a domain is an alias or not
// Call: check_domain_is_alias ( string domain)
//
function check_domain_is_alias ($domain)
{
   $result = db_query ("SELECT dalias FROM domain_alias WHERE dalias='$domain'");
   if ($result['rows'] > 0)
		 { return TRUE; }
   return FALSE;
}

//
// convert_on_to_no
// Action: convert a on value to Y or N (for form checkbox)
// Call: convert_on_to_no ( string )
function convert_on_to_no ($string)
{	
	if ( $string == "on" ) { return 'N'; }
	else { return 'Y'; }
}


function debug_info ($string)
{

	file_put_contents('php://stderr', "DEBUG OVA DEV : $string\n");

}

function clean_empty_line ($filename){

	$lines = file($filename);

	foreach ($lines as $line_num => $line) {
		$line = chop ($line);
		if (eregi("^$|^#",$line)){
			unset($lines[$line_num]);
		}
	}
	return $lines;

}

// print_yahoo_menu()
// Action: print a menu link with Absolute URI system for a yahoo menu object
// Call: print_yahoo_menu(string $uri,string $text)
// Returns: html text : <a target="_top" href=absoluteuri()+$uri>$text</a>
//
function print_yahoo_menu($uri, $text) {
  print "<a class=\"yui3-menuitem-content\" href=\"".getabsoluteuri().$uri."\">".$text."</a>";
}


function ova_array_to_xml ($dataarray, $tabcount=2, $tagname) { 
  
  $xmldata = "";  
  $tabcount++;
  $tabSpace = "\t"; 
  $extraTabSpace = ""; 
  for ($i = 0; $i<$tabcount; $i++) { 
    $tabSpace .= "\t"; 
  }

  for ($i = 0; $i<$tabcount+1; $i++) { 
    $extraTabSpace .= "\t"; 
  } 

  foreach($dataarray as $tag => $val) {
    if (!is_array($val)) { 
      $xmldata .= PHP_EOL.$tabSpace.'<'.$tag.'>'.htmlentities($val).'</'.$tag.'>'; 
    }
    else { 
      $xmldata .= PHP_EOL.$extraTabSpace.'<'.$tagname.'>'.ova_array_to_xml($val, $tabcount,"$tagname"); 
      $xmldata .= PHP_EOL.$extraTabSpace.'</'.$tagname.'>'; 
    } 
  } 
    
return $xmldata; 
}

/*
 * If you visit a file that doesn't contain these lines at its end, please
 * cut and paste everything from here to that file.
 */

/*
 * Local Variables:
 * c-basic-offset: 2
 * End:
 *
 * vim: softtabstop=2 tabstop=2 expandtab autoindent formatoptions=croqlt smartindent cindent shiftwidth=2
 */

?>
