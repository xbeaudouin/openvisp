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

/* INCLUDES */
#include <ctype.h>
#include <strings.h>
#include <sys/types.h>
#include <unistd.h>
#include <sys/time.h>
#include <sys/resource.h>
#include <netinet/in.h>
#include <sys/signal.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <sys/wait.h>
#include <sys/stat.h>
#include <syslog.h>
#include <signal.h>
#include <stdlib.h>
#include <string.h>
#include <stdarg.h>
#include <errno.h>
#include <netdb.h>
#include <fcntl.h>
#include <stdio.h>
#include <mysql.h>
#include <setjmp.h>

/* SIGPIPE quirks */
#ifndef MSG_NOSIGNAL
  /* Operating systems which have SO_NOSIGPIPE but not MSG_NOSIGNAL */
  #if defined (__FreeBSD__) || defined (__OpenBSD__) || defined(__APPLE__)
    #define MSG_NOSIGNAL SO_NOSIGPIPE
  /* Some versions of NetBSD dont have SO_NOSIGPIPE, check if we can use it or define as 0 */
  #elif defined(__NetBSD__)
    #ifdef SO_NOSIGPIPE
      #define MSG_NOSIGNAL SO_NOSIGPIPE
    #else
      #define MSG_NOSIGNAL 0
    #endif
  #else
    #error Your OS doesnt support MSG_NOSIGNAL or SO_NOSIGPIPE, please report to policyd-devel@lists.sf.net
  #endif
#endif

/* CONFIGS */
#define PROJECT         "policyd"
#define VERSION         "v1.82"

/* Miscellaneous constants */
#define LISTENQ         1023    /* 2nd argument to listen() */
#define MAXLINE         1023    /* max text line length */
#define BUFFSIZE        8191    /* buffer size for reads and writes */
#define BUFSIZE         4095 
#ifndef MAXFDS
  #define MAXFDS        1023    /* max file descriptors   */
#endif

#define POSTFIX_X_HEADER        "action=prepend X-Greylist: Passed"
#define POSTFIX_GOOD            "action=dunno\n\n"
#define POSTFIX_GREYLIST        "action=defer_if_permit Policy Rejection-"
#define POSTFIX_BAD_SIZE        "action=reject Policy Rejection-"
#define POSTFIX_SPAMTRAP        "action=reject Policy Rejection-"
#define POSTFIX_BLACKLIST_PERM  "action=reject Policy Rejection-"
#define POSTFIX_BLACKLIST_TEMP  "action=defer_if_permit Policy Rejection-"
#define POSTFIX_MODULE_FAILURE  "action=defer_if_permit Policy Rejection- Invalid data\n\n"
#define POSTFIX_QUOTA_EXCEEDED_PERM  "action=reject Policy Rejection-"
#define POSTFIX_QUOTA_EXCEEDED_TEMP  "action=defer_if_permit Policy Rejection-"


/* MySQL VARIABLES */
char *MYSQLHOST;
char *MYSQLUSER;
char *MYSQLPASS;
char *MYSQLDBASE;
char *MYSQLOPT;
 int MYSQLPORT;

 
/* GLOBAL OPTARGS */
 char *configpath;
 int DEBUG;
 int DAEMON;
 int FAILSAFE;
 int TRIPLET_TIME;
 int TRIPLET_AUTH_TIMEOUT;
 int TRIPLET_UNAUTH_TIMEOUT;
 int OPTINOUT;
 int OPTINOUTALL;
 int TRAINING_MODE;
 int TRAINING_POLICY_TIMEOUT;
 int AUTO_WHITE_LISTING;
 int AUTO_WHITELIST_NUMBER;
 int AUTO_BLACKLIST_NUMBER;
 int AUTO_WHITELIST_EXPIRE;
 int AUTO_BLACKLIST_EXPIRE;
 int AUTO_WHITELIST_NETBLOCK;
 int SPAMTRAP_AUTO_EXPIRE;
 int WHITELISTING;
 int WHITELISTNULL;
 int WHITELISTSENDER;
 int WHITELISTDNSNAME;
 int BLACKLIST_TEMP_REJECT;
 int BLACKLISTING;
 int BLACKLIST_TIMEOUT;
 int BLACKLIST_NETBLOCK;
 int BLACKLIST_HELO;
 int BLACKLIST_HELO_AUTO_EXPIRE;
 int BLACKLISTSENDER;
 int BLACKLISTDNSNAME;
 int AUTO_BLACK_LISTING;
 int GREYLISTING;
 int SPAMTRAPPING;
 int HELO_CHECK;
 int HELO_MAX_COUNT;
 int HELO_BLACKLIST_AUTO_EXPIRE;
 int HELO_AUTO_EXPIRE;
 int GREYLIST_X_HEADER;
 unsigned int GREYLIST_HOSTADDR;
 int BINDPORT;
 int QUOTA_EXCEEDED_TEMP_REJECT;
 int SENDERTHROTTLE;
 int SENDER_THROTTLE_SASL;
 int SENDER_THROTTLE_HOST;
 int SENDERMSGLIMIT;
 int SENDERRCPTLIMIT;
 int SENDERTIMELIMIT;
 int SENDERQUOTALIMIT;
 int SENDERMSGSIZE;
 int SENDER_INACTIVE_EXPIRE;
 int SENDER_THROTTLE_AUTOBLACKLIST;
 int SENDER_THROTTLE_AUTOBLACKLIST_NUMBER;
 int SENDER_THROTTLE_AUTOBLACKLIST_EXPIRE;

 int RECIPIENTTHROTTLE;
 int RECIPIENTMSGLIMIT;
 int RECIPIENTTIMELIMIT;
 int RECIPIENT_INACTIVE_EXPIRE;
 int SYSLOG_FACILITY;
 int DATABASE_KEEPALIVE;
 int count;
 uid_t UID;
 gid_t GID;
 char *BINDHOST;
 char *CONN_ACL;
 char *CHROOT;
 char *PIDFILE;
 char *postfix_greylist;
 char *postfix_bad_size;
 char *postfix_spamtrap;
 char *postfix_blacklist;
 char *postfix_sender_quota_exceeded;
 char *postfix_recipient_quota_exceeded;

 
