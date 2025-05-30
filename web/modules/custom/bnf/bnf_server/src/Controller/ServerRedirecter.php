<?php

namespace Drupal\bnf_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;

/**
 * Redirects from the server to client side.
 */
class ServerRedirecter extends ControllerBase {

  /**
   * Redirecting to the import URL of the client site.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse|array<mixed>
   *   A redirect, or a notice page.
   */
  public function import(string $uuid, Request $request): TrustedRedirectResponse|array {
    $session = $request->getSession();
    $url = $session->get(LoginController::CALLBACK_URL_KEY);

    // If we have no URL to redirect to, we'll display a notice template,
    // with info to the user of how they can manually import the content.
    if (empty($url)) {
      return [
        '#theme' => 'bnf_server_missing_callback',
        '#node_uuid' => $uuid,
      ];
    }

    return new TrustedRedirectResponse("$url/admin/bnf/import/{$uuid}");
  }

  /**
   * Redirecting to the subcribe-to-term URL of the client site.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse|array<mixed>
   *   A redirect, or a notice page.
   */
  public function subscribe(string $uuid, string $label, Request $request): TrustedRedirectResponse|array {
    $session = $request->getSession();
    $url = $session->get(LoginController::CALLBACK_URL_KEY);

    // If we have no URL to redirect to, we'll display a notice template,
    // prompting the user to log in.
    if (empty($url)) {
      return [
        '#theme' => 'bnf_server_missing_callback',
      ];
    }

    $query = [
      'uuid' => $uuid,
      'label' => $label,
    ];
    $url = Url::fromUri("{$url}/admin/bnf/subscriptions/new", ['query' => $query])->toString();

    return new TrustedRedirectResponse($url);
  }

}
