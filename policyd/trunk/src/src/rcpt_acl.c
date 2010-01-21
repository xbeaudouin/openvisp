#include "policyd.h"

/*
 * function: rcpt_acl_check
 *  purpose: check if the sender is allowed to mail the recipient/domain
 *   return: 1=yes, 0=no
 */
int
rcpt_acl_check (unsigned int fd)
{
  /* reset mysql_optarray[fd][0] before getting new value */
  mysql_optarray[fd][0]=0;

  
  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d checking rcpt_acl\n", fd);

  /* build up & execute query */
  snprintf(mysqlquery_array[fd], 512,
    "SELECT _wblist FROM rcpt_acl WHERE \
     (_sender='@%s' OR _sender='%s') AND (_rcpt='%s' OR _rcpt='@%s') \
     ORDER BY _priority DESC LIMIT 1",
      host_array[fd][7], triplet_array[fd][1], triplet_array[fd][2], host_array[fd][9]);
  if(db_charquery(fd) == -1) return(db_failure(fd, "rcpt_acl"));

  /* blacklisted? */
  if(mysqlchar_array[fd][0][0] == 'b')
  {
    if(DEBUG > 0)
    {
      logmessage("DEBUG: fd: %d rcpt_acl blacklist found: @%s/%s -> %s/@%s\n", fd,
        host_array[fd][7], triplet_array[fd][1], triplet_array[fd][2], host_array[fd][9]);
      logmessage("DEBUG: fd: %d bypassing other modules\n", fd);
    }

    logmessage("rcpt=%lu, rcpt_acl=block, host=%s (%s), from=%s, to=%s, size=%s\n",
      rcpt_count,               /* recipient count      */
      host_array[fd][2],        /* ip address           */
      host_array[fd][0],        /* hostname             */
      triplet_array[fd][1],     /* from address         */
      triplet_array[fd][2],     /* recipient address    */
      triplet_array[fd][3]);    /* mail size            */

    return (2); /* found */

  } else if(mysqlchar_array[fd][0][0] == 'w') {
  
      logmessage("DEBUG: fd: %d rcpt_acl whitelist found: %s/%s -> %s/%s\n", fd,
        host_array[fd][7], triplet_array[fd][1], triplet_array[fd][2], host_array[fd][9]);

      return (1);  /* whitelisted */
  }

  if(DEBUG > 0)
    logmessage("DEBUG: fd: %d rcpt_acl not found: %s %s\n",
      fd, host_array[fd][7], triplet_array[fd][1]);

  return (0);   /* not found */
}
