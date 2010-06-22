/*
 * ====================================================================
 * The Apache Software License, Version 1.1
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
 * $Id: mod_vhs.c,v 1.108 2009-05-22 20:27:29 kiwi Exp $
 */
/*
 * Brief instructions to use mod_vhs with apache2-mpm-itk support.
 * - To compile mod_vhs with apache2-mpm-itk support add "-DHAVE_MPM_ITK_SUPPORT" to your "CFLAGS".
 * - To enable apache2-mpm-itk support set "vhs_itk_enable On" in your <VirtualHost> section.
 * - Pass the uidNumber and gidNumber to the uid and gid directive in your home.conf like the example in the README file.
 */

#include "mod_vhs.h"

#ifdef HAVE_MOD_DBD_SUPPORT
static ap_dbd_t *(*vhost_dbd_acquire_fn)(request_rec*) = NULL;
static void (*vhost_dbd_prepare_fn)(server_rec*, const char*, const char*) = NULL;
#define VH_KEY "mod_vhs"
#endif /* HAVE_MOD_DBD_SUPPORT */

/*
 * Let's start coding
 */
module AP_MODULE_DECLARE_DATA vhs_module;

/*
 * For some Ugly reason this has to be here.
 * Don't know why... Maybe some brain damage inside core ?
 * DON'T EVEN THINK to move that into mod_vhs.h
 */
#ifdef HAVE_LDAP_SUPPORT
/* TODO: make KazarPerson stuff */
char *ldap_attributes[] = { "apacheServerName", "apacheDocumentRoot", "apacheScriptAlias", "apacheSuexecUid", "apacheSuexecGid", "apacheServerAdmin","apachePhpopts","associatedDomain", 0 };

/* TODO: cca marche ? */
static APR_OPTIONAL_FN_TYPE(uldap_connection_close) *util_ldap_connection_close;
static APR_OPTIONAL_FN_TYPE(uldap_connection_find) *util_ldap_connection_find;
static APR_OPTIONAL_FN_TYPE(uldap_cache_comparedn) *util_ldap_cache_comparedn;
static APR_OPTIONAL_FN_TYPE(uldap_cache_compare) *util_ldap_cache_compare;
static APR_OPTIONAL_FN_TYPE(uldap_cache_checkuserid) *util_ldap_cache_checkuserid;
static APR_OPTIONAL_FN_TYPE(uldap_cache_getuserdn) *util_ldap_cache_getuserdn;
static APR_OPTIONAL_FN_TYPE(uldap_ssl_supported) *util_ldap_ssl_supported;

static void ImportULDAPOptFn(void)
{
    util_ldap_connection_close  = APR_RETRIEVE_OPTIONAL_FN(uldap_connection_close);
    util_ldap_connection_find   = APR_RETRIEVE_OPTIONAL_FN(uldap_connection_find);
    util_ldap_cache_comparedn   = APR_RETRIEVE_OPTIONAL_FN(uldap_cache_comparedn);
    util_ldap_cache_compare     = APR_RETRIEVE_OPTIONAL_FN(uldap_cache_compare);
    util_ldap_cache_checkuserid = APR_RETRIEVE_OPTIONAL_FN(uldap_cache_checkuserid);
    util_ldap_cache_getuserdn   = APR_RETRIEVE_OPTIONAL_FN(uldap_cache_getuserdn);
    util_ldap_ssl_supported     = APR_RETRIEVE_OPTIONAL_FN(uldap_ssl_supported);
}
#endif /* HAVE_LDAP_SUPPORT */
/*
 * END OF UGLY REASON 
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
	vhr->enable 		= 0;

	/*
	 * From mod_alias.c
	 */
	vhr->aliases		= apr_array_make(p, 20, sizeof(alias_entry));
	vhr->redirects		= apr_array_make(p, 20, sizeof(alias_entry));

#ifdef HAVE_LDAP_SUPPORT
	vhr->ldap_binddn	= NULL;
	vhr->ldap_bindpw	= NULL;
	vhr->ldap_have_url	= 0;
	vhr->ldap_have_deref	= 0;
	vhr->ldap_deref		= always;
#endif /* HAVE_LDAP_SUPPORT */

#ifdef HAVE_MPM_ITK_SUPPORT
	vhr->itk_enable 	= 0;
#endif /* HAVE_MPM_ITK_SUPPORT */

#ifdef HAVE_MOD_DBD_SUPPORT
	vhr->dbd_table_name	= NULL;
	vhr->query		= NULL;
	vhr->label		= NULL;
#endif

	return (void *)vhr;
}

/*
 * Apache merge per server config structures
 */
static void    *
vhs_merge_server_config(apr_pool_t * p, void *parentv, void *childv)
{
	vhs_config_rec *parent	= (vhs_config_rec *) parentv;
	vhs_config_rec *child	= (vhs_config_rec *) childv;
	vhs_config_rec *conf	= (vhs_config_rec *) apr_pcalloc(p, sizeof(vhs_config_rec));

	conf->enable 		= (child->enable ? child->enable : parent->enable);
	conf->path_prefix 	= (child->path_prefix ? child->path_prefix : parent->path_prefix);
	conf->default_host 	= (child->default_host ? child->default_host : parent->default_host);
	conf->lamer_mode 	= (child->lamer_mode ? child->lamer_mode : parent->lamer_mode);
	conf->log_notfound 	= (child->log_notfound ? child->log_notfound : parent->log_notfound);

#ifdef HAVE_MOD_PHP_SUPPORT
	conf->safe_mode 	= (child->safe_mode ? child->safe_mode : parent->safe_mode);
	conf->open_basedir 	= (child->open_basedir ? child->open_basedir : parent->open_basedir);
	conf->display_errors 	= (child->display_errors ? child->display_errors : parent->display_errors);
	conf->append_basedir 	= (child->append_basedir ? child->append_basedir : parent->append_basedir);
	conf->openbdir_path 	= (child->openbdir_path ? child->openbdir_path : parent->openbdir_path);
	conf->phpopt_fromdb 	= (child->phpopt_fromdb ? child->phpopt_fromdb : parent->phpopt_fromdb);
#endif /* HAVE_MOD_PHP_SUPPORT */

#ifdef HAVE_MOD_SUPHP_SUPPORT
    conf->suphp_config_path = (child->suphp_config_path ? child->suphp_config_path : parent->suphp_config_path);
#endif /* HAVE_MOD_SUPHP_SUPPORT */

#ifdef HAVE_MPM_ITK_SUPPORT
	conf->itk_enable = (child->itk_enable ? child->itk_enable : parent->itk_enable);
#endif /* HAVE_MPM_ITK_SUPPORT */

#ifdef HAVE_LDAP_SUPPORT
    if (child->ldap_have_url) {
    	conf->ldap_have_url = child->ldap_have_url;
    	conf->ldap_url      = child->ldap_url;
    	conf->ldap_host     = child->ldap_host;
    	conf->ldap_port     = child->ldap_port;
    	conf->ldap_basedn   = child->ldap_basedn;
    	conf->ldap_scope    = child->ldap_scope;
    	conf->ldap_filter   = child->ldap_filter;
    	conf->ldap_secure   = child->ldap_secure;
    } else {
		conf->ldap_have_url = parent->ldap_have_url;
		conf->ldap_url      = parent->ldap_url;
		conf->ldap_host     = parent->ldap_host;
		conf->ldap_port     = parent->ldap_port;
		conf->ldap_basedn   = parent->ldap_basedn;
		conf->ldap_scope    = parent->ldap_scope;
		conf->ldap_filter   = parent->ldap_filter;
		conf->ldap_secure   = parent->ldap_secure;
    }
    if (child->ldap_have_deref) {
    	conf->ldap_have_deref = child->ldap_have_deref;
    	conf->ldap_deref      = child->ldap_deref;
    } else {
    	conf->ldap_have_deref = parent->ldap_have_deref;
    	conf->ldap_deref      = parent->ldap_deref;
    }

    conf->ldap_binddn = (child->ldap_binddn ? child->ldap_binddn : parent->ldap_binddn);
    conf->ldap_bindpw = (child->ldap_bindpw ? child->ldap_bindpw : parent->ldap_bindpw);
#endif /* HAVE_LDAP_SUPPORT */
	
#ifdef HAVE_MOD_DBD_SUPPORT
	conf->dbd_table_name = (child->dbd_table_name ? child->dbd_table_name : parent->dbd_table_name);
	conf->query	     = (child->query ? child->query : parent->query);
	conf->label	      = (child->label ? child->label : parent->label);
#endif /* HAVE_MOD_DBD_SUPPORT */

	conf->aliases   = apr_array_append(p, child->aliases, parent->aliases);
	conf->redirects = apr_array_append(p, child->redirects, parent->redirects);

	return conf;
}