/* GLOBAL VARIABLES/ARRAYS */
 MYSQL * volatile mysql;
unsigned long int rcpt_count;           /* total mails processed */
unsigned long int mysql_failure_count;  /* total mysql queries   */
unsigned long int last_mail_time;       /* seconds since epoch   */
unsigned long int last_mysql_failure;   /* seconds since epoch   */
unsigned long int mysql_timeout;        /* mysql query timeout   */
       sigjmp_buf sjmp;

  int msock;                            /* master server socket  */
  int ssock;                            /* slave server socket   */
  int         mysql_array[MAXFDS][10];
  int      mysql_optarray[MAXFDS][1];
  
  char    mysqlchar_array[MAXFDS][20][64];
  char         host_array[MAXFDS][20][64];
  char       policy_array[MAXFDS][20][64];
  char      triplet_array[MAXFDS][20][64];

  char   mysqlquery_array[MAXFDS][512];
  char    xgreylist_array[MAXFDS][128];
  char      extract_array[MAXFDS][64];
  char   extract_ip_array[MAXFDS][64];
  char  extract_host_addr[MAXFDS][64];
  char        return_code[MAXFDS][64];
  char extract_array_conf[MAXFDS][64];
  
  unsigned int i[MAXFDS], instance_inc[MAXFDS], t;
  unsigned int tcount[MAXFDS], tquota[MAXFDS], trcpt[MAXFDS];
  char      tattrib_array[MAXFDS][1];
  int x[MAXFDS], y[MAXFDS];
  struct timeval timevalue;     /* gettimeofday() */
  unsigned int timenow;
  FILE *fd_config, *pidfile;
  int  action_array[MAXFDS];
  char confbuf[256];
  char buf[MAXFDS][MAXLINE];
  unsigned int buf_size[MAXFDS];
  unsigned int buf_counter[MAXFDS];


/*  PROTOTYPES */

  // sockets
  int w_select(int nfds, fd_set *readfds, fd_set *writefds, fd_set *exceptfds, struct timeval *timeout);
  int w_socket(int family, int type, int protocol);
  int w_accept(unsigned int fd, struct sockaddr *sa, socklen_t *salenptr);
   void buf_write(unsigned int fd, const char *ptr, size_t nbytes);
  int socktimeout(unsigned int fd, unsigned int sec);
  int daemonize(int nochdir, int noclose);
  int w_tcp_conn_acl (const char *host);
  int    cidr_ip_match (unsigned long ip, char *range);
  pid_t w_fork(void);
const char *w_inet_ntop(int family, const void *addrptr, char *strptr, size_t len);
 ssize_t w_read(unsigned int fd, char *ptr, size_t max_size);
 ssize_t w_write(unsigned int fd, const void *vbuf);
 ssize_t f_write(unsigned int volatile fd, const void *vptr, size_t n);
 void w_close(unsigned int fd);
 void w_bind(unsigned int fd, const struct sockaddr *sa, socklen_t salen);
 void w_listen(unsigned int fd, unsigned int backlog);
 void sigalrm_handler (void);

  // functions
  void chk_pol(unsigned int fd);
  int bindsock(unsigned int port, unsigned int qlen);
  int greylist_check(unsigned int fd);
  int spamtrap_check(unsigned int fd);
  int throttle_check(unsigned int fd);
  int throttle_host(unsigned int fd);
  int throttle_from(unsigned int fd);
  int throttle_sasl(unsigned int fd);
  int throttle_rcpt(unsigned int fd);
  int helo_check(unsigned int fd);
  int module_info_check(unsigned int fd);
  int blacklist_helo_check(unsigned int fd);
  int gettime(void);
  int db_failure(unsigned int fd, char *module);
  int whitelist_check(unsigned int fd);
  int whitelist_sender_check(unsigned int fd);
  int whitelist_dnsname_check(unsigned int fd);
  int blacklist_sender_check(unsigned int fd);
  int blacklist_dnsname_check(unsigned int fd);
  void policy_reply(unsigned int fd, int code, int status);
  int blacklist_check(unsigned int fd);
  int extract_seconds(char *token);
  int parse_syslog_priority (char *str);
  int database_probe(unsigned int fd);
  int w_string_strip(void *str, char *token);
 void drop_privs(void);
 void read_conf(unsigned int prog);
 void logmessage(const char *fmt, ...);
 void usage(char *usag);
 void clear_var(unsigned int fd);
 void parse_buf(unsigned int fd, char *buf);
 void fold();
 void extract (unsigned int fd, char *token, unsigned int startlen);
 void extract_ip(unsigned int fd, char *token);
 void extract_ipfill(unsigned int fd, char *token);
 void extract_conf(unsigned int fd, char *token, unsigned int startlen);
 void syslog_token_set (char *token, int *value);
 char *strip_space (char *str);
  
 
  // mysql
 int db_doquery(unsigned int volatile fd);
 int db_optquery(unsigned int volatile fd);
 int db_charquery(unsigned int volatile fd);
 int db_printquery(unsigned int volatile fd);
 int db_deletequery(unsigned int volatile fd);
 int w_mysql_query(unsigned int volatile fd, const char *function);
MYSQL *db_connect(const char *dbname);



/* EOF */
