<?php
//
// File: update.php
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/versions.inc.php");
include ("../languages/" . check_language () . ".lang");

$dir= "./";
 
$versionfrom = versionfrom();
$last_sqlquery = lastquery_version();
$versionto   = $version;

$filelist = array();
$i = 0;
$ok = 0;
$from = '^'.$versionfrom;
$to = $versionto.'\.sql$';

// TODO: Add a check if db is allready up to date.

# Recupere liste des fichiers
$filesindir = array();
$handle = opendir($dir);
while (($file = readdir($handle)) !== false) 
{
    if (eregi ('\.sql$',$file)) $filesindir[]=$file;
}
sort($filesindir);
 
# Determine les fichiers SQL a passer
print var_dump($filesindir);
foreach($filesindir as $file)
{
	if (eregi($from,$file))
	{
		$filelist[]=$file;
		// Mettre from avec valeur fin de $file
	}
	else if (eregi($to,$file))
	{
		$filelist[]=$file;
	}
}
print  var_dump($filelist);

# Boucle sur chaque fichier
foreach($filelist as $file)
{
	print 'File : '.$file.'<br/>';
	
	$name = substr($file, 0, strlen($file) - 4);
	$buffer = '';
	$arraysql = Array();
	$fp = fopen($dir.$file,"r");
	if ($fp)
	{
		while (!feof ($fp))
		{
			$buf = fgets($fp, 4096);
					
			// Ajout ligne si non commentaire
			if ((! eregi('^--',$buf)) && (! eregi('^#',$buf)))  $buffer .= $buf;
			//          print $buf.'<br>';

			if (eregi(';',$buffer))
			{
				// Found new request
				$arraysql[$i]=trim($buffer);
				$i++;
				$buffer='';
			}
		}
				
		if ($buffer) $arraysql[$i]=trim($buffer);
		fclose($fp);
	}
	
	// Loop on each request
	foreach($arraysql as $i=>$sql)
	{
		if ($sql && ($i+1) > $last_sqlquery)
		{
			// Ajout trace sur requete (eventuellement à commenter si beaucoup de requetes)
			print('upgrade: Request'.' '.($i+1)." sql='".$sql."\n<br/><br/>");
			$result = db_query($sql);
			if ( $result['result'] != FALSE ){ increase_query( ($i+1) ); }
		}
	}
        
	// update the version
	update_db_vers();

}

// Here we will remake all foreign keys
$file = "fk.sql";
$name = substr($file, 0, strlen($file) - 4);
$buffer = '';
$arraysql = Array();
$fp = fopen($dir.$file,"r");
if ($fp)
	{
		while (!feof ($fp))
			{
				$buf = fgets($fp, 4096);
				
				// Ajout ligne si non commentaire
				if ((! eregi('^--',$buf)) && (! eregi('^#',$buf)))  $buffer .= $buf;
			//          print $buf.'<br>';
				
				if (eregi(';',$buffer))
					{
						// Found new request
						$arraysql[$i]=trim($buffer);
						$i++;
						$buffer='';
					}
			}
		
		if ($buffer) $arraysql[$i]=trim($buffer);
		fclose($fp);
	}

// Loop on each request
foreach($arraysql as $i=>$sql)
	{
		if ($sql)
			{
				// Ajout trace sur requete (eventuellement à commenter si beaucoup de requetes)
				print('upgrade: Request'." sql='".$sql."\n<br/><br/>");
				$result = db_query($sql,0);
			}
	}


// TODO: redirect to login


?>
