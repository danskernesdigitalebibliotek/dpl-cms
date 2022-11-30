<?php

namespace Drupal\dpl_loans\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Render loan list react app.
 */
class DplLoansController extends ControllerBase {

  /**
   * Demo react rendering.
   *
   * @return array
   *   Render array.
   */
  public function list(): array {
    $block_manager = $this->configFactory->get('plugin.manager.block');

    // You can hard code configuration, or you load from settings.
    $config = [];
    $plugin_block = $block_manager->createInstance('dpl_loans_list_block', $config);

    // Some blocks might implement access check.
    $access_result = $plugin_block->access($this->currentUser());

    // Return empty render array if user doesn't have access.
    // $access_result can be boolean or an AccessResult class.
    if (is_object($access_result) && $access_result->isForbidden() || is_bool($access_result) && !$access_result) {
      // You might need to add some cache tags/contexts.
      return [];
    }

    // Add the cache tags/contexts.
    $render = $plugin_block->build();
    $this->configFactory->get('renderer')->addCacheableDependency($render, $plugin_block);

    return $render;
  }

}
