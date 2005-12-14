/*
 * ==================================================================== The
 * Apache Software License, Version 1.1
 * 
 * Copyright (c) 2000 The Apache Software Foundation.  All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * 
 * 3. The end-user documentation included with the redistribution, if any, must
 * include the following acknowledgment: "This product includes software
 * developed by the Apache Software Foundation (http://www.apache.org/)."
 * Alternately, this acknowledgment may appear in the software itself, if and
 * wherever such third-party acknowledgments normally appear.
 * 
 * 4. The names "Apache" and "Apache Software Foundation" must not be used to
 * endorse or promote products derived from this software without prior
 * written permission. For written permission, please contact
 * apache@apache.org.
 * 
 * 5. Products derived from this software may not be called "Apache", nor may
 * "Apache" appear in their name, without prior written permission of the
 * Apache Software Foundation.
 * 
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESSED OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL
 * THE APACHE SOFTWARE FOUNDATION OR ITS CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 * ====================================================================
 * 
 * This software consists of voluntary contributions made by many individuals on
 * behalf of the Apache Software Foundation.  For more information on the
 * Apache Software Foundation, please see <http://www.apache.org/>.
 * 
 * Portions of this software are based upon public domain software originally
 * written at the National Center for Supercomputing Applications, University
 * of Illinois, Urbana-Champaign.
 */
/*
 * $Id: mod_vhs.c,v 1.82 2005-12-14 13:19:20 kiwi Exp $
 */

/*
 * Version of mod_vhs
 */
#define VH_VERSION	"mod_vhs/1.0.28"

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

/* Original Author: Michael Link <mlink@apache.org> */
/* mod_vhs author : Xavier Beaudouin <kiwi@oav.net> */
/* Some parts of this code has been stolen from mod_alias */

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

#include "ap_config_auto.h"

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
 * Libhome stuff
 */
#define	HOME_DONT_SUBSTITUTE_SYSTEM 1
/* libhome compatibility */
#define	DONT_SUBSTITUTE_SYSTEM 1
#include <home/hpwd.h>

/*
 * Include php support
 */
/*
 * #define HAVE_MOD_PHP_SUPPORT
 */

#ifdef HAVE_MOD_PHP_SUPPORT
#include <zend.h>
#include <zend_qsort.h>
#include <zend_API.h>
#include <zend_ini.h>
#include <zend_alloc.h>
#include <zend_operators.h>
#endif

/*
 * For mod_alias like operations
 */
#define AP_MAX_REG_MATCH 10

/*
 * Threads stuff
 */
#if APR_HAS_THREADS
static apr_thread_mutex_t *mutex = NULL;
#endif

/*
 * Let's start coding
 */
module AP_MODULE_DECLARE_DATA vhs_module;

/*
 * Configuration structure
 */
typedef struct {
	unsigned short int enable;	/* Enable the module */
	char           *libhome_tag;	/* Tags to be used by libhome */

	char           *path_prefix;	/* Prefix to add to path returned by
					 * libhome */
	char           *default_host;	/* Default host to redirect to */

	unsigned short int lamer_mode;	/* Lamer friendly mode */
	unsigned short int log_notfound;	/* Log request for vhost/path
						 * is not found */

#ifdef HAVE_MOD_PHP_SUPPORT
	char           *openbdir_path;	/* PHP open_basedir default path */

	unsigned short int safe_mode;	/* PHP Safe mode */
	unsigned short int open_basedir;	/* PHP open_basedir */
	unsigned short int append_basedir;	/* PHP append current
						 * directory to open_basedir */
	unsigned short int display_errors;	/* PHP display_error */
	unsigned short int phpopt_fromdb;	/* Get PHP options from
						 * libhome */
#endif				/* HAVE_MOD_PHP_SUPPORT */

	/*
	 * From mod_alias.c
	 */
	apr_array_header_t *aliases;
	apr_array_header_t *redirects;
	/*
	 * End of borrowing
	 */

}		vhs_config_rec;

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
#endif				/* DEBIAN */
#endif
	int		redir_status;	/* 301, 302, 303, 410, etc... */
}		alias_entry;

typedef struct {
	apr_array_header_t *redirects;
}		alias_dir_conf;
/*
 * End of borrowin
 */

/*
 * Apache per server config structure
 */
static void    *
vhs_create_server_config(apr_pool_t * p, server_rec * s)
{
	vhs_config_rec *vhr = (vhs_config_rec *) apr_pcalloc(p, sizeof(vhs_config_rec));

	/*
	 * Pre default the module is not enabled
	 */
	vhr->enable = 0;
	/*
	 * From mod_alias.c
	 */
	vhr->aliases = apr_array_make(p, 20, sizeof(alias_entry));
	vhr->redirects = apr_array_make(p, 20, sizeof(alias_entry));
	/*
	 * End of borrowing
	 */
	return (void *)vhr;
}

