<?php


function delete_spam($id,$secid,$rid){
	$query = "UPDATE msgrcpt, msgs SET msgrcpt.rs = 'D' WHERE msgrcpt.mail_id = '".$id."' and msgrcpt.mail_id = msgs.mail_id and msgs.secret_id = '".$secid."' and msgrcpt.rid = '".$rid."'";
	$result = mysql_query($query) or die("Query failed $query");
	print "</br>Your delete submit is in progress.";

}


function release_spam($id,$sid,$rid){
	$query = "UPDATE msgrcpt SET rs = 'p' WHERE mail_id = '".$id."' and rid = '".$rid."'";
	$result = mysql_query($query) or die("Query failed $query");
	print "<br />Your relase submit is in progress<br />";
	
	$info_spam = str_split($id);
	system ("/usr/local/www/data/amavisd-release spam/".$info_spam{0}."/".$id.".gz ".$sid);
	$query2 = "UPDATE msgrcpt SET rs = 'R' WHERE mail_id =  '".$id."'";
	$result2 = mysql_query($query2) or die("Query failed $query2");

}


$phpuser = "";
$phpdb = "";
$phppass = "";
$phphost = "";

$link = mysql_connect($phphost,$phpuser,$phppass) or die ("Soucis Mysql");
mysql_select_db($phpdb);

if ( $_GET["maction"] == "delete" && $_GET["sel"] == "all" ){

        $query_spam = "SELECT msgs.from_addr, msgs.spam_level, msgs.size, msgs.subject, msgs.mail_id, msgs.secret_id, msgrcpt.rid, FROM_UNIXTIME(msgs.time_num) as time_mail, msgrcpt.rs";
        $query_spam .= " FROM msgs, msgrcpt";
        $query_spam .= " where msgrcpt.rid = '".$_GET["pid"]."'";
        $query_spam .= " and msgrcpt.mail_id = msgs.mail_id";
        $query_spam .= " and msgrcpt.rs = '' ";
        $query_spam .= " and msgs.content = 'S'";
        $query_spam .= " and msgs.time_iso like '%".$_GET["per"]."%'";

	$query = "SELECT msgs.from_addr, msgs.spam_level, msgs.size, msgs.subject, msgs.mail_id, msgs.secret_id, FROM_UNIXTIME(msgs.time_num) as time_mail, maddr.email";
	$query .= " FROM msgs, msgrcpt, maddr";
	$query .= " where msgs.mail_id='".$_GET["pmailid"]."'";
	$query .= " and msgs.secret_id='".$_GET["pmailsecid"]."'";
	$query .= " and msgrcpt.rid = '".$_GET["pid"]."'";
	$query .= " and msgrcpt.mail_id = msgs.mail_id";
	$query .= " and maddr.id = msgrcpt.rid";
	$query .= " and msgrcpt.rs = ''";


	$result = mysql_query($query_spam) or die("Query failed $query");

	$boucle = 0;

	while ( $obj_spam = mysql_fetch_object($result) ){
		if ( $boucle == 0 ){
			$boucle++;
			echo "Your quarantine mail for the date ".$_GET["per"]." are being deleted in the next 24 hours";
		}
		
		delete_spam($obj_spam->mail_id,$obj_spam->secret_id,$obj_spam->rid);
	}

	die();

 }



$query = "SELECT msgs.from_addr, msgs.spam_level, msgs.size, msgs.subject, msgs.mail_id, msgs.secret_id, FROM_UNIXTIME(msgs.time_num) as time_mail, maddr.email";
$query .= " FROM msgs, msgrcpt, maddr";
$query .= " where msgs.mail_id='".$_GET["pmailid"]."'";
$query .= " and msgs.secret_id='".$_GET["pmailsecid"]."'";
$query .= " and msgrcpt.rid = '".$_GET["pid"]."'";
$query .= " and msgrcpt.mail_id = msgs.mail_id";
$query .= " and maddr.id = msgrcpt.rid";
$query .= " and msgrcpt.rs = ''";

#echo $query."\n</br>";


$result = mysql_query($query) or die("Query failed $query");
$obj = mysql_fetch_object($result);



if ( $_GET["maction"] == "delete" && $_GET["sel"] != "all" ){
	delete_spam($_GET["pmailid"],$_GET["pmailsecid"],$_GET["pid"]);
 }

if ( $_GET["maction"] == "release" ){
	release_spam($_GET["pmailid"],$_GET["pmailsecid"],$_GET["pid"]);
 }




?>
