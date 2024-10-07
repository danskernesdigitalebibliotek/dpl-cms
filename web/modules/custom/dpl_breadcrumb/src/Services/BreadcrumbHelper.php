<?php

namespace Drupal\dpl_breadcrumb\Services;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\node\NodeInterface;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\recurring_events\Entity\EventInstance;
use Drupal\taxonomy\TermInterface;
use Psr\Log\LoggerInterface;
use Safe\DateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Menu Helper service for DPL breadcrumb.
 */
class BreadcrumbHelper {

  /**
   * The entity type interface.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The language manager.
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * PathAuto alias cleaner.
   */
  protected AliasCleanerInterface $aliasCleaner;

  /**
   * Translation interface.
   */
  protected TranslationInterface $translation;

  /**
   * Custom logger service.
   */
  protected LoggerInterface $logger;

  /**
   * Should the current page also be shown in the breadcrumb?
   */
  protected bool $includeCurrentPage = TRUE;

  /**
   * The vocabulary ID, of the taxonomy tree used for content structure.
   */
  protected string $structureVid = 'breadcrumb_structure';

  /**
   * The field name of the node dropdown field, for choosing space in structure.
   */
  protected string $structureFieldName = 'field_breadcrumb_parent';

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    AliasCleanerInterface $alias_cleaner,
    TranslationInterface $translation,
    LoggerInterface $logger,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->aliasCleaner = $alias_cleaner;
    $this->translation = $translation;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('language.manager'),
      $container->get('pathauto.alias_cleaner'),
      $container->get('string_translation'),
      $container->get('dpl_breadcrumb.logger'),
    );
  }

  /**
   * The vocabulary ID, of the taxonomy tree used for content structure.
   */
  public function getStructureVid(): string {
    return $this->structureVid;
  }

  /**
   * The field name of the node dropdown field, for choosing space in structure.
   */
  public function getStructureFieldName(): string {
    return $this->structureFieldName;
  }

  /**
   * Get relevant categories field, as it may differ due to FieldInheritance.
   *
   * Events have a field_categories, but in reality, we want to use the
   * event_categories field instead, as that's where the inheritance is set up.
   */
  public function getCategoriesFieldId(FieldableEntityInterface $entity): ?string {
    if ($entity->hasField('event_categories')) {
      return 'event_categories';
    }

    if ($entity->hasField('field_categories')) {
      return 'field_categories';
    }

    return NULL;
  }

  /**
   * Get relevant branch field, as it may differ due to FieldInheritance.
   *
   * Events have a field_branch, but in reality, we want to use the
   * 'branch' field instead, as that's where the inheritance is set up.
   */
  private function getBranchFieldId(FieldableEntityInterface $entity): ?string {
    if ($entity->hasField('branch')) {
      return 'branch';
    }

    if ($entity->hasField('field_branch')) {
      return 'field_branch';
    }

    return NULL;
  }

  /**
   * Getting the breadcrumb, as a url string (/page/page2/page4).
   */
  public function getBreadcrumbUrlString(FieldableEntityInterface $entity): ?string {
    $url_string = '';

    $breadcrumb = $this->getBreadcrumb($entity);
    $links = $breadcrumb->getLinks();

    if (!empty($links)) {
      $links = array_reverse($links);

      foreach ($links as $link) {
        $text = $link->getText();
        $text = ($text instanceof MarkupInterface) ? $text->__toString() : $text;

        if (is_string($text)) {
          $url_string .= "/$text";
        }
      }
    }

    // If the breadcrumb does not include the current page, we want to
    // add it manually, for the URL.
    if (empty($url_string) || !$this->includeCurrentPage) {
      $url_string = "$url_string/{$entity->label()}";
    }

    $url_string = $this->aliasCleaner->cleanString(
      $url_string
    );

    return $url_string;
  }

  /**
   * Build the breadcrumb array.
   */
  public function getBreadcrumb(FieldableEntityInterface $entity): Breadcrumb {
    $breadcrumb = new Breadcrumb();
    $field_name = $this->getStructureFieldName();

    if ($entity->hasField($field_name)) {
      $this->getStructureBreadcrumb($entity, $breadcrumb);
    }

    $this->getCategoryBreadcrumb($entity, $breadcrumb);

    $this->getBaseBreadcrumb($entity, $breadcrumb);

    $links = $breadcrumb->getLinks();

    // If there is only one link in the breadcrumb, we want to check if it
    // is the current page. If that is the case, we'll reset the breadcrumb,
    // so we don't display a breadcrumb with a single, irrelevant link.
    if (count($links) === 1) {
      try {
        $breadcrumb_entity = $links[0]->getUrl()->getOption('entity');

        if ($breadcrumb_entity === $entity) {
          $breadcrumb = new Breadcrumb();
        }
      }
      catch (\Exception $e) {
        $this->logger->error("Failed checking breadcrumb solo logic. Message: %message", ["%message" => $e->getMessage()]);
      }
    }

    return $breadcrumb;
  }

  /**
   * Build the base breadcrumb, based on possible branch references.
   */
  private function getBaseBreadcrumb(FieldableEntityInterface $entity, Breadcrumb $breadcrumb): Breadcrumb {
    if ($entity->bundle() === 'article') {
      $breadcrumb->addLink(Link::createFromRoute(
        $this->translation->translate('Articles', [], ['context' => 'DPL Breadcrumbs']),
        'view.articles.all'
      ));
      $breadcrumb->addCacheTags(['locale']);
    }

    $entity_type_id = $entity->getEntityTypeId();

    if (in_array($entity_type_id, ['eventseries', 'eventinstance'])) {
      $breadcrumb->addLink(Link::createFromRoute(
        $this->translation->translate('Events', [], ['context' => 'DPL Breadcrumbs']),
        'view.events.all'
      ));
      $breadcrumb->addCacheTags(['locale']);
    }

    $branch_field_id = $this->getBranchFieldId($entity);

    if (!empty($branch_field_id)) {
      $branches = $entity->get($branch_field_id)->referencedEntities();
      $branch = reset($branches);

      if ($branch instanceof FieldableEntityInterface) {
        $breadcrumb->addLink($branch->toLink($branch->label()));
        $breadcrumb->addCacheableDependency($branch);
      }
    }

    return $breadcrumb;
  }

  /**
   * Event instances have a series parent, that we want in the breadcrumb.
   *
   * Ideally, if there are siblings, we want a breadcrumb that looks something
   * like: [base]=> [event series name] => [event instance date].
   */
  public function getEventInstanceBreadcrumbSuffix(EventInstance $instance, Breadcrumb $breadcrumb): Breadcrumb {
    $series = $instance->getEventSeries();
    $instance_count =
      $this->entityTypeManager->getStorage('eventinstance')->getQuery()->condition('eventseries_id', $series->id())->accessCheck()->count()->execute();

    // Technically an instance date may be a range over several days or months,
    // but this quickly becomes very complicated, both to display for the user,
    // but also to use for generating a clean URL alias.
    // We will instead just use the start date, with a year suffix.
    if ($instance_count > 1) {
      $date_string = $instance->get('date')->getValue()[0]['value'] ?? NULL;

      if (!empty($date_string)) {
        $date = new DateTime($date_string);
        $formatted_date = $date->format('Y-m-d');
        $breadcrumb->addLink($instance->toLink($formatted_date));
        $breadcrumb->addCacheableDependency($instance);
      }

      $breadcrumb->addLink($series->toLink($series->label()));
      $breadcrumb->addCacheableDependency($series);
    }
    // If there are no siblings, we'll jump past this, and just show the
    // instance link.
    else {
      $breadcrumb->addLink($instance->toLink($instance->label()));
      $breadcrumb->addCacheableDependency($instance);
    }

    return $breadcrumb;
  }

  /**
   * Build a breadcrumb array, based on field_categories.
   */
  private function getCategoryBreadcrumb(FieldableEntityInterface $entity, Breadcrumb $breadcrumb): Breadcrumb {
    $category_field_id = $this->getCategoriesFieldId($entity);

    if (empty($category_field_id)) {
      return $breadcrumb;
    }

    if ($entity instanceof EventInstance) {
      $breadcrumb = $this->getEventInstanceBreadcrumbSuffix($entity, $breadcrumb);
    }
    elseif ($this->includeCurrentPage) {
      $breadcrumb->addLink($entity->toLink($entity->label()));
      $breadcrumb->addCacheableDependency($entity);
    }

    $categories = $entity->get($category_field_id)->referencedEntities();

    $category = reset($categories);

    if ($category instanceof TermInterface) {
      $category_id = intval($category->id());
      // Get all parent categories, including current.
      $categories = $this->entityTypeManager->getStorage("taxonomy_term")
        ->loadAllParents($category_id);

      foreach ($categories as $category) {
        $breadcrumb->addLink($category->toLink($category->getName()));
        $breadcrumb->addCacheableDependency($category);
      }
    }

    return $breadcrumb;
  }

  /**
   * Find a breadcrumb item by entity, if it is added to the content structure.
   */
  public function getBreadcrumbItem(FieldableEntityInterface $entity): ?TermInterface {
    $id = $entity->id();

    // The field_content on the taxonomy term only supports nodes.
    // If we don't do a check for node here, we will experience a collision
    // as an eventinstance ID probably is identical to an unrelated node ID.
    if (!$id || $entity->getEntityTypeId() !== 'node') {
      return NULL;
    }

    $storage = $this->entityTypeManager->getStorage('taxonomy_term');

    $breadcrumb_items = $storage->loadByProperties([
      'field_content' => $id,
      'vid' => $this->getStructureVid(),
      'status' => TRUE,
    ]);

    $breadcrumb_item = reset($breadcrumb_items);

    if ($breadcrumb_item instanceof TermInterface) {
      return $breadcrumb_item;
    }

    return NULL;
  }

  /**
   * Find a breadcrumb item that is referenced by a entity's breadcrumb field.
   */
  public function getReferencedBreadcrumbItem(FieldableEntityInterface $entity): ?TermInterface {
    $field_key = $this->getStructureFieldName();

    if (!$entity->hasField($field_key) || $entity->get($field_key)->isEmpty()) {
      return NULL;
    }

    $breadcrumb_items = $entity->get($field_key)->referencedEntities();
    $first_breadcrumb = reset($breadcrumb_items);

    return $first_breadcrumb instanceof TermInterface ? $first_breadcrumb : NULL;
  }

  /**
   * Build a breadcrumb array, based on the custom content structure.
   */
  public function getStructureBreadcrumb(FieldableEntityInterface $entity, Breadcrumb $breadcrumb): Breadcrumb {
    $breadcrumb_item = $this->getBreadcrumbItem($entity);

    if (!($breadcrumb_item instanceof TermInterface)) {
      $breadcrumb_item = $this->getReferencedBreadcrumbItem($entity);

      if ($this->includeCurrentPage) {
        $breadcrumb->addLink($entity->toLink($entity->label()));
        $breadcrumb->addCacheableDependency($entity);
      }
    }

    if (!($breadcrumb_item instanceof TermInterface)) {
      return $breadcrumb;
    }

    $breadcrumb_items = $this->getStructureTree($breadcrumb_item);

    foreach ($breadcrumb_items as $item) {
      if (!$item->hasField('field_content')) {
        continue;
      }

      $contents = $item->get('field_content')->referencedEntities();

      /** @var \Drupal\Core\Entity\FieldableEntityInterface $content */
      $content = reset($contents);

      if (!($content instanceof FieldableEntityInterface)) {
        continue;
      }

      $breadcrumb->addLink($content->toLink($item->getName()));
      $breadcrumb->addCacheableDependency($content);
      $breadcrumb->addCacheableDependency($item);
    }

    return $breadcrumb;
  }

  /**
   * Get the full structure tree of this breadcrumb item (including the item).
   *
   * @return array<mixed>
   *   An array of breadcrumb items, to be rendered, with text and route info.
   */
  public function getStructureTree(TermInterface $breadcrumb_item): array {
    /** @var \Drupal\taxonomy\TermStorage $storage */
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $tid = $breadcrumb_item->id();

    if (empty($tid)) {
      return [];
    }

    $full_tree = $storage->loadAllParents(intval($tid));

    if (!empty($full_tree) && !$this->includeCurrentPage) {
      array_shift($full_tree);
    }

    return $full_tree;
  }

  /**
   * Get the possible parent of this breadcrumb item.
   */
  public function getStructureParent(TermInterface $breadcrumb_item): ?TermInterface {
    $parents = $this->getStructureTree($breadcrumb_item);

    // The first item is the actual current breadcrumb item.
    // We want the second item (if it exists).
    if (count($parents) < 2) {
      return NULL;
    }

    $slice = array_slice($parents, 1, 1, TRUE);
    $first_slide = reset($slice);

    return $first_slide instanceof TermInterface ? $first_slide : NULL;
  }

  /**
   * Get nodes that references this breadcrumb item, and render it.
   *
   * We currently only allow nodes to use the structure referencing, but
   * in theory, we may include this in the future to events.
   * If that is the case, the code obviously needs to be updated.
   *
   * @return array<mixed>
   *   An array of rendered node entities.
   */
  public function getRenderedReferencingNodes(TermInterface $breadcrumb_item, string $view_mode = 'nav_teaser'): array {
    $field_name = $this->getStructureFieldName();

    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery();
    $nids = $query
      ->condition($field_name, $breadcrumb_item->id())
      ->condition('status', NodeInterface::PUBLISHED)
      ->accessCheck(TRUE)
      ->sort('title', 'ASC')
      ->execute();

    $nodes = $node_storage->loadMultiple($nids);

    $view_builder = $this->entityTypeManager->getViewBuilder('node');

    $rendered_nodes = [];

    foreach ($nodes as $node) {
      $rendered_nodes[] = $view_builder->view($node, $view_mode);
    }

    return $rendered_nodes;
  }

}
