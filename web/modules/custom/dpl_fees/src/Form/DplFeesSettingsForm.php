<?php

namespace Drupal\dpl_fees\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Menu setting form.
 */
class DplFeesSettingsForm extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array
  {
    return [
      'dpl_fees.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'intermediate_list_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $config = $this->config('dpl_fees.settings');

    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Basic settings'),
      '#tree' => FALSE,
    ];

    $form['settings']['fees_and_replacement_costs_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fees and Replacement costs URL'),
      '#description' => $this->t('File or URL containing the fees and replacement costs'),
      '#default_value' => $config->get('fees_and_replacement_costs_url') ?? '',
    ];

    $form['settings']['terms_of_trade_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Terms of trade text'),
      '#description' => $this->t(''),
      '#default_value' => $config->get('terms_of_trade_text') ?? '',
    ];

    $form['settings']['payment_overview_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Payment options image'),
      '#description' => $this->t('Image containing the available payment options (300x35)'),
      '#default_value' => $config->get('payment_overview_url') ?? '',
    ];

    $form['settings']['payment_overview_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Payment options image'),
      '#description' => $this->t('Image containing the available payment options (300x35)'),
      '#default_value' => $config->get('payment_overview_url') ?? '',
    ];

    $form['settings']['intermediate_list_body_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Intro text'),
      '#description' => $this->t('Display an intro-text below the headline'),
      '#default_value' => $config->get('intermediate_list_body_text') ??  'Fees and replacement costs are handled through the new system "Mit betalingsoverblik.',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void
  {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    parent::submitForm($form, $form_state);

    $this->config('dpl_fees.settings')
      ->set('fees_and_replacement_costs_url', $form_state->getValue('fees_and_replacement_costs_url'))
      ->set('terms_of_trade_text', $form_state->getValue('terms_of_trade_text'))
      ->set('payment_overview_url', $form_state->getValue('payment_overview_url'))
      ->set('intermediate_list_body_text', $form_state->getValue('intermediate_list_body_text'))
      ->save();
  }
}
