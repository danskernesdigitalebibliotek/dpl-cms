<?php

namespace Drupal\dpl_loans\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Loan list setting form.
 */
class LoanListSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'dpl_loan_list.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'loan_list_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('dpl_loan_list.settings');

    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Basic settings', [], ['context' => 'Loan list (settings)']),
      '#tree' => FALSE,
    ];

    $form['settings']['fees_page_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fee page url', [], ['context' => 'Loan list (settings)']),
      '#description' => $this->t('The link to the relevant fee page', [], ['context' => 'Loan list (settings)']),
      '#default_value' => $config->get('fees_page_url') ?? '',
    ];

    $form['settings']['material_overdue_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Material overdue url', [], ['context' => 'Loan list (settings)']),
      '#description' => $this->t('The link to the material overdue page', [], ['context' => 'Loan list (settings)']),
      '#default_value' => $config->get('material_overdue_url') ?? '',
    ];

    $form['settings']['page_size_mobile'] = [
      '#type' => 'number',
      '#title' => $this->t('Page size mobile', [], ['context' => 'Loan list (settings)']),
      '#default_value' => $config->get('page_size_mobile') ?? 25,
      '#min' => 0,
      '#step' => 1,
    ];

    $form['settings']['page_size_desktop'] = [
      '#type' => 'number',
      '#title' => $this->t('Page size desktop', [], ['context' => 'Loan list (settings)']),
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
    $feesUrl = $form_state->getValue('fees_page_url');
    if (!filter_var($feesUrl, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('fees_page_url', $this->t('The url "%url" is not a valid URL.', ['%url' => $feesUrl], ['context' => 'Loan list (settings)']));
    }

    $materialUrl = $form_state->getValue('material_overdue_url');
    if (!filter_var($materialUrl, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('material_overdue_url', $this->t('The url "%url" is not a valid URL.', ['%url' => $materialUrl], ['context' => 'Loan list (settings)']));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);

    $this->config('dpl_loan_list.settings')
      ->set('fees_page_url', $form_state->getValue('fees_page_url'))
      ->set('material_overdue_url', $form_state->getValue('material_overdue_url'))
      ->set('page_size_desktop', $form_state->getValue('page_size_desktop'))
      ->set('page_size_mobile', $form_state->getValue('page_size_mobile'))
      ->save();
  }

}
