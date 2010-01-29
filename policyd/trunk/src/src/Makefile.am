
bin_PROGRAMS = ovpd ovpcleanup ovpstats


ovpd_SOURCES = ovp.c syslog.c generic.c mysql.c greylist.c throttle.c 
ovpd_SOURCES += sockets.c cidr.c spamtrap.c blacklist.c blacklist_helo.c
ovpd_SOURCES += whitelist.c throttle_host.c throttle_sasl.c throttle_from.c
ovpd_SOURCES += throttle_rcpt.c helo.c rcpt_acl.c ovp.h
ovpd_LDADD = @LIBMYSQLCLIENT@
ovpd_LDFLAGS = -lm
ovpd_CFLAGS = @MYSQL_INCLUDE@ $(AM_CFLAGS)

ovpcleanup_SOURCES = cleanup.c syslog.c cidr.c generic.c sockets.c mysql.c ovp.h
ovpcleanup_LDADD = @LIBMYSQLCLIENT@
ovpcleanup_CFLAGS = @MYSQL_INCLUDE@ $(AM_CFLAGS)

ovpstats_SOURCES = stats.c cidr.c syslog.c generic.c sockets.c mysql.c ovp.h
ovpstats_LDADD = @LIBMYSQLCLIENT@
ovpstats_CFLAGS = @MYSQL_INCLUDE@ $(AM_CFLAGS)

