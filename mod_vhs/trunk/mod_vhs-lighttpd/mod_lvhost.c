/*
 * mod_lvhost.c
 * Copyright (C) 2008-2009, Stephane Camberlin, Harry Waye.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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

#define DEFAULT_CACHE_LIFETIME 60

#define PATCH_OPTION(x) \
  p->conf.x = s->x;

/* 
 * Common lvhost related data.
 * 
 * Configuration shared by all handled requests within a config_context.
 */
typedef struct {

  LDAP *ldap_c;         /* LDAP session handle used for all queries. */
  buffer *ldap_uri;     /* URI in form that openldap library expects it. */
  buffer *ldap_dn;  /* If set, use simple binds. */
  buffer *ldap_pass;    /* Simple bind password. */
  buffer *ldap_base;    /* The search root.  Scope is sub. */
  buffer *sasl_user;    /* SASL auth user ( = authz user). */
  buffer *sasl_pass;  
  buffer *sasl_realm;
  buffer *sasl_mech;
  unsigned short ldap_starttls;

  /* The filter used to search for document root,
   *  given an input of server name.  
   *  Default: (&(objectClass=vhost)(serverName=?))
   */
  buffer *ldap_filter;

  /* The key representing docroot.
   *  Default: documentRoot
   */
  buffer *ldap_key;

  unsigned short  free_me;
  unsigned short ldap_persist;  /* If use, use refreshAndPersist. */
  int cache_lifetime;  /* If use, replace DEFAULT_CACHE_LIFETIME */
  
  buffer *ldap_pre;
  buffer *ldap_post;

} lvhost_plugin_config;

