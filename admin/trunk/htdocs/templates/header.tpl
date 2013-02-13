<?php
@header ("Expires: Sun, 16 Mar 2003 05:00:00 GMT");
@header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
@header ("Cache-Control: no-store, no-cache, must-revalidate");
@header ("Cache-Control: post-check=0, pre-check=0", false);
@header ("Pragma: no-cache");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php print isset($PALANG['charset']) ? $PALANG['charset'] : 'iso-8859-1' ?>" />
    <?php
      if (file_exists (realpath ("./stylesheet.css"))) print "<link rel=\"stylesheet\" href=\"stylesheet.css\">\n";
      if (file_exists (realpath ("../stylesheet.css"))) print "<link rel=\"stylesheet\" href=\"../stylesheet.css\">\n";
    ?>
    <title>OpenVISP Admin</title>
  </head>

  <body <?php if (isset($body_class)) print $body_class; ?>>
  <?php
    if ( $ova_info->ova_session_is_registered("sessid") ){
      if ( $CONF['YUI_DEBUG'] == "YES" ){
        print load_js ("../lib/yui/yui/yui.js");
      }
      else{
        print load_js ("../lib/yui/yui/yui-min.js");
        //print '<script src="http://yui.yahooapis.com/3.5.0pr2/build/yui/yui-min.js" type="text/javascript"></script>';
      }

  ?>


  <div id="menu-1" class="yui3-menu yui3-menu-horizontal">



    <div class="yui3-menu-content">
      <ul>

        <!-- Welcome Menu -->
        <li class="yui3-menuitem">
          <a class="yui3-menuitem-content" href="<?php  print $_SESSION['absoluteuri']."users/main.php"; ?>"><?php print $PALANG['pYMenu_welcome'];?></a>
        </li>
      
        <!-- Domain Menu -->
<?php
      if ( $user_info->check_domain_admin("0") ){
?>
        <li>
          <a class="yui3-menu-label" href="#"><?php print $PALANG['pYMenu_domain'];?></a>

          <div id="pim" class="yui3-menu">
            <div class="yui3-menu-content">

              <ul>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri'];?>users/list-domain.php""><?php print $PALANG['pWhostMenu_domain'];?></a>
                </li>

<?php
      $domain_overquota = 1;
        if ( ( $user_info->data_managed['domains'] < $user_info->data_quota['domains'] ) || $user_info->data_quota['domains'] == "-1" ) {
          $domain_overquota = 0;
?>
                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri'];?>domain/create-domain.php"><?php print $PALANG['pAdminMenu_create_domain'];?></a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri'];?>domain/create-domain-alias.php"><?php print $PALANG['pAdminMenu_create_domain_alias'];?></a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri'];?>domain/import-domain.php"><?php print $PALANG['pAdminMenu_import_domain'];?></a>
                </li>

<?php
        } //( ( $user_info->data_managed['domains'] < $user_info->data_quota['domains'] ) || $user_info->data_quota['domains'] == "-1" ) {

?>

              </ul>

            </div>
          </div>

        </li>

<?php
      } // if ( $user_info->check_domain_admin("0") ){

      if ( $user_info->check_mail_admin("0") ){
?>

        <!-- Mail Menu -->

        <li>
          <a class="yui3-menu-label" href=""><?php print $PALANG['pYMenu_mail'];?></a>
          <div id="pim" class="yui3-menu">
            <div class="yui3-menu-content">
              <ul>
                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri'];?>mail/overview.php"><?php print $PALANG['pYMenu_mail_overview'];?></a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href=""><?php print $PALANG['pYMenu_mail_domain_details'];?></a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri'];?>mail/create-mailbox.php"><?php print $PALANG['pYMenu_mail_add_mbox'];?></a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href=""><?php print $PALANG['pYMenu_mail_add_alias'];?></a>
                </li>

                <li class="yui3-menuitem">
                <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri'];?>mail/massive_import.php"><?php print $PALANG['pYMenu_mail_import'];?></a>
                </li>

                <li class="yui3-menuitem">
                <a class="yui3-menuitem-content" href=""><?php print $PALANG['pYMenu_mail_pdf'];?></a>
                </li>

                <li class="yui3-menuitem">
                  <?php print_yahoo_menu("sendmail.php", $PALANG['pMenu_sendmail']); ?>
                </li>

              </ul>

              <ul>

                <li class="yui3-menuitem">
                  <?php print_yahoo_menu("sendmail.php", $PALANG['pYMenu_mail_filter']); ?>
                </li>

                <li class="yui3-menuitem">
                  <?php print_yahoo_menu("sendmail.php", $PALANG['pYMenu_mail_ooo']); ?>
                </li>

                <li class="yui3-menuitem">
                  <?php print_yahoo_menu("sendmail.php", "Forward"); ?>
                </li>

              </ul>
            </div>
          </div>
        </li>

