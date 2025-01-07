<?php

declare(strict_types=1);

namespace Drupal\dpl_consumers;

use Drupal\user\Entity\Role;

/**
 * DPL consumer role.
 *
 * This class is responsible for loading and deleting consumer related roles.
 */
class ConsumerRole {

  public function __construct(
    public string $id,
  ) {}

  /**
   * Load a consumer role.
   */
  public function load(): Role {
    if (!$role = Role::load($this->id)) {
      throw new \RuntimeException(sprintf('Failed to load role: %s', $this->id));
    }

    return $role;
  }

  /**
   * Delete a consumer role.
   */
  public function delete(): void {
    try {
      $role = $this->load();
    }
    catch (\Exception $e) {
      // Does not matter if loading fails.
      // We just do not try to delete the role then.
      return;
    }
    $role->delete();
  }

}
