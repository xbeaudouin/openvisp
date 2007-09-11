#
# Copyright (c) 2005 Xavier Beaudouin <kiwi@oav.net>
#
# $Id: Makefile,v 1.10 2007-09-11 08:26:01 kiwi Exp $
#

##
## Have a look in this file the compilation / configuration options.
##
## If you are using Debian/GNU Linux, please double check the
## specific updates for your OS.
##

# In general you can use apxs, but on debian you should use apxs2
APXS = apxs
#APXS = apxs2

NAME = mod_vhs
SRCS = $(NAME).c
OBJS = $(NAME).o
APACHE_MODULE = $(NAME).so

RM = rm -f
LN = ln -sf
CP = cp -f
INDENT = /usr/bin/indent

CFLAGS=-I/usr/local/include -DHAVE_MMC_SUPPORT

# For debian users, you'll have to uncomment these of you will have
# big errors under complilation. Don't ask me why, but debian apache2
# is redefining strangely some headers.... :/

#CFLAGS= -DDEBIAN -I/usr/include/apr-0

# If you want to enable mod_suphp support please uncomment the following
# line :
#CFLAGS+=	-DHAVE_MOD_SUPHP_SUPPORT

# Uncomment only ONE of these CFLAGS according of what kind of compilation
# you need with mod_vhs

# Flags for compilation (Full PHP + Debug)
#CFLAGS+= -I/usr/local/include -I/usr/local/include/php -I/usr/local/include/php/main -I/usr/local/include/php/TSRM -I/usr/local/include/php/Zend -DHAVE_MOD_PHP_SUPPORT -DVH_DEBUG -Wc,-Wall

# Flags for compilation (Full Debug)
#CFLAGS+= -I/usr/local/include/home -DVH_DEBUG -Wc,-Wall

# Flags for compilation with PHP
CFLAGS+= -I/usr/local/include/home -I/usr/local/include/php -I/usr/local/include/php/main -I/usr/local/include/php/TSRM -I/usr/local/include/php/Zend -DHAVE_MOD_PHP_SUPPORT -Wc,-Wall

# Flags for compilation with only libhome
#CFLAGS+= -I/usr/local/include/home -Wc,-Wall

LDFLAGS = -L/usr/local/lib -lhome

################################################################
### End of user configuration directives
################################################################

default: all

all: install

install: $(SRCS)
	$(APXS) -i -a -c $(LDFLAGS) $(CFLAGS) $(SRCS)

clean:
	$(RM) $(OBJS) $(APACHE_MODULE) $(NAME).slo $(NAME).lo $(NAME).la
	$(RM) -r .libs

indent:
	$(INDENT) $(SRCS)
	$(RM) $(SRCS).BAK