/*
 * Apache merge per server config structures
 */
static void    *
vhs_merge_server_config(apr_pool_t * p, void *parentv, void *childv)
{
	vhs_config_rec *parent = (vhs_config_rec *) parentv;
	vhs_config_rec *child = (vhs_config_rec *) childv;
	vhs_config_rec *conf = (vhs_config_rec *) apr_pcalloc(p, sizeof(vhs_config_rec));

	conf->enable = (child->enable ? child->enable : parent->enable);
	conf->libhome_tag = (child->libhome_tag ? child->libhome_tag : parent->libhome_tag);
	conf->path_prefix = (child->path_prefix ? child->path_prefix : parent->path_prefix);
	conf->default_host = (child->default_host ? child->default_host : parent->default_host);
	conf->lamer_mode = (child->lamer_mode ? child->lamer_mode : parent->lamer_mode);
	conf->log_notfound = (child->log_notfound ? child->log_notfound : parent->log_notfound);

#ifdef HAVE_MOD_PHP_SUPPORT
	conf->safe_mode = (child->safe_mode ? child->safe_mode : parent->safe_mode);
	conf->open_basedir = (child->open_basedir ? child->open_basedir : parent->open_basedir);
	conf->display_errors = (child->display_errors ? child->display_errors : parent->display_errors);
	conf->append_basedir = (child->append_basedir ? child->append_basedir : parent->append_basedir);
	conf->openbdir_path = (child->openbdir_path ? child->openbdir_path : parent->openbdir_path);
	conf->phpopt_fromdb = (child->phpopt_fromdb ? child->phpopt_fromdb : parent->phpopt_fromdb);
#endif				/* HAVE_MOD_PHP_SUPPORT */

	conf->aliases = apr_array_append(p, child->aliases, parent->aliases);
	conf->redirects = apr_array_append(p, child->redirects, parent->redirects);

	return conf;
}

/*
 * From mod_alias.c
 */
static void    *
create_alias_dir_config(apr_pool_t * p, char *d)
{
	alias_dir_conf *a = (alias_dir_conf *) apr_pcalloc(p, sizeof(alias_dir_conf));
	a->redirects = apr_array_make(p, 2, sizeof(alias_entry));
	return a;
}

static void    *
merge_alias_dir_config(apr_pool_t * p, void *basev, void *overridesv)
{
	alias_dir_conf *a = (alias_dir_conf *) apr_pcalloc(p, sizeof(alias_dir_conf));
	alias_dir_conf *base = (alias_dir_conf *) basev;
	alias_dir_conf *overrides = (alias_dir_conf *) overridesv;
	a->redirects = apr_array_append(p, overrides->redirects, base->redirects);
	return a;
}

/* need prototype for overlap check */
static int	alias_matches(const char *uri, const char *alias_fakename);

static const char *
add_alias_internal(cmd_parms * cmd, void *dummy,
		   const char *f, const char *r,
		   int use_regex)
{
	server_rec     *s = cmd->server;
	vhs_config_rec *conf = ap_get_module_config(s->module_config,
						    &vhs_module);
	alias_entry    *new = apr_array_push(conf->aliases);
	alias_entry    *entries = (alias_entry *) conf->aliases->elts;
	int		i;

	/* XX r can NOT be relative to DocumentRoot here... compat bug. */

	if (use_regex) {
#ifdef DEBIAN
		new->regexp = ap_pregcomp(cmd->pool, f, AP_REG_EXTENDED);
#else		
		new->regexp = ap_pregcomp(cmd->pool, f, REG_EXTENDED);
#endif /* DEBIAN */
		if (new->regexp == NULL)
			return "Regular expression could not be compiled.";
		new->real = r;
	} else {
		/*
		 * XXX This may be optimized, but we must know that new->real
		 * exists.  If so, we can dir merge later, trusing new->real
		 * and just canonicalizing the remainder.  Not till I finish
		 * cleaning out the old ap_canonical stuff first.
		 */
		new->real = r;
	}
	new->fake = f;
	new->handler = cmd->info;

	/*
	 * check for overlapping (Script)Alias directives and throw a warning
	 * if found one
	 */
	if (!use_regex) {
		for (i = 0; i < conf->aliases->nelts - 1; ++i) {
			alias_entry    *p = &entries[i];

			if ((!p->regexp && alias_matches(f, p->fake) > 0)
			    || (p->regexp && !ap_regexec(p->regexp, f, 0, NULL, 0))) {
				ap_log_error(APLOG_MARK, APLOG_WARNING, 0, cmd->server,
					     "The %s directive in %s at line %d will probably "
				"never match because it overlaps an earlier "
					     "%sAlias%s.",
				   cmd->cmd->name, cmd->directive->filename,
					     cmd->directive->line_num,
					     p->handler ? "Script" : "",
					     p->regexp ? "Match" : "");
				break;	/* one warning per alias should be
					 * sufficient */
			}
		}
	}
	return NULL;
}

