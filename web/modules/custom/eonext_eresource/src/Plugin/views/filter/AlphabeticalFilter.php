<?php

namespace Drupal\eonext_eresource\Plugin\views\filter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;
use Drupal\views\Attribute\ViewsFilter;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom views filter plugin to expose content title index.
 */
#[ViewsFilter("eresource_az_index")]
class AlphabeticalFilter extends FilterPluginBase {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);

    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->routeMatch = $container->get('current_route_match');

    return $instance;
  }

  /**
   * {@inheritDoc}
   *
   * @param array<mixed> $form
   *   Form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state): void {
    parent::buildExposedForm($form, $form_state);

    $is_multiple = $this->options['expose']['multiple'];

    $options = [];
    if (!is_array($is_multiple)) {
      $options += [
        '_none' => $this->t('- All -'),
      ];
    }

    $options += $this->getContentIndex();

    $form[$this->options['expose']['identifier']] = [
      '#type' => $is_multiple ? 'checkboxes' : 'radios',
      '#title' => $this->t('Index'),
      '#options' => $options,
      '#default_value' => $is_multiple ? ['_none'] : '_none',
      '#attributes' => [
        'class' => [
          'e-resource_index-filter',
        ],
      ],
    ];
  }

  /**
   * Builds filter options.
   *
   * Creates a list of first characters from existing content titles.
   * Used further as a filter of content whose title starts with selected
   * filter character.
   *
   * @return array<mixed>
   *   A set of available filter values.
   */
  public function getContentIndex(): array {
    $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'status' => Node::PUBLISHED,
      'type' => 'e_resource',
    ]);

    $index = [];
    foreach ($nodes as $node) {
      $letter = trim((string) $node->label())[0];
      $index[mb_strtolower($letter)] = mb_strtoupper($letter);
    }

    $index = array_unique($index);
    \Safe\natsort($index);

    return $index;
  }

  /**
   * {@inheritDoc}
   */
  public function query(): void {
    $table = $this->ensureMyTable();

    $selected_indexes = array_filter($this->value, static function ($value) {
      return !empty($value) && $value !== '_none';
    });

    if (!empty($selected_indexes)) {
      /** @var \Drupal\Core\Database\Query\Condition $or */
      /* @phpstan-ignore-next-line */
      $or = $this->query->getConnection()->condition('OR');

      foreach ($selected_indexes as $item) {
        $or->condition("{$table}.title", "{$item}%", 'LIKE');
      }

      /* @phpstan-ignore-next-line */
      $this->query->addWhere(0, $or);
    }

    // $argumentId = key($this->view->argument);
    // $argument = $this->view->argument[$argumentId];
    // $argument_table = $argument->definition['table'];
    // $argument_field = $argument->definition['field'];
    // $argument_value = $this->view->args[0];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    return Cache::mergeTags(['node_list:e_resource'], parent::getCacheTags());
  }

}
