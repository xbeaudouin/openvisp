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
 * function: greylist_check
 *  purpose: check if triplet exists in mysql
 *   return: 0 for new record, 1 for update
 */
int
greylist_check(unsigned int fd)
{

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking greylist\n", fd);
  
  /* set sane defaults */
  mysql_array[fd][0] = -2;
  mysql_optarray[fd][0] = OPTINOUTALL;
  
  /* opt-in/opt-out? */
  if(OPTINOUT == 1)
  {

    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "SELECT _optin FROM policy WHERE _rcpt='%s' OR _rcpt='@%s' ORDER BY _priority DESC LIMIT 1",
      triplet_array[fd][2], host_array[fd][9]);
    if(db_optquery(fd) == -1) return(db_failure(fd, "greylist"));

    /* user is opted out */
    if(mysql_optarray[fd][0] == 0)
    {
      logmessage("rcpt=%lu, greylist=optout, host=%s (%s), from=%s, to=%s, size=%s\n",
        rcpt_count,                       /* recipient count      */
        host_array[fd][2],                /* host address         */
        host_array[fd][0],                /* hostname             */
        triplet_array[fd][1],             /* sender               */
        triplet_array[fd][2],             /* recipient            */
        triplet_array[fd][3]);            /* size                 */

      if(STATISTICS == 1)
      {
        sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "greylist=optout");
        if(db_optquery(fd) == -1) return(db_failure(fd, "greylist_optout"));
      };

      return (0);
    }
  }


  /* domain or mail address is in training mode? */
  if(TRAINING_POLICY_TIMEOUT != 0)
  {
    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "SELECT COUNT(*) FROM policy_training WHERE _rcpt='%s' OR _rcpt='@%s'",
      triplet_array[fd][2], host_array[fd][9]);
    if(db_optquery(fd) == -1) return(db_failure(fd, "greylist"));

    /* training policy is activated for domain or email address */
    if(mysql_optarray[fd][0] >= 1)
      mysql_optarray[fd][0] = 2;
  }


  /* build up & execute query */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT _count,_datenew,_datelast FROM triplet WHERE _host='%s' AND _from='%s' AND _rcpt='%s'",
      triplet_array[fd][0], triplet_array[fd][1], triplet_array[fd][2]);
  if(db_doquery(fd) == -1) return(db_failure(fd, "greylist"));

  /* update the greylist xheader */
  if(GREYLIST_X_HEADER==1)
    snprintf(xgreylist_array[fd], 128, "%s host: %s count: %d size: %s\n\n",
      POSTFIX_X_HEADER, host_array[fd][2], mysql_array[fd][0], triplet_array[fd][3]);

  
  /* triplet not found in greylist database */
  if(mysql_array[fd][0]==-2)
  {
    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "INSERT DELAYED INTO triplet (_datenew,_datelast,_host,_from,_rcpt) VALUES (%d,%d,'%s','%s','%s')",
      timenow, timenow, triplet_array[fd][0], triplet_array[fd][1], triplet_array[fd][2]);
    if(db_doquery(fd) == -1) return(db_failure(fd, "greylist"));

    /* auto black listing is enabled */
    if(AUTO_BLACK_LISTING == 1)
    {
      /* perform query to see how many triplets there are for a host/network */
      /* build up & execute query */
      snprintf(mysqlquery_array[fd], 512,
        "SELECT COUNT(*) FROM triplet WHERE _host='%s' AND _count = 0",
          triplet_array[fd][0]);
      if(db_doquery(fd) == -1) return(db_failure(fd, "greylist"));

      if(DEBUG > 0)
        logmessage("DEBUG: fd: %d unauth triplet: %d\n", fd, mysql_array[fd][0]);

      /* host has more than allowed number of unauthenticated triplets */
      if(mysql_array[fd][0] >= AUTO_BLACKLIST_NUMBER)
      {
        int expire=0;
      
        /* never auto expire blacklist? */
        if(AUTO_BLACKLIST_EXPIRE > 0)
          expire=timenow+AUTO_BLACKLIST_EXPIRE;

        /* blacklist netblock /24 */
        if(BLACKLIST_NETBLOCK==1)
        { /* blacklist netblock */
          snprintf(mysqlquery_array[fd], 512,
            "INSERT DELAYED INTO blacklist (_blacklist,_description,_expire) VALUES ('%s.%%','# autoblacklisted', %d)",
             triplet_array[fd][0], expire);
        } else { /* blacklist host ip */
          snprintf(mysqlquery_array[fd], 512,
            "INSERT DELAYED INTO blacklist (_blacklist,_description,_expire) VALUES ('%s','# autoblacklisted',%d)",
             host_array[fd][2], expire);
        }
        /* execute query */
        if(db_doquery(fd) == -1) return(db_failure(fd, "greylist"));
      
        logmessage("rcpt=%lu, greylist=abl, host=%s (%s), from=%s, to=%s, size=%s, expire=%d\n",
          rcpt_count,                       /* recipient count      */
          host_array[fd][2],                /* host address         */
          host_array[fd][0],                /* hostname             */
          triplet_array[fd][1],             /* sender               */
          triplet_array[fd][2],             /* recipient            */
          triplet_array[fd][3],             /* size                 */
          expire);                          /* expiry date          */

        /* build up & execute query */
        snprintf(mysqlquery_array[fd], 512,
          "DELETE QUICK from triplet WHERE _host='%s'", triplet_array[fd][0]);
        if(db_doquery(fd) == -1) return(db_failure(fd, "greylist"));

        if(STATISTICS == 1)
        {
          sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "greylist=abl");
          if(db_optquery(fd) == -1) return(db_failure(fd, "greylist_abl"));
        };

        /* reject */
        return (-1);
      }
    }
      

    /* not in training mode, reject if this is the first attempt */
    if((TRAINING_MODE == 0) && (mysql_optarray[fd][0] != 2))
    {
      logmessage("rcpt=%lu, greylist=new, host=%s (%s), from=%s, to=%s, size=%s\n",
        rcpt_count,                       /* recipient count      */
        host_array[fd][2],                /* host address         */
        host_array[fd][0],                /* hostname             */
        triplet_array[fd][1],             /* sender               */
        triplet_array[fd][2],             /* recipient            */
        triplet_array[fd][3]);            /* size                 */
      
      if(STATISTICS == 1)
      {
        sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "greylist=new");
        if(db_optquery(fd) == -1) return(db_failure(fd, "greylist_new"));
      };

      /* reject */
      return (-1);
    }
    
    /* in training mode, always accept */
    if((TRAINING_MODE == 1) || (mysql_optarray[fd][0] == 2))
    {
      logmessage("rcpt=%lu, greylist=new_train, host=%s (%s), from=%s, to=%s, size=%s\n",
        rcpt_count,
        host_array[fd][2],      /* host */
        host_array[fd][0],      /* hostname */
        triplet_array[fd][1],   /* from */
        triplet_array[fd][2],   /* rcpt */
        triplet_array[fd][3]    /* size */
      );

      if(STATISTICS == 1)
      {
        sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "greylist=new_train");
        if(db_optquery(fd) == -1) return(db_failure(fd, "greylist_new_train"));
      };

      /* accept */
      return (0);
    }
    
  } else { /* triplet exists in database */
    
    /* has TRIPLET_TIME expired since triplet creation? */
    if(timenow < (unsigned int)(mysql_array[fd][1]+TRIPLET_TIME))
    {
      /* not in training mode */
      if((TRAINING_MODE == 0) && (mysql_optarray[fd][0] != 2))
      {
        logmessage("rcpt=%lu, greylist=abuse, host=%s (%s), from=%s, to=%s, size=%s\n", 
          rcpt_count,                       /* recipient count      */
          host_array[fd][2],                /* host address         */
          host_array[fd][0],                /* hostname             */
          triplet_array[fd][1],             /* sender               */
          triplet_array[fd][2],             /* recipient            */
          triplet_array[fd][3]);            /* size                 */

        if(STATISTICS == 1)
        {
          sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "greylist=abuse");
          if(db_optquery(fd) == -1) return(db_failure(fd, "greylist_abuse"));
        };

        return (-1);
      }
    }
    
    
    /* implement autowhitelisting */
    if(AUTO_WHITE_LISTING==1) 
    {
      /* expire auto-whitelisted hosts if enabled */
      int expire=0;
      if(AUTO_WHITELIST_EXPIRE > 0)
        expire=timenow+AUTO_WHITELIST_EXPIRE;

      /* save an sql lookup if awl == 1 */
      if(AUTO_WHITELIST_NUMBER == 1)
        goto awl;

      /* check how many auth triplets there are for a host/network */
      /* build up & execute query */
      snprintf(mysqlquery_array[fd], 512,
        "SELECT COUNT(*) FROM triplet WHERE _host='%s' AND _count > 0",
          triplet_array[fd][0]);
      if(db_doquery(fd) == -1) return(db_failure(fd, "greylist"));

      if(DEBUG > 0)
        logmessage("DEBUG: fd: %d whitelist result: %d\n", fd, mysql_array[fd][0]);

      if(mysql_array[fd][0] >= AUTO_WHITELIST_NUMBER)
        goto awl;       /* auto whitelist network/host */
      else
        goto nawl;      /* dont auto white list network */

awl:

      if(AUTO_WHITELIST_NETBLOCK==1)
      {
        /* build up & execute query */
        snprintf(mysqlquery_array[fd], 512,
          "INSERT DELAYED INTO whitelist (_whitelist,_description,_expire) VALUES ('%s.%%','# autowhitelisted host',%d)",
          triplet_array[fd][0], expire);
      } else {
        /* build up & execute query */
        snprintf(mysqlquery_array[fd], 512,
          "INSERT DELAYED INTO whitelist (_whitelist,_description,_expire) VALUES ('%s','# autowhitelisted host',%d)",
          host_array[fd][2], expire);
      }
      if(db_doquery(fd) == -1) return(db_failure(fd, "greylist"));

      /* build up & execute query */
      snprintf(mysqlquery_array[fd], 512,
        "DELETE QUICK from triplet WHERE _host='%s'", triplet_array[fd][0]);
      if(db_doquery(fd) == -1) return(db_failure(fd, "greylist"));

      logmessage("rcpt=%lu, greylist=awl, host=%s (%s), from=%s, to=%s, size=%s, expire=%d\n",
        rcpt_count,                       /* recipient count      */
        host_array[fd][2],                /* host address         */
        host_array[fd][0],                /* hostname             */
        triplet_array[fd][1],             /* sender               */
        triplet_array[fd][2],             /* recipient            */
        triplet_array[fd][3],             /* size                 */
        expire);                          /* expiry date          */

        if(STATISTICS == 1)
        {
          sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "greylist=awl");
          if(db_optquery(fd) == -1) return(db_failure(fd, "greylist_awl"));
        }

      return(0);
    }
    
