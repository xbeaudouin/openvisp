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
/*  $Id: mod_vdbh.c,v 1.1 2004-07-25 14:48:44 kiwi Exp $
*/

/* Author: Michael Link <mlink@apache.org> */

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

#ifdef HAVE_STDDEF_H
#include <stddef.h>
#endif

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

#include <mysql.h>

#define	mkupper(c)	(((c) >= 'a' && (c) <= 'z') ? ((c) - 'a' + 'A') : c)

static int match(const char* pattern, const char* string)
{
	char type;
	char delim = ' ';

	if (!pattern || !string) {
		return 0;
	}

	while (*string && *pattern && *pattern != '*' && *pattern != '%') {
		if (*pattern == '\\' && *(pattern + 1)) {
			if (!*++pattern || !(mkupper(*pattern) == mkupper(*string))) {
				return 0;
			}
			else {
				pattern++, string++;
			}
		}


		if (*pattern == '?') {
			pattern++, string++;
		}
		else if (mkupper(*pattern) == mkupper(*string)) {
			pattern++, string++;
		}
		else {
			break;
		}
	}
	
	if (*pattern == '*' || *pattern == '%') {
		type = (*pattern++);
		while (*string) {
			if (match(pattern, string)) {
				return 1;
			}
			else if (type == '*' || *string != delim) {
				string++;
			}
			else {
				break;
			}
		}
	}
	
	if (!*string && !*pattern) {
		return 1;
	}
	
	return 0;
}

static int vdbh_init_handler(apr_pool_t *pconf, apr_pool_t *plog, apr_pool_t *ptemp, server_rec *s);
static void* vdbh_create_server_config(apr_pool_t *p, server_rec *s);
static void *vdbh_merge_server_config(apr_pool_t *p, void *parentv, void *childv);

/* 2  */ static int vdbh_translate_name(request_rec *r);

static const char* set_switch(cmd_parms *parms, void *mconfig, int flag);
static const char* set_field(cmd_parms *parms, void *mconfig, const char *arg);
static const char* set_port(cmd_parms *parms, void *mconfig, const char *arg);
static const char* set_decline(cmd_parms *parms, void *mconfig, const char *arg);

static const command_rec vdbh_commands[] = {
	AP_INIT_FLAG(		"vdbh",							set_switch,		(void*) 1,	RSRC_CONF,		"turn mod_vdbh on or off." ),
	AP_INIT_FLAG(		"vdbh_CLIENT_COMPRESS",			set_switch,		(void*) 2,	RSRC_CONF,		"use mysql CLIENT_COMPRESS feature." ),
	AP_INIT_FLAG(		"vdbh_CLIENT_SSL",				set_switch,		(void*) 4,	RSRC_CONF,		"use mysql CLIENT_SSL feature." ),
	AP_INIT_TAKE1(		"vdbh_MySQL_Database",			set_field,		(void*) 0,	RSRC_CONF,		"set mysql database." ),
	AP_INIT_TAKE1(		"vdbh_MySQL_Table",				set_field,		(void*) 1,	RSRC_CONF,		"set mysql table." ),
	AP_INIT_TAKE1(		"vdbh_MySQL_Host_Field",		set_field,		(void*) 2,	RSRC_CONF,		"set mysql server field." ),
	AP_INIT_TAKE1(		"vdbh_MySQL_Path_Field",		set_field,		(void*) 3,	RSRC_CONF,		"set mysql path field." ),
	AP_INIT_TAKE1(		"vdbh_MySQL_Host",				set_field,		(void*) 4,	RSRC_CONF,		"set mysql host." ),
	AP_INIT_TAKE1(		"vdbh_MySQL_Port",				set_port,		NULL,		RSRC_CONF,		"set mysql port." ),
	AP_INIT_TAKE1(		"vdbh_MySQL_Password",			set_field,		(void*) 5,	RSRC_CONF,		"set mysql password." ),
	AP_INIT_TAKE1(		"vdbh_MySQL_Username",			set_field,		(void*) 6,	RSRC_CONF,		"set mysql username." ),
	AP_INIT_TAKE1(		"vdbh_MySQL_Environment_Field",	set_field,		(void*) 7,	RSRC_CONF,		"set mysql environment table." ),
	AP_INIT_TAKE1(		"vdbh_Path_Prefix",				set_field,		(void*) 8,	RSRC_CONF,		"set path prefix." ),
	AP_INIT_TAKE1(		"vdbh_Default_Host",			set_field,		(void*) 9,	RSRC_CONF,		"set default host if HTTP/1.1 is not used." ),
	AP_INIT_ITERATE(	"vdbh_Declines",				set_decline,	NULL,		RSRC_CONF,		"declined path." ),
	{ NULL }
};

