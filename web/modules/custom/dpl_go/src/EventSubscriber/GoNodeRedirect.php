<?php

namespace Drupal\dpl_go\EventSubscriber;

use Drupal\Core\Url;
use Drupal\dpl_go\GoSite;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Alter the response on GO nodes to a redirect to the external Go app.
 *
 * @package Drupal\dpl_go\EventSubscriber
 */
class GoNodeRedirect implements EventSubscriberInterface {

  public function __construct(protected GoSite $goSite) {}

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      'kernel.request' => [
        ['redirectGoContent'],
        ['redirectGoPreview'],
      ],
    ];
  }

  /**
   * Look up the content and redirect to the external Go app if it is a GO node.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The response event.
   */
  public function redirectGoContent(RequestEvent $event): void {
    $request = $event->getRequest();

    if ($request->attributes->get('_route') !== 'entity.node.canonical') {
      return;
    }

    $node = $request->attributes->get('node');

    if ($node instanceof NodeInterface && $this->goSite->isGoNode($node)) {
      $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();

      $response = new RedirectResponse($this->goSite->getGoBaseUrl() . $url);
      $event->setResponse($response);
    }
  }

  /**
   * Redirect GO Preview to the external Go app.
   *
   * Look up the content and redirect the preview to the external Go app if it
   * is a GO node. We also make sure to remove the destination query parameter
   * to avoid being redirected back to /admin/content.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The response event.
   */
  public function redirectGoPreview(RequestEvent $event): void {
    $request = $event->getRequest();

    if ($request->attributes->get('_route') !== 'entity.node.preview') {
      return;
    }

    $node = $request->attributes->get('node_preview');

    if ($node instanceof NodeInterface && $this->goSite->isGoNode($node)) {
      $url = $this->goSite->getGoBaseUrl() . $request->getPathInfo();

      // Unset the destination query parameter to avoid being redirected
      // back to /admin/content.
      $query_params = $request->query->all();
      unset($query_params['destination']);
      $request->query->replace($query_params);

      $preview_token = $node->get('preview_token')->value;

      $response = new RedirectResponse($url . '?token=' . $preview_token);
      $event->setResponse($response);
    }
  }

}
