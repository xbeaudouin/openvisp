dnl modules enabled in this directory by default

AC_PREREQ(2.54)

APACHE_MODPATH_INIT(mod_vh)
APACHE_MODULE(vdbh, mass virtual hosting module, mod_vh.lo, , yes, [
	AC_CHECK_HEADERS(stddef.h)
	AC_CHECK_HEADERS(home/hpwd.h)
	AC_CHECK_HEADERS([home/home_version.h])
	AC_CHECK_LIB(home, setpwtag)
	AC_CHECK_LIB(home, home_getpwnam)
])

APACHE_MODPATH_FINISH
