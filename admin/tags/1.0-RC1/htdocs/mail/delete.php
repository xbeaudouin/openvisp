<?php
//
// File: delete.php
//
// Template File: message.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fDelete
// fDomain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fDelete = get_get('delete');
   $fDomain = get_get('domain');
	 $domain_policy = get_domain_policy($fDomain);
	 $tMessage = "";
   $tDomain = $fDomain;
	 $overview2 = "YES";
   if (!check_owner ($SESSID_USERNAME, $fDomain))
   {
      $error = 1;
      $tMessage = $PALANG['pDelete_domain_error'] . "<b>$fDomain</b>!</div>";
   }
   else
   {



		 $sql_query = "DELETE FROM alias
WHERE alias.policy_id = ".$domain_policy['id']."
AND alias.address='$fDelete'
";

      $result = db_query ($sql_query);
      if ($result['rows'] != 1)
      {
         $error = 1;
         $tMessage .= $PALANG['pDelete_delete_error'] . "<b>$fDelete</b> (alias)!</div>";
      }
      else
      {
         db_log ($SESSID_USERNAME, $fDomain, "delete alias", $fDelete);
      }
			
			$sql_query = "SELECT * FROM alias where goto like '%$fDelete%'";
      $result = db_query ($sql_query);
      if ($result['rows'] == 1)
      {
				$row = db_array ($result['result']);
				$tAddress = $row['address'];
				$tGoto = $row['goto'];

				$tGoto = str_replace("$fDelete", "", "$tGoto");
				$tGoto = str_replace(",,", "", "$tGoto");

				if ( $tGoto == "" )
						{
								$sql_query = "DELETE FROM alias WHERE address='$tAddress'";
						}
				else
						{
								$sql_query = "UPDATE alias SET goto='$tGoto' WHERE address='$tAddress'";
						}

				$result = db_query ($sql_query);
				if ($result['rows'] != 1)
					{
						$error = 1;
						$tMessage .= $PALANG['pDelete_delete_error'] . "<b>$fDelete</b> (alias)!</div>";
					}
				else
					{
						db_log ($SESSID_USERNAME, $fDomain, "delete alias", $fDelete);
					}

			}

			if ( check_policyhosting() ){

				$result = db_query ("delete from policy where _rcpt='$fDelete'","1", "policyd");
				if ($result['rows'] != 1)
					{
						$error = 1;
						$tMessage .= $PALANG['pDelete_delete_error'] . "<b>$fDelete</b> (policyd)!</div>";
					}
				else
					{
						db_log ($SESSID_USERNAME, $fDomain, "delete mailbox", $fDelete);
					}
					
			}

			$sql_query = "SELECT mailbox.*
FROM mailbox,domain
WHERE mailbox.username='$fDelete'
AND mailbox.domain_id=domain.id
AND domain.domain='$fDomain'";

			$result = db_query ($sql_query);
      if ($result['rows'] == 1)
      {

				$row = db_array($result['result']);

         $result = db_query ("DELETE FROM mailbox WHERE id='".$row['id']."'");
         if ($result['rows'] != 1)
         {
            $error = 1;
            $tMessage .= $PALANG['pDelete_delete_error'] . "<b>$fDelete</b> (mailbox)!</div>";
         }
         else
         {
					 $result = db_query ("SELECT * FROM vacation WHERE mailbox_id='".$row['id']."'");
					 if ( $result['rows'] == 1 ){
						 db_query ("DELETE FROM vacation WHERE mailbox_id='".$row['id']."'");
					 }
					 db_log ($SESSID_USERNAME, $fDomain, "delete mailbox", $fDelete);
         }
      }
   }

   if ($error != 1)
   {
      header ("Location: overview.php?domain=$fDomain");
      exit;
   }

   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}
?>
