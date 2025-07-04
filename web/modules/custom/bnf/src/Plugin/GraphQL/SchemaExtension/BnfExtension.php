<?php

namespace Drupal\bnf\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * Extends the GraphQL schema with BNF extensions.
 *
 * @SchemaExtension(
 *   id = "bnf_extension",
 *   name = "BNF Extension",
 *   schema = "graphql_compose"
 * )
 */
class BnfExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('NodeInterface', 'url', $builder->compose(
      $builder->produce('entity_url')
        ->map('entity', $builder->fromParent())
        ->map('options', $builder->fromValue(['absolute' => TRUE])),
      $builder->produce('url_path')
        ->map('url', $builder->fromParent())
    ));

    $registry->addFieldResolver('NodeInterface', 'bundle', $builder->compose(
      $builder->produce('entity_bundle')
        ->map('entity', $builder->fromParent())
    ));

    $registry->addFieldResolver('MediaVideo', 'thumbnail', $builder->compose(
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:media'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('thumbnail.entity')),
      $builder->produce('image_url')
        ->map('entity', $builder->fromParent())
    ));

    $registry->addFieldResolver('MediaVideotool', 'thumbnail', $builder->compose(
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:media'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('thumbnail.entity')),
      $builder->produce('image_url')
        ->map('entity', $builder->fromParent())
    ));

  }

}
