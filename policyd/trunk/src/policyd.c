#include "policyd.h"


/*
 *
 *
 *                           Policy Daemon
 *
 *  policy daemon is used in conjuction with postfix to combat spam.
 *
 *  Copyright (C) 2007 Nigel Kukard <nkukard@lbsd.net>
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


int
main(int argc, char **argv)
{
  int c, numi, maxi, maxfd, listenfd, connfd, sockfd;
  int nready, client[MAXFDS];
  fd_set rset, wset, rallset, wallset;
  char host[48];
  socklen_t clilen;
  struct sockaddr_in cliaddr, servaddr;
  struct rlimit rlimit_nofile;

  if(argc < 2) usage(argv[0]);
  while ((c = getopt(argc,argv,":c:v")) != EOF)
  {
    switch(c)
    {
      case 'c':
        configpath=optarg;
        read_conf(0);
        break;

      case 'v':
	logmessage("%s %s\n", PROJECT, VERSION);
	exit(0);

      default:
        usage(argv[0]);
    }
  }
  
  logmessage("starting %s %s\n", PROJECT, VERSION);

  /*
   *  raise RLIMIT_FSIZE to MAXFDS, bail if cannot.
   */
  if (getrlimit(RLIMIT_NOFILE, &rlimit_nofile) == -1)
  {
    logmessage("cannot get rlimit: %s\n", strerror(errno));
    exit (-1);
  } else {
    if((MAXFDS+1) > rlimit_nofile.rlim_max)
    {
      /* if it needs increasing, keep 1 fd for logging */
      rlimit_nofile.rlim_max = MAXFDS+1;
      rlimit_nofile.rlim_cur = MAXFDS+1;
      if(setrlimit(RLIMIT_NOFILE, &rlimit_nofile) == -1)
      {
        logmessage("cannot set rlimit: %s\n", strerror(errno));
        exit (-1);
      };
    };
  };
  
  if(DEBUG)
    logmessage("DEBUG: fd: 0: rlimit: max: %d cur: %d\n",
      rlimit_nofile.rlim_cur, rlimit_nofile.rlim_max);

  /*
   *  bind to port
   */
  listenfd = w_socket(AF_INET, SOCK_STREAM, 0);
  bzero(&servaddr, sizeof(servaddr));
  servaddr.sin_family = AF_INET;
  servaddr.sin_addr.s_addr=inet_addr(BINDHOST);
  servaddr.sin_port=htons(BINDPORT);

  w_bind(listenfd, (struct sockaddr *) &servaddr, sizeof(servaddr));
  w_listen(listenfd, LISTENQ);

  /*
   *  drop all privileges
   */
  drop_privs();

  /*
   *  reset counters
   */
  rcpt_count=0;
  mysql_timeout=5;
  mysql_failure_count=0;
  last_mail_time=gettime();
  if((GREYLIST_HOSTADDR > 4) || (GREYLIST_HOSTADDR < 1))
    GREYLIST_HOSTADDR=3;
    
  /* 
   *  prefer fd sets
   */
  maxfd = listenfd;                     /* initialize */
  maxi = -1;                            /* index into client[] array */
  for(numi=0;numi<MAXFDS;numi++)
    client[numi] = -1;                  /* indicates available entry */

  FD_ZERO(&rallset);
  FD_ZERO(&wallset);
  FD_SET(listenfd, &rallset);
  
  /*
   *  connect to mysql
   */
#if defined(MYSQL_VERSION_ID) && MYSQL_VERSION_ID >= 40000
  mysql_server_init(0, NULL, NULL);
