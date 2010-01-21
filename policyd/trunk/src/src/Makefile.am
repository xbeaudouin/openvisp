
bin_PROGRAMS = policyd cleanup stats


policyd_SOURCES = policyd.c syslog.c generic.c mysql.c greylist.c throttle.c 
policyd_SOURCES += sockets.c cidr.c spamtrap.c blacklist.c blacklist_helo.c
policyd_SOURCES += whitelist.c throttle_host.c throttle_sasl.c throttle_from.c
policyd_SOURCES += throttle_rcpt.c helo.c rcpt_acl.c policyd.h
policyd_LDADD = @LIBMYSQLCLIENT@
policyd_LDFLAGS = -lm
policyd_CFLAGS = @MYSQL_INCLUDE@ $(AM_CFLAGS)

cleanup_SOURCES = cleanup.c syslog.c cidr.c generic.c sockets.c mysql.c policyd.h
cleanup_LDADD = @LIBMYSQLCLIENT@
cleanup_CFLAGS = @MYSQL_INCLUDE@ $(AM_CFLAGS)

stats_SOURCES = stats.c cidr.c syslog.c generic.c sockets.c mysql.c policyd.h
stats_LDADD = @LIBMYSQLCLIENT@
stats_CFLAGS = @MYSQL_INCLUDE@ $(AM_CFLAGS)

