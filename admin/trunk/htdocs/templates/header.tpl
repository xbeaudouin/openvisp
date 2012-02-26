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
  if ( session_is_registered ("sessid") ){
		if ( $CONF['YUI_DEBUG'] == "YES" ){
			print load_js ("../lib/yui/yui/yui.js");
		}		
		else{
			print load_js ("../lib/yui/yui/yui-min.js");
		}

?>


<div id="menu-1" class="yui3-menu yui3-menu-horizontal">
  <div class="yui3-menu-content">
    <ul>
      <li class="yui3-menuitem">
        <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri']."users/main.php"?>">
          <?php print $PALANG['pYMenu_welcome'];?>
        </a>
      </li>

      <?php
    if(check_mail_admin($SESSID_USERNAME,"1")){
    ?>
    <li>
      <a class="yui3-menu-label" href="<?php print $_SESSION['absoluteuri']."mail/overview.php";?>"><?php print $PALANG['pYMenu_mail'];?></a>
      <div id="pim" class="yui3-menu">
        <div class="yui3-menu-content">
          <ul>
            <li class="yui3-menuitem">
              <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri']."mail/overview.php";?>"><?php print $PALANG['pYMenu_mail'];?></a>
            </li>

		<?php

		if ( $fDomain != NULL){

			if( $domain_info->can_add_mailbox() ){
				?>
            <li class="yui3-menuitem">
              <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri']."mail/import-mailbox.php?domain=$fDomain";?>"><?php print $PALANG['pMenu_import_mailbox'];?></a>
            </li>
            <li class="yui3-menuitem">
              <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri']."mail/create-mailbox.php?domain=$fDomain";?>"><?php print $PALANG['pMenu_create_mailbox'];?></a>
            </li>
				<?php
			}
			else {
				print $PALANG['pOverview_no_add_mailboxes'];
			}


		}
		?>

              <a class="yui3-menuitem-content" href="<?php print $_SESSION['absoluteuri']."users/massive_import.php";?>"><?php print $PALANG['pMenu_massive_import'];?></a>
            </li>
            <li class="yui3-menuitem">
              <a class="yui3-menuitem-content" href="#">--</a>
            </li>
            <li class="yui3-menuitem">
              <?php print_yahoo_menu("sendmail.php", $PALANG['pMenu_sendmail']); ?>
            </li>
          </ul>
        </div>
      </div>
    </li>
    <?php
    }



    ?>


    
    <li>
      <a class="yui3-menu-label" href="#"><?php print $PALANG['pYMenu_ova'];?></a>
      <div id="pim" class="yui3-menu">
        <div class="yui3-menu-content">
          <ul>
            <li class="yui3-menuitem">
              <?php  print_yahoo_menu("users/viewlog.php", $PALANG['pMenu_viewlog']); ?>
            </li>
          </ul>
        </div>
      </div>
    </li>

		<li><form> Domain : <input type="text"></form>
    </li>

  	<?php
  	
    ?>
  </ul>
</div>
</div>

<?php

}

?>


<center>
