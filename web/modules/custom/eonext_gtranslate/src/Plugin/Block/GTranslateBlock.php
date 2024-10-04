<?php

namespace Drupal\eonext_gtranslate\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a "google page translate" dropdown widget.
 *
 * @Block(
 *   id = "eonext_gtranslate_widget_block",
 *   admin_label = "GTranslate widget"
 * )
 */
class GTranslateBlock extends BlockBase
{

  /**
   * {@inheritDoc}
   */
  public function build(): array {
    return [
      '#theme' => 'eonext_gtranslate',
      '#link_label' => $this->t('Language'),
      '#attached' => [
        'library' => [
          'eonext_gtranslate/gtranslate',
        ],
      ],
    ];
  }
}
