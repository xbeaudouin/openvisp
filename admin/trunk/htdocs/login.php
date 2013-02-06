<?php
//
// File: login.php
//
// Template File: login.tpl
//
// Template Variables:
//
//  tMessage
//  tUsername
//
// Form POST \ GET Variables:  
//
//  fUsername
//  fPassword
//  fCookie
//  fCode
//
require ("./variables.inc.php");
require ("./config.inc.php");
require ("./lib/functions.inc.php");
require ("./lib/versions.inc.php");
include ("./languages/" . check_language () . ".lang");
if (getcryptograph()) {
  $cryptinstall="./lib/crypt/cryptographp.fct.php";
  include $cryptinstall;
}

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   include ("./templates/header.tpl");
   $needs_upgrade = needs_upgrade();
   if ($needs_upgrade == 2) {
   	include ("./templates/login_error_unknown.tpl");
   } else {
        if ($needs_upgrade == 1) { 
           print "Warning ! Unkown upgrade type. You have some big problems inside your database. Please check with developers what it is.";
        } else {
           if ($needs_upgrade == 0) 
   	     include ("./templates/login.tpl");
	}
   }
   include ("./templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fUsername = get_post('fUsername');
   $fPassword = get_post('fPassword');
   $fCookie   = get_post('fCookie');
   if (getcryptograph()) {
     $fCode   = get_post('fCode');
   }

   // Protection form session steal
   if ($fCookie != crsf_key('login')) {
     // Session seems to be cached or stolen
     // Remove it from webserver and start a new one.
     // Write a message, but it should be never read.
		 //		 print $PALANG['pLogin_session_stolen'];
     session_unset ();
     session_destroy ();
     header("Location: login.php");
     exit;
   }

   if (getcryptograph()) {
     if (!chk_crypt($fCode)) {
       // Wrong Cryptocode
       //print $PALANG['pLogin_session_stolen'];
       session_unset ();
       session_destroy ();
       header("Location: login.php");
       exit;
     }
   }

   // Try to check if we have an admin account
   $result = db_query("SELECT password FROM accounts WHERE username='$fUsername' AND enabled='1'");
   if ($result['rows'] == 1) {
	 $row = db_array ($result['result']);
	 $password = pacrypt ($fPassword, $row['password']);
       
	 $result = db_query ("SELECT accounts.username, rights.manage FROM accounts, rights WHERE accounts.username='$fUsername' AND accounts.password='$password' AND accounts.enabled='1' and accounts.id=rights.accounts_id");
	 if ($result['rows'] != 1) {
		 $error = 1;
		 $tMessage = $PALANG['pLogin_password_incorrect'];
		 $tUsername = $fUsername;
	 } else {
		 $row = db_array($result['result']);
		 if ( $row['manage'] == '1' ){
			 $user_priv = "superadmin";
		 } else {
			 $user_priv = "user";
		 }
	 }
   } else {
	 // Try to check if we have an end user acces
		 $result = db_query("SELECT password FROM mailbox WHERE username='$fUsername' AND active='1'");
		 if ($result['rows'] == 1) {
		$row = db_array ($result['result']);
		$password = pacrypt ($fPassword, $row['password']);

		$result = db_query ("SELECT username FROM mailbox WHERE username='$fUsername' AND password='$password' and active='1'");
		if ($result['rows']!=1) {
			$error = 1;
			$tMessage = $PALANG['pLogin_password_incorrect'];
			$tUsername = $fUsername;
		} else {
			// this a mailbox user
			$user_priv = "mailbox";
		}
	} else {
		$error = 1;
		$tMessage = $PALANG['pLogin_username_incorrect'];
		$tUsername = $fUsername;
	}
   }
   
   if ($error != 1) {
      if (!getcryptograph()) {
        // If we don't use cryptograph, then start the session
        session_start(); 
      }
      ova_session_register("sessid");
      $absoluteuri = OVA_getabsoluteuri();
      switch ($user_priv) {
      	case "superadmin": 
		      $_SESSION['userid']['username'] = $fUsername;
		      $_SESSION['absoluteuri'] = $absoluteuri;
		      header("Location: users/main.php");
		      break;
	      case "mailbox":
		      $_SESSION['userid']['username'] = $fUsername;
		      $_SESSION['absoluteuri'] = $absoluteuri;
		      header("Location: users/main.php");
		      break;
		    case "user":
		      $_SESSION['userid']['username'] = $fUsername;
		      $_SESSION['absoluteuri'] = $absoluteuri;
		      header("Location: users/main.php");
		      break;
	      default:
		      header("Location: login.php");
		      break;
      }
      exit;
   }
   
   include ("./templates/header.tpl");
   include ("./templates/login.tpl");
   include ("./templates/footer.tpl");
} 
?>
