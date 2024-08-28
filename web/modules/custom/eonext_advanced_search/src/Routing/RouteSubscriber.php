<?php

namespace Drupal\eonext_advanced_search\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\eonext_advanced_search\Form\AdvancedSearchConfigForm;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  public function __construct(protected ConfigFactoryInterface $configFactory) {}

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('dpl_react_apps.advanced_search')) {
      $advanced_search_enabled = $this
        ->configFactory
        ->get(AdvancedSearchConfigForm::CONFIG_ID)
        ->get('advanced_search_enabled') ?? FALSE;

      if (!$advanced_search_enabled) {
        $route->setRequirement('_access', 'FALSE');
      }
    }
  }

}