static const char *
add_alias(cmd_parms * cmd, void *dummy, const char *f, const char *r)
{
	return add_alias_internal(cmd, dummy, f, r, 0);
}

static const char *
add_alias_regex(cmd_parms * cmd, void *dummy, const char *f, const char *r)
{
	return add_alias_internal(cmd, dummy, f, r, 1);
}

static const char *
add_redirect_internal(cmd_parms * cmd,
		      alias_dir_conf * dirconf,
		      const char *arg1, const char *arg2,
		      const char *arg3, int use_regex)
{
	alias_entry    *new;
	server_rec     *s = cmd->server;
	vhs_config_rec *serverconf = ap_get_module_config(s->module_config,
							  &vhs_module);
	int		status = (int)(long)cmd->info;
#if APR_MAJOR_VERSION > 0
	ap_regex_t     *r = NULL;
#else
#ifdef DEBIAN
	ap_regex_t     *r = NULL;
#else
	regex_t        *r = NULL;
#endif				/* DEBIAN */
#endif
	const char     *f = arg2;
	const char     *url = arg3;

	if (!strcasecmp(arg1, "gone"))
		status = HTTP_GONE;
	else if (!strcasecmp(arg1, "permanent"))
		status = HTTP_MOVED_PERMANENTLY;
	else if (!strcasecmp(arg1, "temp"))
		status = HTTP_MOVED_TEMPORARILY;
	else if (!strcasecmp(arg1, "seeother"))
		status = HTTP_SEE_OTHER;
	else if (apr_isdigit(*arg1))
		status = atoi(arg1);
	else {
		f = arg1;
		url = arg2;
	}

	if (use_regex) {
#ifdef DEBIAN		
		r = ap_pregcomp(cmd->pool, f, AP_REG_EXTENDED);
#else
		r = ap_pregcomp(cmd->pool, f, REG_EXTENDED);
#endif /* DEBIAN */
		if (r == NULL)
			return "Regular expression could not be compiled.";
	}
	if (ap_is_HTTP_REDIRECT(status)) {
		if (!url)
			return "URL to redirect to is missing";
		if (!use_regex && !ap_is_url(url))
			return "Redirect to non-URL";
	} else {
		if (url)
			return "Redirect URL not valid for this status";
	}

	if (cmd->path)
		new = apr_array_push(dirconf->redirects);
	else
		new = apr_array_push(serverconf->redirects);

	new->fake = f;
	new->real = url;
	new->regexp = r;
	new->redir_status = status;
	return NULL;
}


static const char *
add_redirect(cmd_parms * cmd, void *dirconf,
	     const char *arg1, const char *arg2,
	     const char *arg3)
{
	return add_redirect_internal(cmd, dirconf, arg1, arg2, arg3, 0);
}

static const char *
add_redirect2(cmd_parms * cmd, void *dirconf,
	      const char *arg1, const char *arg2)
{
	return add_redirect_internal(cmd, dirconf, arg1, arg2, NULL, 0);
}

static const char *
add_redirect_regex(cmd_parms * cmd, void *dirconf,
		   const char *arg1, const char *arg2,
		   const char *arg3)
{
	return add_redirect_internal(cmd, dirconf, arg1, arg2, arg3, 1);
}

static int 
alias_matches(const char *uri, const char *alias_fakename)
{
	const char     *aliasp = alias_fakename, *urip = uri;

	while (*aliasp) {
		if (*aliasp == '/') {
			/*
			 * any number of '/' in the alias matches any number
			 * in the supplied URI, but there must be at least
			 * one...
			 */
			if (*urip != '/')
				return 0;

			do {
				++aliasp;
			} while (*aliasp == '/');
			do {
				++urip;
			} while (*urip == '/');
		} else {
			/* Other characters are compared literally */
			if (*urip++ != *aliasp++)
				return 0;
		}
	}

	/* Check last alias path component matched all the way */

	if (aliasp[-1] != '/' && *urip != '\0' && *urip != '/')
		return 0;

	/*
	 * Return number of characters from URI which matched (may be greater
	 * than length of alias, since we may have matched doubled slashes)
	 */

	return urip - uri;
}

