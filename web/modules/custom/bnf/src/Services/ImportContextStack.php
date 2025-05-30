<?php

declare(strict_types=1);

namespace Drupal\bnf\Services;

use Drupal\bnf\ImportContext;

/**
 * Manages the current import context.
 *
 * When mapping a top level element, BnfImporter creates an ImportContext with
 * the relevant context of the import. Mappers can get the current context from
 * this service.
 *
 * This is implemented as a stack to allow for recursive importing.
 */
class ImportContextStack {

  /**
   * Stack of current import contexts.
   *
   * @var \Drupal\bnf\ImportContext[]
   */
  protected array $stack;

  /**
   * Push an import context on the stack.
   */
  public function push(ImportContext $context): void {
    $this->stack[] = $context;
  }

  /**
   * Pop an import context from the stack.
   */
  public function pop(): ImportContext {
    $context = array_pop($this->stack);

    if (!$context) {
      throw new \RuntimeException('No current import context');
    }

    return $context;
  }

  /**
   * Return the current context on the stack.
   */
  public function current(): ImportContext {
    $context = end($this->stack);

    if (!$context) {
      throw new \RuntimeException('No current import context');
    }

    return $context;
  }

  /**
   * Return the size of the stack.
   */
  public function size(): int {
    return count($this->stack);
  }

}
