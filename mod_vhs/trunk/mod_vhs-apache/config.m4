dnl modules enabled in this directory by default

AC_PREREQ(2.54)

APACHE_MODPATH_INIT(mod_vdbh)
APACHE_MODULE(vdbh, mass virtual hosting module, mod_vdbh.lo, , yes, [
	AC_CHECK_HEADERS(stddef.h)
	
	AC_ARG_WITH(mysqldir, 
		AC_HELP_STRING(--with-mysqldir=DIR, MySQL directory prefix), 
		[
			mydir=$withval
			
			if -z "$mydir"; then
				mydir="/usr/local"
			fi
			
			AC_MSG_RESULT(using `$mydir' as MySQL directory prefix)
		],
		[	
			mydir="/usr/local"
			AC_MSG_RESULT(using `$mydir' as MySQL directory prefix)
		])
		
	AH_TEMPLATE([HAVE_LIBMYSQLCLIENT],
				[Define to 1 if you have the libmysqlclient library installed.])
	
	APR_ADDTO(LDFLAGS, [-L${mydir}/lib/mysql])
	
	AC_CHECK_LIB(mysqlclient, mysql_init,
		[AC_DEFINE(HAVE_LIBMYSQLCLIENT)
			APR_ADDTO(LIBS, [-lmysqlclient])
			APR_ADDTO(LDFLAGS, [-R${mydir}/lib/mysql])
			APR_ADDTO(INCLUDES, [-I${mydir}/include/mysql])
		],
		[AC_MSG_ERROR([libmysqlclient not found])
	])
])

APACHE_MODPATH_FINISH
