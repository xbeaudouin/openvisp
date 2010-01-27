
bin_PROGRAMS = ovpd cleanup stats


ovpd_SOURCES = ovp.c syslog.c generic.c mysql.c greylist.c throttle.c 
ovpd_SOURCES += sockets.c cidr.c spamtrap.c blacklist.c blacklist_helo.c
ovpd_SOURCES += whitelist.c throttle_host.c throttle_sasl.c throttle_from.c
ovpd_SOURCES += throttle_rcpt.c helo.c rcpt_acl.c ovp.h
ovpd_LDADD = @LIBMYSQLCLIENT@
ovpd_LDFLAGS = -lm
ovpd_CFLAGS = @MYSQL_INCLUDE@ $(AM_CFLAGS)

cleanup_SOURCES = cleanup.c syslog.c cidr.c generic.c sockets.c mysql.c ovp.h
cleanup_LDADD = @LIBMYSQLCLIENT@
cleanup_CFLAGS = @MYSQL_INCLUDE@ $(AM_CFLAGS)

stats_SOURCES = stats.c cidr.c syslog.c generic.c sockets.c mysql.c ovp.h
stats_LDADD = @LIBMYSQLCLIENT@
stats_CFLAGS = @MYSQL_INCLUDE@ $(AM_CFLAGS)

