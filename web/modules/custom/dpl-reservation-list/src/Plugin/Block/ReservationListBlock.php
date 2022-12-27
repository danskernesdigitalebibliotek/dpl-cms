<?php

namespace Drupal\dpl_reservations\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dpl_react_apps\Controller\DplReactAppsController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dpl_library_agency\Branch\BranchRepositoryInterface;
use Drupal\dpl_library_agency\BranchSettings;
use Drupal\dpl_library_agency\Branch\Branch;


/**
 * Provides user reservations list.
 *
 * @Block(
 *   id = "dpl_reservations_list_block",
 *   admin_label = "List user reservations"
 * )
 */
class ReservationListBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * ReservationListBlock constructor.
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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, protected BranchSettings $branchSettings, protected BranchRepositoryInterface $branchRepository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration = $configuration;
    $this->configFactory = $configFactory;
    $this->branchSettings = $branchSettings;
    $this->branchRepository = $branchRepository;

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
      $container->get('dpl_library_agency.branch_settings'),
      $container->get('dpl_library_agency.branch.repository'),
    );
  }


  /**
   * Build a string of JSON data containing information about branches.
   *
   * Uses the format:
   *
   * [{
   *    "branchId":"DK-775120",
   *    "title":"Højbjerg"
   * }, {
   *    "branchId":"DK-775122",
   *    "title":"Beder-Malling"
   * }]
   *
   * This is to be used as props/attributes for React apps.
   *
   * @param \Drupal\dpl_library_agency\Branch\Branch[] $branches
   *   The branches to build the string with.
   */
  protected function buildBranchesJsonProp(array $branches) : string {
    return json_encode(array_map(function (Branch $branch) {
      return [
        'branchId' => $branch->id,
        'title' => $branch->title,
      ];
    }, $branches));
  }

  /**
   * Builds a comma separated list of branch ids.
   *
   * This is to be used as props/attributes for React apps.
   *
   * @param string[] $branchIds
   *   The ids of the branches to use.
   */
  protected function buildBranchesListProp(array $branchIds) : string {
    return implode(',', $branchIds);
  }


  /**
   * {@inheritDoc}
   *
   * @return mixed[]
   *   The app render array.
   */
  public function build() {
    $context = ['context' => 'Reservation list'];
    $contextAria = ['context' => 'Reservation list (Aria)'];

    $fbsConfig = $this->configFactory->get('dpl_fbs.settings');
    $publizonConfig = $this->configFactory->get('dpl_publizon.settings');

    $build = ['reservation-list' => dpl_react_render('reservation-list', [
      
      'ereolen-my-page-url' =>"https://ereolen.dk/user/me/",
      'blacklisted-pickup-branches-config' => $this->buildBranchesListProp($this->branchSettings->getExcludedReservationBranches()),
'pause-reservation-info-url' =>"https://images.unsplash.com/photo-1571043733612-d5444ff7d4ae?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1335&q=80",
'pause-reservation-start-date-config' => '2022-06-30',
// Config.
'branches-config' => $this->buildBranchesJsonProp($this->branchRepository->getBranches()),
'blacklisted-availability-branches-config' => $this->buildBranchesListProp($this->branchSettings->getExcludedAvailabilityBranches()),
'reservation-list-header-text' => $this->t('Your reservations',[], $context),
'reservation-list-physical-reservations-header-text' => $this->t('Physical reservations',[], $context),
'reservation-list-pause-reservation-text' => $this->t('Pause your reservations',[], $context),
'reservation-list-digital-reservations-header-text' => $this->t('Digital reservations',[], $context),
'reservation-list-ready-for-pickup-title-text' => $this->t('Ready for pickup',[], $context),
'reservation-list-ready-for-pickup-empty-text' => $this->t('At the moment you have 0 reservations ready for pickup',[], $context),
'reservation-list-physical-reservations-empty-text' => $this->t('At the moment you have 0 physical reservations',[], $context),
'reservation-list-all-empty-text' => $this->t('At the moment you have 0 reservations',[], $context),
'reservation-list-digital-reservations-empty-text' => $this->t('At the moment you have 0 reservations on digital items',[], $context),
'reservation-list-ready-text' => $this->t('Ready',[], $context),
'material-by-author-text' => $this->t('By',[], $context),
'material-and-author-text' => $this->t('and',[], $context),
'reservation-list-number-in-queue-text' => $this->t('There are @count people in the queue before you',[], $context),
'reservation-list-first-in-queue-text' => $this->t('You are at the front of the queue',[], $context),
'reservation-list-in-queue-text' => $this->t('queued',[], $context),
'reservation-pick-up-latest-text' => $this->t('Pick up before @date',[], $context),
'publizonEbook-text' => $this->t('E-book',[], $context),
'publizon-audio-book-text' => $this->t('Audiobook',[], $context),
'publizon-podcast-text' => $this->t('Podcast',[], $context),
'reservation-list-loan-before-text' => $this->t('Borrow before @date',[], $context),
'reservation-list-available-in-text' => $this->t('Available in @count days',[], $context),
'reservation-list-days-text' => $this->t('days',[], $context),
'reservation-list-day-text' => $this->t('day',[], $context),
'reservation-details-button-remove-text' => $this->t('Remove your reservation',[], $context),
'reservation-details-others-in-queue-text' => $this->t('Others are queueing for this material',[], $context),
'reservation-details-number-in-queue-label-text' => $this->t('@count queued',[], $context),
'reservation-details-status-title-text' => $this->t('Status',[], $context),
'reservation-details-expires-title-text' => $this->t('Pickup deadline',[], $context),
'reservation-details-digital-material-expires-title-text' => $this->t('Borrow before',[], $context),
'reservation-details-pick-up-at-title-text' => $this->t('Pickup branch',[], $context),
'reservation-details-change-text' => $this->t('Apply changes',[], $context),
'reservation-details-pickup-deadline-title-text' => $this->t('Pickup deadline',[], $context),
'reservation-details-date-of-reservation-title-text' => $this->t('Date of reservation',[], $context),
'reservation-details-no-interest-after-title-text' => $this->t('Not interested after',[], $context),
'reservation-details-ready-for-loan-text' => $this->t('Ready for pickup',[], $context),
'reservation-details-remove-digital-reservation-text' => $this->t('Remove your reservation',[], $context),
'reservation-details-digital-reservation-go-to-ereolen-text' => $this->t('Go to eReolen',[], $context),
'reservation-details-borrow-before-text' => $this->t('Borrow before @date',[], $context),
'reservation-details-expires-text' => $this->t('Your reservation expires @date!',[], $context),
'reservation-details-save-text' => $this->t('Save',[], $context),
'reservation-details-cancel-text' => $this->t('Cancel',[], $context),
'delete-reservation-modal-header-text' => [
'type' => 'plural',
'text' => [
    $this->t('Cancel reservation', [], $context),
    $this->t('Cancel reservations', [], $context),
],
],

'delete-reservation-modal-delete-question-text' => [
'type' => 'plural',
'text' => [
    $this->t('Do you want to cancel your reservation?', [], $context),
    $this->t('Do you want to cancel your reservations?', [], $context),
],
],
'delete-reservation-modal-delete-question-text' => [
'type' => 'plural',
'text' => [
    $this->t('Cancel reservation', [], $context),
    $this->t('Cancel reservations', [], $context),
],
],

'delete-reservation-modal-not-regrettable-text' => $this->t('You cannot regret this action',[], $context),
'delete-reservation-modal-close-modal-text' => $this->t('Close delete reservation modal',[], $context),
'delete-reservation-modal-aria-description-text' => $this->t('This button opens a modal that covers the entire page and contains the possibility to delete a selected reservation, or multiple selected reservations',[], $context),
'reservation-listPause-reservation-text' => $this->t('Pause your reservations',[], $context),
'reservation-list-on-hold-aria-text' => $this->t('Reservations have been paused in the following time span: ',[], $context),
'reservation-list-pause-reservation-aria-modal-text' => $this->t('This button opens a modal that covers the entire page and contains the possibility to pause physical reservations',[], $context),
'pause-reservation-modal-aria-description-text' => $this->t('This modal makes it possible to pause your physical reservations',[], $context),
'pause-reservation-modal-header-text' => $this->t('Pause reservations on physical items',[], $context),
'pause-reservation-modal-body-text' => $this->t('Pause your reservations early, since reservations that are already being processed, will not be paused.',[], $context),
'pause-reservation-modal-close-modal-text' => $this->t('Close pause reservations modal',[], $context),
'date-inputs-start-date-label-text' => $this->t('Start date',[], $context),
'date-inputs-end-date-label-text' => $this->t('End date',[], $context),
'pause-reservation-modal-below-inputs-text-text'=> $this->t('pause-reservation-modal-below-inputs-text-text',[], $context),
'pause-reservation-modal-link-text' => $this->t('Read more about pausing reservertions and what that means here',[], $context),
'pause-reservation-modal-save-button-label-text' => $this->t('Save',[], $context),
'one-month-text' => $this->t('1 month',[], $context),
'two-months-text' => $this->t('2 months',[], $context),
'three-months-text' => $this->t('3 months',[], $context),
'six-months-text' => $this->t('6 months',[], $context),
'one-year-text' => $this->t('1 year',[], $context),
'list-details-nothing-selected-label-text' => $this->t('Pick',[], $context),
'show-more-text' => $this->t('show more',[], $context),
'result-pager-status-text' => $this->t('Showing @itemsShown out of @hitcount results',[], $context),
'reservation-list-status-icon-ready-for-pickup-aria-label-text' => $this->t('This material is ready for pickup',[], $context),

'reservation-list-status-icon-queued-aria-label-text' => [
'type' => 'plural',
'text' => [
    $this->t('You are the only person queued for this material', [], $context),
    $this->t('This material has @count people in queue before you', [], $context),
],
],
'reservation-list-status-icon-ready-in-aria-label-text' => [
'type' => 'plural',
'text' => [
    $this->t('This material is ready in 1 day', [], $context),
    $this->t('This material is ready in @count days', [], $context),
],
],
      ] + DplReactAppsController::externalApiBaseUrls()),
    ];
    return $build;
  }

}
