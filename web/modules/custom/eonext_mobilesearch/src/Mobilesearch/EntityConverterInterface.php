<?php

namespace Drupal\eonext_mobilesearch\Mobilesearch;

use Drupal\Core\Entity\EntityInterface;
use Drupal\eonext_mobilesearch\Mobilesearch\DTO\MobilesearchEntityInterface;

/**
 * Common interface for entity converters.
 */
interface EntityConverterInterface {

  /**
   * Convert an entity object into a serializable object.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to convert.
   *
   * @return \Drupal\eonext_mobilesearch\Mobilesearch\DTO\MobilesearchEntityInterface
   *   Serializable MOS DTO payload.
   */
  public function convert(EntityInterface $entity): MobilesearchEntityInterface;

}
