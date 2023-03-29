<?php

namespace Drupal\dpl_fees\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dpl_react_apps\Controller\DplReactAppsController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides user intermediate list.
 *
 * @Block(
 *   id = "dpl_fees_block",
 *   admin_label = "List user fees"
 * )
 */
class IntermediateListBlock extends BlockBase implements ContainerFactoryPluginInterface
{
  /**
   * Drupal config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * IntermediateListBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Drupal config factory to get FBS and Publizon settings.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration = $configuration;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritDoc}
   *
   * @return mixed[]
   *   The app render array.
   */
  public function build()
  {

    $context = ['context' => 'Fees list'];
    $contextAria = ['context' => 'Fees list (Aria)'];

    $fbsConfig = $this->configFactory->get('dpl_fbs.settings');
    $publizonConfig = $this->configFactory->get('dpl_publizon.settings');
    $feesConfig = $this->configFactory->get('dpl_fees.settings');
    $data = [
        // Config.
        "fbs-base-url" => $fbsConfig->get('base_url'),
        "publizon-base-url" => $publizonConfig->get('base_url'),
        "page-size-desktop" => "25",
        "page-size-mobile" => "25",
        // Urls.
        // @todo update placeholder URL's
        // 'fees-page-url' => "https://unsplash.com/photos/wd6YQy0PJt8",
        // 'material-overdue-url' => "https://unsplash.com/photos/wd6YQy0PJt8",
        'search-url' => DplReactAppsController::searchResultUrl(),
        'dpl-cms-base-url' => DplReactAppsController::dplCmsBaseUrl(),
        // Texts.
        'intermediate-list-headline-text' => $this->t("fees & replacement costs", [], $context),
        'intermediate-list-body-text' => $feesConfig->get('intermediate_list_body_text'),
        // $this->t("overdue fees and replacement costs that were created before 27/10/2020 can still be paid on this page.", [], $context),
        'view-fees-and-compensation-rates-text' => $this->t("see our fees and replacement costs", [], $context),
        'material-and-author-text' => $this->t("and", [], $context),
        'total-fee-amount-text' => $this->t("Fee", [], $context),
        'other-materials-text' => $this->t("Other materials", [], $context),
        'material-by-author-text' => $this->t("By", [], $context),
        'intermediate-list-days-text' => $this->t("Days", [], $context),
        'pay-text' => $this->t("Pay", [], $context),
        'total-text' => $this->t("Total", [], $context),
        'i-accept-text' => $this->t("I accept the", [], $context),
        'terms-of-trade-text' => $feesConfig->get('terms_of_trade_text'),
        'unpaid-fees-text' => $this->t("Unsettled debt", [], $context),
        'pre-payment-type-change-date-text' => $this->t("BEFORE 27/10 2020", [], $context),
        'post-payment-type-change-date-text' => $this->t("AFTER 27/10 2020", [], $context),
        'already-paid-text' => $this->t("Please note that paid fees are not registered up until 72 hours after your payment after which your debt is updated and your user unblocked if it has been blocked.", [], $context),
        'intermediate-payment-modal-header-text' => $this->t("Unpaid fees post 27/10 2020", [], $context),
        'intermediate-payment-modal-body-text' => $this->t("You will be redirected to Mit Betalingsoverblik.", [], $context),
        'intermediate-payment-modal-notice-text' => $this->t("Paid fees can take up to 24 hours to registrer.", [], $context),
        'intermediate-payment-modal-goto-text' => $this->t("Go to Mit Betalingsoverblik", [], $context),
        'intermediate-payment-modal-cancel-text' => $this->t("Cancel", [], $context),
        'fee-details-modal-screen-reader-text' => $this->t("A modal containing details about a fee", [], $context),
        'empty-intermediate-list-text' => $this->t("You have 0 unpaid fees or replacement costs", [], $context),
        'fee-details-modal-close-modal-aria-label-text' => $this->t("Close fee details modal", [], $contextAria),
        'fee-details-modal-description-text' => $this->t("Modal containing information about this element or group of elements fees", [], $context),
        'turned-in-text' => $this->t("Turned in @date", [], $context),
        'plus-x-other-materials-text' => $this->t("+ @amount other materials", [], $context),
        'item-fee-amount-text' => $this->t("Fee @fee,-", [], $context),
        'fee-created-text' => $this->t("Fees charged @date", [], $context),
        'available-payment-types-url' => $feesConfig->get('payment_overview_url'),
        'payment-overview-url' => $this->t("https://unsplash.com/photos/yjI3ozta2Zk", [], $context),
        'view-fees-and-compensation-rates-url' => $feesConfig->get('fees_and_replacement_costs_url'),
        'terms-of-trade-url' => $this->t("https://unsplash.com/photos/JDzoTGfoogA", [], $context),
        ] + DplReactAppsController::externalApiBaseUrls();

    $app = [
      '#theme' => 'dpl_react_app',
      "#name" => 'intermediate-list',
      '#data' => $data,
    ];

    return $app;
  }
}
