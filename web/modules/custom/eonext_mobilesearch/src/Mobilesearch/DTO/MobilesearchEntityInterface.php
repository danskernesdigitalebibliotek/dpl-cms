<?php

namespace Drupal\eonext_mobilesearch\Mobilesearch\DTO;

/**
 * Interface for mobilesearch entities payload.
 */
interface MobilesearchEntityInterface extends \JsonSerializable {

  /**
   * Gets entity id.
   *
   * @return int
   *   Entity id.
   */
  public function getId(): int;

  /**
   * Gets route where push should occur.
   *
   * @return string
   *   Route suffix.
   */
  public function getRoute(): string;

  /**
   * Gets entity name eager to push.
   *
   * @return string
   *   Entity name.
   */
  public function getEntityName(): string;

}
