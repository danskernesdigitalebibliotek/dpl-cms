<?php

namespace Drupal\bnf_server\Plugin\GraphQL\DataProducer;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\node\Entity\Node;
use GuzzleHttp\Client;

/**
 * Resolves the `receiveClientPing` mutation.
 *
 * @DataProducer(
 *   id = "receive_client_ping_producer",
 *   name = "Receive Client Ping Producer",
 *   description = "Handles the receiveClientPing mutation.",
 *   produces = @ContextDefinition("any",
 *     label = "Ping Response"
 *   ),
 *   consumes = {
 *     "uuid" = @ContextDefinition("string",
 *       label = "UUID"
 *     ),
 *     "callbackUrl" = @ContextDefinition("string",
 *       label = "Callback URL"
 *     )
 *   }
 * )
 */
class ReceiveClientPingProducer extends DataProducerPluginBase {

  /**
   * Resolves the mutation.
   *
   * @param string $uuid
   *   The client UUID.
   * @param string $callbackUrl
   *   The callback URL.
   *
   * @return array
   *   The response data.
   */
  public function resolve(string $uuid, string $callbackUrl): array {
    \Drupal::logger('bnf_server')->info('Received ping from UUID: @uuid with callback URL: @callbackUrl', [
      '@uuid' => $uuid,
      '@callbackUrl' => $callbackUrl,
    ]);

    // For now, we only support articles. In the future, this should be
    // sent along as a parameter, as GraphQL exposes different queries
    // for each node type (nodeArticle)
    $node_type = 'article';
    $query_name = 'node' . ucfirst($node_type);

    // For now, we only support the title of the nodes.
    $query = <<<GRAPHQL
    query {
      $query_name(id: "$uuid") {
        title
      }
    }
    GRAPHQL;

    $client = new Client();
    try {
      $response = $client->post($callbackUrl, [
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'auth' => ['external_system', 'test'],
        'json' => [
          'query' => $query,
        ],
      ]);

      $data = json_decode($response->getBody()->getContents(), TRUE);
      $node_data = $data['data'][$query_name] ?? NULL;

      if (empty($node_data)) {
        throw new \Exception('Could not retrieve node data.');
      }

      $node_data['type'] = $node_type;

      $node = Node::create($node_data);
      $node->save();



      if (true) {}
    } catch (\Exception $e) {
      \Drupal::logger('bnf_server')->warning('Could not load node of type @node_type with UUID @uuid at @callbackUrl', [
        '@uuid' => $uuid,
        '@node_type' => $node_type,
        '@callbackUrl' => $callbackUrl,
      ]);

      return [
        'status' => 'failure',
        'message' => 'Could not load node from callback URL.',
      ];


    }


    \Drupal::logger('bnf_server')->info('Created new @node_type node with BNF ID @uuid', [
      '@uuid' => $uuid,
      '@node_type' => $node_type,
    ]);

    return [
      'status' => 'success',
      'message' => 'Node created successfully.',
    ];
  }

}
