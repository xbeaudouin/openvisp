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
 * function: spamtrap_check
 *  purpose: check if there is a delivery attempt to a spamtrap
 *   return: 1=yes, 0=no
 */
int
spamtrap_check(unsigned int fd)
{
  
  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking spamtrap\n", fd);

  /* reset value */
  mysql_optarray[fd][0] = 0;
    
  /* build up & execute query */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT _active FROM spamtrap WHERE _rcpt='%s' AND _active=1", triplet_array[fd][2]);
  if(db_optquery(fd) == -1) return(db_failure(fd, "spamtrap"));

  /* we have a delivery attempt to a spamtrap */
  if(mysql_optarray[fd][0] == 1)
  {
    int expire=0;
    if(DEBUG > 0)
      logmessage("DEBUG: fd: %d spamtrap found: %s from: %s\n", fd, 
        triplet_array[fd][2],   /* rcpt */
        host_array[fd][2]);     /* host */

    /* never auto expire blacklist? */
    if(SPAMTRAP_AUTO_EXPIRE > 0)
      expire=timenow+SPAMTRAP_AUTO_EXPIRE;

    /* blacklist netblock /24 */
    if(BLACKLIST_NETBLOCK==1)
    {
      /* build up query */
      snprintf(mysqlquery_array[fd], 512,
        "INSERT DELAYED INTO blacklist (_blacklist,_description,_expire) VALUES ('%s','# spamtrap delivery: (%s)',%d)",
       host_array[fd][3], triplet_array[fd][2], expire);
    } else { /* blacklist host ip */
      /* build up query */
      snprintf(mysqlquery_array[fd], 512,
        "INSERT DELAYED INTO blacklist (_blacklist,_description,_expire) VALUES ('%s','# spamtrap delivery: (%s)',%d)",
      host_array[fd][2], triplet_array[fd][2], expire);
    }
    /* execute query */
    if(db_doquery(fd) == -1) return(db_failure(fd, "spamtrap"));
    
    logmessage("rcpt=%lu, spamtrap=new, host=%s (%s), from=%s, to=%s, size=%s, expire=%d\n",
      rcpt_count,                       /* recipient count      */
      host_array[fd][2],                /* host                 */
      host_array[fd][0],                /* hostname             */
      triplet_array[fd][1],             /* from                 */
      triplet_array[fd][2],             /* rcpt                 */
      triplet_array[fd][3],             /* size                 */
      expire                            /* expiry               */
    );

    return (1);
  }

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d spamtrap not found: %s\n", fd, triplet_array[fd][2]);

  /* no delivery attempted to spamtrap */
  return (0);
}
 
/* EOF */
