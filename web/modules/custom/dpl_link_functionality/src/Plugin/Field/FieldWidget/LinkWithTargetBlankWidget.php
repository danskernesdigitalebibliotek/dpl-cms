<?php

namespace Drupal\dpl_link_functionality\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;

/**
 * Plugin implementation of the 'link' widget with target blank option.
 *
 * @FieldWidget(
 *   id = "link_target_blank",
 *   label = @Translation("Link with Target Blank"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkWithTargetBlankWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'target_blank' => 0,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['target_blank'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open link in new window/tab'),
      '#default_value' => isset($items[$delta]->target_blank) ? $items[$delta]->target_blank : 0,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = parent::massageFormValues($values, $form, $form_state);
    foreach ($values as &$value) {
      if (!empty($value['target_blank'])) {
        $value['attributes']['target'] = '_blank';
      } else {
        unset($value['attributes']['target']);
      }
    }
    return $values;
  }

}
