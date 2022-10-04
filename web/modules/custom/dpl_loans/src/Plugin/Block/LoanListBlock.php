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

    $data = [
      "fbs-base-url-config" => 'http://fbs-mock.docker',
      "bottom-due-date-renew-loan-modal-button-text" => $this->t("Select all with the possibility of renewal", [], $context),
      "bottom-due-date-renew-loan-modal-checkbox-text" => $this->t("Renew possible", [], $context),
      "bottom-renew-loan-modal-button-text" => $this->t("Select all with the possibility of renewal", [], $context),
      "bottom-renew-loan-modal-checkbox-text" => $this->t("Renew possible", [], $context),
      "due-date-link-to-page-with-fees-text" => $this->t("Navigate to fees page", [], $context),
      "due-date-renew-loan-close-modal-text" => $this->t("This button closes the renew loan modal", [], $contextAria),
      "due-date-renew-loan-modal-button-text" => $this->t("Select all with the possibility of renewal", [], $context),
      "due-date-renew-loan-modal-checkbox-text" => $this->t("Renew possible", [], $context),
      "due-date-renew-loan-modal-description-text" => $this->t("This modal groups loans by delivery date and makes it possible to renew loans", [], $contextAria),
      "due-date-renew-loan-modal-header-text" => $this->t("To be returned", [], $context),
      "due-date-warning-loan-overdue-text" => $this->t("The delivery date for the loan has been exceeded, therefore you will be charged a fee when the material is delivered", [], $context),
      "loan-lList-modal-description-text" => $this->t("This modal groups loans by delivery date and makes it possible to renew loans", [], $contextAria),
      "loan-list-close-modal-text" => $this->t("Close modal", [], $contextAria),
      "loan-list-days-text" => $this->t("days", [], $context),
      "loan-list-denied-inter-library-loan-text" => $this->t("The material is on loan from another municipality and the renewal is therefore subject to another library's acceptance", [], $context),
      "loan-list-denied-max-renewals-reached-text" => $this->t("The material cannot be renewed several times", [], $context),
      "loan-list-denied-other-reason-text" => $this->t("The material is reserved by others", [], $context),
      "loan-list-empty-physical-loans-text" => $this->t("You currently have zero physical loans", [], $context),
      "loan-list-label-checkbox-material-modal-text" => $this->t("select item to renew", [], $contextAria),
      "loan-list-late-fee-desktop-text" => $this->t("You will be charged a fee, when the item is returned", [], $context),
      "loan-list-late-fee-mobile-text" => $this->t("You will be charged a fee, when the item is returned", [], $contextMobile),
      "loan-list-list-text" => $this->t("Show materials not grouped by delivery date", [], $contextAria),
      "loan-list-material-and-author-text" => $this->t("and", [], $context),
      "loan-list-material-by-author-text" => $this->t("By", [], $context),
      "loan-list-materials-desktop-text" => $this->t("other items", [], $context),
      "loan-list-materials-mobile-text" => $this->t("other items", [], $contextMobile),
      "loan-list-materials-modal-desktop-text" => $this->t("This button opens a dialog box that covers the entire window", [], $contextAria),
      "loan-list-materials-modal-mobile-text" => $this->t("This button opens a dialog box that covers the entire window", [], $contextMobile),
      "loan-list-physical-loans-title-text" => $this->t("Physical loans", [], $context),
      "loan-list-renew-multiple-button-explanation-text" => $this->t("Renew several, this button opens a dialog box that covers the entire window", [], $contextAria),
      "loan-list-renew-multiple-button-text" => $this->t("Renew several", [], $context),
      "loan-list-stack-text" => $this->t("Show materials grouped by delivery date", [], $contextAria),
      "loan-list-status-badge-danger-text" => $this->t("Expired", [], $context),
      "loan-list-status-badge-warning-text" => $this->t("Expiring soon", [], $context),
      "loan-list-status-circle-aria-label-text" => $this->t("Materialet skal afleveres om", [], $contextAria),
      "loan-list-title-text" => $this->t("Your loans", [], $context),
      "loan-list-to-be-delivered-material-text" => $this->t("Due date", [], $context),
      "loan-list-to-be-delivered-text" => $this->t("Due date", [], $context),
      "loan-modal-material-and-author-text" => $this->t("and", [], $context),
      "loan-modal-material-by-author-text" => $this->t("By", [], $context),
      "material-details-and-author-text" => $this->t("and", [], $context),
      "material-details-by-author-text" => $this->t("By", [], $context),
      "material-details-close-modal-text" => $this->t("This button closes the material detail modal", [], $contextAria),
      "material-details-hand-in-label-text" => $this->t("Return", [], $context),
      "material-details-link-to-page-with-fees-text" => $this->t("Navigate to fees page", [], $context),
      "material-details-loan-date-label-text" => $this->t("Lending date", [], $context),
      "material-details-material-number-label-text" => $this->t("Material number", [], $context),
      "material-details-modal-description-text" => $this->t("This modal displays a material's details and allows the material to be renewed if it can be renewed", [], $contextAria),
      "material-details-modal-overdue-text" => $this->t("Expired", [], $context),
      "material-details-overdue-text" => $this->t("Expired", [], $context),
      "material-details-renew-loan-button-text" => $this->t("Renew your loan", [], $context),
      "renew-loan-modal-button-text" => $this->t("Renewal all selected", [], $context),
      "renew-loan-modal-checkbox-text" => $this->t("Select all with the possibility of renewal", [], $context),
      "renew-loan-modal-close-modal-text" => $this->t("This button closes the renew loan modal", [], $contextAria),
      "renew-loan-modal-description-text" => $this->t("This modal makes it possible to renew loans", [], $contextAria),
      "renew-loan-modal-header-text" => $this->t("Renew several", [], $context),
    ];

    return dpl_react_render('loan-list', $data);
  }

}
