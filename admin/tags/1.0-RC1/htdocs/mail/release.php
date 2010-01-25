<?php
//
// File: mail/release.php
//
// Template File: mail/release.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// key
//

require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
include ("../languages/" . check_language () . ".lang");

$tMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
	$tKey = get_get('key');
 	$username = get_username_key($tKey);
	$tKey2 = get_get('key2');

	$info = array();
	$info = get_spam_key($username['id']);

	$keypass = md5($username['username'].":".date("d/m/Y\n", $info['created']));
	$keypass2 = $info['key2'];

	if ( (sizeof($username) > 0)  && ($keypass == $tKey) && ($keypass2 == $tKey2) ){
		include ("../templates/header.tpl");
		$date_quarantine = list_date_quarantine($username['username']);
		if ( (is_array ($date_quarantine)) && ( sizeof($date_quarantine) > 0 ) ) {
			$tDate = $date_quarantine[sizeof($date_quarantine) - 1];
			$spam_list_date = list_spam_date($tDate,$username['username']);
		}

		include ("../templates/users/release.tpl");
		include ("../templates/footer.tpl");
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$tKey = get_post('key');
	$tKey2 = get_post('key2');
 	$username = get_username_key($tKey);
	$tDate = get_post('fDate');
	$tAction = get_post('fAction');

	$info = array();
	$info = get_spam_key($username['id']);

	$keypass = md5($username['username'].":".date("d/m/Y\n", $info['created']));
	$keypass2 = $info['key2'];

	if ( (sizeof($username) > 0)  && ($keypass == $tKey) && ($keypass2 == $tKey2) ){

		if ( $tAction == "delete" ){

			$tMailid = get_post('fMailid');

			foreach ( $tMailid as $mailid )
				{

					$mailid = urldecode($mailid);

					$query = "UPDATE msgrcpt, msgs, maddr
SET msgrcpt.rs = 'D'
WHERE msgrcpt.mail_id = '".$mailid."' 
AND msgrcpt.mail_id = msgs.mail_id 
AND msgs.time_iso like '".$tDate."%' 
AND msgrcpt.rid = maddr.id 
AND maddr.email = '".$username['username']."'
";

					$result = db_query($query);

				}
			$tMessage = $PALANG['pAmavis_Delete'];

		}

		elseif ( $tAction == "release" ){

			$tMailid = get_post('fMailid');

			foreach ( $tMailid as $mailid )
				{

					$mailid = urldecode($mailid);

					$query = "UPDATE msgrcpt, msgs, maddr 
SET msgrcpt.rs = 'p' 
WHERE msgrcpt.mail_id = '".$mailid."' 
AND msgrcpt.mail_id = msgs.mail_id 
AND msgs.time_iso like '".$tDate."%' 
AND msgrcpt.rid = maddr.id 
AND maddr.email = '".$username['username']."'
";

					$result = db_query($query);

					$query = "SELECT msgs.secret_id
FROM  msgrcpt, msgs, maddr
WHERE msgrcpt.mail_id = '".$mailid."' 
AND msgrcpt.mail_id = msgs.mail_id 
AND msgs.time_iso like '".$tDate."%' 
AND msgrcpt.rid = maddr.id 
AND maddr.email = '".$username['username']."'
";

					$result = db_query($query);

					$row = db_array($result['result']);
					
					//$info_spam = str_split($mailid);
					system ($CONF['amavisd-release']." spam/".$mailid{0}."/".$mailid.".gz ".$row['secret_id']);
					//print "/usr/local/www/data/amavisd-release spam/".$mailid{0}."/".$mailid.".gz ".$row['secret_id']."<br>";
					$query2 = "UPDATE msgrcpt SET rs = 'R' WHERE mail_id =  '".$mailid."'";
					$result2 = db_query($query2) or die("Query failed $query2");
					$tMessage = $PALANG['pAmavis_Submit'];
				}

		}

		include ("../templates/header.tpl");

		$date_quarantine = list_date_quarantine($username['username']);
		if ( (is_array ($date_quarantine)) && ( sizeof($date_quarantine) > 0 ) ) {
			$tDate = $date_quarantine[sizeof($date_quarantine) - 1];
			$spam_list_date = list_spam_date($tDate,$username['username']);
		}
	
		include ("../templates/users/release.tpl");
		include ("../templates/footer.tpl");

	}

}



?>