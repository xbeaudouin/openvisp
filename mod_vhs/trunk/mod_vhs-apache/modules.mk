mod_vdbh.la: mod_vdbh.lo
	$(MOD_LINK) mod_vdbh.lo
DISTCLEAN_TARGETS = modules.mk
static =  mod_vdbh.la
shared =