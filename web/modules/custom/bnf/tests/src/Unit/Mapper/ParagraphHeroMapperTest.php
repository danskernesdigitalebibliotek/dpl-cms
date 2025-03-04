<?php

declare(strict_types=1);

namespace Drupal\Tests\bnf\Unit\Mapper;

use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\Body\Text;
use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\HeroDate\DateTime;
use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\HeroImage\MediaImage;
use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\HeroLink\Link;
use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\ParagraphHero;
use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\ParagraphTextBody;
use Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\HeroDescription\Text as HeroDescription;
use Drupal\bnf\Plugin\bnf_mapper\ParagraphHeroMapper;
use Drupal\bnf\Plugin\bnf_mapper\ParagraphTextBodyMapper;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\StreamWrapper\PublicStream;
use Prophecy\PhpUnit\ProphecyTrait;
/**
 * Tests the hero paragraph mapper.
 */
class ParagraphHeroMapperTest extends EntityMapperTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getEntityName(): string {
    return 'paragraph';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityClass(): string {
    return Paragraph::class;
  }

  /**
   * Test hero paragraph mapping.
   */
  public function testParagraphHeroMapping(): void {
    $logoUrl = 'https://images.squarespace-cdn.com/content/v1/62b33f3f92756a580d5bd588/42eb9748-baa3-43d2-8e63-72975db26e8e/reload+logo+venstrestillet.png?format=200w';

    $this->storageProphecy->create([
      'type' => 'hero',
      'field_hero_content_type' => 'The content type',
      // We do not support term categories. See info in mapper.
      'field_hero_categories' => [],
      'field_hero_description' => [
        'value' => 'The description',
        'format' => 'some_format',
      ],
      'field_hero_link' => [
        'uri' => 'http://example.com/',
        'title' => 'The website of Reload'
      ],
      'field_hero_date' => '2025-01-01',
      'field_hero_title' => 'The title',
    ])->willReturn($this->entityProphecy);


    $fileSystem = $this->prophesize(FileSystemInterface::class)->reveal();
    $fileRepository = $this->prophesize(FileRepositoryInterface::class)->reveal();

    $mapper = new ParagraphHeroMapper([], '', [], $this->entityManagerProphecy->reveal(), $fileSystem, $fileRepository);

    $graphqlElement = ParagraphHero::make(
      'hero',
      'The title',
      // Category terms are not supported. See info in mapper.
      [],
      'The content type',
      HeroDescription::make(
        'The description',
        'some_format'
      ),
      DateTime::make(
        '1735686000', 'Europe/Copenhagen', FALSE
      ),
      MediaImage::make(
        'media',
        'Logo of Reload',
        MediaImage\Image::make(
          $logoUrl
        )
      ),
      Link::make(
        FALSE,
        'The website of Reload',
        'https://reload.dk'
      ),
    );

    $paragraph = $mapper->map($graphqlElement);

    if (true) {}
    $this->assertSame($paragraph, $this->entityProphecy->reveal());
  }

}
