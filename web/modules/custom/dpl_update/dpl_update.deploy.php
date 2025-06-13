<?php

use Drupal\collation_fixer\CollationFixer;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\drupal_typed\DrupalTyped;
use Drupal\media\Entity\Media;
use Drupal\node\NodeInterface;
use Drupal\recurring_events\Entity\EventInstance;
use Drupal\recurring_events\Entity\EventSeries;

/**
 * Linking new field inheritances with existing eventinstances.
 *
 * There is a fault in field_inheritance, when you add new fields/inheritances,
 * it doesn't get updated on the old eventinstances until they get saved from
 * the form.
 * This is because the logic that links eventinstances and eventseries together
 * is set by field_inheritance directly in the form_alter and form_submit.
 * This helper function allows you to pass along a name of a field inherited
 * field that has been set up at /admin/structure/field_inheritance, and
 * the helper will find all eventinstances and make sure the new field is
 * linked together with the relevant eventseries.
 */
function _dpl_update_field_inheritance(string $field_inheritance_name): string {
  $ids =
    \Drupal::entityQuery('eventinstance')
      ->accessCheck(FALSE)
      ->execute();

  if (empty($ids) || is_int($ids)) {
    return 'No entities to update.';
  }

  $entities =
    \Drupal::entityTypeManager()->getStorage('eventinstance')->loadMultiple($ids);

  $count = 0;

  foreach ($entities as $entity) {
    try {
      if (!($entity instanceof EventInstance)) {
        throw new Exception('Entity is not an expected EventInstance.');
      }

      $event_series = $entity->getEventSeries();

      if (!($event_series instanceof EventSeries)) {
        throw new Exception('Entity parent is not an expected EventSeries.');
      }

      // This matches the key that is defined in field_inheritance.
      $state_key = $entity->getEntityTypeId() . ':' . $entity->uuid();
      $field_inheritance = \Drupal::keyValue('field_inheritance')->get($state_key);

      // In theory, an eventinstance could be set up to inherit from another
      // entity than the eventseries - but in practice, this is really unlikely,
      // and something we're willing to disregard.
      $field_inheritance[$field_inheritance_name] = [
        'entity' => $event_series->id(),
      ];

      \Drupal::keyValue('field_inheritance')->set($state_key, $field_inheritance);

      $entity->save();
      $count++;
    }
    catch (\Throwable $e) {
      \Drupal::logger('dpl_update')->error('Could not update field_inheritance on eventinstance @id - Error: @message', [
        '@message' => $e->getMessage(),
        '@id' => $entity->id(),
      ]);
    }
  }

  return "Updated $count eventinstances, linking field  '$field_inheritance_name' to inherit from eventseries.";
}

/**
 * Helper function, for setting field value on entities content on new fields.
 *
 * This is useful if you have created a new field, and want to set a
 * default value.
 */
function _dpl_update_set_value(string $field_name, mixed $value, string $entity_type = 'node'): string {
  $ids =
    \Drupal::entityQuery($entity_type)
      ->accessCheck(FALSE)
      ->execute();

  if (!is_array($ids) || empty($ids)) {
    return "No $entity_type entities to update.";
  }

  $entities =
    \Drupal::entityTypeManager()->getStorage($entity_type)->loadMultiple($ids);

  $count = 0;

  foreach ($entities as $entity) {
    try {
      if (!($entity instanceof FieldableEntityInterface)) {
        throw new Exception('Entity is not an expected FieldableEntity.');
      }

      $entity->set($field_name, $value);

      $entity->save();
      $count++;
    }
    catch (\Throwable $e) {
      \Drupal::logger('dpl_update')->error('Could not set default value on @field_name on @entity_type @id - Error: @message', [
        '@message' => $e->getMessage(),
        '@entity_type' => $entity_type,
        '@id' => $entity->id(),
        '@field_name' => $field_name,
      ]);
    }
  }

  return "Set default value for $field_name on $count $entity_type.";
}

/**
 * Re-generating missing URL aliases for entity types.
 *
 * Useful, if you've created or altered a new pattern.
 */
