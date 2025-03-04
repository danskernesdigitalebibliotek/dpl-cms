<?php

declare(strict_types=1);

namespace Drupal\bnf\Plugin\bnf_mapper;

use Drupal\bnf\Attribute\BnfMapper;
use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\ParagraphTextBody;
use Drupal\bnf\Plugin\BnfMapperPluginParagraphBase;
use Spawnia\Sailor\ObjectLike;

/**
 * Mapping ParagraphTextBody => text_body.
 */
#[BnfMapper(
  id: ParagraphTextBody::class,
  )]
class ParagraphTextBodyMapper extends BnfMapperPluginParagraphBase {

  /**
   * {@inheritdoc}
   */
  public function map(ObjectLike $object): mixed {
    if (!$object instanceof ParagraphTextBody) {
      throw new \RuntimeException('Wrong class handed to mapper');
    }

    return $this->paragraphStorage->create([
      'type' => 'text_body',
      'field_body' => [
        'value' => $object->body->value ?? '',
        'format' => $object->body->format ?? '',
      ],
    ]);

  }

}
