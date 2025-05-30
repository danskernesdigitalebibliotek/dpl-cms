<?php

declare(strict_types=1);

namespace Drupal\bnf\Plugin\bnf_mapper;

use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\BannerLink\Link as BannerLink;
use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\GoLinkParagraph\LinkRequired\Link as GoLink;
use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\HeroLink\Link as HeroLink;
use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\Link\Link as LinksLink;
use Drupal\bnf\Plugin\Traits\LinkTrait;
use Drupal\bnf\Services\BnfImporter;
use Drupal\bnf\Services\ImportContextStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Spawnia\Sailor\ObjectLike;

/**
 * Base class for link fields that import linked content.
 */
abstract class BnfMapperImportedLinkFieldPluginBase extends BnfMapperPluginBase {

  use LinkTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ImportContextStack $importContext,
    protected BnfImporter $importer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function map(ObjectLike $object): mixed {
    if (!in_array(get_class($object), [
      BannerLink::class,
      GoLink::class,
      HeroLink::class,
      LinksLink::class,
    ])) {
      throw new \RuntimeException('Wrong class handed to mapper');
    }

    if ($object->internal && is_string($object->id)) {
      // Recursion protection.
      if ($this->importContext->size() > 3) {
        return NULL;
      }
      /** @var \Drupal\node\Entity\Node[] $existing */
      $existing = $this->entityTypeManager->getStorage('node')->loadByProperties(['uuid' => $object->id]);

      if ($existing) {
        $node = reset($existing);
      }
      else {
        $node = $this->importer->importNode($object->id, $this->importContext->current());
      }

      if (!$node) {
        return NULL;
      }

      $goLinkValue = [
        'uri' => 'entity:node/' . $node->id(),
        'title' => $object->title,
      ];
    }
    else {
      $goLinkValue = $this->getLinkValue($object);
    }

    return $goLinkValue;
  }

}
