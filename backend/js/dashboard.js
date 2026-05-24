/**
 * AuthMe Admin — Dashboard Page JavaScript
 *
 * Handles copy-to-clipboard functionality and toast notifications
 * for the admin dashboard page.
 *
 * @package AuthMe
 */
(function () {
  "use strict";

  /**
   * Show a temporary toast notification at the bottom-right of the page.
   *
   * @param {string} msg - The message to display.
   */
  function showToastMessage(msg) {
    var existingToast = document.querySelector(".am-toast");
    if (existingToast) existingToast.remove();

    var toast = document.createElement("div");
    toast.className = "am-toast";
    toast.innerText = msg;
    document.body.appendChild(toast);

    setTimeout(function () {
      if (toast && toast.parentNode) toast.remove();
    }, 2000);
  }

  /**
   * Copy the given text to the clipboard using the modern
   * Clipboard API with a textarea fallback for older browsers.
   *
   * @param {string} textToCopy - The text to copy.
   */
  function copyToClipboard(textToCopy) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard
        .writeText(textToCopy)
        .then(function () {
          showToastMessage("Copied to clipboard ✓");
        })
        .catch(function () {
          fallbackCopy(textToCopy);
        });
    } else {
      fallbackCopy(textToCopy);
    }
  }

  /**
   * Fallback copy method using a temporary textarea element.
   *
   * @param {string} text - The text to copy.
   */
  function fallbackCopy(text) {
    var textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.style.position = "fixed";
    textarea.style.left = "-9999px";
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand("copy");
    document.body.removeChild(textarea);
    showToastMessage("Copied to clipboard ✓");
  }

  /* ── Initialize on DOM Ready ────────────── */
  document.addEventListener("DOMContentLoaded", function () {
    var container = document.getElementById("am-dashboard");
    if (!container) return;

    // Attach click handlers to all copy buttons within the dashboard
    var copyButtons = container.querySelectorAll(".am-copy-btn");
    copyButtons.forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();

        var textToCopy = this.getAttribute("data-copy");
        if (textToCopy) {
          copyToClipboard(textToCopy);
          return;
        }

        // Fallback: try to get the code text from the parent code block
        var parentCode = this.closest(".am-code-block");
        if (parentCode) {
          var codeElem = parentCode.querySelector(".am-code-text");
          if (codeElem) {
            copyToClipboard(codeElem.innerText);
          }
        }
      });
    });
  });
})();
