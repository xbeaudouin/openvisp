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

/*
 * function: read_conf
 *  purpose: read config options from file and store memory
 */
void
read_conf(unsigned int prog)
{
  
  memset(confbuf, 0x00, 256);
  
  /* file should exist */
  if((fd_config=freopen(configpath, "r", stdin)) == NULL)
  {
    fprintf(stderr, "fopen(): %s: %s\n", strerror(errno), configpath);
    exit(-1);
  }

  /* cycle through file, grab config options */
  while(fgets(confbuf, 256, fd_config) != NULL)
  {
    memset(extract_array[0], 0x00, 256);

    /* SYSLOG FACILITY */
    if(strncmp(confbuf, "SYSLOG_FACILITY=", 16) == 0)
    {
      extract(0, confbuf, 15);
      SYSLOG_FACILITY=parse_syslog_priority(extract_array[0]);
    }

    /* DAEMON MODE */
    if(strncmp(confbuf, "DAEMON=", 7) == 0)
    {
      extract(0, confbuf, 6);
      DAEMON=atol(extract_array[0]);
    }

    /* CHROOT */
    if(strncmp(confbuf, "CHROOT=", 7) == 0)
    {
      extract(0, confbuf, 6);
      if((CHROOT=malloc(strlen(extract_array[0])+1)) != NULL)
        strcpy(CHROOT, extract_array[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }

    /* UID */
    if(strncmp(confbuf, "UID=", 4) == 0)
    { 
      extract(0, confbuf, 3);
      UID=atol(extract_array[0]);
    }

    /* GID */
    if(strncmp(confbuf, "GID=", 4) == 0)
    { 
      extract(0, confbuf, 3);
      GID=atol(extract_array[0]);
    }

    /* DEBUG MODE */
    if(strncmp(confbuf, "DEBUG=", 6) == 0)
    { 
      extract(0, confbuf, 5);
      DEBUG=atol(extract_array[0]);
    }
      
    /* PORT TO BIND TO */
    if(strncmp(confbuf, "BINDPORT=", 9) == 0)
    { 
      extract(0, confbuf, 8);
      BINDPORT=atol(extract_array[0]);
    }
    
    /* HOST TO BIND TO */
    if(strncmp(confbuf, "BINDHOST=", 9) == 0)
    { 
      extract(0, confbuf, 8);
      if((BINDHOST=malloc(strlen(extract_array[0])+1)) != NULL)
        strcpy(BINDHOST,extract_array[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }
    
    /* CONNECTION ACL */
    if(strncmp(confbuf, "CONN_ACL=", 9) == 0)
    { 
      extract(0, confbuf, 8);
      if((CONN_ACL=malloc(strlen(extract_array[0])+1)) != NULL)
        strcpy(CONN_ACL,extract_array[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }
    
    /* PID TO WRITE TO */
    if(strncmp(confbuf, "PIDFILE=", 8) == 0)
    { 
      extract(0, confbuf, 7);
      if((PIDFILE=malloc(strlen(extract_array[0])+1)) != NULL)
        strcpy(PIDFILE,extract_array[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }
    
    /* GREYLISTING */
    if(strncmp(confbuf, "GREYLISTING=", 12) == 0)
    {
      extract(0, confbuf, 11);
      GREYLISTING=atol(extract_array[0]);
    }

    /* GREYLIST_HOSTADDR */
    if(strncmp(confbuf, "GREYLIST_HOSTADDR=", 18) == 0)
    {
      extract(0, confbuf, 17);
      GREYLIST_HOSTADDR=atol(extract_array[0]);
    }

    /* TRAINING_MODE */
    if(strncmp(confbuf, "TRAINING_MODE=", 14) == 0)
    { 
      extract(0, confbuf, 13);
      TRAINING_MODE=atol(extract_array[0]);
    }

    /* TRAINING_POLICY_TIMEOUT */
    if(strncmp(confbuf, "TRAINING_POLICY_TIMEOUT=", 24) == 0)
    {
      extract(0, confbuf, 23);
      TRAINING_POLICY_TIMEOUT=extract_seconds(extract_array[0]);
    }

    /* GREYLIST_X_HEADER */
    if(strncmp(confbuf, "GREYLIST_X_HEADER=", 18) == 0)
    { 
      extract(0, confbuf, 17);
      GREYLIST_X_HEADER=atol(extract_array[0]);
    }
    
    /* WHITELISTING */
    if(strncmp(confbuf, "WHITELISTING=", 13) == 0)
    { 
      extract(0, confbuf, 12);
      WHITELISTING=atol(extract_array[0]);
    }
    
    /* AUTO_WHITELIST_EXPIRE */
    if(strncmp(confbuf, "AUTO_WHITELIST_EXPIRE=", 22) == 0)
    { 
      extract(0, confbuf, 21);
      AUTO_WHITELIST_EXPIRE=extract_seconds(extract_array[0]);
    }
    
    /* AUTO_BLACKLIST_EXPIRE */
    if(strncmp(confbuf, "AUTO_BLACKLIST_EXPIRE=", 22) == 0)
    { 
      extract(0, confbuf, 21);
      AUTO_BLACKLIST_EXPIRE=extract_seconds(extract_array[0]);
    }
    
    /* AUTO_WHITELIST_NETBLOCK */
    if(strncmp(confbuf, "AUTO_WHITELIST_NETBLOCK=", 24) == 0)
    { 
      extract(0, confbuf, 23);
      AUTO_WHITELIST_NETBLOCK=atol(extract_array[0]);
    }
    
    /* AUTO_WHITELIST_NUMBER */
    if(strncmp(confbuf, "AUTO_WHITELIST_NUMBER=", 22) == 0)
    { 
      extract(0, confbuf, 21);
      AUTO_WHITELIST_NUMBER=atol(extract_array[0]);
    }
   
    /* AUTO_BLACKLIST_NUMBER */
    if(strncmp(confbuf, "AUTO_BLACKLIST_NUMBER=", 22) == 0)
    { 
      extract(0, confbuf, 21);
      AUTO_BLACKLIST_NUMBER=atol(extract_array[0]);
    }
   
    /* AUTO_WHITE_LISTING */
    if(strncmp(confbuf, "AUTO_WHITE_LISTING=", 19) == 0)
    { 
      extract(0, confbuf, 18);
      AUTO_WHITE_LISTING=atol(extract_array[0]);
    }
    
    /* SPAMTRAP_AUTO_EXPIRE */
    if(strncmp(confbuf, "SPAMTRAP_AUTO_EXPIRE=", 21) == 0)
    { 
      extract(0, confbuf, 20);
      SPAMTRAP_AUTO_EXPIRE=extract_seconds(extract_array[0]);
    }
    
    /* HELO CHECK */
    if(strncmp(confbuf, "HELO_CHECK=", 11) == 0)
    { 
      extract(0, confbuf, 10);
      HELO_CHECK=atol(extract_array[0]);
    }
    
    /* HELO_MAX_COUNT */
    if(strncmp(confbuf, "HELO_MAX_COUNT=", 15) == 0)
    { 
      extract(0, confbuf, 14);
      HELO_MAX_COUNT=atol(extract_array[0]);
    }
    
    /* HELO_BLACKLIST_AUTO_EXPIRE */
    if(strncmp(confbuf, "HELO_BLACKLIST_AUTO_EXPIRE=", 27) == 0)
    { 
      extract(0, confbuf, 26);
      HELO_BLACKLIST_AUTO_EXPIRE=extract_seconds(extract_array[0]);
    }
    
    /* HELO_AUTO_EXPIRE */
    if(strncmp(confbuf, "HELO_AUTO_EXPIRE=", 17) == 0)
    { 
      extract(0, confbuf, 16);
      HELO_AUTO_EXPIRE=extract_seconds(extract_array[0]);
    }
    
    /* BLACKLISTING */
    if(strncmp(confbuf, "BLACKLISTING=", 13) == 0)
    { 
      extract(0, confbuf, 12);
      BLACKLISTING=atol(extract_array[0]);
    }

    /* RCPT_ACL */
    if(strncmp(confbuf, "RCPT_ACL=", 9) == 0)
    { 
      extract(0, confbuf, 8);
      RCPT_ACL=atol(extract_array[0]);
    }

    /* AUTO_BLACK_LISTING */
    if(strncmp(confbuf, "AUTO_BLACK_LISTING=", 19) == 0)
    { 
      extract(0, confbuf, 18);
      AUTO_BLACK_LISTING=atol(extract_array[0]);
    }
    
    /* BLACKLIST_HELO */
    if(strncmp(confbuf, "BLACKLIST_HELO=", 15) == 0)
    { 
      extract(0, confbuf, 14);
      BLACKLIST_HELO=atol(extract_array[0]);
    }

    /* BLACKLIST_HELO_AUTO_EXPIRE */
    if(strncmp(confbuf, "BLACKLIST_HELO_AUTO_EXPIRE=", 27) == 0)
    { 
      extract(0, confbuf, 26);
      BLACKLIST_HELO_AUTO_EXPIRE=extract_seconds(extract_array[0]);
    }

    /* SPAMTRAPPING */
    if(strncmp(confbuf, "SPAMTRAPPING=", 13) == 0)
    { 
      extract(0, confbuf, 12);
      SPAMTRAPPING=atol(extract_array[0]);
    }

    /* BLACKLIST_TEMP_REJECT */
    if(strncmp(confbuf, "BLACKLIST_TEMP_REJECT=", 22) == 0)
    { 
      extract(0, confbuf, 21);
      BLACKLIST_TEMP_REJECT=atol(extract_array[0]);
    }
    
    /* BLACKLIST_TIMEOUT */
    if(strncmp(confbuf, "BLACKLIST_TIMEOUT=", 18) == 0)
    { 
      extract(0, confbuf, 17);
      BLACKLIST_TIMEOUT=extract_seconds(extract_array[0]);
    }
    
    /* BLACKLIST_NETBLOCK */
    if(strncmp(confbuf, "BLACKLIST_NETBLOCK=", 19) == 0)
    { 
      extract(0, confbuf, 18);
      BLACKLIST_NETBLOCK=atol(extract_array[0]);
    }
    
    /* BLACKLIST SENDER */
    if(strncmp(confbuf, "BLACKLISTSENDER=", 16) == 0)
    { 
      extract(0, confbuf, 15);
      BLACKLISTSENDER=atol(extract_array[0]);
    }

    /* BLACKLIST DNS NAME */
    if(strncmp(confbuf, "BLACKLISTDNSNAME=", 17) == 0)
    {
      extract(0, confbuf, 16);
      BLACKLISTDNSNAME=atol(extract_array[0]);
    }
     
    /* WHITELIST NULL SENDER */
    if(strncmp(confbuf, "WHITELISTNULL=", 14) == 0)
    { 
      extract(0, confbuf, 13);
      WHITELISTNULL=atol(extract_array[0]);
    }

    /* WHITELIST SENDER */
    if(strncmp(confbuf, "WHITELISTSENDER=", 16) == 0)
    { 
      extract(0, confbuf, 15);
      WHITELISTSENDER=atol(extract_array[0]);
    }

    /* WHITELIST DNS NAME */
    if(strncmp(confbuf, "WHITELISTDNSNAME=", 17) == 0)
    { 
      extract(0, confbuf, 16);
      WHITELISTDNSNAME=atol(extract_array[0]);
    }

    /* SENDERTHROTTLE */
    if(strncmp(confbuf, "SENDERTHROTTLE=", 15) == 0)
    { 
      extract(0, confbuf, 14);
      SENDERTHROTTLE=atol(extract_array[0]);
    }
    
    /* SENDER_THROTTLE_SASL */
    if(strncmp(confbuf, "SENDER_THROTTLE_SASL=", 21) == 0)
    { 
      extract(0, confbuf, 20);
      SENDER_THROTTLE_SASL=atol(extract_array[0]);
    }
    
    /* SENDER_THROTTLE_HOST */
    if(strncmp(confbuf, "SENDER_THROTTLE_HOST=", 21) == 0)
    { 
      extract(0, confbuf, 20);
      SENDER_THROTTLE_HOST=atol(extract_array[0]);
    }
    
    /* SENDER_THROTTLE_ENVELOPE */
    if(strncmp(confbuf, "SENDER_THROTTLE_ENVELOPE=", 25) == 0)
    { 
      extract(0, confbuf, 24);
      SENDER_THROTTLE_ENVELOPE=atol(extract_array[0]);
    }
    
    /* MAXIMUM SENDER MESSAGE LIMIT */
    if(strncmp(confbuf, "SENDERMSGLIMIT=", 15) == 0)
    { 
      extract(0, confbuf, 14);
      SENDERMSGLIMIT=atol(extract_array[0]);
    }
    
    /* MAXIMUM SENDER RCPT LIMIT */
    if(strncmp(confbuf, "SENDERRCPTLIMIT=", 16) == 0)
    { 
      extract(0, confbuf, 15);
      SENDERRCPTLIMIT=atol(extract_array[0]);
    }
    
    /* MAX SENDER VOLUME/QUOTA SENDER TIME LIMIT */
    if(strncmp(confbuf, "SENDERQUOTALIMIT=", 17) == 0)
    { 
      extract(0, confbuf, 16);
      SENDERQUOTALIMIT=atol(extract_array[0]);
    }
    
    /* MAX MAIL SIZE PER SENDER */
    if(strncmp(confbuf, "SENDERMSGSIZE=", 14) == 0)
    { 
      extract(0, confbuf, 13);
      SENDERMSGSIZE=atol(extract_array[0]);
    }
    
    /* MAX SENDER TIME LIMIT */
    if(strncmp(confbuf, "SENDERTIMELIMIT=", 16) == 0)
    { 
      extract(0, confbuf, 15);
      SENDERTIMELIMIT=extract_seconds(extract_array[0]);
    }

    /* INACTIVE SENDER EXPIRATION TIME LIMIT */
    if(strncmp(confbuf, "SENDER_INACTIVE_EXPIRE=", 23) == 0)
    {
      extract(0, confbuf, 22);
      SENDER_INACTIVE_EXPIRE=extract_seconds(extract_array[0]);
    }

    /* SENDER_THROTTLE_AUTOBLACKLIST */
    if(strncmp(confbuf, "SENDER_THROTTLE_AUTOBLACKLIST=", 30) == 0)
    { 
      extract(0, confbuf, 29);
      SENDER_THROTTLE_AUTOBLACKLIST=atol(extract_array[0]);
    }
    
    /* SENDER_THROTTLE_AUTOBLACKLIST_NUMBER */
    if(strncmp(confbuf, "SENDER_THROTTLE_AUTOBLACKLIST_NUMBER=", 37) == 0)
    { 
      extract(0, confbuf, 36);
      SENDER_THROTTLE_AUTOBLACKLIST_NUMBER=atol(extract_array[0]);
    }

    /* SENDER_THROTTLE_AUTOBLACKLIST_EXPIRE */
    if(strncmp(confbuf, "SENDER_THROTTLE_AUTOBLACKLIST_EXPIRE=", 37) == 0)
    { 
      extract(0, confbuf, 36);
      SENDER_THROTTLE_AUTOBLACKLIST_EXPIRE=extract_seconds(extract_array[0]);
    }

    
    /* RECIPIENTTHROTTLE */
    if(strncmp(confbuf, "RECIPIENTTHROTTLE=", 18) == 0)
    { 
      extract(0, confbuf, 17);
      RECIPIENTTHROTTLE=atol(extract_array[0]);
    }
    
    /* MAX RECIPIENT MESSAGES LIMIT */
    if(strncmp(confbuf, "RECIPIENTMSGLIMIT=", 18) == 0)
    { 
      extract(0, confbuf, 17);
      RECIPIENTMSGLIMIT=atol(extract_array[0]);
    }

    /* MAX RECIPIENT TIME LIMIT */
    if(strncmp(confbuf, "RECIPIENTTIMELIMIT=", 19) == 0)
    { 
      extract(0, confbuf, 18);
      RECIPIENTTIMELIMIT=extract_seconds(extract_array[0]);
    }
    
    /* INACTIVE RECIPIENT EXPIRATION TIME LIMIT */
    if(strncmp(confbuf, "RECIPIENT_INACTIVE_EXPIRE=", 26) == 0)
    {
      extract(0, confbuf, 25);
      RECIPIENT_INACTIVE_EXPIRE=extract_seconds(extract_array[0]);
    }
    
    /* TRIPLET TIMEOUT */
    if(strncmp(confbuf, "TRIPLET_TIME=", 13) == 0)
    { 
      extract(0, confbuf, 12);
      TRIPLET_TIME=extract_seconds(extract_array[0]);
    }
    
    /* TRIPLET_AUTH_TIMEOUT */
    if(strncmp(confbuf, "TRIPLET_AUTH_TIMEOUT=", 21) == 0)
    { 
      extract(0, confbuf, 20);
      TRIPLET_AUTH_TIMEOUT=extract_seconds(extract_array[0]);
    }
    
    /* TRIPLET_UNAUTH_TIMEOUT */
    if(strncmp(confbuf, "TRIPLET_UNAUTH_TIMEOUT=", 23) == 0)
    { 
      extract(0, confbuf, 22);
      TRIPLET_UNAUTH_TIMEOUT=extract_seconds(extract_array[0]);
    }
    
    /* OPT-IN / OPT-OUT */
    if(strncmp(confbuf, "OPTINOUT=", 9) == 0)
    { 
      extract(0, confbuf, 8);
      OPTINOUT=atol(extract_array[0]);
    }
    
    /* OPT-IN / OPT-OUT */
    if(strncmp(confbuf, "OPTINOUTALL=", 12) == 0)
    { 
      extract(0, confbuf, 11);
      OPTINOUTALL=atol(extract_array[0]);
    }
    
    /* FAILOVER MODE */
    if(strncmp(confbuf, "FAILSAFE=", 9) == 0)
    { 
      extract(0, confbuf, 8);
      FAILSAFE=atol(extract_array[0]);
    }
    
    /* DATABASE_KEEPALIVE */
    if(strncmp(confbuf, "DATABASE_KEEPALIVE=", 19) == 0)
    { 
      extract(0, confbuf, 18);
      DATABASE_KEEPALIVE=atol(extract_array[0]);
    }

    /* MYSQL HOST */
    if(strncmp(confbuf, "MYSQLHOST=", 10) == 0)
    { 
      extract_conf(0, confbuf, 9);
      if((MYSQLHOST=malloc(strlen(extract_array_conf[0])+1)) != NULL)
        strcpy(MYSQLHOST, extract_array_conf[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }

    /* MYSQL DATABASE */
    if(strncmp(confbuf, "MYSQLDBASE=", 11) == 0)
    { 
      extract_conf(0, confbuf, 10);
      if((MYSQLDBASE=malloc(strlen(extract_array_conf[0])+1)) != NULL)
        strcpy(MYSQLDBASE, extract_array_conf[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }

    /* MYSQL USER */
    if(strncmp(confbuf, "MYSQLUSER=", 10) == 0)
    { 
      extract_conf(0, confbuf, 9);
      if((MYSQLUSER=malloc(strlen(extract_array_conf[0])+1)) != NULL)
        strcpy(MYSQLUSER, extract_array_conf[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }

    /* MYSQL PASS */
    if(strncmp(confbuf, "MYSQLPASS=", 10) == 0)
    { 
      extract_conf(0, confbuf, 9);
      if((MYSQLPASS=malloc(strlen(extract_array_conf[0])+1)) != NULL)
        strcpy(MYSQLPASS, extract_array_conf[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }
    
    /* MYSQL OPTIONS */
    if(strncmp(confbuf, "MYSQLOPT=", 9) == 0)
    { 
      extract_conf(0, confbuf, 8);
      if((MYSQLOPT=malloc(strlen(extract_array_conf[0])+1)) != NULL)
        strcpy(MYSQLOPT, extract_array_conf[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }

    /* MYSQL PORT */
    if(strncmp(confbuf, "MYSQLPORT=", 10) == 0)
    { 
      extract(0, confbuf, 9);
      MYSQLPORT=atol(extract_array[0]);
    }

    /* GREYLIST_REJECTION */
    if(strncmp(confbuf, "GREYLIST_REJECTION=", 19) == 0)
    { 
      extract_conf(0, confbuf, 18);
      if((postfix_greylist=malloc(512)) != NULL)
        snprintf(postfix_greylist, 512, "%s %s\n\n", POSTFIX_GREYLIST, extract_array_conf[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }
    
    /* BLACKLIST_REJECTION */
    if(strncmp(confbuf, "BLACKLIST_REJECTION=", 20) == 0)
    { 
      extract_conf(0, confbuf, 19);
      if((postfix_blacklist=malloc(512)) != NULL)
      {
        if(BLACKLIST_TEMP_REJECT==1)
          snprintf(postfix_blacklist, 512, "%s %s\n\n", POSTFIX_BLACKLIST_TEMP, extract_array_conf[0]);
        else
          snprintf(postfix_blacklist, 512, "%s %s\n\n", POSTFIX_BLACKLIST_PERM, extract_array_conf[0]);
      }
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }
    
    /* THROTTLE BAD SIZE */
    if(strncmp(confbuf, "SENDER_SIZE_REJECTION=", 22) == 0)
    { 
      extract_conf(0, confbuf, 21);
      if((postfix_bad_size=malloc(512)) != NULL)
        snprintf(postfix_bad_size, 512, "%s %s\n\n", POSTFIX_BAD_SIZE, extract_array_conf[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }
    
    /* SPAMTRAP */
    if(strncmp(confbuf, "SPAMTRAP_REJECTION=", 19) == 0)
    { 
      extract_conf(0, confbuf, 18);
      if((postfix_spamtrap=malloc(512)) != NULL)
        snprintf(postfix_spamtrap, 512, "%s %s\n\n", POSTFIX_SPAMTRAP, extract_array_conf[0]);
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }
    
    /* MAX SENDER QUOTA EXCEEDED */
    if(strncmp(confbuf, "SENDER_QUOTA_REJECTION=", 23) == 0)
    { 
      extract_conf(0, confbuf, 22);
      if((postfix_sender_quota_exceeded=malloc(512)) != NULL)
      {
        if(QUOTA_EXCEEDED_TEMP_REJECT==1)
          snprintf(postfix_sender_quota_exceeded, 512, "%s %s\n\n", POSTFIX_QUOTA_EXCEEDED_TEMP, extract_array_conf[0]);
        else
          snprintf(postfix_sender_quota_exceeded, 512, "%s %s\n\n", POSTFIX_QUOTA_EXCEEDED_PERM, extract_array_conf[0]);
      }
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }
    
    /* MAX RECIPIENT QUOTA EXCEEDED */
    if(strncmp(confbuf, "RECIPIENT_QUOTA_REJECTION=", 26) == 0)    { 
      extract_conf(0, confbuf, 25);
      if((postfix_recipient_quota_exceeded=malloc(512)) != NULL)
      {
        if(QUOTA_EXCEEDED_TEMP_REJECT==1)
          snprintf(postfix_recipient_quota_exceeded, 512, "%s %s\n\n", POSTFIX_QUOTA_EXCEEDED_TEMP, extract_array_conf[0]);
        else
          snprintf(postfix_recipient_quota_exceeded, 512, "%s %s\n\n", POSTFIX_QUOTA_EXCEEDED_PERM, extract_array_conf[0]);
      }
      else {
        logmessage("malloc(): %s\n", strerror(errno)); 
        exit(-1);
      }
    }
    
    /* QUOTA_EXCEEDED_TEMP_REJECT */
    if(strncmp(confbuf, "QUOTA_EXCEEDED_TEMP_REJECT=", 27) == 0)
    { 
      extract(0, confbuf, 26);
      QUOTA_EXCEEDED_TEMP_REJECT=atol(extract_array[0]);
    }

    memset(confbuf, 0x00, 256);
  }

  /* backward compatible with old configs */
  if(!SYSLOG_FACILITY)
    SYSLOG_FACILITY=LOG_MAIL|LOG_INFO;

  /* close config file when we're done */
  if(fclose(fd_config) != 0)
  {
    logmessage("fclose(): %s: %s\n", configpath, strerror(errno));
    exit(-1);
  }
  
  /* background policyd */
  if(DAEMON)
  {

    /* dont let cleanup run in the background */
    if(prog == 0)
    {
      if(daemonize(0,0) == -1)
      {
        fprintf(stderr, "daemon(): %s\n", strerror(errno));
        exit(-1);
      }
    }
  }
  
  /* dump all debugging info */
  if(DEBUG > 0)
  {
    logmessage(" ---- DAEMON CONFIG ----\n"); 
    logmessage("config: version> %s\n", PACKAGE_VERSION);
    logmessage("config: debug> %d\n", DEBUG);
    logmessage("config: daemon mode> %d\n", DAEMON);
    logmessage("config: bindhost> %s\n", BINDHOST);
    logmessage("config: bindport> %d\n", BINDPORT);
    logmessage("config: pidfile> %s\n", PIDFILE);
    logmessage("config: syslog> %d\n", SYSLOG_FACILITY);
    logmessage("config: chroot> %s\n", CHROOT);
    logmessage("config: uid> %d\n", UID);
    logmessage("config: gid> %d\n", GID);
    logmessage("config: conn acl> %s\n", CONN_ACL);
    logmessage("\n");
    
    logmessage(" ---- DATABASE CONFIG ----\n");
    logmessage("config: host> %s\n", MYSQLHOST);
    logmessage("config: user> %s\n", MYSQLUSER);
    logmessage("config: pass> %s\n", MYSQLPASS);
    logmessage("config: database> %s\n", MYSQLDBASE);
    logmessage("config: options> %s\n", MYSQLOPT);
    logmessage("config: failsafe> %d\n", FAILSAFE);
    logmessage("config: keep alive> %d\n", DATABASE_KEEPALIVE);
    logmessage("config: version> %d\n", MYSQL_VERSION_ID);
    logmessage("\n");

    logmessage(" ---- WHITELISTING ----\n"); 
    logmessage("config: whitelisting> %d\n", WHITELISTING);
    logmessage("config: whitelistnullsender> %d\n", WHITELISTNULL);
    logmessage("config: whitelistsender> %d\n", WHITELISTSENDER);
    logmessage("config: whitelistdnsname> %d\n", WHITELISTDNSNAME);
    logmessage("config: autowhitelisting> %d\n", AUTO_WHITE_LISTING);
    logmessage("config: autowhitelist_number> %d\n", AUTO_WHITELIST_NUMBER);
    logmessage("config: autowhitelist_netblock> %d\n", AUTO_WHITELIST_NETBLOCK);
    logmessage("config: autowhitelist_expire> %d\n", AUTO_WHITELIST_EXPIRE);
    logmessage("\n");

    logmessage(" ---- BLACKLISTING ----\n"); 
    logmessage("config: blacklisting> %d\n", BLACKLISTING);
    logmessage("config: blacklisting_temp_reject> %d\n", BLACKLIST_TEMP_REJECT);
    logmessage("config: blacklisting_netblock> %d\n", BLACKLIST_NETBLOCK);
    logmessage("config: postfix_blacklist> %s\n", postfix_blacklist);
    logmessage("config: autoblacklisting> %d\n", AUTO_BLACK_LISTING);
    logmessage("config: autoblacklist_number> %d\n", AUTO_BLACKLIST_NUMBER);
    logmessage("config: autoblacklist_expire> %d\n", AUTO_BLACKLIST_EXPIRE);
    logmessage("config: blacklist_rejection> %s\n", postfix_blacklist);
    logmessage("\n");
    
    logmessage(" ---- HELO (HRP) ----\n");
    logmessage("config: helo> %d\n", HELO_CHECK);
    logmessage("config: helo_max_count> %d\n", HELO_MAX_COUNT);
    logmessage("config: helo_blacklist_auto_expire> %d\n", HELO_BLACKLIST_AUTO_EXPIRE);
    logmessage("config: helo_auto_expire> %d\n", HELO_AUTO_EXPIRE);
    logmessage("\n");

    logmessage(" ---- SPAMTRAP CONFIG ----\n"); 
    logmessage("config: spamtrap> %d\n", SPAMTRAPPING);
    logmessage("config: postfix_spamtrap> %s\n", postfix_spamtrap);
    logmessage("config: spamtrapauto_expire> %d\n", SPAMTRAP_AUTO_EXPIRE);
    logmessage("\n");

    logmessage(" ---- GREYLISTING CONFIG ----\n"); 
    logmessage("config: greylisting> %d\n", GREYLISTING);
    logmessage("config: greylist_hostaddr> %d\n", GREYLIST_HOSTADDR);
    logmessage("config: postfix_greylist> %s\n", postfix_greylist);
    logmessage("config: greylist_x_header> %d\n", GREYLIST_X_HEADER);
    logmessage("config: trainingmode> %d\n", TRAINING_MODE);
    logmessage("config: training_policyd_timeout> %d\n", TRAINING_POLICY_TIMEOUT);
    logmessage("config: triplet timeout> %d\n", TRIPLET_TIME);
    logmessage("config: optin/optout> %d\n", OPTINOUT);
    logmessage("config: optin all in> %d\n", OPTINOUTALL);
    logmessage("config: triplet auth timeout> %d\n", TRIPLET_AUTH_TIMEOUT);
    logmessage("config: triplet unauth timeout> %d\n", TRIPLET_UNAUTH_TIMEOUT);
    logmessage("\n");

    logmessage(" ---- SENDER THROTTLE CONFIG ----\n"); 
    logmessage("config: sender throttle> %d\n", SENDERTHROTTLE);
    logmessage("config: sender throttle sasl> %d\n", SENDER_THROTTLE_SASL);
    logmessage("config: sender throttle host> %d\n", SENDER_THROTTLE_HOST);
    logmessage("config: postfix_sender_quota_exceeded> %s\n", postfix_sender_quota_exceeded);
    logmessage("config: quota_exceeded_temp_reject> %d\n", QUOTA_EXCEEDED_TEMP_REJECT);
    logmessage("config: postfix_bad_size> %s\n", postfix_bad_size);
    logmessage("config: sender msglimit> %d\n", SENDERMSGLIMIT);
    logmessage("config: sender quotalimit> %d\n", SENDERQUOTALIMIT);
    logmessage("config: sender timelimit> %d\n", SENDERTIMELIMIT);
    logmessage("config: sender msgsize> %d\n", SENDERMSGSIZE);
    logmessage("config: sender expire inactive> %d\n", SENDER_INACTIVE_EXPIRE);
    logmessage("config: sender throttle autoblacklisting> %d\n", SENDER_THROTTLE_AUTOBLACKLIST);
    logmessage("config: sender throttle autoblacklist number> %d\n", SENDER_THROTTLE_AUTOBLACKLIST_NUMBER);
    logmessage("config: sender throttle autoblacklist expire> %d\n", SENDER_THROTTLE_AUTOBLACKLIST_EXPIRE);
    logmessage("\n");

    logmessage(" ---- RECIPIENT THROTTLE CONFIG ----\n");
    logmessage("config: recipient throttle> %d\n", RECIPIENTTHROTTLE);
    logmessage("config: recipient msglimit> %d\n", RECIPIENTMSGLIMIT);
    logmessage("config: recipient timelimit> %d\n", RECIPIENTTIMELIMIT);
    logmessage("config: recipient expire inactive> %d\n", RECIPIENT_INACTIVE_EXPIRE);
    logmessage("config: postfix_recipient_quota_exceeded> %s\n", postfix_recipient_quota_exceeded);
    logmessage("config: quota_exceeded_temp_reject> %d\n", QUOTA_EXCEEDED_TEMP_REJECT);
    logmessage("\n");
  }

  if((SENDER_THROTTLE_HOST) && (SENDER_THROTTLE_SASL))
  {
    logmessage("FATAL: you may NOT have SENDER_THROTTLE_HOST and SENDER_THROTTLE_SASL enabled\n");
    exit (-1);
  }

  /* quick acl check */
  if((!CONN_ACL) || (!strlen(CONN_ACL)))
  {
    logmessage("FATAL: you did not upgrade correctly or have broken something. "
               "Please read the Changelog.txt. You're missing the CONN_ACL setting\n");
    exit (-1);
  }
}



/*
 * function: w_string_strip
 *  purpose:
 *   return:
 */             
int
w_string_strip(void *str, char *token)
{
  char	*ptr;
  ptr	 = strtok(str, token);

  if(ptr != NULL)
  {
    return (1); /* found */
  } else {
    return (0); /* not found */
  }
}



/* 
 * function: logmessage
 *  purpose: log messages to syslog or stdout/stderr
 *   return: nada
 */
void
logmessage(const char *fmt, ...)
{
  va_list ap;
  va_start(ap, fmt);
  
  if(DAEMON == 0)
  {
    vfprintf(stdout, fmt, ap);
    fflush(stdout);
  }
  else
    vsyslog(SYSLOG_FACILITY, fmt, ap);
  
  va_end(ap);
}




/*
 * function: usage
 *  purpose: print out usage information
 *   return: nada
 */
void
usage(char *usag)
{ 
  logmessage("policyd %s\n", PACKAGE_VERSION);
  logmessage("usage: %s -c /path/to/policyd.conf\n", usag);
  exit(-1);
}




/*
 * function: extract_seconds
 *  purpose: convert token to seconds
 *   return: seconds
 */
int
extract_seconds(char *token)
{
           char tmp[32];
  unsigned  int multiplier=0;

  /* allow values of 0 */
  if((isdigit(token[0]) != 0) && (atol(token) == 0))
    return 0;
  
  memset(tmp, 0x00, 32);
  switch(token[strlen(token) - 1])
  {

    case 's':
      multiplier = 1;
      break;

    case 'm':
      multiplier = 60;
      break;
  
    case 'h':
      multiplier = 60 * 60;
      break;
    
    case 'd':
      multiplier = 60 * 60 * 24;
      break;
      
    case 'w':
      multiplier = 60 * 60 * 24 * 7;
      break;
    
    case 'M':
      multiplier = 60 * 60 * 24 * 31 ;
      break;
    
    case 'Y':
      multiplier = 60 * 60 * 24 * 31 * 12;
      break;
    
    default:
      logmessage("fatal: invalid time unit: %s\n", token);
      exit(-1);
  }
  
  strncpy(tmp, token, sizeof(tmp) - 2);
  return (atol(tmp) * multiplier);
}




/*
 * function: extract
 *  purpose: extract policy variable
 *   return: policy variable (into an array)
 */
void
extract(unsigned int fd, char *token, unsigned int startlen)
{
  unsigned int y, clen=startlen, tlen;

  memset(extract_array[fd], 0x00, 64);
  tlen=strlen(token);

  for( ; clen <= tlen && clen <= 63 ; clen++) {
    if(token[clen]=='=') {
      for(clen++,y=0; clen<=tlen&&clen<=63;clen++) {
        /* we only want characters [A-Z][a-z][0-9]/@: and . */
        if((isalnum(token[clen]) != 0)
                || (token[clen] == '@')
                || (token[clen] == '|')
                || (token[clen] == '.')
                || (token[clen] == ':')
                || (token[clen] == '_')
                || (token[clen] == '-')
                || (token[clen] == ' ')
                || (token[clen] == '/')) {
          extract_array[fd][y]=token[clen]; y++;
        }
      }
    }
  }
}




/*
 * function: extract_conf
 *  purpose: extract policy variable
 *   return: policy variable (into an array)
 */
void
extract_conf(unsigned int fd, char *token, unsigned int startlen)
{
  unsigned int y, clen=startlen, tlen;

  memset(extract_array_conf[fd], 0x00, 512);
  tlen=strlen(token);

  for( ; clen <= tlen && clen <= 511 ; clen++) {
    if(token[clen]=='=') {
      for(clen++,y=0; clen<=tlen&&clen<=511;clen++) {
        /* we only want characters [A-Z][a-z][0-9]/@ and . */
        if((isalnum(token[clen]) != 0) || isascii(token[clen] != 0)) {
	  if((token[clen] != '"') && (token[clen] != '\n')) {
          extract_array_conf[fd][y]=token[clen]; y++; }
        }
      }
    }
  }
}





/*
 * XXX Needs updating to handle IPv6 subnets (use prefix length 
 * XXX instead of number of octets?)
 * function: extract_ip
 *  purpose: extract ip address from policy variable
 *   return: policy variable (into an array)
 */
void
extract_ip(unsigned int fd, char *token)
{
  unsigned int x=15, y=0, z=0, len;

  memset(extract_ip_array[fd], 0x00, 64);
  len=strlen(token);

  for(x=15,z=0,y=0;x<len||x<64;x++) {
    if(token[x] == '\n') break;

    /* we only want characters [0-9][a-f][A-F]/: and . */
    if ( (isdigit(token[x]) != 0)
	 || ((token[x] == '.') || (token[x] == ':'))
	 || ((token[x] >= 'a') && (token[x] <= 'f'))
	 || ((token[x] >= 'A') && (token[x] <= 'F')) )
    {
      if(token[x] == '.') z++;
      if(z == GREYLIST_HOSTADDR) break;
      extract_ip_array[fd][y]=token[x]; y++;
    }
  }
}




/*
 * XXX Needs updating to handle IPv6 subnets (use prefix length 
 * XXX instead of number of octets?)
 * function: extract_ipfill
 *  purpose: extract ip address from policy variable
 *   return: policy variable (into an array)
 */
void
extract_ipfill(unsigned int fd, char *token)
{
  unsigned int x=15, y=0, z=0, len, w=5;

  memset(extract_ip_array[fd], 0x00, 64);
  len=strlen(token);

  for(x=15,z=0,y=0;x<len||x<64;x++) {
    if(token[x] == '\n') break;

    /* we only want characters [0-9][a-f][A-F]: and . */
    if ( (isdigit(token[x]) != 0)
	 || ((token[x] == '.') || (token[x] == ':'))
	 || ((token[x] >= 'a') && (token[x] <= 'f'))
	 || ((token[x] >= 'A') && (token[x] <= 'F')) )
    {
      if(token[x] == '.')
      {
        if(z == 0) 
           snprintf(host_array[fd][w], 64, "%s.%%.%%.%%", extract_ip_array[fd]);

        if(z == 1)
           snprintf(host_array[fd][w], 64, "%s.%%.%%", extract_ip_array[fd]);
  
        if(z == 2) 
           snprintf(host_array[fd][w], 64, "%s.%%", extract_ip_array[fd]);

        z++; w--;
      }

      if(z == GREYLIST_HOSTADDR)
        break;
      extract_ip_array[fd][y]=token[x];
      y++;
    }
  }
}




/*
 *  function: fold
 *   purpose: shut down all open connections and close policyd gracefully
 *    return: nada
 */
void
fold()
{
  logmessage("shutting down..\n");
  shutdown(msock, SHUT_RDWR);
  shutdown(ssock, SHUT_RDWR);
  exit(0);
}




/*
 * function: gettime   
 *  purpose: get current time       
 *   return: return current time
 */
int
gettime(void)
{
  /* get current time */
  if(gettimeofday(&timevalue, NULL) != 0)
  {
    logmessage("gettimeofday(): %s\n", strerror(errno));
    exit(-1);
  }

  return (timevalue.tv_sec);
}




/*  
 * function: drop_privs
 *  purpose: drop privledges 
 *   return: nada
 */
void
drop_privs(void)
{
  /* 
   * 1) quick sanity check
   * 2) ensure backward compatibility with old configs
   */
  if(PIDFILE)
  {
    /* write to pid file */
    if((pidfile=fopen(PIDFILE, "w")) == NULL)
    {
      fprintf(stderr, "fopen(): %s: %s\n", strerror(errno), PIDFILE);
      exit(-1);
    }
    fprintf(pidfile, "%d\n", (unsigned int)getpid());

    /* we're done, clean up */
    if(fclose(pidfile) != 0)
    {
      logmessage("fclose(): %s: %s\n", PIDFILE, strerror(errno));
      exit(-1);
    }
  }

  /* change root */
  if(chdir(CHROOT) == -1) 
  {
    logmessage("chdir(): %s\n", strerror(errno));
    exit(-1);
  }
   
  /* chroot */
  if(chroot(".") == -1)
  {
    logmessage("chroot(): %s\n", strerror(errno));
    exit(-1);
  }

  /* change gid */
  if(setgid(GID) == -1)
  {
    logmessage("setgid(): %s\n", strerror(errno));
    exit(-1);
  }

  /* change uid */
  if(setuid(UID) == -1)
  {
    logmessage("setuid(): %s\n", strerror(errno));
    exit(-1);
  }
  
}




/*
 * function: policy_reply
 *  purpose: reply/talk to Postfix
 *   return: 0=sucessfull write(), 1=failed write()
 */
void
policy_reply(unsigned int fd, int code, int status)
{
  /* keep gcc quiet for now */
  status = 0;
 

  switch (code)
  {
    /* accept: always allow */
    case 0:
      /* dump greylisting information into mail? */
      if((GREYLISTING==1) && (GREYLIST_X_HEADER==1))
      {
      
        /* whitelisted */
        snprintf(xgreylist_array[fd], 128, "%s host: %s\n\n",
        POSTFIX_X_HEADER, host_array[fd][2]);
      
        /* if not whitelisted, greylist.c already filled in all the details */
        buf_write(fd, xgreylist_array[fd], strlen(xgreylist_array[fd]));
      } else {
    	buf_write(fd, POSTFIX_GOOD, strlen(POSTFIX_GOOD));
      }

    break;

  /* reject: greylisting */
  case -1:
    buf_write(fd, postfix_greylist, strlen(postfix_greylist));
    break;

  /* reject: blacklisted */
  case -2:
    buf_write(fd, postfix_blacklist, strlen(postfix_blacklist));
    break;

  /* reject: message size too big */
  case -3:
    buf_write(fd, postfix_bad_size, strlen(postfix_bad_size));
    break;
    
  /* reject: spam trap address */
  case -4:
    buf_write(fd, postfix_spamtrap, strlen(postfix_spamtrap));
    break;
    
  /* reject: max sender quota exceeded */
  case -5:
    buf_write(fd, postfix_sender_quota_exceeded, strlen(postfix_sender_quota_exceeded));
    break;
    
  /* reject: helo checking */
  case -6:
    buf_write(fd, postfix_blacklist, strlen(postfix_blacklist));
    break;
    
  /* reject: max recipient quota exceeded */
  case -7:
    buf_write(fd, postfix_recipient_quota_exceeded, strlen(postfix_recipient_quota_exceeded));
    break;
    
  /* reject: max recipient quota exceeded */
  case -8:
    buf_write(fd, POSTFIX_MODULE_FAILURE, strlen(POSTFIX_MODULE_FAILURE));
    break;

  /* something bad happened */
  default:
    logmessage("WARNING: policy_reply called with unknown code\n");
    buf_write(fd, POSTFIX_MODULE_FAILURE, strlen(POSTFIX_MODULE_FAILURE));
    break;
  }
}




/*
 * function: db_failure
 *  purpose: handle database failures so policyd isnt a single point of failure.
 *   return: -20 for failures (talk to Postfix first)
 */
int
db_failure(unsigned int fd, char *module)
{

  if(FAILSAFE==1)
  {
    /* do not fail */
    logmessage("rcpt=%lu, %s=bypass, host=%s (%s), from=%s, to=%s, size=%s\n",
      rcpt_count,               /* recipient count      */
      module,                   /* module name          */
      host_array[fd][2],        /* host                 */
      host_array[fd][0],        /* hostname             */
      triplet_array[fd][1],     /* from                 */
      triplet_array[fd][2],     /* rcpt                 */
      triplet_array[fd][3]      /* size                 */
    );  

    mysql_failure_count++;
    policy_reply(fd, 0, 0);
    return (-20); 
  }             
                
  if(FAILSAFE==0)
  {             
    /* fail as requested */
    logmessage("rcpt=%lu, %s=failed, host=%s (%s), from=%s, to=%s, size=%s\n",
      rcpt_count,               /* recipient count      */
      module,                   /* module name          */
      host_array[fd][2],        /* host                 */
      host_array[fd][0],        /* hostname             */
      triplet_array[fd][1],     /* from                 */
      triplet_array[fd][2],     /* rcpt                 */
      triplet_array[fd][3]      /* size                 */
    );

    policy_reply(fd, -1, 0);
    return (-20);
  }

  return (0); /* not reached */
}


void
sigalrm_handler (void)
{
  alarm (0);                      /* reset alarm timer */
  siglongjmp (sjmp, 1);           /* jump back */
}



/* EOF */
