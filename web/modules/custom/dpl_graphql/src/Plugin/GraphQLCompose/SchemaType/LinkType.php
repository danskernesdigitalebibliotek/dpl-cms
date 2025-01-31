<?php

declare(strict_types=1);

namespace Drupal\graphql_compose\Plugin\GraphQLCompose\SchemaType;

use Drupal\graphql_compose\Plugin\GraphQLCompose\GraphQLComposeSchemaTypeBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * {@inheritdoc}
 *
 * @GraphQLComposeSchemaType(
 *   id = "Link",
 * )
 */
class LinkType extends GraphQLComposeSchemaTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getExtensions(): array {
    $extensions = parent::getExtensions();

    $extensions[] = new ObjectType([
      'name' => 'Link',
      'description' => (string) $this->t('A link.'),
      'fields' => fn() => [
        'aria_label' => [
          'type' => Type::string(),
          'description' => (string) $this->t('The aria label of the link.'),
        ],
        'target' => [
          'type' => Type::string(),
          'description' => (string) $this->t('The target of the link.'),
        ],
      ],
    ]);

    return $extensions;
  }

}
