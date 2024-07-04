<?php

declare(strict_types=1);

namespace Drupal\collation_fixer\Commands;

use Drupal\collation_fixer\CollationFixer;
use Drupal\collation_fixer\CollationMismatch;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for fixing collations from the command line.
 */
final class CollationFixerCommands extends DrushCommands {

  /**
   * Constructor.
   */
  public function __construct(
    private CollationFixer $collationFixer,
  ) {
    parent::__construct();
  }

  /**
   * Fix collation for one or more tables.
   *
   * @command collation-fixer
   * @option table Name of a table to fix collation on. Defaults to all tables.
   */
  public function fixTable(array $options = ['table' => NULL]) : void {
    $tables = $this->collationFixer->checkCollation($options['table']);
    $fixes = array_filter(array_map(function (CollationMismatch $mismatch) {
      return $this->collationFixer->fixCollation($mismatch->actual->table);
    }, $tables));

    $numFixes = count($fixes);
    if ($numFixes > 0) {
      $this->io()->success("{$numFixes} tables were processed.");
    }
    else {
      $this->io()->info('No collation fixes needs to be done');
    }
  }

}
