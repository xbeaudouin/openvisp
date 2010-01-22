<?php
//
// File: delete-account.php
//
// Template File: accounts/delete-account.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// fUsername
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session ();

if ($_SERVER["REQUEST_METHOD"] == "GET" && $SESSID_USERNAME != NULL)
{

	$fUsername = get_get('username');

	$result = db_query ("SELECT * FROM accounts WHERE username='$fUsername'");
	if ($result['rows'] == 1)
		{
			$result = db_query ("DELETE FROM accounts WHERE username='$fUsername'");
			if ($result['rows'] != 1)
				{
					$error = 1;
					$tMessage = $PALANG['pDelete_delete_error'] . "<b>$fUsername</b> (account)!</div>";
				}
			else
				{
					db_log ($SESSID_USERNAME, "ova.local", "delete account", $fUsername);
				}
		}


	header("Location: list-accounts.php");
}

?>