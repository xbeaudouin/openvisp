mod_vdbh.la: mod_vh.lo
	$(MOD_LINK) mod_vh.lo
DISTCLEAN_TARGETS = modules.mk
static =  mod_vh.la
shared =