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
 * function: throttle_check
 *  purpose: throttle users based on SASL info or envelope FROM
 *   return: 0 for new record, 1 for update
 */
int
throttle_check (unsigned int fd)
{
  unsigned int tnum = 0;
  unsigned int tresult = 0;
  
  mysql_optarray[fd][0] = 0;

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking throttle\n", fd);
  
  /* build up & execute query */
  if(SENDER_THROTTLE_HOST == 1)
  {
    tnum = 1;
    snprintf(mysqlquery_array[fd], 512,
      "SELECT _from,_count_max,_count_cur,_date,_quota_cur,_quota_max,"
      " _time_limit,_mail_size,_count_tot,_rcpt_max,_rcpt_cur,_rcpt_tot,"
      " _log_warn, _log_panic, _abuse_tot"
      " FROM throttle WHERE _from='%s' OR _from='%s' OR _from='%s' OR _from='%s'"
      " ORDER BY _priority DESC LIMIT 1",
      host_array[fd][2], host_array[fd][3], host_array[fd][4], host_array[fd][5]);
    
  } else if((SENDER_THROTTLE_SASL == 1) && (triplet_array[fd][4][0] != 0x00))  {
    tnum = 2;
    snprintf(mysqlquery_array[fd], 512,
      "SELECT _from,_count_max,_count_cur,_date,_quota_cur,_quota_max,"
      " _time_limit,_mail_size,_count_tot,_rcpt_max,_rcpt_cur,_rcpt_tot,"
      " _log_warn, _log_panic, _abuse_tot"
      " FROM throttle WHERE _from='%s'", triplet_array[fd][4]);
  } else if (SENDER_THROTTLE_ENVELOPE == 1)  {
    tnum = 3;
    snprintf(mysqlquery_array[fd], 512,
      "SELECT _from,_count_max,_count_cur,_date,_quota_cur,_quota_max,"
      " _time_limit,_mail_size,_count_tot,_rcpt_max,_rcpt_cur,_rcpt_tot,"
      " _log_warn, _log_panic, _abuse_tot"
      " FROM throttle WHERE _from='%s' OR _from='@%s'"
      " ORDER BY _priority DESC LIMIT 1",
      triplet_array[fd][1], host_array[fd][7]);
  } else {
    /* No throttling mechanisms are actually enabled - tnum == 0 */
    if (DEBUG >= 3)
      logmessage("DEBUG: fd: %d: no throttle set for %s\n",
        fd, triplet_array[fd][1]);

    return(0);
  }
  if(db_charquery(fd) == -1) return(db_failure(fd, "throttle"));    
  
  /* max messages is disabled in database, fall back to config default */
  if(atol(mysqlchar_array[fd][1]) == 0)
    snprintf(mysqlchar_array[fd][1], sizeof(mysqlchar_array[fd][1]),
      "%d", SENDERMSGLIMIT);

  /* max user quota is disabled in database, fall back to config defaults */
  if(atol(mysqlchar_array[fd][5]) == 0)
    snprintf(mysqlchar_array[fd][5], sizeof(mysqlchar_array[fd][5]),
      "%d", SENDERQUOTALIMIT);
  
  /* max time limit is disabled in database, fall back to config defaults */
  if(atol(mysqlchar_array[fd][6]) == 0)
    snprintf(mysqlchar_array[fd][6], sizeof(mysqlchar_array[fd][6]),
      "%d", SENDERTIMELIMIT);

  /* max message size is disabled in database, fall back to config defaults */
  if(atol(mysqlchar_array[fd][7]) == 0)
    snprintf(mysqlchar_array[fd][7], sizeof(mysqlchar_array[fd][7]),
      "%d", SENDERMSGSIZE);

  /* max rcpt limit is disabled in database, fall back to config defaults */
  if(atol(mysqlchar_array[fd][9]) == 0)
    snprintf(mysqlchar_array[fd][9], sizeof(mysqlchar_array[fd][9]),
      "%d", SENDERRCPTLIMIT);

  /* check postfix policy instance */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT COUNT(_instance) from throttle_from_instance \
       WHERE _instance='%s'", triplet_array[fd][6]); 
  if(db_optquery(fd) == -1) return(db_failure(fd, "throttle"));
  
  /* is instance recorded? */
  if(mysql_optarray[fd][0] == 0)
  {
    int expire=0;
    
    /* its not, so record it */
    if(SENDERTIMELIMIT > 0)
      expire=timenow;

    snprintf(mysqlquery_array[fd], 512,
      "INSERT DELAYED INTO throttle_from_instance (_instance,_expire) VALUES ('%s',%d)",
      triplet_array[fd][6], expire);
    if(db_doquery(fd) == -1) return(db_failure(fd, "throttle"));

    instance_inc[fd] = 1;
  } else {
    instance_inc[fd] = 0;
  }

  /* prepare attributes & thresholds */
  /* count, quota, rcpt */
  tquota[fd] = atof(mysqlchar_array[fd][4]) / atof(mysqlchar_array[fd][5]) * 100;
  tcount[fd] = atof(mysqlchar_array[fd][2]) / atof(mysqlchar_array[fd][1]) * 100;
  trcpt[fd] = atof(mysqlchar_array[fd][10]) / atof(mysqlchar_array[fd][9]) * 100;
  
  /* catch wierd ones */
  if(DEBUG >= 4)
    logmessage("DEBUG: fd: %d: tquota[fd]: %d, tcount[fd]: %d, trcpt[fd]: %d\n",
       fd, tquota[fd], tcount[fd], trcpt[fd]);
  
  /* highest percentage always wins.. mmm.. ugly stuff*/
  if(tquota[fd] >= tcount[fd] && tquota[fd] >= trcpt[fd]) {
    tresult = tquota[fd];
    if (DEBUG >= 4) logmessage("tquota[fd] won\n"); }
  
  if(tcount[fd] >= tquota[fd] && tcount[fd] >= trcpt[fd]) {
    tresult = tcount[fd];
    if (DEBUG >= 4) logmessage("tquota[fd] won\n"); }
    
  if(trcpt[fd]  >= tcount[fd] && trcpt[fd] >= tquota[fd]) {
    tresult = trcpt[fd];
    if (DEBUG >= 4) logmessage("tquota[fd] won\n"); }

  if(DEBUG >= 4)
    logmessage("DEBUG: fd: %d: tresult: %d\n", fd, tresult);

  /* percentage won, set attribute accordingly */
  if (tresult >= 0 && tresult <= 49)
  {
    tattrib_array[fd][0] = 'a'; 
  } 
  else if (tresult >= 50 && tresult <= 89)
  {
    tattrib_array[fd][0] = 'w';
  }
  else if (tresult >= 90)
  {
    tattrib_array[fd][0] = 'p';
  }
  else
  {
    logmessage("fatal: throttle_check(): invalid tresult: %d\n", tresult);
    return (-1);
  }

  /* we selectively choose which throttle module we want */
  switch(tnum)
  {
    case 1:
      return(throttle_host(fd));

    case 2:
      return(throttle_sasl(fd));

    case 3:
      return(throttle_from(fd));

    default:
      logmessage("fatal: throttle_check(): no tnum\n");
      return (-1);
  }
  
  return (0); /* never reached */
}
 
/* EOF */
