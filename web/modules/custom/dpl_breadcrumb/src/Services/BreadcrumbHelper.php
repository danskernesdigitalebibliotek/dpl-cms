<?php

namespace Drupal\dpl_breadcrumb\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\node\Entity\Node;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Menu Helper service for DPL breadcrumb.
 */
class BreadcrumbHelper {

  /**
   * The entity type interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * PathAuto alias cleaner.
   *
   * @var \Drupal\pathauto\AliasCleanerInterface
   */
  protected $aliasCleaner;

  /**
   * Translation interface.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translation;

  /**
   * Should the current page also be shown in the breadcrumb?
   *
   * @var bool
   */
  protected $includeCurrentPage = TRUE;

  /**
   * The vocabulary ID, of the taxonomy tree used for content structure.
   *
   * @var string
   */
  protected $structureVid = 'content_structure';

  /**
   * The field name of the node dropdown field, for choosing space in structure.
   *
   * @var string
   */
  protected $structureFieldName = 'field_breadcrumb_parent';

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    AliasCleanerInterface $alias_cleaner,
    TranslationInterface $translation
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->aliasCleaner = $alias_cleaner;
    $this->translation = $translation;
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
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getStructureVid(): string {
    return $this->structureVid;
  }

  /**
   * {@inheritdoc}
   */
  public function getStructureFieldName(): string {
    return $this->structureFieldName;
  }

  /**
   * Getting the breadcrumb, as a url string (/page/page2/page4).
   */
  public function getBreadcrumbUrlString(Node $node): string|null {
    $breadcrumb = $this->getBreadcrumb($node);

    if (empty($breadcrumb)) {
      return NULL;
    }

    $breadcrumb = array_reverse($breadcrumb);
    $breadcrumb_string = '';

    // To avoid crazy long URLs, we'll limit the amount of items.
    $max_items = 5;
    $i = 1;

    foreach ($breadcrumb as $breadcrumb_item) {
      if ($i > $max_items) {
        break;
      }

      $text = $breadcrumb_item['text'] ?? NULL;

      if (!empty($text)) {
        $langcode = $this->languageManager->getCurrentLanguage()->getId();

        $text = $this->aliasCleaner->cleanString(
          $text,
          ['langcode' => $langcode]
        );

        $breadcrumb_string .= "/$text";
      }

      $i++;
    }

    // If the breadcrumb does not include the current page, we want to
    // add it manually, for the URL.
    if (!$this->includeCurrentPage) {
      $breadcrumb_string .= "/{$node->label()}";
    }

    return $breadcrumb_string;
  }

  /**
   * Build the breadcrumb array.
   *
   * @return array<mixed>
   *   An array of breadcrumb items, to be rendered, with text and route info.
   */
  public function getBreadcrumb(Node $node): array {
    $breadcrumb = $this->getBaseBreadcrumb($node);
    $field_name = $this->getStructureFieldName();

    if ($node->hasField($field_name)) {
      return array_merge($this->getStructureBreadcrumb($node), $breadcrumb);
    }

    if ($node->hasField('field_categories')) {
      return array_merge($this->getCategoryBreadcrumb($node), $breadcrumb);
    }

    return $breadcrumb;
  }

  /**
   * Build the base breadcrumb, based on possible branch references.
   *
   * @return array<mixed>
   *   An array of breadcrumb items, to be rendered, with text and route info.
   */
  private function getBaseBreadcrumb(Node $node): array {
    $breadcrumb = [];

    $branch = NULL;

    if ($node->hasField('field_branch')) {
      $branches = $node->get('field_branch')->referencedEntities();
      $branch = reset($branches);
    }

    if ($branch instanceof Node) {
      $url_object = $branch->toUrl();

      $breadcrumb[] = [
        'text' => $branch->label(),
        'route_name' => $url_object->getRouteName(),
        'route_parameters' => $url_object->getRouteParameters(),
      ];
    }

    if ($node->bundle() === 'article') {
      $breadcrumb[] = [
        'text' => $this->translation->translate('Articles'),
        'route_name' => 'view.articles.page_1',
        'route_parameters' => [],
      ];
    }

    return $breadcrumb;
  }