static char    *
try_alias_list(request_rec * r, apr_array_header_t * aliases,
	       int doesc, int *status)
{
	alias_entry    *entries = (alias_entry *) aliases->elts;
#ifdef DEBIAN	
	ap_regmatch_t	regm [AP_MAX_REG_MATCH];
#else	
	regmatch_t	regm [AP_MAX_REG_MATCH];
#endif  /* DEBIAN */
	char           *found = NULL;
	int		i;

	for (i = 0; i < aliases->nelts; ++i) {
		alias_entry    *p = &entries[i];
		int		l;

		if (p->regexp) {
			if (!ap_regexec(p->regexp, r->uri, AP_MAX_REG_MATCH, regm, 0)) {
				if (p->real) {
					found = ap_pregsub(r->pool, p->real, r->uri,
						    AP_MAX_REG_MATCH, regm);
					if (found && doesc) {
						apr_uri_t	uri;
						apr_uri_parse(r->pool, found, &uri);
						/*
						 * Do not escape the query
						 * string or fragment.
						 */
						found = apr_uri_unparse(r->pool, &uri,
						     APR_URI_UNP_OMITQUERY);
						found = ap_escape_uri(r->pool, found);
						if (uri.query) {
							found = apr_pstrcat(r->pool, found, "?",
							   uri.query, NULL);
						}
						if (uri.fragment) {
							found = apr_pstrcat(r->pool, found, "#",
							uri.fragment, NULL);
						}
					}
				} else {
					/* need something non-null */
					found = apr_pstrdup(r->pool, "");
				}
			}
		} else {
			l = alias_matches(r->uri, p->fake);

			if (l > 0) {
				if (doesc) {
					char           *escurl;
					escurl = ap_os_escape_path(r->pool, r->uri + l, 1);

					found = apr_pstrcat(r->pool, p->real, escurl, NULL);
				} else
					found = apr_pstrcat(r->pool, p->real, r->uri + l, NULL);
			}
		}

		if (found) {
			if (p->handler) {	/* Set handler, and leave a
						 * note for mod_cgi */
				r->handler = p->handler;
				apr_table_setn(r->notes, "alias-forced-type", r->handler);
			}
			/*
			 * XXX This is as SLOW as can be, next step, we
			 * optimize and merge to whatever part of the found
			 * path was already canonicalized.  After I finish
			 * eliminating os canonical. Better fail test for
			 * ap_server_root_relative needed here.
			 */
			if (!doesc) {
				found = ap_server_root_relative(r->pool, found);
			}
			if (found) {
				*status = p->redir_status;
			}
			return found;
		}
	}

	return NULL;
}

static int 
fixup_redir(request_rec * r)
{
	void           *dconf = r->per_dir_config;
	alias_dir_conf *dirconf =
	(alias_dir_conf *) ap_get_module_config(dconf, &vhs_module);
	char           *ret;
	int		status;

	/* It may have changed since last time, so try again */

	if ((ret = try_alias_list(r, dirconf->redirects, 1, &status)) != NULL) {
		if (ap_is_HTTP_REDIRECT(status)) {
			if (ret[0] == '/') {
				char           *orig_target = ret;

				ret = ap_construct_url(r->pool, ret, r);
				ap_log_rerror(APLOG_MARK, APLOG_DEBUG, 0, r,
				"incomplete redirection target of '%s' for "
					      "URI '%s' modified to '%s'",
					      orig_target, r->uri, ret);
			}
			if (!ap_is_url(ret)) {
				status = HTTP_INTERNAL_SERVER_ERROR;
				ap_log_rerror(APLOG_MARK, APLOG_ERR, 0, r,
					    "cannot redirect '%s' to '%s'; "
					      "target is not a valid absoluteURI or abs_path",
					      r->uri, ret);
			} else {
				/*
				 * append requested query only, if the config
				 * didn't supply its own.
				 */
				if (r->args && !ap_strchr(ret, '?')) {
					ret = apr_pstrcat(r->pool, ret, "?", r->args, NULL);
				}
				apr_table_setn(r->headers_out, "Location", ret);
			}
		}
		return status;
	}
	return DECLINED;
}

/*
 * End of borrowing
 */

/*
 * Set the fields inside the conf struct
 */
static const char *
set_field(cmd_parms * parms, void *mconfig, const char *arg)
{
	int		pos = (int)parms->info;
	vhs_config_rec *vhr = (vhs_config_rec *) ap_get_module_config(parms->server->module_config, &vhs_module);

	switch (pos) {
	case 0:
		vhr->libhome_tag = apr_pstrdup(parms->pool, arg);
		break;
	case 1:
		vhr->path_prefix = apr_pstrdup(parms->pool, arg);
		break;
	case 2:
		vhr->default_host = apr_pstrdup(parms->pool, arg);
		break;
#ifdef HAVE_MOD_PHP_SUPPORT
	case 3:
		vhr->openbdir_path = apr_pstrdup(parms->pool, arg);
		break;
#endif				/* HAVE_MOD_PHP_SUPPORT */
	}

	return NULL;
}

/*
 * To setting flags
 */