/*
 * Set the fields inside the conf struct
 */
static const char * set_field(cmd_parms * parms, void *mconfig, const char *arg)
{
	int		pos = (int) parms->info;

#ifdef HAVE_MOD_DBD_SUPPORT
	static unsigned int label_num = 0;
#endif
	vhs_config_rec *vhr = (vhs_config_rec *) ap_get_module_config(parms->server->module_config, &vhs_module);

	switch (pos) {
#ifdef HAVE_MOD_DBD_SUPPORT
	case 0:
	  vhr->dbd_table_name = apr_pstrdup(parms->pool, arg);
	  vhr->query = apr_pstrdup(parms->pool, apr_pstrcat(parms->pool, "SELECT ServerName, ServerAdmin, DocumentRoot, suexec_uid, suexec_gid, php_env, associateddomain, isalias FROM ", vhr->dbd_table_name, " WHERE ServerName = %s AND active = 'yes'", NULL));

	  /*	  VH_AP_LOG_ERROR (APLOG_MARK, APLOG_DEBUG, 0, parms->server,
		       "set_field: Query='%s' arg='%s' for server: '%s' line: %d",
		       vhr->query, arg, parms->server->defn_name, parms->server->defn_line_number );
	  */
	  /* code repris de mod_authn_dbd pour preparer la connection et requete a la base. */
	  if (vhost_dbd_prepare_fn == NULL) {
	    vhost_dbd_prepare_fn = APR_RETRIEVE_OPTIONAL_FN(ap_dbd_prepare);
	    if (vhost_dbd_prepare_fn == NULL) {
	      return "You must load mod_dbd to enable VhostDBD functions";
	    }
	    vhost_dbd_acquire_fn = APR_RETRIEVE_OPTIONAL_FN(ap_dbd_acquire);
	  }
	  vhr->label = apr_psprintf(parms->pool, "vhost_vhs_%d", ++label_num);

	  vhost_dbd_prepare_fn(parms->server, vhr->query, vhr->label);
	  break;
#endif /* HAVE_MOD_DBD_SUPPORT */
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
#endif /* HAVE_MOD_PHP_SUPPORT */

#ifdef HAVE_LDAP_SUPPORT
	case 4:
		vhr->ldap_binddn = apr_pstrdup(parms->pool, arg);
		break;

	case 5:
		vhr->ldap_bindpw = apr_pstrdup(parms->pool, arg);
		break;

	case 6:
	    if (strcmp(arg, "never") == 0 || strcasecmp(arg, "off") == 0) {
	        vhr->ldap_deref = never;
	        vhr->ldap_have_deref = 1;
	    }
	    else if (strcmp(arg, "searching") == 0) {
	        vhr->ldap_deref = searching;
	        vhr->ldap_have_deref = 1;
	    }
	    else if (strcmp(arg, "finding") == 0) {
	        vhr->ldap_deref = finding;
	        vhr->ldap_have_deref = 1;
	    }
	    else if (strcmp(arg, "always") == 0 || strcasecmp(arg, "on") == 0) {
	        vhr->ldap_deref = always;
	        vhr->ldap_have_deref = 1;
	    }
	    else {
	        return "Unrecognized value for vhs_DAPAliasDereference directive";
	    }
		break;
#endif /* HAVE_LDAP_SUPPORT */

#ifdef HAVE_MOD_SUPHP_SUPPORT
	case 10:
		vhr->suphp_config_path = apr_pstrdup(parms->pool, arg);
		break;
#endif /* HAVE_MOD_SUPHP_SUPPRT */
	}

	return NULL;
}

/*
 * LDAP Parse URL :
 * Use the ldap url parsing routines to break up the LDAP  URL into
 * host and port.
 * Is out of set_field because it is very big stuff
 */
