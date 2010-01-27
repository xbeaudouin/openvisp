#include "ovp.h"


/*
 *
 *
 *                           Policy Daemon
 *
 *  policy daemon is used in conjuction with postfix to combat spam.
 *
 *  Copyright (C) 2004 Cami Sardinha (cami@mweb.co.za)
 *
 *
 *  This program is free software; you can redistribute it and/or modify it
 *  under the terms of the  GNU General  Public License as published by the
 *  Free Software Foundation;  either version 2 of the License, or (at your
 *  option) any later version.
 *
 *  This program  is  distributed  in the hope that  it will be useful, but
 *  WITHOUT  WARRANTY; without even the implied warranty of MERCHANTABILITY
 *  or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 *  for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free  Software Foundation Inc.,
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *
 *
 */

/*
 * function: w_mysql_query
 *  purpose: wrapper function for mysql_query()
 *   return: 0=success, -1=failure
 */
int
w_mysql_query(unsigned int volatile fd, const char *function)
{

  /* cut down latency, do not do any queries for 30 seconds if 3 failures occur */
  if(mysql_failure_count >= 3)
  {

    /* has 30 seconds passed? */
    if(timenow >= (last_mysql_failure+30))
    {                                 /* timer has expired     */
      mysql_failure_count=0;
      mysql_close(mysql);
      mysql = db_connect(MYSQLDBASE); /* reconnect to database */
    } else {                          /* timer has not expired */

      return (-1);                    /* so we fail the query  */
    }
  }

  /* catch query timeouts */
  if (sigsetjmp (sjmp, 1))
  {
    if(DEBUG > 2)
      logmessage("%s()/mysql_query(): mysql_query timeout number: %d\n",
        function, mysql_failure_count);
    
    mysql_failure_count++;
    last_mysql_failure=timenow;

    /* reset the timer */
    alarm (0);

    /* unset the signal handler */
    signal (SIGALRM, SIG_DFL);

    mysql_array[fd][0] = -1;
    return (-1); /* failure */
  }

  /* set the SIGALRM handler */
  signal (SIGALRM, (void *) sigalrm_handler);
  
  /* mysql timeout */
  alarm(mysql_timeout);
  
  /* fire off query */
  if (mysql_query(mysql, mysqlquery_array[fd]) != 0)
  {
    /* reset the timer */
    alarm (0);

    /* unset the signal handler */
    signal (SIGALRM, SIG_DFL);

    return (-1);
  }
  
  /* reset the timer */
  alarm (0);

  /* unset the signal handler */
  signal (SIGALRM, SIG_DFL);

  /* ensure that we only go into bypass mode after 3 consecutive failures */
  mysql_failure_count=0;

  return (0); /* success */
}




/*
 * function: db_doquery
 *  purpose: do mysql queries
 *   return: 0=success, -1=failure
 */
int
db_doquery(unsigned int volatile fd)
{
  
  if(DEBUG > 1)
    logmessage("DEBUG: fd: %d db_doquery(): %s\n", fd, mysqlquery_array[fd]);

  /* fire off query */
  if (w_mysql_query(fd, "db_doquery") != 0)
    goto err;

  /* select() result */
  if (mysql_field_count(mysql) > 0)
  {
    int num_fields, i=0;
    MYSQL_RES   *res;
    MYSQL_ROW    row, end_row;

    if (!(res = mysql_store_result(mysql)))
      goto err;
    
    num_fields = mysql_num_fields(res);
    while ((row = mysql_fetch_row(res)))
    {
      for (end_row=row+num_fields;row<end_row;++row,i++)
      {
        if(DEBUG > 1)
          logmessage("DEBUG: fd: %d row: %d data: %d (recieved)\n", fd, i, atol((char *)*row));

        mysql_array[fd][i]=atol(row ? (char*)*row : "0"); /* return seconds from epoch */

        if(DEBUG > 1)
          logmessage("DEBUG: fd: %d row: %d data: %d (extracted)\n", fd, i, mysql_array[fd][i]);
      }
    }
    mysql_free_result(res);
  } else {
    
    /* update() result */
    mysql_array[fd][0]=mysql_affected_rows(mysql);
  }


  return (0); /* success */
  
err:
  
  if(DEBUG > 1)
    logmessage("db_doquery()/mysql_query(): %s -> %s\n", mysql_error(mysql), mysqlquery_array[fd]);

  mysql_array[fd][0] = -1;
  return (-1); /* failure */
}




/*
 * function: db_charquery
 *  purpose: do mysql query
 *   return: 0=success, -1=failure
 *   return: multi-dimensional char array
 */