static const char *
set_flag(cmd_parms * parms, void *mconfig, int flag)
{
	int		pos = (int)parms->info;
	vhs_config_rec *vhr = (vhs_config_rec *) ap_get_module_config(parms->server->module_config, &vhs_module);

	switch (pos) {
	case 0:
		if (flag) {
			vhr->lamer_mode = 1;
		} else {
			vhr->lamer_mode = 0;
		}
		break;
#ifdef HAVE_MOD_PHP_SUPPORT
	case 1:
		if (flag) {
			vhr->safe_mode = 1;
		} else {
			vhr->safe_mode = 0;
		}
		break;
	case 2:
		if (flag) {
			vhr->open_basedir = 1;
		} else {
			vhr->open_basedir = 0;
		}
		break;
	case 3:
		if (flag) {
			vhr->phpopt_fromdb = 1;
		} else {
			vhr->phpopt_fromdb = 0;
		}
		break;
	case 4:
		if (flag) {
			vhr->display_errors = 1;
		} else {
			vhr->display_errors = 0;
		}
		break;
#endif				/* HAVE_MOD_PHP_SUPPORT */
	case 5:
		if (flag) {
			vhr->enable = 1;
		} else {
			vhr->enable = 0;
		}
		break;
#ifdef HAVE_MOD_PHP_SUPPORT
	case 6:
		if (flag) {
			vhr->append_basedir = 1;
		} else {
			vhr->append_basedir = 0;
		}
		break;
#endif				/* HAVE_MOD_PHP_SUPPORT */
	case 7:
		if (flag) {
			vhr->log_notfound = 1;
		} else {
			vhr->log_notfound = 0;
		}
		break;

	}

	return NULL;
}


static int 
vhs_init_handler(apr_pool_t * pconf, apr_pool_t * plog, apr_pool_t * ptemp, server_rec * s)
{
	ap_add_version_component(pconf, VH_VERSION);

	return OK;
}

/*
 * Used for redirect subsystem when a hostname is not found
 */
static int 
vhs_redirect_stuff(request_rec * r, vhs_config_rec * vhr)
{

	if (vhr->default_host) {
		apr_table_setn(r->headers_out, "Location", vhr->default_host);
#ifdef VH_DEBUG
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "redirect_stuff: using a redirect to %s for %s", vhr->default_host, r->hostname);
#endif				/* VH_DEBUG */
		return HTTP_MOVED_TEMPORARILY;
	}
	/* Failsafe */
#ifdef VH_DEBUG
	ap_log_error(APLOG_MARK, APLOG_ALERT, 0, r->server, "redirect_stuff: no host found (non HTTP/1.1 request, no default set) %s", r->hostname);
#endif				/* VH_DEBUG */
	return DECLINED;
}

/*
 * Get libhome the entries for hostname
 */
struct passwd  *
vhs_get_home_stuff(request_rec * r, vhs_config_rec * vhr, char *host)
{
	struct passwd  *p;
#if	APR_HAS_THREADS
	/* Thread stuff */
	apr_status_t	rv;
#endif
	/*
	 * libhome stuff is not thread safe so be nice and add a mutex
	 */
	/*
	 * Set the default libhome tag
	 */
#if APR_HAS_THREAD
	rv = apr_thread_mutex_lock(mutex);
#endif
	if (vhr->libhome_tag) {
		setpwtag(vhr->libhome_tag);
#ifdef VH_DEBUG
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "get_home_stuff: setpwtag set %s", vhr->libhome_tag);
#endif				/* VH_DEBUG */
	} else {
		setpwtag("mod_vhs");
#ifdef VH_DEBUG
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "get_home_stuff: setpwtag set default mod_vhs");
#endif				/* VH_DEBUG */
	}

	p = home_getpwnam(host);

#if APR_HAS_THREAD
	apr_thread_mutex_unlock(mutex);
#endif				/* APR_HAS_TREAD */

#ifdef VH_DEBUG
	if (p == NULL) {
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "get_home_stuff: libhome returned nothing");
	} else {
		if (p->pw_dir == NULL) {
			ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "get_home_stuff: libhome returned NULL path");
		} else {
			ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "get_home_stuff: libhome returned \"%s\" path", p->pw_dir);
		}
	}
#endif				/* VH_DEBUG */
	return p;
}

#ifdef HAVE_MOD_PHP_SUPPORT
/*
 * This function will configure on the fly the php like php.ini will do
 */
