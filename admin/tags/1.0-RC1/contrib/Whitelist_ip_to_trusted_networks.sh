#!/bin/sh
#
# update trusted netowrks in spamassassin from policyd whitelist
#
# keep 02_networks AFTER your local clear networks eg have a
# clear_networks in file 00_networks_trusted.cf and all you local ip
# in 01_networks_trusted.cf
#
php /usr/local/sbin/Whitelist_ip_to_trusted_networks.php > /etc/mail/spamassassin/02_networks_trusted_policyd_whitelist_ip.cf
#

