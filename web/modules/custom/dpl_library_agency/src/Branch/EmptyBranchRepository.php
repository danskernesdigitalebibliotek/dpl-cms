<?php

namespace Drupal\dpl_library_agency\Branch;

use Drupal\Core\Cache\Cache;

/**
 * A branch repository which always returns an empty set.
 */
class EmptyBranchRepository implements BranchRepositoryInterface {

  /**
   * {@inheritdoc}
   */
  public function getBranches(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    return Cache::PERMANENT;
  }

}
