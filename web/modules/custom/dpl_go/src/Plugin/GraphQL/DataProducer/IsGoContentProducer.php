<?php

namespace Drupal\dpl_go\Plugin\GraphQL\DataProducer;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Resolves whether the content is GO content.
 *
 * @DataProducer(
 *   id = "is_go_content",
 *   name = "Is GO Content Producer",
 *   description = "Provides whether the content is GO content.",
 *   produces = @ContextDefinition("any",
 *     label = "Request Response"
 *   ),
 *   consumes = {
 *     "url" = @ContextDefinition("any",
 *       label = @Translation("Url")
 *     )
 *   }
 * )
 */
class IsGoContentProducer extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Node storage.
   */
  protected EntityStorageInterface $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Resolves whether the content is GO content.
   *
   * @param \Drupal\Core\Url $url
   *   The URL object.
   *
   * @return bool
   *   TRUE if the content is GO content, FALSE otherwise.
   */
  public function resolve(Url $url): bool {

    $route_parameters = $url->getRouteParameters();
    $node = $this->nodeStorage->load($route_parameters['node']);

    if ($node instanceof NodeInterface && str_starts_with($node->bundle(), 'go_')) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
