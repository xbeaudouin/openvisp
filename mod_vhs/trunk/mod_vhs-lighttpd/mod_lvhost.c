/*
 *
 * mod_lvhost.c
 * Copyright (c) 2008, Stephane Camberlin
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions 
 * are met:
 *
 * - Redistributions of source code must retain the above copyright notice, 
 *   this list of conditions and the following disclaimer.
 *
 * - Redistributions in binary form must reproduce the above copyright 
 *   notice, this list of conditions and the following disclaimer in the 
 *   documentation and/or other materials provided with the distribution.
 *
 * - Neither the name of the 'incremental' nor the names of its 
 *   contributors may be used to endorse or promote products derived 
 *   from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT 
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS 
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE 
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (
 * INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS 
 * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

#include <ctype.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <time.h>
#include <assert.h>

#include "base.h"
#include "log.h"
#include "buffer.h"
#include "stat_cache.h"

#include "plugin.h"

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <ldap.h>
#include <lber.h>

#define DEBUG 1
#define CACHE_LIFETIME 3600

#define PATCH_OPTION(x) \
        p->conf.x = s->x;

/** 
 * Common lvhost related data.
 * 
 * Configuration shared by all handled requests within a config_context.
 */
typedef struct
{
	LDAP	*ldap_c;	/** LDAP session handle used for all queries. */
	buffer	*ldap_uri;	/** URI in form that openldap library expects it. */
	unsigned short	ldap_starttls;
	buffer	*sasl_user;	/** SASL auth user ( = authz user). */
	buffer	*sasl_pass;	
	buffer	*sasl_realm;
	buffer	*sasl_mech;
	buffer	*ldap_binddn;	/** If set, use simple binds. */
	buffer	*ldap_password;	/** Simple bind password. */
	buffer	*ldap_base;		/** The search root.  Scope is sub */

	/** The filter used to search for document root,
	 *  given an input of server name.  
	 *  Default: (&(objectClass=vhost)(serverName=?))
	 */
	buffer	*ldap_filter;

	/** The attribute representing docroot.
	 *  Default: documentRoot
	 */
	buffer	*ldap_attribute;

	unsigned short ldap_persist;	/** If use, use refreshAndPersist */
  
	buffer	*ldap_pre;
	buffer	*ldap_post;
  
	int		free_me;

} lvhost_plugin_config;

/* cache struct.  Maybe use lightys arrays later */
typedef struct {

	buffer* server_name;
	buffer* docroot;
	time_t birth;

} doc_cache_t;

typedef struct {

	PLUGIN_DATA;
  
	buffer  *docroot;
	buffer  *tmp_buf;
	doc_cache_t **doc_cache;
	unsigned int cache_size;

	lvhost_plugin_config **config_storage;
	lvhost_plugin_config conf;

} plugin_data;

INIT_FUNC(mod_lvhost_init) {

	plugin_data *p;

	p = (plugin_data*)malloc( sizeof(*p) );

	if( p )
	{
		p->tmp_buf = buffer_init();
		p->docroot = buffer_init();
		p->doc_cache = malloc( 0 );
		p->cache_size = 0;
	}

	return p;
}

FREE_FUNC(mod_lvhost_cleanup) {

	plugin_data *p = p_d;

	UNUSED(srv);

	if (!p) return HANDLER_GO_ON;

	if (p->config_storage) {
    
		size_t i, j;

		for (i = 0; i < srv->config_context->used; i++)
		{  
			lvhost_plugin_config *s = p->config_storage[i];
      
			if (!s) continue;
 
			if (s->free_me) ldap_unbind_ext(s->ldap_c, NULL, NULL);

	        /* Free all the buffers for this config_context */
    	    buffer *bufs[] = {	s->ldap_uri,		s->sasl_user,
								s->sasl_pass,		s->sasl_mech,
								s->sasl_realm,		s->ldap_binddn,
            	                s->ldap_password,	s->ldap_base,
								s->ldap_filter,		s->ldap_attribute,
								s->ldap_pre,		s->ldap_post };

			for(j = 0; j * sizeof(buffer*) < sizeof(bufs); j++)
				buffer_free(bufs[j]);
	      
			free(s);
		} 
	      
		free(p->config_storage);
	}

	buffer_free(p->docroot);
	free(p->doc_cache);

	free(p);

	return HANDLER_GO_ON;
}

