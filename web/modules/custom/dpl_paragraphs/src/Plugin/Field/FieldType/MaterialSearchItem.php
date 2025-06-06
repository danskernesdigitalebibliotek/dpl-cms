<?php

declare(strict_types=1);

namespace Drupal\dpl_paragraphs\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Xx.
 *
 * @FieldType(
 *   id = "dpl_paragraphs_material_search",
 *   module = "dpl_paragraphs",
 *   label = @Translation("DPL Material Search"),
 *   category = @Translation("DPL"),
 *   default_widget = "dpl_paragraphs_material_search",
 *   default_formatter = "string",
 * )
 */
final class MaterialSearchItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   *
   * @return mixed[]
   *   An associative array with the schema definition.
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition): array {

    return [
      'columns' => [
        'link' => [
          'type' => 'text',
          'length' => 1024,
          'not null' => FALSE,
          'description' => 'The link to a search page.',
        ],
        'cql' => [
          'type' => 'text',
          // Taken from dpl_fbi.install.
          'length' => 16000,
          'not null' => TRUE,
          'description' => 'A CQL query.',
        ],
        'location' => [
          'type' => 'text',
          'length' => 1024,
          'not null' => FALSE,
          'description' => '"Location" search filter',
        ],
        'sublocation' => [
          'type' => 'text',
          'length' => 1024,
          'not null' => FALSE,
          'description' => '"Sub-location" search filter',
        ],
        'sort' => [
          'type' => 'varchar',
          'length' => 128,
          'not null' => FALSE,
          'description' => 'The chosen search sort',
        ],
        'onshelf' => [
          'type' => 'int',
          'length' => 1,
          'not null' => FALSE,
          'description' => '"On shelf" search filter',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty(): bool {
    $value = $this->get('cql')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition): array {

    $properties['link'] = DataDefinition::create('string')
      ->setLabel(t('Link to search', [], ['context' => 'DPL material search']))
      ->setRequired(FALSE);

    $properties['cql'] = DataDefinition::create('string')
      ->setLabel(t('CQL query', [], ['context' => 'DPL material search']))
      ->setRequired(TRUE);

    $properties['location'] = DataDefinition::create('string')
      ->setLabel(t('Location', [], ['context' => 'DPL material search']))
      ->setRequired(FALSE);

    $properties['sublocation'] = DataDefinition::create('string')
      ->setLabel(t('Sub-location', [], ['context' => 'DPL material search']))
      ->setRequired(FALSE);

    $properties['onshelf'] = DataDefinition::create('boolean')
      ->setLabel(t('On-shelf', [], ['context' => 'DPL material search']))
      ->setRequired(FALSE);

    $properties['sort'] = DataDefinition::create('string')
      ->setLabel(t('Sorting', [], ['context' => 'DPL material search']))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   *
   * @return mixed[]
   *   An associative array of values.
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition): array {
    $values['link'] = "/advanced-search?onshelf=true&sublocation=fantasy&advancedSearchCql=+term.title%3D'Harry+Potter'+AND+term.creator%3D+'J.K.+Rowling'+AND+(+term.generalmaterialtype%3D'bøger'+OR+term.generalmaterialtype%3D'e-bøger')+AND+term.fictionnonfiction%3D'fiction'";
    $values['cql'] = " term.title='Harry Potter' AND term.creator= 'J.K. Rowling' AND ( term.generalmaterialtype='bøger' OR term.generalmaterialtype='e-bøger') AND term.fictionnonfiction='fiction'";
    $values['location'] = '';
    $values['sublocation'] = 'fantasy';
    $values['onshelf'] = 1;
    $values['sort'] = 'sort.latestpublicationdate.asc';
    return $values;
  }

}
