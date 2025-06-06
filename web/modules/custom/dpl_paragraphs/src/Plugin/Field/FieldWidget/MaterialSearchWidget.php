<?php

namespace Drupal\dpl_paragraphs\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function Safe\parse_url;
use function Safe\preg_match;

/**
 * Plugin implementation of the 'material_search' widget.
 *
 * @FieldWidget(
 *   id = "dpl_paragraphs_material_search",
 *   module = "dpl_paragraphs",
 *   label = @Translation("DPL search link to filters"),
 *   field_types = {
 *     "dpl_paragraphs_material_search"
 *   }
 * )
 */
final class MaterialSearchWidget extends WidgetBase {

  /**
   * The module handler, we use to get dpl_admin assets.
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    string $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    ModuleHandlerInterface $module_handler,
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $fieldStorageDefinition = $items->getFieldDefinition()->getFieldStorageDefinition();

    $element['link'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Fill out fields using link (optional)', [], ['context' => 'DPL material search']),
    ];

    $element['link']['url'] = [
      '#type' => 'textarea',
      '#title' => $fieldStorageDefinition->getPropertyDefinition('link')?->getLabel(),
      '#default_value' => $items[$delta]->link ?? '',
      '#ajax' => [
        'callback' => [$this, 'loadFilters'],
        'event' => 'input',
      ],
      '#prefix' => '<div id="field-my-input-wrapper">',
      '#suffix' => '</div>',
      '#attributes' => [
        'placeholder' => $this->t(
          'E.g. https://mitbibliotek.dk/advanced-search?advancedSearchCql=%27Harry+Potter%27',
          [], ['context' => 'DPL material search']
        ),
      ],
    ];

    // Loading the help video, that is part of dpl_admin's assets.
    // We could have put this video in the dpl_paragraphs module, but I think
    // in the future we will do this more, and it would be nice to have all the
    // help-assets in one central place.
    $dplAdminPath = $this->moduleHandler->getModule('dpl_admin')->getPath();
    $videoPath = "$dplAdminPath/assets/material_search.webm";

    $element['link']['guide'] = [
      '#type' => 'details',
      '#title' => $this->t('Video-guide on how to find link', [], ['context' => 'DPL material search']),
      '#open' => FALSE,
      'video' => [
        '#type' => 'markup',
        '#markup' => Markup::create("<video controls width=\"100%\" ><source src=\"/$videoPath\" type=\"video/webm\" /></video>"),
      ],
    ];

    $element['filters'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Search filters', [], ['context' => 'DPL material search']),
      '#attributes' => [
        'class' => ['material-search-filters'],
      ],
      'sort' => [
        '#type' => 'select',
        '#title' => $fieldStorageDefinition->getPropertyDefinition('sort')?->getLabel(),
        '#default_value' => $items[$delta]->sort ?? 'relevance',
        '#options' => [
          'relevance' => $this->t(
            'By relevance', [], ['context' => 'DPL material search']
          ),
          'sort.title.asc' => $this->t(
            'By title (ascending)', [], ['context' => 'DPL material search']
          ),
          'sort.title.desc' => $this->t(
            'By title (descending)', [], ['context' => 'DPL material search']
          ),
          'sort.creator.asc' => $this->t(
            'By creator (ascending)', [], ['context' => 'DPL material search']
          ),
          'sort.creator.desc' => $this->t(
            'By creator (descending)', [], ['context' => 'DPL material search']
          ),
          'sort.latestpublicationdate.asc' => $this->t(
            'By publication date (ascending)', [], ['context' => 'DPL material search']
          ),
          'sort.latestpublicationdate.desc' => $this->t(
            'By publication date (descending)', [], ['context' => 'DPL material search']
          ),
        ],
      ],
      'cql' => [
        '#type' => 'textarea',
        '#title' => $fieldStorageDefinition->getPropertyDefinition('cql')?->getLabel(),
        '#required' => TRUE,
        '#default_value' => $items[$delta]->cql ?? '',
      ],
      'location' => [
        '#type' => 'textarea',
        '#title' => $fieldStorageDefinition->getPropertyDefinition('location')?->getLabel(),
        '#default_value' => $items[$delta]->location ?? '',
      ],
      'sublocation' => [
        '#type' => 'textarea',
        '#title' => $fieldStorageDefinition->getPropertyDefinition('sublocation')?->getLabel(),
        '#default_value' => $items[$delta]->sublocation ?? '',
      ],
      'onshelf' => [
        '#type' => 'checkbox',
        '#title' => $fieldStorageDefinition->getPropertyDefinition('onshelf')?->getLabel(),
        '#default_value' => $items[$delta]->onshelf ?? '',
      ],
    ];

    return $element;
  }

  /**
   * AJAX callback, triggered when inputting URL in link field.
   *
   * When called, we'll find the supplied URL, and run some checks to see if
   * we can find filters such as CQL or location.
   *
   *  If we cannot, we will display a warning for the user.
   *
   * @param array<mixed> $form
   *   The form, as part of formElement().
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state object, as part of formElement().
   */
  public function loadFilters(array &$form, FormStateInterface $formState): AjaxResponse {
    // As this is happening inside a paragraph, we need to actually find the
    // right subform. This can be done by looking at the triggering element
    // (the button that triggers this) and using 'array_parents' to loop
    // through $form and finding this form element.
    $triggeringElement = (array) $formState->getTriggeringElement();

    $array_parents = (array) $triggeringElement['#array_parents'];
    // We want the parent of the parent of the link field, as the link field
    // is also inside its own fieldset. Hence -2.
    $array_parents = array_slice($array_parents, 0, -2);
    $formElement = NestedArray::getValue($form, $array_parents);

    // To load the value, we want to do something similar, but instead of
    // looking at #array_parents, we'll instead look at #parents.
    $parents = (array) $triggeringElement['#parents'];
    $parents = array_slice($parents, 0, -2);
    $values = $formState->getValue($parents);

    $link = $values['link']['url'] ?? '';

    // When pasting a link, spaces may be added unintentionally in the
    // beginning or end of the string. To avoid confusion, we'll just remove it.
    $link = trim($link);
    $linkName = $formElement['link']['url']['#name'];

    // The JS selector we use with AJAX.
    $linkSelector = "[name=\"$linkName\"]";

    $response = new AjaxResponse();

    $cqlName = $formElement['filters']['cql']['#name'];
    $cqlValue = $this->getFilter($link, 'advancedSearchCql');

    $warningClass = 'dpl-material-search-warning';

    // If the user inputted a link that we could not find any CQL data from,
    // we'll display a warning.
    if (!$cqlValue) {
      $warningMessage = $this->t(
        'The link you pasted is not valid. See video below, for how to find a correct link.',
        [], ['context' => 'DPL material search']
      );
      $warningMarkup = Markup::create(
        "<div class=\"dpl-form-warning $warningClass\">{$warningMessage->render()}</div>"
      );
      $response->addCommand(new InvokeCommand($linkSelector, 'addClass', ['error']));
      $response->addCommand(new AfterCommand($linkSelector, $warningMarkup));

      return $response;
    }

    // Remove warning that may have been set previously, due to invalid URL.
    $response->addCommand(new InvokeCommand($linkSelector, 'removeClass', ['error']));
    $response->addCommand(new RemoveCommand(".$warningClass"));

    $response->addCommand(new InvokeCommand("[name=\"$cqlName\"]", 'val', [$cqlValue]));

    // The onshelf is special, as it is a checkbox, and the value is sent along
    // as a string 'true'/'false'.
    $onshelfName = $formElement['filters']['onshelf']['#name'];
    $onshelfValue = (string) $this->getFilter($link, 'onshelf');
    $onshelfBoolValue = (strtolower($onshelfValue) === 'true');
    $response->addCommand(new InvokeCommand(
      "[name=\"$onshelfName\"]",
      'prop',
      ['checked', $onshelfBoolValue])
    );

    // Add remaining, simple filters, along with their default values.
    $filter_default_values = [
      'location' => '',
      'sublocation' => '',
      'sort' => 'relevance',
    ];

    foreach ($filter_default_values as $filterKey => $filterDefaultValue) {
      $name = $formElement['filters'][$filterKey]['#name'];
      $value = $this->getFilter($link, $filterKey);
      $value = !empty($value) ? $value : $filterDefaultValue;

      $response->addCommand(new InvokeCommand("[name=\"$name\"]", 'val', [$value]));
    }

    return $response;
  }

