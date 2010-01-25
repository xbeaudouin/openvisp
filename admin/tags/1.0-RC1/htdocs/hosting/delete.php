<?php
//
// File: delete.php
//
// Template File: message.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fVhost
// fDomain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{

  $fVhost = get_get('vhost');
  $fDomain = get_get('domain');
  $fAccount = get_get('account');
	$fDomain_id = get_domain_id($fDomain);
	
  if ( isset($fVhost) )
  {
    $result = db_query ("DELETE FROM whost WHERE vhost='$fVhost' AND domain_id = '$fDomain_id'");
    if ($result['rows'] != 1)
    {
       $error = 1;
       $tMessage = $PALANG['pWhostEdit_whost_delete_error'];
    }
  
    if ($error != 1)
    {
       header ("Location: list-webvirtual.php?username=$SESSID_USERNAME");
       exit;
    }
  }
  if ( isset($fAccount) )
  {
    $result = db_query ("DELETE FROM ftpaccount WHERE login = '$fAccount' AND domain_id = '$fDomain_id'");
    if ($result['rows'] != 1)
    {
      $error = 1;
      $tMessage = $PALANG['pWhostEdit_ftp_delete_error'];
    }
    if ($error != 1)
    {
      header ("Location: list-ftp.php?username=$SESSID_USERNAME");
      exit;
    }
  }
  
  if ( !isset($fVhost) &&  !isset($fAccount) )
  {
    header ("Location: ../list-webvirtual.php?username=$SESSID_USERNAME");
    exit;
  }
  
  include ("../templates/header.tpl");
  include ("../templates/hosting/menu.tpl");
  include ("../templates/hosting/list-webvirtual.tpl");
  include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
  include ("../templates/header.tpl");
  include ("../templates/hosting/menu.tpl");
  include ("../templates/hosting/list-webvirtual.tpl");
  include ("../templates/footer.tpl");
}

?>
