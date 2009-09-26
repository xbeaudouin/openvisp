#! /bin/sh
case $1 in
	'start')
		/usr/local/policyd/policyd -c /usr/local/policyd/policyd.conf
		;;
	'stop')
		pkill -15 policyd
		;;
esac
