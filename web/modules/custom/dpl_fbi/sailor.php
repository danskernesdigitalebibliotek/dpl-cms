<?php declare(strict_types=1);

// phpcs:ignoreFile

use Drupal\dpl_fbi\SailorEndpointConfig;

return [
  'fbi' => \Drupal::service(SailorEndpointConfig::class),
];