SETDEFAULTS_FUNC(mod_lvhost_set_defaults) {

	plugin_data *p = p_d;
  
	size_t i, j, k;
	int err;
  
	struct berval cred;

	int ldap_version = LDAP_VERSION3;

	/* 
	 * List of config values to be read from the lighttpd configfile.
	 * Format is { confstring, destination memory, type, scope }
	 * Please place T_CONFIG_STRINGs at top.
	 */
	config_values_t cv[] = {
  
		{ "lvhost.uri",				NULL,
									T_CONFIG_STRING,
									T_CONFIG_SCOPE_SERVER },

		{ "lvhost.sasl_mech",		NULL,
									T_CONFIG_STRING, 
									T_CONFIG_SCOPE_SERVER },

	 	{ "lvhost.sasl_user",		NULL,
									T_CONFIG_STRING, 
									T_CONFIG_SCOPE_SERVER },

		{ "lvhost.sasl_realm",		NULL,
									T_CONFIG_STRING,
									T_CONFIG_SCOPE_SERVER },

	    { "lvhost.sasl_pass",		NULL,
    	                            T_CONFIG_STRING,
        	                        T_CONFIG_SCOPE_SERVER },

		{ "lvhost.binddn",			NULL,
									T_CONFIG_STRING,
									T_CONFIG_SCOPE_SERVER },

		{ "lvhost.password",		NULL,
									T_CONFIG_STRING,
									T_CONFIG_SCOPE_SERVER },

		{ "lvhost.base",			NULL,
									T_CONFIG_STRING,
									T_CONFIG_SCOPE_SERVER },

		{ "lvhost.filter",			NULL,
									T_CONFIG_STRING,
									T_CONFIG_SCOPE_SERVER },

		{ "lvhost.attribute",		NULL,
									T_CONFIG_STRING,
									T_CONFIG_SCOPE_SERVER },

        { "lvhost.starttls",        NULL,
                                    T_CONFIG_BOOLEAN,
                                    T_CONFIG_SCOPE_SERVER },

		{ "lvhost.persistent",		NULL,
									T_CONFIG_BOOLEAN,
									T_CONFIG_SCOPE_SERVER },

		{ NULL,						NULL,
									T_CONFIG_UNSET,
									T_CONFIG_SCOPE_UNSET }

	};
  
	if ( !p ) return HANDLER_ERROR;
  
	/* 
	 * Allocate memory for pointers to instances of lvhost_plugin_config
	 * that will be created one per config_context.
	 */
	p->config_storage = 
			(lvhost_plugin_config**)malloc(	srv->config_context->used
											* sizeof(lvhost_plugin_config*) );

	if( !(p->config_storage) ) return HANDLER_ERROR;

	/*
	 * If we managed to allocate space for the config points,
	 * continue with initiate these pointers.
	 */
	for (i = 0; i < srv->config_context->used; i++)
	{
		buffer *myfilter = buffer_init();
		char *qmark = 0;
 
		/* The lvhost plugin config for the i-th config_context */

		lvhost_plugin_config *s;
		s = (lvhost_plugin_config *)malloc( sizeof(*s) );
		if( !s ) return HANDLER_ERROR;

		/* Initialize all the buffers for this config_context */
		buffer **bufs[] = {	&s->ldap_uri, 		&s->sasl_mech, 	&s->sasl_user,
							&s->sasl_realm,		&s->sasl_pass, 	&s->ldap_binddn,
							&s->ldap_password,	&s->ldap_base,	&s->ldap_filter,
							&s->ldap_attribute };

		/* Init these separately */
		s->ldap_pre = buffer_init();
		s->ldap_post = buffer_init();

		for(j = 0; j * sizeof(buffer**) < sizeof(bufs); j++)
		{
#if DEBUG==2
			log_error_write(	srv, __FILE__, __LINE__, "ss",
								"Initializing buffer for", cv[j].key );
#endif
			*bufs[j] = buffer_init();
		}

		/*
		 * Point the destinations of cv to our config storage to
		 * be filled by config_insert_values_global.  dests must be the same
		 * order as they are in cv, so make sure bufs and bools are correct
		 */
		unsigned short *bools[] = { &s->ldap_starttls, &s->ldap_persist };
		void** dests = (void**)malloc( sizeof(bufs) + sizeof(bools) );
		if(!dests) return HANDLER_ERROR;

		/* Append bools to the buffers */
		for(j = 0; j * sizeof(buffer**) < sizeof(bufs); j++)
			dests[j] = *bufs[j];
		memcpy(	dests + j, bools, sizeof(bools) );

		/* nDests should be the same as the length of cv - 1 */
		size_t nDests = sizeof(cv) / sizeof(config_values_t) - 1;
		assert( (nDests * sizeof(void*)) == (sizeof(bools) + sizeof(bufs)) );

		for(j = 0; cv[j].key; j++)
		{
#if DEBUG==2
			log_error_write(	srv, __FILE__, __LINE__, "ss",
								"Setting destination for", cv[j].key );
#endif
			assert( dests[j] );
			cv[j].destination = dests[j];
		}

		p->config_storage[i] = s;

		if (config_insert_values_global(srv, 
				((data_config *)srv->config_context->data[i])->value, cv))
		{
			return HANDLER_ERROR;
		}

#if DEBUG==2
		/* Print what we got from lighttpd config. */
		for(j = 0; cv[j].key; j++)
		{
			switch( cv[j].type )
			{
				case T_CONFIG_STRING:
					log_error_write(	srv, __FILE__, __LINE__, "ssb",
										"lvhost config: ", cv[j].key, 
										(buffer*)dests[j] 				);
					break;

				case T_CONFIG_BOOLEAN:
					log_error_write(	srv, __FILE__, __LINE__, "ssd",
										"lvhost config: ", cv[j].key,
										*((unsigned short*)cv[j].destination) );
					break;
			default:
					log_error_write(	srv, __FILE__, __LINE__, "ssd",
										"lvhost config: ", cv[j].key,
										*((int*)cv[j].destination)		);
			};
		}
#endif /* DEBUG */

		/*
		 * Initiate default values for certain buffers
		 */

		/**
		 * Structure containing a buffer - stringi default pairing
		 */
		typedef struct { buffer* b; const char* d; } buffer_default_t;
		buffer_default_t defaults[] = 
			{
				{ s->ldap_filter,		"(&(objectClass=vhost)(serverName=?))" 	},
				{ s->ldap_attribute,	"documentRoot"							},
				{ s->sasl_mech,			"PLAIN"									}
			};

		for(j = 0; j * sizeof(buffer_default_t) < sizeof(defaults); j++)
			if( buffer_is_empty(defaults[j].b) )
				buffer_copy_string( defaults[j].b, defaults[j].d );

		/*
		 * Check that we have none empty buffers on
		 * the following variables.
		 */
		buffer* required_buffers[] = { 	s->ldap_uri, s->ldap_base,
										s->ldap_filter, s->ldap_attribute };

		for(j = 0; j * sizeof(buffer*) < sizeof(required_buffers); j++)
		{
			/* Get the index of required_buffers, as seen in cv */
			for( k = 0; k < nDests; k++ )
				if( required_buffers[j] == dests[k] ) break;

			/* Should have gotten a match */
			assert( k < nDests );

			if( buffer_is_empty(required_buffers[j]) )
			{
				log_error_write(	srv, __FILE__, __LINE__, "ss", 
									"Missing required option:", cv[k].key );
				/* TODO: readd this line maybe
				 * I don't seem to understand config_contexts 
				 * yet to leave this out
				 * return HANDLER_ERROR;
				 */

				return HANDLER_GO_ON;
			}
		}

		/*
		 * Next we split the ldap_filter string in two
		 * at the contained question mark if it has one,
		 * and put the two halfs in ldap_pre and ldap_post maybe
		 */
		buffer_copy_string_buffer(myfilter, s->ldap_filter);

		if( (qmark = index(myfilter->ptr, '?')) )
		{
			*qmark = '\0';
			buffer_copy_string(s->ldap_pre, myfilter->ptr);
			buffer_copy_string(s->ldap_post, qmark+1);
		}
		else
		{
			buffer_copy_string_buffer(s->ldap_pre, myfilter);
		}
    
		buffer_free(myfilter);
 
		s->free_me = 0;

		/*
		 * Now we setup the ldap session to be referenced by ldap_c
		 */
		if ((err = ldap_initialize( &s->ldap_c,
									s->ldap_uri->ptr )) != LDAP_SUCCESS) {
			log_error_write(	srv, __FILE__, __LINE__, "ss", 
								"ldap_initialize() failed, exiting... :", 
								ldap_err2string(err)						);
			return HANDLER_ERROR;
		}
    
		s->free_me = 1;

		/* Start TLS if requested */
		if(    s->ldap_starttls 
			&& ( err = ldap_start_tls_s( s->ldap_c, NULL, NULL ) ) )
		{
            log_error_write(    srv, __FILE__, __LINE__, "ss",
                                "ldap_start_tls_s() failed, exiting... :",
                                ldap_err2string(err)                        );
			return HANDLER_ERROR;
		}

		ldap_set_option( s->ldap_c, LDAP_OPT_PROTOCOL_VERSION, &ldap_version );

		/*
		 * If binddn was given, assume we want  a simple bind.
		 * For now let us perform all ldap operations syncronously.
		 */
		if( !buffer_is_empty(s->ldap_binddn) )
		{
	        if ((err = ldap_simple_bind_s(  s->ldap_c,
    	                                    s->ldap_binddn->ptr,
			                                s->ldap_password->ptr)) != LDAP_SUCCESS)
			{
        	    log_error_write(    srv, __FILE__, __LINE__, "ss",
	                                "ldap_simple_bind_s() failed, exiting... :",
    	                            ldap_err2string(err)                        );
        	    return HANDLER_ERROR;
			}

			/* We were successful.  No need to do anything else */
			return HANDLER_GO_ON;
		}

		if( !buffer_is_empty(s->sasl_pass) )
		{
			cred.bv_val = strdup(s->sasl_pass->ptr);
			cred.bv_len = strlen(s->sasl_pass->ptr);
		} else {
			cred.bv_val = NULL;
			cred.bv_len = 0;
		}
  
		if ((err = ldap_sasl_bind_s(	s->ldap_c, 
										s->sasl_user->ptr,
										s->sasl_mech->ptr,
										&cred, NULL, NULL, NULL	)) != LDAP_SUCCESS)
		{
			log_error_write(	srv, __FILE__, __LINE__, "ss", 
								"ldap_sasl_bind_s() failed, exiting... :", 
								ldap_err2string(err)						);
			return HANDLER_ERROR;
		}
	}

	return HANDLER_GO_ON;
}

