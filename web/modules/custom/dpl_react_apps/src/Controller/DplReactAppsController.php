<?php

namespace Drupal\dpl_react_apps\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Url;
use Drupal\dpl_library_agency\ReservationSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for rendering full page DPL React apps.
 */
class DplReactAppsController extends ControllerBase {

  /**
   * Reservation settings service.
   *
   * @var \Drupal\dpl_library_agency\ReservationSettings
   */
  protected $reservationSettings;

  /**
   * DdplReactAppsController constructor.
   */
  public function __construct(
    ReservationSettings $reservationSettings,
  ) {
    $this->reservationSettings = $reservationSettings;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dpl_library_agency.reservation_settings'),
    );
  }

  /**
   * Render search result app.
   *
   * @return mixed[]
   *   Render array.
   */
  public function search(): array {
    $options = ['context' => 'Search Result'];

    return [
      'search-result' => dpl_react_render('search-result', [
        // Urls.
        'auth-url' => self::authUrl(),
        'material-url' => self::materialUrl(),
        'search-url' => self::searchResultUrl(),
        // Text.
        'available-text' => $this->t('Available', [], $options),
        'by-author-text' => $this->t('By', [], $options),
        'et-al-text' => $this->t('et. al.', [], $options),
        'in-series-text' => $this->t('In series', [], $options),
        'number-description-text' => $this->t('Nr.', [], $options),
        'out-of-text' => $this->t('out of', [], $options),
        'results-text' => $this->t('results', [], $options),
        'show-more-text' => $this->t('Show more', [], $options),
        'showing-text' => $this->t('Showing', [], $options),
        'unavailable-text' => $this->t('Unavailable', [], $options),
        'showing-results-for-text' => $this->t('Showing results for', [], $options),
      ]),
    ];
  }

  /**
   * Render work page.
   *
   * @param string $wid
   *   A work id.
   *
   * @return mixed[]
   *   Render array.
   */
  public function work(string $wid): array {
    // Translation context.
    $c = ['context' => 'Work Page'];

    return [
      'material' => dpl_react_render('material', [
        'wid' => $wid,
        // Config.
        'sms-notifications-for-reservations-enabled-config' =>
        // Data attributes can only be strings
        // so we need to convert the boolean to a number (0/1).
        (int) $this->reservationSettings->smsNotificationsIsEnabled(),
        // Urls.
        'auth-url' => self::authUrl(),
        'material-url' => self::materialUrl(),
        'search-url' => self::searchResultUrl(),
        'dpl-cms-base-url' => self::dplCmsBaseUrl(),
        // Text.
        'already-reserved-text' => $this->t('Already reserved', [], $c),
        'approve-reservation-text' => $this->t('Approve reservation', [], $c),
        'audience-text' => $this->t('Audience', [], $c),
        'available-text' => $this->t('Available', [], $c),
        'cannot-see-review-text' => $this->t('The review is not accessible', [], $c),
        'cant-reserve-text' => $this->t("Can't be reserved", [], $c),
        'cant-view-review-text' => $this->t('Cannot view review', [], $c),
        'close-text' => $this->t('Close', [], $c),
        'contributors-text' => $this->t('Contributors', [], $c),
        'copies-there-is-text' => $this->t('copies there is', [], $c),
        'creators-are-missing-text' => $this->t('Creators are missing', [], $c),
        'days-text' => $this->t('Days', [], $c),
        'description-headline-text' => $this->t('Description', [], $c),
        'details-of-the-material-text' => $this->t('Details of the material', [], $c),
        'details-text' => $this->t('Details', [], $c),
        'edition-text' => $this->t('Edition', [], $c),
        'editions-text' => $this->t('Editions', [], $c),
        'et-al-text' => $this->t('et al.', [], $c),
        'fiction-nonfiction-text' => $this->t('Fiction/nonfiction', [], $c),
        'find-on-bookshelf-text' => $this->t('Find on bookshelf', [], $c),
        'find-on-shelf-expand-button-explanation-text' => $this->t('Find on shelf expand button explanation', [], $c),
        'find-on-shelf-modal-close-modal-aria-label-text' => $this->t('Close reservation modal', [], $c),
        'find-on-shelf-modal-list-find-on-shelf-text' => $this->t('Find it on shelf', [], $c),
        'find-on-shelf-modal-list-item-count-text' => $this->t('Home', [], $c),
        'find-on-shelf-modal-list-material-text' => $this->t('Material', [], $c),
        'find-on-shelf-modal-no-location-specified-text' => $this->t('No location for find on shelf specified', [], $c),
        'find-on-shelf-modal-periodical-edition-dropdown-text' => $this->t('Find on shelf modal periodical dropdown - choose edition/volume', [], $c),
        'find-on-shelf-modal-periodical-year-dropdown-text' => $this->t('Find on shelf modal periodical dropdown - choose year', [], $c),
        'find-on-shelf-modal-screen-reader-modal-description-text' => $this->t('Reservation modal screen reader description', [], $c),
        'genre-and-form-text' => $this->t('Genre', [], $c),
        'get-online-text' => $this->t('Get online', [], $c),
        'go-to-e-reolen-text' => $this->t('Go to e-Reolen', [], $c),
        'go-to-text' => $this->t('Go to', [], $c),
        'have-no-interest-after-text' => $this->t('Have no interest after', [], $c),
        'hearts-icon-text' => $this->t('hearts', [], $c),
        'identifier-text' => $this->t('Identifiers', [], $c),
        'in-same-series-text' => $this->t('In the same series', [], $c),
        'in-series-text' => $this->t('in the series', [], $c),
        'infomedia-modal-close-modal-aria-label-text' => $this->t('Close infomedia modal', [], $c),
        'infomedia-modal-screen-reader-modal-description-text' => $this->t('Infomedia modal screen reader description', [], $c),
        'isbn-text' => $this->t('ISBN', [], $c),
        'language-text' => $this->t('Language', [], $c),
        'libraries-have-the-material-text' => $this->t('Libraries have the material', [], $c),
        'loading-text' => $this->t('Loading', [], $c),
        'login-to-see-review-text' => $this->t('Log in to read the review', [], $c),
        'material-header-all-editions-text' => $this->t('All editions', [], $c),
        'material-header-author-by-text' => $this->t('By', [], $c),
        'material-is-included-text' => $this->t('Material is included', [], $c),
        'material-is-loaned-out-text' => $this->t('Material is loaned out', [], $c),
        'missing-data-text' => $this->t('Missing data', [], $c),
        'modal-reservation-form-email-header-description-text' => $this->t('If you want to receive notifications by e-mail, you can enter or change the desired e-mail here.', [], $c),
        'modal-reservation-form-email-header-title-text' => $this->t('Change email', [], $c),
        'modal-reservation-form-email-input-field-description-text' => $this->t('Type in email', [], $c),
        'modal-reservation-form-email-input-field-label-text' => $this->t('Email', [], $c),
        'modal-reservation-form-no-interest-after-header-description-text' => $this->t('Set date for when your interest for the material will expire.', [], $c),
        'modal-reservation-form-no-interest-after-header-title-text' => $this->t('Change interest deadline', [], $c),
        'modal-reservation-form-pickup-header-description-text' => $this->t('Decide at which library you want to pickup the material.', [], $c),
        'modal-reservation-form-pickup-header-title-text' => $this->t('Choose pickup library', [], $c),
        'modal-reservation-form-sms-header-description-text' => $this->t('If you want to receive SMS notifications, you can enter or change your phone number here.', [], $c),
        'modal-reservation-form-sms-header-title-text' => $this->t('Change phone number', [], $c),
        'modal-reservation-form-sms-input-field-description-text' => $this->t('Input phone number', [], $c),
        'modal-reservation-form-sms-input-field-label-text' => $this->t('Phone', [], $c),
        'number-description-text' => $this->t('Nr.', [], $c),
        'number-in-queue-text' => $this->t('You are number', [], $c),
        'ok-button-text' => $this->t('Ok', [], $c),
        'one-month-text' => $this->t('1 month', [], $c),
        'one-year-text' => $this->t('1 year', [], $c),
        'original-title-text' => $this->t('Original title', [], $c),
        'out-of-text' => $this->t('out of', [], $c),
        'periodical-select-edition-text' => $this->t('Edition', [], $c),
        'periodical-select-year-text' => $this->t('Year', [], $c),
        'periodikum-select-week-text' => $this->t('Week', [], $c),
        'periodikum-select-year-text' => $this->t('Year', [], $c),
        'pickup-location-text' => $this->t('Pick up at', [], $c),
        'possible-text' => $this->t('possible', [], $c),
        'publisher-text' => $this->t('Publisher', [], $c),
        'queue-text' => $this->t('in queue', [], $c),
        'rating-is-text' => $this->t('Rating of this item is', [], $c),
        'rating-text' => $this->t('out of', [], $c),
        'read-article-text' => $this->t('Read article', [], $c),
        'receive-email-when-material-ready-text' => $this->t('Receive mail when the material is ready', [], $c),
        'receive-sms-when-material-ready-text' => $this->t('Receive SMS when the material is ready', [], $c),
        'reservation-errors-description-text' => $this->t('Year', [], $c),
        'reservation-errors-title-text' => $this->t('Reservation error', [], $c),
        'reservation-modal-close-modal-aria-label-text' => $this->t('Close reservation modal', [], $c),
        'reservation-modal-screen-reader-modal-description-text' => $this->t('modal for reservation', [], $c),
        'reservation-succes-is-reserved-for-you-text' => $this->t('is reserved for you', [], $c),
        'reservation-succes-preferred-pickup-branch-text' => $this->t('The material is at home, and you will be notified as soon as it is ready for you - collect it at', [], $c),
        'reservation-succes-title-text' => $this->t('The material is available and is now reserved for you!', [], $c),
        'reservations-for-this-material-text' => $this->t('reservations for this material', [], $c),
        'reserve-book-text' => $this->t('Reserve book', [], $c),
        'reserve-text' => $this->t('Reserve', [], $c),
        'reviews-text' => $this->t('Reviews', [], $c),
        'scope-text' => $this->t('Scope', [], $c),
        'see-online-text' => $this->t('See online', [], $c),
        'shift-text' => $this->t('Shift', [], $c),
        'six-months-text' => $this->t('6 months', [], $c),
        'this-month-text' => $this->t('This month', [], $c),
        'three-months-text' => $this->t('3 months', [], $c),
        'try-agin-button-text' => $this->t('Try again', [], $c),
        'two-months-text' => $this->t('2 months', [], $c),
        'type-text' => $this->t('Type', [], $c),
        'unavailable-text' => $this->t('Unavailable', [], $c),
        'we-have-shopped-text' => $this->t('In stock:', [], $c),
        'you-have-borrowed-text' => $this->t('You have borrowed', [], $c),
      ]),
    ];
  }

  /**
   * Builds an url for the local search result route.
   */
  public static function searchResultUrl(): string {
    return self::ensureUrlIsString(
      Url::fromRoute('dpl_react_apps.search_result')->toString()
    );
  }

  /**
   * Builds an url for the material/work route.
   */
  public static function materialUrl(): string {
    // React applications support variable replacement where variables are
    // prefixed with :. Specify the variable :workid as a parameter to let the
    // route build the url. Unfortunatly : will be encoded as %3A so we have to
    // decode the url again to make replacement work.
    $url = self::ensureUrlIsString(
      Url::fromRoute('dpl_react_apps.work')
        ->setRouteParameter('wid', ':workid')
        ->toString()
    );
    return urldecode($url);
  }

  /**
   * Builds an url for the react apps to use for authorization.
   */
  public static function authUrl(): string {
    return self::ensureUrlIsString(
      Url::fromRoute('dpl_login.login')->toString()
    );
  }

  /**
   * Get the base url of the API exposed by this site.
   */
  public static function dplCmsBaseUrl(): string {
    return self::ensureUrlIsString(
      Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString()
    );
  }

  /**
   * Make sure that generated url is a string.
   *
   * @param string|\Drupal\Core\GeneratedUrl $url
   *   Drupal generated Url object.
   */
  public static function ensureUrlIsString(string|GeneratedUrl $url): string {
    if ($url instanceof GeneratedUrl) {
      $url = $url->getGeneratedUrl();
    }

    return $url;
  }

}