#ifdef HAVE_LDAP_SUPPORT
static const char *mod_vhs_ldap_parse_url(cmd_parms *cmd, void *dummy, const char *url)
{
    int result;
    apr_ldap_url_desc_t *urld;
    apr_ldap_err_t	*ldap_result;

    vhs_config_rec *vhr = (vhs_config_rec *) ap_get_module_config(cmd->server->module_config, &vhs_module);

    VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG|APLOG_NOERRNO, 0,
	         cmd->server, "ldap url parse: `%s'",
	         url);

    result = apr_ldap_url_parse(cmd->pool, url, &(urld), &(ldap_result));
    if (result != APR_SUCCESS) {
	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, cmd->server, "ldap url not parsed : %s.", ldap_result->reason);
    	return ldap_result->reason;
    }
    vhr->ldap_url = apr_pstrdup(cmd->pool, url);

    VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG|APLOG_NOERRNO, 0,
	         cmd->server, "ldap url parse: Host: %s", urld->lud_host);
    VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG|APLOG_NOERRNO, 0,
	         cmd->server, "ldap url parse: Port: %d", urld->lud_port);
    VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG|APLOG_NOERRNO, 0,
	         cmd->server, "ldap url parse: DN: %s", urld->lud_dn);
    VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG|APLOG_NOERRNO, 0,
	         cmd->server, "ldap url parse: attrib: %s", urld->lud_attrs? urld->lud_attrs[0] : "(null)");
    VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG|APLOG_NOERRNO, 0,
	         cmd->server, "ldap url parse: scope: %s",
	         (urld->lud_scope == LDAP_SCOPE_SUBTREE? "subtree" :
		 urld->lud_scope == LDAP_SCOPE_BASE? "base" :
		 urld->lud_scope == LDAP_SCOPE_ONELEVEL? "onelevel" : "unknown"));
    VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG|APLOG_NOERRNO, 0,
	         cmd->server, "ldap url parse: filter: %s", urld->lud_filter);

    /* Set all the values, or at least some sane defaults */
    if (vhr->ldap_host) {
        char *p = apr_palloc(cmd->pool, ap_strlen(vhr->ldap_host) + ap_strlen(urld->lud_host) + 2);
        strcpy(p, urld->lud_host);
        strcat(p, " ");
        strcat(p, vhr->ldap_host);
        vhr->ldap_host = p;
    }
    else {
        vhr->ldap_host = urld->lud_host? apr_pstrdup(cmd->pool, urld->lud_host) : "localhost";
    }
    vhr->ldap_basedn = urld->lud_dn? apr_pstrdup(cmd->pool, urld->lud_dn) : "";

    vhr->ldap_scope = urld->lud_scope == LDAP_SCOPE_ONELEVEL ? LDAP_SCOPE_ONELEVEL : LDAP_SCOPE_SUBTREE;

    if (urld->lud_filter) {
        if (urld->lud_filter[0] == '(') {
            /*
             * Get rid of the surrounding parens; later on when generating the
             * filter, they'll be put back.
             */
            vhr->ldap_filter = apr_pstrdup(cmd->pool, urld->lud_filter+1);
            vhr->ldap_filter[ap_strlen(vhr->ldap_filter)-1] = '\0';
        }
        else {
            vhr->ldap_filter = apr_pstrdup(cmd->pool, urld->lud_filter);
        }
    }
    else {
        vhr->ldap_filter = "objectClass=apacheConfig";
    }

    /*
     *  "ldaps" indicates secure ldap connections desired
     */
    if (strncasecmp(url, "ldaps", 5) == 0)
    {
        vhr->ldap_secure = 1;
        vhr->ldap_port = urld->lud_port? urld->lud_port : LDAPS_PORT;

        VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG|APLOG_NOERRNO, 0, cmd->server,
                     "LDAP: using SSL connections");
    }
    else
    {
        vhr->ldap_secure = 0;
        vhr->ldap_port = urld->lud_port? urld->lud_port : LDAP_PORT;
        VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG|APLOG_NOERRNO, 0, cmd->server,
                     "LDAP: not using SSL connections");
    }

    vhr->ldap_have_url = 1;
    return NULL;
}
#endif /* HAVE_LDAP_SUPPORT */

/*
 * To setting flags
 */
static const char * set_flag(cmd_parms * parms, void *mconfig, int flag)
{
	int		pos = (int)parms->info;
	vhs_config_rec *vhr = (vhs_config_rec *) ap_get_module_config(parms->server->module_config, &vhs_module);

	/*	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, parms->server,
		     "set_flag:Flag='%d' for server: '%s' for pos='%d' line: %d",
		     flag, parms->server->defn_name, pos, parms->server->defn_line_number ); */
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
#ifdef HAVE_MPM_ITK_SUPPORT
	case 8:
		if (flag) {
			vhr->itk_enable = 1;
		} else {
			vhr->itk_enable = 0;
		}
		break;
#endif /* HAVE_MPM_ITK_SUPPORT  */
	}
	return NULL;
}

#ifdef HAVE_MPM_ITK_SUPPORT
typedef struct {
	uid_t uid;
	gid_t gid;
	char   *username;
        int nice_value;
} itk_conf;
#endif /* HAVE_MPM_ITK_SUPPORT  */

static int vhs_init_handler(apr_pool_t * pconf, apr_pool_t * plog, apr_pool_t * ptemp, server_rec * s)
{
#ifdef HAVE_LDAP_SUPPORT
	/* make sure that mod_ldap (util_ldap) is loaded */
	if (ap_find_linked_module("util_ldap.c") == NULL) {
		ap_log_error(APLOG_MARK, APLOG_ERR|APLOG_NOERRNO, 0, s,
			"Module mod_ldap missing. Mod_ldap (aka. util_ldap) "
			"must be loaded in order for mod_vhs to function properly");
		return HTTP_INTERNAL_SERVER_ERROR;
	}
#endif /* HAVE_LDAP_SUPPORT */

	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, s, "loading version %s.", VH_VERSION);

	ap_add_version_component(pconf, VH_VERSION);

#ifdef HAVE_MPM_ITK_SUPPORT
	unsigned short int itk_enable = 1;
	server_rec *sp;

	module *mpm_itk_module = ap_find_linked_module("itk.c");
	if (mpm_itk_module == NULL) {
		ap_log_error(APLOG_MARK, APLOG_ERR, 0, s, "vhs_init_handler: itk.c is not loaded");
		itk_enable = 0;
	}

	for (sp = s; sp; sp = sp->next) {
		vhs_config_rec *vhr = (vhs_config_rec *) ap_get_module_config(sp->module_config, &vhs_module);

		if (vhr->itk_enable) {
			if (!itk_enable) {
				vhr->itk_enable = 0;
			} else {
				itk_conf *cfg = (itk_conf *) ap_get_module_config(sp->module_config, mpm_itk_module);
				vhr->itk_defuid = cfg->uid;
				vhr->itk_defgid = cfg->gid;
				vhr->itk_defusername = cfg->username;

				ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, sp, "vhs_init_handler: itk uid='%d' itk gid='%d' "/*itk username='%s'*/, cfg->uid, cfg->gid/*, cfg->username */);
			}
		}       
	}
#endif /* HAVE_MPM_ITK_SUPPORT  */

	return OK;
}

/*
 * Used for redirect subsystem when a hostname is not found
 */
static int vhs_redirect_stuff(request_rec * r, vhs_config_rec * vhr)
{
	if (vhr->default_host) {
		apr_table_setn(r->headers_out, "Location", vhr->default_host);
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "redirect_stuff: using a redirect to %s for %s", vhr->default_host, r->hostname);
		return HTTP_MOVED_TEMPORARILY;
	}
	/* Failsafe */
	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_ALERT, 0, r->server, "redirect_stuff: no host found (non HTTP/1.1 request, no default set) %s", r->hostname);
	return DECLINED;
}

#ifdef HAVE_MOD_DBD_SUPPORT 
/*
 *  Get the stuff from Mod DBD
 */