int
db_charquery(unsigned int volatile fd)
{      

  if(DEBUG > 1)
    logmessage("DEBUG: fd: %d db_charquery(): %s\n", fd, mysqlquery_array[fd]);

  /* fire off query */
  if (w_mysql_query(fd, "db_charquery") != 0)
    goto err;

  /* get rid of stale data */
  for(t=0 ; t<20 ; t++)
    memset(mysqlchar_array[fd][t], 0x00, 64);
 
  /* select() result */
  if (mysql_field_count(mysql) > 0)  
  {
    int num_fields, i=0;
    MYSQL_RES   *res;
    MYSQL_ROW    row, end_row;
  
    if (!(res = mysql_store_result(mysql)))
      goto err;
    
    num_fields = mysql_num_fields(res);
    while ((row = mysql_fetch_row(res)))
    {
      for (end_row=row+num_fields;row<end_row;++row)
      {
        if(DEBUG > 1)
          logmessage("DEBUG: fd: %d row: %d data: %s (recieved)\n", fd, i, (char *)*row);

        strncpy(mysqlchar_array[fd][i], (char*)*row, 64);

        if(DEBUG > 1)
          logmessage("DEBUG: fd: %d row: %d data: %s (extracted)\n", fd, i, mysqlchar_array[fd][i]);
        i++;
      }
    }
    mysql_free_result(res);
  }

  return (0); /* success */
  
err:

  if(DEBUG > 1)
    logmessage("db_charquery()/mysql_query(): %s -> %s\n", mysql_error(mysql), mysqlquery_array[fd]);
  
  mysql_array[fd][0] = -1;
  return (-1); /* failure */
}




/*
 * function: db_optquery
 *  purpose: do mysql opt in/out queries
 *   return: 0=success, -1=failure
 */
int
db_optquery(unsigned int volatile fd)
{
  
  if(DEBUG > 1)
    logmessage("DEBUG: fd: %d, db_optquery(): %s\n", fd, mysqlquery_array[fd]);

  /* fire off query */
  if (w_mysql_query(fd, "db_optquery") != 0)
    goto err;

  /* select() result */
  if (mysql_field_count(mysql) > 0)
  {
    int num_fields, i=0;
    MYSQL_RES   *res;
    MYSQL_ROW    row, end_row;

    if (!(res = mysql_store_result(mysql)))
      goto err;

    num_fields = mysql_num_fields(res);
    while ((row = mysql_fetch_row(res)))
    {
      for (end_row=row+num_fields;row<end_row;++row,i++)
      {
        if(DEBUG > 1)
          logmessage("DEBUG: fd: %d row: %d data: %d (recieved)\n", fd, i, atoi((char *)*row));

        mysql_optarray[fd][0]=atol(row ? (char*)*row : "0");

        if(DEBUG > 1)
          logmessage("DEBUG: fd: %d row: %d data: %d (extracted)\n", fd, i, mysql_optarray[fd][0]);
      }
    }
    mysql_free_result(res);
  }

  return (0); /* success */
  
err:
  
  if(DEBUG > 1)
    logmessage("db_optquery()/mysql_query(): %s -> %s\n", mysql_error(mysql), mysqlquery_array[fd]);
  
  mysql_array[fd][0] = -1;
  return (-1); /* failure */
}




/*
 * function: db_deletequery
 *  purpose: expire triplet in database
 *   return: 0=success, -1=failure
 */
int
db_deletequery(unsigned int volatile fd)
{
  count=0;

start:
  
  if(DEBUG > 1)
    logmessage("DEBUG: fd: %d, db_deletequery(): %s\n", fd, mysqlquery_array[fd]);

  /* fire off query */
  if (w_mysql_query(fd, "db_deletequery") != 0)
    goto err;

  /* select() result */
  if (mysql_field_count(mysql) > 0)
  {
    int num_fields, i=0;
    MYSQL_RES   *res;
    MYSQL_ROW    row, end_row;

    if (!(res = mysql_store_result(mysql)))
      goto err;

    num_fields = mysql_num_fields(res);
    while ((row = mysql_fetch_row(res)))
    {
      for (end_row=row+num_fields;row<end_row;++row,i++)
      {
        logmessage("%ld\n", atol(row ? (char*)*row : "0"));
      }
    }
    mysql_free_result(res);
  } else {
    /* MySQL does not handle extremely large deletes very well */
    if((int)mysql_affected_rows(mysql) == 100000)
    {
      count=count+(int)mysql_affected_rows(mysql);
      goto start;
    }
    
    /* didnt reach 100000 deletes */
    count=count+(int)mysql_affected_rows(mysql);
    
  }
  
  logmessage("expired: %d records\n", count);

  return (0); /* success */
  
err:

  if(count != 0)
    logmessage("expired: %d records\n", count);

  if(DEBUG > 1)
    logmessage("db_deletequery()/mysql_query(): %s -> %s\n", mysql_error(mysql), mysqlquery_array[fd]);
  
  mysql_array[fd][0] = -1;
  return (-1); /* failure */
}



