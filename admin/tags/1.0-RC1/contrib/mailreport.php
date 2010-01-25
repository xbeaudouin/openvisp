<?php

function list_spam($username,$date,$date2){

	#$query = "SELECT msgs.from_addr, msgs.spam_level, msgs.size, msgs.subject, msgs.mail_id, msgs.secret_id";
	$query_spam = "SELECT msgs.from_addr, msgs.spam_level, msgs.size, msgs.subject, msgs.mail_id, msgs.secret_id, msgrcpt.rid, FROM_UNIXTIME(msgs.time_num) as time_mail, msgrcpt.rs";
	$query_spam .= " FROM msgs, msgrcpt, maddr";
	$query_spam .= " where maddr.email = '".$username."'";
	$query_spam .= " and maddr.id = msgrcpt.rid";
	$query_spam .= " and msgrcpt.mail_id = msgs.mail_id";
	$query_spam .= " and msgrcpt.rs = '' ";
	$query_spam .= " and msgs.content = 'S'";
	$query_spam .= " and msgs.time_iso like '%".$date."%'";


	$query_virus = "SELECT msgs.from_addr, msgs.spam_level, msgs.size, msgs.subject, msgs.mail_id, msgs.secret_id, msgrcpt.rid, FROM_UNIXTIME(msgs.time_num) as time_mail, msgrcpt.rs";
	$query_virus .= " FROM msgs, msgrcpt, maddr";
	$query_virus .= " where maddr.email = '".$username."'";
	$query_virus .= " and maddr.id = msgrcpt.rid";
	$query_virus .= " and msgrcpt.mail_id = msgs.mail_id";
	$query_virus .= " and msgs.content = 'V'";
	$query_virus .= " and msgs.time_iso like '%".$date."%'";
	$query_virus .= " and msgrcpt.rs = '' ";

	$query_banned = "SELECT msgs.from_addr, msgs.spam_level, msgs.size, msgs.subject, msgs.mail_id, msgs.secret_id, msgrcpt.rid, FROM_UNIXTIME(msgs.time_num) as time_mail, msgrcpt.rs";
	$query_banned .= " FROM msgs, msgrcpt, maddr";
	$query_banned .= " where maddr.email = '%".$username."%'";
	$query_banned .= " and maddr.id = msgrcpt.rid";
	$query_banned .= " and msgrcpt.mail_id = msgs.mail_id";
	$query_banned .= " and msgs.content = 'B'";
	$query_banned .= " and msgs.time_iso like '%".$date."%'";
	$query_banned .= " and msgrcpt.rs = '' ";

// 	echo $query_spam."\n";
// 	echo $query_virus."\n";
// 	echo $query_banned."\n";

	$result_spam = mysql_query($query_spam) or die("Query failed $query");
	$nb_spam = mysql_num_rows($result_spam);

	$result_banned = mysql_query($query_banned) or die("Query failed $query");
	$nb_banned = mysql_num_rows($result_banned);

	$nb_virus = 0;
//	$result_virus = mysql_query($query_virus) or die("Query failed $query");
//	$nb_virus = mysql_num_rows($result_virus);

	$eol = "\n";
	$action_url = "http://##WEBSERVER##/mailaction.php";

	$headers .= 'From: Mail Administrator <mailadmin@##MAILDOMAIN##>'.$eol; 
	$headers .= 'Reply-To: Mail Administrator <mailadmin@##MAILDOMAIN##>'.$eol; 
	$headers .= 'Return-Path: Mail Administrator <mailadmin@##MAILDOMAIN##>'.$eol; 
	$headers .= "Message-ID: <".$now." TheSystem@##MAILDOMAIN##>".$eol; 
	$headers .= "X-Mailer: PHP v".phpversion().$eol;
	$mime_boundary=md5(time()); 
	$headers .= 'MIME-Version: 1.0'.$eol; 
	$headers .= "Content-Type: multipart/related; boundary=\"".$mime_boundary."\"".$eol; 

	$font_size = '<font size="-1">';



	//echo $username;
	// echo " nb spam : ".$nb_spam;

	if ( $nb_spam > 0 ){

 

		$mail_msg = "Content-Type: multipart/alternative".$eol; 
		$mail_msg .= "--".$mime_boundary.$eol; 
		$mail_msg .= "Content-Type: text/html; charset=iso-8859-1".$eol; 
		$mail_msg .= "Content-Transfer-Encoding: 8bit".$eol; 
		$mail_msg .= "<html><head>";
		$mail_msg .= '<META http-equiv="Content-Type" content="text/html; charset="UTF-8">';
		$mail_msg .= "<title>Quarantine Mail list</title></head><body>";
		$mail_msg .= "<h2>Here are the spam list in quarantine for $date2</h2></br>".$eol; 

		$boucle = 0;

		while ( $obj_spam = mysql_fetch_object($result_spam) ){

			if ( $boucle == 0 ){
				$boucle++;
				$mail_msg .= "<a href='".$action_url."?maction=delete&pid=".$obj_spam->rid."&sel=all&per=".$date."'>Destroy all</a></br>".$eol;
				$mail_msg .= "<table><tr><td>From</td><td>Subject</td><td>Time / Date</td><td>Size</td>";
				$mail_msg .= "<td>Spam Note</td><td>action</td></tr>";
			}

			$mail_msg .= "<tr>\n";
			$mail_msg .= " <td>$font_size".$obj_spam->from_addr."</font></td>\n";
			$mail_msg .= " <td>$font_size".$obj_spam->subject."</font></td>\n";
			$mail_msg .= " <td>$font_size".$obj_spam->time_mail."</font></td>\n";
			$mail_msg .= " <td>$font_size".$obj_spam->size."</font></td>\n";
			$mail_msg .= " <td>$font_size".$obj_spam->spam_level."</font></td>\n";
			$mail_msg .= " <td><a href='".$action_url."?maction=delete&pmailid=".urlencode($obj_spam->mail_id)."&pmailsecid=".urlencode($obj_spam->secret_id)."&pid=".$obj_spam->rid."'>Delete</a> / <a href='".$action_url."?maction=release&pmailid=".urlencode($obj_spam->mail_id)."&pmailsecid=".urlencode($obj_spam->secret_id)."&pid=".$obj_spam->rid."'>Release</a></td>\n";
			$mail_msg .= "</tr>\n\n";

		}


		$mail_msg .= "</table>";

	}

	//echo " | nb banned : ".$nb_banned;

	if ( $nb_banned > 0 ){



		if ( !isset($mail_msg) ){
			$mail_msg = "Content-Type: multipart/alternative".$eol; 
			$mail_msg .= "--".$mime_boundary.$eol; 
			$mail_msg .= "Content-Type: text/html; charset=iso-8859-1".$eol; 
			$mail_msg .= "Content-Transfer-Encoding: 8bit".$eol; 
			$mail_msg .= "<html><head>";
			$mail_msg .= '<META http-equiv="Content-Type" content="text/html; charset="UTF-8">';
			$mail_msg .= "<title>Banned list in quarantine </title></head><body>";
		}

		$mail_msg .= "<h2>Here is the banned list in quarantine for $date2</h2></br>".$eol; 
		$mail_msg .= "<font size='-2'>";
		$mail_msg .= "<table><tr><td>From</td><td>Subject</td><td>Time / Date</td><td>size</td>";
		$mail_msg .= "<td>action</td></tr>";

		while ( $obj_banned = mysql_fetch_object($result_banned) ){

			$mail_msg .= "<tr>\n";
			$mail_msg .= " <td>$font_size".$obj_banned->from_addr."</font></td>\n";
			$mail_msg .= " <td>$font_size".$obj_banned->subject."</font></td>\n";
			$mail_msg .= " <td>$font_size".$obj_banned->time_mail."</font></td>\n";
			$mail_msg .= " <td>$font_size".$obj_banned->size."</font></td>\n";
			$mail_msg .= " <td><a href='".$action_url."?maction=delete&pmailid=".urlencode($obj_banned->mail_id)."&pmailsecid=".urlencode($obj_banned->secret_id)."&pid=".$obj_spam->rid."'>Delete</a> / <a href='".$action_url."?maction=release&pmailid=".urlencode($obj_banned->mail_id)."&pmailsecid=".urlencode($obj_banned->secret_id)."&pid=".$obj_spam->rid."'>Release</a></td>\n";
			$mail_msg .= "</tr>\n\n";
		}


		$mail_msg .= "</table>";

	}

	//echo " | nb virus : ".$nb_virus;
	
	if ( $nb_virus > 0 ){

		if ( ! isset($mail_msg) ){
			$mail_msg = "Content-Type: multipart/alternative".$eol; 
			$mail_msg .= "--".$mime_boundary.$eol; 
			$mail_msg .= "Content-Type: text/html; charset=iso-8859-1".$eol; 
			$mail_msg .= "Content-Transfer-Encoding: 8bit".$eol; 
			$mail_msg .= "<html><head>";
			$mail_msg .= '<META http-equiv="Content-Type" content="text/html; charset="UTF-8">';
			$mail_msg .= "<title>Virus quarantine list</title></head><body>";
		}

		$mail_msg .= "<h2>Here is the virus list in quarantine for $date2</h2></br>".$eol;
		$mail_msg .= "<a href='".$action_url."?maction=delete&pmailid=".urlencode($obj_virus->mail_id)."&pmailsecid=".urlencode($obj_virus->secret_id)."&pid=".$obj_virus->rid."'>Delete all</a></br>".$eol;
		$mail_msg .= "<font size='-2'>";
		$mail_msg .= "<table><tr><td>From</td><td>Subject</td><td>Time / Date</td><td>Size</td>";
		$mail_msg .= "<td>action</td></tr>";

		while ( $obj_virus = mysql_fetch_object($result_virus) ){

			$mail_msg .= "<tr>\n";
			$mail_msg .= " <td>$font_size".$obj_virus->from_addr."</font></td>\n";
			$mail_msg .= " <td>$font_size".$obj_virus->subject."</font></td>\n";
			$mail_msg .= " <td>$font_size".$obj_virus->time_mail."</font></td>\n";
			$mail_msg .= " <td>$font_size".$obj_virus->size."</font></td>\n";
			$mail_msg .= " <td><a href='".$action_url."?maction=delete&pmailid=".urlencode($obj_virus->mail_id)."&pmailsecid=".urlencode($obj_virus->secret_id)."&pid=".$obj_virus->rid."'>delete</a> / <a href='".$action_url."?maction=release&pmailid=".urlencode($obj_virus->mail_id)."&pmailsecid=".urlencode($obj_virus->secret_id)."&pid=".$obj_spam->rid."'>Release</a></td>\n";
			$mail_msg .= "</tr>\n\n";
		}


		$mail_msg .= "</table>";

	}




	if ( $mail_msg ){
		$mail_msg .= "</font>";
		$mail_msg .= "</body></html>\n\n";
		mail($username,"Spam / Virus / Banned list in quarantine", $mail_msg, $headers);

	}



}


$phpuser = "";
$phpdb = "";
$phppass = "";
$phphost = "";


$link = mysql_connect($phphost,$phpuser,$phppass) or die ("Soucis Mysql");
mysql_select_db($phpdb);


$now = time();
$yesterday = $now - 86400;
setlocale(LC_ALL, "fr_FR");

$query = 'select username from mailbox where active="1"';
$result = mysql_query($query) or die ("Query failed $query");

while ($row = mysql_fetch_object($result)) {

if ( (ereg_replace("^[0]","",date("H\n", $now)) ) > 9   ){
  list_spam($row->username,date("Ymd",$now),date("d/m/Y\n", $now));
}
else{
  list_spam($row->username,date("Ymd",$yesterday),date("d/m/Y\n", $yesterday));
  list_spam($row->username,date("Ymd",$now),date("d/m/Y\n", $now));
}

}




?>