function _dpl_update_generate_url_aliases(string $entity_type): string {
  $ids =
    \Drupal::entityQuery($entity_type)
      ->accessCheck(FALSE)
      ->execute();

  if (!is_array($ids) || empty($ids)) {
    return "No $entity_type entities to update.";
  }

  foreach ($ids as $id) {
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($id);

    if (!($entity instanceof EntityInterface)) {
      continue;
    }

    \Drupal::service('pathauto.generator')->updateEntityAlias($entity, 'update');
  }

  $count = count($ids);

  return "Updated $count aliased entities of type $entity_type.";
}

/**
 * Fix collation for all tables to fix alphabetical sorting.
 */
function dpl_update_deploy_fix_collation(): string {
  if (!\Drupal::moduleHandler()->moduleExists('collation_fixer')) {
    return "No table collations fixed. collation_fixer module is not enabled.";
  }
  $collation_fixer = DrupalTyped::service(CollationFixer::class, CollationFixer::class);
  $collation_fixer->fixCollation();
  return "Fixed collation for all tables";
}

/**
 * Set branches without value to not promoted on lists.
 */
function dpl_update_deploy_set_branches_not_promoted(): string {
  $branches = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(
    ['type' => 'branch'],
  );
  $branches_with_empty_promotion_fields = array_filter(
    $branches,
    fn(NodeInterface $branch) => $branch->get('field_promoted_on_lists')->isEmpty()
  );
  array_map(
    fn(NodeInterface $branch) => $branch->set('field_promoted_on_lists', 0)->save(),
    $branches_with_empty_promotion_fields
  );

  $count_branches = count($branches_with_empty_promotion_fields);
  return "Set default value for promoted on lists for {$count_branches} branches";
}

/**
 * Migrate values from field_title to field_underlined_title.
 */
function dpl_update_deploy_migrate_content_slider_titles(): string {
  $paragraph_storage = Drupal::entityTypeManager()->getStorage('paragraph');

  $old_field = 'field_title';
  $new_field = 'field_underlined_title';

  $paragraph_ids = Drupal::entityQuery('paragraph')
    ->condition('type', ['content_slider', 'content_slider_automatic'], 'IN')
    ->condition("$old_field.value", "", "<>")
    ->accessCheck(FALSE)
    ->execute();

  if (empty($paragraph_ids)) {
    return "No content sliders found.";
  }

  $paragraph_ids = is_array($paragraph_ids) ? $paragraph_ids : [];
  $paragraphs = $paragraph_storage->loadMultiple($paragraph_ids);

  $updated_titles = [];
  foreach ($paragraphs as $paragraph) {
    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    if (!$paragraph->hasField($new_field)) {
      continue;
    }

    $old_value = $paragraph->get($old_field)->getString();

    if (!$paragraph->get($new_field)->isEmpty()) {
      continue;
    }

    $paragraph->set($new_field, [
      'value' => $old_value,
      'format' => 'underlined_title',
    ]);
    $paragraph->save();
    $updated_titles[] = $old_value;
  }

  if (empty($updated_titles)) {
    return 'No titles were migrated.';
  }

  $count = count($updated_titles);

  return "Migrated titles ($count): " . implode(', ', $updated_titles);
}

/**
 * Set default value for all existing eventseries:field_relevant_ticket_manager.
 */
function dpl_update_deploy_field_relevant_ticket_manager(): string {
  return _dpl_update_set_value('field_relevant_ticket_manager', TRUE, 'eventseries');
}

/**
 * Re-generating the URL aliases of taxonomy terms.
 *
 * Relevant after we've created "search" overview on the term pages of tags
 * and categories.
 */
function dpl_update_deploy_update_term_url_aliases(): string {
  return _dpl_update_generate_url_aliases('taxonomy_term');
}

/**
 * Link new event_screen_names inheritance on eventinstances.
 */
function dpl_update_update_screen_name_field_inheritance(): string {
  return _dpl_update_field_inheritance('event_screen_names');
}

