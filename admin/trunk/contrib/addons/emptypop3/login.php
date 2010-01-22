<?
 // This file will ask people login and password 
 // and will empty all the pop3 data inside.
 // Usefull for people that have problems with their 
 // mail boxes that cannot clean theirselves their mailboxes.
@header ("Expires: Sun, 16 Mar 2003 05:00:00 GMT");
@header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
@header ("Cache-Control: no-store, no-cache, must-revalidate");
@header ("Cache-Control: post-check=0, pre-check=0", false);
@header ("Pragma: no-cache");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset='iso-8859-1'" />
<title>OpenVISP Admin - Mailbox Cleaner</title>
</head>
<body>
<?

function escape_string ($string)
{
   if (get_magic_quotes_gpc () == 0)
   {
      $search = array ("/'/", "/\"/", "/;/");
      $replace = array ("\\\'", "\\\"", "\\;");
      $escaped_string = preg_replace ($search, $replace, $string); 
   }
   else
   {
       $escaped_string = $string;
   }
   return $escaped_string;
}

function get_post ($string)
{
  if (($_SERVER['REQUEST_METHOD'] == "POST") && isset($_POST[$string]))
  {
    return  escape_string($_POST[$string]);
  }
  else
  { 
    return NULL;
  }
}

  if ($_SERVER['REQUEST_METHOD'] == "GET")
  {
 ?>
<center>
<form name="login" method="post">
 Login : <input name="user" type="text"/><br/>
 Password : <input name="password" type="password" /><br/>
 Password (again) : <input name="password2" type="password" /><br/>
 This will <B>DESTROY</B> all your mail in your INBOX. Enter <i>"Understood"</i> in the 
 following box to confirm you have totaly understood what this means for you.
 <input type="text" name="understood" value="No ! I'm not sure ! Please don't delete my mail !!!"/>
 <br/>
 <input type="submit"/>
</form>
</center>
<?
  } 
  if ($_SERVER['REQUEST_METHOD'] == "POST")
  {
  // So, username is your POP3 $username, password is your $password
    $username = get_post('user');
    $password = get_post('password');
    $password2= get_post('password2');
    $understood=strtolower(get_post('understood'));

    if($understood != 'understood') {
      print("You have not understood what your are doing. Exiting !");
      exit;
    }
    
    if($password != $password2) {
      print("Password don't match. Try again !");
      exit;
    }
    
    if (($username == NULL)||($password==NULL)) {
      header("Location: login.php");
      exit;
    }
    
    $cmd = array();
    $cmd[]  = "USER $username\r\n";
    $cmd[]  = "PASS $password\r\n";
    $cmd[]  = "STAT\r\n";
    $cmd[]  = "QUIT\r\n";

// Server is your POP3 server, ie pop3.server.com
// Port is the port number ( should be 110 )
    $server = "pop3.server.com";
    $port = 110;

    $fp  = fsockopen($server, $port);
    if(!$fp)
    {
        print("Error connecting to server $server");
    }
    else
    {
        $ret = fgets($fp, 1024);
        foreach($cmd as $ret)
        {
            fputs($fp,$ret);
            $line = fgets($fp, 1024);
            print($line."<br>");
            if($ret=="STAT\r\n")
            {
                $fields = explode(" ",$line);
                $num_mails = $fields[1];
                for($i=1;$i<=$num_mails;$i++)
                {
                fputs($fp,"DELE $i\r\n");
                $line = fgets($fp, 1024);
                }
            }
        }
        print ("Mailbox is clean now!");
    }
  }
 ?>

</body>
</html>



