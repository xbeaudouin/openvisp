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
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *  or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 *  for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation Inc.,
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *
 *
 */

int
main(int argc, char **argv)
{
  int c;
  memset(mysqlquery_array[0], 0x00, 512);

  if(argc < 2)
    usage(argv[0]);

  while ((c = getopt(argc,argv,":c:")) != EOF)
  { 
    switch(c)
    { 
      case 'c':
        configpath=optarg;
        read_conf(1);
        break;
        
      default:
        usage(argv[0]);
    }
  }

  logmessage("processing statistics: %s %s\n", PACKAGE_NAME, PACKAGE_VERSION);
      
  /* connect to mysql */
#if defined(MYSQL_VERSION_ID) && MYSQL_VERSION_ID >= 40000
  mysql_server_init(0, NULL, NULL);
#endif
  mysql = db_connect(MYSQLDBASE);

  /* store current time */
  timenow=gettime();
  mysql_timeout=600;

  if(GREYLISTING == 1)
  {
    logmessage("<--\n");
    logmessage("greylisting: triplet information\n");
    logmessage("-->\n");
    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "SELECT 'Triplets: ' as description, count(*) AS my_count FROM triplet");
    if(db_printquery(0) == -1) exit(-1);

    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "SELECT 'Verified: ' as description, count(*) AS my_count FROM triplet \
       WHERE _count > 0"); 
    if(db_printquery(0) == -1) exit(-1);
    
    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "SELECT 'Unverified: ' as description, count(*) AS my_count FROM triplet \
       WHERE _count = 0"); 
    if(db_printquery(0) == -1) exit(-1);

          
    logmessage("<--\n");
    logmessage("greylisting: top 10 networks with validated triplets\n");
    logmessage("-->\n");
    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "SELECT _host, count(*) AS my_count FROM triplet WHERE _count > 0 GROUP BY \
        _host ORDER BY my_count DESC limit 10");
    if(db_printquery(0) == -1) exit(-1);

    
    /* expunge unvalidated triplets */
    logmessage("<--\n");
    logmessage("greylisting: top 10 networks with unvalidated triplets\n");
    logmessage("-->\n");
    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "SELECT _host, count(*) AS my_count FROM triplet WHERE _count = 0 GROUP BY \
        _host ORDER BY my_count DESC limit 10");
    if(db_printquery(0) == -1) exit(-1);

    
    /* expunge unvalidated triplets */
    logmessage("<--\n");
    logmessage("greylisting: top 10 networks with most delivered mails\n");
    logmessage("-->\n");
    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "SELECT _host, sum(_count) AS scnt FROM triplet WHERE _count > 0 GROUP BY \
        _host ORDER BY scnt DESC limit 10");
    if(db_printquery(0) == -1) exit(-1);
  }

  if(HELO_CHECK == 1)
  { 
    logmessage("<--\n");
    logmessage("helo: top 10 hosts with different identities\n");
    logmessage("-->\n");
    /* build up & execute query */
    snprintf(mysqlquery_array[0], 512,
      "SELECT _host, count(*) AS my_count FROM helo GROUP BY _host \
      ORDER BY my_count DESC limit 10;");
    if(db_printquery(0) == -1) exit(-1);
  }

  mysql_close(mysql);
    
  return (0);
}
 
/* EOF */
