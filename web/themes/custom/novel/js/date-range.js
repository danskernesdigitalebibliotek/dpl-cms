// Allow anonymous functions here as this construct is in accordance with
// how other JavaScript behavior code looks.
// eslint-disable-next-line func-names
(function (Drupal) {
  Drupal.behaviors.dateRange = {
    attach(context, settings) {
      window.DateRange.init(context, {
        locale: settings.dateRange.language,
        altInput: true,
        // A rather compact format that mimics what we use elsewhere.
        altFormat: "j. M Y",
      });
    },
  };
})(Drupal);
