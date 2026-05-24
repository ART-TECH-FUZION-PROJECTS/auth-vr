/**
 * AuthMe — Global Confirmation Modal JS
 *
 * Provides window.authmeConfirm(title, message, btnText)
 * which returns a Promise resolving to true (confirm) or false (cancel).
 *
 * @package AuthMe
 */

(function (window) {
  "use strict";

  /**
   * Show the confirmation modal.
   *
   * @param {string} title      Modal title.
   * @param {string} message    Detailed message.
   * @param {string} btnText    Text for the confirm button.
   * @returns {Promise<boolean>} Resolves to true on confirm, false on cancel.
   */
  window.authmeConfirm = function (title, message, btnText) {
    return new Promise(function (resolve) {
      var modal = document.getElementById("authme-confirm-modal");
      var titleEl = document.getElementById("authme-confirm-title");
      var messageEl = document.getElementById("authme-confirm-message");
      var proceedBtn = document.getElementById("authme-confirm-proceed");
      var cancelBtn = document.getElementById("authme-confirm-cancel");

      if (!modal || !titleEl || !messageEl || !proceedBtn || !cancelBtn) {
        console.error("AuthMe Confirm: Modal elements not found.");
        resolve(false);
        return;
      }

      // Set content
      titleEl.textContent = title || "Confirm Action";
      messageEl.textContent = message || "Are you sure you want to proceed?";
      proceedBtn.textContent = btnText || "Confirm";

      // Show modal
      modal.style.display = "flex";
      modal.classList.remove("hiding");

      // Handle Proceed
      var onProceed = function () {
        cleanup();
        resolve(true);
      };

      // Handle Cancel
      var onCancel = function () {
        cleanup();
        resolve(false);
      };

      // Cleanup function
      var cleanup = function () {
        modal.classList.add("hiding");
        setTimeout(function () {
          modal.style.display = "none";
        }, 300);

        proceedBtn.removeEventListener("click", onProceed);
        cancelBtn.removeEventListener("click", onCancel);
      };

      // Bind events
      proceedBtn.addEventListener("click", onProceed);
      cancelBtn.addEventListener("click", onCancel);
    });
  };
})(window);
