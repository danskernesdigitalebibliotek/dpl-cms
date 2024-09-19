<?php

namespace Drupal\eonext_mobilesearch\EventSubscriber;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\eonext_mobilesearch\Event\EntityActionEvent;
use Drupal\eonext_mobilesearch\Mobilesearch\EntityConverterFactory;
use Drupal\eonext_mobilesearch\Mobilesearch\Mobilesearch;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Mobilesearch entity action event subscriber.
 *
 * This triggers mobilesearch push.
 */
class EntityActionSubscriber implements EventSubscriberInterface {

  public function __construct(
    protected Mobilesearch $mobileSearch,
    protected ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityActionEvent::EVENT_ENTITY_INSERT => ['entityInsert', -100],
      EntityActionEvent::EVENT_ENTITY_UPDATE => ['entityUpdate', -100],
      EntityActionEvent::EVENT_ENTITY_DELETE => 'entityDelete',
    ];
  }

  /**
   * Trigger MOS push on entity insert.
   *
   * @param \Drupal\eonext_mobilesearch\Event\EntityActionEvent $event
   *   Triggered event.
   */
  public function entityInsert(EntityActionEvent $event): void {
    $entity = $event->getEntity();
    $normalizedEntity = EntityConverterFactory::getConverter($entity)
      ->convert($entity);

    $this->moduleHandler->alter(
      'mobilesearch_push',
      $normalizedEntity,
      $entity,
      $event
    );

    $this->mobileSearch->push($normalizedEntity, $event->getServiceAction());
  }

  /**
   * Trigger MOS push on entity update.
   *
   * @param \Drupal\eonext_mobilesearch\Event\EntityActionEvent $event
   *   Triggered event.
   */
  public function entityUpdate(EntityActionEvent $event): void {
    $entity = $event->getEntity();
    $normalizedEntity = EntityConverterFactory::getConverter($entity)
      ->convert($entity);

    $this->moduleHandler->alter(
      'mobilesearch_push',
      $normalizedEntity,
      $entity,
      $event
    );

    $result = $this->mobileSearch->push($normalizedEntity, $event->getServiceAction());
    if (!$result) {
      $event->setAction(EntityActionEvent::EVENT_ENTITY_INSERT);
      $this->mobileSearch->push($normalizedEntity, $event->getServiceAction());
    }
  }

  /**
   * Trigger MOS delete on entity delete.
   *
   * @param \Drupal\eonext_mobilesearch\Event\EntityActionEvent $event
   *   Triggered event.
   */
  public function entityDelete(EntityActionEvent $event): void {
    $entity = $event->getEntity();
    $normalizedEntity = EntityConverterFactory::getConverter($entity)
      ->convert($entity);

    $this->moduleHandler->alter(
      'mobilesearch_push',
      $normalizedEntity,
      $entity,
      $event
    );

    $this->mobileSearch->push($normalizedEntity, $event->getServiceAction());
  }

}
