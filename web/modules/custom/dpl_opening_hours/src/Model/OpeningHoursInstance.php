<?php

namespace Drupal\dpl_opening_hours\Model;

use Drupal\dpl_opening_hours\Model\Repetition\NoRepetition;
use Drupal\dpl_opening_hours\Model\Repetition\Repetition;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Value object which defines a single time period where a branch is open.
 */
class OpeningHoursInstance {

  /**
   * Constructor.
   */
  public function __construct(
    public readonly ?int $id,
    public readonly NodeInterface $branch,
    public readonly TermInterface $categoryTerm,
    public readonly \DateTimeInterface $startTime,
    public readonly \DateTimeInterface $endTime,
    public readonly Repetition $repetition = new NoRepetition()
  ) {
    $startTimeString = $this->startTime->format('c');
    $endTimeString = $this->endTime->format('c');
    $diff = $this->startTime->diff($this->endTime);
    if ($diff->invert) {
      throw new \InvalidArgumentException(
        "End time ({$endTimeString}) must be greater than start time ({$startTimeString})"
      );
    }

    if ($diff->d > 0) {
      throw new \InvalidArgumentException(
        "End date ({$endTimeString}}) must be within a day of start date ({$startTimeString})"
      );
    }
  }

}