  /**
   * Build a breadcrumb array, based on field_categories.
   *
   * @return array<mixed>
   *   An array of breadcrumb items, to be rendered, with text and route info.
   */
  public function getCategoryBreadcrumb(Node $node): array {
    $breadcrumb = $this->includeCurrentPage ? [
      [
        'text' => $node->label(),
        'route_name' => 'entity.node.canonical',
        'route_parameters' => ['node' => $node->id()],
      ],
    ] : [];

    $categories = $node->get('field_categories')->referencedEntities();

    $category = reset($categories);

    if ($category instanceof TermInterface) {
      $category_id = intval($category->id());
      // Get all parent categories, including current.
      $categories = $this->entityTypeManager->getStorage("taxonomy_term")
        ->loadAllParents($category_id);

      foreach ($categories as $category) {
        $breadcrumb[] = [
          'text' => $category->getName(),
        ];
      }
    }

    return $breadcrumb;
  }

  /**
   * Find a breadcrumb item by node, if it is added to the content structure.
   */
  public function getBreadcrumbItem(Node $node): TermInterface|null {
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');

    $nid = $node->id();

    if (!$nid) {
      return NULL;
    }

    $breadcrumb_items = $storage->loadByProperties([
      'field_content' => $node->id(),
      'vid' => $this->getStructureVid(),
    ]);

    $breadcrumb_item = reset($breadcrumb_items);

    if ($breadcrumb_item instanceof TermInterface) {
      return $breadcrumb_item;
    }

    return NULL;
  }

  /**
   * Find a breadcrumb item that is referenced by a node's breadcrumb field.
   */
  public function getReferencedBreadcrumbItem(Node $node): TermInterface|NULL {
    $field_key = $this->getStructureFieldName();

    if (!$node->hasField($field_key) || $node->get($field_key)->isEmpty()) {
      return NULL;
    }

    $breadcrumb_items = $node->get($field_key)->referencedEntities();

    return reset($breadcrumb_items);
  }

  /**
   * Build a breadcrumb array, based on the custom content structure.
   *
   * @return array<mixed>
   *   An array of breadcrumb items, to be rendered, with text and route info.
   */
  public function getStructureBreadcrumb(Node $node): array {
    $breadcrumb_item = $this->getBreadcrumbItem($node);
    $breadcrumb = [];

    if (!($breadcrumb_item instanceof TermInterface)) {
      $breadcrumb_item = $this->getReferencedBreadcrumbItem($node);

      $breadcrumb = $this->includeCurrentPage ? [
        [
          'text' => $node->label(),
          'route_name' => 'entity.node.canonical',
          'route_parameters' => ['node' => $node->id()],
        ],
      ] : [];
    }

    if (!($breadcrumb_item instanceof TermInterface)) {
      return [];
    }

    $breadcrumb_items = $this->getStructureTree($breadcrumb_item);

    foreach ($breadcrumb_items as $item) {
      $breadcrumb[] = [
        'text' => $item->getName(),
        'route_name' => 'entity.node.canonical',
        'route_parameters' => ['node' => $item->get('field_content')->getString()],
      ];
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
  public function getStructureParent(TermInterface $breadcrumb_item): TermInterface|NULL {
    $parents = $this->getStructureTree($breadcrumb_item);

    // The first item is the actual current breadcrumb item.
    // We want the second item (if it exists).
    if (count($parents) < 2) {
      return NULL;
    }

    $slice = array_slice($parents, 1, 1, TRUE);

    return reset($slice);
  }

  /**
   * Get content that references this breadcrumb item, and render it.
   *
   * @return array<mixed>
   *   An array of breadcrumb items, to be rendered, with text and route info.
   */
  public function getRenderedReferencingContent(TermInterface $breadcrumb_item, string $view_mode = 'nav_teaser'): array {
    $field_name = $this->getStructureFieldName();

    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery();
    $nids = $query
      ->condition($field_name, $breadcrumb_item->id())
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