/* cache struct.  Maybe use lightys arrays later. */
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

  p = (plugin_data*)malloc(sizeof(*p));

  if(p)
  {
    p->tmp_buf = buffer_init();
    p->docroot = buffer_init();
    p->doc_cache = malloc(0);
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
      
      /* Free all the buffers for this config_context. */
      buffer *bufs[] = { 
        s->ldap_uri, s->sasl_user,
        s->sasl_pass, s->sasl_mech,
        s->sasl_realm, s->ldap_dn,
        s->ldap_pass, s->ldap_base,
        s->ldap_filter,  s->ldap_key,
        s->ldap_pre, s->ldap_post 
        };

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
   * Format is { confstring, destination memory, type, scope }.
   * Please place T_CONFIG_STRINGs at top.
   */
  config_values_t cv[] = {
  
    { "lvhost.uri",  NULL,
      T_CONFIG_STRING,
      T_CONFIG_SCOPE_SERVER },

    { "lvhost.sasl_mech", NULL,
      T_CONFIG_STRING, 
      T_CONFIG_SCOPE_SERVER },

    { "lvhost.sasl_user", NULL,
      T_CONFIG_STRING, 
      T_CONFIG_SCOPE_SERVER },

    { "lvhost.sasl_realm", NULL,
      T_CONFIG_STRING,
      T_CONFIG_SCOPE_SERVER },

    { "lvhost.sasl_pass", NULL,
      T_CONFIG_STRING,
      T_CONFIG_SCOPE_SERVER },

    { "lvhost.dn", NULL,
      T_CONFIG_STRING,
      T_CONFIG_SCOPE_SERVER },

    { "lvhost.pass", NULL,
      T_CONFIG_STRING,
      T_CONFIG_SCOPE_SERVER },

    { "lvhost.base", NULL,
      T_CONFIG_STRING,
      T_CONFIG_SCOPE_SERVER },

    { "lvhost.filter", NULL,
      T_CONFIG_STRING,
      T_CONFIG_SCOPE_SERVER },

    { "lvhost.key", NULL,
      T_CONFIG_STRING,
      T_CONFIG_SCOPE_SERVER },
    
    { "lvhost.starttls", NULL,
      T_CONFIG_BOOLEAN,
      T_CONFIG_SCOPE_SERVER },

    { "lvhost.persistent", NULL,
      T_CONFIG_BOOLEAN,
      T_CONFIG_SCOPE_SERVER },
    
    { NULL, NULL,
      T_CONFIG_UNSET,
      T_CONFIG_SCOPE_UNSET }
  };
  
  if (!p) return HANDLER_ERROR;
  
  /* 
   * Allocate memory for pointers to instances of lvhost_plugin_config
   * that will be created one per config_context.
   */
  p->config_storage = (lvhost_plugin_config**) malloc(srv->config_context->used * sizeof(lvhost_plugin_config*));

  if(!(p->config_storage)) return HANDLER_ERROR;

  /*
   * If we managed to allocate space for the config points,
   * continue with initiate these pointers.
   */
  for (i = 0; i < srv->config_context->used; i++)
  {
    buffer *myfilter = buffer_init();
    char *qmark = 0;
 
    /* The lvhost plugin config for the i-th config_context. */
    lvhost_plugin_config *s;
    s = (lvhost_plugin_config *)malloc(sizeof(*s));
    if(!s) return HANDLER_ERROR;

    /* Initialize all the buffers for this config_context. */
    buffer **bufs[] = {  
      &s->ldap_uri, &s->sasl_mech, &s->sasl_user,
      &s->sasl_realm, &s->sasl_pass, &s->ldap_dn,
      &s->ldap_pass, &s->ldap_base, &s->ldap_filter,
      &s->ldap_key 
    };

    /* Init these separately */
    s->ldap_pre = buffer_init();
    s->ldap_post = buffer_init();

    for(j = 0; j * sizeof(buffer**) < sizeof(bufs); j++)
    {
#if DEBUG==2
      log_error_write(srv, __FILE__, __LINE__, "ss", "Initializing buffer for", cv[j].key);
#endif
      *bufs[j] = buffer_init();
    }
    
    /*
     * Point the destinations of cv to our config storage to
     * be filled by config_insert_values_global.  dests must be the same
     * order as they are in cv, so make sure bufs and bools are correct.
     */
    unsigned short *bools[] = { &s->ldap_starttls, &s->ldap_persist };
    void** dests = (void**) malloc(sizeof(bufs) + sizeof(bools));
    if(!dests) return HANDLER_ERROR;

    /* Append bools to the buffers. */
    for(j = 0; j * sizeof(buffer**) < sizeof(bufs); j++)
      dests[j] = *bufs[j];
    memcpy( dests + j, bools, sizeof(bools) );

    /* nDests should be the same as the length of cv - 1. */
    size_t nDests = sizeof(cv) / sizeof(config_values_t) - 1;
    assert((nDests * sizeof(void*)) == (sizeof(bools) + sizeof(bufs)));

    for(j = 0; cv[j].key; j++)
    {
#if DEBUG
      log_error_write(srv, __FILE__, __LINE__, "ss", "Setting destination for", cv[j].key);
#endif
      assert(dests[j]);
      cv[j].destination = dests[j];
    }

    p->config_storage[i] = s;

    if (config_insert_values_global(srv, ((data_config *)srv->config_context->data[i])->value, cv))
      return HANDLER_ERROR;

#if DEBUG
    /* Print what we got from lighttpd config. */
    for(j = 0; cv[j].key; j++)
    {
      switch(cv[j].type)
      {
        case T_CONFIG_STRING:
          log_error_write(srv, __FILE__, __LINE__, "ssb", cv[j].key, "=", (buffer*) dests[j]);
          break;

        case T_CONFIG_BOOLEAN:
          log_error_write(srv, __FILE__, __LINE__, "ssd", cv[j].key, "=", *((unsigned short*) cv[j].destination));
          break;

      default:
          log_error_write(srv, __FILE__, __LINE__, "ssd", cv[j].key, "=", *((int*)cv[j].destination));
      };
    }
#endif /* DEBUG */

    /*
     * Initiate default values for certain buffers.
     */

    /*
     * Structure containing a buffer - stringi default pairing.
     */
    typedef struct { buffer* b; const char* d; } buffer_default_t;
    buffer_default_t defaults[] = 
      {
        { s->ldap_filter, "(&(objectClass=vhost)(serverName=?))" },
        { s->ldap_key,    "documentRoot" },
        { s->sasl_mech,   "PLAIN" }
      };

    for(j = 0; j * sizeof(buffer_default_t) < sizeof(defaults); j++)
      if(buffer_is_empty(defaults[j].b))
        buffer_copy_string( defaults[j].b, defaults[j].d );

    /*
     * Check that we have none empty buffers on
     * the following variables.
     */
    buffer* required_buffers[] = { 
      s->ldap_uri, s->ldap_base,
      s->ldap_filter, s->ldap_key 
    };

    for(j = 0; j * sizeof(buffer*) < sizeof(required_buffers); j++)
    {
      /* Get the index of required_buffers, as seen in cv.*/
      for(k = 0; k < nDests; k++)
        if(required_buffers[j] == dests[k]) break;

      /* Should have gotten a match */
      assert(k < nDests);

      if(buffer_is_empty(required_buffers[j]))
      {
        log_error_write(srv, __FILE__, __LINE__, "ss", "Missing required option", cv[k].key);
        /* TODO: readd this line maybe
         * I don't seem to understand config_contexts yet to leave this out.
         * return HANDLER_ERROR;
         */

        return HANDLER_GO_ON;
      }
    }

    /*
     * Next we split the ldap_filter string in two
     * at the contained question mark if it has one,
     * and put the two halfs in ldap_pre and ldap_post maybe.
     */
    buffer_copy_string_buffer(myfilter, s->ldap_filter);

    if((qmark = index(myfilter->ptr, '?')))
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
     * Now we setup the ldap session to be referenced by ldap_c.
     */
    if ((err = ldap_initialize(&s->ldap_c, s->ldap_uri->ptr)) != LDAP_SUCCESS) 
    {
      log_error_write(srv, __FILE__, __LINE__, "ss", "ldap_initialize() failed, exiting... :", ldap_err2string(err));
      return HANDLER_ERROR;
    }
    
    s->free_me = 1;

    ldap_set_option(s->ldap_c, LDAP_OPT_PROTOCOL_VERSION, &ldap_version);

    /*
     * If dn was given, assume we want a simple bind.
     * For now let us perform all ldap operations syncronously.
     */
    if(!buffer_is_empty(s->ldap_dn))
    {
      if ((err = ldap_simple_bind_s(s->ldap_c, s->ldap_dn->ptr, s->ldap_pass->ptr)) != LDAP_SUCCESS)
      {
        log_error_write(srv, __FILE__, __LINE__, "ss", "ldap_simple_bind_s() failed, exiting... :", ldap_err2string(err));
        return HANDLER_ERROR;
      }
     
      if ((err = ldap_set_option(s->ldap_c, LDAP_OPT_RESTART, LDAP_OPT_ON)) != LDAP_SUCCESS)
      {
        log_error_write(srv, __FILE__, __LINE__, "ss", "Set LDAP_OPT_RESTART failed, exiting... :", ldap_err2string(err));
        return HANDLER_ERROR;
      }

      /* We were successful.  No need to do anything else. */
      return HANDLER_GO_ON;
    }

    if(!buffer_is_empty(s->sasl_pass))
    {
      cred.bv_val = strdup(s->sasl_pass->ptr);
      cred.bv_len = strlen(s->sasl_pass->ptr);
    } else {
      cred.bv_val = NULL;
      cred.bv_len = 0;
    }
  
    if ((err = ldap_sasl_bind_s(s->ldap_c, s->sasl_user->ptr, s->sasl_mech->ptr, &cred, NULL, NULL, NULL)) != LDAP_SUCCESS)
    {
      log_error_write(srv, __FILE__, __LINE__, "ss", "ldap_sasl_bind_s() failed, exiting... :", ldap_err2string(err));
      return HANDLER_ERROR;
    }

    if ((err = ldap_set_option(s->ldap_c, LDAP_OPT_RESTART, LDAP_OPT_ON)) != LDAP_SUCCESS)
    {
      log_error_write(srv, __FILE__, __LINE__, "ss", "Set LDAP_OPT_RESTART failed, exiting... :", ldap_err2string(err));
      return HANDLER_ERROR;
    }
  
  }

  return HANDLER_GO_ON;
}

