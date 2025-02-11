<?php

declare(strict_types=1);

namespace Drupal\bnf\Plugin\bnf_mapper;

use Drupal\bnf\Attribute\BnfMapper;
use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\ParagraphAccordion;
use Drupal\bnf\Plugin\BnfMapperPluginBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Spawnia\Sailor\ObjectLike;

/**
 * Maps accordion paragraphs.
 */
#[BnfMapper(
  id: ParagraphAccordion::class,
  )]
class ParagraphAccordionMapper extends BnfMapperPluginBase {

  /**
   * Entity storage to create paragroph in.
   */
  protected EntityStorageInterface $paragraphStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->paragraphStorage = $entityTypeManager->getStorage('paragraph');
  }

  /**
   * {@inheritdoc}
   */
  public function map(ObjectLike $object): mixed {
    if (!$object instanceof ParagraphAccordion) {
      throw new \RuntimeException('Wrong class handed to mapper');
    }

    /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
    $paragraph = $this->paragraphStorage->create([
      'type' => 'accordion',
    ]);

    $paragraph->set('field_accordion_title', [
      'value' => $object->accordionTitle->value,
      'format' => $object->accordionTitle->format,
    ]);

    $paragraph->set('field_accordion_description', [
      'value' => $object->accordionDescription->value ?? '',
      'format' => $object->accordionDescription->format ?? '',
    ]);

    return $paragraph;
  }

}
