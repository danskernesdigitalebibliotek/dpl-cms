<?php

declare(strict_types=1);

namespace Drupal\dpl_fbi;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\dpl_fbi\GraphQL\Operations\WorkInfo;
use function Safe\preg_replace;

/**
 * The FBI service.
 */
class Fbi {

  const FBI_PROFILE = 'next';

  /**
   * Configuration.
   */
  protected ImmutableConfig $config;

  /**
   * General settings from 'dpl_library_agency'.
   *
   * @todo This ought to be our own configuration, but await work on DDFNEXT-957.
   */
  protected ImmutableConfig $agencySettings;

  /**
   * Constructor.
   *
   * @todo Move the profile configuration to this module.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
  ) {
    $this->config = $configFactory->get('dpl_fbi.settings');
    $this->agencySettings = $configFactory->get('dpl_library_agency.general_settings');
  }

  /**
   * Get profile names.
   *
   * @return array<string, string>
   *   Profile type to name mapping.
   */
  public function getProfiles(): array {
    return $this->agencySettings->get('fbi_profiles') ?? [
      FbiProfileType::DEFAULT->value => self::FBI_PROFILE,
      FbiProfileType::LOCAL->value => self::FBI_PROFILE,
      FbiProfileType::GLOBAL->value => self::FBI_PROFILE,
    ];

  }

  /**
   * Get API URLs for FBI.
   *
   * @return array<string, string>
   *   Service to URL mapping.
   */
  public function getServiceUrls(): array {
    $baseUrl = $this->config->get('base_url');

    $urls = [];
    // Create an URL for each profile.
    if ($baseUrl) {
      foreach ($this->getProfiles() as $type => $profile) {
        // The default FBI service has its own key with no suffix.
        $service_key = $type === FbiProfileType::DEFAULT->value ? 'fbi' : sprintf('fbi-%s', $type);

        // Create a service url with the profile embedded.
        $base_url = preg_replace('/\[profile\]/', $profile, $baseUrl);
        $urls[$service_key] = $base_url;
      }
    }

    return $urls;
  }

  /**
   * Get the service URL for the given profile.
   */
  public function getServiceUrl(string $type): string {
    $types = $this->getProfiles();

    if (!isset($types[$type])) {
      throw new \RuntimeException(sprintf('Unknown profile type %s', $type));
    }

    $profile = $types[$type];

    return preg_replace('/\[profile\]/', $profile, $this->config->get('base_url'));
  }

  /**
   * Get title of work.
   *
   * This should be replaced with a function that returns all the metadata the
   * work page needs.
   */
  public function getWorkTitle(string $wid): string {
    $info = WorkInfo::execute($wid);

    return $info->errorFree()->data?->work->titles->full[0] ?? '';
  }

}
