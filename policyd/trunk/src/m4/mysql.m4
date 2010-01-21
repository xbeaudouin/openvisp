# Ripped from MyDNS

##
## Find MySQL client library (@LIBMYSQLCLIENT@)
##
AC_DEFUN([AC_LIB_MYSQLCLIENT],
	[
		libmysqlclient_dirs="/usr/local/mysql/lib /usr/local/lib/mysql /usr/local/lib /usr/lib64/mysql /usr/pkg/lib/mysql \
				/usr/lib/mysql /usr/lib /lib"
		AC_ARG_WITH(mysql-lib,
			AC_HELP_STRING([--with-mysql-lib=DIR], [look for the MySQL client library in DIR]),
			libmysqlclient_dirs="$withval $libmysqlclient_dirs")
		libmysqlclient_found=no
		for libmysqlclient_dir in $libmysqlclient_dirs; do
			if test "$libmysqlclient_found" != yes; then
				if test "$libmysqlclient_found" != yes; then
					# Look for shared lib first
					AC_CHECK_FILE($libmysqlclient_dir/libmysqlclient.so, libmysqlclient_found=yes, libmysqlclient_found=no)
					if test "$libmysqlclient_found" = yes; then
						LIBMYSQLCLIENT="-L$libmysqlclient_dir -lmysqlclient"
					else
						# If shared lib not found, look for static lib
						AC_CHECK_FILE($libmysqlclient_dir/libmysqlclient.a, libmysqlclient_found=yes, libmysqlclient_found=no)
						if test "$libmysqlclient_found" = yes; then
							# Darwin/OSX/powerpc doesn't seem to like linking against .a's
							#LIBMYSQLCLIENT="$libmysqlclient_dir/libmysqlclient.a"
							LIBMYSQLCLIENT="-L$libmysqlclient_dir -lmysqlclient"
						fi
					fi
				fi
			fi
		done


		if test "$libmysqlclient_found" = yes; then
			## libmysqlclient depends on libz
			if ! test -n "$LIBZ"; then
				AC_LIB_Z
			fi
			if ! test -n "$LIBZ"; then
				## No zlib
				AC_MSG_ERROR([

][  ###
][  ###  zlib compression library (libz.a) not found.
][  ###
][  ###  Please download and install the zlib compression
][  ###  library from the following URL:
][  ###
][  ###       http://www.gzip.org/zlib/
][  ###
][  ###  (Error detail might be available in `config.log')
][  ###
])
     			fi
			LIBMYSQLCLIENT="$LIBMYSQLCLIENT $LIBZ"
		fi
		if test "$libmysqlclient_found" != yes; then
			## No MySQL
			AC_MSG_ERROR([

][  ###
][  ###  MySQL client library (libmysqlclient.so or libmysqlclient.a)
][  ###  not found.
][  ###
][  ###  You need the MySQL development libraries and headers
][  ###  installed in order to build policyd.
][  ### 
][  ###  You can also try specifying:
][  ###
][  ###  --with-mysql-include=DIR  and/or  --with-mysql-lib=DIR
][  ###
][  ###  (Error detail might be available in `config.log')
][  ###
])
		fi
		AC_SUBST(LIBMYSQLCLIENT)
	]
)

##
##	Find location of MySQL header files (@MYSQL_INCLUDE@)
##
AC_DEFUN([AC_HEADER_MYSQL],
	[
		ac_mydns_header_dirs="/usr/include /usr/include/mysql /usr/local/include \
				/usr/local/include/mysql /usr/local/mysql/include /usr/pkg/include/mysql"
		ac_mydns_header_found=no, ac_mydns_header_ok=no
		AC_ARG_WITH(mysql-include,
			AC_HELP_STRING([--with-mysql-include=DIR],
								[look for MySQL include files in DIR]),
			ac_mydns_header_dirs="$withval $ac_mydns_header_dirs")
		for dir in $ac_mydns_header_dirs
		do
			if test "$ac_mydns_header_found" != yes
			then
				AC_CHECK_FILE($dir/mysql.h, ac_mydns_header_found=yes, ac_mydns_header_found=no)
				test "$ac_mydns_header_found" = yes && MYSQL_INCLUDE="-I$dir"
			fi
		done
		AC_SUBST(MYSQL_INCLUDE)
	]
)


