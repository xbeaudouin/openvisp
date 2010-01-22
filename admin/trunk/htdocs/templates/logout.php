<?php
//
// File: logout.php
//
// Template File: -none-
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// -none-
//
require ("../config.inc.php");
require ("../lib/functions.inc.php");

// We use here check_session() since and admin is also a postmaster
$SESSID_USERNAME = check_user_session ();

session_unset ();
session_destroy ();

header ("Location: ../login.php");
exit;
?>