int getmoddbdhome(request_rec *r, vhs_config_rec *vhr, const char *hostname, mod_vhs_request_t *reqc)
{
	const char     		*host = 0;

	apr_status_t rv = 0;
	ap_dbd_t *dbd;
	apr_dbd_prepared_t *statement;
	apr_dbd_results_t *res = NULL;
	apr_dbd_row_t *row = NULL;

	VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome --------------------------------------------");

	/*	mod_vhs_request_t *reqc;
    	reqc = (mod_vhs_request_t *)apr_pcalloc(r->pool, sizeof(mod_vhs_request_t));
    	ap_set_module_config(r->request_config, &vhs_module, reqc);
	*/
        if (!vhr->enable) {
	  return DECLINED;
        }

	if (vhr->query == NULL) {
	  ap_log_error(APLOG_MARK, APLOG_ERR, 0, r->server, "getmoddbdhome: No VhostDBDQuery has been specified");
	  return DECLINED;
	}
	/*    host = r->hostname; */
	  host = ap_get_server_name(r);
	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome: search for vhost: '%s'", host);

	  dbd = vhost_dbd_acquire_fn(r);
	  if (dbd == NULL) {
	    ap_log_rerror(APLOG_MARK, APLOG_ERR, 0, r, "getmoddbdhome: Failed to acquire database connection to look up host '%s'", host);
	    return DECLINED;
	  }

	  statement = apr_hash_get(dbd->prepared, vhr->label, APR_HASH_KEY_STRING);
	  if (statement == NULL) {
	    ap_log_rerror(APLOG_MARK, APLOG_ERR, 0, r, "getmoddbdhome: A prepared statement could not be found for "
			  "VhostDBDQuery with the key '%s'", vhr->label);
	    return DECLINED;
	  }

	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome: query='%s'", vhr->query);
	  /* execute the query of a statement and parameter host */
	  if (apr_dbd_pvselect(dbd->driver, r->pool, dbd->handle, &res, statement, 0, host, NULL) != 0) {
	    ap_log_rerror(APLOG_MARK, APLOG_ERR, 0, r, "getmoddbdhome: Query execution error looking up '%s' in database", host);
	    return DECLINED;
	  }

	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome: apr_dbd_get_row return : %d", rv);
	  if ((rv = apr_dbd_get_row(dbd->driver, r->pool, res, &row, -1))) {
	    ap_log_rerror(APLOG_MARK, APLOG_ERR, 0, r, "No found results for host '%s' in database", host);
	    return DECLINED;
	  }
	  /* requete dbd ok */
	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome: dbd is ok");


	  /* "SELECT ServerName, ServerAdmin, DocumentRoot, suexec_uid, suexec_gid, php_env, associateddomain, isalias FROM ", vhr->dbd_table_name, " WHERE ServerName = %s AND active = 'yes'" */

	  /* servername */
	  reqc->name = apr_pstrdup(r->pool, apr_dbd_get_entry(dbd->driver, row, 0));
	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome: server_name='%s'", reqc->name);

	  /* email admin server */
	  reqc->admin = apr_pstrdup(r->pool, apr_dbd_get_entry(dbd->driver, row, 1));
	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome: server_admin='%s'", reqc->admin);

	  /* document root */
	  reqc->docroot = apr_pstrdup(r->pool, apr_dbd_get_entry(dbd->driver, row, 2));
	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome: docroot=%s", reqc->docroot);

	  /* suexec UID */
	  reqc->uid = apr_pstrdup(r->pool, apr_dbd_get_entry(dbd->driver, row, 3));
	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome: uid=%s", reqc->uid);

	  /* suexec GID */
	  reqc->gid = apr_pstrdup(r->pool, apr_dbd_get_entry(dbd->driver, row, 4));
	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome: gid=%s", reqc->gid);

	  /* phpopt_fromdb / options PHP */
	  reqc->phpoptions = apr_pstrdup(r->pool, apr_dbd_get_entry(dbd->driver, row, 5));
	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome: phpoptions=%s", reqc->phpoptions);

	  /* associate domain */
	  reqc->associateddomain = apr_pstrdup(r->pool, apr_dbd_get_entry(dbd->driver, row, 6));
	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "getmoddbdhome: associateddomain=%s", reqc->associateddomain);


	  /* the vhost has been found, set vhost_found to VH_VHOST_INFOS_FOUND */
	  reqc->vhost_found = VH_VHOST_INFOS_FOUND;

	  apr_pool_userdata_set(reqc, VH_KEY, apr_pool_cleanup_null, r->pool);

	  return OK;
}
#endif /* HAVE_MOD_DBD_SUPPORT */

#ifdef HAVE_MPM_ITK_SUPPORT
/*
 * This function will configure MPM-ITK
 */
static int vhs_itk_post_read(request_rec *r)
{
  //	struct passwd  *p;

	uid_t libhome_uid;
	gid_t libhome_gid;
	int vhost_found_by_request = DECLINED;
	
  	vhs_config_rec *vhr = (vhs_config_rec *) ap_get_module_config(r->server->module_config, &vhs_module);
	VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "vhs_itk_post_read: BEGIN ***");

	mod_vhs_request_t *reqc;

	reqc = ap_get_module_config(r->request_config, &vhs_module);
	if (reqc)
	  return OK;

	reqc = ap_get_module_config(r->request_config, &vhs_module);	
	if (!reqc)
	  {
		reqc = (mod_vhs_request_t *)apr_pcalloc(r->pool, sizeof(mod_vhs_request_t));
		reqc->vhost_found = VH_VHOST_INFOS_NOT_YET_REQUESTED;
		ap_set_module_config(r->request_config, &vhs_module, reqc);
		/* VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: variable reqc does not already exists.... creating ! pid=%d request_rec=%d @request_config='%d'", getpid(), r, &(r->request_config)); */
	  }

#ifdef HAVE_LDAP_SUPPORT
		vhost_found_by_request = getldaphome(r, vhr, r->hostname, reqc);
#endif
#ifdef HAVE_MOD_DBD_SUPPORT
		vhost_found_by_request = getmoddbdhome(r, vhr, r->hostname, reqc);
