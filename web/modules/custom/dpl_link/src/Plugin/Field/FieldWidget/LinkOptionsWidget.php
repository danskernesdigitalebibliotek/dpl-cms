<?php

namespace Drupal\dpl_link\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldType\LinkItem;
use Drupal\linkit\Plugin\Field\FieldWidget\LinkitWidget;

/**
 * xxx
 *
 * @FieldWidget(
 *   id = "dpl_link_options",
 *   label = @Translation("DPL Link with options"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkOptionsWidget extends LinkitWidget {

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
    $item = $items[$delta] ?? NULL;
    $target = 'self';

    if ($item instanceof LinkItem) {
      $target = $item->getUrl()->getOptions()['target'] ?? $target;
    }

    $element['target_blank'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open link in new window/tab'),
      '#default_value' => ($target === '_blank'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = parent::massageFormValues($values, $form, $form_state);

    if (!empty($values[0]['target_blank'])) {
      $values[0]['options']['attributes']['target'] = '_blank';
    }

    return $values;
  }

}