static void 
vhs_php_config(request_rec * r, vhs_config_rec * vhr, char *path, char *passwd)
{
	/*
	 * Some Basic PHP stuff, thank to Igor Popov module
	 */
	apr_table_set(r->subprocess_env, "PHP_DOCUMENT_ROOT", path);
	zend_alter_ini_entry("doc_root", sizeof("doc_root"), path, strlen(path), 4, 1);
	/*
	 * vhs_PHPsafe_mode support
	 */
	if (vhr->safe_mode) {
#ifdef VH_DEBUG
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP safe_mode engaged");
#endif				/* VH_DEBUG */
		zend_alter_ini_entry("safe_mode", 10, "1", 1, 4, 16);
#ifdef VH_DEBUG
	} else {
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP safe_mode inactive, defaulting to php.ini values");
#endif				/* VH_DEBUG */
	}

	/*
	 * vhs_PHPopen_baserdir    \ vhs_append_open_basedir |  support
	 * vhs_open_basedir_path   /
	 */
	if (vhr->open_basedir) {
		if (vhr->append_basedir && vhr->openbdir_path) {
			/*
			 * There is a default open_basedir path and
			 * configuration allow appending them
			 */
			char           *obasedir_path;

			if (vhr->path_prefix) {
				obasedir_path = apr_pstrcat(r->pool, vhr->openbdir_path, ":", vhr->path_prefix, path, NULL);
			} else {
				obasedir_path = apr_pstrcat(r->pool, vhr->openbdir_path, ":", path, NULL);
			}
			zend_alter_ini_entry("open_basedir", 13, obasedir_path, strlen(obasedir_path), 4, 16);
#ifdef VH_DEBUG
			ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP open_basedir set to %s (appending mode)", obasedir_path);
#endif				/* VH_DEBUG */
		} else {
			zend_alter_ini_entry("open_basedir", 13, path, strlen(path), 4, 16);
#ifdef VH_DEBUG
			ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP open_basedir set to %s", path);
#endif				/* VH_DEBUG */
		}
#ifdef VH_DEBUG
	} else {
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP open_basedir inactive defaulting to php.ini values");
#endif				/* VH_DEBUG */
	}

	/*
	 * vhs_PHPdisplay_errors support
	 */
	if (vhr->display_errors) {
#ifdef VH_DEBUG
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP display_errors engaged");
#endif				/* VH_DEBUG */
		zend_alter_ini_entry("display_errors", 10, "1", 1, 4, 16);
#ifdef VH_DEBUG
	} else {
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP display_errors inactive defaulting to php.ini values");
#endif				/* VH_DEBUG */
	}

	/*
	 * vhs_PHPopt_fromdb
	 */
	if (vhr->phpopt_fromdb) {
#ifdef VH_DEBUG
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP from DB engaged");
#endif				/* VH_DEBUG */
		char           *retval;
		char           *state;
		char           *myphpoptions;

		myphpoptions = apr_pstrdup(r->pool, passwd);
#ifdef VH_DEBUG
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: DB => %s", myphpoptions);
#endif				/* VH_DEBUG */

		if ((ap_strchr(myphpoptions, ';') != NULL) && (ap_strchr(myphpoptions, '=') != NULL)) {
			/* Getting values for PHP there so we can proceed */

			retval = apr_strtok(myphpoptions, ";", &state);
			while (retval != NULL) {
				char           *key = NULL;
				char           *val = NULL;
				char           *strtokstate = NULL;

				key = apr_strtok(retval, "=", &strtokstate);
				val = apr_strtok(NULL, "=", &strtokstate);
#ifdef VH_DEBUG
				ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: Zend PHP Stuff => %s => %s", key, val);
#endif				/* VH_DEBUG */
				zend_alter_ini_entry(key, strlen(key) + 1, val, strlen(val), 4, 16);
				retval = apr_strtok(NULL, ";", &state);
			}
		}
#ifdef VH_DEBUG
		else {
			ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: no PHP stuff found.");
		}
#endif				/* VH_DEBUG */
	}
}
#endif				/* HAVE_MOD_PHP_SUPPORT */