#endif  
  mysql = db_connect(MYSQLDBASE);

  /*
   *  signal: terminate cleanly
   */
  signal(SIGTERM, fold);
  signal(SIGINT, fold);

  /* enter infinite loop */
  for(;;)
  {
    rset = rallset;                        /* structure assignment (read set)  */
    wset = wallset;                        /* structure assignment (write set) */

    nready = w_select(maxfd + 1, &rset, &wset, NULL, NULL);  /* ready,aim,fire */
    if(nready == 0)                            /* hit system interupt/timeout? */
      continue;

    if(FD_ISSET(listenfd, &rset))                     /* new client connection */
    {
      int found_free_slot = 0;

      clilen=sizeof(cliaddr);
      connfd=w_accept(listenfd, (struct sockaddr *) &cliaddr, &clilen);

      for(numi=0; numi < MAXFDS; numi++) {
        if(client[numi] < 0) {
          client[numi] = connfd;                       /* save file descriptor */
          if(DEBUG > 0)
            logmessage("DEBUG: saved fd: numi = %d, connfd = %d\n", numi, connfd);
          found_free_slot = 1;
          break;
        }
      }

      /* check if we ran out of slots and didn't find one above */
      if (!found_free_slot)
      {
        logmessage("WARNING: No free slots found, closing connection from %s:%d\n",
          w_inet_ntop(AF_INET, &cliaddr.sin_addr, host, sizeof(host)),
          ntohs(cliaddr.sin_port));
        w_close(connfd);
        continue;
      }

      /* tcp acl check */
/*
      if(w_tcp_conn_acl(w_inet_ntop(AF_INET, &cliaddr.sin_addr, host, sizeof(host))) == -1)
      {
	 logmessage("WARNING: connection attempt from: %s\n",
	   w_inet_ntop(AF_INET, &cliaddr.sin_addr, host, sizeof(host)));
	 w_close(connfd);
	 continue;
      }
*/
      /* max fds */

      logmessage("connection from: %s port: %d slots: %d of %d used\n",
        w_inet_ntop(AF_INET, &cliaddr.sin_addr, host, sizeof(host)),
        ntohs(cliaddr.sin_port), numi, MAXFDS);

      FD_SET(connfd, &rallset);            /* add new descriptor to set        */
      buf_counter[connfd] = 0;             /* zero current buffer counters     */
      buf_size[connfd] = 0;
                                        /* go ahead, be anal, clear the buffer */
      bzero(buf[connfd], sizeof(buf[connfd]));
      if(connfd > maxfd) maxfd = connfd;   /* for select                       */
      if(numi > maxi)    maxi = numi;      /* max index in client[] array      */
      if(--nready <= 0)  continue;         /* no more readable descriptors     */

    }

    /* check all active clients for data */
    for(numi=0 ; numi<= maxi ; numi++)
    {
      if((sockfd=client[numi]) < 0)
        continue;

      /* check if readable socket is ready */
      if(FD_ISSET(sockfd, &rset))
      {
	ssize_t rres;
	
        if(DEBUG > 2)
          logmessage("DEBUG: fd: %d select(): fd %d is ready for read\n", sockfd, sockfd);
     
        /* read as much data as we can */
        rres = w_read(sockfd,buf[sockfd],MAXLINE);
	switch (rres)
	{
          case -3:
          case -1:
            w_close(sockfd);            /* shut down socket           */
            FD_CLR(sockfd, &rallset);   /* remove fd from read set    */
            client[numi] = -1;          /* make descriptor available  */
            break;  
	  
	  case -2:                      /* got the information needed */
            chk_pol(sockfd);            /* sort Postfix's information */
            FD_CLR(sockfd, &rallset);   /* remove fd from read set    */
            FD_SET(sockfd, &wallset);   /* add fd to write set        */
            break;
        }
	
        if(--nready <= 0)
          break;                 /* no more readable file descriptors */
      }

      /* check if writable socket is ready */
      if(FD_ISSET(sockfd, &wset))
      {
	ssize_t rres;
	
        if(DEBUG > 2)
          logmessage("DEBUG: fd: %d select(): fd %d is ready for write\n", sockfd, sockfd);

        /* write as much data as we can */
	rres = w_write(sockfd,buf[sockfd]);
	switch (rres)
	{
          case -1:
            w_close(sockfd);            /* shut down socket          */
            FD_CLR(sockfd, &wallset);   /* remove fd from write set  */
            client[numi] = -1;          /* make descriptor available */
	    break;  

          case -2:                      /* write was successfull     */
            FD_CLR(sockfd, &wallset);   /* remove fd from read set   */
            FD_SET(sockfd, &rallset);   /* add fd to write set       */
            buf_size[sockfd] = 0;       /* reset buffer size         */
            buf_counter[sockfd] = 0;    /* reset buffer counter      */
            break;
	}
	
        if(--nready <= 0)
          break;                /* no more writable file descriptors */
      }
    }
  }
  
  return (0);                                  /* never reached */
}




/*
 * function: chk_pol
 *  purpose: sort and parse data from postfix
 *   return: 0 for connection closing
 */
