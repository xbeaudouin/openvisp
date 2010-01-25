<?php
//
// File: spamreport.php
//
// Template File: 
//
// Template Variables:


if ( ( $_SERVER['REQUEST_METHOD'] == "GET" ) or ( $_SERVER['REQUEST_METHOD'] == "POST" ) ){

 }
 else{

	 require ("../variables.inc.php");
	 require ("../config.inc.php");
	 require ("../lib/functions.inc.php");
	 require("../lib/send_mail.inc.php");
	 include ("../languages/" . check_language () . ".lang");

	 // Since this code is executed from terminal, no stderr is available.
	 $CONF['SQL_DEBUG'] = "NO";

	 $result = db_query("truncate table spamreport");

	 $list_user = get_list_email_spamreport();

	 if (sizeof($list_user) > 0){

		 for ($i = 0; $i < sizeof ($list_user); $i++){

			 $now = time();

			 $keypass = md5($list_user[$i]['username'].":".date("d/m/Y\n", $now));
			 $keypass2 = substr(md5 (mt_rand ()), 0, 254);

			 $query_spam = "INSERT INTO spamreport(id,key2,mailbox_id,created) VALUES('".$keypass."','".$keypass2."','".$list_user[$i]['id']."','$now')";
			 $result2 = db_query($query_spam);

			 $tSubject = $PALANG['pQuarantine_Subject'];
			 $tBody    = $PALANG['pQuarantine_Part1'] . date("d/m/Y\n", $now)."</br>\n";
			 $tBody    .= '<a href="'.$CONF['release_url'].'?key='.$keypass.'&key2='.$keypass2.'">URL</a>';

			 send_mail($CONF['release_from'],$list_user[$i]['username'],$tSubject,$tBody,'','html');

		 }
		 
	 }

 
 }

?>