static int 
vhs_translate_name(request_rec * r)
{
	/* ap_conf_vector_t *sconf = r->server->module_config; */
	vhs_config_rec *vhr = (vhs_config_rec *) ap_get_module_config(r->server->module_config, &vhs_module);
	core_server_config *conf = (core_server_config *) ap_get_module_config(r->server->module_config, &core_module);

	const char     *host = 0;
	char           *path = NULL;
	/* mod_alias like functions */
	char           *ret = 0;
	int		status = 0;
	/* libhome */
	struct passwd  *p;
	char           *ptr = 0;

	/* If VHS is not enabled, then don't process request */
	if (!vhr->enable) {
		return DECLINED;
	}
	/* Handle alias stuff */
	if ((ret = try_alias_list(r, vhr->redirects, 1, &status)) != NULL) {
		if (ap_is_HTTP_REDIRECT(status)) {
			/* include QUERY_STRING if any */
			if (r->args) {
				ret = apr_pstrcat(r->pool, ret, "?", r->args, NULL);
			}
			apr_table_setn(r->headers_out, "Location", ret);
		}
		return status;
	}
	if ((ret = try_alias_list(r, vhr->aliases, 0, &status)) != NULL) {
		r->filename = ret;
		return OK;
	}
	/* Avoid handling request that don't start with '/' */
	if (r->uri[0] != '/' && r->uri[0] != '\0') {
		ap_log_error(APLOG_MARK, APLOG_ALERT, 0, r->server, "vhs_translate_name: declined %s no leading `/'", r->uri);
		return DECLINED;
	}
	if (!(host = apr_table_get(r->headers_in, "Host"))) {
		return vhs_redirect_stuff(r, vhr);
	}
	if (ptr = ap_strchr(host, ':')) {
		*ptr = '\0';
	}
#if 0
	/* If there is no Host: header given */
	if (r->hostname == NULL) {
		return vhs_redirect_stuff(r, vhr);
	} else {
		host = r->hostname;
	}
#endif

#ifdef VH_DEBUG
	ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: looking for %s", host);
#endif				/* VH_DEBUG */

	p = vhs_get_home_stuff(r, vhr, (char *)host);

	if (p != NULL) {
		/* Ok we have a path so we are sure we have a VHS host */
		path = p->pw_dir;
#ifdef VH_DEBUG
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: path found in database for %s is %s", host, path);
#endif				/* VH_DEBUG */

	} else {
		/*
		 * Trying to get lamer mode or not
		 */
		if (vhr->lamer_mode) {
#ifdef VH_DEBUG
			ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: Lamer friendly mode engaged");
#endif
			if ((strncasecmp(host, "www.", 4) == 0) && (strlen(host) > 4)) {
				char           *lhost;
				lhost = apr_pstrdup(r->pool, host + 5 - 1);
#ifdef VH_DEBUG
				ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: Found a lamer for %s -> %s", host, lhost);
#endif
				p = vhs_get_home_stuff(r, vhr, lhost);
				if (p != NULL) {
					path = p->pw_dir;
#ifdef VH_DEBUG
					ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: lamer for %s -> %s => %s", host, lhost, path);
#endif
				} else {
					if (vhr->log_notfound) {
						ap_log_error(APLOG_MARK, APLOG_NOTICE, 0, r->server, "vhs_translate_name: no host found in database for %s (lamer %s)", host, lhost);
					}
					return vhs_redirect_stuff(r, vhr);
				}
			}
		} else {
			if (vhr->log_notfound) {
				ap_log_error(APLOG_MARK, APLOG_NOTICE, 0, r->server, "vhs_translate_name: no host found in database for %s (lamer tested)", host);
			}
			return vhs_redirect_stuff(r, vhr);
		}
	}

	if (path == NULL) {
		if (vhr->log_notfound) {
			ap_log_error(APLOG_MARK, APLOG_NOTICE, 0, r->server, "vhs_translate_name: no path found found in database for %s (normal)", host);
		}
		return vhs_redirect_stuff(r, vhr);
	}
#ifdef WANT_VH_HOST
	apr_table_set(r->subprocess_env, "VH_HOST", host);
#endif				/* WANT_VH_HOST */
	apr_table_set(r->subprocess_env, "VH_GECOS", p->pw_gecos ? p->pw_gecos : "");
	/* Do we have handle vhr_Path_Prefix here ? */
	if (vhr->path_prefix) {
		apr_table_set(r->subprocess_env, "VH_PATH", apr_pstrcat(r->pool, vhr->path_prefix, path, NULL));
		apr_table_set(r->subprocess_env, "SERVER_ROOT", apr_pstrcat(r->pool, vhr->path_prefix, path, NULL));
	} else {
		apr_table_set(r->subprocess_env, "VH_PATH", path);
		apr_table_set(r->subprocess_env, "SERVER_ROOT", path);
	}

	if (p->pw_class) {
		r->server->server_admin = apr_pstrcat(r->pool, p->pw_class, NULL);
	} else {
		r->server->server_admin = apr_pstrcat(r->pool, "webmaster@", r->hostname, NULL);
	}
	r->server->server_hostname = apr_pstrcat(r->pool, host, NULL);
	r->parsed_uri.path = apr_pstrcat(r->pool, vhr->path_prefix ? vhr->path_prefix : "", path, r->parsed_uri.path, NULL);
	r->parsed_uri.hostname = r->server->server_hostname;
	r->parsed_uri.hostinfo = r->server->server_hostname;

	/* document_root */
	if (vhr->path_prefix) {
		conf->ap_document_root = apr_pstrcat(r->pool, vhr->path_prefix, path, NULL);
	} else {
		conf->ap_document_root = apr_pstrcat(r->pool, path, NULL);
	}

	/* if directory exist */
	if (!ap_is_directory(r->pool, path)) {
		ap_log_error(APLOG_MARK, APLOG_ALERT, 0, r->server,
		"vhs_translate_name: homedir '%s' is not dir at all", path);
		return DECLINED;
	}
	r->filename = apr_psprintf(r->pool, "%s%s%s", vhr->path_prefix ? vhr->path_prefix : "", path, r->uri);

	/* Avoid getting two // in filename */
	ap_no2slash(r->filename);

	ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: translated http://%s%s to file %s", host, r->uri, r->filename);

#ifdef HAVE_MOD_PHP_SUPPORT
	vhs_php_config(r, vhr, path, (char *)p->pw_passwd);
#endif				/* HAVE_MOD_PHP_SUPPORT */
	return OK;
}

/*
 * Stuff for register the module
 */
