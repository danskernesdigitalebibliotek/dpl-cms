<?php

namespace Drupal\dpl_fees\Controller;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Render intermediate list react app.
 */
class DplFeesController extends ControllerBase {

  /**
   * DplFeesController constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $blockManager
   *   Drupal block manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Drupal renderer service.
   */
  public function __construct(
    private BlockManagerInterface $blockManager,
    private RendererInterface $renderer
  ) {
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('renderer'),
    );
  }

  /**
   * Demo react rendering.
   *
   * @return mixed[]
   *   Render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function list(): array {

    // You can hard code configuration, or you load from settings.
    $config = [];
    /** @var \Drupal\dpl_fees\Plugin\Block\IntermediateListBlock $plugin_block */
    $plugin_block = $this->blockManager->createInstance('dpl_fees_block', $config);

    // Some blocks might implement access check.
    $access_result = $plugin_block->access($this->currentUser());

    if (is_object($access_result) && $access_result->isForbidden() || is_bool($access_result) && !$access_result) {
      throw new AccessDeniedHttpException();
    }

    // Add the cache tags/contexts.
    $render = $plugin_block->build();
    $this->renderer->addCacheableDependency($render, $plugin_block);

    return $render;
  }

}
