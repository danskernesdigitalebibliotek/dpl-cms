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
 * @block(
 *   id = "dpl_fees_block",
 *   admin_label = "List user fees"
 * )
 */
class IntermediateListBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration = $configuration;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
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
  public function build() {

    $context = ['context' => 'Fees list'];
    $contextAria = ['context' => 'Fees list (Aria)'];

    $fbsConfig = $this->configFactory->get('dpl_fbs.settings');
    $publizonConfig = $this->configFactory->get('dpl_publizon.settings');

    $data = [
      // Config.
      "fbs-base-url" => $fbsConfig->get('base_url'),
      "publizon-base-url" => $publizonConfig->get('base_url'),
      // This overrides config.
      "page-size-desktop" => "25",
      // This overrides config.
      "page-size-mobile" => "25",
      // Urls.
      // @todo update placeholder URL's
      // 'fees-page-url' => "https://unsplash.com/photos/wd6YQy0PJt8",
      // 'material-overdue-url' => "https://unsplash.com/photos/wd6YQy0PJt8",
      'search-url' => DplReactAppsController::searchResultUrl(),
      'dpl-cms-base-url' => DplReactAppsController::dplCmsBaseUrl(),
      // Texts.
      'intermediate-list-headline-text' => $this->t("fees & replacement costs", [], $context),
      'intermediate-list-body-text' => $this->t("overdue fees and replacement costs that were created before 27/10/2020 can still be paid on this page.", [], $context),
      'view-fees-and-compensation-rates-text' => $this->t("see our fees and replacement costs", [], $context),
      'material-and-author-text' => $this->t("and", [], $context),
      'total-fee-amount-text' => $this->t("Fee", [], $context),
      'other-materials-text' => $this->t("Other materials", [], $context),
      'material-by-author-text' => $this->t("By", [], $context),
      'intermediate-list-days-text' => $this->t("Days", [], $context),
      'pay-text' => $this->t("Pay", [], $context),
      'total-text' => $this->t("Total", [], $context),
      'i-accept-text' => $this->t("I accept the", [], $context),
      'terms-of-trade-text' => $this->t("Terms of trade", [], $context),
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

      'available-payment-types-url' => $this->t("https://unsplash.com/photos/JDzoTGfoogA", [], $context),
      'payment-overview-url' => $this->t("https://unsplash.com/photos/yjI3ozta2Zk", [], $context),
      'view-fees-and-compensation-rates-url' => $this->t("https://unsplash.com/photos/NEJcmvLFcws", [], $context),
      'terms-of-trade-url' => $this->t("https://unsplash.com/photos/JDzoTGfoogA", [], $context),


      // 'group-modal-due-date-link-to-page-with-fees-text' => $this->t("Read more about fees", [], $context),
      // 'group-modal-due-date-renew-loan-close-modal-aria-label-text' => $this->t("Close renew loans modal", [], $contextAria),
      // 'group-modal-due-date-aria-description-text' => $this->t("This modal groups loans after due date and makes it possible to renew said loans", [], $context),
      // 'group-modal-checkbox-text' => $this->t("Choose all renewable", [], $context),
      // 'group-modal-due-date-header-text' => $this->t("Due date @date", [], $context),
      // 'group-modal-due-date-warning-loan-overdue-text' => $this->t("The due date of return is exceeded, therefore you will be charged a fee, when the item is returned", [], $context),
      // 'group-modal-go-to-material-text' => $this->t("Go to material details", [], $context),
      // 'group-modal-return-library-text' => $this->t("Can be returned to all branches of Samsøs libraries", [], $context),
      // 'loan-list-aria-label-list-button-text' => $this->t("This button shows all loans in the list", [], $contextAria),
      // 'loan-list-aria-label-stack-button-text' => $this->t("This button filters the list, so only one the materials that have the same due date is shown", [], $contextAria),
      // 'group-modal-renew-loan-denied-inter-library-loan-text' => $this->t("The item has been lent to you by another library and renewal is therefore conditional of the acceptance by that library", [], $context),
      // 'group-modal-renew-loan-denied-max-renewals-reached-text' => $this->t("The item cannot be renewed further", [], $context),
      // 'group-modal-renew-loan-denied-reserved-text' => $this->t("The item is reserved by another patron", [], $context),
      // 'loan-list-digital-loans-empty-list-text' => $this->t("You have no digital loans at the moment", [], $context),
      // 'loan-list-digital-loans-title-text' => $this->t("Digital loans", [], $context),
      // 'loan-list-digital-physical-loans-empty-list-text' => $this->t("You have 0 loans at the moment", [], $context),
      // 'loan-list-due-date-modal-aria-label-text' => $this->t("This button opens a modal that covers the entire page and contains loans with the same due date as the loan currently in focus", [], $contextAria),
      // 'group-modal-hidden-label-checkbox-on-material-text' => $this->t("Select material for renewal", [], $context),
      // 'loan-list-material-late-fee-text' => $this->t("You will be charged a fee, when the item is returned", [], $context),
      // 'loan-list-material-days-text' => $this->t("days", [], $context),
      // 'loan-list-material-day-text' => $this->t("day", [], $context),
      // 'loan-list-additional-materials-text' => [
      //   'type' => 'plural',
      //   'text' => [
      //     $this->t('+ 1 other material', [], $context),
      //     $this->t('+ @count other materials', [], $context),
      //   ],
      // ],
      // 'loan-list-physical-loans-empty-list-text' => $this->t("You have no physical loans at the moment", [], $context),
      // 'loan-list-physical-loans-title-text' => $this->t("Physical loans", [], $context),
      // 'loan-list-renew-multiple-button-explanation-text' => $this->t("This button opens a modal that covers the entire page and contains loans with different due dates, if some of the loans in the modal are renewable you can renew them", [], $context),
      // 'loan-list-renew-multiple-button-text' => $this->t("Renew several", [], $context),
      // 'loan-list-status-badge-danger-text' => $this->t("Expired", [], $context),
      // 'loan-list-status-badge-warning-text' => $this->t("Expiring soon", [], $context),
      // 'loan-list-status-circle-aria-label-text' => [
      //   'type' => 'plural',
      //   'text' => [
      //     $this->t('This material is due in one day', [], $contextAria),
      //     $this->t('This material is due in @count days', [], $contextAria),
      //   ],
      // ],
      // 'loan-list-title-text' => $this->t("Your loans", [], $context),
      // 'loan-list-to-be-delivered-digital-material-text' => $this->t("Due date @count", [], $context),
      // 'group-modal-due-date-material-text' => $this->t("Afleveres @count", [], $context),
      // 'loan-list-to-be-delivered-text' => $this->t("Due date @count", [], $context),
      // 'material-and-author-text' => $this->t("and", [], $context),
      // 'material-by-author-text' => $this->t("By", [], $context),
      // 'material-details-close-modal-aria-label-text' => $this->t("Close material details modal", [], $contextAria),
      // 'material-details-digital-due-date-label-text' => $this->t("Expires", [], $context),
      // 'material-details-physical-due-date-label-text' => $this->t("Afleveres", [], $context),
      // 'material-details-go-to-ereolen-text' => $this->t("Go to eReolen", [], $context),
      // 'material-details-link-to-page-with-fees-text' => $this->t("Read more about fees", [], $context),
      // 'material-details-loan-date-label-text' => $this->t("Loan date", [], $context),
      // 'material-details-material-number-label-text' => $this->t("Material Item Number", [], $context),
      // 'material-details-modal-aria-description-text' => $this->t("This modal shows material details, and makes it possible to renew a material, of that material is renewable", [], $contextAria),
      // 'material-details-overdue-text' => $this->t("Expired", [], $context),
      // 'material-details-renew-loan-button-text' => $this->t("Renew your loans", [], $context),
      // 'material-details-warning-loan-overdue-text' => $this->t("The due date of return is exceeded, therefore you will be charged a fee, when the item is returned", [], $context),
      // 'publizon-audio-book-text' => $this->t("Audiobook", [], $context),
      // 'publizon-ebook-text' => $this->t("E-book", [], $context),
      // 'publizon-podcast-text' => $this->t("Podcast", [], $context),
      // 'group-modal-aria-description-text' => $this->t("This modal makes it possible to renew materials", [], $contextAria),
      // 'group-modal-button-text' => $this->t("Renewable (@count)", [], $context),
      // 'group-modal-close-modal-aria-label-text' => $this->t("Close modal with grouped loans", [], $contextAria),
      // 'group-modal-header-text' => $this->t("Renew several", [], $context),
      // 'result-pager-status-text' => $this->t("Showing @itemsShown out of @hitcount loans", [], $context),
      // 'show-more-text' => $this->t("show more", [], $context),
    ];

    return dpl_react_render('intermediate-list', $data);
  }

}
