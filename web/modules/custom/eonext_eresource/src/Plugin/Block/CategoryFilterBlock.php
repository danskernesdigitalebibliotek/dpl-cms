<?php

namespace Drupal\eonext_eresource\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block that lists E-Resource categories.
 */
#[Block(
  id: "eonext_eresource_category_filter_block",
  admin_label: new TranslatableMarkup("E-resource category filter"),
  category: new TranslatableMarkup("EO Next")
)]
class CategoryFilterBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected CurrentRouteMatch $routeMatch;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected EntityTypeManager $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);

    $instance->routeMatch = $container->get('current_route_match');
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;
  }

  /**
   * {@inheritDoc}
   *
   * @return array<mixed>
   *   Block render array.
   */
  public function build(): array {
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    $terms = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->loadTree('e_resource_category', 0, NULL, TRUE);

    $menu_items = [];
    foreach ($terms as $term) {
      $options = [
        'attributes' => [
          'class' => [
            'e-resource-category',
          ],
        ],
      ];
      if ($term->id() === $this->routeMatch->getParameter('taxonomy_term')?->id()) {
        $options['attributes']['class'][] = 'active';
      }

      $menu_items[] = $term->toLink($term->label(), 'canonical', $options);
    }

    return [
      '#theme' => 'item_list',
      '#title' => $this->t('E-resource category'),
      '#items' => $menu_items,
      '#attributes' => [
        'class' => ['e-resource-category-filter'],
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheTags(): array {
    return Cache::mergeTags(['node_list:e_resource'], parent::getCacheTags());
  }

}
