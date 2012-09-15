<?php

// send_mail
// Action: send a mail
// Return if mail has been sent
// Call: send_mail(string from, string to, string subject, string message, string cc, string bcc)
// Details of calls :
// $from     = mail from
// $to       = recipient for the mail
// $subject  = subject of the mail
// $message  = message body
// $cc       = Carbon Copy
function send_mail($from, $to, $subject="", $message="(empty message)", $cc="",$format="text")
{
	global $CONF;

	$smail = new SMTP($CONF['smtp_server'], $CONF['smtp_port']); //Initiate class into object.
	if ($smail->errno)
           die ($smail->errmsg());
	if (!$smail->SetFrom("$from"))
           die ($smail->errmsg());
	if (!$smail->SetToTitle("$to"))
           die ($smail->errmsg());
	if (!$smail->SetTo("$to"))
           die ($smail->errmsg());
	// if (!$smail->SetTo('somebody@somewhere.com')) die ($smail->errmsg());
	if ($cc) {
		if (!$smail->SetCC("$cc"))
                   die ($smail->errmsg());
		// if (!$smail->SetCC('somebody@something.com')) die ($smail->errmsg());
	}
	if (!$smail->SetSubject("$subject"))
            die ($smail->errmsg());
	if (!$smail->SetText("$message"))
            die ($smail->errmsg());
	if (!$smail->sendmail($format))
            die ($smail->errmsg());
	if (!$smail->end())
            die ($smail->errmsg());
}

Class SMTP {
	// global variables

Var     $errno     = 0;
Var     $errmsg    = '';
Var     $errors=Array(
         1 => 'Invalid Input',
         2 => 'Unable to establish connection',
         3 => 'Connection timed out',
         4 => 'SMTP Error returned',
         );

Var     $server         = "";
Var     $user           = "";
Var     $sock           = false;
Var     $readlength     = 1024;

Var     $mailto         = array();
Var     $mailcc         = array();
Var     $mailfrom       = "";
Var     $mailfromtitle  = '';
Var     $mailsubject    = "";
Var     $mailtext       = "";
Var     $attachments    = array();

Var     $deadmsg        = "";
Var     $verbose        = "0";
                                 //Modify this for verbose output of the transaction..
                                 //1 for verbose
                                 //0 for silent

Function SMTP($server, $smtpport=25, $timeout=10)
         {
         // Return false if we can't get an SMTP connection...

         if (strlen($server)<=0)
                 return $this->error(1, 'server');
         else    $this->server=$server;

         if (!$this->sock = fsockopen($server, $smtpport, &$errno, &$errstr, $timeout))
                 return $this->error(2);

         if (!socket_set_blocking($this->sock, true))
                 return $this->error(4, 'set blocking');

         if (!$this->GetFeedback())
                 return $this->error(2, 'during onset');

         return true;
         }

Function Error($errno, $message)
         {
         $this->errno=$errno;
         $this->errmsg.=$message;
         return false;
         }

Function ErrMsg($error=false)
         {
         if (false===$error)
                 {
                 $errno=$this->errno;
                 return $this->$errors[$errno].' '.$this->errmsg;
                 }
         else    return $this->$errors[$error];
         }

Function ErrNo()
         {
         return $this->errno;
         }

Function SetSubject($subject)
         {
         if (strlen($subject)<=0)
                 return $this->Error(1, 'subject');
         $this->mailsubject = ereg_replace("\n"," ",$subject);
         return true;
         }

Function setText($text)
         {
         if (strlen($text)<=0)
                 return $this->Error(1, 'message body');
         $text = ereg_replace("\n.\n", "\n. \n", $text);
         $text = ereg_replace("\n.\r", "\n. \r", $text);
         $this->mailtext = $text;
         return true;
         }

Function SetTo($to)
         {
         // THIS CAN BE USED TO SET AS MANY "BCC" RECIPIENTS AS YOU LIKE.
         if (strlen($to)<=0)
                 return $this->Error(1, '"to" too short');
         if (strlen($to)>=129)
                 return $this->Error(1, '"to" too long');
         $this->mailto[]=$to;
         return true;
         }

Function SetToTitle($title)
         {
         if (strlen($title)<=0)
                 return $this->Error(1, 'totitle too short');
         if (strlen($title)>=128)
                 return $this->Error(1, 'totitle too long');
         $this->mailtotitle="<$title>";
         return true;
         }

function SetCC($to)
         {
         // THIS WORKS LIKE "SETTO" ABOVE, BUT TELLS EVERYBODY WHO'S INVOLVED.
         // This is a potential privacy issue...

         if (!$this->setto($to))
                 return false;
         $this->mailcc[]=$to;
         return true;
         }

Function SetFrom($from)
         {
         if (strlen($from)<=0)
                 return $this->Error(1, 'from too short');
         if (strlen($from)>=128)
                 return $this->Error(1, 'from too long');
         $this->mailfrom=$from;
         return true;
         }

Function SetFromTitle($title)
         {
         if (strlen($title)<=0)
                 return $this->Error(1, 'fromtitle too short');
         if (strlen($title)>=128)
                 return $this->Error(1, 'fromtitle too long');
         $this->mailfromtitle=$title;
         return true;
         }

function AddAttachment($type, $data, $name)
         {
         // THIS WILL TAKE ATTACHMENTS, BUT WON'T YET MAIL THEM.
         $insert=sizeof($this->attachements);
         $this->attachements[$insert][data]=$data;
         $this->attachements[$insert][type]=$type;
         $this->attachements[$insert][name]=$name;
         return false;
         }

Function BuildBody($MIMEType)
         {
	 $return = "";
         // Take the text, add any attachements, doc type, etc.
         // attachments currently don't work.

         // SET MIME TYPE
         if ($MIMEType=='text')
                 $return.="Content-Type: text/plain; charset=iso-8859-1\r\n";
         elseif($MIMEType=='html')
                 $return.="Content-Type: text/html; charset=iso-8859-1\r\n";
         else
                 $return.="Content-Type: text/plain; charset=iso-8859-1\r\n";

         // SET TO HEADER
         if (strlen($this->mailtotitle)>=1)
                 $return.="To: ".$this->mailtotitle."\r\n";

         // SET FROM HEADER
         if (strlen($this->mailfromtitle)>=1)
                 $return.="From: ".$this->mailfromtitle." <".$this->mailfrom.">\r\n";
         else    $return.="From: ".$this->mailfrom."\r\n";

         if (sizeof($this->mailcc)>=1)
                 {
                 $return.="Cc:";
                 for ($i=0; $i<sizeof($this->mailcc); $i++)
                         {
                         if ($i)
                                 $return.=", ";
                         $return.=$this->mailcc[$i];
                         }
                 $return.="\r\n";
                 }

         if (strlen($this->mailsubject)>=1)
                 $return.="Subject: ".$this->mailsubject."\r\n";

         $return .="X-Priority: 3\r\n";
         $return .= "\r\n" . $this->mailtext;

         return $return;
         }

Function sendmail($text_type='html')
         {
         if (!$this->sock)
                 return $this->error(2);
         else    {
                 if (!$body=$this->BuildBody($text_type))
                         return $this->Error(1, 'BuildBody Failed');

                 $head[]="HELO ".$this->server;
                 $head[]="MAIL FROM:<".$this->mailfrom.">";
                 while (list($key, $value)= each($this->mailto))
                         {
                         $head[]="RCPT TO:<$value>";
                         }
                 $head[]='DATA';

                 reset ($head);

                 while (list($key, $value)=each ($head))
                         {
                         fputs($this->sock, $value."\r\n");
                         if (!$this->GetFeedback())
                                 return $this->error($this->errno(), "($value)");
                         }

                 fputs($this->sock, "$body\r\n.\r\n");
                 if (!$this->GetFeedback())
                         return false;
                 }
         $this->ResetData();
         return true;
         }

function ResetData()
         {
         $mailto         = array();
         $mailfrom       = "";
         $mailfromtitle  = '';
         $mailsubject    = "";
         $mailtext       = "";
         $attachments    = array();
         return true;
         }


Function GetFeedback()
         {
         if (!$response=fgets($this->sock, $this->readlength))
                 return false;
         if ($this->IsOK($response))
                 return true;
         else    return false;
         }

Function IsOK ($input)
         {
         // Extract the return code from the SMTP server, and make sure it's a
         // 'yahoo' instead of a 'shucks'.

         if (!ereg("((^[0-9])([0-9]*))", $input, $regs))
                 return $this->error(1, 'input');

         $code=$regs[1];

         switch ($code)
                 {
                 case '220':
                 case '221':
                 case '250':
                 case '251':
                 case '354':
                         return true;
                         break;
                 default:
                         return $this->error(4, $input);
                         break;
                 }
         }

Function end()
         {
         if (!$this->sock)
                 return $this->error(3, 'function end');

         fputs($this->sock, "QUIT\r\n");

         if ($this->GetFeedback())
                 {
                 fclose($this->sock);
                 return true;
                 }
         else    {
                 return false;
                 }
         }

} // END OF CLASS...


