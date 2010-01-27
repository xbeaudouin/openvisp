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
 * function: throttle_host
 *  purpose: throttle users based on host
 *   return: 0 for new record, 1 for update
 */
int
throttle_host (unsigned int fd)
{

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking throttle-host\n", fd);

  /* user is not in the database */
  if(strlen(mysqlchar_array[fd][0]) < 2)
  {
    if(atol(triplet_array[fd][3]) >= atol(mysqlchar_array[fd][7]))
      goto abuse;
    
    logmessage("rcpt=%lu, throttle=new(a), host=%s (%s), from=%s, to=%s, size=%d/%d, "
      "quota=%d/%d, count=1/%d(1), rcpt=1/%d(1), threshold=0%|0%|0%\n",
      rcpt_count,                       /* recipient count      */
      host_array[fd][2],                /* ip address           */
      host_array[fd][0],                /* hostname             */
      triplet_array[fd][1],             /* from                 */
      triplet_array[fd][2],             /* to                   */
      atol(triplet_array[fd][3]),       /* size_cur             */
      atol(mysqlchar_array[fd][7]),     /* size_max             */
      atol(triplet_array[fd][3]),       /* quota_cur            */
      atol(mysqlchar_array[fd][5]),     /* quota_max            */
      atol(mysqlchar_array[fd][1]),     /* count_max            */
      atol(mysqlchar_array[fd][9])      /* rcpt_max             */
    );

    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "INSERT DELAYED INTO throttle "
      "(_date,_from,_quota_cur,_quota_max,_rcpt_max,_mail_size,_count_max,_time_limit)"
      " VALUES (%d, '%s', %d, %ld, %ld, %ld, %ld, %ld)",
      timenow,
      host_array[fd][2],
      atoi(triplet_array[fd][3]),
      atol(mysqlchar_array[fd][5]),
      atol(mysqlchar_array[fd][9]),
      atol(mysqlchar_array[fd][7]),
      atol(mysqlchar_array[fd][1]),
      atol(mysqlchar_array[fd][6]));
    if(db_doquery(fd) == -1) return(db_failure(fd, "throttle"));

    if(STATISTICS == 1)
    {
      sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "throttle=new(a)");
      if(db_optquery(fd) == -1) return(db_failure(fd, "throttle_new_a"));
    };

    /* sender does not exist in the database, insert and allow */
    return (0);
  }

  /* if sender is sending a message bigger than allowed, reject */
  if(atol(triplet_array[fd][3]) >= atol(mysqlchar_array[fd][7]))
  {
abuse:
    logmessage("rcpt=%lu, throttle=abuse(f), host=%s (%s), from=%s, to=%s, size=%d/%d, "
      "quota=%d/%d, count=%d/%d(%d), rcpt=%d/%d(%d), threshold=%d%|%d%|%d%\n",
      rcpt_count,                       /* recipient count      */
      host_array[fd][2],                /* ip address           */
      host_array[fd][0],                /* hostname             */
      triplet_array[fd][1],             /* from                 */
      triplet_array[fd][2],             /* to                   */
      atol(triplet_array[fd][3]),       /* size_cur             */
      atol(mysqlchar_array[fd][7]),     /* size_max             */
      atol(mysqlchar_array[fd][4]),     /* quota_cur            */
      atol(mysqlchar_array[fd][5]),     /* quota_max            */
      atol(mysqlchar_array[fd][2]),     /* count_cur            */
      atol(mysqlchar_array[fd][1]),     /* count_max            */
      atol(mysqlchar_array[fd][8]),     /* count_tot            */
      atol(mysqlchar_array[fd][10]),    /* rcpt_cur             */
      atol(mysqlchar_array[fd][9]),     /* rcpt_max             */
      atol(mysqlchar_array[fd][11]),    /* rcpt_tot             */
      tquota[fd],                       /* quota percentage     */
      tcount[fd],                       /* count percentage     */
      trcpt[fd]                         /* rcpt  percentage     */
    );

    if(STATISTICS == 1)
    {
      sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "throttle=abuse(f)");
      if(db_optquery(fd) == -1) return(db_failure(fd, "throttle_abuse_f"));
    };

    return (-3);
  }

  /* if time has expired, clear quota for size+message+rcpt count */
  if(timenow > (unsigned int)(atol(mysqlchar_array[fd][6])+atol(mysqlchar_array[fd][3])))
  {
    logmessage("rcpt=%lu, throttle=clear(a), host=%s (%s), from=%s, to=%s, size=%d/%d, "
      "quota=%d/%d, count=1/%d(%d), rcpt=1/%d(%d), threshold=0%|0%|0%\n",
      rcpt_count,                       /* recipient count      */
      host_array[fd][2],                /* ip address           */
      host_array[fd][0],                /* hostname             */
      triplet_array[fd][1],             /* from                 */
      triplet_array[fd][2],             /* to                   */
      atol(triplet_array[fd][3]),       /* size_cur             */
      atol(mysqlchar_array[fd][7]),     /* size_max             */
      atoi(triplet_array[fd][3]),       /* quota_cur            */
      atol(mysqlchar_array[fd][5]),     /* quota_max            */
      atol(mysqlchar_array[fd][1]),     /* count_max            */
      atol(mysqlchar_array[fd][8])+instance_inc[fd], /* count_tot            */
      atol(mysqlchar_array[fd][9]),     /* rcpt_max             */
      atol(mysqlchar_array[fd][11])+1   /* rcpt_tot             */
    );
      
    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "UPDATE throttle SET"
      " _rcpt_cur=1,"
      " _rcpt_tot=_rcpt_tot+1,"
      " _date=%d,"
      " _quota_cur=%d,"
      " _count_cur=1,"
      " _count_tot=_count_tot+%d,"
      " _abuse_tot=_abuse_tot+_abuse_cur,"
      " _abuse_cur=0"
      " WHERE _from='%s'",
      timenow,
      atoi(triplet_array[fd][3]),
      instance_inc[fd],
      mysqlchar_array[fd][0]);
    if(db_doquery(fd) == -1) return(db_failure(fd, "throttle"));

    if(STATISTICS == 1)
    {
      sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "throttle=clear(a)");
      if(db_optquery(fd) == -1) return(db_failure(fd, "throttle_clear_a"));
    };

    /* counter reset because of expiry, allow mail */
    return (0);
  }
  
  /* if the sender is past his quota and the timeout has not expired */
  /* then reject the message */
  if(atol(mysqlchar_array[fd][2])  >= atol(mysqlchar_array[fd][1]) || /* count  */
     atol(mysqlchar_array[fd][4])  >= atol(mysqlchar_array[fd][5]) || /* quota  */
     atol(mysqlchar_array[fd][10]) >= atol(mysqlchar_array[fd][9])) /* rcpt max */
  {
    if((instance_inc[fd] == 0) && (atol(mysqlchar_array[fd][10]) < atol(mysqlchar_array[fd][9])))
      goto update;
    
    logmessage("rcpt=%lu, throttle=abuse(f), host=%s (%s), from=%s, to=%s, size=%d/%d, "
      "quota=%d/%d, count=%d/%d(%d), rcpt=%d/%d(%d), abuse=%d, threshold=%d%|%d%|%d%\n",
      rcpt_count,                       /* recipient count      */
      host_array[fd][2],                /* ip address           */
      host_array[fd][0],                /* hostname             */
      triplet_array[fd][1],             /* from                 */
      triplet_array[fd][2],             /* to                   */
      atol(triplet_array[fd][3]),       /* size_cur             */
      atol(mysqlchar_array[fd][7]),     /* size_max             */
      atol(mysqlchar_array[fd][4]),     /* quota_cur            */
      atol(mysqlchar_array[fd][5]),     /* quota_max            */
      atol(mysqlchar_array[fd][2]),     /* count_cur            */
      atol(mysqlchar_array[fd][1]),     /* count_max            */
      atol(mysqlchar_array[fd][8]),     /* count_tot            */
      atol(mysqlchar_array[fd][10]),    /* rcpt_cur             */
      atol(mysqlchar_array[fd][9]),     /* rcpt_max             */
      atol(mysqlchar_array[fd][11]),    /* rcpt_tot             */
      atol(mysqlchar_array[fd][14]),    /* abuse_tot            */
      tquota[fd],                       /* quota percentage     */
      tcount[fd],                       /* count percentage     */
      trcpt[fd]                         /* rcpt  percentage     */
    );

    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "UPDATE throttle SET"
      " _abuse_cur=1"
      " WHERE _from='%s'",
      mysqlchar_array[fd][0]);
    if(db_doquery(fd) == -1) return(db_failure(fd, "throttle"));

    if(STATISTICS == 1)
    {
      sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "throttle=abuse(f)");
      if(db_optquery(fd) == -1) return(db_failure(fd, "throttle_abuse_f"));
    };

    if(SENDER_THROTTLE_AUTOBLACKLIST == 1)
    {
      if(atol(mysqlchar_array[fd][14]) >= SENDER_THROTTLE_AUTOBLACKLIST_NUMBER-1) /* _abuse_tot */
      {
        int expire=0;

        /* never auto expire blacklist? */
        if(SENDER_THROTTLE_AUTOBLACKLIST_EXPIRE > 0)
            expire=timenow+SENDER_THROTTLE_AUTOBLACKLIST_EXPIRE;
        
        snprintf(mysqlquery_array[fd], 512,
                 "INSERT DELAYED INTO blacklist (_blacklist,_description,_expire) "
                 "VALUES ('%s','# sender throttle autoblacklisted',%d)",
                 mysqlchar_array[fd][0], expire);
        
        /* execute query */
        if(db_doquery(fd) == -1) return(db_failure(fd, "throttle"));

        logmessage("rcpt=%lu, throttle=blacklisted(f), host=%s (%s), from=%s, to=%s, size=%d/%d, "
                   "quota=%d/%d, count=%d/%d(%d), rcpt=%d/%d(%d), abuse=%d, threshold=%d%|%d%|%d%\n",
                   rcpt_count,                       /* recipient count      */
                   host_array[fd][2],                /* ip address           */
                   host_array[fd][0],                /* hostname             */
                   triplet_array[fd][1],             /* from                 */
                   triplet_array[fd][2],             /* to                   */
                   atol(triplet_array[fd][3]),       /* size_cur             */
                   atol(mysqlchar_array[fd][7]),     /* size_max             */
                   atol(mysqlchar_array[fd][4]),     /* quota_cur            */
                   atol(mysqlchar_array[fd][5]),     /* quota_max            */
                   atol(mysqlchar_array[fd][2]),     /* count_cur            */
                   atol(mysqlchar_array[fd][1]),     /* count_max            */
                   atol(mysqlchar_array[fd][8]),     /* count_tot            */
                   atol(mysqlchar_array[fd][10]),    /* rcpt_cur             */
                   atol(mysqlchar_array[fd][9]),     /* rcpt_max             */
                   atol(mysqlchar_array[fd][11]),    /* rcpt_tot             */
                   atol(mysqlchar_array[fd][14]),    /* abuse_tot            */
                   tquota[fd],                       /* quota percentage     */
                   tcount[fd],                       /* count percentage     */
                   trcpt[fd]                         /* rcpt  percentage     */
            );

        if(STATISTICS == 1)
        {
          sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "throttle=blacklisted(f)");
          if(db_optquery(fd) == -1) return(db_failure(fd, "throttle_blacklisted_f"));
        };
      }
    }
    return (-5);
  }

