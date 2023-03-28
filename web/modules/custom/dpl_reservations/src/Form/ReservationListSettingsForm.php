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
      'reservation_list.settings',
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
    $config = $this->config('reservation_list.settings');

    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Basic settings'),
      '#tree' => FALSE,
    ];

    $form['settings']['pause_reservation_info_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pause reservation link'),
      '#description' => $this->t('The link in the pause reservation modal'),
      '#default_value' => $config->get('pause_reservation_info_url') ?? '',
    ];

    $form['settings']['ereolen_my_page_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ereolen link'),
      '#description' => $this->t('My page in ereolen'),
      '#default_value' => $config->get('ereolen_my_page_url') ?? '',
    ];
    $form['settings']['pause_reservation_start_date_config'] = [
      '#type' => 'date',
      '#title' => $this->t('Start date'),
      '#description' => $this->t('Pause reservation start date'),
      '#default_value' => $config->get('pause_reservation_start_date_config'),
    ];

    $form['settings']['page_size_mobile'] = [
      '#type' => 'number',
      '#title' => $this->t('Page size mobile'),
      '#default_value' => $config->get('page_size_mobile') ?? 25,
    ];

    $form['settings']['page_size_desktop'] = [
      '#type' => 'number',
      '#title' => $this->t('Page size desktop'),
      '#default_value' => $config->get('page_size_desktop') ?? 25,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $feesUrl = $form_state->getValue('pause_reservation_info_url');
    if (!filter_var($feesUrl, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('pause_reservation_info_url', $this->t('The url "%url" is not a valid URL.', ['%url' => $feesUrl]));
    }

    $materialUrl = $form_state->getValue('ereolen_my_page_url');
    if (!filter_var($materialUrl, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('ereolen_my_page_url', $this->t('The url "%url" is not a valid URL.', ['%url' => $materialUrl]));
    }

    $pageSizeMobile = $form_state->getValue('page_size_mobile');
    if (is_int($pageSizeMobile) && $pageSizeMobile <= 0) {
      $form_state->setErrorByName('page_size_mobile', $this->t('Page size mobile has to be a positive integer'));
    }

    $pageSizeDesktop = $form_state->getValue('page_size_desktop');
    if (is_int($pageSizeDesktop) && $pageSizeDesktop <= 0) {
      $form_state->setErrorByName('page_size_desktop', $this->t('Page size desktop has to be a positive integer'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);

    $this->config('reservation_list.settings')
      ->set('pause_reservation_info_url', $form_state->getValue('pause_reservation_info_url'))
      ->set('ereolen_my_page_url', $form_state->getValue('ereolen_my_page_url'))
      ->set('pause_reservation_start_date_config', $form_state->getValue('pause_reservation_start_date_config'))
      ->set('page_size_desktop', $form_state->getValue('page_size_desktop'))
      ->set('page_size_mobile', $form_state->getValue('page_size_mobile'))
      ->save();
  }

}
