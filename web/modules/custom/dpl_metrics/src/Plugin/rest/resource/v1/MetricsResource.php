<?php

namespace Drupal\dpl_metrics\Plugin\rest\resource\v1;

use Drupal\dpl_admin\Services\VersionHelper;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use function Safe\sprintf;

// Descriptions quickly become long and Doctrine annotations have no good way
// of handling multiline strings.
// phpcs:disable Drupal.Files.LineLength.TooLong
/**
 * A resource for obtaining and exposing status information about running site.
 *
 * @RestResource(
 *   id = "metrics:metrics",
 *   label = @Translation("Get status information about running site"),
 *   serialization_class = "",
 *
 *   uri_paths = {
 *     "canonical" = "/api/v1/metrics",
 *   },
 *
 *   responses = {
 *     200 = {
 *       "description" = "OK",
 *       "schema" = {
 *         "type" = "object",
 *         "properties" = {
 *           "data" = {
 *             "type" = "object",
 *             "description" = "Metrics information",
 *             "properties" = {
 *                "cms-release-version" = {
 *                 "type" = "string",
 *                 "description" = "The dpl-cms build version",
 *               },
 *             },
 *           },
 *         },
 *       },
 *     },
 *     500 = {
 *       "description" = "Internal server error"
 *     },
 *   }
 * )
 */
class MetricsResource extends ResourceBase {
  /**
   * The version helper.
   *
   * @var \Drupal\dpl_admin\Services\VersionHelper
   */
  protected VersionHelper $versionHelper;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest')
    );

    $instance->versionHelper = $container->get('dpl_admin.version_helper');

    return $instance;
  }

  /**
   * Get site stats.
   *
   * @return \Drupal\rest\ResourceResponseInterface
   *   The response containing matching campaign.
   */
  public function get(Request $request): ResourceResponseInterface {
    switch ($request->headers->get('Accept')) {
      case 'application/json':
        $response = new ModifiedResourceResponse([
          'versions' => [
            'cms' => $this->versionHelper->getVersion(),
          ],
        ]);
        break;

      case 'text/plain':
      default:
        $response = new ModifiedResourceResponse();
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContent(implode("\n\n", $this->collectPrometheusMetricLines()));
        break;
    }

    return $response;
  }

  /**
   * Collect Prometheus metric lines.
   *
   * @return mixed[]
   *   The Prometheus metric lines.
   */
  private function collectPrometheusMetricLines(): array {
    $lines = [];
    $lines[] = $this->prometheusLine('versions', [
      'application' => 'cms',
      'version' => $this->versionHelper->getVersion(),
    ]);
    return $lines;
  }

  /**
   * Create a Prometheus formatted metrics line.
   *
   * @param string $metric
   *   The metric name (category).
   * @param mixed[] $values
   *   The metric values (label => value).
   */
  private function prometheusLine(string $metric, array $values): string {
    $elements = array_reduce(array_keys($values), function ($carry, $label) use ($values) {
      $carry[] = sprintf('%s="%s"', $label, $values[$label]);
      return $carry;
    }, []);

    return sprintf('%s{%s}', $metric, implode(', ', $elements));
  }

}
