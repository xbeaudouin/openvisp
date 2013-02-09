<?php

class OVA
{

  protected $db_link;

  function __construct ($db_link)
  {
    $this->db_link = $db_link;
  }


  //
  // fetch_running_version
  // Action: fetch from database the OVA version
  // Call: fetch_running_version()
  //

  function fetch_running_version(){
    $query = "SELECT * FROM ovavers";
    $this->sql_result = $this->db_link->sql_query($query);
    $this->running_version = $this->sql_result['result'][0]['ova'];
    $this->running_db = $this->sql_result['result'][0]['query'];
  }


  // fetch_latest_version
  // Action: fetch the latest version installed on disk
  // Call: fetch_latest_version

  function fetch_latest_version(){
    $fp = fopen($this->directory_update."latest_version.txt","r");
    if ($fp)
      {
        $buf = "";
        while (!feof ($fp))
          {
            $buf .= fgets($fp, 4096);
          }
        fclose($fp);
        $this->latest_version = chop($buf);
      }
  }

  function fetch_latest_sql(){

    $handle = opendir($this->directory_update);
    $this->sql_files = array();
    while (($file = readdir($handle)) !== false) 
      {
        
        if ( (preg_match('/([0-9]*)\.sql$/',$file,$result_match)) && (preg_replace("/.sql/","",$result_match[0]) > $this->running_db) ) $this->sql_files[]=$result_match[0];

      }
    sort($this->sql_files);
    $this->sql_files[]="fk.sql";

  }

  function show_latest_sql(){

    foreach($this->sql_files as $file)
      {
        print 'File : '.$file.'<br/>';
      }

  }


  function update_version($query){
    $query = "UPDATE ovavers SET query=$query";
    $this->sql_result = $this->db_link->sql_query($query);
  }


  function apply_latest_sql(){

    foreach($this->sql_files as $file)
      {
        print 'File : '.$file.'<br/>';
  
        $name = substr($file, 0, strlen($file) - 4);
        $buffer = '';
        $arraysql = Array();
        $fp = fopen($this->directory_update.$file,"r");
        if ($fp)
          {
            while (!feof ($fp))
              {
                $buf = preg_replace('~[[:cntrl:]]~', '', fgets($fp, 4096));
                
                // Ajout ligne si non commentaire
                if ((! eregi('^--',$buf)) && (! eregi('^#',$buf)) && (! eregi('^ ',$buf)) && (! eregi('^\n\r',$buf))){
                  $buffer .= $buf." ";
                }
                //          print $buf.'<br>';
                
                
                if (eregi(';',$buffer))
                  {
                    // Found new request
                    $arraysql[]=trim($buffer);
                    $buffer='';
                  }
              }
            if ($buffer && !empty($buffer)) $arraysql[]=trim($buffer);
            fclose($fp);
          }

        foreach($arraysql as $sql)
          {

            $this->sql_result = $this->db_link->sql_query($sql,2);
            if ( $this->sql_result['return_code'] != 200 ){
              print('upgrade: Failed to execute SQL request : '.$sql."\n<br/><br/>");
              die('upgrade: Failed to execute SQL request : '.$sql."\n<br/><br/>");
            }
          }

        if ( ($this->sql_query['return_code'] != 200) && ($file != "fk.sql") ){
          $this->update_version(preg_replace("/.sql/","",$file));
        }

      }

  }


  function do_log ($domain_id,$action,$data, $domain2=""){
    global $CONF;
    global $user_info;

    $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];


