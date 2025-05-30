<?php

declare(strict_types=1);

namespace Drupal\Tests\bnf\Unit\Mapper;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * Base class for testing mappers that produce entities.
 */
abstract class EntityMapperTestBase extends UnitTestCase {

  /**
   * EntityManager prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<\Drupal\Core\Entity\EntityTypeManagerInterface>
   */
  protected ObjectProphecy $entityManagerProphecy;

  /**
   * FileSystemInterface prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<\Drupal\Core\File\FileSystemInterface>
   */
  protected ObjectProphecy $fileSystemProphecy;

  /**
   * FileRepositoryInterface prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<\Drupal\file\FileRepositoryInterface>
   */
  protected ObjectProphecy $fileRepositoryProphecy;

  /**
   * EntityStorage prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<\Drupal\Core\Entity\EntityStorageInterface>
   */
  protected ObjectProphecy $storageProphecy;

  /**
   * Entity prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<\Drupal\Core\Entity\EntityBase>
   */
  protected ObjectProphecy $entityProphecy;

  /**
   * Translation prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<\Drupal\Core\StringTranslation\TranslationInterface>
   */
  protected ObjectProphecy $translationProphecy;

  /**
   * Logger prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<\Psr\Log\LoggerInterface>
   */
  protected ObjectProphecy $loggerProphecy;

  /**
   * Setup for each test.
   */
  public function setUp(): void {
    parent::setUp();

    $this->entityManagerProphecy = $this->prophesize(EntityTypeManagerInterface::class);
    $this->storageProphecy = $this->prophesize(EntityStorageInterface::class);
    $this->entityManagerProphecy->getStorage($this->getEntityName())->willReturn($this->storageProphecy);
    $this->fileRepositoryProphecy = $this->prophesize(FileRepositoryInterface::class);
    $this->fileSystemProphecy = $this->prophesize(FileSystemInterface::class);
    $this->translationProphecy = $this->prophesize(TranslationInterface::class);
    $this->loggerProphecy = $this->prophesize(LoggerInterface::class);
    $this->entityProphecy = $this->prophesize($this->getEntityClass());
  }

  /**
   * Return the name of the entity type to work with.
   */
  abstract protected function getEntityName(): string;

  /**
   * Return the class name of the entity to work with.
   */
  abstract protected function getEntityClass(): string;

}
