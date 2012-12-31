<?php

/**
 * ADMIN
 *
 * Copyright (c) 2004-2012,
 * Association Kazar
 * Xavier BEAUDOUIN
 * Nicolas GORALSKI
 * All right reserved
 *
 * @copyright 2006-2012 Kazar, the authors
 *
 */

/**
 * This class handle all admin relative operations
 * @package admin
 */


class ADMIN
{

  protected $db_link;

  function __construct ($db_link)
  {
    $this->db_link = $db_link;
  }


  function fetch_info()
  {
    $query="";
  }


  //
  // list_admin_accounts()
  // get list of all admin account
  // Call: list_admin_accounts()
  //
  function list_admin_accounts(){

    $query = "SELECT username FROM accounts ORDER BY username";
    $result = $this->db_link->sql_query($query);
    $this->admin_account_list = $result['result'];

  }

  //
  // fetch_admin_rights()
  // get account rights
  // Call: get_account_rights(string username)
  //
  function fetch_admin_rights($username)
  {

    $query = "SELECT * FROM accounts, rights WHERE accounts.username='$username' AND accounts.id=rights.accounts_id";
    $result = $this->db_link->sql_query($query);
    $this->account_rights = $result['result'][0];

  }


  //
  // info()
  // get account informations
  // Call: get_account_info(string username)
  //
  function info($username)
  {

    $query = "SELECT * FROM accounts WHERE username='$username'";
    $result = $this->db_link->sql_query($query);
    $this->account_info = $result['result'][0];

  }



} // END class 

?>