#endif
	if (vhost_found_by_request == OK)
	  {
		libhome_uid = atoi(reqc->uid);
		libhome_gid = atoi(reqc->gid);
	  }
	else
	  {
		if (vhr->lamer_mode) 
		  {
			VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_itk_post_read: Lamer friendly mode engaged");
			if ((strncasecmp(r->hostname, "www.", 4) == 0) && (ap_strlen(r->hostname) > 4)) {
				char           *lhost;
				lhost = apr_pstrdup(r->pool, r->hostname + 5 - 1);
			  VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_itk_post_read: Found a lamer for %s -> %s", r->hostname, lhost);
#ifdef HAVE_LDAP_SUPPORT
			  vhost_found_by_request = getldaphome(r, vhr, lhost, reqc);
#endif
#ifdef HAVE_MOD_DBD_SUPPORT
			  vhost_found_by_request = getmoddbdhome(r, vhr, lhost, reqc);
#endif
			  if (vhost_found_by_request == OK) {
				libhome_uid = atoi(reqc->uid);
				libhome_gid = atoi(reqc->gid);
				VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_itk_post_read: lamer for %s -> %s has itk uid='%d' itk gid='%d'", r->hostname, lhost, libhome_uid, libhome_gid);
				} else {
					libhome_uid = vhr->itk_defuid;
					libhome_gid = vhr->itk_defgid;
				VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_itk_post_read: no lamer found for %s set default itk uid='%d' itk gid='%d'", r->hostname, libhome_uid, libhome_gid);
				}
			} else { /* if ((strncasecmp(r->hostname, "www.", 4) == 0) && (strlen(r->hostname) > 4)) */
				libhome_uid = vhr->itk_defuid;
				libhome_gid = vhr->itk_defgid;
			  VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_itk_post_read: no lamer found for %s set default itk uid='%d' itk gid='%d'", r->hostname, libhome_uid, libhome_gid);
			}
		  } else { /* if (vhr->lamer_mode) */
			libhome_uid = vhr->itk_defuid;
			libhome_gid = vhr->itk_defgid;
		}
	}

       /* If ITK support is not enabled, then don't process request */
	if (vhr->itk_enable) {
       	module *mpm_itk_module = ap_find_linked_module("itk.c");
       
		if (mpm_itk_module == NULL) {
			ap_log_error(APLOG_MARK, APLOG_ERR, 0, r->server, "vhs_itk_post_read: itk.c is not loaded");
			return HTTP_INTERNAL_SERVER_ERROR;
		}
	  itk_conf *cfg = (itk_conf *) ap_get_module_config(r->per_dir_config, mpm_itk_module);

	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "vhs_itk_post_read: itk uid='%d' itk gid='%d' itk username='%s' before change", cfg->uid, cfg->gid, cfg->username);
	if ((libhome_uid == -1 || libhome_gid == -1)) { 
			cfg->uid = vhr->itk_defuid;
			cfg->gid = vhr->itk_defgid;
			cfg->username = vhr->itk_defusername;
		} else {
		char *itk_username = NULL;
		/* struct passwd *pw = getpwuid(libhome_uid); */

       		cfg->uid = libhome_uid;
		cfg->gid = libhome_gid;

			/* set the username - otherwise MPM-ITK will not work */
		/* itk_username = apr_psprintf(r->pool, "%s", pw->pw_name); */
		itk_username = apr_psprintf(r->pool, "root");
       	       cfg->username = itk_username;
		}
	  VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "vhs_itk_post_read: itk uid='%d' itk gid='%d' itk username='%s' after change", cfg->uid, cfg->gid, cfg->username);
	}
	VH_AP_LOG_RERROR(APLOG_MARK, APLOG_DEBUG, 0, r, "vhs_itk_post_read: END ***");
       return OK;
}
#endif /* HAVE_MPM_ITK_SUPPORT  */


#ifdef HAVE_MOD_SUPHP_SUPPORT
/*
 * This function will configure suPHP
 */
typedef struct {
	int			engine;			/* Status of suPHP_Engine */
	char		*php_config;
	int			cmode;			/* Server of directory configuration? */
	char		*target_user;
	char		*target_group;
	apr_table_t	*handlers;
} suphp_conf;

static int vhs_suphp_handler(request_rec *r)
{
	module *suphp_module = ap_find_linked_module("mod_suphp.c");

	if (suphp_module == NULL) {
		ap_log_error(APLOG_MARK, APLOG_ERR, 0, r->server, "vhs_suphp_handler: mod_suphp.c is not loaded");
		return HTTP_INTERNAL_SERVER_ERROR;
	}

	suphp_conf *cfg    = (suphp_conf *)ap_get_module_config(r->server->module_config, suphp_module);
	suphp_conf *dircfg = (suphp_conf *)ap_get_module_config(r->per_dir_config, suphp_module);

	if (cfg == NULL)
		return HTTP_INTERNAL_SERVER_ERROR;

	dircfg->engine       = cfg->engine;
	dircfg->php_config   = cfg->php_config;
	dircfg->target_user  = cfg->target_user;
	dircfg->target_group = cfg->target_group;

	ap_set_module_config(r->per_dir_config, suphp_module, dircfg);

	return DECLINED;
}

// XXX: to test
static void vhs_suphp_config(request_rec *r, vhs_config_rec *vhr, char *path, char *uid, char *uid)
{
  /* Path to the suPHP config file per user */
	char *transformedPath = NULL;
	char *transformedUid = NULL;
	char *transformedGid = NULL;

	if (vhr->suphp_config_path) {
		//if ((apr_strstr(r->pool,vhr->suphp_config_path,"%s")!=NULL) && (username!=NULL))
		if ((strstr(vhr->suphp_config_path,"%s")!=NULL) && (username!=NULL))
			transformedPath = apr_psprintf(r->pool, vhr->suphp_config_path, username);
		else
			transformedPath = vhr->suphp_config_path;
	} else {
		transformedPath = path;
	}

	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_ERR, 0, r->server, "vhs_suphp_config: suPHP_config_dir set to %s", transformedPath);

	module *suphp_module = ap_find_linked_module("mod_suphp.c");

	if (suphp_module == NULL) {
		ap_log_error(APLOG_MARK, APLOG_ERR, 0, r->server, "vhs_suphp_config: mod_suphp.c is not loaded");
		return;
	}

	suphp_conf *cfg = (suphp_conf *)ap_get_module_config(r->server->module_config, suphp_module);
	if ( cfg == NULL )
		ap_log_error(APLOG_MARK, APLOG_ERR, 0, r->server, "vhs_suphp_config: suPHP_config_dir is NULL");

	cfg->engine       = (strstr(passwd,"engine=Off") == NULL);
	//cfg->engine       = (apr_strstr(r->pool,passwd,"engine=Off") == NULL);
	cfg->php_config   = apr_pstrdup(r->pool,transformedPath);

	transformedUid    = apr_psprintf(r->pool, "#%d", uid);
	cfg->target_user  = apr_pstrdup(r->pool,transformedUid);

	transformedGid    = apr_psprintf(r->pool, "#%d", gid);
	cfg->target_group = apr_pstrdup(r->pool,transformedGid);

	ap_set_module_config(r->server->module_config, suphp_module, cfg);
}
#endif /* HAVE_MOD_SUPHP_SUPPORT  */

#ifdef HAVE_MOD_PHP_SUPPORT
/*
 * This function will configure on the fly the php like php.ini will do
 */
