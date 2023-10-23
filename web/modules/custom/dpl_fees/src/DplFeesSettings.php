<?php

namespace Drupal\dpl_fees;

use Drupal\dpl_react\DplReactConfigBase;

/**
 * Class that handles instant loan settings.
 */
class DplFeesSettings extends DplReactConfigBase {
  const FEES_AND_REPLACEMENT_COSTS_URL = '';
  const TERMS_OF_TRADE_TEXT = '';
  const TERMS_OF_TRADE_URL = '';
  const PAYMENT_OVERVIEW_URL = '';
  const FEE_LIST_BODY_TEXT = '';
  const PAGE_SIZE_DESKTOP = 25;
  const PAGE_SIZE_MOBILE = 25;
  const AVAILABLE_PAYMENT_TYPES_URL = '';

  /**
   * Gets the configuration key for the instant loan settings.
   */
  public function getConfigKey(): string {
    return 'dpl_fees.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(): array {
    return $this->legacyConfig();
  }

}
