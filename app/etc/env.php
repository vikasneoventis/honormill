<?php
return array (
  'backend' => 
  array (
    'frontName' => 'backend1965',
  ),
  'crypt' => 
  array (
    'key' => 'a2175fafbd4ec0a9058dd8c6df257991',
  ),
  'db' => 
  array (
    'table_prefix' => '',
    'connection' => 
    array (
      'default' => 
      array (
        'host' => 'localhost',
        'dbname' => 'maxwellb_maxwellb_honormill_new',
        'username' => 'maxwellb_honormi',
        'password' => 'GipsyChewsSubwayHiring20',
        'active' => '1',
      ),
    ),
  ),
  'resource' => 
  array (
    'default_setup' => 
    array (
      'connection' => 'default',
    ),
  ),
  'x-frame-options' => 'SAMEORIGIN',
  'MAGE_MODE' => 'developer',
  'session' => 
  array (
    'save' => 'memcache',
    'save_path' => 'unix:///var/run/memcached-multi/maxwellb.honormill.com_sessions.sock?persistent=1&weight=2&timeout=10&retry_interval=0',
  ),
  'cache_types' => 
  array (
    'config' => 1,
    'layout' => 1,
    'block_html' => 1,
    'collections' => 1,
    'reflection' => 1,
    'db_ddl' => 1,
    'eav' => 1,
    'customer_notification' => 1,
    'config_integration' => 1,
    'config_integration_api' => 1,
    'full_page' => 0,
    'translate' => 1,
    'config_webservice' => 1,
    'compiled_config' => 1,
  ),
  'install' => 
  array (
    'date' => 'Mon, 29 Jan 2018 10:04:37 +0000',
  ),
  'system' => 
  array (
    'default' => 
    array (
      'dev' => 
      array (
        'debug' => 
        array (
          'debug_logging' => '0',
        ),
      ),
    ),
  ),
  'cache' => 
  array (
    'frontend' => 
    array (
      'default' => 
      array (
        'backend' => 'Cm_Cache_Backend_Redis',
        'backend_options' => 
        array (
          'server' => '/var/run/redis-multi/maxwellb.honormill.com_cache.sock',
          'database' => '0',
          'port' => '0',
        ),
      ),
    ),
  ),
);