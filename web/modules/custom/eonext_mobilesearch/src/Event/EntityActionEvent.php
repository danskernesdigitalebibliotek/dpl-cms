<?php

namespace Drupal\eonext_mobilesearch\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Entity\EntityInterface;

/**
 * Event, triggered on entity insert/update/delete Drupal hooks.
 */
class EntityActionEvent extends Event {
  public const EVENT_ENTITY_INSERT = 'entity_insert';
  public const EVENT_ENTITY_UPDATE = 'entity_update';
  public const EVENT_ENTITY_DELETE = 'entity_delete';

  /**
   * Event constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Event entity.
   */
  public function __construct(
    protected EntityInterface $entity,
    protected string $action = self::EVENT_ENTITY_UPDATE,
  ) {}

  /**
   * Gets the event entity object.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Entity related to the event.
   */
  public function getEntity(): EntityInterface {
    return $this->entity;
  }

  /**
   * Gets the event target action.
   *
   * @return string
   *   Action to perform.
   */
  public function getAction(): string {
    return $this->action;
  }

  /**
   * Sets the event target action.
   *
   * @param string $action
   *   Action to perform.
   *
   * @return $this
   *   This vent object.
   */
  public function setAction(string $action): self {
    $this->action = $action;

    return $this;
  }

  /**
   *
   */
  public function getServiceAction(): string {
    return match ($this->action) {
      self::EVENT_ENTITY_INSERT => 'PUT',
      self::EVENT_ENTITY_UPDATE => 'POST',
      self::EVENT_ENTITY_DELETE => 'DELETE',
      default => throw new \InvalidArgumentException("Unknown action '{$this->action}'"),
    };
  }

  /**
   * Checks whether the event listener should be triggered.
   *
   * Simply checks which entity type is that.
   *
   * @return bool
   *   TRUE to trigger, FALSE otherwise.
   */
  public function shouldTrigger(): bool {
    $supports = ['node', 'eventinstance'];

    return (in_array($this->entity->getEntityTypeId(), $supports));
  }

}
