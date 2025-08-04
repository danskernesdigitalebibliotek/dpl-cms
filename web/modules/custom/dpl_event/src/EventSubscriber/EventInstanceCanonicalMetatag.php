<?php

namespace Drupal\dpl_event\EventSubscriber;

use Drupal\dpl_event\ReoccurringDateFormatter;
use Drupal\recurring_events\Entity\EventInstance;
use Drupal\recurring_events\Entity\EventSeries;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Set canonical URL of eventinstances to eventseries if relevant.
 *
 * As we use field inheritance for eventinstances, a lot of the content will
 * look as duplicate for search engines.
 * If there are sibling eventinstances, we'll set the series as the canonical
 * "owner" of the content.
 */
class EventInstanceCanonicalMetatag implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    private ReoccurringDateFormatter $reoccurringDateFormatter,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::VIEW => ['onView', 100],
    ];
  }

  /**
   * If instance siblings, alter the canonical URL.
   */
  public function onView(ViewEvent $event): void {
    $request = $event->getRequest();
    $routeName = $request->attributes->get('_route');
    $eventInstance = $request->attributes->get('eventinstance');

    if ($routeName !== 'entity.eventinstance.canonical' || !$eventInstance instanceof EventInstance) {
      return;
    }

    $eventSeries = $eventInstance->getEventSeries();

    if (!($eventSeries instanceof EventSeries)) {
      return;
    }

    $upcomingIds = $this->reoccurringDateFormatter->getUpcomingEventIds($eventSeries);

    // We have a logic in dpl_event/EventSubscriber/EventSeriesRedirect that
    // redirects EventSeries with a single active instance to the instance.
    // If that's the case, we do not want to set the canonical link to a page
    // that redirects back.
    if (count($upcomingIds) > 1) {
      $this->setCanonicalMetatag($event, $eventSeries);
    }
  }

  /**
   * Unset any existing canonical url tag, and set EventSeries as the new one.
   */
  private function setCanonicalMetatag(ViewEvent $event, EventSeries $eventSeries): void {
    $eventSeriesUrl = $eventSeries->toUrl()->setAbsolute()->toString();
    $result = $event->getControllerResult();
    $attachments = $result['#attached'];

    if (isset($attachments['html_head_link'])) {
      foreach ($attachments['html_head_link'] as $key => $link) {
        $rel = $link[0]['rel'] ?? NULL;

        if ($rel == 'canonical') {
          unset($attachments['html_head_link'][$key]);
        }
      }

    }

    $attachments['html_head_link'][] = [
        [
          'rel' => 'canonical',
          'href' => $eventSeriesUrl,
        ],
    ];

    $result['#attached'] = $attachments;
    $event->setControllerResult($result);
  }

}
