<?php
/**
 * @file
 * Contains Drupal\dpl_fbs\Form\FbsSettingsForm.
 */

namespace Drupal\dpl_fbs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class FbsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'fbs.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'fbs_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('fbs.settings');

    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Basic settings'),
      '#tree' => FALSE,
    ];

    $form['settings']['base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('FBS service url'),
      '#description' => $this->t('Which FBS service should be used (default: https://fbs-openplatform.dbc.dk).'),
      '#default_value' => $config->get('base_url') ?? 'https://fbs-openplatform.dbc.dk',
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

    $this->config('fbs.settings')
      ->set('base_url', $form_state->getValue('base_url'))
      ->save();
  }
}
