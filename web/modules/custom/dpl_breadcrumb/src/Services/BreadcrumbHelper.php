<?php

namespace Drupal\dpl_breadcrumb\Services;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\taxonomy\Entity\Term;
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

    return $breadcrumb_string;
  }

  /**
   * Build the base breadcrumb, based on possible branch references.
   */
  private function getBaseBreadcrumb(Node $node): array {
    $breadcrumb = [];

    $branch = NULL;

    if ($node->bundle() === 'article') {
      $breadcrumb[] = [
        'text' => $this->translation->translate('Articles'),
        'uuid' => NULL,
        'route_name' => 'view.articles.page_1',
        'route_parameters' => [],
      ];
    }

    if ($node->hasField('field_branch')) {
      $branches = $node->get('field_branch')->referencedEntities();
      $branch = reset($branches);
    }

    if ($branch instanceof Node) {
      $url_object = $branch->toUrl();

      $breadcrumb[] = [
        'text' => $branch->label(),
        'uuid' => $branch->uuid(),
        'route_name' => $url_object->getRouteName(),
        'route_parameters' => $url_object->getRouteParameters(),
      ];
    }

    return $breadcrumb;
  }

  /**
   * Build a breadcrumb array, based on field_categories.
   */
  public function getCategoryBreadcrumb(Node $node): array {
    $breadcrumb = [];

    $categories = $node->get('field_categories')->referencedEntities();

    $category = reset($categories);

    if ($category instanceof Term) {
      $category_id = intval($category->id());
      // Get all parent categories, including current.
      $categories = $this->entityTypeManager->getStorage("taxonomy_term")
        ->loadAllParents($category_id);

      foreach ($categories as $category) {
        $breadcrumb[] = [
          'text' => $category->getName(),
          'uuid' => $category->uuid(),
        ];
      }
    }

    return $breadcrumb;
  }

  /**
   * Build a breadcrumb array, based on the content structure menu.
   */
  public function getMenuBreadcrumb(Node $node): array {
    $storage = $this->entityTypeManager->getStorage('menu_link_content');
    $current_uuid = $this->getBreadcrumbItemUuid($node);

    if ($current_uuid) {
      $breadcrumb = $this->menuLoop($current_uuid, $storage, []);
    }
    else {
      if ($node->get('field_breadcrumb_parent')->isEmpty()) {
        return [];
      }

      $breadcrumb_parent_menu_uuid = $node->get('field_breadcrumb_parent')->getString();

      $breadcrumb_parents = $storage->loadByProperties([
        'uuid' => $breadcrumb_parent_menu_uuid,
      ]);

      if (empty($breadcrumb_parents)) {
        return [];
      }

      $breadcrumb_parent = reset($breadcrumb_parents);

      $uuid = $breadcrumb_parent->get('uuid')->getString();

      $breadcrumb = $this->menuLoop($uuid, $storage, []);
    }

    $last_link = reset($breadcrumb);
    $last_link_nid = $last_link['route_parameters']['node'] ?? NULL;

    // If latest breadcrumb item is linked to this node, we won't display it.
    if ($last_link_nid == $node->id()) {
      array_shift($breadcrumb);
    }

    return $breadcrumb;
  }

  /**
   * Build the breadcrumb array.
   */
  public function getBreadcrumb(Node $node): array {
    $breadcrumb = $this->getBaseBreadcrumb($node);

    if ($node->hasField('field_breadcrumb_parent')) {
      return array_merge($this->getMenuBreadcrumb($node), $breadcrumb);
    }

    if ($node->hasField('field_categories')) {
      return array_merge($this->getCategoryBreadcrumb($node), $breadcrumb);
    }

    return $breadcrumb;
  }

  /**
   * Generating a breadcrumb tree, recursively.
   */
  public function menuLoop(string $uuid, EntityStorageInterface $storage, array $breadcrumb): array {
    $uuid = str_replace('menu_link_content:', '', $uuid);

    $links = $storage->loadByProperties([
      'uuid' => $uuid,
    ]);

    $link = reset($links);

    if (!($link instanceof MenuLinkContent)) {
      return $breadcrumb;
    }

    $url_object = $link->getUrlObject();

    $breadcrumb[] = [
      'text' => $link->getTitle(),
      'uuid' => $link->getPluginId(),
      'route_name' => $url_object->getRouteName(),
      'route_parameters' => $url_object->getRouteParameters(),
    ];

    $parent_uuid = $link->get('parent')->getString();

    if ($parent_uuid) {
      return $this->menuLoop($parent_uuid, $storage, $breadcrumb);
    }

    return $breadcrumb;
  }

  /**
   * Find a breadcrumb UUID by node, if it is added to the content structure.
   */
  public function getBreadcrumbItemUuid(Node $node): string|null {
    $storage = $this->entityTypeManager->getStorage('menu_link_content');

    $breadcrumb_items = $storage->loadByProperties([
      'link' => "entity:node/{$node->id()}",
      'menu_name' => $this->getMenuId(),
    ]);

    $breadcrumb_item = reset($breadcrumb_items);

    if ($breadcrumb_item instanceof MenuLinkContent) {
      return $breadcrumb_item->uuid();
    }

    return NULL;
  }

  /**
   * Getting the ID of the menu we use as a content structure.
   */
  public function getMenuId(): string {
    return 'content-structure';
  }

}
