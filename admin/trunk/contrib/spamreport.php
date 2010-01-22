<?

function list_spam($username,$date,$date2,$urlkey,$urlkey2,$date3){


	$eol = "\n";
	$action_url = "http://##WEBSERVER##/##OVA##/users/release.php";

	$headers .= 'From: Mail Administrator <mailadmin@##MAILDOMAIN##>'.$eol;

	$headers .= 'Reply-To: Mail Administrator <mailadmin@##MAILDOMAIN##>'.$eol; 
	$headers .= 'Return-Path: Mail Administrator <mailadmin@##MAILDOMAIN##>'.$eol; 
	$headers .= "Message-ID: <".$now." TheSystem@##MAILDOMAIN##>".$eol; 
	$headers .= "X-Mailer: PHP v".phpversion().$eol;
	$mime_boundary=md5(time()); 
	$headers .= 'MIME-Version: 1.0'.$eol; 
	$headers .= "Content-Type: multipart/related; boundary=\"".$mime_boundary."\"".$eol; 

	$font_size = '<font size="-1">';

	$query_spam = "INSERT INTO spamreport VALUES('$urlkey','$urlkey2','$username','$date3')";
	$result = mysql_query($query_spam);

	$mail_msg = "Content-Type: multipart/alternative".$eol; 
	$mail_msg .= "--".$mime_boundary.$eol; 
	$mail_msg .= "Content-Type: text/html; charset=iso-8859-1".$eol; 
	$mail_msg .= "Content-Transfer-Encoding: 8bit".$eol; 
	$mail_msg .= "<html><head>";
	$mail_msg .= '<META http-equiv="Content-Type" content="text/html; charset="UTF-8">';
	$mail_msg .= "<title>Quarantine Access Code</title></head><body>";
	$mail_msg .= "<h2>Here is your web access to your quarantine for the following date : $date2</h2></br>".$eol; 
	$mail_msg .= '<a href="'.$action_url.'?key='.$urlkey.'&key2='.$urlkey2.'">URL</a>';

	$query_spam = "INSERT INTO spamreport VALUES('$urlkey','$urlkey2','$username','$date3')";
	$result = mysql_query($query_spam);


	if ( $mail_msg ){
		$mail_msg .= "</font>";
		$mail_msg .= "</body></html>\n\n";
		mail($username,"Quarantine Access Code", $mail_msg, $headers);

	}


}


$phpuser = "";
$phpdb = "";
$phppass = "";
$phphost = "";

$link = mysql_connect($phphost,$phpuser,$phppass) or die ("Mysql Problem");
mysql_select_db($phpdb);


$now = time();
$yesterday = $now - 86400;
setlocale(LC_ALL, "fr_FR");


$result = mysql_query("truncate table spamreport") or die ("Query failed $query");

$query = 'select username from mailbox where active="1"';
$result = mysql_query($query) or die ("Query failed $query");

while ($row = mysql_fetch_object($result)) {

	//	$keypass = substr(md5($row->username.":".date("d/m/Y\n", $now)),0,254);
	$keypass = md5($row->username.":".date("d/m/Y\n", $now));
	$keypass2 = substr(md5 (mt_rand ()), 0, 254);
	$now = time();
	list_spam($row->username,date("Ymd",$now),date("d/m/Y\n", $now),$keypass,$keypass2,$now);

}




?>