<?php
//
// File: routers2.inc.php
//
// Do some routers2 stuff

if (ereg ("routers2.inc.php", $_SERVER['PHP_SELF']))
{
   header ("Location: login.php");
   exit;
}

//
// routers2_get_img_url
// Call: routers_get_img_url(string cfgfile, string port, string what)
// Return: return the good url from routers2 to get the image.
//         $what should be
//         "d" = day
//         "w" = week
//         "m" = month
//         "y" = month
//
function routers2_get_img_url($graphid, $port) {
  global $CONF;
  
  $url  = $CONF['datacenter_routers2_url'] . "?rtr=".$cfgfile;
  $url .= "&xgtype=".$what."&page=image&xgstyle=l2&if=".$port;

  return $url;
}

// routers2_get_img_url_small
// Call: routers2_get_img_url_small(string cfgfile, string port)
// Return: the good url from routers2 to get a small day image
function routers2_get_img_url($graphid, $port) {
  global $CONF;
  
  $url  = $CONF['datacenter_routers2_url'] . "?rtr=".$cfgfile;
  $url .= "&xgtype=d&page=image&xgstyle=s&if=".$port;

  return $url;
}

?>