void
chk_pol(unsigned int fd)
{
  /*
   *  clear buffers for filedescriptor
   */
  clear_var(fd);
  
  /*
   *  parse buf and sort into array
   */
  parse_buf(fd, buf[fd]);
  
  /*
   *  ensure we have the information needed of enabled modules
   */
  if(module_info_check(fd) == -1)
  {
    policy_reply(fd, -8, 0);
    return;
  }

  /*
   *  rcpt: update mail counter, all needed module data is valid
   *  time: set time to current time
   */
  rcpt_count++;
  timenow=gettime();

  /*
   *  check to see that database connection is live
   */
  if(DATABASE_KEEPALIVE == 1)
  {

    /*
     *  do not probe database unless at least 30 seconds 
     *  has passed since the last mail
     */
    if(timenow >= (last_mail_time+30))
    {                             /* timer has expired */
      int probe;
      probe=database_probe(fd);

      /*
       *  handle database failure
       */
      if(probe == -20)
      {
        last_mail_time=timenow;
        goto passthrough;
      }
    }

    last_mail_time=timenow;
  }


    
  /*
   * future modules go here: (order matters)
   * 
   *  [X]: whitelisting
   *  [X]: whitelisting(sender/domain)
   *  [X]: whitelisting(dnsname)
   *  [X]: blacklisting
   *  [X]: blacklisting(helo)
   *  [X]: blacklisting(sender/domain)
   *  [X]: spamtrap
   *  [X]: helo check (multiple helo forgeries)
   *  [X]: greylisting
   *  [X]: sender throttling
   *  [X]: recipient throttling
   * 
   */
  
  /* check if whitelisted */
  if(WHITELISTING==1)
  {
    switch (whitelist_check(fd))
    {
      case 1:
        policy_reply(fd, 0, 0);
        return;

      case -20:
        return;
    }
      
    /* check if sender/domain is whitelisted */
    if(WHITELISTSENDER==1)
      switch (whitelist_sender_check(fd))
      {
        case 1:
          policy_reply(fd, 0, 0);
          return;

        case -20:
          return;
      }
    
    /* check if sender/domain is whitelisted */
    if(WHITELISTDNSNAME==1)
      switch (whitelist_dnsname_check(fd))
      {
        case 1:
          policy_reply(fd, 0, 0);
          return;

        case -20:
          return;
      }
  } /* end of all whitelisting modules */


  /* blacklist */
  if(BLACKLISTING==1)
  {
    switch (blacklist_check(fd))
    {
      case 1:
        policy_reply(fd, -2, 0);
        return;

      case -20:
        return;
    }

    /* blacklist helo */
    if(BLACKLIST_HELO==1)
      switch (blacklist_helo_check(fd))
      {
        case 1:
          policy_reply(fd, -2, 0);
          return;

        case -20:
          return;
      }
  
    /* blacklist sender/domain */
    if(BLACKLISTSENDER==1)
      switch (blacklist_sender_check(fd))
      {
        case 1:
          policy_reply(fd, -2, 0);
          return;

        case -20:
          return;
      }

    /* blacklist dnsname */
    if(BLACKLISTDNSNAME==1)
      switch(blacklist_dnsname_check(fd))
      {
        case 1:
          policy_reply(fd, -2, 0);
          return;

        case -20:
          return;
      }
  } /* end of all blacklisting modules */


  /* spamtrap */
  if(SPAMTRAPPING==1)
    switch(spamtrap_check(fd))
    {
      case 1:
        policy_reply(fd, -4, 0);
        return;

      case -20:
        return;
    }

  /* helo check */
  if(HELO_CHECK==1)
    switch(helo_check(fd))
    {
      case 1:
        policy_reply(fd, -6, 0);
        return;
     
      case -20:
        return;
    }
  
  /* check if greylisted */
  if(GREYLISTING==1)
    switch (greylist_check(fd)) 
    {
      case 0:  
      	if((SENDERTHROTTLE == 0) && (RECIPIENTTHROTTLE == 0))
        {
          policy_reply(fd, 0, 0);
	  return;
	}
	break;
	
      case -1:  /* greylist reject */
      	policy_reply(fd, -1, 0);
	return;

      case -20:
        return;
   }

  /* check if sender is throttled */
  if(SENDERTHROTTLE==1)
    switch (throttle_check(fd))
    {
      case 0:
      	if(RECIPIENTTHROTTLE == 0)
	{
          policy_reply(fd, 0, 0);
          return;
	}
	break;

      case -3:   /* message size too big */
        policy_reply(fd, -3, 0);
        return;

      case -5:   /* quota reached */
        policy_reply(fd, -5, 0);
       	return;

      case -20:
        return;
    }

  /* check if recipient is throttled */
  if(RECIPIENTTHROTTLE==1)
  {
    switch (throttle_rcpt(fd))
    {
      case 0:
        /* allow mail */
	policy_reply(fd, 0, 0);
        return;
	
      case -7:
        policy_reply(fd, -7, 0);
        break;

      case -20:
        return;
    }

    return;
  }

  /* end of modules */

passthrough:

  /*
   * in order to reach here, no modules have been used.
   * switch into pass-through mode and allow.
   */
  logmessage("rcpt=%lu, module=bypass, host=%s (%s), from=%s, to=%s, size=%s\n",
    rcpt_count,                       /* recipient count      */
    host_array[fd][2],                /* host                 */
    host_array[fd][0],                /* hostname             */
    triplet_array[fd][1],             /* from                 */
    triplet_array[fd][2],             /* rcpt                 */
    triplet_array[fd][3]);            /* size                 */
  
  policy_reply(fd, 0, 0);
  return;
}




