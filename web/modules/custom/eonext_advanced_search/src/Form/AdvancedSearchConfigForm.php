<?php

namespace Drupal\eonext_advanced_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Advanced search settings form.
 */
class AdvancedSearchConfigForm extends ConfigFormBase {
  public const FORM_ID = 'advanced_search_config_form';

  public const CONFIG_ID = 'eonext.advanced_search_config_settings';

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return self::FORM_ID;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames(): array {
    return [self::CONFIG_ID];
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $form['advanced_search_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Advanced Search'),
      '#config_target' => self::CONFIG_ID . ':advanced_search_enabled',
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addWarning(
      $this->t('Clear caches for changes to take effect.')
    );

    parent::submitForm($form, $form_state);
  }

}