  /**
   * We need to use extractFormValues, as we use fieldsets.
   *
   * Drupal does not understand the widget out-of-the-box, as the fields such
   * as 'cql' is not in the root of the form element array.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   {@inheritDoc}.
   * @param array<mixed> $form
   *   {@inheritDoc}.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   {@inheritDoc}.
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state): void {
    // Getting the subform values.
    $userInput = $form_state->getUserInput();
    $parents = $form['#parents'] ?? [];
    $input = NestedArray::getValue($userInput, $parents);

    foreach ($items as $delta => $item) {
      $values = $input['field_material_search'][$delta] ?? [];
      $value = [
        'link' => $values['link']['url'] ?? '',
        'cql' => $values['filters']['cql'] ?? '',
        'location' => $values['filters']['location'] ?? '',
        'sublocation' => $values['filters']['sublocation'] ?? '',
        'onshelf' => !empty($values['filters']['onshelf']),
        'sort' => $values['filters']['sort'] ?? 'relevance',
      ];

      $items->set($delta, $value);
    }
  }

  /**
   * Finding a named key from a possible URL/URI input.
   *
   * This works, whether it's an absolute or relative URL, and regardless
   * which base site is used.
   */
  private function getFilter(string $url, string $key): ?string {
    // Add HTTP prefix if missing to ensure parse_url works correctly.
    if (!preg_match('#^https?://#', $url)) {
      $url = 'https://' . trim($url, '/');
    }

    $parts = parse_url($url);

    if (isset($parts['query'])) {
      parse_str($parts['query'], $query);
      $result = $query[$key] ?? NULL;

      return is_string($result) ? ltrim($result) : NULL;
    }

    return NULL;
  }

}
