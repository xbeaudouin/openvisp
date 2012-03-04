<?php
//
// File: ajax/domain/domain_search.php
//
// Form POST \ GET Variables:
//
// fDomain
//

require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");


require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/domain.class.php");
require_once ("../lib/mail.class.php");

$SESSID_USERNAME = check_user_session ();

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

  $fDomain=get_post("wdomain");
  $_SESSION['sessid']['wdomain'] = $fDomain;
  
}


?>