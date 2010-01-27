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
 * function: blacklist_check
 *  purpose: module to check if connecting host/network is blacklisted.
 *   return: 1=yes, 0=no
 */
int
blacklist_check(unsigned int fd)
{

  /* reset mysql_optarray[fd][0] before getting new value */
  mysql_optarray[fd][0]=0;

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking blacklist\n", fd);

  /* build up query & execute */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT COUNT(*) FROM blacklist WHERE _blacklist='%s' OR _blacklist='%s' OR _blacklist='%s' OR _blacklist='%s'",
    host_array[fd][2], host_array[fd][3], host_array[fd][4], host_array[fd][5]);
  if(db_optquery(fd) == -1) return(db_failure(fd, "blacklist"));

  /* blacklisted */
  if(mysql_optarray[fd][0] >= 1)
  {
    if(DEBUG > 0)
    {
      logmessage("DEBUG: fd: %d blacklist found: %s\n", fd, host_array[fd][2]);
      logmessage("DEBUG: fd: %d bypassing other modules\n", fd);
    }

    logmessage("rcpt=%lu, blacklist=block, host=%s (%s), from=%s, to=%s, size=%s\n",
      rcpt_count,                       /* recipient count      */
      host_array[fd][2],                /* host address         */
      host_array[fd][0],                /* hostname             */
      triplet_array[fd][1],             /* sender               */
      triplet_array[fd][2],             /* recipient            */
      triplet_array[fd][3]);            /* size                 */

    if(STATISTICS == 1)
    {
      sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "blacklist=block");
      if(db_optquery(fd) == -1) return(db_failure(fd, "blacklist_statistics"));
    };

    return (1); /* found */
  }

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d blacklist not found: %s\n", fd, host_array[fd][2]);

  return (0);   /* not found */
}



/*
 * function: blacklist_sender_check
 *  purpose: check if the sender address/domain host is blacklisted
 *   return: 1=yes, 0=no
 */
int
blacklist_sender_check (unsigned int fd)
{
  /* reset mysql_optarray[fd][0] before getting new value */
  mysql_optarray[fd][0]=0;

  
  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking blacklist sender/domain\n", fd);

  /* build up & execute query */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT COUNT(*) FROM blacklist_sender WHERE _blacklist='@%s' OR _blacklist='%s'",
      host_array[fd][7], triplet_array[fd][1]);
  if(db_optquery(fd) == -1) return(db_failure(fd, "blacklist_sender"));

  /* blacklisted */
  if(mysql_optarray[fd][0] >= 1)
  {
    if(DEBUG > 0)
    {
      logmessage("DEBUG: fd: %d blacklist sender found: %s/%s\n", fd,
        host_array[fd][7], triplet_array[fd][1]);
      logmessage("DEBUG: fd: %d bypassing other modules\n", fd);
    }

    logmessage("rcpt=%lu, blacklist_sender=block, host=%s (%s), from=%s, to=%s, size=%s\n",
      rcpt_count,               /* recipient count      */
      host_array[fd][2],        /* ip address           */
      host_array[fd][0],        /* hostname             */
      triplet_array[fd][1],     /* from address         */
      triplet_array[fd][2],     /* recipient address    */
      triplet_array[fd][3]);    /* mail size            */

    return (1); /* found */
  }

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d blacklist_sender not found: %s %s\n",
      fd, host_array[fd][7], triplet_array[fd][1]);

  return (0);   /* not found */
}
 


/*
 * function: blacklist_dnsname_check
 *  purpose: check if the dns sender address/domain host is blacklisted
 *   return: 1=yes, 0=no
 */
int
blacklist_dnsname_check (unsigned int fd)
{
  /* reset mysql_optarray[fd][0] before getting new value */
  mysql_optarray[fd][0]=0;

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking blacklist dnsname\n", fd);

  /* save sql lookup if there is no hostname information */
  if(strcmp(host_array[fd][0], "unknown") == 0)
    goto end;
      
  /* build up & execute query */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT COUNT(*) FROM blacklist_dnsname WHERE '%s' LIKE _blacklist",
      host_array[fd][0]);
  if(db_optquery(fd) == -1) return(db_failure(fd, "blacklist_dnsname"));

  /* blacklisted */
  if(mysql_optarray[fd][0] >= 1)
  {
    if(DEBUG > 0)
    {
      logmessage("DEBUG: fd: %d blacklist dnsname found: %s/%s\n", fd,
        host_array[fd][7], triplet_array[fd][1]);
      logmessage("DEBUG: fd: %d bypassing other modules\n", fd);
    }

    logmessage("rcpt=%lu, blacklist_dnsname=block, host=%s (%s), from=%s, to=%s, size=%s\n",
      rcpt_count,               /* recipient count      */
      host_array[fd][2],        /* ip address           */
      host_array[fd][0],        /* hostname             */
      triplet_array[fd][1],     /* from address         */
      triplet_array[fd][2],     /* recipient address    */
      triplet_array[fd][3]);    /* mail size            */

    if(STATISTICS == 1)
    {
      sprintf(mysqlquery_array[fd], "UPDATE statistics set _count=_count+1 where _action='%s'", "blacklist_dnsname=block");
      if(db_optquery(fd) == -1) return(db_failure(fd, "blacklist_dnsname_statistics"));
    };
  
    return (1); /* found */
  }

end:
  
  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d blacklist_sender not found: %s\n", fd, host_array[fd][2]);

  return (0);   /* not found */
}
  

/* EOF */
