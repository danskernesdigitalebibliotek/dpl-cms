<?php

declare(strict_types=1);

namespace Drupal\Tests\bnf_client\Unit\Services;

use Drupal\bnf_client\Services\SubscriptionCreator;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Tests the SubscriptionCreator service.
 *
 * @group bnf_client
 * @coversDefaultClass \Drupal\bnf_client\Services\SubscriptionCreator
 */
class SubscriptionCreatorTest extends UnitTestCase {

  /**
   * EntityTypeManager prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<\Drupal\Core\Entity\EntityTypeManagerInterface>
   */
  protected ObjectProphecy $entityTypeManagerProphecy;

  /**
   * Subscription storage prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<\Drupal\Core\Entity\EntityStorageInterface>
   */
  protected ObjectProphecy $subscriptionStorageProphecy;

  /**
   * Term storage prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<\Drupal\Core\Entity\EntityStorageInterface>
   */
  protected ObjectProphecy $termStorageProphecy;

  /**
   * The service under test.
   */
  protected SubscriptionCreator $subscriptionCreator;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->entityTypeManagerProphecy = $this->prophesize(EntityTypeManagerInterface::class);
    $this->subscriptionStorageProphecy = $this->prophesize(EntityStorageInterface::class);
    $this->termStorageProphecy = $this->prophesize(EntityStorageInterface::class);

    $this->entityTypeManagerProphecy
      ->getStorage('bnf_subscription')
      ->willReturn($this->subscriptionStorageProphecy->reveal());

    $this->entityTypeManagerProphecy
      ->getStorage('taxonomy_term')
      ->willReturn($this->termStorageProphecy->reveal());

    $this->subscriptionCreator = new SubscriptionCreator(
      $this->entityTypeManagerProphecy->reveal()
    );
  }

  /**
   * Test that invalid UUID throws exception.
   *
   * @covers ::addSubscription
   */
  public function testInvalidUuidThrowsException(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('A valid UUID is required for subscription_uuid.');

    $this->subscriptionCreator->addSubscription('not-a-valid-uuid', 'Test Label');
  }

  /**
   * Test that empty label throws exception.
   *
   * @covers ::addSubscription
   */
  public function testEmptyLabelThrowsException(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Label cannot be empty.');

    $this->subscriptionCreator->addSubscription(
      '4669c003-5673-46eb-9950-aa62ca4b4a2f',
      ''
    );
  }

  /**
   * Test that existing subscription is skipped.
   *
   * @covers ::addSubscription
   */
  public function testExistingSubscriptionIsSkipped(): void {
    $uuid = '4669c003-5673-46eb-9950-aa62ca4b4a2f';
    $label = 'Test Subscription';

    // Mock that a subscription already exists.
    $existingSubscription = $this->prophesize(\Drupal\bnf_client\Entity\Subscription::class);
    $this->subscriptionStorageProphecy
      ->loadByProperties(['subscription_uuid' => $uuid])
      ->willReturn([$existingSubscription->reveal()]);

    $result = $this->subscriptionCreator->addSubscription($uuid, $label);

    $this->assertStringContainsString('already exists', $result);
    $this->assertStringContainsString('Skipping creation', $result);

    // Verify create was never called.
    $this->subscriptionStorageProphecy->create(Argument::any())->shouldNotHaveBeenCalled();
  }

  /**
   * Test successful subscription creation without tag.
   *
   * @covers ::addSubscription
   */
  public function testSuccessfulSubscriptionCreationWithoutTag(): void {
    $uuid = '4669c003-5673-46eb-9950-aa62ca4b4a2f';
    $label = 'Test Subscription';

    // No existing subscription.
    $this->subscriptionStorageProphecy
      ->loadByProperties(['subscription_uuid' => $uuid])
      ->willReturn([]);

    // Mock subscription entity.
    $subscriptionEntity = $this->prophesize(\Drupal\bnf_client\Entity\Subscription::class);
    $subscriptionEntity->save()->shouldBeCalled();

    $this->subscriptionStorageProphecy
      ->create([
        'subscription_uuid' => $uuid,
        'label' => $label,
      ])
      ->willReturn($subscriptionEntity->reveal());

    $result = $this->subscriptionCreator->addSubscription($uuid, $label);

    $this->assertStringContainsString('Successfully created subscription', $result);
    $this->assertStringContainsString($label, $result);
  }

  /**
   * Test successful subscription creation with new tag.
   *
   * @covers ::addSubscription
   */
  public function testSuccessfulSubscriptionCreationWithNewTag(): void {
    $uuid = '4669c003-5673-46eb-9950-aa62ca4b4a2f';
    $label = 'Test Subscription';
    $tagName = 'My New Tag';

    // No existing subscription.
    $this->subscriptionStorageProphecy
      ->loadByProperties(['subscription_uuid' => $uuid])
      ->willReturn([]);

    // No existing tag.
    $this->termStorageProphecy
      ->loadByProperties(['name' => $tagName, 'vid' => 'tags'])
      ->willReturn([]);

    // Mock new term creation.
    $termEntity = $this->prophesize(\Drupal\taxonomy\TermInterface::class);
    $termEntity->save()->shouldBeCalled();
    $termEntity->id()->willReturn(42);

    $this->termStorageProphecy
      ->create(['name' => $tagName, 'vid' => 'tags'])
      ->willReturn($termEntity->reveal());

    // Mock subscription entity.
    $subscriptionEntity = $this->prophesize(\Drupal\bnf_client\Entity\Subscription::class);
    $subscriptionEntity->save()->shouldBeCalled();

    $this->subscriptionStorageProphecy
      ->create([
        'subscription_uuid' => $uuid,
        'label' => $label,
        'tags' => [['target_id' => 42]],
      ])
      ->willReturn($subscriptionEntity->reveal());

    $result = $this->subscriptionCreator->addSubscription($uuid, $label, $tagName);

    $this->assertStringContainsString('Created new tag', $result);
    $this->assertStringContainsString('Successfully created subscription', $result);
    $this->assertStringContainsString('automatically tag imported content', $result);
  }

  /**
   * Test successful subscription creation with existing tag.
   *
   * @covers ::addSubscription
   */
  public function testSuccessfulSubscriptionCreationWithExistingTag(): void {
    $uuid = '4669c003-5673-46eb-9950-aa62ca4b4a2f';
    $label = 'Test Subscription';
    $tagName = 'Existing Tag';

    // No existing subscription.
    $this->subscriptionStorageProphecy
      ->loadByProperties(['subscription_uuid' => $uuid])
      ->willReturn([]);

    // Existing tag found.
    $existingTerm = $this->prophesize(\Drupal\taxonomy\TermInterface::class);
    $existingTerm->id()->willReturn(99);

    $this->termStorageProphecy
      ->loadByProperties(['name' => $tagName, 'vid' => 'tags'])
      ->willReturn([$existingTerm->reveal()]);

    // Mock subscription entity.
    $subscriptionEntity = $this->prophesize(\Drupal\bnf_client\Entity\Subscription::class);
    $subscriptionEntity->save()->shouldBeCalled();

    $this->subscriptionStorageProphecy
      ->create([
        'subscription_uuid' => $uuid,
        'label' => $label,
        'tags' => [['target_id' => 99]],
      ])
      ->willReturn($subscriptionEntity->reveal());

    $result = $this->subscriptionCreator->addSubscription($uuid, $label, $tagName);

    $this->assertStringContainsString('Found existing tag', $result);
    $this->assertStringContainsString('Successfully created subscription', $result);

    // Verify no new term was created.
    $this->termStorageProphecy->create(Argument::any())->shouldNotHaveBeenCalled();
  }

}
