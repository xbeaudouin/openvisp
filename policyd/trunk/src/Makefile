#
# systems which install in /usr
inc  = -I/usr/include/mysql
lib  = -L/usr/lib/mysql -L/usr/lib64/mysql
# systems with single mysql subdir
inc += -I/usr/local/mysql/include -I/usr/local/mysql/include/mysql
lib += -L/usr/local/mysql/lib -L/usr/local/mysql/lib/mysql
# freebsd ports
inc += -I/usr/local/include/mysql
lib += -L/usr/local/lib/mysql
# netbsd pkgsrc
inc += -I/usr/pkg/include/mysql
lib += -L/usr/pkg/lib/mysql
# os x
lib += -L/usr/local/mysql


CC       := gcc
CPPFLAGS := -O $(inc)
CFLAGS   := -g -W -Wall -DMAXFDS=4096
OS_NAME  := $(shell uname | tr [A-Z] [a-z])
LDLIBS    = $(lib) -lmysqlclient -lz

ifeq "${OS_NAME}" "sunos"
LDLIBS   += -lsocket -lnsl -lm
endif

.c.o:
	@echo "  compiling ${CFLAGS} $<"
	@${CC} ${CPPFLAGS} ${CFLAGS} -o $@ -c $<

all:
	@echo ""
	@echo "Possible options are:"
	@echo ""
	@echo "  make build"
	@echo "  make install | install-strip"
	@echo "  make upgrade"
	@echo "  make clean"
	@echo ""
	@exit

build: policyd_banner policyd_start policyd cleanup stats

linux: all
solaris: all
freebsd: all

policyd: syslog.o policyd.o generic.o mysql.o greylist.o throttle.o sockets.o cidr.o spamtrap.o blacklist.o blacklist_helo.o \
	whitelist.o throttle_host.o throttle_sasl.o throttle_from.o throttle_rcpt.o helo.o
	@echo ""
	@echo "[*] Linking policyd*"
	@$(CC) $(CFLAGS) $(CPPFLAGS) \
	syslog.o policyd.o generic.o mysql.o greylist.o throttle.o sockets.o cidr.o spamtrap.o blacklist.o blacklist_helo.o \
	whitelist.o throttle_host.o throttle_sasl.o throttle_from.o throttle_rcpt.o helo.o \
	$(LDLIBS) -o policyd

cleanup: syslog.o cidr.o cleanup.o generic.o sockets.o mysql.o
	@echo "[*] Linking cleanup*"
	@$(CC) $(CFLAGS) $(CPPFLAGS) \
	syslog.o cidr.o cleanup.o generic.o sockets.o mysql.o \
	$(LDLIBS) -o cleanup

stats: cidr.o syslog.o stats.o generic.o sockets.o mysql.o
	@echo "[*] Linking stats*"
	@$(CC) $(CFLAGS) $(CPPFLAGS) \
	cidr.o syslog.o stats.o generic.o sockets.o mysql.o \
	$(LDLIBS) -o stats
	@echo ""

policyd_start: blacklist.o blacklist_helo.o cidr.o cleanup.o \
	generic.o greylist.o helo.o mysql.o policyd.o sockets.o \
	spamtrap.o stats.o syslog.o throttle.o throttle_from.o \
	throttle_host.o throttle_rcpt.o throttle_sasl.o whitelist.o

policyd_banner:
	@echo ""
	@echo "[-] Building Policy Daemon:"
	@echo ""
	@echo "[*] Building Objects:"
	@echo ""

clean:
	rm -f *.o core policyd cleanup stats

install: policyd cleanup stats
	mkdir -p /usr/local/policyd
	cp -f stats cleanup policyd /usr/local/policyd
	cp -i policyd.conf /usr/local/policyd

upgrade: policyd cleanup stats
	cp -f stats cleanup policyd /usr/local/policyd

install-strip: policyd cleanup stats
	mkdir -p /usr/local/policyd
	strip stats cleanup policyd
	cp -f stats cleanup policyd /usr/local/policyd
	cp -i policyd.conf /usr/local/policyd