<?php
      } //  if ( $user_info->check_mail_admin("0") ){

      if ( $user_info->check_hosting_admin("0") ){
?>

        <!-- Hosting Menu -->

        <li>
          <a class="yui3-menu-label" href="">Hosting</a>
          <div id="pim" class="yui3-menu">
            <div class="yui3-menu-content">
              <ul>
                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href=""><?php print $PALANG['pYMenu_hosting_overview'];?></a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href=""><?php print $PALANG['pYMenu_hosting_domain_details'];?></a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href=""><?php print $PALANG['pYMenu_hosting_add_www'];?></a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href=""><?php print $PALANG['pYMenu_hosting_add_ftp'];?></a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href=""><?php print $PALANG['pYMenu_hosting_add_db'];?></a>
                </li>

                <li class="yui3-menuitem">
                <a class="yui3-menuitem-content" href=""><?php print $PALANG['pYMenu_hosting_import'];?></a>
                </li>

                <li class="yui3-menuitem">
                <a class="yui3-menuitem-content" href=""><?php print $PALANG['pYMenu_hosting_pdf'];?></a>
                </li>

                <li class="yui3-menuitem">
                  <?php print_yahoo_menu("sendmail.php", $PALANG['pMenu_sendmail']); ?>
                </li>


              </ul>
            </div>
          </div>
        </li>

<?php
      } // if ( $user_info->check_hosting_admin("0") ){

      if ( $user_info->check_datacenter_admin("0") ){
?>


        <!-- Datacenter Menu -->

        <li>
          <a class="yui3-menu-label" href="<?php print $_SESSION['absoluteuri']."mail/overview.php";?>">Datacenter</a>
          <div id="pim" class="yui3-menu">
            <div class="yui3-menu-content">
              <ul>
                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href="">Datacenter Overview</a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href="">Tickets</a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href="">APC</a>
                </li>

                <li class="yui3-menuitem">
                  <a class="yui3-menuitem-content" href="">Stats</a>
                </li>

              </ul>
            </div>
          </div>
        </li>

<?php
      } // if ( $user_info->check_datacenter_admin("0") ){

      if ( $user_info->check_ova_admin("0") ){
?>
        <!-- OVA Menu-->                                                                                                                                                                           
   
        <li>
          <a class="yui3-menu-label" href="#"><?php print $PALANG['pYMenu_ova'];?></a>
          <div id="pim" class="yui3-menu">
            <div class="yui3-menu-content">
              <ul>

                <li class="yui3-menuitem">
                  <?php  print_yahoo_menu("users/viewlog.php", $PALANG['pMenu_viewlog']); ?>
                </li>

                <li class="yui3-menuitem">
                  <?php  print_yahoo_menu("accounts/list-accounts.php", "Users Overviews"); ?>
                </li>

                <li class="yui3-menuitem">
                  <?php  print_yahoo_menu("users/viewlog.php", "Your Account"); ?>
                </li>

                <li class="yui3-menuitem">
                  <?php  print_yahoo_menu("accounts/add-account.php",$PALANG['pAccountMenu_add_account']); ?>
                </li>

              </ul>
            </div>
          </div>
        </li>

<?php
      } // if ( $user_info->check_ova_admin("0") ){

      if ( $user_info->username != "admin@ova.local" ){

?>
        <li><span style="padding-left:240px"></span>
        </li>

        <li>
          <form>
            Select a domain : <input type="text" class="menu_domain_input" id="DomainSearch" value="Search">
          </form><!--  value="domain.fr"-->
        </li>
        
        <li><span style="padding-left:30px"></span>
        </li>
        <li>
          <span id="CurDomain">Current domain : <?php if (isset($_SESSION['sessid']['wdomain'])){print $_SESSION['sessid']['wdomain'];} ?></span>
        </li>

<?php
      } // if ( $user_info->username != "admin@ova.local" ){

?>        
        
      </ul>
    </div>
  </div>

  <?php

    } // if ( $ova->session_is_registered("sessid") ){


/*
    $acDomain_info = array(
      "url" => $_SESSION['absoluteuri']."ajax/domain/domain_search.php?domain={query}",
      "urlparam" => ,
      "method" => "POST",
      "name" => "Domain",
      "resultField" => "domain",
    );
*/

print "<center>\n";

if ( (isset($SESSID_USERNAME)) && ($user_info->username != "admin@ova.local") ){



  print "USER OVERVIEW QUOTA STATS <br/>\n";

  if ( $user_info->rights['manage'] == 1 ){

    print "Domain ".$user_info->data_managed['domains']."/&infin;, ";
    print "Mailboxes ".$user_info->data_managed['mailboxes']."/&infin;, ";
    print "Aliases ".$user_info->data_managed['aliases']."/&infin;, ";
    print "WebHost ".$user_info->data_managed['web_host']."/&infin;, ";
    print "FTP ".$user_info->data_managed['ftp_account']."/&infin;, ";
    print "DB ".($user_info->data_managed['mysql_db']+$user_info->data_managed['pgsql_db'])."/&infin;, ";
    print "DB Users ".($user_info->data_managed['mysql_user']+$user_info->data_managed['pgsql_user'])."/&infin;, ";
 
  }
  else{

    print "Domain  ".$user_info->total_managed_domain."/".$user_info->data_quota['domains']."\n";
    print "Mailbox ";

  }


  //Storage 20/80G,
  print "<br/>
  <hr>";
}

?>
