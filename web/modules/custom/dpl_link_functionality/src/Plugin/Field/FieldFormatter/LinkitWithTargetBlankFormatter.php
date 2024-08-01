<?php

declare(strict_types=1);

namespace Drupal\dpl_link_functionality\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Render\Element\Link;

/**
 * Plugin implementation of the 'Linkit with Target Blank' formatter.
 *
 * @FieldFormatter(
 *   id = "linkit_target_blank",
 *   label = @Translation("Linkit with Target Blank"),
 *   field_types = {"link"},
 * )
 */
final class LinkitWithTargetBlankFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $element = [];
    foreach ($items as $delta => $item) {
      if (!$item->isEmpty()) {
        $url = $item->getUrl()->toString();
        $attributes = $item->getValue()['attributes'] ?? [];
        $element[$delta] = [
          '#type' => 'link',
          '#title' => $item->title ?? $url,
          '#url' => $item->getUrl(),
          '#options' => [
            'attributes' => $attributes,
          ],
        ];
      }
    }
    return $element;
  }

}
