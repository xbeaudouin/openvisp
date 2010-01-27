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
 * function: blacklist_helo_check
 *  purpose: module to check if connecting mta is using a blacklisted HELO
 *   return: 1=yes, 0=no
 */
int
blacklist_helo_check(unsigned int fd)
{
  
  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking blacklist helo\n", fd);

  /* save an sql lookup if there is no helo information */
  if(triplet_array[fd][5][0] == 0x00)
    goto notfound;
  
  /* build up query & execute*/
  snprintf(mysqlquery_array[fd], 512,
    "SELECT COUNT(_helo) FROM blacklist_helo WHERE '%s' LIKE _helo", triplet_array[fd][5]);
  if(db_optquery(fd) == -1) return(db_failure(fd, "blacklist_helo"));

  /* we have forged helo attempt */
  if(mysql_optarray[fd][0] == 1)
  {
    int expire=0;

    if(DEBUG > 0)
      logmessage("DEBUG: fd: %d blacklisted helo found: %s from: %s\n", fd,
        triplet_array[fd][5],           /* helo */
        host_array[fd][2]);             /* host */

    /* never auto expire blacklist? */
    if(BLACKLIST_HELO_AUTO_EXPIRE > 0)
      expire=timenow+BLACKLIST_HELO_AUTO_EXPIRE;

    /* blacklist netblock/24 */
    if(BLACKLIST_NETBLOCK == 1)
    {
      /* build up query */
      snprintf(mysqlquery_array[fd], 512,
        "INSERT DELAYED INTO blacklist (_blacklist,_description,_expire) VALUES ('%s.%%','# blacklisted helo: (%s)',%d)",
       host_array[fd][2], triplet_array[fd][5], expire);
    } else { /* blacklist host ip */
      /* build up query */
      snprintf(mysqlquery_array[fd], 512,
        "INSERT DELAYED INTO blacklist (_blacklist,_description,_expire) VALUES ('%s','# blacklisted helo: (%s)',%d)",
        host_array[fd][2], triplet_array[fd][5], expire);
    }
    /* execute query */
    if(db_doquery(fd) == -1) return(db_failure(fd, "blacklist_helo"));
    
    logmessage("rcpt=%lu, blacklist_helo=new, host=%s (%s), from=%s, to=%s, size=%s, helo=%s, expire=%d\n",
      rcpt_count,                       /* recipient count      */
      host_array[fd][2],                /* host address         */
      host_array[fd][0],                /* hostname             */
      triplet_array[fd][1],             /* sender               */
      triplet_array[fd][2],             /* recipient            */
      triplet_array[fd][3],             /* size                 */
      triplet_array[fd][5],             /* helo                 */
      expire                            /* expiry date          */
    );

    if(STATISTICS == 1)
    {
      sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "blacklist_helo=new");
      if(db_optquery(fd) == -1) return(db_failure(fd, "blacklist_helo_statistics"));
    };

    return (1);
  }

notfound:

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d blacklist helo not found: %s\n", fd, triplet_array[fd][5]);

  /* no forged HELO attempt */
  return (0);
}
 
/* EOF */