nawl:

    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "UPDATE triplet SET _datelast='%d',_count=_count+1 WHERE _host='%s' AND _from='%s' AND _rcpt='%s'",
      timenow, triplet_array[fd][0], triplet_array[fd][1], triplet_array[fd][2]);
    if(db_doquery(fd) == -1) return(db_failure(fd, "greylist"));
    
    /* training mode */
    if((TRAINING_MODE == 0) && (mysql_optarray[fd][0] != 2))
    {
      /* yes, it has.. update */
      logmessage("rcpt=%lu, greylist=update, host=%s (%s), from=%s, to=%s, size=%s\n",
        rcpt_count,                       /* recipient count      */
        host_array[fd][2],                /* host address         */
        host_array[fd][0],                /* hostname             */
        triplet_array[fd][1],             /* sender               */
        triplet_array[fd][2],             /* recipient            */
        triplet_array[fd][3]);            /* size                 */

        if(STATISTICS == 1)
        {
          sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "greylist=update");
          if(db_optquery(fd) == -1) return(db_failure(fd, "greylist_update"));
        };
    }
    
    /* training mode */
    if((TRAINING_MODE == 1) || (mysql_optarray[fd][0] == 2))
    {
      /* yes, it has.. update */
      logmessage("rcpt=%lu, greylist=update_train, host=%s (%s), from=%s, to=%s, size=%s\n",
        rcpt_count,                       /* recipient count      */
        host_array[fd][2],                /* host address         */
        host_array[fd][0],                /* hostname             */
        triplet_array[fd][1],             /* sender               */
        triplet_array[fd][2],             /* recipient            */
        triplet_array[fd][3]);            /* size                 */

        if(STATISTICS == 1)
        {
          sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "greylist=update_train");
          if(db_optquery(fd) == -1) return(db_failure(fd, "greylist_update_train"));
        };
    }

    return (0);
  }

  return (0); /* never reached */
}
 
/* EOF */
