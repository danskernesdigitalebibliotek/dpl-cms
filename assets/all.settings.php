<?php

/**
 * @file
 * Amazee.io Drupal all environment configuration file.
 *
 * This file should contain all settings.php configurations that are needed by
 * all environments.
 *
 * It contains some defaults that the amazee.io team suggests, please edit them
 * as required.
 */

use Drupal\Core\Installer\InstallerKernel;

// Hardcode a Site UUID to enable sharing of configuration between sites beyond
// site install.
$config['system.site']['uuid'] = '13ef1a53-dfb4-4c82-9b64-44586a366729';

// Hardcode the site mail as we don't want to allow changing it in the UI.
// The email needs to match what is setup in Azure Communication Services.
$config['system.site']['mail'] = 'mail@folkebibliotekernescms.dk';

// Configure logging using the project name and environment from the Lagoon
// environment.
$config['jsonlog.settings']['jsonlog_siteid'] = getenv('LAGOON_PROJECT') . '_' . getenv('LAGOON_ENVIRONMENT');
$config['jsonlog.settings']['jsonlog_canonical'] = getenv('LAGOON_PROJECT') . '_' . getenv('LAGOON_ENVIRONMENT');
if (InstallerKernel::installationAttempted()) {
  // During the installation we can end up in situations where both JSONLog ond
  // Drupal will output messages. Squash any messages coming from JSONLog by
  // setting a low (meaning high) threshold.
  $config['jsonlog.settings']['jsonlog_stdout'] = TRUE;
  $config['jsonlog.settings']['jsonlog_severity_threshold'] = 0;
}

// Support overriding the database configuration using non-standard Lagoon
// environment variables.
// This is explicitly added to support migration between databases.
if (getenv('MARIADB_DATABASE_OVERRIDE')) {
  $databases['default']['default'] = [
    'driver' => 'mysql',
    // These settings intentionally do not have defaults. If overriding the
    // database configuration then all standard configuration must be defined.
    'database' => getenv('MARIADB_DATABASE_OVERRIDE'),
    'username' => getenv('MARIADB_USERNAME_OVERRIDE'),
    'password' => getenv('MARIADB_PASSWORD_OVERRIDE'),
    'host' => getenv('MARIADB_HOST_OVERRIDE'),
    // These settings intentionally have defaults. It is not likely that they
    // will be defined when overriding the database.
    'port' => getenv('MARIADB_PORT_OVERRIDE') ?: 3306,
    'charset' => getenv('MARIADB_CHARSET_OVERRIDE') ?: 'utf8mb4',
    'collation' => getenv('MARIADB_COLLATION_OVERRIDE') ?: 'utf8mb4_general_ci',
    'prefix' => '',
  ];
}

// Exclude certain modules from configuration export.
$settings['config_exclude_modules'] = [
  // Development modules that is only enabled in development environment.
  'bnf_example_content',
  'dpl_related_content_tests',
  'dpl_example_content',
  'dpl_example_breadcrumb',
  'dblog',
  'devel',
  'devel_generate',
  'field_ui',
  'purge_ui',
  'views_ui',
  'restui',
  'upgrade_status',
  'uuid_url',
  // These are enabled as needed, so exclude them from export.
  'bnf_client',
  'bnf_server',
];

// Defines where the sync folder of your configuration lives. In this case it's
// inside the Drupal root, which is protected by amazee.io Nginx configs, so it
// cannot be read via the browser. If your Drupal root is inside a subfolder
// (like 'web') you can put the config folder outside this subfolder for an
// advanced security measure: '../config/sync'.
$settings['config_sync_directory'] = '../config/sync';

/**
 * Private file path.
 *
 * A local file system path where private files will be stored. This directory
 * must be absolute, outside the Drupal installation directory and not
 * accessible over the web.
 *
 * Note: Caches need to be cleared when this value is changed to make the
 * private:// stream wrapper available to the system.
 *
 * See https://www.drupal.org/documentation/modules/file for more information
 * about securing private files.
 */
$settings['file_private_path'] = $app_root . '/sites/default/files/private';

// Set service base urls for the react apps.
$config['dpl_react_apps.settings']['services'] = [
  'cover' => ['base_url' => 'https://cover.dandigbib.org'],
  // @todo This should be updated to use the correct URL when available.
  'fbi' => ['base_url' => 'https://temp.fbi-api.dbc.dk/[profile]/graphql'],
  'material-list' => ['base_url' => 'https://prod.materiallist.dandigbib.org'],
];

// Use Danish collation to support proper sorting with Danish characters.
// Without this ÆØÅ will not be handled properly.
$databases['default']['default']['charset'] = 'utf8mb4';
$databases['default']['default']['collation'] = 'utf8mb4_danish_ci';

