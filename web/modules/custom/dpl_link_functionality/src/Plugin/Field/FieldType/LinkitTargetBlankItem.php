<?php

declare(strict_types=1);

namespace Drupal\dpl_link_functionality\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'linkit_target_blank' field type.
 *
 * @FieldType(
 *   id = "linkit_target_blank",
 *   label = @Translation("Linkit with Target Blank"),
 *   description = @Translation("A field containing a link with an optional target blank attribute."),
 *   default_widget = "linkit_target_blank",
 *   default_formatter = "linkit_target_blank",
 * )
 */
final class LinkitTargetBlankItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function isEmpty(): bool {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition): array {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('URL'))
      ->setRequired(TRUE);

    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Link title'))
      ->setRequired(FALSE);

    $properties['attributes'] = DataDefinition::create('map')
      ->setLabel(t('Link attributes'))
      ->setDescription(t('The attributes for the link, including target.'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints(): array {
    $constraints = parent::getConstraints();

    $constraint_manager = $this->getTypedDataManager()->getValidationConstraintManager();

    $options['value']['Length']['max'] = 2048;

    $constraints[] = $constraint_manager->create('ComplexData', $options);
    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition): array {

    $columns = [
      'value' => [
        'type' => 'varchar',
        'not null' => FALSE,
        'description' => 'URL of the link.',
        'length' => 2048,
      ],
      'title' => [
        'type' => 'varchar',
        'not null' => FALSE,
        'description' => 'Title of the link.',
        'length' => 255,
      ],
      'attributes' => [
        'type' => 'text',
        'not null' => FALSE,
        'description' => 'Serialized array of link attributes.',
      ],
    ];

    $schema = [
      'columns' => $columns,
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition): array {
    $random = new Random();
    $values['value'] = $random->word(mt_rand(1, 50));
    $values['title'] = $random->word(mt_rand(1, 10));
    $values['attributes'] = serialize(['target' => '_blank']);
    return $values;
  }

}