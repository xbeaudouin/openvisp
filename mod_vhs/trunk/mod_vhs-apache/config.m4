dnl modules enabled in this directory by default

AC_PREREQ(2.54)

APACHE_MODPATH_INIT(mod_vh)
APACHE_MODULE(vdbh, mass virtual hosting module, mod_vh.lo, , yes, [
	AC_CHECK_HEADERS(stddef.h)
	AC_PATH_PROGS(LIBHOME_SH, libhome.sh)
  	if test "$LIBHOME_SH" != ""
  	then
		PW_INC=`$LIBHOME_SH --inc`
		PW_LIB=`$LIBHOME_SH --lib`
		savedCPPFLAGS="$CPPFLAGS"
		savedLIBS="$LIBS"
		LIBS="$LIBS $PW_LIB"
		savedCPPFLAGS="$CPPFLAGS"
		CPPFLAGS="$CPPFLAGS $PW_INC"
		AC_CHECK_HEADERS([home_version.h])
		CPPFLAGS="$savedCPPFLAGS"
		AC_CHECK_LIB(home, home_getpwnam)
  	fi
])

APACHE_MODPATH_FINISH