/*
I wanted a class that could be easily stretched into doing attachements
(intelligently!) wasn't cumbersome, and verified that stuff being flung
at the SMTP server made it.

Something that would allow for intelligent error reporting and would 
reliably fail
if something goes wrong, and do so w/o waiting for the PHP timeout...

This has:

-       Bcc: support - now send the same mail to 100 people w/o sending
         100 emails! (repeatedly use function SetTo())

-       Error code control - If the SMTP server complains, we trap that
         and report the error - so the application can act on it properly.

-       MIME Support is but a small breath away - need to update only the
         BuildBody() function as soon as an effective algorithm is figured out...

-       Broke out alot of the settings functions because I could never remember
         what went where... feel free to create "wrapper" functions to set a bunch
         all at once.

-       Greatly improved header support - though still not fully complete. (MIME
         only partially supported)

-       block read support - to trap the SMTP server replies.

-       connection timeout() should be implemented - but I don't have a slow or
         unreliable mailserver to test it on...

-       We can now send multiple e-mails with a single connection... when a mail
         is sent, you can simply set the fields and send another! This **SHOULD** result
         in better performance when sending alot of emails. This functionality should
         mirror the "reset" SMTP command...

-       Lots of incrememental improvements - loops and functions used to turn
         recycled code into reused code, etc.

-       CC: Support - works just like SetTo() but also writes the header(s)
         in the e-mail body.

CHANGES:

v1.3.1  16 Sep 01  James McGlinn <james@mcglinn.org>
         Fixed bug in routine setting mail from title
v1.3.2  16 Sep 01  James McGlinn <james@mcglinn.org>
         Added new blank line between headers and message text to 
prevent remote
         server from inserting headers into middle of message
*/
?>

