/* ====================================================================
 * The Apache Software License, Version 1.1
 *
 * Copyright (c) 2000 The Apache Software Foundation.  All rights
 * reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in
 *    the documentation and/or other materials provided with the
 *    distribution.
 *
 * 3. The end-user documentation included with the redistribution,
 *    if any, must include the following acknowledgment:
 *       "This product includes software developed by the
 *        Apache Software Foundation (http://www.apache.org/)."
 *    Alternately, this acknowledgment may appear in the software itself,
 *    if and wherever such third-party acknowledgments normally appear.
 *
 * 4. The names "Apache" and "Apache Software Foundation" must
 *    not be used to endorse or promote products derived from this
 *    software without prior written permission. For written
 *    permission, please contact apache@apache.org.
 *
 * 5. Products derived from this software may not be called "Apache",
 *    nor may "Apache" appear in their name, without prior written
 *    permission of the Apache Software Foundation.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESSED OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED.  IN NO EVENT SHALL THE APACHE SOFTWARE FOUNDATION OR
 * ITS CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF
 * USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT
 * OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 * ====================================================================
 *
 * This software consists of voluntary contributions made by many
 * individuals on behalf of the Apache Software Foundation.  For more
 * information on the Apache Software Foundation, please see
 * <http://www.apache.org/>.
 *
 * Portions of this software are based upon public domain software
 * originally written at the National Center for Supercomputing Applications,
 * University of Illinois, Urbana-Champaign.
 */
/*  $Id: mod_vhs.c,v 1.17 2004-12-30 12:23:19 kiwi Exp $
*/

/* 
 * Set this if you'd like to have looooots of debug
#define VH_DEBUG 1
 */

/* Original Author: Michael Link <mlink@apache.org> */
/* mod_vhs author : Xavier Beaudouin <kiwi@oav.net> */

#include "apr.h"
#include "apr_strings.h"
#include "apr_lib.h"
#include "apr_uri.h"

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
#define	DONT_SUBSTITUTE_SYSTEM 1
#include <home/hpwd.h>

static int vhs_init_handler(apr_pool_t *pconf, apr_pool_t *plog, apr_pool_t *ptemp, server_rec *s);
static void* vhs_create_server_config(apr_pool_t *p, server_rec *s);
static void *vhs_merge_server_config(apr_pool_t *p, void *parentv, void *childv);

static int vhs_translate_name(request_rec *r);

/* Prototypes for configuration work */
static const char* set_field(cmd_parms *parms, void *mconfig, const char *arg);
static const char* set_flag (cmd_parms *parms, void *mconfig, int flag);

static const command_rec vhs_commands[] = {
	AP_INIT_TAKE1("vhs_libhome_tag",	set_field,(void*) 0,RSRC_CONF,"Set libhome tag." ),
	AP_INIT_TAKE1("vhs_Path_Prefix",	set_field,(void*) 1,RSRC_CONF,"Set path prefix." ),
	AP_INIT_TAKE1("vhs_Default_Host",	set_field,(void*) 2,RSRC_CONF,"Set default host if HTTP/1.1 is not used." ),
	AP_INIT_FLAG("vhs_Lamer",		set_flag, (void*) 0,RSRC_CONF,"Enable Lamer Friendly mode"),
	{ NULL }
};

static void register_hooks(apr_pool_t *p)
{
	ap_hook_post_config(vhs_init_handler, NULL, NULL, APR_HOOK_MIDDLE);
	ap_hook_translate_name(vhs_translate_name, NULL, NULL, APR_HOOK_FIRST);
}

AP_DECLARE_DATA module vhs_module = {
    STANDARD20_MODULE_STUFF,
    NULL,			/* create per-directory config structure */
    NULL,			/* merge per-directory config structures */
    vhs_create_server_config,	/* create per-server config structure */
    vhs_merge_server_config,	/* merge per-server config structures */
    vhs_commands,		/* command apr_table_t */
    register_hooks		/* register hooks */
};

/*
 * Configuration structure
 */
typedef struct {
	char			*libhome_tag;
	
	char			*path_prefix;
	char			*default_host;
	
	unsigned short int	lamer_mode;	/* Lamer friendly mode */
	
} vhs_config_rec;

/*
 * Apache per server config structure
 */
static void* vhs_create_server_config(apr_pool_t *p, server_rec *s)
{
	vhs_config_rec *vhr = (vhs_config_rec*) apr_pcalloc(p, sizeof(vhs_config_rec));
	
	return vhr;
}

/*
 * Apache merge per server config structures
 */
static void *vhs_merge_server_config(apr_pool_t *p, void *parentv, void *childv)
{
	vhs_config_rec *parent = (vhs_config_rec *) parentv;
	vhs_config_rec *child = (vhs_config_rec *) childv;
	vhs_config_rec *conf = (vhs_config_rec*) apr_pcalloc(p, sizeof(vhs_config_rec));
	
	conf->libhome_tag  = (child->libhome_tag ? child->libhome_tag : parent->libhome_tag);

	conf->path_prefix  = (child->path_prefix ? child->path_prefix : parent->path_prefix);
	conf->default_host = (child->default_host ? child->default_host : parent->default_host);

	return conf;
}

/*
 * Set the fields inside the conf struct
 */
static const char* set_field(cmd_parms *parms, void *mconfig, const char *arg)
{
	int pos = (int) parms->info;
	vhs_config_rec *vhr = (vhs_config_rec*) ap_get_module_config(parms->server->module_config, &vhs_module);
	
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
	}
	
	return NULL;
}

/*
 * To setting flags
 */
