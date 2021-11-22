<?php

namespace Drupal\dpl_cms\Updater;

use Drupal\Core\Updater\UpdaterInterface;
use Drupal\Core\Updater\Module as UpdaterModule;

/**
 * Extending the core updater module class.
 *
 * In order to override destination path.
 */
class Module extends UpdaterModule implements UpdaterInterface {

  /**
   * {@inheritdoc}
   */
  public static function getRootDirectoryRelativePath() {
    return 'modules/local';
  }

}