static void register_hooks(apr_pool_t *p)
{
	ap_hook_post_config(vdbh_init_handler, NULL, NULL, APR_HOOK_MIDDLE);
	ap_hook_translate_name(vdbh_translate_name, NULL, NULL, APR_HOOK_FIRST);
}

AP_DECLARE_DATA module vdbh_module = {
    STANDARD20_MODULE_STUFF,
    NULL,							/* create per-directory config structure */
    NULL,							/* merge per-directory config structures */
    vdbh_create_server_config,		/* create per-server config structure */
	vdbh_merge_server_config,		/* merge per-server config structures */
    vdbh_commands,					/* command apr_table_t */
    register_hooks					/* register hooks */
};

enum {
	f_on		= 1,
	f_compress	= 2,
	f_ssl		= 4
};

typedef struct {
	char *decline;
} decline_string;

typedef struct {
	ptrdiff_t		fl;
	char			*mysql_database;
	char			*mysql_table;
	char			*mysql_host_field;
	char			*mysql_path_field;
	char			*mysql_environment_field;
	
	char			*mysql_host;
	unsigned int	mysql_port;
	char			*mysql_username;
	char			*mysql_password;
	
	char			*path_prefix;
	char			*default_host;
	
	apr_array_header_t	*declines;
	
	MYSQL			*mysql;
} vdbh_config_rec;

static void* vdbh_create_server_config(apr_pool_t *p, server_rec *s)
{
	vdbh_config_rec *vdbhr = (vdbh_config_rec*) apr_pcalloc(p, sizeof(vdbh_config_rec));
	
	vdbhr->declines = apr_array_make(p, 0, sizeof(decline_string));
	
	return vdbhr;
}

static void *vdbh_merge_server_config(apr_pool_t *p, void *parentv, void *childv)
{
	vdbh_config_rec *parent = (vdbh_config_rec *) parentv;
	vdbh_config_rec *child = (vdbh_config_rec *) childv;
	vdbh_config_rec *conf = (vdbh_config_rec*) apr_pcalloc(p, sizeof(vdbh_config_rec));
	
	if (child->fl & f_on) {
		conf->fl						= child->fl;
		
		if (parent->fl & f_compress) {
			conf->fl |= f_compress;
		}
		
		if (parent->fl & f_ssl) {
			conf->fl |= f_ssl;
		}
		
		conf->mysql_database			= (child->mysql_database ? child->mysql_database : parent->mysql_database);
		conf->mysql_table				= (child->mysql_table ? child->mysql_table : parent->mysql_table);
		conf->mysql_host_field			= (child->mysql_host_field ? child->mysql_host_field : parent->mysql_host_field);
		conf->mysql_path_field			= (child->mysql_path_field ? child->mysql_path_field : parent->mysql_path_field);
		conf->mysql_environment_field	= (child->mysql_environment_field ? child->mysql_environment_field : parent->mysql_environment_field);
		conf->mysql_host				= (child->mysql_host ? child->mysql_host : parent->mysql_host);
		conf->mysql_port				= (child->mysql_port ? child->mysql_port : parent->mysql_port);
		conf->mysql_username			= (child->mysql_username ? child->mysql_username : parent->mysql_username);
		conf->mysql_password			= (child->mysql_password ? child->mysql_password : parent->mysql_password);
		
		conf->path_prefix				= (child->path_prefix ? child->path_prefix : parent->path_prefix);
		conf->default_host				= (child->default_host ? child->default_host : parent->default_host);

		conf->declines					= apr_array_copy(p, parent->declines);
		conf->declines					= apr_array_append(p, conf->declines, child->declines);
		
		conf->mysql						= NULL;
	}
	
	return conf;
}

static const char* set_switch(cmd_parms *parms, void *mconfig, int flag)
{
	ptrdiff_t pos = (ptrdiff_t) parms->info;
	vdbh_config_rec *vdbhr = (vdbh_config_rec*) ap_get_module_config(parms->server->module_config, &vdbh_module);
	
	if (flag) {
		vdbhr->fl |= pos;
	}
	else {
		vdbhr->fl &= ~pos;
	}
	
	return NULL;
}

