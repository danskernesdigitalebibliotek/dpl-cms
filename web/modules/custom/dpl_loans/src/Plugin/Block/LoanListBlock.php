<?php

namespace Drupal\dpl_loans\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides user loans list
 *
 * @block(
 *   id = "dpl_loans_list_block",
 *   admin_label = "List user loans"
 * )
 */
class LoanListBlock extends BlockBase {

  public function build() {
    $context = ['context' => 'Loan list'];
    $contextMobile = ['context' => 'Loan list (mobile)'];
    $contextAria = ['context' => 'Loan list (Aria)'];

    $fbsConfig = \Drupal::config('dpl_fbs.settings');
    $publizonConfig = \Drupal::config('dpl_publizon.settings');

    $data = [
      // Config
      "fbs-base-url-config" => $fbsConfig->get('base_url'),
      "publizon-base-url-config" => $publizonConfig->get('base_url'),
      "page-size-desktop" => "25", // this overrides config
      "page-size-mobile" => "25", // this overrides config
      // Urls.
      'fees-page-url' => "https://unsplash.com/photos/wd6YQy0PJt8", // todo
      'material-overdue-url' => "https://unsplash.com/photos/wd6YQy0PJt8", // todo
      'search-url' => self::searchResultUrl(),
      'dpl-cms-base-url' => self::dplCmsBaseUrl(),
      // Texts
      'group-modal-due-date-link-to-page-with-fees-text' => $this->t("Read more about fees",[],$c),
      'group-modal-due-date-renew-loan-close-modal-aria-label-text' => $this->t("Close renew loans modal",[],$c),
      'group-modal-due-date-aria-description-text' => $this->t("This modal groups loans after due date and makes it possible to renew said loans",[],$c),
      'group-modal-checkbox-text' => $this->t("Choose all renewable",[],$c),
      'group-modal-due-date-header-text' => $this->t("Due date @date",[],$c),
      'group-modal-due-date-warning-loan-overdue-text' => $this->t("The due date of return is exceeded, therefore you will be charged a fee, when the item is returned",[],$c),
      'loan-list-aria-label-list-button-text' => $this->t("This button shows all loans in the list",[],$c),
      'loan-list-aria-label-stack-button-text' => $this->t("This button filters the list, so only one the materials that have the same due date is shown",[],$c),
      'group-modal-renew-loan-denied-inter-library-loan-text' => $this->t("The item has been lent to you by another library and renewal is therefore conditional of the acceptance by that library",[],$c),
      'group-modal-renew-loan-denied-max-renewals-reached-text' => $this->t("The item cannot be renewed further ",[],$c),
      'group-modal-renew-loan-denied-reserved-text' => $this->t("The item is reserved by another patron",[],$c),
      'loan-list-digital-loans-empty-list-text' => $this->t("You have no digital loans at the moment",[],$c),
      'loan-list-digital-loans-title-text' => $this->t("Digital loans",[],$c),
      'loan-list-digital-physical-loans-empty-list-text' => $this->t("You have @count loans at the moment",[],$c),
      'loan-list-due-date-modal-aria-label-text' => $this->t("This button opens a modal that covers the entire page and contains loans with the same due date as the loan currently in focus",[],$c),
      'group-modal-hidden-label-checkbox-on-material-text' => $this->t("Select material for renewal",[],$c),
      'loan-list-material-late-fee-text' => $this->t("You will be charged a fee, when the item is returned",[],$c),
      'loan-list-material-days-text' => $this->t("days",[],$c),
      'loan-list-material-day-text' => $this->t("day",[],$c),
      'loan-list-additional-materials-text' => [
        'type' => 'plural',
        'text' => [
          $this->t('+ 1 other material', [], $c),
          $this->t('+ @count other materials', [], $c),
        ],
      ],
      'loan-list-physical-loans-empty-list-text' => $this->t("You have no physical loans at the moment",[],$c),
      'loan-list-physical-loans-title-text' => $this->t("Physical loans",[],$c),
      'loan-list-renew-multiple-button-explanation-text' => $this->t("This button opens a modal that covers the entire page and contains loans with different due dates, if some of the loans in the modal are renewable you can renew them",[],$c),
      'loan-list-renew-multiple-button-text' => $this->t("Renew several",[],$c),
      'loan-list-status-badge-danger-text' => $this->t("Expired",[],$c),
      'loan-list-status-badge-warning-text' => $this->t("Expiring soon",[],$c),
      'loan-list-status-circle-aria-label-text' => [
        'type' => 'plural',
        'text' => [
          $this->t('This material is due in one day', [], $c),
          $this->t('This material is due in @count days', [], $c),
        ],
      ],
      'loan-list-title-text' => $this->t("Your loans",[],$c),
      'loan-list-to-be-delivered-digital-material-text' => $this->t("Due date @count",[],$c),
      'group-modal-due-date-material-text' => $this->t("Afleveres @count",[],$c),
      'loan-list-to-be-delivered-text' => $this->t("Due date @count",[],$c),
      'material-and-author-text' => $this->t("and",[],$c),
      'material-by-author-text' => $this->t("By",[],$c),
      'material-details-close-modal-aria-label-text' => $this->t("Close material details modal",[],$c),
      'material-details-due-date-label-text' => $this->t("Afleveres",[],$c),
      'material-details-link-to-page-with-fees-text' => $this->t("Read more about fees",[],$c),
      'material-details-loan-date-label-text' => $this->t("Loan date",[],$c),
      'material-details-material-number-label-text' => $this->t("Material Item Number",[],$c),
      'material-details-modal-aria-description-text' => $this->t("This modal shows material details, and makes it possible to renew a material, of that material is renewable",[],$c),
      'material-details-overdue-text' => $this->t("Expired",[],$c),
      'material-details-renew-loan-button-text' => $this->t("Renew your loans",[],$c),
      'material-details-warning-loan-overdue-text' => $this->t("The due date of return is exceeded, therefore you will be charged a fee, when the item is returned",[],$c),
      'publizon-audio-book-text' => $this->t("Audiobook",[],$c),
      'publizon-ebook-text' => $this->t("E-book",[],$c),
      'publizon-podcast-text' => $this->t("Podcast",[],$c),
      'group-modal-aria-description-text' => $this->t("This modal makes it possible to renew materials",[],$c),
      'group-modal-button-text' => $this->t("Renewable (@count)",[],$c),
      'group-modal-close-modal-aria-label-text' => $this->t("Close modal with grouped loans",[],$c),
      'group-modal-header-text' => $this->t("Renew several",[],$c),
      'result-pager-status-text' => $this->t("Showing @itemsShown out of @hitcount loans",[],$c),
      'show-more-text' => $this->t("show more",[],$c),
    ]

    return dpl_react_render('loan-list', $data);
  }

}