static void vhs_php_config(request_rec * r, vhs_config_rec * vhr, char *path, char *passwd)
{
	/*
	 * Some Basic PHP stuff, thank to Igor Popov module
	 */
	apr_table_set(r->subprocess_env, "PHP_DOCUMENT_ROOT", path);
	zend_alter_ini_entry("doc_root", sizeof("doc_root"), path, ap_strlen(path), 4, 1);
	/*
	 * vhs_PHPsafe_mode support
	 */
	if (vhr->safe_mode) {
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP safe_mode engaged");
		zend_alter_ini_entry("safe_mode", 10, "1", 1, 4, 16);
	} else {
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP safe_mode inactive, defaulting to php.ini values");
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
			char *obasedir_path;

			if (vhr->path_prefix) {
				obasedir_path = apr_pstrcat(r->pool, vhr->openbdir_path, ":", vhr->path_prefix, path, NULL);
			} else {
				obasedir_path = apr_pstrcat(r->pool, vhr->openbdir_path, ":", path, NULL);
			}
			zend_alter_ini_entry("open_basedir", 13, obasedir_path, ap_strlen(obasedir_path), 4, 16);
			VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP open_basedir set to %s (appending mode)", obasedir_path);
		} else {
			zend_alter_ini_entry("open_basedir", 13, path, ap_strlen(path), 4, 16);
			VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP open_basedir set to %s", path);
		}
	} else {
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP open_basedir inactive defaulting to php.ini values");
	}

	/*
	 * vhs_PHPdisplay_errors support
	 */
	if (vhr->display_errors) {
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP display_errors engaged");
		zend_alter_ini_entry("display_errors", 10, "1", 1, 4, 16);
	} else {
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP display_errors inactive defaulting to php.ini values");
	}

	/*
	 * vhs_PHPopt_fromdb
	 */
	if (vhr->phpopt_fromdb) {
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: PHP from DB engaged");
		char           *retval;
		char           *state;
		char           *myphpoptions;

		myphpoptions = apr_pstrdup(r->pool, passwd);
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: DB => %s", myphpoptions);

		if ((ap_strchr(myphpoptions, ';') != NULL) && (ap_strchr(myphpoptions, '=') != NULL)) {
			/* Getting values for PHP there so we can proceed */

			retval = apr_strtok(myphpoptions, ";", &state);
			while (retval != NULL) {
				char           *key = NULL;
				char           *val = NULL;
				char           *strtokstate = NULL;

				key = apr_strtok(retval, "=", &strtokstate);
				val = apr_strtok(NULL, "=", &strtokstate);
				VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: Zend PHP Stuff => %s => %s", key, val);
				zend_alter_ini_entry(key, ap_strlen(key) + 1, val, ap_strlen(val), 4, 16);
				retval = apr_strtok(NULL, ";", &state);
			}
		}
		else {
			VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_php_config: no PHP stuff found.");
		}
	}
}
#endif				/* HAVE_MOD_PHP_SUPPORT */



#ifdef HAVE_LDAP_SUPPORT
#define FILTER_LENGTH MAX_STRING_LEN

/*
 *  Get the stuff from LDAP
 */
int getldaphome(request_rec *r, vhs_config_rec *vhr, char *hostname, mod_vhs_request_t *reqc)
{
	/* LDAP associated variable and stuff */
	const char 		**vals = NULL;
	char 			filtbuf[FILTER_LENGTH];
    	int 			result = 0;
    	const char 		*dn = NULL;
    	util_ldap_connection_t 	*ldc = NULL;
    	int 			failures = 0;
	
  	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "getldaphome() called.");

start_over:

	if (vhr->ldap_host) {
	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "util_ldap_connection_find(r,%s,%d,%s,%s,%d,%d);",vhr->ldap_host, vhr->ldap_port, vhr->ldap_binddn, vhr->ldap_bindpw, vhr->ldap_deref, vhr->ldap_secure);
    		ldc = util_ldap_connection_find(r, vhr->ldap_host, vhr->ldap_port, vhr->ldap_binddn,
						vhr->ldap_bindpw, vhr->ldap_deref, vhr->ldap_secure);
	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "util_ldap_connection_find();");
    	} else {
        	ap_log_rerror(APLOG_MARK, APLOG_WARNING|APLOG_NOERRNO, 0, r,
                      		"translate: no vhr->host - weird...?");
        	return DECLINED;
    	}

	ap_log_rerror(APLOG_MARK, APLOG_DEBUG|APLOG_NOERRNO, 0, r, "translating %s", r->uri);

    	apr_snprintf(filtbuf, FILTER_LENGTH, "(&(%s)(|(apacheServerName=%s)(apacheServerAlias=%s)))", vhr->ldap_filter, hostname, hostname);
	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "filtbuf = %s",filtbuf);
    	result = util_ldap_cache_getuserdn(r, ldc, vhr->ldap_url, vhr->ldap_basedn, vhr->ldap_scope, ldap_attributes, filtbuf, &dn, &vals);
    	util_ldap_connection_close(ldc);

    	/* sanity check - if server is down, retry it up to 5 times */
	if (result == LDAP_SERVER_DOWN) {
    		if (failures++ <= 5) {
    			goto start_over;
        	}
    	}

    if ((result == LDAP_NO_SUCH_OBJECT)) {
     	ap_log_rerror(APLOG_MARK, APLOG_WARNING|APLOG_NOERRNO, 0, r,
    		          "virtual host %s not found",
    		          hostname);
	return DECLINED;
    }

    /* handle bind failure */
    if (result != LDAP_SUCCESS) {
       ap_log_rerror(APLOG_MARK, APLOG_WARNING|APLOG_NOERRNO, 0, r,
                     "translate failed; virtual host %s; URI %s [%s]",
    		         hostname, r->uri, ldap_err2string(result));
	return DECLINED;
    }


  int i = 0;
  while (ldap_attributes[i]) {
	if (strcasecmp (ldap_attributes[i], "apacheServerName") == 0) {
	  reqc->name = apr_pstrdup (r->pool, vals[i]);
	}
	else if (strcasecmp (ldap_attributes[i], "apacheServerAdmin") == 0) {
	  reqc->admin = apr_pstrdup (r->pool, vals[i]);
	}
	else if (strcasecmp (ldap_attributes[i], "apacheDocumentRoot") == 0) {
	  reqc->docroot = apr_pstrdup (r->pool, vals[i]);
	}
	else if (strcasecmp (ldap_attributes[i], "apachePhpopts") == 0) {
	  reqc->phpoptions = apr_pstrdup (r->pool, vals[i]);
	}
	else if (strcasecmp (ldap_attributes[i], "apacheSuexecUid") == 0) {
	  reqc->uid = apr_pstrdup(r->pool, vals[i]);
	}
	else if (strcasecmp (ldap_attributes[i], "apacheSuexecGid") == 0) {
	  reqc->gid = apr_pstrdup(r->pool, vals[i]);
	}
	else if (strcasecmp (ldap_attributes[i], "associatedDomain") == 0) {
	  reqc->associateddomain = apr_pstrdup(r->pool, vals[i]);
	}
	i++;
  }
    
  reqc->vhost_found = VH_VHOST_INFOS_FOUND;

  return OK;
}
#endif /* HAVE_LDAP_SUPPORT */

/*
 * Send the right path to the end user uppon a request.
 */
