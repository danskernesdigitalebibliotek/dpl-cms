<?php

namespace Drupal\dpl_cms\Updater;

use Drupal\Core\Updater\UpdaterInterface;
use Drupal\Core\Updater\Module as UpdaterModule;

/**
 * Extending the core updater module class.
 *
 * We need to change the path
 * because we want to persist the modules in a volume.
 * A symlink is pointing from:
 * modules/local -> sites/default/files/modules_local
 * in order to complete the setup.
 */
class Module extends UpdaterModule implements UpdaterInterface {

  /**
   * {@inheritdoc}
   */
  public static function getRootDirectoryRelativePath() {
    return 'modules/local';
  }

  function dpl_cms_update_9001() {
    \Drupal::service('module_installer')->install(['dpl_fbs']);
    \Drupal::service('module_installer')->install(['dpl_loans']);
    \Drupal::service('module_installer')->install(['dpl_publizon']);
    \Drupal::service('module_installer')->install(['dpl_user_profile']);
  }

}
