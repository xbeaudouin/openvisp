# This compiles and install the module With PHP support
apxs -i -a -c -I/usr/local/include/php/Zend -I/usr/local/include/php -DHAVE_MOD_PHP_SUPPORT -L/usr/local/lib -lhome mod_vhs.c
