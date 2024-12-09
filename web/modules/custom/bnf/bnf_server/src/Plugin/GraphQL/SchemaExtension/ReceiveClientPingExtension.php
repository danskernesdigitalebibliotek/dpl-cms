<?php

namespace Drupal\bnf_server\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * @SchemaExtension(
 *   id = "receive_client_ping_extension",
 *   name = "Receive Client Ping Extension",
 *   schema = "graphql_compose"
 * )
 */
class ReceiveClientPingExtension extends SdlSchemaExtensionPluginBase {

  /**
   * Registers the resolvers for the schema.
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();
    $registry->addFieldResolver('Mutation', 'receiveClientPing',
      $builder->produce('receive_client_ping_producer')
        ->map('uuid', $builder->fromArgument('uuid'))
        ->map('callbackUrl', $builder->fromArgument('callbackUrl'))
    );
  }

}
