<?php

declare(strict_types=1);

namespace Drupal\bnf_client\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Webmozart\Assert\Assert;

/**
 * Helper for programmatically creating BNF subscriptions.
 */
final class SubscriptionCreator {
  public const MODULE_NOT_ENABLED_MESSAGE = 'The bnf_client module is not enabled. Subscription could not be created.';

  /**
   * Constructs a SubscriptionCreator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager used to load and create subscription and taxonomy
   *   term entities.
   */
  public function __construct(private EntityTypeManagerInterface $entityTypeManager) {}

  /**
   * Convenience: Add subscription if service is available, else return message.
   *
   * @param string $subscriptionUuid
   *   The UUID of the term on Delingstjenesten to subscribe to.
   * @param string $label
   *   The label for the subscription.
   * @param string|null $tagName
   *   Optional tag name to create and associate with the subscription.
   *
   * @return string
   *   A feedback message indicating the result, or
   *   SubscriptionCreator::MODULE_NOT_ENABLED_MESSAGE if the service
   *   is not available.
   */
  public static function addIfAvailable(string $subscriptionUuid, string $label, ?string $tagName = NULL): string {
    if (!\Drupal::hasService('bnf_client.subscription_creator')) {
      return self::MODULE_NOT_ENABLED_MESSAGE;
    }

    /** @var self $service */
    $service = \Drupal::service('bnf_client.subscription_creator');
    return $service->addSubscription($subscriptionUuid, $label, $tagName);
  }

  /**
   * Add a BNF subscription.
   *
   * @param string $subscriptionUuid
   *   The UUID of the term on Delingstjenesten to subscribe to.
   * @param string $label
   *   The label for the subscription.
   * @param string|null $tagName
   *   Optional tag name to create and associate with the subscription.
   *   If provided, a taxonomy term will be created (or reused) in the 'tags'
   *   vocabulary and the subscription will be configured to automatically tag
   *   all imported content with this term.
   *
   * @return string
   *   Feedback message.
   */
  public function addSubscription(string $subscriptionUuid, string $label, ?string $tagName = NULL): string {
    Assert::uuid($subscriptionUuid, 'A valid UUID is required for subscription_uuid.');
    Assert::notEmpty($label, 'Label cannot be empty.');

    $feedback = [];

    $subscriptionStorage = $this->entityTypeManager->getStorage('bnf_subscription');

    /** @var \Drupal\bnf_client\Entity\Subscription[] $existing */
    $existing = $subscriptionStorage->loadByProperties([
      'subscription_uuid' => $subscriptionUuid,
    ]);

    if ($existing) {
      return "The subscription '$label' ($subscriptionUuid) already exists. Skipping creation.";
    }

    // Create the subscription.
    $subscriptionData = [
      'subscription_uuid' => $subscriptionUuid,
      'label' => $label,
    ];

    // Create and associate taxonomy term if tag name is provided.
    $hasTagName = $tagName !== null && $tagName !== '';
    if ($hasTagName) {
      $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');

      /** @var \Drupal\taxonomy\Entity\Term[] $existingTerms */
      $existingTerms = $termStorage->loadByProperties([
        'name' => $tagName,
        'vid' => 'tags',
      ]);

      if ($existingTerms) {
        $tagTerm = reset($existingTerms);
        $feedback[] = "Found existing tag '$tagName' (ID: {$tagTerm->id()}).";
      }
      else {
        /** @var \Drupal\taxonomy\TermInterface $tagTerm */
        $tagTerm = $termStorage->create([
          'name' => $tagName,
          'vid' => 'tags',
        ]);
        $tagTerm->save();
        $feedback[] = "Created new tag '$tagName' (ID: {$tagTerm->id()}).";
      }

      $subscriptionData['tags'] = [['target_id' => $tagTerm->id()]];
    }

    /** @var \Drupal\bnf_client\Entity\Subscription $subscription */
    $subscription = $subscriptionStorage->create($subscriptionData);
    $subscription->save();

    $feedback[] = "Successfully created subscription for '$label' ($subscriptionUuid).";

    if ($hasTagName) {
      $feedback[] = "Subscription configured to automatically tag imported content with '$tagName'.";
    }

    return implode("\n", $feedback);
  }

}
