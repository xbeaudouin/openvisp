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

/*
 * function: w_select (function_select)
 *  purpose: wrapper for select()
 */             
int     
w_select(int nfds, fd_set *readfds, fd_set *writefds, fd_set *exceptfds, struct timeval *timeout)
{               
  int n;
  if ((n=select(nfds, readfds, writefds, exceptfds, timeout)) < 0)
  {
    if (errno == EINTR)
    {
      logmessage("warning: select(): system interupt\n");
      return 0;
    }
    if (errno == EBADF)
    {
      logmessage("warning: select(): descriptor was not open\n");
      return 0;
    }

    logmessage("fatal: select(): %s\n", strerror(errno));
    exit(-1);
  }

  return(n);
}




/* 
 * function: w_accept (wrapped_select)
 *  purpose: wrapped for accept()
 *   return: file descriptor
 */
int
w_accept(unsigned int fd, struct sockaddr *sa, socklen_t *salenptr)
{
  int n;
   
again:
  
  if ((n = accept(fd, sa, salenptr)) < 0)
  {
#ifdef  EPROTO
    if (errno == EPROTO || errno == ECONNABORTED)
    {
#else
    if (errno == ECONNABORTED)
    {
#endif
      if(DEBUG > 2)
        logmessage("DEBUG: fd: %d accept(): %s: retrying\n", fd, strerror(errno));
      
      goto again;
    } else {
      logmessage("fatal: accept(): %s (MAXFDS=%d ENV=%d)\n", strerror(errno), MAXFDS, getdtablesize());
      exit(-1);
    }
  }

  return(n);
}




/*
 * function: w_bind (wrapper_bind)
 *  purpose: wrapper for bind()
 */
void
w_bind(unsigned int fd, const struct sockaddr *sa, socklen_t salen)
{
  u_int yes=1;
  
  /* set options on socket (reuse port/address) */
  if(setsockopt(fd, SOL_SOCKET, SO_REUSEADDR, &yes, sizeof(yes)) < 0)
  {
    logmessage("fatal: setsockopt(): %s\n", strerror(errno));
    exit(-1);
  }
  
  /* bind a name to a socket */
  if (bind(fd, sa, salen) < 0) 
  {
    logmessage("fatal: bind(): %s\n", strerror(errno));
    exit(-1);
  }
}




/*
 * function: w_listen (wrapper_listen)
 *  purpose: wrapper for listen()
 */
void
w_listen(unsigned int fd, unsigned int backlog)
{
  /* listen for connection on a socket */
  if (listen(fd, backlog) < 0)
  {
    logmessage("fatal: listen(): %s\n", strerror(errno));
    exit(-1);
  }
}




/*
 * function: w_read (function_read)
 *  purpose: read number of bytes read
 *   return: number bytes read
 */ 
ssize_t
w_read(unsigned int fd, char *ptr, size_t max_size)
{       
  ssize_t  n;
  size_t   data_read = 0;                                    /* for debug only */

  /* receive data. disable signals are do not wait */
  while ((n = recv(fd, (void *) ptr + buf_counter[fd], 1, MSG_DONTWAIT | MSG_NOSIGNAL)) == 1)
  {
    data_read++;
    buf_counter[fd]++;
    buf_size[fd]++;


    /* check if we've reached the end of the buffer */
    if (buf_counter[fd] >= max_size)
    {
      if (DEBUG > 2)
        logmessage("DEBUG: fd: %d reached end of buffer, aborting\n", fd);

      return -3;
    }

    /* need at least 2 bytes to check against */
    if (buf_counter[fd] > 2)
    {
      /* received policy protocol terminator */
      if (strncmp(ptr + buf_counter[fd] - 2, "\n\n", 2) == 0)
      {
	if (DEBUG > 2)
          logmessage("DEBUG: fd %d: %s: returning -2 after reading %d bytes\n",fd, __FUNCTION__, data_read);
	
        return -2;
      }	
    }
  }

  /* check if recv returned an error */ 
  if (n == -1)
  {
    /* Ignore EAGAIN */
    if (errno == EAGAIN) 
    {
      if (DEBUG > 2)
        logmessage("DEBUG: fd: %d returning data_read = %d after EAGAIN\n", fd, data_read);
      
      return data_read;
    }
    
    /* here something bad happened */
    if (DEBUG > 2)
      logmessage("DEBUG: fd: %d connection problem during read: %s\n", fd, strerror(errno));

    return -1;
  }

  /* EOF was reached if n == 0 */
  if (n == 0)
  {
    if(DEBUG > 2)
      logmessage("DEBUG: fd: %d connection got an EOF, data_read = %d\n", fd, data_read);
     
    return -1;
  }

  if (DEBUG > 2)  
    logmessage("DEBUG: fd: %d returning data_read = %d\n", fd, data_read);
  
  /* return total amount of bytes read */
  return data_read; 
}   




/*
 * function: buf_write
 *  purpose: prepare buffer that needs to be written
 *   return: 
 */
void 
buf_write(unsigned int fd, const char *ptr, size_t nbytes)
{
  memcpy(buf[fd], (void *) ptr, nbytes);

  buf_counter[fd] = 0; 
  buf_size[fd] = nbytes;
}




/*
 * function: w_write (function_write)
 *  purpose: write buffer to a descriptor.
 *   return: bytes written
 */
ssize_t
w_write(unsigned int fd, const void *vbuf)
{
  ssize_t nbytes;

  /* send data. disable signals are do not wait */
  nbytes = send(fd, vbuf + buf_counter[fd], buf_size[fd] - buf_counter[fd], MSG_DONTWAIT | MSG_NOSIGNAL);

  /* check if send returned an error */ 
  if (nbytes == -1)
  {
    /* ignore EAGAIN */
    if (errno == EAGAIN) 
    {
      if(DEBUG > 2)
        logmessage("DEBUG: fd: %d write(): must try again after %d bytes (EAGAIN)\n", fd, nbytes);
      
      return nbytes;
    }
    
    /* something bad happened */
    if (DEBUG > 2)
      logmessage("DEBUG: fd: %d connection problem during write: %s\n", fd, strerror(errno));

    return -1;
  }

  /* EOF was reached if n == 0 */
  if (nbytes == 0)
  {
    if (DEBUG > 2)
      logmessage("DEBUG: fd: %d connection got an EOF during write after %d bytes\n", fd, nbytes);
     
    return -1;
  }

  /* increment written buffer counter */
  buf_counter[fd] += nbytes;

  if (DEBUG > 2)
    logmessage("DEBUG: fd: %d returning after %d bytes of data written\n", fd, nbytes);

  /* check if buffer has been written */
  if (buf_counter[fd] == buf_size[fd])
    return -2;
  
  return nbytes;
}




/*
 * function: w_close (function_close)
 *  purpose: close a file descriptor
 */
void
w_close(unsigned int fd)
{ 
  if(DEBUG > 2)
    logmessage("DEBUG: fd: %d shutting down fd %d\n", fd, fd);

  /* shut down connection */
  if (shutdown(fd, SHUT_RDWR) == -1)
    logmessage("shutdown(%d): %s\n", fd, strerror(errno));
  
  /* close a file descriptor */
  if (close(fd) == -1)
    logmessage("close(%d): %s\n", fd, strerror(errno));
}   



/*
 *  function: w_tcp_conn_acl
 *   purpose: check if connecting host is allowed       
 *    return: 0=allow, -1=disallow
 */             
int                     
w_tcp_conn_acl (const char *host)
{

  char            *p, range[strlen(CONN_ACL)];
  unsigned long   ip = inet_addr (host);          
    signed int    xi;
        
  strcpy(range, CONN_ACL);                  /* tmp buffer */
  for( xi=strlen(range) ; xi >= 0 ; xi--)             
  {
    p = range + xi;                         /* cycle backwards */
    if((range[xi] == ' ') || (range[xi] == ',') || (range == p))
    {                                       /* delimiter */
      if(range == p)
        p = range + xi;
      else
        p = range + xi + 1;
      if((p[strlen(p)-1] == ' ') || (p[strlen(p)-1] == ','))
        p[strlen(p)-1] = 0x00;              /* chomp trailing char */

      if(cidr_ip_match(ip, p) == 1) 
        return (1);                         /* pass */
      *p = 0x00;                            /* chomp old match */
    }
  }

  return (-1);                                    /* fail */
}



/*
 * function: w_socket (wrapper_socket)
 *  purpose: 
 *   return: file descriptor
 */
int     
w_socket(int family, int type, int protocol)
{
  int n;

  /* create an endpoint for communication */
  if ((n = socket(family, type, protocol)) < 0)
  {
    logmessage("fatal: socket(): %s\n", strerror(errno));
    exit(-1);
  }
  
  return (n);
}



/*
 * function: w_socket (wrapper_socket)
 *  purpose: 
 *   return: file descriptor
 */
const char *
w_inet_ntop(int family, const void *addrptr, char *strptr, size_t len)
{
  const char      *ptr;

  if (strptr == NULL)             /* check for old code */
  {
    logmessage("fatal: NULL 3rd argument to w_inet_ntop");
    exit(-1);
  }
  
  if ((ptr=inet_ntop(family, addrptr, strptr, len)) == NULL)
  {
    logmessage("fatal: inet_ntop(): %s\n", strerror(errno));
    exit(-1);
  }
  
  return(ptr);
}




/*
 * function: w_fork (wrapper_fork)
 *  purpose: background process
 *   return: pid
 */
pid_t
w_fork(void)
{
  pid_t pid;

  if ((pid = fork()) == -1)
  {
    logmessage("fatal: fork(): %s\n", strerror(errno));
    exit(-1);
  }

  return (pid);
}




/*
 *  function: daemon
 *   purpose: backgroup processes
 *    return: status
 */
int
daemonize(int nochdir, int noclose)
{
  unsigned int i;
  pid_t pid;

  if((pid=w_fork()) < 0)
    return (-1);
  else if(pid)
    _exit(0);                   /* parent terminates */

  /* child 1 continues */
  if(setsid() < 0)              /* become session leader */
    return (-1);

  if((pid=w_fork()) < 0)
    return (-1);
  else if(pid)
    _exit(0);                   /* child 1 terminates */

  /* child 2 continues */
  if(nochdir)
    chdir("/");                 /* change working directory */

  /* close off all file descriptors */
  if(noclose)
    for(i=0;i<64;i++)
      close(i);

  /* redirect stdin, stdout and stderr to /dev/null */
  open("/dev/null", O_RDONLY);
  open("/dev/null", O_RDWR);
  open("/dev/null", O_RDWR);

  return (0);
}

/* EOF */
