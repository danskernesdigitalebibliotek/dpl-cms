<?php

namespace Drupal\eonext_mobilesearch\Mobilesearch;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\eonext_mobilesearch\Form\MobilesearchSettingsForm;
use Drupal\eonext_mobilesearch\Mobilesearch\DTO\MobilesearchEntityInterface;
use Drupal\eonext_mobilesearch\Mobilesearch\DTO\RequestDto;
use GuzzleHttp\ClientInterface;

/**
 * MobileSearch service comunication.
 */
class Mobilesearch {

  /**
   * Config service.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * Logger channel service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * Whether debug is enabled.
   *
   * @var bool
   */
  protected bool $debugEnabled;

  /**
   * MobileSearch communication class constructor.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   HTTP client service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerChannelFactory
   *   Logger channel factory service.
   */
  public function __construct(
    protected ClientInterface $httpClient,
    protected ConfigFactoryInterface $configFactory,
    LoggerChannelFactory $loggerChannelFactory,
  ) {
    $this->config = $this->configFactory->get(MobilesearchSettingsForm::CONFIG_ID);
    $this->logger = $loggerChannelFactory->get('eonext_mobilesearch');
    $this->debugEnabled = (bool) ($this->config->get('debug') ?? FALSE);
  }

  /**
   * Send an update content request.
   *
   * @param \Drupal\eonext_mobilesearch\Mobilesearch\DTO\MobilesearchEntityInterface $payload
   *   Update payload.
   */
  public function push(MobilesearchEntityInterface $payload, string $httpAction): bool {
    $this->logger->info("Pushing {$payload->getEntityName()}: {$payload->getId()}");

    $url = $this->config->get('url') . '/' . $payload->getRoute();
    try {
      $json = json_encode($this->wrapPayload($payload), JSON_THROW_ON_ERROR);
      $result = $this->httpClient->request($httpAction, $url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => $json,
        'timeout' => $this->config->get('timeout'),
      ]);

      $response = json_decode($result->getBody()->getContents(), TRUE, 512, JSON_THROW_ON_ERROR);
      $status = filter_var($response['status'] ?? '', FILTER_VALIDATE_BOOLEAN);
      return $status;
    }
    catch (\Exception $e) {
      $this->logger->critical('Push failed with exception: ' . $e->getMessage());

      return FALSE;
    }
    finally {
      if ($this->debugEnabled) {
        $this->log($url, $httpAction, $payload, $response ?? NULL, $status ?? FALSE);
      }
    }
  }

  /**
   * Log service communication information.
   *
   * @param \Drupal\eonext_mobilesearch\Mobilesearch\DTO\MobilesearchEntityInterface $payload
   *   Sent payload.
   * @param mixed $response
   *   Raw response.
   * @param bool $error
   *   Is an error logging.
   */
  protected function log(
    string $url,
    string $method,
    MobilesearchEntityInterface $payload,
    mixed $response = NULL,
    bool $isSuccess = TRUE,
  ): void {
    $payload = json_decode(json_encode($payload, JSON_THROW_ON_ERROR), TRUE, 512, JSON_THROW_ON_ERROR);

    array_walk_recursive($payload, static function (&$element) {
      if ($element && mb_strlen($element) > 120) {
        $element = Unicode::truncate($element, 120, TRUE, TRUE);
      }
    });

    $loggerCallback = $isSuccess
      ? $this->logger->info(...)
      : $this->logger->error(...);

    $loggerCallback(
      'Request (' . $method . ' | ' . $url . '): <pre>' . var_export(
        $payload,
        TRUE
      ) . '</pre>'
    );
    $loggerCallback(
      'Response: <pre>' . var_export($response, TRUE) . '</pre>'
    );
  }

  /**
   * Wrap payload with authorization credentials.
   *
   * @param \Drupal\eonext_mobilesearch\Mobilesearch\DTO\MobilesearchEntityInterface $payload
   *   Payload to alter.
   *
   * @return \JsonSerializable
   *   Result payload.
   */
  protected function wrapPayload(MobilesearchEntityInterface $payload): \JsonSerializable {
    return (new RequestDto())
      ->setCredentials(
        $this->config->get('agency'),
        $this->config->get('key')
      )
      ->setBody($payload);
  }

}