    if ( $CONF['logging'] == 'YES' ) {

        $query = "INSERT INTO log (accounts_id, domain_id, domain_name, ip, action, data)
        VALUES ('".$user_info->data['id']."','".$domain_id."', '$domain2', '$REMOTE_ADDR','$action','$data')";
        $result = $this->db_link->sql_query($query);
   
    }

  }


  //
  // generate_password
  // Action: Generates a random password
  // Call: generate_password ()
  //
  function generate_password (){
    global $CONF;

    if ($CONF['generate_password'] == "YES"){
      if ($CONF['password_generator'] == ""){ $password = substr (md5 (mt_rand ()), 0, $CONF['generate_password_length']);  }
      else {  $password = exec($CONF['password_generator']); }
    }

    return $password;

  }

  //
  // debug_info()
  // Action: print some debug info on the stderr
  // call: debug_info(message);
  //
  function debug_info ($string){
    file_put_contents('php://stderr', "DEBUG OVA DEV : $string\n");
  }


  //
  // pacrypt
  // Action: Encrypts password based on config settings
  // Call: pacrypt (string cleartextpassword)
  //
  function pacrypt ($pw, $pw_db=""){
    global $CONF;
    $password = "";
    $salt = "";

    if ($CONF['encrypt'] == 'md5crypt'){
      $split_salt = preg_split ('/\$/', $pw_db);
      if (isset ($split_salt[2])) $salt = $split_salt[2];

      $password = md5crypt ($pw, $salt);
    }

    if ($CONF['encrypt'] == 'md5'){
      $password = md5($pw);
    }

    if ($CONF['encrypt'] == 'system'){
      if (ereg ("\$1\$", $pw_db)){
         $split_salt = preg_split ('/\$/', $pw_db);
         $salt = $split_salt[2];
      }
      else{
         $salt = substr ($pw_db, 0, 2);
      }
      $password = crypt ($pw, $salt);
    }

    if (($CONF['encrypt'] == 'cleartext') || ($CONF['encrypt'] == 'clear'))
    {
      $password = $pw;
    }

    return $password;
  }


  //
  // getabsoluteuri()
  // Action: get the absolute uri from session
  // return: the absolute uri
  //
  function getabsoluteuri() {
    if (isset($_SESSION['absoluteuri'])) {
      return $_SESSION['absoluteuri'];
    } else { 
    // Usualy we should have an index.php that
    // redirect to the right file even if there is
    // several redirect. This is ugly, but it should work.
      header ("Location: index.php");
      print ("Session is expired. Please login again.\n");
      $this->debug_info("Session is expired. Please login again");
      exit;
    }
  }

  //
  // stabsoluteuri
  // Action: Heavyly inspired from phpMyadmin PmaAbsoluteUri(), permit to find the 
  // absolute URI of where is located OpenVISP Admin.
  // return: the absolute URI
  //
  function setabsoluteuri() {
    global $CONF;
          // Setup a default value to let the people and lazy sysadmin work anyway,
          // they'll get an error if autodetect code don't work
          $absolute_uri = $CONF['baseurl'];
          $is_https = OVA_ishttps();

          if (strlen($absolute_uri) < 5
              // needed to catch http/https switch
              || ($is_https && substr($absolute_uri, 0, 6) != 'https:')
              || (!$is_https && substr($absolute_uri, 0, 5) != 'http:')
          ) {
              $url = array();

              // At first we try to parse REQUEST_URI, it might contain full URL
              if (OVA_getenv('REQUEST_URI')) {
                  $url = @parse_url(OVA_getenv('REQUEST_URI')); // produces E_WARNING if it cannot get parsed, e.g. '/foobar:/'
                  if ($url === false) {
                      $url = array( 'path' => $_SERVER['REQUEST_URI'] );
                  }
              }

              // If we don't have scheme, we didn't have full URL so we need to
              // dig deeper
              if (empty($url['scheme'])) {
                  // Scheme
                  if (OVA_getenv('HTTP_SCHEME')) {
                      $url['scheme'] = OVA_getenv('HTTP_SCHEME');
                  } else {
                      $url['scheme'] =
                          OVA_getenv('HTTPS') && strtolower(OVA_getenv('HTTPS')) != 'off'
                              ? 'https'
                              : 'http';
                  }

                  // Host and port
                  if (OVA_getenv('HTTP_HOST')) {
                      if (strpos(OVA_getenv('HTTP_HOST'), ':') !== false) {
                          list($url['host'], $url['port']) =
                              explode(':', OVA_getenv('HTTP_HOST'));
                      } else {
                          $url['host'] = OVA_getenv('HTTP_HOST');
                      }
                  } elseif (OVA_getenv('SERVER_NAME')) {
                      $url['host'] = OVA_getenv('SERVER_NAME');
                  } else {
                      //$this->error_pma_uri = true;
                      return false;
                  }

                  // If we didn't set port yet...
                  if (empty($url['port']) && OVA_getenv('SERVER_PORT')) {
                      $url['port'] = OVA_getenv('SERVER_PORT');
                  }

                  // And finally the path could be already set from REQUEST_URI
                  if (empty($url['path'])) {
                      if (OVA_getenv('PATH_INFO')) {
                          $path = parse_url(OVA_getenv('PATH_INFO'));
                      } else {
                          // PHP_SELF in CGI often points to cgi executable, so use it
                          // as last choice
                          $path = parse_url(OVA_getenv('PHP_SELF'));
                      }
                      $url['path'] = $path['path'];
                  }
              }

              // Make url from parts we have
              $absolute_uri = $url['scheme'] . '://';
              // Was there user information?
              if (!empty($url['user'])) {
                  $absolute_uri .= $url['user'];
                  if (!empty($url['pass'])) {
                      $absolute_uri .= ':' . $url['pass'];
                  }
                  $absolute_uri .= '@';
              }
              // Add hostname
              $absolute_uri .= $url['host'];
              // Add port, if it not the default one
              if (! empty($url['port'])
                && (($url['scheme'] == 'http' && $url['port'] != 80)
                  || ($url['scheme'] == 'https' && $url['port'] != 443))) {
                  $absolute_uri .= ':' . $url['port'];
              }
              // And finally path, without script name, the 'a' is there not to
              // strip our directory, when path is only /pmadir/ without filename.
              // Backslashes returned by Windows have to be changed.
              // Only replace backslashes by forward slashes if on Windows,
              // as the backslash could be valid on a non-Windows system.
              //if ($this->get('PMA_IS_WINDOWS') == 1) {
              //    $path = str_replace("\\", "/", dirname($url['path'] . 'a'));
              //} else {
                  $path = dirname($url['path'] . 'a');
              //}

              // To work correctly within transformations overview:
              //if (defined('PMA_PATH_TO_BASEDIR') && PMA_PATH_TO_BASEDIR == '../../') {
              //    if ($this->get('PMA_IS_WINDOWS') == 1) {
              //        $path = str_replace("\\", "/", dirname(dirname($path)));
              //    } else {
              //        $path = dirname(dirname($path));
              //    }
              //}
              // in vhost situations, there could be already an ending slash
              if (substr($path, -1) != '/') {
                  $path .= '/';
              }
              $absolute_uri .= $path;

              // We used to display a warning if PmaAbsoluteUri wasn't set, but now
              // the autodetect code works well enough that we don't display the
              // warning at all. The user can still set PmaAbsoluteUri manually.
              // See
              // http://sf.net/tracker/?func=detail&aid=1257134&group_id=23067&atid=377411

          } else {
              // The URI is specified, however users do often specify this
              // wrongly, so we try to fix this.

              // Adds a trailing slash et the end of the phpMyAdmin uri if it
              // does not exist.
              if (substr($absolute_uri, -1) != '/') {
                  $absolute_uri .= '/';
              }

              // If URI doesn't start with http:// or https://, we will add
              // this.
              if (substr($absolute_uri, 0, 7) != 'http://'
                && substr($absolute_uri, 0, 8) != 'https://') {
                  $absolute_uri =
                      (OVA_getenv('HTTPS') && strtolower(OVA_getenv('HTTPS')) != 'off'
                          ? 'https'
                          : 'http')
                      . ':' . (substr($absolute_uri, 0, 2) == '//' ? '' : '//')
                      . $absolute_uri;
              }
          }

          return $absolute_uri;
  }


  //
  // redirect_login()
  // Action: redirect to login page and exit
  // call: redirect_login();
  //
  function redirect_login(){
    global $CONF;
    header ("Location: ".getabsoluteuri()."/login.php");
    exit;
  }

  //
  // redirect_logout()
  // Action: redirect to login page and exit
  // call: redirect_logout();
  //
  function redirect_logout(){
     // $path = getrelativepath(dirname(__FILE__));   
     header ("Location: ".getabsoluteuri()."/logout.php");
     exit;
  }

  // 
  // crsf_key
  // Action: create a key from php session to avoid CSRF problems
  // call: crsf_key("page.php")
  //
  function crsf_key($page){
    return md5crypt(session_id(), $page, "php");
  }

  //
  // check_session
  // Action: Check if the current session is ok, else create it.
  // Call: check_session
  //
  function check_session(){
 
    if(!isset($_SESSION)) {
      session_start ();
    }

    if ( ! $this->ova_session_is_registered("sessid")){
      $this->debug_info("Session not registered redirect to login ");
      $this->redirect_login();
      exit;
    }

    $SESSID_USERNAME = $_SESSION['userid']['username'];
    return $SESSID_USERNAME;
  }

  //
  // ova_session_register
  // Action: register the ova session
  // Call: ova_session_register(variable_name)
  //
  function ova_session_register($variable) {
    //global $session_started;
    //$this->debug_info("SESSION STARTED #$session_started#");
    //if ($session_started == true) {
      if (isset($GLOBALS[$variable])) {
        $_SESSION[$variable] =& $GLOBALS[$variable];
      } else {
        $_SESSION[$variable] = null;
      }
    //}
    //return false;
  }

  function ova_session_is_registered($variable) {
    return isset($_SESSION) && array_key_exists($variable, $_SESSION);
  }

  function ova_session_unregister($variable) {
    unset($_SESSION[$variable]);
  }


}

?>