<?php

namespace Drupal\dpl_patron_page\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Patron page setting form.
 */
class PatronPageSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'patron_page.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'patron_page_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('patron_page.settings');

    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Basic settings'),
      '#tree' => FALSE,
    ];


    $form['settings']['text_notifications_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable SMS notifications'),
      '#default_value' => $config->get('text_notifications_enabled'),
    ];

    $form['settings']['fees_page_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fee page url'),
      '#description' => $this->t('The link to the relevant fee page'),
      '#default_value' => $config->get('fees_page_url') ?? '',
    ];

    $form['settings']['material_overdue_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Material overdue url'),
      '#description' => $this->t('The link to the material overdue page'),
      '#default_value' => $config->get('material_overdue_url') ?? '',
    ];
    $form['settings']['pincode_length'] = [
      '#type' => 'number',
      '#title' => $this->t('Pincode length'),
      '#default_value' => $config->get('pincode_length') ?? 4,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $feesUrl = $form_state->getValue('fees_page_url');
    if (!filter_var($feesUrl, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('fees_page_url', $this->t('The url "%url" is not a valid URL.', ['%url' => $feesUrl]));
    }

    $materialUrl = $form_state->getValue('material_overdue_url');
    if (!filter_var($materialUrl, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('material_overdue_url', $this->t('The url "%url" is not a valid URL.', ['%url' => $materialUrl]));
    }

    $pageSizeMobile = $form_state->getValue('pincode_length');
    if (!is_int($pageSizeMobile) && $pageSizeMobile <= 0) {
      $form_state->setErrorByName('pincode_length', $this->t('Pincode length has to be a positive integer'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);

    $this->config('patron_page.settings')
      ->set('fees_page_url', $form_state->getValue('fees_page_url'))
      ->set('material_overdue_url', $form_state->getValue('material_overdue_url'))
      ->set('text_notifications_enabled', $form_state->getValue('text_notifications_enabled'))
      ->set('pincode_length', $form_state->getValue('pincode_length'))
      ->save();
  }

}