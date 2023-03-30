<?php

namespace Drupal\dpl_reservations\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Reservation list setting form.
 */
class ReservationListSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'dpl_reservation_list.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'reservation_list_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('dpl_reservation_list.settings');

    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Basic settings', [], ['context' => 'Reservation list (settings)']),
      '#tree' => FALSE,
    ];

    $form['settings']['pause_reservation_info_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pause reservation link', [], ['context' => 'Reservation list (settings)']),
      '#description' => $this->t('The link in the pause reservation modal', [], ['context' => 'Reservation list (settings)']),
      '#default_value' => $config->get('pause_reservation_info_url') ?? '',
    ];

    $form['settings']['ereolen_my_page_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ereolen link', [], ['context' => 'Reservation list (settings)']),
      '#description' => $this->t('My page in ereolen', [], ['context' => 'Reservation list (settings)']),
      '#default_value' => $config->get('ereolen_my_page_url') ?? '',
    ];
    $form['settings']['pause_reservation_start_date_config'] = [
      '#type' => 'date',
      '#title' => $this->t('Start date', [], ['context' => 'Reservation list (settings)']),
      '#description' => $this->t('Pause reservation start date', [], ['context' => 'Reservation list (settings)']),
      '#default_value' => $config->get('pause_reservation_start_date_config'),
    ];

    $form['settings']['page_size_mobile'] = [
      '#type' => 'number',
      '#title' => $this->t('Page size mobile', [], ['context' => 'Reservation list (settings)']),
      '#default_value' => $config->get('page_size_mobile') ?? 25,
      '#min' => 0,
      '#step' => 1,
    ];

    $form['settings']['page_size_desktop'] = [
      '#type' => 'number',
      '#title' => $this->t('Page size desktop', [], ['context' => 'Reservation list (settings)']),
      '#default_value' => $config->get('page_size_desktop') ?? 25,
      '#min' => 0,
      '#step' => 1,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $feesUrl = $form_state->getValue('pause_reservation_info_url');
    if (!filter_var($feesUrl, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('pause_reservation_info_url', $this->t('The url "%url" is not a valid URL.', ['%url' => $feesUrl], ['context' => 'Reservation list (settings)']));
    }

    $materialUrl = $form_state->getValue('ereolen_my_page_url');
    if (!filter_var($materialUrl, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('ereolen_my_page_url', $this->t('The url "%url" is not a valid URL.', ['%url' => $materialUrl], ['context' => 'Reservation list (settings)']));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);

    $this->config('dpl_reservation_list.settings')
      ->set('pause_reservation_info_url', $form_state->getValue('pause_reservation_info_url'))
      ->set('ereolen_my_page_url', $form_state->getValue('ereolen_my_page_url'))
      ->set('pause_reservation_start_date_config', $form_state->getValue('pause_reservation_start_date_config'))
      ->set('page_size_desktop', $form_state->getValue('page_size_desktop'))
      ->set('page_size_mobile', $form_state->getValue('page_size_mobile'))
      ->save();
  }

}
