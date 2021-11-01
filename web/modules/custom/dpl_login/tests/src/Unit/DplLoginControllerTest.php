<?php

namespace Drupal\Tests\dpl_library_token\Unit;

use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Tests\UnitTestCase;
use Drupal\dpl_login\AccessToken;
use Drupal\Core\Routing\UrlGenerator;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\dpl_login\UserTokensProvider;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\dpl_library_token\LibraryTokenHandler;
use Drupal\dpl_login\Controller\DplLoginController;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Utility\UnroutedUrlAssemblerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\dpl_login\Exception\MissingConfigurationException;

// Mocking user_logout function.
// TODO: Could be solved with php-mock in the future.
require_once 'user_logout.mock.php';

/**
 * Unit tests for the Library Token Handler.
 */
class DplLoginControllerTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $logger = $this->prophesize(LoggerInterface::class);
    $logger->error(Argument::any(), Argument::any())->shouldNotBeCalled();
    $logger_factory = $this->prophesize(LoggerChannelFactoryInterface::class);
    $logger_factory->get(Argument::any())->willReturn($logger->reveal());

    $fake_access_token = AccessToken::createFromOpenidConnectContext([
      'tokens' => [
        'access_token' => 'dasjhsadhadsjkhkajsdhkj',
        'expire' => 9999,
      ],
    ]);
    $user_token_provider = $this->prophesize(UserTokensProvider::class);
    $user_token_provider->getAccessToken()->willReturn($fake_access_token);

    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $config = $this->prophesize(ImmutableConfig::class);
    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $config_factory->get(LibraryTokenHandler::SETTINGS_KEY)->willReturn($config->reveal());

    $unrouted_url_assembler = $this->prophesize(UnroutedUrlAssemblerInterface::class);
    $generated_url = $this->prophesize(GeneratedUrl::class);
    $generated_url->getGeneratedUrl()->willReturn('https://local.site');
    $url_generator = $this->prophesize(UrlGenerator::class);
    $url_generator->generateFromRoute('<front>', Argument::cetera())->willReturn($generated_url);

    $container = new ContainerBuilder();
    $container->set('logger.factory', $logger_factory->reveal());
    $container->set('dpl_login.user_tokens', $user_token_provider->reveal());
    $container->set('config.factory', $config_factory->reveal());
    $container->set('unrouted_url_assembler', $unrouted_url_assembler->reveal());
    $container->set('url_generator', $url_generator->reveal());

    \Drupal::setContainer($container);
  }

  /**
   * Make sure an config missing exception is thrown.
   */
  public function testThatExceptionIsThrownIfLogoutEndpointIsMissing(): void {
    $container = \Drupal::getContainer();
    $controller = DplLoginController::create($container);
    $this->expectException(MissingConfigurationException::class);
    $this->expectExceptionMessage('Adgangsplatformen plugin config variable logout_endpoint is missing');
    $controller->logout();
  }

  /**
   * The user is redirected to external login if everything is ok.
   */
  public function testThatExternalRedirectIsActivatedIfEverythingIsOk(): void {
    $config = $this->prophesize(ImmutableConfig::class);
    $config->get('settings')->willReturn([
      'logout_endpoint' => 'https://valid.uri',
    ])->shouldBeCalledTimes(1);
    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $config_factory->get(LibraryTokenHandler::SETTINGS_KEY)->willReturn($config->reveal());

    $container = \Drupal::getContainer();
    $container->set('config.factory', $config_factory->reveal());
    \Drupal::setContainer($container);

    $controller = DplLoginController::create($container);
    $response = $controller->logout();

    $this->assertInstanceOf(TrustedRedirectResponse::class, $response);
    $this->assertSame(
      'https://valid.uri?singlelogout=true&access_token=dasjhsadhadsjkhkajsdhkj&redirect_uri=https%3A//local.site',
      $response->headers->get('location')
    );
  }

  /**
   * Test that normal Drupal users (admins get logged out.
   */
  public function testThatAdminsGetLoggedOut(): void {
    $config = $this->prophesize(ImmutableConfig::class);
    $config->get('settings')->willReturn([
      'logout_endpoint' => 'https://valid.uri',
    ])->shouldBeCalledTimes(1);
    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $config_factory->get(LibraryTokenHandler::SETTINGS_KEY)->willReturn($config->reveal());
    $user_token_provider = $this->prophesize(UserTokensProvider::class);
    $user_token_provider->getAccessToken()->willReturn(NULL);
    $url_generator = $this->prophesize(UrlGenerator::class);
    $url_generator->generateFromRoute('<front>', Argument::cetera())->willReturn('https://local.site');

    $container = \Drupal::getContainer();
    $container->set('config.factory', $config_factory->reveal());
    $container->set('dpl_login.user_tokens', $user_token_provider->reveal());
    $container->set('url_generator', $url_generator->reveal());
    \Drupal::setContainer($container);

    $controller = DplLoginController::create($container);
    $response = $response = $controller->logout();

    $this->assertInstanceOf(RedirectResponse::class, $response);
    $this->assertSame(
      'https://local.site',
      $response->headers->get('location')
    );
  }

}
