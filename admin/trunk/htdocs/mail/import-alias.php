<?php
//
// File: import-mailbox.php
//
// Template File: mail/import-alias.tpl
//
// Template Variables:
//
// Form POST \ GET Variables:
//
// domain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();
$list_domains = list_local_domains_for_admin ($SESSID_USERNAME);

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
	 $tDomain = get_get('domain');

   if ( $tDomain != NULL ) {

     if (!check_owner($SESSID_USERNAME, $tDomain)) {
       // Be paranoid. If someone is trying to get acces to a
       // a domain that is not in charge, then logout the user
       // directly.
       header ("Location: ../logout.php");
     }
   } else {
     header ("Location: overview.php");
   }

   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/import-alias.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{

	$fDomain=get_post('fDomain');

	if ( $fDomain != NULL ) {
		
		if (!check_owner($SESSID_USERNAME, $fDomain)) {
			// Be paranoid. If someone is trying to get acces to a
			// a domain that is not in charge, then logout the user
			// directly.
			header ("Location: ../logout.php");
		}
	} else {
		header ("Location: overview.php");
	}


	$uploadfile = $CONF['uploaddir'] ."/". basename($_FILES['user_file']['name']);

	if (move_uploaded_file($_FILES['user_file']['tmp_name'], $uploadfile)) {

		$lines = file($uploadfile);
		foreach ($lines as $line_num => $line) {
			$line = chop ($line);

			/* { print "VIDE\n<br/>";next();} */

			if (!eregi("^$",$line)){
				$info = explode(";", $line);

				$alias_from = $info[0];
				$alias_table_to = preg_split ('/,/', $info[1]);
				$alias_active = $info[2];

				if ( eregi("@",$alias_from )){
					$array = preg_split ('/@/', $alias_from);
					$alias_from = $array[0];
				}

				$alias_from = chop(escape_string($alias_from)) . "@" .  $fDomain;

				for ( $i=0; $i < sizeof($alias_table_to); $i++)
					{
						if ( !eregi("@",$alias_table_to[$i] )){
							$alias_table_to[$i] =  chop(escape_string($alias_table_to[$i])) . "@" .  $fDomain;
						}
					}
				$alias_to = implode(",", $alias_table_to);


				$add_alias =  add_mailbox_alias( $fDomain, $alias_from, $alias_to );
				$tMessage .= $add_alias['message'];

			}

		}

	}



	include ("../templates/header.tpl");
	include ("../templates/mail/menu.tpl");

	//print $pImport_Error."<br />";
	print $tMessage;

	//	include ("./templates/import-mailbox.tpl");
	print "<br/><a href=\"overview.php?domain=$fDomain\">".$PALANG['pImport_Users_return']."</a><br/>";
	include ("../templates/footer.tpl");
 }
?>
