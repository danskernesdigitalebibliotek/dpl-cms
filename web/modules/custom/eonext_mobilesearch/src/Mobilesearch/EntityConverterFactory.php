<?php

namespace Drupal\eonext_mobilesearch\Mobilesearch;

use Drupal\Core\Entity\EntityInterface;

/**
 * Simple static factory for entity converters.
 */
class EntityConverterFactory {

  /**
   * Gets the converter based on the given type.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The type of entity.
   *
   * @return \Drupal\eonext_mobilesearch\Mobilesearch\EntityConverterInterface
   *   The converter instance for the specified type.
   */
  public static function getConverter(EntityInterface $entity): EntityConverterInterface {
    return match ($entity->getEntityTypeId()) {
      'node', 'eventinstance' => \Drupal::service('eonext.mobilesearch.node_converter'),
      default => throw new \RuntimeException('No converter available for the type given.')
    };
  }

}
