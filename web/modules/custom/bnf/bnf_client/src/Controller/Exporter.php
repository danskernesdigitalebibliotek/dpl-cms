<?php

namespace Drupal\bnf_client\Controller;

use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class Exporter extends ControllerBase {

  /**
   * HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructs a new ChatService object.
   */
  public function __construct(
    Client $http_client,
  ) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
    );
  }


  public function exportNode(string $uuid): RedirectResponse {

    // @todo
    $callback_url = 'http://varnish.dpl-cms.orb.local/graphql';

    $mutation = <<<GRAPHQL
    mutation {
      receiveClientPing(uuid: "$uuid", callbackUrl: "$callback_url") {
        status
        message
      }
    }
    GRAPHQL;

    // @todo
    $bnf_server = $callback_url;

    try {
      $this->httpClient->post($bnf_server, [
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'auth' => ['external_system', 'test'],
        'json' => [
          'query' => $mutation,
        ],
      ]);

      $this->messenger()->addStatus($this->t('Content created.'));
    }
    catch (\Exception $e) {
      $this->messenger()->addError($e->getMessage());
    }


    return new RedirectResponse(Url::fromRoute('system.admin_content')->toString());
  }
}