update:

  /* sender has not reached his quota, increase count */
  logmessage("rcpt=%lu, throttle=update(%c), host=%s (%s), from=%s, to=%s, size=%d/%d, "
    "quota=%d/%d, count=%d/%d(%d), rcpt=%d/%d(%d), threshold=%d%|%d%|%d%\n",
    rcpt_count,                                              /* recipient count */
    tattrib_array[fd][0],                                    /* attribute state */
    host_array[fd][2],                                       /* ip address      */
    host_array[fd][0],                                       /* hostname        */
    triplet_array[fd][1],                                    /* from            */
    triplet_array[fd][2],                                    /* to              */
    atol(triplet_array[fd][3]),                              /* size_cur        */
    atol(mysqlchar_array[fd][7]),                            /* size_max        */
    atol(mysqlchar_array[fd][4])+atol(triplet_array[fd][3]), /* quota_cur       */
    atol(mysqlchar_array[fd][5]),                            /* quota_max       */
    atol(mysqlchar_array[fd][2])+instance_inc[fd],           /* count_cur       */
    atol(mysqlchar_array[fd][1]),                            /* count_max       */
    atol(mysqlchar_array[fd][8])+instance_inc[fd],           /* count_tot       */
    atol(mysqlchar_array[fd][10])+1,                         /* rcpt_cur        */ 
    atol(mysqlchar_array[fd][9]),                            /* rcpt_max        */ 
    atol(mysqlchar_array[fd][11])+1,                         /* rcpt_tot        */
    tquota[fd],                                              /* quota percentage*/
    tcount[fd],                                              /* count percentage*/
    trcpt[fd]                                                /* rcpt  percentage*/
  );
      
  /* build up & execute query */
  snprintf(mysqlquery_array[fd], 512,
    "UPDATE throttle SET"
    " _rcpt_cur=_rcpt_cur+1,"
    " _rcpt_tot=_rcpt_tot+1,"
    " _quota_cur=_quota_cur+%ld,"
    " _count_cur=_count_cur+%d,"
    " _count_tot=_count_tot+%d,"
    " _abuse_cur=0"
    " WHERE _from='%s'",
    atol(triplet_array[fd][3]),
    instance_inc[fd],
    instance_inc[fd],
    mysqlchar_array[fd][0]);
  if(db_doquery(fd) == -1) return(db_failure(fd, "throttle"));

  if(STATISTICS == 1)
    {
      sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "throttle=update");
      if(db_optquery(fd) == -1) return(db_failure(fd, "throttle_update"));
    };

  return (0); /* never reached */
}
 
/* EOF */
