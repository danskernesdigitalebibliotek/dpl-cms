/**
 * @file
 * POC for redirecting to login page after registration.
 *
 * Could be done in dpl react as well.
 */

(function dplPatronRegPostRegister(drupalSettings) {
  let seconds = 10;
  let intervalId;

  function redirect() {
    const currentPath = drupalSettings.dpl_patron_reg.currentPath || null;
    if (currentPath) {
      window.location.assign(`/login?current-path=${currentPath}`);
    }

    window.location.assign("/login");
  }

  function updateSecs() {
    document.getElementById("seconds").innerHTML = seconds;
    seconds -= 1;
    if (seconds === -1) {
      clearInterval(intervalId);
      redirect();
    }
  }

  function countdownTimer() {
    intervalId = setInterval(function () {
      updateSecs();
    }, 1000);
  }

  Drupal.behaviors.dplPatronReg = {
    attach() {
      countdownTimer();
    },
  };
})(drupalSettings);
