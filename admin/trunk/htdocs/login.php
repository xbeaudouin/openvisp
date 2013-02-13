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

require_once ("MDB2.php");
require_once ("./lib/db.class.php");
require_once ("./lib/ova.class.php");
require_once ("./lib/user.class.php");

$ovadb = new DB();
$ova_info = new OVA($ovadb); 
$user = new USER($ovadb); 


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

  $user->fetch_info($fUsername);

  if ( $user->check_passwd($fPassword) ){

    if (!getcryptograph()) {
      // If we don't use cryptograph, then start the session
      session_start(); 
    }
    $ova_info->ova_session_register("sessid");
    $absoluteuri = $ova_info->setabsoluteuri();

    $_SESSION['userid']['username'] = $fUsername;
    $_SESSION['absoluteuri'] = $absoluteuri;
    header("Location: users/main.php");


  }
  else{
    $error = 1;
    $tMessage = $PALANG['pLogin_password_incorrect'];
    $tUsername = $fUsername;
  }



  include ("./templates/header.tpl");
  include ("./templates/login.tpl");
  include ("./templates/footer.tpl");
} 
?>
