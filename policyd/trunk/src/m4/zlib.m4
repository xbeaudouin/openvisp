# Ripped from MyDNS

##
## Find zlib compression library (@LIBZ@)
##
AC_DEFUN([AC_LIB_Z],
	[
		ac_zlib_dirs="/lib /usr/lib /usr/local/lib"
		AC_ARG_WITH(zlib,
			AC_HELP_STRING([--with-zlib=DIR], [look for the zlib compression library in DIR]),
			ac_zlib_dirs="$withval $ac_zlib_dirs")
		ac_zlib_found=no, ac_zlib_ok=no
		for dir in $ac_zlib_dirs
		do
			if test "$ac_zlib_found" != yes
			then
				AC_CHECK_FILE($dir/libz.a, ac_zlib_found=yes, ac_zlib_found=no)
				if test "$ac_zlib_found" = yes
				then
					AC_CHECK_LIB(z, deflate, ac_zlib_ok=yes, ac_zlib_ok=no)
					if test "$ac_zlib_ok" = yes
					then
						LIBZ="-L$dir -lz"
					fi
				fi
			fi
		done
		AC_SUBST(LIBZ)
	]
)
