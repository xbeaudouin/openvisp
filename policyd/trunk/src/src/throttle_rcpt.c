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
 * function: throttle_rcpt
 *  purpose: throttle users based on envelope RCPT
 *   return: 0 for new record, 1 for update
 */
signed int
throttle_rcpt (unsigned int fd)
{

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking throttle-rcpt\n", fd);
  
  /* build up & execute query */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT _rcpt,_count_max,_count_cur,_time_limit,_date,_count_tot"
    " FROM throttle_rcpt WHERE _rcpt='%s'", triplet_array[fd][2]);
  if(db_charquery(fd) == -1) return(db_failure(fd, "throttle_rcpt"));    
  
  /* max messages is disabled in database, fall back to config default */
  if(atol(mysqlchar_array[fd][1]) == 0)
    snprintf(mysqlchar_array[fd][1], sizeof(mysqlchar_array[fd][1]),
      "%d", RECIPIENTMSGLIMIT);

  /* max time limit is disabled in database, fall back to config defaults */
  if(atol(mysqlchar_array[fd][3]) == 0)
    snprintf(mysqlchar_array[fd][3], sizeof(mysqlchar_array[fd][3]),
      "%d", RECIPIENTTIMELIMIT);

  /* prepare attributes & thresholds */
  trcpt[fd] = atof(mysqlchar_array[fd][2]) / atof(mysqlchar_array[fd][1]) * 100;

  /* percentage won, set attribute accordingly */
  if (trcpt[fd] >= 0 && trcpt[fd] <= 49)
  {
    tattrib_array[fd][0] = 'a';
  }
  else if (trcpt[fd] >= 50 && trcpt[fd] <= 89)
  {
    tattrib_array[fd][0] = 'w';
  }
  else if (trcpt[fd] >= 90)
  {
    tattrib_array[fd][0] = 'p';
  }
  else
  {
    logmessage("fatal: throttle_rcpt(): invalid tresult: %d\n", trcpt[fd]);
  }

  
  /* user is not in the database */
  if(strlen(mysqlchar_array[fd][0]) < 2)
  {
    logmessage("rcpt=%lu, throttle_rcpt=new(a), host=%s (%s), from=%s, to=%s, "
      "count=1/%d(1), threshold=0%\n",
      rcpt_count,                       /* recipient count      */
      host_array[fd][2],                /* ip address           */
      host_array[fd][0],                /* hostname             */
      triplet_array[fd][1],             /* from                 */
      triplet_array[fd][2],             /* to                   */
      atol(mysqlchar_array[fd][1])      /* count_max            */
    );

    if(STATISTICS == 1)
    {
      sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "throttle_rcpt=new(a)");
      if(db_optquery(fd) == -1) return(db_failure(fd, "throttle_rcpt_new_a"));
    };
      
    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "INSERT DELAYED INTO throttle_rcpt (_date,_rcpt,_count_max,_time_limit) "
      "VALUES (%d, '%s', %ld, %ld)",
      timenow,
      triplet_array[fd][2],
      atol(mysqlchar_array[fd][1]),
      atol(mysqlchar_array[fd][3]));
    if(db_doquery(fd) == -1) return(db_failure(fd, "throttle_rcpt"));
    
    /* recipient does not exist in the database, insert and allow */
    return (0);
  }
  
  /* if time has expired, clear quota for message count */
  if(timenow > (unsigned int)(atol(mysqlchar_array[fd][4])+atol(mysqlchar_array[fd][3])))
  {
    logmessage("rcpt=%lu, throttle_rcpt=clear(a), host=%s (%s), from=%s, to=%s, "
      "count=0/%d(%d), threshold=0%\n",
      rcpt_count,                       /* recipient count      */
      host_array[fd][2],                /* ip address           */
      host_array[fd][0],                /* hostname             */
      triplet_array[fd][1],             /* from                 */
      triplet_array[fd][2],             /* to                   */
      atol(mysqlchar_array[fd][1]),     /* count_max            */
      atol(mysqlchar_array[fd][5])      /* count_tot            */
    );

    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "UPDATE throttle_rcpt SET"
      " _count_cur=1,"
      " _count_tot=_count_tot+1,"
      " _abuse_tot=_abuse_tot+_abuse_cur,"
      " _date=%d,"
      " _abuse_cur=0"
      " WHERE _rcpt='%s'",
      timenow, triplet_array[fd][2]);
    if(db_doquery(fd) == -1) return(db_failure(fd, "throttle_rcpt"));

    if(STATISTICS == 1)
    {
      sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "throttle_rcpt=clear(a)");
      if(db_optquery(fd) == -1) return(db_failure(fd, "throttle_rcpt_clear_a"));
    };

    /* counter reset because of expiry, allow mail */
    return (0);
  }
  
  /* if the recipient is past his quota and the timeout has not expired */
  /* then reject the message */
  if(atol(mysqlchar_array[fd][2]) >= atol(mysqlchar_array[fd][1]))
  {
    logmessage("rcpt=%lu, throttle_rcpt=abuse(f), host=%s (%s), from=%s, to=%s, "
      "count=%d/%d(%d), threshold=%d%\n",
      rcpt_count,                       /* recipient count      */
      host_array[fd][2],                /* ip address           */
      host_array[fd][0],                /* hostname             */
      triplet_array[fd][1],             /* from                 */
      triplet_array[fd][2],             /* to                   */
      atol(mysqlchar_array[fd][2]),     /* count_cur            */
      atol(mysqlchar_array[fd][1]),     /* count_max            */
      atol(mysqlchar_array[fd][5]),     /* count_tot            */
      trcpt[fd]                         /* rcpt  percentage     */
    );

    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "UPDATE throttle_rcpt SET"
      " _abuse_cur=1"
      " WHERE _rcpt='%s'",
      triplet_array[fd][2]);
    if(db_doquery(fd) == -1) return(db_failure(fd, "throttle-rcpt"));
    
    return (-7);
  }

  /* if the recipient has not reached his quota, increase count */
  logmessage("rcpt=%lu, throttle_rcpt=update(%c), host=%s (%s), from=%s, to=%s, "
    "count=%d/%d(%d), threshold=%d%\n",
    rcpt_count,                       /* recipient count      */
    tattrib_array[fd][0],             /* attribute state      */
    host_array[fd][2],                /* ip address           */
    host_array[fd][0],                /* hostname             */
    triplet_array[fd][1],             /* from                 */
    triplet_array[fd][2],             /* to                   */
    atol(mysqlchar_array[fd][2])+1,   /* count_cur            */
    atol(mysqlchar_array[fd][1]),     /* count_max            */
    atol(mysqlchar_array[fd][5])+1,   /* count_tot            */
    trcpt[fd]                         /* rcpt  percentage     */
  );
      
  /* build up & execute query */
  snprintf(mysqlquery_array[fd], 512,
    "UPDATE throttle_rcpt SET"
    " _count_cur=_count_cur+1,"
    " _abuse_cur=0"
    " WHERE _rcpt='%s'",
    triplet_array[fd][2]);
  if(db_doquery(fd) == -1) return(db_failure(fd, "throttle_rcpt"));

  if(STATISTICS == 1)
  {
    sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "throttle_rcpt=update");
    if(db_optquery(fd) == -1) return(db_failure(fd, "throttle_rcpt_update"));
  };

  return (0); /* never reached */
}
 
/* EOF */
