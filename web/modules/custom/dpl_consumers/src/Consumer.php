<?php

declare(strict_types=1);

namespace Drupal\dpl_consumers;

use Drupal\consumers\Entity\ConsumerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * DPL consumer.
 *
 * This class is responsible for creating consumers.
 */
class Consumer {

  public function __construct(
    public string $clientId,
    protected EntityTypeManagerInterface $entityTypeManager,
    public string | NULL $label = NULL,
    public string | NULL $secret = NULL,
  ) {}

  /**
   * Create a consumer.
   */

  /**
   * Create a consumer.
   *
   * @param ConsumerUser $user
   *   The user to connect the consumer to.
   * @param ConsumerRole $role
   *   The role to connect the consumer to.
   */
  public function create(ConsumerUser $user, ConsumerRole $role): ConsumerInterface {
    if (!$this->label || !$this->clientId || !$this->secret) {
      throw new \RuntimeException('Label, client ID and secret are required to create a consumer.');
    }
    $consumer = $this->entityTypeManager->getStorage('consumer')->create([
      'label' => $this->label,
      'client_id' => $this->clientId,
      'secret' => $this->secret,
      'third_party' => FALSE,
      'user_id' => $user->load()->id(),
      'roles' => [$role->load()->id()],
    ]);

    $consumer->save();

    if (!($consumer instanceof ConsumerInterface)) {
      throw new \RuntimeException('Failed to create consumer.');
    }

    return $consumer;
  }

  /**
   * Load a consumer.
   */
  public function load(): ConsumerInterface | NULL {
    $consumers = $this->entityTypeManager->getStorage('consumer')
      ->loadByProperties(['client_id' => $this->clientId]);

    if (empty($consumers)) {
      return NULL;
    }

    $consumer = reset($consumers);

    if (!($consumer instanceof ConsumerInterface)) {
      return NULL;
    }

    return $consumer;
  }

  /**
   * Delete a consumer based on a client ID.
   */
  public function delete(): void {
    if ($consumer = $this->load()) {
      $consumer->delete();
    }
  }

}
