<?php

namespace Drupal\media_videotool\Form;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\media\MediaTypeInterface;
use Drupal\media_library\Form\AddFormBase;
use Drupal\media_videotool\Traits\HasVideoToolFeaturesTrait;

/**
 * Creates a form to create VideoTool media entities from within Media Library.
 */
class VideoToolMediaLibraryAddForm extends AddFormBase {

  use HasVideoToolFeaturesTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'media_videotool_media_library_add';
  }

  /**
   * {@inheritDoc}
   */
  protected function buildInputElement(array $form, FormStateInterface $form_state): array {
    $media_type = $this->getMediaType($form_state);

    $form['container'] = [
      '#type' => 'container',
      '#title' => $this->t('Add @type', [
        '@type' => $media_type->label(),
      ]),
    ];

    $form['container']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Add @type video via URL', [
        '@type' => $media_type->label(),
      ]),
      '#description' => $this->t('VideoTool URL example: https://media.videotool.dk/?vn=557_2023103014511477700668916683'),
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'https://',
      ],
    ];

    $form['container']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add'),
      '#button_type' => 'primary',
      '#submit' => ['::addButtonSubmit'],
      '#ajax' => [
        'callback' => '::updateFormCallback',
        'wrapper' => 'media-library-wrapper',
        'url' => Url::fromRoute('media_library.ui'),
        'options' => [
          'query' => $this->getMediaLibraryState($form_state)->all() + [
            FormBuilderInterface::AJAX_FORM_REQUEST => TRUE,
          ],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($form_state->getValue('url') !== NULL) {
      if (!self::isValidVideoToolUrl($form_state->getValue('url'))) {
        $form_state->setError($form, $this->t("The given URL does not match the VideoTool URL pattern."));
      }
    }
  }

  /**
   * Submit handler for the add button.
   *
   * @param array $form
   *   The form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function addButtonSubmit(array $form, FormStateInterface $form_state): void {
    $this->processInputValues([$form_state->getValue('url')], $form, $form_state);
  }

  /**
   * Returns the definition of the source field for a media type.
   *
   * @param \Drupal\media\MediaTypeInterface $media_type
   *   The media type to get the source definition for.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface|null
   *   The field definition.
   */
  protected function getSourceFieldDefinition(MediaTypeInterface $media_type): ?FieldDefinitionInterface {
    return $media_type->getSource()->getSourceFieldDefinition($media_type);
  }

}
