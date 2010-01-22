<?php
//
// File: index.php
//
// Template File: -none-
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// -none-
//
if (!file_exists (realpath ("./config.inc.php"))) {
if (file_exists (realpath ("./setup.php")))
{
   print <<< EOF
<html>
<head>
<title>Welcome to OpenVISP Admin</title>
</head>
<body>
<h1>Welcome to OpenVISP Admin</h1>
It seems that you are running OpenVISP Admin for the first time.<br />
<p />
You can now run <a href="setup.php">setup</a> to make sure that all the
functions are available for OpenVISP Admin to run.<br />
</body>
</html>
EOF;
}
}
else
{
   header ("Location: login.php");
   exit;
}

?>