static const char* set_flag (cmd_parms *parms, void *mconfig, int flag)
{
	int pos = (int) parms->info;
	vhs_config_rec *vhr = (vhs_config_rec*) ap_get_module_config(parms->server->module_config, &vhs_module);

	switch (pos) {
		case 0:
			if(flag) {
				vhr->lamer_mode = 1;
			} else {
				vhr->lamer_mode = 0;
			}
			break;
	}

	return NULL;
}


static int vhs_init_handler(apr_pool_t *pconf, apr_pool_t *plog, apr_pool_t *ptemp, server_rec *s)
{
	ap_add_version_component(pconf, "mod_vhs/1.0.8");
	
	return OK;
}

/* XXX: WTF with http_core.h ??? */
typedef struct {
#ifdef GPROF
	char *gprof_dir;
#endif
	const char *ap_document_root;
	char *access_name;
	apr_array_header_t *sec_dir;
	apr_array_header_t *sec_url;
	int redirect_limit;
	int subreq_limit;
} core_server_config;

static int vhs_translate_name(request_rec *r)
{
	vhs_config_rec *vhr = (vhs_config_rec*) ap_get_module_config(r->server->module_config, &vhs_module);
	core_server_config *conf = (core_server_config *)ap_get_module_config(r->server->module_config, &vhs_module);
	const char *host;
	char *path;
	char *env = NULL;
	char *ptr;
	int i;
	/* libhome */
	struct passwd *p;

	// I think this was for a 1.3 work around
	if (r->uri[0] != '/') {
		ap_log_error(APLOG_MARK, APLOG_ALERT, 0, r->server, "vhs_translate_name: declined %s no leading `/'", r->uri);
		return DECLINED;
	}
	
	if (!(host = apr_table_get(r->headers_in, "Host"))) {
		if (!vhr->default_host) {
			ap_log_error(APLOG_MARK, APLOG_ALERT, 0, r->server, "vhs_translate_name: no host found (non HTTP/1.1 request, no default set) %s", host);
			return DECLINED;
		}
		else {
			host = vhr->default_host;
		}
	}

	/* DNS names are case insensitives */
	apr_tolower(host);

	if (ptr = strchr(host,':')) {
		*ptr = '\0';
	}

#ifdef VH_DEBUG
	ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: looking for %s", host);
#endif /* VH_DEBUG */

	/*
	 * libhome stuff is not thread safe so be nice and add a mutex
	 */
	/*
 	 * Set the default libhome tag
	 */
	if (vhr->libhome_tag) {
		setpwtag(vhr->libhome_tag);
#ifdef VH_DEBUG
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: setpwtag set %s", vhr->libhome_tag);
#endif /* VH_DEBUG */
	} else {
		setpwtag("mod_vhs");
#ifdef VH_DEBUG
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: setpwtag set %s", "mod_vhs");
#endif /* VH_DEBUG */
	}

	if((p=home_getpwnam(host))!=NULL) {
		path = p->pw_dir;
#ifdef VH_DEBUG
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: path found in database for %s is %s", host, path);
#endif /* VH_DEBUG */

	} else {
		/*
		 * Trying to get lamer mode or not 
		 */
		if (vhr->lamer_mode) {
#ifdef VH_DEBUG
			ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: Lamer friendly mode engaged");
#endif
			if( (strncasecmp(host,"www.",4) == 0) && (strlen(host) > 4) ) {
				char *lhost;
/*
				lhost = strdup( host + 5-1);
*/
				lhost = apr_pstrdup( r->pool, host + 5-1);
#ifdef VH_DEBUG
				ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: Found a lamer for %s -> %s",host, lhost);
#endif
				if((p=home_getpwnam(lhost)) != NULL) {
					path = p->pw_dir;
#ifdef VH_DEBUG
					ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: lamer for %s -> %s => %s",host, lhost, path);
#endif
				} else {
					ap_log_error(APLOG_MARK, APLOG_NOTICE, 0, r->server, "vhs_translate_name: no host found in database for %s (lamer %s)", host, lhost);
					return DECLINED;
				}
			}
		} else {
			ap_log_error(APLOG_MARK, APLOG_NOTICE, 0, r->server, "vhs_translate_name: no host found in database for %s", host);
			return DECLINED;
		}
	}

	if(path == NULL) {
		ap_log_error(APLOG_MARK, APLOG_NOTICE, 0, r->server, "vhs_translate_name: no path found found in database for %s", host);
		return DECLINED;
	}

	
	apr_table_set(r->subprocess_env, "VH_HOST", host);
	apr_table_set(r->subprocess_env, "VH_PATH", path);
	apr_table_set(r->subprocess_env, "VH_GECOS", p->pw_gecos ? p->pw_gecos : "");
	apr_table_set(r->subprocess_env, "SERVER_ROOT", path);
/*
	apr_table_set(r->subprocess_env, "DOCUMENT_ROOT", path);
*/
	r->server->server_admin = apr_pstrcat(r->pool,"webmaster@",host, NULL);
	r->server->server_hostname = apr_pstrcat(r->pool,r->hostname,NULL);
	r->parsed_uri.path = apr_pstrcat(r->pool, path,r->parsed_uri.path,NULL);
	r->parsed_uri.hostname = r->server->server_hostname;	
	r->parsed_uri.hostinfo = r->server->server_hostname;	

	conf->ap_document_root = path;	/* Set the bloody DOCUMENT_ROOT */

	r->filename = apr_psprintf(r->pool, "%s%s%s", vhr->path_prefix ? vhr->path_prefix : "", path, r->uri);
	ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vhs_translate_name: translated http://%s%s to file %s", host, r->uri, r->filename);

	return OK;
}