/*
 * function: clear_var
 *  purpose: clear all variables associated with file descriptor
 *   return: nada
 */
void
clear_var(unsigned int fd)
{
  /*
   *  allow by default
   */
  action_array[fd]=0;
  
  /*
   *  clear all buffers
   */
  for(i[fd]=0;i[fd]<20;i[fd]++)
  {
    memset(policy_array[fd][i[fd]],    0x00, 64);
    memset(triplet_array[fd][i[fd]],   0x00, 64);
    memset(host_array[fd][i[fd]],      0x00, 64);
    memset(mysqlchar_array[fd][i[fd]], 0x00, 64);
  }
}




/*
 * function: parse_buf
 *  purpose: sort what data Postfix has given us
 *   return: nada
 */
void
parse_buf(unsigned int fd, char *buf)
{

  /*
   *  dump all contents from postfix into an array
   */
  for(i[fd]=0, x[fd]=0, y[fd]=0; i[fd] < strlen(buf); i[fd]++,y[fd]++)
  {     
    if(buf[i[fd]] == '\n')
    {
      x[fd]++; /* move to next array element       */
 
      /* null terminate all arrays */
      policy_array[fd][x[fd]][y[fd]]='\0';
 
      i[fd]++; /* we dont want newlines            */
      y[fd]=0; /* reset new array element position */

      if(DEBUG > 0)
        logmessage("DEBUG: fd: %d policy_array[%d][%d]:%s\n", fd, fd, x[fd]-1, policy_array[fd][x[fd]-1]);
    }

    if(y[fd] <= 63)
      policy_array[fd][x[fd]][y[fd]]=tolower(buf[i[fd]]);
  }

  
  /*
   *  get what we want out of array
   */
  for(i[fd]=0;i[fd]<20;i[fd]++)
  {

    /* connecting ip */
    if(strncmp(policy_array[fd][i[fd]], "client_address=", 15) == 0)
    {
      extract_ip(fd, policy_array[fd][i[fd]]);
      extract_ipfill(fd, policy_array[fd][i[fd]]);

      strncpy(host_array[fd][2], policy_array[fd][i[fd]]+15, 64);
      strncpy(triplet_array[fd][0], extract_ip_array[fd], 64);
    }

    /* connecting ip hostname */
    if(strncmp(policy_array[fd][i[fd]], "client_name=", 12) == 0)
    {
      extract(fd, policy_array[fd][i[fd]], 11);
      strncpy(host_array[fd][0], extract_array[fd], 64);
    }

    /* sender address */
    if(strncmp(policy_array[fd][i[fd]], "sender=", 7) == 0)
    {
      char *s;
      extract(fd, policy_array[fd][i[fd]], 6);
      strncpy(triplet_array[fd][1], extract_array[fd], 64);

      /* add in some type of data for the database */
      if(triplet_array[fd][1][0] == 0x00)
        strncpy(triplet_array[fd][1], "<>", 3); /* append null */

      /* optinout needed for recipient domain */
      s=strrchr(extract_array[fd], '@');
      if(s != NULL)
      {
        strncpy(host_array[fd][7], s+1, 64);
        strncpy(host_array[fd][6], extract_array[fd],
          strlen(triplet_array[fd][1]) - strlen(host_array[fd][7]) -1);
      }
    }

    /* recipient address */
    if(strncmp(policy_array[fd][i[fd]], "recipient=", 10) == 0)
    {
      char *r;
      extract(fd, policy_array[fd][i[fd]], 9);
      strncpy(triplet_array[fd][2], extract_array[fd], 60);
                                   /* max length of recipient in mysql */
      /* optinout needed for recipient domain */
      r=strrchr(extract_array[fd], '@');
      if(r != NULL)
      {
        strncpy(host_array[fd][9], r+1, 64);
        strncpy(host_array[fd][8], extract_array[fd],
          strlen(triplet_array[fd][2]) - strlen(host_array[fd][9]) - 1);
      }
    }

    /* message size */
    if(strncmp(policy_array[fd][i[fd]], "size=", 5) == 0)
    {
      extract(fd, policy_array[fd][i[fd]], 4);
      strncpy(triplet_array[fd][3], extract_array[fd], 64);
    }

    /* sasl_username */
    if(strncmp(policy_array[fd][i[fd]], "sasl_username=", 14) == 0)
    {
      extract(fd, policy_array[fd][i[fd]], 13);
      strncpy(triplet_array[fd][4], extract_array[fd], 64);
    }
    
    /* helo_name */
    if(strncmp(policy_array[fd][i[fd]], "helo_name=", 10) == 0)
    {
      extract(fd, policy_array[fd][i[fd]], 9);
      strncpy(triplet_array[fd][5], extract_array[fd], 60);
    }
    
    /* instance */
    if(strncmp(policy_array[fd][i[fd]], "instance=", 9) == 0)
    {
      extract(fd, policy_array[fd][i[fd]], 8);
      strncpy(triplet_array[fd][6], extract_array[fd], 60);
    }
  }
  
  /* DEBUG: easier when reporting problems */
  if(DEBUG > 0)
  {
    for(x[fd]=0;x[fd]<15;x[fd]++)
      if(host_array[fd][x[fd]][0] != 0x00)
        logmessage("DEBUG: fd: %d host_array[%d][%d]: %s\n", fd, fd, x[fd], host_array[fd][x[fd]]);

    for(x[fd]=0;x[fd]<15;x[fd]++)
      if(triplet_array[fd][x[fd]][0] != 0x00)
        logmessage("DEBUG: fd: %d triplet_array[%d][%d]: %s\n", fd, fd, x[fd], triplet_array[fd][x[fd]]);
  }
}




