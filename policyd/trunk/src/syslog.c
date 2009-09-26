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
 *  SYSLOG FACILITIES
 */
static struct syslog_priority {
  char *str;
  int num;
} syslog_priorities[] = {
        
  /* priorities */
#ifdef LOG_EMERG
  { "LOG_EMERG", LOG_EMERG },
#endif  
#ifdef LOG_ALERT
  { "LOG_ALERT", LOG_ALERT },
#endif
#ifdef LOG_CRIT
  { "LOG_CRIT", LOG_CRIT },
#endif
#ifdef LOG_ERR
  { "LOG_ERR", LOG_ERR },
#endif  
#ifdef LOG_WARNING
  { "LOG_WARNING", LOG_WARNING },
#endif          
#ifdef LOG_NOTICE
  { "LOG_NOTICE", LOG_NOTICE }, 
#endif          
#ifdef LOG_INFO
  { "LOG_INFO", LOG_INFO },
#endif          
#ifdef LOG_DEBUG
  { "LOG_DEBUG", LOG_DEBUG },
#endif  


  /* facilities */
#ifdef LOG_KERN
  { "LOG_KERN", LOG_KERN },
#endif
#ifdef LOG_USER
  { "LOG_USER", LOG_USER },
#endif
#ifdef LOG_MAIL
  { "LOG_MAIL", LOG_MAIL },
#endif
#ifdef LOG_DAEMON
  { "LOG_DAEMON", LOG_DAEMON },
#endif
#ifdef LOG_AUTH
  { "LOG_AUTH", LOG_AUTH },
#endif
#ifdef LOG_SYSLOG
  { "LOG_SYSLOG", LOG_SYSLOG },
#endif
#ifdef LOG_AUTHPRIV
  { "LOG_AUTHPRIV", LOG_AUTHPRIV },
#endif
#ifdef LOG_LOCAL0
  { "LOG_LOCAL0", LOG_LOCAL0 },
#endif
#ifdef LOG_LOCAL1
  { "LOG_LOCAL1", LOG_LOCAL1 },
#endif
#ifdef LOG_LOCAL2
  { "LOG_LOCAL2", LOG_LOCAL2 },
#endif
#ifdef LOG_LOCAL3
  { "LOG_LOCAL3", LOG_LOCAL3 },
#endif
#ifdef LOG_LOCAL4
  { "LOG_LOCAL4", LOG_LOCAL4 },
#endif
#ifdef LOG_LOCAL5
  { "LOG_LOCAL5", LOG_LOCAL5 },
#endif
#ifdef LOG_LOCAL6
  { "LOG_LOCAL6", LOG_LOCAL6 },
#endif
#ifdef LOG_LOCAL7
  { "LOG_LOCAL7", LOG_LOCAL7 },
#endif
  { NULL, 0 }
};




/*
 * function: parse_syslog_priority
 *  purpose: split up tokens and ensure they are syslog facilities/priorities
 *   return: syslog facility
 */
int
parse_syslog_priority(char *str)
{
  char *token;
  int  n = -1;

  token = (char *) strtok (str, "|");
  if (token == NULL)
  {
    logmessage("fatal: error parsing (1st) syslog string: %s from %s\n", token, str);
    exit(-1);
  }

  /* ensure priority/facility is supported */
  syslog_token_set (token, &n);

  /* ensure priority/facility is supported */
  while ((token = (char *) strtok (NULL, "|")) != NULL)
    syslog_token_set (token, &n);

  if (n == -1)
  {
    logmessage("fatal: error parsing (2st) syslog string: %s from %s\n", token, str);
    exit(-1);
  }

  /* return priority */
  return (n);
}




/*
 * function: syslog_token_set
 *  purpose: check token against struct of priorities/facilities
 *   return: nada
 */
void
syslog_token_set(char *token, int *value)
{
  unsigned int     i;
  token = strip_space (token);

  for (i = 0; syslog_priorities[i].str != NULL; i++)
  {
    if(DEBUG > 3)
      logmessage("DEBUG: token = %s, got = %s\n",
        token, syslog_priorities[i].str);

    if (!strcasecmp (token, syslog_priorities[i].str))
    {
      if (*value == -1)
      {
        if(DEBUG > 3)
          logmessage("DEBUG: initial set: %s, n = 0x%08lx\n",
            token, syslog_priorities[i].num);

        *value = syslog_priorities[i].num;
      } else {

        if(DEBUG > 3)
          logmessage("DEBUG: subsequent OR: %s, n = 0x%08lx\n",
            token, *value | syslog_priorities[i].num);

        *value = *value | syslog_priorities[i].num;
      }
      
      return;
    }
  }

   logmessage("fatal: didn't find priority '%s', exiting\n", token);
   exit (-1);
}




/*
 * function: strip_space
 *  purpose: remove whitespace
 *   return: cleaned string
 */
char
*strip_space (char *str)
{
           char    *p;
  unsigned int     i = 0;

  if (strlen (str) == 0)
    return (str);

  for (i = 0; isspace (str[i]); i++)
    ;

  strcpy (str, str + i);

  p = str + strlen (str);
    while ((p--) != str && isspace (*p))
  *p = 0;

  return (str);
}


/* EOF */
