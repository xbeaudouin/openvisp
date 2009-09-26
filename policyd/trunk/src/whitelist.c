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
 * function: whitelist_check
 *  purpose: check if the connecting host is whitelisted
 *   return: 1=yes, 0=no
 */
int
whitelist_check (unsigned int fd)
{
  
  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking whitelist\n", fd);

  /* whitelist null senders */
  if((WHITELISTNULL == 1) && (strlen(triplet_array[fd][1]) == 2))
    mysql_optarray[fd][0] = 1;

  else {

    /* build up & execute query */
    snprintf(mysqlquery_array[fd], 512,
      "SELECT COUNT(*) FROM whitelist WHERE _whitelist='%s' OR _whitelist='%s' OR _whitelist='%s' OR _whitelist='%s'",
        host_array[fd][2], host_array[fd][3], host_array[fd][4], host_array[fd][5]);

    if(db_optquery(fd) == -1) return(db_failure(fd, "whitelist"));
  }

  /* whitelisted */
  if(mysql_optarray[fd][0] >= 1)
  {
    if(DEBUG > 0)
    {
      logmessage("DEBUG: fd: %d whitelist found: %s\n", fd, host_array[fd][2]);
      logmessage("DEBUG: fd: %d bypassing other modules\n", fd);
    }

    logmessage("rcpt=%lu, whitelist=update, host=%s (%s), from=%s, to=%s, size=%s\n",
      rcpt_count,               /* recipient count      */
      host_array[fd][2],        /* ip address           */
      host_array[fd][0],        /* hostname             */
      triplet_array[fd][1],     /* from address         */
      triplet_array[fd][2],     /* recipient address    */
      triplet_array[fd][3]);    /* mail size            */

    return (1); /* found */
  }

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d whitelist not found: %s\n", fd, host_array[fd][2]);

  return (0);   /* not found */
}
 


/*
 * function: whitelist_sender_check
 *  purpose: check if the sender address/domain host is whitelisted
 *   return: 1=yes, 0=no
 */
int
whitelist_sender_check (unsigned int fd)
{
  
  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking whitelist sender/domain\n", fd);

  /* build up & execute query */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT COUNT(*) FROM whitelist_sender WHERE _whitelist='@%s' OR _whitelist='%s'",
      host_array[fd][7], triplet_array[fd][1]);
  if(db_optquery(fd) == -1) return(db_failure(fd, "whitelist_sender"));

  /* whitelisted */
  if(mysql_optarray[fd][0] >= 1)
  {
    if(DEBUG > 0)
    {
      logmessage("DEBUG: fd: %d whitelist sender found: %s/%s\n", fd,
        host_array[fd][7], triplet_array[fd][1]);
      logmessage("DEBUG: fd: %d bypassing other modules\n", fd);
    }

    logmessage("rcpt=%lu, whitelist_sender=update, host=%s (%s), from=%s, to=%s, size=%s\n",
      rcpt_count,               /* recipient count      */
      host_array[fd][2],        /* ip address           */
      host_array[fd][0],        /* hostname             */
      triplet_array[fd][1],     /* from address         */
      triplet_array[fd][2],     /* recipient address    */
      triplet_array[fd][3]);    /* mail size            */

    return (1); /* found */
  }

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d whitelist_sender not found: %s\n", fd, host_array[fd][2]);

  return (0);   /* not found */
}
 


/*
 * function: whitelist_dnsname_check
 *  purpose: check if the dns sender address/domain host is whitelisted
 *   return: 1=yes, 0=no
 */
int
whitelist_dnsname_check (unsigned int fd)
{
  
  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking whitelist dnsname\n", fd);

  /* save sql lookup if there is no hostname information */
  if(strcmp(host_array[fd][0], "unknown") == 0)
    goto end;
      
  /* build up & execute query */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT COUNT(*) FROM whitelist_dnsname WHERE '%s' LIKE _whitelist",
      host_array[fd][0]);
  if(db_optquery(fd) == -1) return(db_failure(fd, "whitelist_dnsname"));

  /* whitelisted */
  if(mysql_optarray[fd][0] >= 1)
  {
    if(DEBUG > 0)
    {
      logmessage("DEBUG: fd: %d whitelist dnsname found: %s/%s\n", fd,
        host_array[fd][7], triplet_array[fd][1]);
      logmessage("DEBUG: fd: %d bypassing other modules\n", fd);
    }

    logmessage("rcpt=%lu, whitelist_dnsname=update, host=%s (%s), from=%s, to=%s, size=%s\n",
      rcpt_count,               /* recipient count      */
      host_array[fd][2],        /* ip address           */
      host_array[fd][0],        /* hostname             */
      triplet_array[fd][1],     /* from address         */
      triplet_array[fd][2],     /* recipient address    */
      triplet_array[fd][3]);    /* mail size            */

    return (1); /* found */
  }

end:
  
  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d whitelist_sender not found: %s\n", fd, host_array[fd][2]);

  return (0);   /* not found */
}
 
/* EOF */
