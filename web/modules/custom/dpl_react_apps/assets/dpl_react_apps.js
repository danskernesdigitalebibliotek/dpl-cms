(function (Drupal, drupalSettings) {
  Drupal.behaviors.dplReactApps = {
    attach() {
      window.dplReactGlobals = window.dplReactGlobals || {};

      Object.assign(
        window.dplReactGlobals,
        drupalSettings.dpl_react_apps || {},
      );
    },
  };
})(Drupal, drupalSettings);
