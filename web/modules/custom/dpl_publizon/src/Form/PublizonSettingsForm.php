<?php
/**
 * @file
 * Contains Drupal\dpl_publizon\Form\PublizonSettingsForm.
 */

namespace Drupal\dpl_publizon\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class PublizonSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'publizon.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'publizon_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('publizon.settings');

    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Basic settings'),
      '#tree' => FALSE,
    ];

    $form['settings']['base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Publizon service url'),
      '#description' => $this->t('Which publizon service should be used (default: https://pubhub-openplatform.dbc.dk, QA: https://pubhub-openplatform.test.dbc.dk).'),
      '#default_value' => $config->get('base_url') ?? 'https://pubhub-openplatform.dbc.dk',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue('base_url');
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('base_url', $this->t('The url "%url" is not a valid URL.', ['%url' => $url]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('publizon.settings')
      ->set('base_url', $form_state->getValue('base_url'))
      ->save();
  }
}
