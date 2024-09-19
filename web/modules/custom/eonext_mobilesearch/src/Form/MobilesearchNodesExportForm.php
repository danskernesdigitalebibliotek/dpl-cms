<?php

namespace Drupal\eonext_mobilesearch\Form;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Mobilesearch node export settings form.
 */
class MobilesearchNodesExportForm extends ConfigFormBase {

  /**
   * Entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected EntityTypeBundleInfoInterface $entityTypeBundleManager;

  public const FORM_ID = 'eonext_mobilesearch.settings_nodes';

  public const CONFIG_ID = 'eonext_mobilesearch.exportable_bundles';

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container): static {
    $instance = parent::create($container);

    $instance->entityTypeBundleManager = $container->get('entity_type.bundle.info');

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return [self::CONFIG_ID];
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

    $node_types = $this
      ->entityTypeBundleManager
      ->getBundleInfo('node');

    $node_types += $this
      ->entityTypeBundleManager
      ->getBundleInfo('eventinstance');

    $form_state->set('node_types', $node_types);

    foreach ($node_types as $node_type => $info) {
      $form[$node_type] = [
        '#type' => 'checkbox',
        '#title' => $info['label'],
        '#default_value' => $config->get($node_type),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_ID);

    $node_types = $form_state->get('node_types') ?? [];
    foreach ($node_types as $node_type => $info) {
      $config->set($node_type, $form_state->getValue($node_type));
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
