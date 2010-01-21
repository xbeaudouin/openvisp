#include "policyd.h"

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
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *  or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 *  for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation Inc.,
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *
 *
 */

int
main(int argc, char **argv)
{
  int c;

  if(argc < 2)
    usage(argv[0]);

  while ((c = getopt(argc,argv,":c:")) != EOF)
  { 
    switch(c)
    { 
      case 'c':
        configpath=optarg;
        read_conf(1);
        break;
        
      default:
        usage(argv[0]);
    }
  }

  logmessage("clean up process starting: %s %s\n", PACKAGE_NAME, PACKAGE_VERSION);
      
  /* connect to mysql */
#if defined(MYSQL_VERSION_ID) && MYSQL_VERSION_ID >= 40000
  mysql_server_init(0, NULL, NULL);
#endif
  mysql = db_connect(MYSQLDBASE);

  /* store current time */
  timenow=gettime();
  mysql_timeout=600;

  /* clean up greylisting */
  if(GREYLISTING==1)
  {
    /* expunge validated triplets */
    logmessage("expiring validated records older than %d days (%d)\n",
      (TRIPLET_AUTH_TIMEOUT / 86400), timenow - TRIPLET_AUTH_TIMEOUT);
    
    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "DELETE QUICK FROM triplet WHERE _datelast < %d LIMIT 100000",
      timenow - TRIPLET_AUTH_TIMEOUT);
    if(db_deletequery(0) == -1) exit(-1);

    /* expunge unvalidated triplets */
    logmessage("expiring unvalidated records older than %d days (%d)\n",
      (TRIPLET_UNAUTH_TIMEOUT / 86400), timenow - TRIPLET_UNAUTH_TIMEOUT);

    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "DELETE QUICK FROM triplet WHERE _datenew < %d AND _count = 0 LIMIT 100000",
      timenow - TRIPLET_UNAUTH_TIMEOUT);
    if(db_deletequery(0) == -1) exit(-1);
  }


  /* clean up autoblacklisted hosts */
  if(BLACKLISTING == 1)
  {
    /* expunge blacklist entries that have expired */
    logmessage("expiring blacklisted records (%d)\n", timenow);

    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "DELETE QUICK FROM blacklist WHERE _expire <= %d \
       AND _expire != 0 LIMIT 100000",
      timenow);
    if(db_deletequery(0) == -1) exit(-1);
  }

  
  /* clean up auto whitelisted hosts */
  if(AUTO_WHITE_LISTING == 1)
  {
    /* expunge whitelist entries that have expired */
    logmessage("expiring autowhitelisted records older than %d days (%d)\n",
      (AUTO_WHITELIST_EXPIRE / 86400), timenow);

    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "DELETE QUICK FROM whitelist WHERE _expire <= %d AND \
       _expire != 0 LIMIT 100000",
      timenow);
    if(db_deletequery(0) == -1) exit(-1);
  }


  /* clean up helo information */
  if(HELO_CHECK == 1)
  {
    /* expunge helo entries that have expired */
    logmessage("expiring helo records older than %d days (%d)\n",
      (HELO_AUTO_EXPIRE / 86400), timenow);

    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "DELETE QUICK FROM helo WHERE _expire <= %d AND _expire != 0 LIMIT 100000",
      timenow);
    if(db_deletequery(0) == -1) exit(-1);
  }


  /* clean up expired throttlesender information */
  if((SENDERTHROTTLE == 1) && (SENDER_INACTIVE_EXPIRE))
  {
    logmessage("expiring throttlesender records older than %d days (%d)\n",
      (SENDER_INACTIVE_EXPIRE / 86400), timenow - SENDER_INACTIVE_EXPIRE);

    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "DELETE QUICK FROM throttle WHERE _date < %d",
      timenow - SENDER_INACTIVE_EXPIRE);
    if(db_deletequery(0) == -1) exit(-1);


    logmessage("expiring throttlesender instances older than 1 hour (%d)\n",
      timenow - 3600);

    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "DELETE QUICK FROM throttle_from_instance WHERE _expire < %d",
      timenow - 3600);
    if(db_deletequery(0) == -1) exit(-1);
  }


  /* clean up expired throttlerecipients information */
  if((RECIPIENTTHROTTLE == 1) && (RECIPIENT_INACTIVE_EXPIRE))
  {
    logmessage("expiring throttlerecipient records older than %d days (%d)\n",
      (RECIPIENT_INACTIVE_EXPIRE / 86400), timenow - RECIPIENT_INACTIVE_EXPIRE);

     /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "DELETE QUICK FROM throttle_rcpt WHERE _date < %d",
      timenow - RECIPIENT_INACTIVE_EXPIRE);
    if(db_deletequery(0) == -1) exit(-1);
  }


  /* clean up expired optin / optout policies */
  if(TRAINING_POLICY_TIMEOUT != 0)
  {
    /* clean up expired training policies */
    logmessage("expiring training policies records older than %d days (%d)\n",
      (TRAINING_POLICY_TIMEOUT / 86400), timenow - TRAINING_POLICY_TIMEOUT);

    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "DELETE QUICK FROM policy_training WHERE _expire < %d and _expire != 0",
      timenow - TRAINING_POLICY_TIMEOUT);
    if(db_deletequery(0) == -1) exit(-1);
  }

  mysql_close(mysql);
    
  return (0);
}
 
/* EOF */
