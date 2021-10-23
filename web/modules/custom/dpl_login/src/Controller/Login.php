<?php

namespace Drupal\dpl_login\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Url;
use Drupal\openid_connect\OpenIDConnectClaims;
use Drupal\openid_connect\Plugin\OpenIDConnectClientManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Login Controller for initiating MS logins.
 *
 * @package Drupal\dpl_login\Controller
 */
class Login extends ControllerBase {

  const OPENID_CONNECT_PLUGIN_NAME = 'adgangsplatformen';
  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Request Stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Path Validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Drupal\openid_connect\Plugin\OpenIDConnectClientManager definition.
   *
   * @var \Drupal\openid_connect\Plugin\OpenIDConnectClientManager
   */
  protected $pluginManager;

  /**
   * The OpenID Connect claims.
   *
   * @var \Drupal\openid_connect\OpenIDConnectClaims
   */
  protected $claims;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   The path validator service.
   * @param \Drupal\openid_connect\Plugin\OpenIDConnectClientManager $pluginManager
   *   The plugin manager.
   * @param \Drupal\openid_connect\OpenIDConnectClaims $claims
   *   The OpenID Connect claims.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    RequestStack $requestStack,
    PathValidatorInterface $pathValidator,
    OpenIDConnectClientManager $pluginManager,
    OpenIDConnectClaims $claims
  ) {
    $this->configFactory = $configFactory;
    $this->requestStack = $requestStack;
    $this->pathValidator = $pathValidator;
    $this->pluginManager = $pluginManager;
    $this->claims = $claims;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('config.factory'),
      $container->get('request_stack'),
      $container->get('path.validator'),
      $container->get('plugin.manager.openid_connect_client.processor'),
      $container->get('openid_connect.claims')
    );
  }

  /**
   * Redirect the user to Medlemsservice for authentication.
   */
  public function login(): Response {
    $configuration = $this->config(
      sprintf('openid_connect.settings.%s', self::OPENID_CONNECT_PLUGIN_NAME)
    )
      ->get('settings');
    /** @var \Drupal\openid_connect\Plugin\OpenIDConnectClientInterface $client */
    $client = $this->pluginManager->createInstance(
      self::OPENID_CONNECT_PLUGIN_NAME,
      $configuration
    );
    $scopes = $this->claims->getScopes($client);
    $destination = $this->previousUrl();

    $_SESSION['openid_connect_op'] = 'login';
    $_SESSION['openid_connect_destination'] = [
      $destination->getInternalPath(),
    ];

    $response = $client->authorize($scopes);

    return $response;
  }

  /**
   * Helper for finding the previous URL / the referrer.
   */
  protected function previousUrl(): Url {
    $front = Url::fromRoute('<front>');
    // This should give us a referrer if one is set. Otherwise we'll get the
    // front page. We'll use that as destination for a successful login process.
    $request = $this->requestStack->getCurrentRequest();

    if (!$request instanceof Request) {
      return $front;
    }

    $referer = $request->server->get('HTTP_REFERER');

    $fakeRequest = Request::create($referer);
    $url = $this->pathValidator->getUrlIfValid($fakeRequest->getRequestUri());

    // If we do not get a Url object it could be because the HTTP_REFREER points
    // to i.e. a 404 page. No matter why we will just use the frontpage as
    // destination.
    if (!$url instanceof Url) {
      return $front;
    }

    return $url;
  }

}