static const char* set_field(cmd_parms *parms, void *mconfig, const char *arg)
{
	ptrdiff_t pos = (ptrdiff_t) parms->info;
	vdbh_config_rec *vdbhr = (vdbh_config_rec*) ap_get_module_config(parms->server->module_config, &vdbh_module);
	
	switch (pos) {
	  case 0:	vdbhr->mysql_database = apr_pstrdup(parms->pool, arg);				break;
	  case 1:	vdbhr->mysql_table = apr_pstrdup(parms->pool, arg);					break;
	  case 2:	vdbhr->mysql_host_field = apr_pstrdup(parms->pool, arg);			break;
	  case 3:	vdbhr->mysql_path_field = apr_pstrdup(parms->pool, arg);			break;
	  case 4:	vdbhr->mysql_host = apr_pstrdup(parms->pool, arg);					break;
	  case 5:	vdbhr->mysql_password = apr_pstrdup(parms->pool, arg);				break;
	  case 6:	vdbhr->mysql_username = apr_pstrdup(parms->pool, arg);				break;
	  case 7:	vdbhr->mysql_environment_field = apr_pstrdup(parms->pool, arg);		break;
	  case 8:	vdbhr->path_prefix = apr_pstrdup(parms->pool, arg);					break;
	  case 9:	vdbhr->default_host = apr_pstrdup(parms->pool, arg);				break;
	}
	
	return NULL;
}

static const char* set_port(cmd_parms *parms, void *mconfig, const char *arg)
{
	ptrdiff_t pos = (ptrdiff_t) parms->info;
	vdbh_config_rec *vdbhr = (vdbh_config_rec*) ap_get_module_config(parms->server->module_config, &vdbh_module);

	vdbhr->mysql_port = (unsigned int) atoi(arg);
	
	return NULL;
}

static const char* set_decline(cmd_parms *parms, void *mconfig, const char *arg)
{
	ptrdiff_t pos = (ptrdiff_t) parms->info;
	vdbh_config_rec *vdbhr = (vdbh_config_rec*) ap_get_module_config(parms->server->module_config, &vdbh_module);
	decline_string *d = apr_array_push(vdbhr->declines);

	d->decline = apr_pstrdup(parms->pool, arg);
		
	return NULL;
}

#ifndef CLIENT_COMPRESS
# define CLIENT_COMPRESS 0
#endif

#ifndef CLIENT_SSL
# define CLIENT_SSL 0
#endif

static char* get_path(request_rec *r, const char *host, char **env, vdbh_config_rec *vdbhr)
{
	MYSQL_RES *res;
	MYSQL_ROW row;
	char *qstr;
	char *rstr;
	
	if (!vdbhr->mysql) {
		unsigned int client_flags = 0;
		
		if (!(vdbhr->mysql = mysql_init(NULL))) {
			ap_log_error(APLOG_MARK, APLOG_ERR|APLOG_NOERRNO, 0, r->server, "vdbh: get_path: unable to allocate MYSQL connection.");
	
			return NULL;
		}
		
		if (vdbhr->fl & f_compress) {
			client_flags |= CLIENT_COMPRESS;
		}
		
		if (vdbhr->fl & f_ssl) {
			client_flags |= CLIENT_SSL;
		}
		
		if (!mysql_real_connect(vdbhr->mysql, vdbhr->mysql_host, vdbhr->mysql_username, vdbhr->mysql_password, vdbhr->mysql_database, vdbhr->mysql_port, NULL, client_flags)) {
			ap_log_error(APLOG_MARK, APLOG_ERR|APLOG_NOERRNO, 0, r->server, "vdbh: get_path: unable to connect to database: %s.", mysql_error(vdbhr->mysql));
			mysql_close(vdbhr->mysql);
			vdbhr->mysql = NULL;
	
			return NULL;
		}
	}
	
	if (vdbhr->mysql_environment_field) {
		qstr = apr_psprintf(r->pool, "SELECT %s,%s FROM %s WHERE %s = '%s'", vdbhr->mysql_path_field, vdbhr->mysql_environment_field, vdbhr->mysql_table, vdbhr->mysql_host_field, host);
	}
	else {
		qstr = apr_psprintf(r->pool, "SELECT %s FROM %s WHERE %s = '%s'", vdbhr->mysql_path_field, vdbhr->mysql_table, vdbhr->mysql_host_field, host);
	}
	
	if (mysql_real_query(vdbhr->mysql, qstr, strlen(qstr))) {
		ap_log_error(APLOG_MARK, APLOG_ERR|APLOG_NOERRNO, 0, r->server, "vdbh: get_path: %s/%s", mysql_error(vdbhr->mysql), host);
		mysql_close(vdbhr->mysql);
		vdbhr->mysql = NULL;
		
		return NULL;
	}
	
	if (!(res = mysql_store_result(vdbhr->mysql))) {
		ap_log_error(APLOG_MARK, APLOG_ERR|APLOG_NOERRNO, 0, r->server, "vdbh: get_path: %s/%s", mysql_error(vdbhr->mysql), host);
		mysql_close(vdbhr->mysql);
		vdbhr->mysql = NULL;
		
		return NULL;
	}
	
	switch (mysql_num_rows(res)) {
	  case 1:	break;
	  case 0:
		ap_log_error(APLOG_MARK, APLOG_ERR|APLOG_NOERRNO, 0, r->server, "vdbh: get_path: no results for %s", host);
		mysql_free_result(res);
		mysql_close(vdbhr->mysql);
		vdbhr->mysql = NULL;
		
		return NULL;
	  default:
		ap_log_error(APLOG_MARK, APLOG_ERR|APLOG_NOERRNO, 0, r->server, "vdbh: get_path: %s has more than 1 server row, failing.", host);
		
		return NULL;
	}
	
	if (!(row = mysql_fetch_row(res))) {
		ap_log_error(APLOG_MARK, APLOG_ERR|APLOG_NOERRNO, 0, r->server, "vdbh: get_path: %s/%s", mysql_error(vdbhr->mysql), host);
		mysql_free_result(res);
		mysql_close(vdbhr->mysql);
		vdbhr->mysql = NULL;
		
		return NULL;
	}
	
	rstr = apr_pstrdup(r->pool, row[0]);
	
	if (vdbhr->mysql_environment_field) {
		*env = apr_pstrdup(r->pool, row[1]);
	}
	
	mysql_free_result(res);

	return rstr;
}

