<?php

/**
 * @file
 * Enables modules and site configuration for a standard site installation.
 */

/**
 * Implements hook_updater_info_alter().
 */
function dpl_cms_modules_updater_info_alter(&$updaters): void {
  // Adjust weight so that the theme Updater gets a chance to handle a given
  // update task before module updaters.
  $updaters['module']['class'] = 'Drupal\\dpl_upload_modules\\Updater\\Module';
}