static int mod_lvhost_patch_connection(server *srv, connection *con, plugin_data *p)
{

  size_t i, j;
  lvhost_plugin_config *s = p->config_storage[0];

  PATCH_OPTION(ldap_uri);
  PATCH_OPTION(ldap_dn);
  PATCH_OPTION(ldap_pass);
  PATCH_OPTION(ldap_base);
  PATCH_OPTION(ldap_filter);
  PATCH_OPTION(ldap_key);
  PATCH_OPTION(ldap_pre);
  PATCH_OPTION(ldap_post);
  PATCH_OPTION(ldap_c);


  for (i = 1; i < srv->config_context->used; i++)
  {
    data_config *dc = (data_config *)srv->config_context->data[i];
    s = p->config_storage[i];

    if (!config_check_cond(srv, con, dc)) continue;

    for (j = 0; j < dc->value->used; j++) {
    
      data_unset *du = dc->value->data[j];

      if (buffer_is_equal_string(du->key, CONST_STR_LEN("lvhost.uri"))) {
        PATCH_OPTION(ldap_uri);
        PATCH_OPTION(ldap_pre);
        PATCH_OPTION(ldap_post);
        PATCH_OPTION(ldap_c);
      } else if (buffer_is_equal_string(du->key, CONST_STR_LEN("lvhost.dn"))) {
        PATCH_OPTION(ldap_dn);
      } else if (buffer_is_equal_string(du->key, CONST_STR_LEN("lvhost.pass"))) {
        PATCH_OPTION(ldap_pass);
      } else if (buffer_is_equal_string(du->key, CONST_STR_LEN("lvhost.base"))) {
        PATCH_OPTION(ldap_base);
      } else if (buffer_is_equal_string(du->key, CONST_STR_LEN("lvhost.filter"))) {
        PATCH_OPTION(ldap_filter);
      } else if (buffer_is_equal_string(du->key, CONST_STR_LEN("lvhost.key"))) {
        PATCH_OPTION(ldap_key);
      }
    }   
  }

  return 0;
}

