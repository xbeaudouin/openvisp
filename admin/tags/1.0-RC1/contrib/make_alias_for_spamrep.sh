#!/bin/sh
#
# update spamrep alias
#
cd /path/to/localhost
#
# /path/to/localhost is where you put make_alias_file_for_spamrep.php 
#
php make_alias_file_for_spamrep.php > /usr/local/etc/spamrep_byuser.users
#
# the /usr/local/etc/spamrep_byuser.users is default location for the spamrep_today_byuser script
#