static int mod_lvhost_patch_connection(	server *srv, 
										connection *con, 
										plugin_data *p	)
{
	size_t i;
	lvhost_plugin_config *s = p->config_storage[0];

	PATCH_OPTION(ldap_base);
	PATCH_OPTION(ldap_attribute);
	PATCH_OPTION(ldap_pre);
	PATCH_OPTION(ldap_post);
	PATCH_OPTION(ldap_c);

	for (i = 1; i < srv->config_context->used; i++)
	{
		data_config *dc = (data_config *)srv->config_context->data[i];
		s = p->config_storage[i];

		if (!config_check_cond(srv, con, dc)) continue;

		PATCH_OPTION(ldap_base);
		PATCH_OPTION(ldap_attribute);
		PATCH_OPTION(ldap_pre);
		PATCH_OPTION(ldap_post);
		PATCH_OPTION(ldap_c);
	}

	return 0;
}

CONNECTION_FUNC(mod_lvhost_handle_docroot)
{
	plugin_data *p = p_d;

	int n = 0;
	size_t i;
  
	LDAPMessage *msg, *entry;
	struct berval **vals;
	int ldap_scope = LDAP_SCOPE_SUBTREE;

	/* Fold if no host specified */
	if (!con->uri.authority->used)
	{
		log_error_write(	srv, __FILE__, __LINE__, 
							"s", "Exiting: no host header."	);
		return HANDLER_GO_ON;
	}

	/* Find the requested server name in cache */
	doc_cache_t *cache_entry = 0;

#if DEBUG==2
	log_error_write( 	srv, __FILE__, __LINE__, "sd",
						"Checking cache of size", p->cache_size );
#endif

	for(i = 0; i < p->cache_size; i++)
	{
		if(buffer_is_equal(p->doc_cache[i]->server_name, con->uri.authority))
		{
			cache_entry = p->doc_cache[i]; n=1;
			break;
		}
	}

	if( cache_entry && ( (time(0) - cache_entry->birth ) < CACHE_LIFETIME ) )
	{
		log_error_write(	srv, __FILE__, __LINE__, "sb", 
							"Using cache for", cache_entry->server_name	);
        buffer_copy_string_buffer(con->server_name, cache_entry->server_name);
        buffer_copy_string_buffer(con->physical.doc_root, cache_entry->docroot);
		return HANDLER_GO_ON;
	}

	mod_lvhost_patch_connection(srv, con, p);

	/* Perhaps ldap_c has become invalid?  Create a new session? */
	if (!p->conf.ldap_c)
	{
		log_error_write(	srv, __FILE__, __LINE__, "s", 
							"Exiting: no ldap handler..."	);
		return HANDLER_GO_ON;
	}
  
	buffer_copy_string_buffer(p->tmp_buf, p->conf.ldap_pre);
  
	if (p->conf.ldap_post->used)
	{
		buffer_append_string_buffer(p->tmp_buf, con->uri.authority);
		buffer_append_string_buffer(p->tmp_buf, p->conf.ldap_post);
	}

	if (ldap_search_ext_s(	p->conf.ldap_c, p->conf.ldap_base->ptr,
							ldap_scope,	p->tmp_buf->ptr, NULL,
							0, NULL, NULL, NULL, 0, &msg	) != LDAP_SUCCESS)
	{
		log_error_write(	srv, __FILE__, __LINE__, "sb", 
							"Exiting: ldap_search_ext_s failed:", p->tmp_buf);
	}
	else
	{
		/* If search successful, update the cache. */
		n = ldap_count_entries(p->conf.ldap_c, msg);
		if( !n ) return HANDLER_GO_ON;
		if( n>1 )
		{
			log_error_write(	srv, __FILE__, __LINE__, "sbs", 
								"Duplicate entry for filter:", p->tmp_buf,
								": Just using the first"					);
		}

		entry = ldap_first_entry(p->conf.ldap_c, msg);

		vals = ldap_get_values_len(	p->conf.ldap_c, entry, 
									p->conf.ldap_attribute->ptr );

		if( vals )
		{
			stat_cache_entry *sce;
			buffer_copy_string(p->docroot, vals[0]->bv_val);

			if (HANDLER_ERROR 
					== stat_cache_get_entry(srv, con, p->docroot, &sce))
			{
				log_error_write(	srv, __FILE__, __LINE__, "sb", 
									strerror(errno), p->docroot		);
				return HANDLER_GO_ON;
			}

			if (!S_ISDIR(sce->st.st_mode))
			{
				log_error_write(	srv, __FILE__, __LINE__, "sb", 
									"Not a directory", p->docroot	);
		    	return HANDLER_GO_ON;
			}


			if( !cache_entry )
			{
				cache_entry = malloc( sizeof(doc_cache_t) );
				if(!cache_entry) return HANDLER_ERROR;

				cache_entry->server_name = buffer_init();
				cache_entry->docroot = buffer_init();

				buffer_copy_string_buffer( 	cache_entry->server_name, 
											con->uri.authority );

				p->doc_cache = realloc( p->doc_cache, 
										(p->cache_size + 1) 
										* sizeof(doc_cache_t*) );

				if(!p->doc_cache) return HANDLER_ERROR;

				p->doc_cache[p->cache_size] = cache_entry;
				p->cache_size = p->cache_size + 1;

#if DEBUG==2
				log_error_write( 	srv, __FILE__, __LINE__, "sd", 
									"Adding doc root to cache, new size:", 
									p->cache_size							);
#endif

			}

			cache_entry->birth = time(0);
			buffer_copy_string_buffer(cache_entry->docroot, p->docroot);
		}
		else
		{
			/* We should remove the cache perhaps? Leave it for now. */
		}

		ldap_value_free_len(vals);
		ldap_msgfree(msg);
	}

	/* If we haven't gotten cache_entry by now, we are stumped. */
	if( !cache_entry ) return HANDLER_GO_ON;

	buffer_copy_string_buffer(con->server_name, cache_entry->server_name);
	buffer_copy_string_buffer(con->physical.doc_root, cache_entry->docroot);

#ifdef DEBUG
	log_error_write(    srv, __FILE__, __LINE__, "sb",
						"Server name:", con->server_name    );
	log_error_write(	srv, __FILE__, __LINE__, "sb", 
						"Document root:", con->physical.doc_root	);
#endif

	return HANDLER_GO_ON;
}

int mod_lvhost_plugin_init(plugin *p) {

	p->version                  = LIGHTTPD_VERSION_ID;
	p->name                     = buffer_init_string("lvhost");
	p->init                     = mod_lvhost_init;
	p->cleanup                  = mod_lvhost_cleanup;
	p->set_defaults             = mod_lvhost_set_defaults;
	p->handle_docroot           = mod_lvhost_handle_docroot;

	p->data                     = NULL;

	return 0;

}


