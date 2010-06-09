/*
 * Version of mod_vhs
 */
#define VH_VERSION	"mod_vhs/1.1.0-RC0"


/*
 * Set this if you'd like to have looooots of debug
 */
/*
 * #define VH_DEBUG 1
 */

/*
 * Define this if you have Linux/Debian since it seems to have non standards
 * includes
 */
/*
 * #define DEBIAN 1
 */

/*
 * activate LDAP support
 */
/*
 * #define HAVE_LDAP_SUPPORT
 */

/* Original Author: Michael Link <mlink@apache.org> */
/* mod_vhs author : Xavier Beaudouin <kiwi@oav.net> */
/* Some parts of this code has been stolen from mod_alias */
/* added support for apache2-mpm-itk by Rene Kanzler <rk (at) cosmomill (dot) de> */

/* We need this to be able to access the docroot. */
#define CORE_PRIVATE

#define APR_WANT_STRFUNC
#include "apr_want.h"

#include "apr.h"
#include "apr_strings.h"
#include "apr_lib.h"
#include "apr_uri.h"
#include "apr_thread_mutex.h"
#if APR_MAJOR_VERSION > 0
#include "apr_regexp.h"
#endif

#include "ap_config.h"
#include "httpd.h"
#include "http_config.h"
#include "http_core.h"
#include "http_log.h"
#include "http_main.h"
#include "http_protocol.h"
#include "http_request.h"
#include "util_script.h"
#include "util_ldap.h"
#include "apr_ldap.h"
#include "apr_strings.h"
#include "apr_reslist.h"
#include "apr_dbd.h"
#include "mod_dbd.h"
#include "mpm_common.h"

#include "ap_config_auto.h"

#if defined(HAVE_LDAP_SUPPORT) && !defined(APU_HAS_LDAP) && !defined(APR_HAS_LDAP)
#error mod_vhs requires APR-utils to have LDAP support built in
#endif
#if defined(HAVE_LDAP_SUPPORT) && defined(HAVE_MOD_DBD_SUPPORT)
#error mod_vhs requires only one backend. Choose between HAVE_LDAP_SUPPORT and HAVE_MOD_DBD_SUPPORT
#endif


/* XXX: Do we need that ? */
//#include "ap_mpm.h" /* XXX */

#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <ctype.h>
#include <unistd.h>
#include <errno.h>

#include <sys/types.h>
#include <sys/stat.h>
#include <sys/time.h>
#include <sys/mman.h>
#include <fcntl.h>

/*
 * To enable Apache 2.2 compatibility
 */
#if MODULE_MAGIC_NUMBER_MAJOR >= 20050217
# ifndef DEBIAN
#  define DEBIAN
# endif
#endif

/*
 * Include php support
 */
/*
 * #define HAVE_MOD_PHP_SUPPORT
 */

#ifdef HAVE_MOD_PHP_SUPPORT
#  include <zend.h>
#  include <zend_qsort.h>
#  include <zend_API.h>
#  include <zend_ini.h>
#  include <zend_alloc.h>
#  include <zend_operators.h>
#endif

/*
 * For mod_alias like operations
 */
#define AP_MAX_REG_MATCH 10

/*
 * To avoid compatibity and segfault
 */
#ifdef HAVE_MOD_PHP_SUPPORT
#  ifdef HAVE_MOD_SUPHP_SUPPORT
#    error mod_vhs cannot support mod_php and suphp in the same time.
#    error Please chose what support you want to have
#  endif
#endif

/*
 * Configuration structure
 */