/*
 * function: module_info_check
 *  purpose: ensure we have all needed data for enabled modules
 *   return: 0 for success, -1 for failure
 */
int
module_info_check(unsigned int fd)
{
  
  /* critical information needed for greylisting */
  if(GREYLISTING==1)
  {
    /* ip address */
    if(host_array[fd][2][0] == 0x00) {
      logmessage("invalid host_array[%d][2]: (greylist host ip): %s\n", fd, host_array[fd][2]);
      goto err;
    }

    /* recipient address */
    if(triplet_array[fd][2][0] == 0x00) {
      logmessage("invalid triplet_array[%d][2]: (greylist recipient): %s\n", fd, triplet_array[fd][2]);
      goto err;
    }
  }


  /* critical information needed for sender throttling */
  if(SENDERTHROTTLE==1)
  {
    /* sender */
    if(triplet_array[fd][1][0] == 0x00) {
      logmessage("invalid triplet_array[%d][1]: (sender throttle from): %s\n", fd, triplet_array[fd][1]);
      goto err;
    }
    
    /* size */
    if(triplet_array[fd][3][0] == 0x00) {
      logmessage("invalid triplet_array[%d][3]: (sender throttle size): %s\n", fd, triplet_array[fd][3]);
      goto err;
    }
  }

  /* critical information needed for recipient throttling */
  if(RECIPIENTTHROTTLE==1)
  {
    /* recipient address */
    if(triplet_array[fd][2][0] == 0x00) {
      logmessage("invalid triplet_array[%d][2]: (recipient throttle): %s\n", fd, triplet_array[fd][2]);
      goto err;
    }
  }
  
  /* critical information needed for spamtrapping */
  if(SPAMTRAPPING==1)
  {     
    /* ip address */
    if(host_array[fd][2][0] == 0x00) {
      logmessage("invalid host_array[%d][2]: (spamtrap host ip): %s\n", fd, host_array[fd][2]);
      goto err;
    }
      
    /* recipient address */        
    if(triplet_array[fd][2][0] == 0x00) {
      logmessage("invalid triplet_array[%d][2]: (spamtrap recipient): %s\n", fd, triplet_array[fd][2]);
      goto err;
    }
  }  

  /* critical information needed for helo blacklisting */
  if(BLACKLIST_HELO==1)
  {     
    /* helo information */        
    if(triplet_array[fd][5][0] == 0x00) {
      logmessage("invalid triplet_array[%d][5]: (blacklist helo): %s\n", fd, triplet_array[fd][5]);
      goto err;
    }
  }  

  return (0);  /* success */

err:

  return (-1); /* failure */ 
}

/* EOF */
