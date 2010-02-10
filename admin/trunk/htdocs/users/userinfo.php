<?php
//
// File: userinfo.php
//
// Template File: userinfo.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fPassword_current
// fPassword
// fPassword2
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/userinfo-menu.tpl");

   $result = db_query ("SELECT * FROM accounts WHERE username='$SESSID_USERNAME'");

	 if ($result['rows'] == 1){
		 $row = db_array ($result['result']);
		 $tCompany = $row['company'];
		 $tAddress = $row['address'];
		 $tPostalCode = $row['postal_code'];
		 $tCity = $row['city'];
		 $tWeburl = $row['weburl'];
		 $tEmail = $row['email'];
		 $tFax = $row['fax'];
		 $tPhone = $row['phone'];
		 $tEmailsupport = $row['emailsupport'];
		 $tPhonesupport = $row['phonesupport'];
		 $tWebfaq = $row['webfaq'];

	 }

   include ("../templates/userinfo.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $username = $SESSID_USERNAME;

   $fCompany      = get_post('fCompany');
   $fAddress      = get_post('fAddress');
   $fPostalCode   = get_post('fPostalCode');
   $fCity         = get_post('fCity');
   $fWeburl       = get_post('fWeburl');
   $fEmail        = get_post('fEmail');
   $fPhone        = get_post('fPhone');
   $fFax          = get_post('fFax');
   $fEmailsupport = get_post('fEmailsupport');
   $fPhonesupport = get_post('fPhonesupport');
   $fWebfaq       = get_post('fWebfaq');

	 
   $requete = "UPDATE accounts
set company='$fCompany', address='$fAddress', postal_code='$fPostalCode',
weburl='$fWeburl', email='$fEmail', phone='$fPhone', fax='$fFax',city='$fCity',
emailsupport='$fEmailsupport', phonesupport='$fPhonesupport', webfaq='$fWebfaq'
where username='$username'
";
	 $result = db_query ($requete);
	 // fLogo

	 $uploadfile = "../logos/".$username.".jpg";
 
	 //	 phpinfo();

	 move_uploaded_file($_FILES['flogo']['tmp_name'], $uploadfile);

   
   include ("../templates/header.tpl");
   include ("../templates/userinfo-menu.tpl");

   $result = db_query ("SELECT * FROM accounts WHERE username='$SESSID_USERNAME'");

	 if ($result['rows'] == 1){
		 $row = db_array ($result['result']);
		 $tCompany = $row['company'];
		 $tAddress = $row['address'];
		 $tPostalCode = $row['postal_code'];
		 $tCity = $row['city'];
		 $tWeburl = $row['weburl'];
		 $tEmail = $row['email'];
		 $tFax = $row['fax'];
		 $tPhone = $row['phone'];
		 $tEmailsupport = $row['emailsupport'];
		 $tPhonesupport = $row['phonesupport'];
		 $tWebfaq = $row['webfaq'];

	 }


   include ("../templates/userinfo.tpl");
   include ("../templates/footer.tpl");
}
?>
