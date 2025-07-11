<?php

namespace Drupal\dpl_fbi;

/**
 * FBI Profile types.
 */
enum FbiProfileType: string {
  // The default FBI profile.
  case DEFAULT = 'default';

  // Profile name for FBI local profile.
  case LOCAL = 'local';

  // Profile name for FBI global profile.
  case GLOBAL = 'global';
}
