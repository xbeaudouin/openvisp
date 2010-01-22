<?php
	//
	// File: gen-php.php
	//
	// Template : gen-php.tpl
	//
	// Form POST \ GET Variables:
	//
	// fUsername
	// fDomain
	// fPassword
	// fPassword2
	// fName
	// fQuota
	// fActive
	// fSmtpauth
	//

require ("./variables.inc.php");
require ("./config.inc.php");
require ("./lib/functions.inc.php");
require ("./lib/hosting.inc.php");
require ("./lib/fpdf/fpdf.php");
include ("./languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();


if ($_SERVER['REQUEST_METHOD'] == "GET"){

	$fUsername = get_get('username');
	$fDomain   = get_get('domain');
	
	if (check_owner ($SESSID_USERNAME, $fDomain))
		{

			$fType = get_get('type');

			$result = db_query ("SELECT * FROM accounts WHERE username='$SESSID_USERNAME' ");
      if ($result['rows'] == 1)
				{
					$row = db_array ($result['result']);
					$tCompany = $row['company'];
					$tAddress = $row['address'];
					$tPostalcode = $row['postal_code'];
					$tCity = $row['city'];
					$tWebUrl = $row['weburl'];
					$tEmail = $row['email'];
					$tPhone = $row['phone'];
					$tFax = $row['fax'];
					$tLogo = $SESSID_USERNAME.".jpg";
					$tSupportPhone = $row['phonesupport'];
					$tSupportmail = $row['emailsupport']; 
					$tSupportweb = $row['websupport'];
					$tSupportfaq = $row['webfaq'];
				}

			if ( $fType == 'email' ){

				$sql_query = "SELECT mailbox.*, domain.*
FROM mailbox, domain
WHERE mailbox.username='$fUsername'
AND mailbox.domain_id=domain.id
AND domain.domain='$fDomain'";

				$result = db_query ($sql_query);
				if ($result['rows'] > 0)
					{		
						while ($row = db_array ($result['result']))
							{
								$tMailbox[] = $row;
							}
						include ("./templates/gen-pdf.tpl");
					}

			}
	
			if ( $fType == 'domainemail' ){

				$sql_query = "SELECT mailbox.*, domain.*
FROM mailbox, domain
WHERE domain.domain='$fDomain'
AND domain.id=mailbox.domain_id";

				$result = db_query ($sql_query);
				if ($result['rows'] > 0)
					{		
						while ($row = db_array ($result['result']))
							{
								$tMailbox[] = $row;
							}
						include ("./templates/gen-pdf.tpl");
					}


			}

			if ( $fType == 'ftp' ){

				$result = db_query ("SELECT ftpaccount.*, domain.pdf_custadd, whost.vhost FROM ftpaccount, domain, whost WHERE ftpaccount.login='$fUsername' AND ftpaccount.domain_id=domain.id AND domain.domain='$fDomain' AND ftpaccount.whost_id=whost.id");
				if ($result['rows'] == 1)
					{
						$row = db_array ($result['result']);
						$tName = $row['login'];
						$tServername = $row['vhost'].".".$row['domain'];
						$tPassword = $row['password'];
						$tDomain = $row['domain'];
						$tQuotasz = $row['quotasz'];
						$tQuotafs = $row['quotafs'];
						$tRatioul = $row['ratioul'];
						$tRatiodl = $row['ratiodl'];
						$tCustadd = $row['pdf_custadd'];
						$tVserver = $row['server'];
						$tBandwidthul = $row['bandwidthul'];
						$tBandwidthdl = $row['bandwidthdl'];

						include ("./templates/hosting/gen-ftppdf.tpl");

					}

			}

			if ( $fType == 'mysql' ){


/* db_name	dborglinez001 */
/* db_type	mysql */
/* delete	undefined */
/* domain	linez.org */
/* pdf	undefined */
/* server_id	13 */
/* server_name	local */
/* server_port	3306 */
/* type	mysql */

				$fDb_type = get_get('db_type');
				$fDb_name = get_get('db_name');
				$fServer_id = get_get('server_id');
				$fServer_name = get_get('server_name');
				$fServer_ip_id = get_get('server_ip_id');
				$fServer_port = get_get('server_port');

				$list_accounts = list_database_accounts($fServer_id, $fServer_port, $fServer_ip_id, $fDb_name);
				$ip_info = get_ip_info($fServer_ip_id);

				//print "USER : ".$list_accounts[0]['Db']." => ".$list_accounts[0]['User'];
				//$server_info = server_info($fServer_id);
				
						
				include ("./templates/hosting/gen-dbpdf.tpl");

			}

		}
	else
		{
			print "NOOU :".$PALANG['pDelete_domain_error'];
		}

 }

?>