/**
 * Enable appropriate BNF module.
 */
function dpl_update_deploy_bnf(): string {
  // We're enabling the BNF modules here instead of `core.extension`/update hook
  // because neither module should be enabled across all sites.
  $moduleHandler = DrupalTyped::service(ModuleHandlerInterface::class, ModuleHandlerInterface::class);

  // Needed for the _dpl_update_install_modules() function.
  $moduleHandler->loadInclude('dpl_update', 'install');

  // We determine whether we should set up the client or server depending on the
  // Lagoon environment. The variable is also configured in the docker compose
  // setup.
  $project = getenv('LAGOON_PROJECT');

  if (in_array($project, ['bnf', 'dpl-bnf'])) {
    return _dpl_update_install_modules(['bnf_server']);
  }

  $result = _dpl_update_install_modules(['bnf_client']);

  // Set default server. Configuration of modules enabled in deploy hooks must
  // be handled manually, as configuration import is run before deploy hooks, so
  // having conventionally exported configuration for the module would cause the
  // configuration import to fail as the module haven't been enabled yet. So we
  // have ignored the client config, and set it here. This is overridden in the
  // local setup and PR envs by the settings files.
  DrupalTyped::service(ConfigFactoryInterface::class, ConfigFactoryInterface::class)
    ->getEditable('bnf_client.settings')
    ->set('base_url', 'https://delingstjenesten.dk/')
    ->save();

  return $result;
}

/**
 * Reverting unwanted cache tag update from contrib module update.
 */
function dpl_update_deploy_fix_content_view(): string {
  $config_factory = \Drupal::configFactory();
  $config = $config_factory->getEditable('views.view.content');
  $config->set('display.default.display_options.cache.type', 'tag');
  $config->save(TRUE);

  return 'views.view.contentdisplay.default.display_options.cache.type => tag';
}

/**
 * Find duplicate medias, and hide them in the media library.
 *
 * As part of a bug, all medias pulled from Delingstjenesten/BNF were duplicated
 * resulting in a bloated media library.
 *
 * @param array<mixed> $sandbox
 *   The sandbox, used for batch processing.
 */
