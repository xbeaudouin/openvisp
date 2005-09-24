# This compiles and install the module With PHP support
apxs -i -a -c -I/usr/local/include/home -I/usr/local/include/php -I/usr/local/include/php/TSRM -I/usr/local/include/php/Zend -DHAVE_MOD_PHP_SUPPORT -DVH_DEBUG -L/usr/local/lib -lhome mod_vhs.c