static const command_rec vhs_commands[] = {
	AP_INIT_FLAG("EnableVHS", set_flag, (void *)5, RSRC_CONF, "Enable VHS module"),
	AP_INIT_TAKE1("vhs_libhome_tag", set_field, (void *)0, RSRC_CONF, "Set libhome tag."),
	AP_INIT_TAKE1("vhs_Path_Prefix", set_field, (void *)1, RSRC_CONF, "Set path prefix."),
	AP_INIT_TAKE1("vhs_Default_Host", set_field, (void *)2, RSRC_CONF, "Set default host if HTTP/1.1 is not used."),
	AP_INIT_FLAG("vhs_Lamer", set_flag, (void *)0, RSRC_CONF, "Enable Lamer Friendly mode"),
	AP_INIT_FLAG("vhs_LogNotFound", set_flag, (void *)7, RSRC_CONF, "Log on error log when host or path is not found."),

#ifdef HAVE_MOD_PHP_SUPPORT
	AP_INIT_FLAG("vhs_PHPsafe_mode", set_flag, (void *)1, RSRC_CONF, "Enable PHP Safe Mode"),
	AP_INIT_FLAG("vhs_PHPopen_basedir", set_flag, (void *)2, RSRC_CONF, "Set PHP open_basedir to path"),
	AP_INIT_FLAG("vhs_PHPopt_fromdb", set_flag, (void *)3, RSRC_CONF, "Gets PHP options from db/libhome"),
	AP_INIT_FLAG("vhs_PHPdisplay_errors", set_flag, (void *)4, RSRC_CONF, "Enable PHP display_errors"),
	AP_INIT_FLAG("vhs_append_open_basedir", set_flag, (void *)6, RSRC_CONF, "Append homedir path to PHP open_basedir to vhs_open_basedir_path."),
	AP_INIT_TAKE1("vhs_open_basedir_path", set_field, (void *)3, RSRC_CONF, "The default PHP open_basedir path."),
#endif				/* HAVE_MOD_PHP_SUPPORT */

	AP_INIT_TAKE2("vhs_Alias", add_alias, NULL, RSRC_CONF, "a fakename and a realname"),
	AP_INIT_TAKE2("vhs_ScriptAlias", add_alias, "cgi-script", RSRC_CONF, "a fakename and a realname"),
	AP_INIT_TAKE23("vhs_Redirect", add_redirect, (void *)HTTP_MOVED_TEMPORARILY, OR_FILEINFO,
		   "an optional status, then document to be redirected and "
		       "destination URL"),
	AP_INIT_TAKE2("vhs_AliasMatch", add_alias_regex, NULL, RSRC_CONF, "a regular expression and a filename"),
	AP_INIT_TAKE2("vhs_ScriptAliasMatch", add_alias_regex, "cgi-script", RSRC_CONF, "a regular expression and a filename"),
	AP_INIT_TAKE23("vhs_RedirectMatch", add_redirect_regex, (void *)HTTP_MOVED_TEMPORARILY, OR_FILEINFO,
		       "an optional status, then a regular expression and "
		       "destination URL"),
	AP_INIT_TAKE2("vhs_RedirectTemp", add_redirect2, (void *)HTTP_MOVED_TEMPORARILY, OR_FILEINFO,
		   "a document to be redirected, then the destination URL"),
	AP_INIT_TAKE2("vhs_RedirectPermanent", add_redirect2, (void *)HTTP_MOVED_PERMANENTLY, OR_FILEINFO,
		   "a document to be redirected, then the destination URL"),
	{NULL}
};

static void 
register_hooks(apr_pool_t * p)
{
	/* Modules that have to be loaded before mod_vhs */
	static const char *const aszPre[] =
	{"mod_userdir.c", "mod_vhost_alias.c", NULL};
	/* Modules that have to be loaded after mod_vhs */
	static const char *const aszSucc[] =
	{"mod_php.c", NULL};
	ap_hook_post_config(vhs_init_handler, NULL, NULL, APR_HOOK_MIDDLE);
	ap_hook_translate_name(vhs_translate_name, aszPre, aszSucc, APR_HOOK_FIRST);
	ap_hook_fixups(fixup_redir, NULL, NULL, APR_HOOK_MIDDLE);
#if APR_HAS_THREADS
	apr_status_t	ret;
	ret = apr_thread_mutex_create(&mutex, APR_THREAD_MUTEX_DEFAULT, p);
	apr_pool_cleanup_register(p, mutex, (void *)apr_thread_mutex_destroy,
				  apr_pool_cleanup_null);
#endif
}

AP_DECLARE_DATA module vhs_module = {
	STANDARD20_MODULE_STUFF,
	create_alias_dir_config,/* create per-directory config structure */
	merge_alias_dir_config,	/* merge per-directory config structures */
	vhs_create_server_config,	/* create per-server config structure */
	vhs_merge_server_config,/* merge per-server config structures */
	vhs_commands,		/* command apr_table_t */
	register_hooks		/* register hooks */
};