/*
 * function: db_deletequery
 *  purpose: expire triplet in database
 *   return: 0=success, -1=failure
 */
int
db_printquery(unsigned int volatile fd)
{
  if(DEBUG > 1)
    logmessage("DEBUG: fd: %d, db_printquery(): %s\n", fd, mysqlquery_array[fd]);

  /* fire off query */
  if (w_mysql_query(fd, "db_printquery") != 0)
    goto err;

  /* select() result */
  if (mysql_field_count(mysql) > 0)
  {
    int num_fields;
    MYSQL_RES   *res;
    MYSQL_ROW    row;

    if (!(res = mysql_store_result(mysql)))
      goto err;

    num_fields = mysql_num_fields(res);
    while ((row = mysql_fetch_row(res)))
    {
      unsigned long *lengths;
      lengths = mysql_fetch_lengths(res);
      logmessage("%s\t->  %s\n", row[0], row[1]);
    }
    mysql_free_result(res);
  }
  

  return (0); /* success */
  
err:

  if(DEBUG > 1)
    logmessage("db_printquery()/mysql_query(): %s -> %s\n", mysql_error(mysql), mysqlquery_array[fd]);
  
  mysql_array[fd][0] = -1;
  return (-1); /* failure */
}



/*
 * function: db_connect
 *  purpose: open connection to mysql database
 *   return: MySQL structure
 */
MYSQL *db_connect(const char *dbname)
{
  /* initializes mysql structure */
  
  /*
   * volatile:
   *
   * It's a potential problem, because Sun doesn't guarrantee that automatic
   * variables are preserved when the saved function's context is restored.
   *
   *                                                  Leandro Santi
   */
  MYSQL * volatile db=mysql_init(NULL);
  if(!db)
  {
    logmessage("mysql_init(): no memory\n");
    exit(-1);
  }
  logmessage("connecting to mysql database: %s\n", MYSQLHOST);

  /* catch connection timeouts */
  if (sigsetjmp (sjmp, 1))
  {
    if(DEBUG > 1)
      logmessage("db_connect()/mysql_real_connect(): connection timeout\n");

    /* if the connection fails, that counts as a hard failure */
    mysql_failure_count++;
    last_mysql_failure=timenow;
    
    logmessage("NOT connected..\n");
    return (db); /* failure */
  }

  /* set the SIGALRM handler */
  signal (SIGALRM, (void *) sigalrm_handler);
  
  /* mysql timeout */
  alarm(mysql_timeout);

#if defined(MYSQL_VERSION_ID)
#  if MYSQL_VERSION_ID >= 50003 && MYSQL_VERSION_ID < 50013
   /* hack to allow 5.0.3 => 5.0.13 to reconnect */
   db->reconnect = 1;
#  elif MYSQL_VERSION_ID >= 50013
   mysql_options(db, MYSQL_OPT_RECONNECT, "1");
#  endif
#endif

  /* fire off query */
  /* connect to mysql server */
  if(!mysql_real_connect(db, MYSQLHOST, MYSQLUSER, MYSQLPASS, dbname, MYSQLPORT, NULL, 0))
  {
    logmessage("mysql_real_connect(): %s\n", mysql_error(db));
    mysql_failure_count++;
    last_mysql_failure=timenow;
  } else {
    logmessage("connected..\n");
  }

  /* reset the timer & unset the signal handler */
  alarm (0);           signal (SIGALRM, SIG_DFL);

  return (db);
}




/*
 * function: database_probe
 *  purpose: check to see if connection to the database is open
 *   return: 0=success, -20=failure
 */
int
database_probe(unsigned int fd)
{
  if(DEBUG > 1)
    logmessage("DEBUG: fd: %d database_probe(): mysql_ping\n", fd);

  /* catch query timeouts */
  if (sigsetjmp (sjmp, 1))
  {
    if(DEBUG > 2)
      logmessage("db_doquery()/mysql_query(): mysql_query timeout -> %s\n", mysqlquery_array[fd]);

    /* if the probe fails, that counts as a hard failure */
    mysql_failure_count++;
    last_mysql_failure=timenow;

    return (-20); /* failure */
  }
  
  /* set the SIGALRM handler */
  signal (SIGALRM, (void *) sigalrm_handler);

  /* mysql timeout */
  alarm (mysql_timeout);

  /* reconnect to the database, only after 120 seconds has passed */
  if(DEBUG > 1)
    logmessage("DEBUG: fd: %d database_probe(): reconnecting..\n", fd);
  
  mysql_close(mysql);
  mysql = db_connect(MYSQLDBASE);
  
  /* reset the timer & unset the signal handler */
  alarm (0);
  signal (SIGALRM, SIG_DFL);

  return (0);
}

/* EOF */