CONNECTION_FUNC(mod_lvhost_handle_docroot)
{

  plugin_data *p = p_d;

  int n;
  size_t i;
  
  LDAPMessage *msg, *entry;
  struct berval **vals;
  int ldap_scope = LDAP_SCOPE_SUBTREE;

  /* Fold if no host specified. */
  if (!con->uri.authority->used)
  {
    log_error_write(srv, __FILE__, __LINE__, "s", "No host header, exiting...");
    return HANDLER_GO_ON;
  }

  /* Find the requested server name in cache. */
  doc_cache_t *cache_entry = 0;

#ifdef DEBUG
  log_error_write(srv, __FILE__, __LINE__, "sd", "Checking cache of size", p->cache_size);
#endif

  for(i = 0; i < p->cache_size; i++)
  {
    if(buffer_is_equal(p->doc_cache[i]->server_name, con->uri.authority))
    {
      cache_entry = p->doc_cache[i];
      break;
    }
  }

  if( cache_entry && ((time(0) - cache_entry->birth) < DEFAULT_CACHE_LIFETIME ))
  {
    buffer_copy_string_buffer(con->server_name, cache_entry->server_name);
    buffer_copy_string_buffer(con->physical.doc_root, cache_entry->docroot);
#ifdef DEBUG
    log_error_write(srv, __FILE__, __LINE__, "sb", "Using cache for", cache_entry->server_name);
    log_error_write(srv, __FILE__, __LINE__, "sb", "Server name", con->server_name);
    log_error_write(srv, __FILE__, __LINE__, "sb", "URI authority", con->uri.authority);
    log_error_write(srv, __FILE__, __LINE__, "sb", "Document root", con->physical.doc_root);
#endif
    return HANDLER_GO_ON;
  }

  mod_lvhost_patch_connection(srv, con, p);

  /* Perhaps ldap_c has become invalid ?  Create a new session ? */
  if (!p->conf.ldap_c)
  {
    log_error_write(srv, __FILE__, __LINE__, "s", "No ldap handler, exiting...");
    return HANDLER_GO_ON;
  }
  
  buffer_copy_string_buffer(p->tmp_buf, p->conf.ldap_pre);
  
  if (p->conf.ldap_post->used)
  {
    buffer_append_string_buffer(p->tmp_buf, con->uri.authority);
    buffer_append_string_buffer(p->tmp_buf, p->conf.ldap_post);
  }

  if (ldap_search_ext_s(p->conf.ldap_c, p->conf.ldap_base->ptr, ldap_scope,  p->tmp_buf->ptr, NULL, 0, NULL, NULL, NULL, 0, &msg) != LDAP_SUCCESS)
  {
    log_error_write(srv, __FILE__, __LINE__, "sb", "ldap_search_ext_s failed for", p->tmp_buf);
    return HANDLER_GO_ON;
  }

  n = ldap_count_entries(p->conf.ldap_c, msg);

  if (n==0)
  {
    log_error_write(srv, __FILE__, __LINE__, "sbsb", "No entry for filter", p->tmp_buf, "using default document root", con->physical.doc_root);
    buffer_copy_string_buffer(p->docroot, con->physical.doc_root);
  } 
  else 
  {
    
    if (n>1)
      log_error_write(srv, __FILE__, __LINE__, "sb", "Duplicate entry for filter", p->tmp_buf);
    
    entry = ldap_first_entry(p->conf.ldap_c, msg);

    vals = ldap_get_values_len(p->conf.ldap_c, entry, p->conf.ldap_key->ptr);

    if(vals != NULL)
    {
      buffer_copy_string(p->docroot, vals[0]->bv_val);
      ldap_value_free_len(vals);
    }
    if (entry != NULL)
      ldap_msgfree(entry);
    }
  
  stat_cache_entry *sce;

  if (HANDLER_ERROR == stat_cache_get_entry(srv, con, p->docroot, &sce))
  {
    log_error_write(srv, __FILE__, __LINE__, "sb", strerror(errno), p->docroot);
    return HANDLER_GO_ON;
  }

  if (!S_ISDIR(sce->st.st_mode))
  {
    log_error_write(srv, __FILE__, __LINE__, "sb", "Not a directory", p->docroot);
    return HANDLER_GO_ON;
  }

  if(!cache_entry)
  {
    cache_entry = malloc(sizeof(doc_cache_t));
    if(!cache_entry) return HANDLER_ERROR;

    cache_entry->server_name = buffer_init();
    cache_entry->docroot = buffer_init();

    buffer_copy_string_buffer(cache_entry->server_name, con->uri.authority);

    p->doc_cache = realloc(p->doc_cache, (p->cache_size + 1) * sizeof(doc_cache_t*));

    if(!p->doc_cache) return HANDLER_ERROR;

    p->doc_cache[p->cache_size] = cache_entry;
    p->cache_size = p->cache_size + 1;

#ifdef DEBUG
    log_error_write(srv, __FILE__, __LINE__, "sd", "Adding doc root to cache, new size", p->cache_size);
#endif
  }

  cache_entry->birth = time(0);
  buffer_copy_string_buffer(cache_entry->docroot, p->docroot);
  buffer_copy_string_buffer(con->server_name, con->uri.authority);
  buffer_copy_string_buffer(con->physical.doc_root, p->docroot);

#ifdef DEBUG
  log_error_write(srv, __FILE__, __LINE__, "sb", "Server name", con->server_name);
  log_error_write(srv, __FILE__, __LINE__, "sb", "URI authority", con->uri.authority);
  log_error_write(srv, __FILE__, __LINE__, "sb", "Document root", con->physical.doc_root);
#endif

  /*
   * if (msg != NULL)
   *  ldap_msgfree(msg);
   *
   */

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
