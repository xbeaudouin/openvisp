dnl modules enabled in this directory by default

AC_PREREQ(2.54)

APACHE_MODPATH_INIT(mod_vh)
APACHE_MODULE(vdbh, mass virtual hosting module, mod_vh.lo, , yes, [
	AC_CHECK_HEADERS(stddef.h)
])

APACHE_MODPATH_FINISH
