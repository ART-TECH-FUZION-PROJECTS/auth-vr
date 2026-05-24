/**
 * AuthMe — Toaster Notification System
 *
 * Provides authmeToast(type, message, duration) for showing
 * a simple centered toast notification at the top of the viewport.
 *
 * Types: 'success', 'error', 'warning', 'info'
 * (Controls the left-border accent color)
 *
 * @package AuthMe
 */

(function (window) {
  "use strict";

  /* Timer reference for auto-dismiss */
  var toasterTimeout = null;

  /**
   * Show a toast notification.
   *
   * @param {string} type     'success' | 'error' | 'warning' | 'info'
   * @param {string} message  Text to display.
   * @param {number} duration Auto-dismiss time in ms (default: 5000).
   */
  function authmeToast(type, message, duration) {
    duration = duration || 5000;

    var toaster = document.getElementById("authme-toaster");
    var toasterMessage = document.getElementById("authme-toaster-message");

    if (!toaster || !toasterMessage) return;

    /* Set the message text */
    toasterMessage.textContent = message;

    /* Reset type classes */
    toaster.className = "authme-toaster";

    /* Add type-specific class for left-border accent color */
    if (type && type !== "") {
      toaster.classList.add("authme-toaster-" + type);
    }

    /* Show the toaster */
    toaster.style.display = "block";

    /* Clear any existing timeout */
    if (toasterTimeout) {
      clearTimeout(toasterTimeout);
    }

    /* Auto-hide after the specified duration */
    toasterTimeout = setTimeout(function () {
      authmeCloseToaster();
    }, duration);
  }

  /**
   * Close the toaster with a fade-out animation.
   */
  function authmeCloseToaster() {
    var toaster = document.getElementById("authme-toaster");
    if (!toaster) return;

    toaster.classList.add("authme-toaster-hiding");

    setTimeout(function () {
      toaster.style.display = "none";
      toaster.classList.remove("authme-toaster-hiding");
    }, 300);
  }

  /* ── Bind close button ──────────────── */
  document.addEventListener("DOMContentLoaded", function () {
    var closeBtn = document.getElementById("authme-toaster-close");
    if (closeBtn) {
      closeBtn.addEventListener("click", function () {
        authmeCloseToaster();
      });
    }
  });

  /* Expose globally */
  window.authmeToast = authmeToast;
})(window);
