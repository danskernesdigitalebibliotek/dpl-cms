<?php

namespace Drupal\eonext_mobilesearch\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Mobilesearch settings form class.
 */
class MobilesearchSettingsForm extends ConfigFormBase {

  public const FORM_ID = 'eonext_mobilesearch.settings_form';

  public const CONFIG_ID = 'eonext_mobilesearch.service_settings';

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::CONFIG_ID,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return self::FORM_ID;
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_ID);
    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Endpoint'),
      '#description' => $this->t('Service endpoint URL.'),
      '#default_value' => $config->get('url'),
      '#required' => TRUE,
    ];

    $form['agency'] = [
      '#type' => 'number',
      '#title' => $this->t('Agency'),
      '#placeholder' => '123456',
      '#description' => $this->t('Six digit agency ID.'),
      '#default_value' => $config->get('agency'),
      '#required' => TRUE,
    ];

    $form['key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key'),
      '#description' => $this->t('Access key.'),
      '#default_value' => $config->get('key'),
      '#required' => TRUE,
    ];

    $form['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Debug'),
      '#description' => $this->t('Log service communication.'),
      '#default_value' => $config->get('debug'),
    ];

    $form['timeout'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 10,
      '#title' => $this->t('Timeout'),
      '#description' => $this->t('Request timeout in seconds.'),
      '#default_value' => $config->get('timeout') ?? 2,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $agency = $form_state->getValue('agency');

    if (!preg_match('~\d{6}~', $agency)) {
      $form_state->setErrorByName('agency', $this->t('Wrong agency format.'));
    }
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_ID);

    $form_state->setValue(
      'url',
      rtrim(trim($form_state->getValue('url')), '/')
    );

    $keys = ['url', 'agency', 'key', 'debug', 'timeout'];
    foreach ($keys as $key) {
      $config->set($key, $form_state->getValue($key));
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