if (getenv('CI')) {
  // Curl settings needed to make PHP ignore SSL errors when using Wiremock as
  // a proxy. We do not have a proper SSL setup with trusted certificates.
  $settings['http_client_config']['verify'] = FALSE;
  $settings['http_client_config']['curl'] = [
    CURLOPT_PROXY_SSL_VERIFYHOST => 0,
    CURLOPT_PROXY_SSL_VERIFYPEER => FALSE,
  ];
  // Specify non-HTTP versions of endpoints. This is required to make Cypress
  // mocking work. It does not support ignoring self-signed certificates from
  // Wiremock.
  // Service base urls for the external APIs.
  $config['dpl_fbs.settings'] = ['base_url' => 'http://fbs-openplatform.dbc.dk'];
  $config['dpl_publizon.settings'] = ['base_url' => 'http://pubhub-openplatform.dbc.dk'];
  // Adgangsplatformen OpenID Connect client.
  $config['openid_connect.client.adgangsplatformen']['settings']['authorization_endpoint'] = 'http://login.bib.dk/oauth/authorize';
  $config['openid_connect.client.adgangsplatformen']['settings']['token_endpoint'] = 'http://login.bib.dk/oauth/token/';
  $config['openid_connect.client.adgangsplatformen']['settings']['userinfo_endpoint'] = 'http://login.bib.dk/userinfo/';
  $config['openid_connect.client.adgangsplatformen']['settings']['logout_endpoint'] = 'http://login.bib.dk/logout';
  // The actual values here are not important. The primary thing is that the
  // Adgangsplatformen OpenID Connect client is configured.
  $config['openid_connect.client.adgangsplatformen']['settings']['client_id'] = 'dummy-id';
  $config['openid_connect.client.adgangsplatformen']['settings']['client_id'] = 'dummy-secret';
  $config['openid_connect.client.adgangsplatformen']['settings']['agency_id'] = '100200';

  // Set service base urls for the react apps.
  // We need http domains for testing in CI context.
  $config['dpl_react_apps.settings']['services'] = [
    'cover' => ['base_url' => 'http://cover.dandigbib.org'],
    // @todo This should be updated to use the correct URL when available.
    'fbi' => ['base_url' => 'http://temp.fbi-api.dbc.dk/[profile]/graphql'],
    'material-list' => ['base_url' => 'http://prod.materiallist.dandigbib.org'],
  ];

  // Avoid attempts to send out mail during tests.
  $config['mailsystem.settings']['defaults'] = [
    'formatter' => 'devel_mail_log',
    'sender' => 'devel_mail_log',
  ];

  // We need to be fixed language in our UI texts
  // because we use them for assertions in tests.
  $config['language.negotiation']['selected_langcode'] = 'en';
}

if (getenv('LAGOON_ENVIRONMENT_TYPE') !== 'production') {
  // Skip file system permissions hardening.
  //
  // The system module will periodically check the permissions of your site's
  // site directory to ensure that it is not writable by the website user. For
  // sites that are managed with a version control system, this can cause
  // problems when files in that directory such as settings.php are updated,
  // because the user pulling in the changes won't have permissions to modify
  // files in the directory.
  $settings['skip_permissions_hardening'] = TRUE;
}

// Setup Redis.
if (getenv('LAGOON')) {
  // Prepare the module configuration.
  $settings['redis.connection']['interface'] = 'PhpRedis';
  $settings['redis.connection']['host'] = getenv('REDIS_HOST') ?: 'redis';
  $settings['redis.connection']['port'] = getenv('REDIS_SERVICE_PORT') ?: '6379';
  $settings['cache_prefix']['default'] = getenv('LAGOON_PROJECT') . '_' . getenv('LAGOON_GIT_SAFE_BRANCH');

  // But only enable the module if we are ready.
  if (
    // Do not enable the cache during install.
    !InstallerKernel::installationAttempted()
    // Do not enable the cache if php does not have the extension enabled.
    && extension_loaded('redis')
  ) {
    // Enable the cache backend.
    $settings['cache']['default'] = 'cache.backend.redis';

    // The graphql module seems to have issues with things getting munged in
    // the cache. Obviously this shouldn't happen, but for the moment move
    // it's cache to the database.
    // @see https://www.drupal.org/project/graphql/issues/3477239
    $settings['cache']['bins']['graphql_ast'] = 'cache.backend.database';
    $settings['cache']['bins']['graphql_results'] = 'cache.backend.database';

    // The default example configuration that ships with the module works fine.
    // By using it, we rely on future developers that updates the module to
    // spot if any major changes happens to the config, but as we're using the
    // same version of redis in all environment including our local environment
    // and automated tests, breaking changes should be detected.
    // This is the tradeoff for on the other hand to get an updated example-
    // file.
    $settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml';

    // Allow the services to work before the Redis module itself is enabled.
    $settings['container_yamls'][] = 'modules/contrib/redis/redis.services.yml';

    // And allows to use it without the Redis module being enabled.
    $class_loader->addPsr4('Drupal\\redis\\', 'modules/contrib/redis/src');

    $settings['bootstrap_container_definition'] = [
      'parameters' => [],
      'services' => [
        'redis.factory' => [
          'class' => 'Drupal\redis\ClientFactory',
        ],
        'cache.backend.redis' => [
          'class' => 'Drupal\redis\Cache\CacheBackendFactory',
          'arguments' => [
            '@redis.factory',
            '@cache_tags_provider.container',
            '@serialization.phpserialize',
          ],
        ],
        'cache.container' => [
          'class' => '\Drupal\redis\Cache\PhpRedis',
          'factory' => ['@cache.backend.redis', 'get'],
          'arguments' => ['container'],
        ],
        'cache_tags_provider.container' => [
          'class' => 'Drupal\redis\Cache\RedisCacheTagsChecksum',
          'arguments' => ['@redis.factory'],
        ],
        'serialization.phpserialize' => [
          'class' => 'Drupal\Component\Serialization\PhpSerialize',
        ],
      ],
    ];
  }
}
