#
# Copyright (c) 2005 Xavier Beaudouin <kiwi@oav.net>
#
# $Id: Makefile,v 1.5 2005-12-14 08:45:30 kiwi Exp $
#

NAME = mod_vhs
# In general you can use apxs, but on debian you should use apxs2
#APXS = apxs
APXS = apxs2
SRCS = $(NAME).c
OBJS = $(NAME).o
APACHE_MODULE = $(NAME).so

RM = rm -f
LN = ln -sf
CP = cp -f
INDENT = /usr/bin/indent

# For debian users
CFLAGS= -DDEBIAN -I/usr/include/apr-0
#CFLAGS= -I/usr/include/apr-0

# Uncomment one of these CFLAGS according of what kind of compilation
# you need with mod_vhs

# Flags for compilation (Full PHP + Debug)
#CFLAGS+= -I/usr/local/include -I/usr/local/include/php -I/usr/local/include/php/TSRM -I/usr/local/include/php/Zend -DHAVE_MOD_PHP_SUPPORT -DVH_DEBUG -Wc,-Wall

# Flags for compilation (Full Debug)
CFLAGS+= -I/usr/local/include/home -DVH_DEBUG -Wc,-Wall

# Flags for compilation with PHP
#CFLAGS+= -I/usr/local/include/home -I/usr/local/include/php -I/usr/local/include/php/TSRM -I/usr/local/include/php/Zend -DHAVE_MOD_PHP_SUPPORT -Wc,-Wall

# Flags for compilation with only libhome
#CFLAGS+= -I/usr/local/include/home -Wc,-Wall

LDFLAGS = -L/usr/local/lib -lhome

default: all

all: install

install: $(SRCS)
	$(APXS) -i -a -c $(LDFLAGS) $(CFLAGS) $(SRCS)

clean:
	$(RM) $(OBJS) $(APACHE_MODULE) $(NAME).slo $(NAME).lo $(NAME).la

indent:
	$(INDENT) $(SRCS)
	$(RM) $(SRCS).BAK
