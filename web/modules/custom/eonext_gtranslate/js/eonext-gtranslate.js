Drupal.behaviors.eonext_gtranslate = {
  attach: function (context, settings) {
    const gtranslateModal  = Drupal.dialog('#eonext-gtranslate-element', {
      'title': Drupal.t('Language')
    });

    let link = document.querySelectorAll('.eonext-gtranslate-widget.link');
    link.forEach(link => {
      link.addEventListener('click', (e) => {
        gtranslateModal.showModal();
        e.preventDefault();
      });
    });
  },
  init: function (context, settings) {
    new google.translate.TranslateElement({pageLanguage: 'da'}, 'eonext-gtranslate-element');
  }
};
