<?php

namespace Drupal\dpl_loans\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\dpl_react_apps\Controller\DplReactAppsController;

/**
 * Provides user loans list.
 *
 * @block(
 *   id = "dpl_loans_list_block",
 *   admin_label = "List user loans"
 * )
 */
class LoanListBlock extends BlockBase {

  /**
   * LoanListBlock constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Drupal config factory.
   */
  public function __construct(
    private ConfigFactory $configFactory
  ) {
  }

  /**
   * {@inheritDoc}
   *
   * @return mixed[]
   *   The app render array.
   */
  public function build() {
    $context = ['context' => 'Loan list'];
    $contextAria = ['context' => 'Loan list (Aria)'];

    $fbsConfig = $this->configFactory->get('dpl_fbs.settings');
    $publizonConfig = $this->configFactory->get('dpl_publizon.settings');

    $data = [
      // Config.
      "fbs-base-url-config" => $fbsConfig->get('base_url'),
      "publizon-base-url-config" => $publizonConfig->get('base_url'),
      // This overrides config.
      "page-size-desktop" => "25",
      // This overrides config.
      "page-size-mobile" => "25",
      // Urls.
      // @todo update placeholder URL's
      'fees-page-url' => "https://unsplash.com/photos/wd6YQy0PJt8",
      'material-overdue-url' => "https://unsplash.com/photos/wd6YQy0PJt8",
      'search-url' => DplReactAppsController::searchResultUrl(),
      'dpl-cms-base-url' => DplReactAppsController::dplCmsBaseUrl(),
      // Texts.
      'group-modal-due-date-link-to-page-with-fees-text' => $this->t("Read more about fees", [], $context),
      'group-modal-due-date-renew-loan-close-modal-aria-label-text' => $this->t("Close renew loans modal", [], $contextAria),
      'group-modal-due-date-aria-description-text' => $this->t("This modal groups loans after due date and makes it possible to renew said loans", [], $context),
      'group-modal-checkbox-text' => $this->t("Choose all renewable", [], $context),
      'group-modal-due-date-header-text' => $this->t("Due date @date", [], $context),
      'group-modal-due-date-warning-loan-overdue-text' => $this->t("The due date of return is exceeded, therefore you will be charged a fee, when the item is returned", [], $context),
      'loan-list-aria-label-list-button-text' => $this->t("This button shows all loans in the list", [], $contextAria),
      'loan-list-aria-label-stack-button-text' => $this->t("This button filters the list, so only one the materials that have the same due date is shown", [], $contextAria),
      'group-modal-renew-loan-denied-inter-library-loan-text' => $this->t("The item has been lent to you by another library and renewal is therefore conditional of the acceptance by that library", [], $context),
      'group-modal-renew-loan-denied-max-renewals-reached-text' => $this->t("The item cannot be renewed further", [], $context),
      'group-modal-renew-loan-denied-reserved-text' => $this->t("The item is reserved by another patron", [], $context),
      'loan-list-digital-loans-empty-list-text' => $this->t("You have no digital loans at the moment", [], $context),
      'loan-list-digital-loans-title-text' => $this->t("Digital loans", [], $context),
      'loan-list-digital-physical-loans-empty-list-text' => $this->t("You have @count loans at the moment", [], $context),
      'loan-list-due-date-modal-aria-label-text' => $this->t("This button opens a modal that covers the entire page and contains loans with the same due date as the loan currently in focus", [], $contextAria),
      'group-modal-hidden-label-checkbox-on-material-text' => $this->t("Select material for renewal", [], $context),
      'loan-list-material-late-fee-text' => $this->t("You will be charged a fee, when the item is returned", [], $context),
      'loan-list-material-days-text' => $this->t("days", [], $context),
      'loan-list-material-day-text' => $this->t("day", [], $context),
      'loan-list-additional-materials-text' => [
        'type' => 'plural',
        'text' => [
          $this->t('+ 1 other material', [], $context),
          $this->t('+ @count other materials', [], $context),
        ],
      ],
      'loan-list-physical-loans-empty-list-text' => $this->t("You have no physical loans at the moment", [], $context),
      'loan-list-physical-loans-title-text' => $this->t("Physical loans", [], $context),
      'loan-list-renew-multiple-button-explanation-text' => $this->t("This button opens a modal that covers the entire page and contains loans with different due dates, if some of the loans in the modal are renewable you can renew them", [], $context),
      'loan-list-renew-multiple-button-text' => $this->t("Renew several", [], $context),
      'loan-list-status-badge-danger-text' => $this->t("Expired", [], $context),
      'loan-list-status-badge-warning-text' => $this->t("Expiring soon", [], $context),
      'loan-list-status-circle-aria-label-text' => [
        'type' => 'plural',
        'text' => [
          $this->t('This material is due in one day', [], $contextAria),
          $this->t('This material is due in @count days', [], $contextAria),
        ],
      ],
      'loan-list-title-text' => $this->t("Your loans", [], $context),
      'loan-list-to-be-delivered-digital-material-text' => $this->t("Due date @count", [], $context),
      'group-modal-due-date-material-text' => $this->t("Afleveres @count", [], $context),
      'loan-list-to-be-delivered-text' => $this->t("Due date @count", [], $context),
      'material-and-author-text' => $this->t("and", [], $context),
      'material-by-author-text' => $this->t("By", [], $context),
      'material-details-close-modal-aria-label-text' => $this->t("Close material details modal", [], $contextAria),
      'material-details-due-date-label-text' => $this->t("Afleveres", [], $context),
      'material-details-link-to-page-with-fees-text' => $this->t("Read more about fees", [], $context),
      'material-details-loan-date-label-text' => $this->t("Loan date", [], $context),
      'material-details-material-number-label-text' => $this->t("Material Item Number", [], $context),
      'material-details-modal-aria-description-text' => $this->t("This modal shows material details, and makes it possible to renew a material, of that material is renewable", [], $contextAria),
      'material-details-overdue-text' => $this->t("Expired", [], $context),
      'material-details-renew-loan-button-text' => $this->t("Renew your loans", [], $context),
      'material-details-warning-loan-overdue-text' => $this->t("The due date of return is exceeded, therefore you will be charged a fee, when the item is returned", [], $context),
      'publizon-audio-book-text' => $this->t("Audiobook", [], $context),
      'publizon-ebook-text' => $this->t("E-book", [], $context),
      'publizon-podcast-text' => $this->t("Podcast", [], $context),
      'group-modal-aria-description-text' => $this->t("This modal makes it possible to renew materials", [], $contextAria),
      'group-modal-button-text' => $this->t("Renewable (@count)", [], $context),
      'group-modal-close-modal-aria-label-text' => $this->t("Close modal with grouped loans", [], $contextAria),
      'group-modal-header-text' => $this->t("Renew several", [], $context),
      'result-pager-status-text' => $this->t("Showing @itemsShown out of @hitcount loans", [], $context),
      'show-more-text' => $this->t("show more", [], $context),
    ];

    return dpl_react_render('loan-list', $data);
  }

}
