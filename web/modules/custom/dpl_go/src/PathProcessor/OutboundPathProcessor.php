<?php

declare(strict_types=1);

namespace Drupal\dpl_go\PathProcessor;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Routing\AdminContext;
use Drupal\dpl_go\GoSite;
use Symfony\Component\HttpFoundation\Request;
use function Safe\preg_match;

/**
 * Path processor that rewrites CMS/Go links to full URLs.
 *
 * When linking between the two "sites", we need to use full URLs to load/unload
 * the Go frontend.
 */
class OutboundPathProcessor implements OutboundPathProcessorInterface {

  public function __construct(
    protected GoSite $goSite,
    protected AdminContext $adminContext,
  ) {
  }

  /**
   * {@inheritdoc}
   *
   * @param string $path
   *   The path being processed.
   * @param array<string, mixed> $options
   *   Linking options.
   * @param ?\Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param ?\Drupal\Core\Render\BubbleableMetadata $bubbleableMetadata
   *   Caching metadata.
   */
  public function processOutbound(
    $path,
    &$options = [],
    Request $request = NULL,
    BubbleableMetadata $bubbleableMetadata = NULL,
  ): string {
    // Don't rewrite on admin pages, messes with field editing.
    if ($this->adminContext->isAdminRoute()) {
      return $path;
    }

    $pathParts = explode('/', $path);

    if (
      count($pathParts) == 3 &&
      $pathParts[1] == 'node' &&
      // is_numeric would seem an more obvious choice, but we're really not
      // interested in supporting "1337e0" or " 24  ".
      preg_match('/^\d+$/', $pathParts[2])
    ) {
      // Tell caching that this link depends on wether we're on the go site or
      // not.
      if ($bubbleableMetadata) {
        $bubbleableMetadata->addCacheContexts(['dpl_is_go']);
      }

      $isGoNode = $this->goSite->isGoNid($pathParts[2]);
      if (!is_null($isGoNode)) {
        if ($isGoNode xor $this->goSite->isGoSite()) {
          $options['absolute'] = TRUE;
          $options['base_url'] = $isGoNode ?
            $this->goSite->getGoBaseUrl() :
            $this->goSite->getCmsBaseUrl();
        }
      }
    }

    return $path;
  }

}