static int vdbh_init_handler(apr_pool_t *pconf, apr_pool_t *plog, apr_pool_t *ptemp, server_rec *s)
{
	ap_add_version_component(pconf, "mod_vdbh/1.0.3");
	
	return OK;
}

static int vdbh_translate_name(request_rec *r)
{
	vdbh_config_rec *vdbhr = (vdbh_config_rec*) ap_get_module_config(r->server->module_config, &vdbh_module);
	const char *host;
	char *path;
	char *env = NULL;
	char *ptr;
	decline_string *d = (decline_string*) vdbhr->declines->elts;
	int i;
	
	if (!(vdbhr->fl & f_on)) {
		ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vdbh_translate_name: declined http://%s%s module is not configured for this server", apr_table_get(r->headers_in, "Host"), r->uri);
		return DECLINED;
	}
	
	// I think this was for a 1.3 work around
	if (r->uri[0] != '/') {
		ap_log_error(APLOG_MARK, APLOG_ALERT, 0, r->server, "vdbh_translate_name: declined %s no leading `/'", r->uri);
		return DECLINED;
	}
	
	for (i = 0; i < vdbhr->declines->nelts; i++) {
		if (match(d[i].decline, r->uri)) {
			ap_log_error(APLOG_MARK, APLOG_NOTICE, 0, r->server, "vdbh_translate_name: declined %s", r->uri);
			return DECLINED;
		}
	}
	
	if (!(host = apr_table_get(r->headers_in, "Host"))) {
		if (!vdbhr->default_host) {
			ap_log_error(APLOG_MARK, APLOG_ALERT, 0, r->server, "vdbh_translate_name: no host found (non HTTP/1.1 request, no default set) %s", host);
			return DECLINED;
		}
		else {
			host = vdbhr->default_host;
		}
	}

	if (ptr = strchr(host,':')) {
		*ptr = '\0';
	}
		
	if (!(path = get_path(r, host, &env, vdbhr))) {
		ap_log_error(APLOG_MARK, APLOG_NOTICE, 0, r->server, "vdbh_translate_name: no host found in database for %s", host);
		return DECLINED;
	}
	
	apr_table_set(r->subprocess_env, "VDBH_HOST", host);
	apr_table_set(r->subprocess_env, "VDBH_PATH", path);
	apr_table_set(r->subprocess_env, "VDBH_ENVIRONMENT", env ? env : "");
	
	r->filename = apr_psprintf(r->pool, "%s%s%s", vdbhr->path_prefix ? vdbhr->path_prefix : "", path, r->uri);
	ap_log_error(APLOG_MARK, APLOG_DEBUG, 0, r->server, "vdbh_translate_name: translated http://%s%s to file %s", host, r->uri, r->filename);
	
	return OK;
}