static int vhs_translate_name(request_rec * r)
{
	vhs_config_rec     	*vhr  = (vhs_config_rec *)     ap_get_module_config(r->server->module_config, &vhs_module);
	core_server_config 	*conf = (core_server_config *) ap_get_module_config(r->server->module_config, &core_module);

	const char     		*host = 0;
	/* mod_alias like functions */
	char           		*ret = 0;
	int			   		status = 0;

	/* Stuff */
	char           *ptr = 0;

	mod_vhs_request_t *reqc;
	int vhost_found_by_request = DECLINED;

	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: BEGIN ***");

	/* If VHS is not enabled, then don't process request */
	if (!vhr->enable) {
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: VHS Disabled ");
		return DECLINED;
	}

	reqc = ap_get_module_config(r->request_config, &vhs_module);	
	if (!reqc)
	  {
		/* VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: variable reqc does not already exists.... creating ! pid=%d request_rec=%d @request_config='%d'", getpid(), r, &(r->request_config)); */
		reqc = (mod_vhs_request_t *)apr_pcalloc(r->pool, sizeof(mod_vhs_request_t));
		reqc->vhost_found = VH_VHOST_INFOS_NOT_YET_REQUESTED;
		ap_set_module_config(r->request_config, &vhs_module, reqc);
	  }
	/*else
	  VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: variable reqc already exists ! pid=%d request_rec=%d @request_config='%d'", getpid(), r, &(r->request_config));
	*/
#ifdef HAVE_LDAP_SUPPORT
	/* If we don't have LDAP Url module is disabled */
	if (!vhr->ldap_have_url) {
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: VHS Disabled - No LDAP URL ");
		return DECLINED;
	}
	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: VHS Enabled (LDAP).");
#endif /* HAVE_LDAP_SUPPORT */
#ifdef HAVE_MOD_DBD_SUPPORT
	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: VHS Enabled (Mod DBD).");
#endif /* HAVE_MOD_DBD_SUPPORT */

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
	if ((ptr = ap_strchr(host, ':'))) {
		*ptr = '\0';
	}

	if (reqc->vhost_found == VH_VHOST_INFOS_NOT_YET_REQUESTED)
	  {
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: looking for %s", host);
		/*
		 * Trying to get vhost information
		 */
#ifdef HAVE_LDAP_SUPPORT
		vhost_found_by_request = getldaphome(r, vhr, (char *) host, reqc);
#endif
#ifdef HAVE_MOD_DBD_SUPPORT
		vhost_found_by_request = getmoddbdhome(r, vhr, (char *) host, reqc);
#endif
		
		if (vhost_found_by_request != OK) { 
		/*
		   * The vhost has not been found
		 * Trying to get lamer mode or not
		 */
		if (vhr->lamer_mode) {
			VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: Lamer friendly mode engaged");
			if ((strncasecmp(host, "www.", 4) == 0) && (ap_strlen(host) > 4)) {
				char           *lhost;
				lhost = apr_pstrdup(r->pool, host + 5 - 1);
			  VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: Found a lamer for %s -> %s", host, lhost);
#ifdef HAVE_LDAP_SUPPORT
			  vhost_found_by_request = getldaphome(r, vhr, lhost, reqc);
#endif
#ifdef HAVE_MOD_DBD_SUPPORT
			  vhost_found_by_request = getmoddbdhome(r, vhr, lhost, reqc);
#endif
			  if (vhost_found_by_request != OK) {
					if (vhr->log_notfound) {
						ap_log_error(APLOG_MARK, APLOG_NOTICE, 0, r->server, "vhs_translate_name: no host found in database for %s (lamer %s)", host, lhost);
					}
					return vhs_redirect_stuff(r, vhr);
				}
			}
		} else {
			if (vhr->log_notfound) {
			  ap_log_error(APLOG_MARK, APLOG_NOTICE, 0, r->server, "vhs_translate_name: no host found in database for %s (lamer mode not eanbled)", host);
			}
			return vhs_redirect_stuff(r, vhr);
		}
	}
	  }
	else 
	  {
		VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: Request to backend has already be done (vhs_itk_post_read()) !");
		if (reqc->vhost_found == VH_VHOST_INFOS_NOT_FOUND)
		  vhost_found_by_request = DECLINED; /* the request has already be done and vhost was not found */
		else
		  vhost_found_by_request = OK; /* the request has already be done and vhost was found */
	  }

	if (vhost_found_by_request == OK)
	  VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: path found in database for %s is %s", host, reqc->docroot);
	else
	  {
		if (vhr->log_notfound) {
			ap_log_error(APLOG_MARK, APLOG_NOTICE, 0, r->server, "vhs_translate_name: no path found found in database for %s (normal)", host);
		}
		return vhs_redirect_stuff(r, vhr);
	}

#ifdef WANT_VH_HOST
	apr_table_set(r->subprocess_env, "VH_HOST", host);
#endif /* WANT_VH_HOST */
	apr_table_set(r->subprocess_env, "VH_GECOS", reqc->associateddomain ? reqc->associateddomain : "");
	/* Do we have handle vhr_Path_Prefix here ? */
	if (vhr->path_prefix) {
		apr_table_set(r->subprocess_env, "VH_PATH", apr_pstrcat(r->pool, vhr->path_prefix, reqc->docroot, NULL));
		apr_table_set(r->subprocess_env, "SERVER_ROOT", apr_pstrcat(r->pool, vhr->path_prefix, reqc->docroot, NULL));
	} else {
		apr_table_set(r->subprocess_env, "VH_PATH", reqc->docroot);
		apr_table_set(r->subprocess_env, "SERVER_ROOT", reqc->docroot);
	}

	if (reqc->admin) {
		r->server->server_admin = apr_pstrcat(r->pool, reqc->admin, NULL);
	} else {
		r->server->server_admin = apr_pstrcat(r->pool, "webmaster@", r->hostname, NULL);
	}
	r->server->server_hostname = apr_pstrcat(r->connection->pool, host, NULL);
	r->parsed_uri.path = apr_pstrcat(r->pool, vhr->path_prefix ? vhr->path_prefix : "", reqc->docroot, r->parsed_uri.path, NULL);
	r->parsed_uri.hostname = r->server->server_hostname;
	r->parsed_uri.hostinfo = r->server->server_hostname;

	/* document_root */
	if (vhr->path_prefix) {
		conf->ap_document_root = apr_pstrcat(r->pool, vhr->path_prefix, reqc->docroot, NULL);
	} else {
		conf->ap_document_root = apr_pstrcat(r->pool, reqc->docroot, NULL);
	}

	/* if directory exist */
	if (!ap_is_directory(r->pool, reqc->docroot)) {
		ap_log_error(APLOG_MARK, APLOG_ALERT, 0, r->server,
		"vhs_translate_name: homedir '%s' is not dir at all", reqc->docroot);
		return DECLINED;
	}
	r->filename = apr_psprintf(r->pool, "%s%s%s", vhr->path_prefix ? vhr->path_prefix : "", reqc->docroot, r->uri);

	/* Avoid getting two // in filename */
	ap_no2slash(r->filename);

	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: translated http://%s%s to file %s", host, r->uri, r->filename);

#ifdef HAVE_MOD_PHP_SUPPORT
	vhs_php_config(r, vhr, reqc->docroot, reqc->phpoptions);
#endif /* HAVE_MOD_PHP_SUPPORT */

#ifdef HAVE_MOD_SUPHP_SUPPORT
	vhs_suphp_config(r, vhr, reqc->docroot, reqc->uid, reqc->gid);
#endif /* HAVE_MOD_SUPHP_SUPPORT */

	VH_AP_LOG_ERROR(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: END ***");
	return OK;
}

/*
 * Stuff for register the module
 */
static const command_rec vhs_commands[] = {
	AP_INIT_FLAG( "EnableVHS", set_flag, (void *)5, RSRC_CONF, "Enable VHS module"),

	AP_INIT_TAKE1("vhs_Path_Prefix", set_field, (void *)1, RSRC_CONF, "Set path prefix."),
	AP_INIT_TAKE1("vhs_Default_Host", set_field, (void *)2, RSRC_CONF, "Set default host if HTTP/1.1 is not used."),
	AP_INIT_FLAG( "vhs_Lamer", set_flag, (void *)0, RSRC_CONF, "Enable Lamer Friendly mode"),
	AP_INIT_FLAG( "vhs_LogNotFound", set_flag, (void *)7, RSRC_CONF, "Log on error log when host or path is not found."),

#ifdef HAVE_MOD_PHP_SUPPORT
	AP_INIT_FLAG( "vhs_PHPsafe_mode", set_flag, (void *)1, RSRC_CONF, "Enable PHP Safe Mode"),
	AP_INIT_FLAG( "vhs_PHPopen_basedir", set_flag, (void *)2, RSRC_CONF, "Set PHP open_basedir to path"),
	AP_INIT_FLAG( "vhs_PHPopt_fromdb", set_flag, (void *)3, RSRC_CONF, "Gets PHP options from db/libhome"),
	AP_INIT_FLAG( "vhs_PHPdisplay_errors", set_flag, (void *)4, RSRC_CONF, "Enable PHP display_errors"),
	AP_INIT_FLAG( "vhs_append_open_basedir", set_flag, (void *)6, RSRC_CONF, "Append homedir path to PHP open_basedir to vhs_open_basedir_path."),
	AP_INIT_TAKE1("vhs_open_basedir_path", set_field, (void *)3, RSRC_CONF, "The default PHP open_basedir path."),
#endif /* HAVE_MOD_PHP_SUPPORT */

#ifdef HAVE_MOD_SUPHP_SUPPORT
	AP_INIT_TAKE1( "vhs_suphp_config_path", set_field, (void *)10, RSRC_CONF, "The SuPHP configuration path for the user"),
#endif /* HAVE_MOD_SUPHP_SUPPORT */
#ifdef HAVE_MPM_ITK_SUPPORT
	AP_INIT_FLAG("vhs_itk_enable", set_flag, (void *)8, RSRC_CONF, "Enable MPM-ITK support"),
#endif /* HAVE_MPM_ITK_SUPPORT */

	AP_INIT_TAKE2( "vhs_Alias", add_alias, NULL, RSRC_CONF, "a fakename and a realname"),
	AP_INIT_TAKE2( "vhs_ScriptAlias", add_alias, "cgi-script", RSRC_CONF, "a fakename and a realname"),
	AP_INIT_TAKE23("vhs_Redirect", add_redirect, (void *)HTTP_MOVED_TEMPORARILY, OR_FILEINFO,
						"an optional status, then document to be redirected and "
						"destination URL"),
	AP_INIT_TAKE2( "vhs_AliasMatch", add_alias_regex, NULL, RSRC_CONF, "a regular expression and a filename"),
	AP_INIT_TAKE2( "vhs_ScriptAliasMatch", add_alias_regex, "cgi-script", RSRC_CONF, "a regular expression and a filename"),
	AP_INIT_TAKE23("vhs_RedirectMatch", add_redirect_regex, (void *)HTTP_MOVED_TEMPORARILY, OR_FILEINFO,
						"an optional status, then a regular expression and "
						"destination URL"),
	AP_INIT_TAKE2( "vhs_RedirectTemp", add_redirect2, (void *)HTTP_MOVED_TEMPORARILY, OR_FILEINFO,
						"a document to be redirected, then the destination URL"),
	AP_INIT_TAKE2( "vhs_RedirectPermanent", add_redirect2, (void *)HTTP_MOVED_PERMANENTLY, OR_FILEINFO,
						"a document to be redirected, then the destination URL"),
#ifdef HAVE_LDAP_SUPPORT
	AP_INIT_TAKE1( "vhs_LDAPBindDN",set_field, (void *)4, RSRC_CONF,
						"DN to use to bind to LDAP server. If not provided, will do an anonymous bind."),
	AP_INIT_TAKE1( "vhs_LDAPBindPassword",set_field, (void *)5, RSRC_CONF,
						"Password to use to bind LDAP server. If not provider, will do an anonymous bind."),
	AP_INIT_TAKE1( "vhs_LDAPDereferenceAliases",set_field, (void *)6, RSRC_CONF,
						"Determines how aliases are handled during a search. Can be one of the"
			            		"values \"never\", \"searching\", \"finding\", or \"always\"."
						"Defaults to always."),
	AP_INIT_TAKE1( "vhs_LDAPUrl",mod_vhs_ldap_parse_url, NULL, RSRC_CONF,
						"URL to define LDAP connection in form ldap://host[:port]/basedn[?attrib[?scope[?filter]]]."),
#endif /* HAVE_LDAP_SUPPORT */

#ifdef HAVE_MOD_DBD_SUPPORT
	AP_INIT_TAKE1("vhs_VhostDBDTable", set_field, (void *)0, RSRC_CONF, "Table name used to construct SQL request to fetch vhost for host"),
#endif /* HAVE_MOD_DBD_SUPPORT */

	{NULL}
};

static void register_hooks(apr_pool_t * p)
{
	/* Modules that have to be loaded before mod_vhs */
	static const char *const aszPre[] =
	{"mod_userdir.c", "mod_vhost_alias.c", NULL};
	/* Modules that have to be loaded after mod_vhs */
	static const char *const aszSucc[] =
	{"mod_php.c", "mod_suphp.c", NULL};
	
#ifdef HAVE_MPM_ITK_SUPPORT
	static const char * const aszSuc_itk[]= {"itk.c",NULL };
	ap_hook_post_read_request(vhs_itk_post_read, NULL, aszSuc_itk, APR_HOOK_REALLY_FIRST);
#endif /* HAVE_MPM_ITK_SUPPORT */
	
	ap_hook_post_config(vhs_init_handler, NULL, NULL, APR_HOOK_MIDDLE);
	ap_hook_translate_name(vhs_translate_name, aszPre, aszSucc, APR_HOOK_FIRST);
	ap_hook_fixups(fixup_redir, NULL, NULL, APR_HOOK_MIDDLE);

#ifdef HAVE_MOD_SUPHP_SUPPORT
	ap_hook_handler(vhs_suphp_handler, NULL, aszSucc, APR_HOOK_FIRST);
#endif /* HAVE_MOD_SUPHP_SUPPORT */
#ifdef HAVE_LDAP_SUPPORT
	ap_hook_optional_fn_retrieve(ImportULDAPOptFn,NULL,NULL,APR_HOOK_MIDDLE);
#endif /* HAVE_LDAP_SUPPORT */
}

AP_DECLARE_DATA module vhs_module = {
	STANDARD20_MODULE_STUFF,
	create_alias_dir_config,		/* create per-directory config structure */
	merge_alias_dir_config,			/* merge per-directory config structures */
	vhs_create_server_config,		/* create per-server config structure */
	vhs_merge_server_config,		/* merge per-server config structures */
	vhs_commands,				/* command apr_table_t */
	register_hooks				/* register hooks */
};
