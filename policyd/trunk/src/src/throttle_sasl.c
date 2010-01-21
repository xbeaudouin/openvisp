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
 * function: throttle_sasl
 *  purpose: throttle users based on SASL info 
 *   return: 0 for new record, 1 for update
 */
int
throttle_sasl (unsigned int fd)
{

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking throttle-sasl\n", fd);
  
  /* user is not in the database */
  if(strlen(mysqlchar_array[fd][0]) < 2)
  {
    if(atol(triplet_array[fd][3]) >= atol(mysqlchar_array[fd][7]))
      goto abuse;
    
    logmessage("rcpt=%lu, throttle=new(a), host=%s (%s), from=%s, to=%s, size=%d/%d, "
      "quota=%d/%d, count=1/%d(1), rcpt=1/%d(1), threshold=0%|0%|0%, sasl_username=%s\n",
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
      atol(mysqlchar_array[fd][9]),     /* rcpt_max             */
      triplet_array[fd][4]              /* sasl_username        */
    );
      
    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "INSERT DELAYED INTO throttle "
      "(_date,_from,_quota_cur,_quota_max,_rcpt_max,_mail_size,_count_max, _time_limit)"
      " VALUES (%d, '%s', %d, %ld, %ld, %ld, %ld, %ld)",
      timenow,
      triplet_array[fd][4],
      atoi(triplet_array[fd][3]),
      atol(mysqlchar_array[fd][5]),
      atol(mysqlchar_array[fd][9]),
      atol(mysqlchar_array[fd][7]),
      atol(mysqlchar_array[fd][1]),
      atol(mysqlchar_array[fd][6]));
    if(db_doquery(fd) == -1) return(db_failure(fd, "throttle"));
    
    /* sender does not exist in the database, insert and allow */
    return (0);
  }
  
  /* if sender is sending a message bigger than allowed, reject */
  if(atol(triplet_array[fd][3]) >= atol(mysqlchar_array[fd][7]))
  {
abuse:
    logmessage("rcpt=%lu, throttle=abuse(f), host=%s (%s), from=%s, to=%s, size=%d/%d, "
      "quota=%d/%d, count=%d/%d(%d), rcpt=%d/%d(%d), threshold=%d%|%d%|%d%, sasl_username=%s\n",
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
      trcpt[fd],                        /* rcpt  percentage     */
      triplet_array[fd][4]              /* sasl_username        */
    );
      
    return (-3);
  }
      
  /* if time has expired, clear quota for size+message count */
  if(timenow > (unsigned int)(atol(mysqlchar_array[fd][6])+atol(mysqlchar_array[fd][3])))
  {
    logmessage("rcpt=%lu, throttle=clear(a), host=%s (%s), from=%s, to=%s, size=%d/%d, "
      "quota=%d/%d, count=1/%d(%d), rcpt=1/%d(%d), threshold=0%|0%|0%, sasl_username=%s\n",
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
      atol(mysqlchar_array[fd][8]),     /* count_tot            */
      atol(mysqlchar_array[fd][9]),     /* rcpt_max             */
      atol(mysqlchar_array[fd][11])+1,  /* rcpt_tot             */
      triplet_array[fd][4]              /* sasl_username        */
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
      triplet_array[fd][4]);
    if(db_doquery(fd) == -1) return(db_failure(fd, "throttle"));

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
      "quota=%d/%d, count=%d/%d(%d), rcpt=%d/%d(%d), threshold=%d%|%d%|%d%, sasl_username=%s\n",
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
      trcpt[fd],                        /* rcpt  percentage     */
      triplet_array[fd][4]              /* sasl_username        */
    );

    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "UPDATE throttle SET"
      " _abuse_cur=1"
      " WHERE _from='%s'",
      triplet_array[fd][1]);
    if(db_doquery(fd) == -1) return(db_failure(fd, "throttle"));

    return (-5);
  }

update:

  /* if the sender has not reached his quota, increase count */
  logmessage("rcpt=%lu, throttle=update(%c), host=%s (%s), from=%s, to=%s, size=%d/%d, "
    "quota=%d/%d, count=%d/%d(%d), rcpt=%d/%d(%d), threshold=%d%|%d%|%d%, sasl_username=%s\n",
    rcpt_count,                                              /* recipient count  */
    tattrib_array[fd][0],                                    /* attribute state  */
    host_array[fd][2],                                       /* ip address       */
    host_array[fd][0],                                       /* hostname         */
    triplet_array[fd][1],                                    /* from             */
    triplet_array[fd][2],                                    /* to               */
    atol(triplet_array[fd][3]),                              /* size_cur         */
    atol(mysqlchar_array[fd][7]),                            /* size_max         */
    atol(mysqlchar_array[fd][4])+atol(triplet_array[fd][3]), /* quota_cur        */
    atol(mysqlchar_array[fd][5]),                            /* quota_max        */
    atol(mysqlchar_array[fd][2])+instance_inc[fd],           /* count_cur        */
    atol(mysqlchar_array[fd][1]),                            /* count_max        */
    atol(mysqlchar_array[fd][8])+instance_inc[fd],           /* count_tot        */
    atol(mysqlchar_array[fd][10])+1,                         /* rcpt_cur         */
    atol(mysqlchar_array[fd][9]),                            /* rcpt_max         */
    atol(mysqlchar_array[fd][11])+1,                         /* rcpt_tot         */
    tquota[fd],                                              /* quota percentage */
    tcount[fd],                                              /* count percentage */
    trcpt[fd],                                               /* rcpt  percentage */
    triplet_array[fd][4]                                     /* sasl_username    */
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
    triplet_array[fd][4]);
  if(db_doquery(fd) == -1) return(db_failure(fd, "throttle"));

  return (0); /* never reached */
}
 
/* EOF */
