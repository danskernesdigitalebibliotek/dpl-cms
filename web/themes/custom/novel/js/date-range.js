// Allow anonymous functions here as this construct is in accordance with
// how other JavaScript behavior code looks.
// eslint-disable-next-line func-names
(function (Drupal) {
  Drupal.behaviors.dateRange = {
    attach(context, settings) {
      window.DateRange.init(context, {
      });
    },
  };
})(Drupal);