typedef struct {
	unsigned short int	enable;			/* Enable the module */
	char           		*path_prefix;		/* Prefix to add to path returned by database/ldap */
	char           		*default_host;		/* Default host to redirect to */

	unsigned short int 	lamer_mode;		/* Lamer friendly mode */
	unsigned short int 	log_notfound;		/* Log request for vhost/path is not found */

#ifdef HAVE_MOD_PHP_SUPPORT
	char           		*openbdir_path;		/* PHP open_basedir default path */
	unsigned short int 	safe_mode;		/* PHP Safe mode */
	unsigned short int 	open_basedir;		/* PHP open_basedir */
	unsigned short int 	append_basedir;		/* PHP append current directory to open_basedir */
	unsigned short int 	display_errors;		/* PHP display_error */
	unsigned short int 	phpopt_fromdb;		/* Get PHP options from database/ldap */
#endif /* HAVE_MOD_PHP_SUPPORT */

#ifdef HAVE_MPM_ITK_SUPPORT
        unsigned short int	itk_enable;			/* MPM-ITK support */
	uid_t			itk_defuid;
	gid_t			itk_defgid;
	char			*itk_defusername;
#endif /* HAVE_MPM_ITK_SUPPORT */

#ifdef HAVE_MOD_SUPHP_SUPPORT
	char			  *suphp_config_path;	/* suPHP_ConfigPath */
#endif /* HAVE_MOD_SUPHP_SUPPORT */

#ifdef HAVE_LDAP_SUPPORT
	char				*ldap_url;		/* String representation of LDAP URL */
	char				*ldap_host;		/* Name of the ldap server or space separated list */
	int				ldap_port;		/* Port of the LDAP server */
	char				*ldap_basedn;		/* Base DN */
	int				ldap_scope;		/* Scope of search */
	char				*ldap_filter;		/* LDAP Filter */
	deref_options			ldap_deref;		/* How to handle alias dereferening */
	char				*ldap_binddn;		/* DN to bind to server (can be NULL) */
	char				*ldap_bindpw;		/* Password to bind to server (can be NULL) */
	int				ldap_have_deref;	/* Set if we have found an Deref option */
	int 				ldap_have_url;		/* Set if we have found an LDAP url */
	int				ldap_secure;		/* True if SSL connections are requested */
#endif /* HAVE_LDAP_SUPPORT */

#ifdef HAVE_MOD_DBD_SUPPORT
	const char			*dbd_table_name;
	const char			*query;
	const char			*label;
#endif
	/*
	 * From mod_alias.c
	 */
	apr_array_header_t		*aliases;
	apr_array_header_t		*redirects;
	/*
	 * End of borrowing
	 */
} vhs_config_rec;

typedef struct mod_vhs_request_t {
#ifdef HAVE_LDAP_SUPPORT
    char *dn;				/* The saved dn from a successful search */
#endif
    char *name;				/* ServerName or host accessed uppon request */
    char *associateddomain;		/* The real server name */
    char *admin;			/* ServerAdmin or email for admin */
    char *docroot;			/* DocumentRoot */
    char *phpoptions;			/* PHP Options */
    char *uid;				/* Suexec Uid */
    char *gid;				/* Suexec Gid */
    int vhost_found;			/* set to 1 if the struct is field with vhost information, 0 if not, -1 if the vhost does not exist  */
} mod_vhs_request_t;

/*
 * From mod_alias.c
 */
typedef struct {
	const char     *real;
	const char     *fake;
	char           *handler;

#if APR_MAJOR_VERSION > 0
	ap_regex_t     *regexp;
#else
#ifdef DEBIAN
	ap_regex_t     *regexp;
#else
	regex_t        *regexp;
#endif /* DEBIAN */
#endif /* APR_MAJOR_VERSION */
	int		redir_status;	/* 301, 302, 303, 410, etc... */
}	alias_entry;

typedef struct {
	apr_array_header_t *redirects;
}	alias_dir_conf;

void * create_alias_dir_config(apr_pool_t * p, char *d);
void * merge_alias_dir_config(apr_pool_t * p, void *basev, void *overridesv);
int alias_matches(const char *uri, const char *alias_fakename);
const char * add_alias_internal(cmd_parms * cmd, void *dummy, const char *f, const char *r, int use_regex);
const char * add_alias(cmd_parms * cmd, void *dummy, const char *f, const char *r);
const char * add_alias_regex(cmd_parms * cmd, void *dummy, const char *f, const char *r);
const char * add_redirect_internal(cmd_parms * cmd, alias_dir_conf * dirconf,
		                                  const char *arg1, const char *arg2,
					  const char *arg3, int use_regex);
const char * add_redirect(cmd_parms * cmd, void *dirconf,
	                             const char *arg1, const char *arg2,
				 const char *arg3);
const char * add_redirect2(cmd_parms * cmd, void *dirconf,
				  const char *arg1, const char *arg2);
const char * add_redirect_regex(cmd_parms * cmd, void *dirconf,
		                               const char *arg1, const char *arg2,
				       const char *arg3);
int alias_matches(const char *uri, const char *alias_fakename);
char * try_alias_list(request_rec * r, apr_array_header_t * aliases,
			     int doesc, int *status);
int fixup_redir(request_rec * r);


#ifdef VH_DEBUG
#  define VH_AP_LOG_ERROR ap_log_error
#else
#  define VH_AP_LOG_ERROR my_ap_log_error
static void my_ap_log_error(void *none, ...)
{
  return;
}
#endif

#ifdef VH_DEBUG
#  define VH_AP_LOG_RERROR ap_log_rerror
#else
#  define VH_AP_LOG_RERROR my_ap_log_rerror
static void my_ap_log_rerror(void *none, ...)
{
  return;
}
#endif

#define VH_VHOST_INFOS_FOUND 1
#define VH_VHOST_INFOS_NOT_FOUND -1
#define VH_VHOST_INFOS_NOT_YET_REQUESTED 0
