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
 *  function: cidr_ip_match
 *   purpose: 
 *    return: 
 */
int
cidr_ip_match (unsigned long ip, char *range)
{
		 int	mask;
	unsigned long	t;
	  signed long	lx;
	unsigned long	s;   /* start bit */
	unsigned long	e;   /* end   bit */
	         char	*p;
                 char   buf[64];

	memset (buf, 0, sizeof (buf));
	strncpy (buf, range, sizeof (buf) - 1);

	p = strtok (buf, "/");
	if ((lx=inet_addr(p)) == -1)
		return -1;

	p = strtok (NULL, "/");
	if (p != NULL) {
		mask = atoi (p);
		if (mask < 0 || mask > 32)
			return -1;      /* invalid mask */
	} else
		mask = 32;              /* single IP */

	lx = htonl (lx);
	t = htonl (ip);

	s = (lx & (0 - (1 << (32 - mask))));
	e = (lx | ((1 << (32 - mask)) - 1));

	if (t >= s && t <= e) {
		if(DEBUG)
		  logmessage("found: ip(%ld) range(%s)\n", ip, range);
		return (1);
	} else {
		if(DEBUG)
		  logmessage("not found: ip(%ld) range(%s)\n", ip, range);
		return (-1);
	}
}

/* EOF */
