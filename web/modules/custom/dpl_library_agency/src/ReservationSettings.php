<?php

namespace Drupal\dpl_library_agency;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigManagerInterface;

/**
 * Class that handles reservation settings for a library agency.
 */
class ReservationSettings implements CacheableDependencyInterface {

  /**
   * Constructs a new ReservationSettings object.
   */
  public function __construct(
    protected ConfigManagerInterface $configManager
  ) {}

  /**
   * Get the configuration entity containing reservation settings.
   */
  protected function getConfig(): Config {
    return $this->configManager->getConfigFactory()->get('dpl_library_agency.general_settings');
  }

  /**
   * Checks whether sms notifications for reservations are enabled.
   *
   * @return bool
   *   TRUE if sms notifications are enabled, FALSE otherwise.
   */
  public function smsNotificationsIsEnabled(): bool {
    $config = $this->getConfig();
    return ($config->get('reservation_sms_notifications_disabled')) ? FALSE : TRUE;
  }

  /**
   * Checks whether ready reservations are allowed to be deleted.
   *
   * @return bool
   *   TRUE if allowed to be deleted, FALSE otherwise.
   */
  public function deleteReadyReservationsEnabled(): bool {
    $config = $this->getConfig();
    return ($config->get('reservation_detail_allow_remove_ready_reservations_config')) ? TRUE : FALSE;
  }

  /**
   * Checks whether the one-month interest period should be shown.
   *
   * @return bool
   *   TRUE if allowed to be deleted, FALSE otherwise.
   */
  public function interestPeriodOneMonthEnabled(): bool {
    $config = $this->getConfig();
    return ($config->get('interest_period_one_month_config_text')) ? TRUE : FALSE;
  }

  /**
   * Checks whether the two-months interest period should be shown.
   *
   * @return bool
   *   TRUE if allowed to be deleted, FALSE otherwise.
   */
  public function interestPeriodTwoMonthsEnabled(): bool {
    $config = $this->getConfig();
    return ($config->get('interest_period_two_months_config_text')) ? TRUE : FALSE;
  }

  /**
   * Checks whether the three-months interest period should be shown.
   *
   * @return bool
   *   TRUE if allowed to be deleted, FALSE otherwise.
   */
  public function interestPeriodThreeMonthsEnabled(): bool {
    $config = $this->getConfig();
    return ($config->get('interest_period_three_months_config_text')) ? TRUE : FALSE;
  }

  /**
   * Checks whether the six-months interest period should be shown.
   *
   * @return bool
   *   TRUE if allowed to be deleted, FALSE otherwise.
   */
  public function interestPeriodSixMonthsEnabled(): bool {
    $config = $this->getConfig();
    return ($config->get('interest_period_six_months_config_text')) ? TRUE : FALSE;
  }

  /**
   * Checks whether the twelve-months interest period should be shown.
   *
   * @return bool
   *   TRUE if allowed to be deleted, FALSE otherwise.
   */
  public function interestPeriodTwelveMonthsEnabled(): bool {
    $config = $this->getConfig();
    return ($config->get('interest_period_one_year_config_text')) ? TRUE : FALSE;
  }

  /**
   * Sets whether sms notifications for reservations should be enabled.
   *
   * @param bool $enabled
   *   TRUE if sms notifications should be enabled, FALSE otherwise.
   */
  public function enableSmsNotifications(bool $enabled) : void {
    $this->getConfig()
      ->set('reservation_sms_notifications_disabled', $enabled)
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() : array {
    return $this->getConfig()->getCacheContexts();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() : array {
    return $this->getConfig()->getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() : int {
    return $this->getConfig()->getCacheMaxAge();
  }

}
