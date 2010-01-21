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
 * function: helo_check
 *  purpose: module to check if connecting host is randomizing their HELO
 *   return: 1=yes, 0=no
 */
int
helo_check(unsigned int fd)
{
  
  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking helo\n", fd);

  /* reset value */
  mysql_optarray[fd][0] = 0;
    
  /* save an sql lookup if there is no helo information */
  if(triplet_array[fd][5][0] == 0x00)
    goto notfound;
  
  /* build up query & execute */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT COUNT(_host) FROM helo WHERE _host='%s'", host_array[fd][2]);
  if(db_optquery(fd) == -1) return(db_failure(fd, "helo"));

  /* we have helo abuse */
  if(mysql_optarray[fd][0] >= HELO_MAX_COUNT)
  {
    int expire=0;

    if(DEBUG > 0)
      logmessage("DEBUG: fd: %d helo abuse: %s from: %s (%d unique helo's)\n", fd,
        host_array[fd][2],              /* host       */
        triplet_array[fd][5],           /* helo       */
	HELO_MAX_COUNT);                /* helo count */

    /* never auto expire helo blacklist? */
    if (HELO_BLACKLIST_AUTO_EXPIRE > 0)
      expire=timenow+HELO_BLACKLIST_AUTO_EXPIRE;

    /* build up query */
    snprintf(mysqlquery_array[fd], 512,
      "INSERT DELAYED INTO blacklist (_blacklist,_description,_expire) VALUES ('%s','# helo abuse',%d)",
      host_array[fd][2], expire);
    if(db_doquery(fd) == -1) return(db_failure(fd, "helo"));

    logmessage("rcpt=%lu, helo=abuse, host=%s (%s), from=%s, to=%s, size=%s, helo=%s\n",
      rcpt_count,                       /* recipient count      */
      host_array[fd][2],                /* host address         */
      host_array[fd][0],                /* hostname             */
      triplet_array[fd][1],             /* sender               */
      triplet_array[fd][2],             /* recipient            */
      triplet_array[fd][3],             /* size                 */
      triplet_array[fd][5]              /* helo                 */
    );

    /* clean up helo table entries */
    /* build up query */
    snprintf(mysqlquery_array[fd], 512,
      "DELETE QUICK FROM helo WHERE _host='%s'", host_array[fd][2]);
    if(db_doquery(fd) == -1) return(db_failure(fd, "helo"));

    if(STATISTICS == 1)
    {
      sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "helo=abuse");
      if(db_optquery(fd) == -1) return(db_failure(fd, "helo_abuse"));
    };

    return (1);
  }

notfound:

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d helo abuse not found: %s from: %s\n", fd, triplet_array[fd][5], host_array[fd][2]);
  
  /* reset value */
  mysql_optarray[fd][0] = 0;
    
  /* build up query & execute */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT COUNT(_host) FROM helo WHERE _host='%s' AND _helo='%s'", host_array[fd][2], triplet_array[fd][5]);
  if(db_optquery(fd) == -1) return(db_failure(fd, "helo"));

  /* helo has not been previously stored there */
  if(mysql_optarray[fd][0] == 0)
  {
    int expire=0;

    /* never auto expire helo blacklist? */
    if (HELO_AUTO_EXPIRE > 0)
      expire=timenow+HELO_AUTO_EXPIRE;

    /* build up query & execute */
    snprintf(mysqlquery_array[fd], 512,
      "INSERT DELAYED INTO helo (_host,_helo,_expire) VALUES ('%s','%s',%d)",
      host_array[fd][2], triplet_array[fd][5], expire);
    if(db_doquery(fd) == -1) return(db_failure(fd, "helo"));
  }

  /* no forged HELO attempt */
  return (0);
}
 
/* EOF */