function dpl_update_deploy_duplicate_media_cleanup(array &$sandbox): ?string {
  // Amount of medias we want to handle per sandbox batch.
  $batch_size = 50;

  // First run: prepare everything, finding the initial IDs of duplicate medias.
  if (!isset($sandbox['ids'])) {
    $connection = \Drupal::database();

    // Find all file IDs, that are referenced in more than one media.
    $duplicate_fids = $connection->select('media__field_media_image', 'mfi')
      ->fields('mfi', ['field_media_image_target_id'])
      ->groupBy('field_media_image_target_id')
      ->having('COUNT(entity_id) > 1')
      ->execute();

    if (!($duplicate_fids instanceof StatementInterface)) {
      $message = 'dpl_update_deploy_duplicate_media_cleanup: Could not retrieve media image file ids for duplicate media cleanup.';

      \Drupal::logger('dpl_update')->error($message);

      return $message;
    }

    $duplicate_fids = $duplicate_fids->fetchCol();

    if (empty($duplicate_fids)) {
      return 'No duplicate media files found.';
    }

    // Find all medias that actually reference the duplicated file IDs.
    $media_ids = $connection->select('media__field_media_image', 'mfi')
      ->fields('mfi', ['entity_id'])
      ->condition('mfi.field_media_image_target_id', $duplicate_fids, 'IN')
      ->execute();

    if (!($media_ids instanceof StatementInterface)) {
      $message = 'dpl_update_deploy_duplicate_media_cleanup: Could not retrieve media image ids for duplicate media cleanup.';

      \Drupal::logger('dpl_update')->error($message);

      return $message;
    }

    $media_ids = $media_ids->fetchCol();

    if (empty($media_ids)) {
      return 'No duplicate medias found.';
    }

    // Looking up any entities that may reference the duplicated medias.
    $referenced_media_ids = [];
    $entity_type_manager = \Drupal::entityTypeManager();
    $field_manager = \Drupal::service('entity_field.manager');
    $bundle_info = \Drupal::service('entity_type.bundle.info');

    // Go through all the different entity types (paragraphs, nodes, etc.)
    foreach ($entity_type_manager->getDefinitions() as $entity_type_id => $definition) {
      $entity_class = $definition->getClass();

      // Skip entities that cannot reference the medias.
      if (!is_subclass_of($entity_class, FieldableEntityInterface::class)) {
        continue;
      }

      // Looping through each bundle of the entity types (articles, pages, etc.)
      foreach (array_keys($bundle_info->getBundleInfo($entity_type_id)) as $bundle) {
        $fields = $field_manager->getFieldDefinitions($entity_type_id, $bundle);

        // Going through all fields that exists on this bundle, and see if it is
        // a media-reference.
        foreach ($fields as $field) {
          if ($field->getType() !== 'entity_reference' || $field->getSetting('target_type') !== 'media') {
            continue;
          }

          $field_name = $field->getName();
          $table = "{$entity_type_id}__{$field_name}";

          if (!$connection->schema()->tableExists($table)) {
            continue;
          }

          // Looking up if there are any entities that reference the duplicate
          // media IDs.
          $batch_media_ids = $connection->select($table, 't')
            ->fields('t', ["{$field_name}_target_id"])
            ->condition("{$field_name}_target_id", $media_ids, 'IN')
            ->execute();

          if (!($batch_media_ids instanceof StatementInterface)) {
            $message = 'dpl_update_deploy_duplicate_media_cleanup: Could not retrieve referenced medias for duplicate media cleanup.';

            \Drupal::logger('dpl_update')->error($message);

            return $message;
          }

          $batch_media_ids = $batch_media_ids->fetchCol();

          $referenced_media_ids = array_merge($referenced_media_ids, $batch_media_ids);
        }
      }
    }

    // Finding any medias that have duplicates, but also aren't referenced
    // in any entities AKA orphaned.
    $referenced_media_ids = array_unique($referenced_media_ids);
    $orphaned_media_ids = array_values(array_diff($media_ids, $referenced_media_ids));

    // Initialize sandbox values, that we will use as part of batch.
    $sandbox['ids'] = $orphaned_media_ids;
    $sandbox['total'] = count($orphaned_media_ids);
    $sandbox['current'] = 0;

    if (empty($sandbox['total'])) {
      $sandbox['#finished'] = 1;
      return 'No orphaned media duplicates found.';
    }
  }

  // Slice the current batch from stored IDs, breaking it into batches.
  $batch_ids = array_slice($sandbox['ids'], $sandbox['current'], $batch_size);
  $medias = Media::loadMultiple($batch_ids);

  \Drupal::logger('dpl_update')->notice('dpl_update_deploy_duplicate_media_cleanup: Batch progress: @current / @total.', [
    '@current' => $sandbox['current'],
    '@total' => $sandbox['total'],
  ]);

  // Looping through the medias, and hiding it from medialibrary by
  // unpublishing, and setting a revision message.
  // In a future deploy, we may choose to delete these medias, and then they
  // can be looked up via the revision message.
  foreach ($medias as $media) {
    $media->setUnpublished();
    $media->setOwnerId(1);
    $media->setNewRevision();
    $media->setRevisionLogMessage('Unpublished for duplicate cleanup (dpl_update_deploy_duplicate_media_cleanup)');
    $media->save();
    $sandbox['current']++;
  }

  // Progress feedback, used for looping through batches.
  $sandbox['#finished'] = $sandbox['current'] >= $sandbox['total']
    ? 1
    : ($sandbox['current'] / $sandbox['total']);

  if ($sandbox['#finished'] === 1) {
    \Drupal::logger('dpl_update')->notice(
      'dpl_update_deploy_duplicate_media_cleanup: Finished cleaning up duplicated medias.'
    );

    return "Unpublished and hid {$sandbox['total']} duplicate medias.";
  }

  return NULL;
}
