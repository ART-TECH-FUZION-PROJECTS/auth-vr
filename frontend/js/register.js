/**
 * AuthMe — Registration Form JavaScript
 *
 * Handles the registration flow:
 *   1. Real-time username availability check (debounced AJAX)
 *   2. Real-time email availability check (debounced AJAX)
 *   3. Country code dropdown population & mobile validation
 *   4. Password strength indicator
 *   5. Confirm password match
 *   6. "Send OTP" → stores user data, sends OTP, switches to OTP screen
 *
 * Flow: Register Form → Send OTP → OTP Screen → Verify OTP → Auto-create User
 *
 * @package AuthMe
 */

/* global authmeAjax, authmeIsValidEmail, authmeSetFieldState, authmeValidatePassword, authmePasswordStrength, authmeToast, authmeShowScreen, PhoneCore */

(function () {
  "use strict";

  /* ── Local State ────────────────────────── */
  var regState = {
    usernameValid: false,
    emailValid: false,
    mobileValid: false,
    passwordValid: false,
    confirmValid: false,
  };

  /* ── Debounce Timers ─────────────────── */
  var usernameDebounce = null;
  var emailDebounce = null;
  var mobileDebounce = null;

  /**
   * Reset the internal registration validation state.
   * Called by clearAllForms() in overlay.js when the overlay closes
   * or when the user switches away from the register screen.
   */
  function resetRegState() {
    regState.usernameValid = false;
    regState.emailValid = false;
    regState.mobileValid = false;
    regState.passwordValid = false;
    regState.confirmValid = false;

    // Also reset the custom dropdown back to India (+91) default
    var dropdown = document.getElementById("authme-reg-country-dropdown");
    var mobileInput = document.getElementById("authme-reg-mobile");
    if (dropdown && window.PhoneCore) {
      var sorted = window.PhoneCore.getCountriesSorted();
      var india = sorted.find(function (c) {
        return c.name === "India";
      });
      if (india) {
        // We need to trigger the selection visually
        var flagEl = dropdown.querySelector(".authme-selected-flag");
        var codeEl = dropdown.querySelector(".authme-selected-code");
        var hiddenInput = dropdown.querySelector('input[type="hidden"]');
        if (flagEl) flagEl.textContent = india.flag;
        if (codeEl) codeEl.textContent = india.code;
        if (hiddenInput) hiddenInput.value = india.code;
      }
      if (mobileInput) {
        mobileInput.placeholder = "Enter mobile number";
      }
    }

    // Reset the password strength bar
    var strengthEl = document.getElementById("authme-reg-password-strength");
    if (strengthEl) {
      strengthEl.innerHTML = "";
    }

    // Disable submit button
    var submitBtn = document.getElementById("authme-reg-submit-btn");
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Send OTP";
    }
  }

  // Expose globally so overlay.js clearAllForms() can call it
  window.authmeResetRegState = resetRegState;

  /* ── DOM Ready ───────────────────────── */
  document.addEventListener("DOMContentLoaded", function () {
    var usernameInput = document.getElementById("authme-reg-username");
    var emailInput = document.getElementById("authme-reg-email");
    var mobileInput = document.getElementById("authme-reg-mobile");
    var countrySelect = document.getElementById("authme-reg-country-code");
    var passwordInput = document.getElementById("authme-reg-password");
    var confirmInput = document.getElementById("authme-reg-confirm-password");
    var submitBtn = document.getElementById("authme-reg-submit-btn");
    var form = document.getElementById("authme-register-form");

    if (
      !usernameInput ||
      !emailInput ||
      !mobileInput ||
      !countrySelect ||
      !passwordInput ||
      !confirmInput ||
      !submitBtn ||
      !form
    )
      return;

    var usernameMsg = document.getElementById("authme-reg-username-msg");
    var emailMsg = document.getElementById("authme-reg-email-msg");
    var mobileMsg = document.getElementById("authme-reg-mobile-msg");
    var passwordMsg = document.getElementById("authme-reg-password-msg");
    var confirmMsg = document.getElementById("authme-reg-confirm-password-msg");
    var strengthEl = document.getElementById("authme-reg-password-strength");

    /* ── Username Validation ─────────────── */
    usernameInput.addEventListener("input", function () {
      var value = this.value.trim();
      regState.usernameValid = false;
      updateSubmitButton();

      if (!value) {
        authmeSetFieldState(usernameInput, usernameMsg, "", "");
        return;
      }

      // Client-side checks first
      if (!/^[a-zA-Z]/.test(value)) {
        authmeSetFieldState(
          usernameInput,
          usernameMsg,
          "error",
          "Username must start with an alphabet character.",
        );
        return;
      }
      if (!/^[a-zA-Z][a-zA-Z0-9]{3,13}$/.test(value)) {
        authmeSetFieldState(
          usernameInput,
          usernameMsg,
          "error",
          "Username must be 4–14 alphanumeric characters.",
        );
        return;
      }

      // Debounced AJAX uniqueness check
      clearTimeout(usernameDebounce);
      usernameDebounce = setTimeout(function () {
        authmeAjax(
          "authme_check_username",
          { username: value },
          function (data) {
            regState.usernameValid = true;
            authmeSetFieldState(
              usernameInput,
              usernameMsg,
              "success",
              data.message,
            );
            updateSubmitButton();
          },
          function (data) {
            regState.usernameValid = false;
            authmeSetFieldState(
              usernameInput,
              usernameMsg,
              "error",
              data.message,
            );
            updateSubmitButton();
          },
        );
      }, 500);
    });

    /* ── Email Validation ────────────────── */
    emailInput.addEventListener("input", function () {
      var value = this.value.trim();
      regState.emailValid = false;
      updateSubmitButton();

      if (!value) {
        authmeSetFieldState(emailInput, emailMsg, "", "");
        return;
      }

      // Client-side format check first
      if (!authmeIsValidEmail(value)) {
        authmeSetFieldState(
          emailInput,
          emailMsg,
          "error",
          "Please enter a valid email address.",
        );
        return;
      }

      // Debounced AJAX uniqueness check
      clearTimeout(emailDebounce);
      emailDebounce = setTimeout(function () {
        authmeAjax(
          "authme_check_email",
          { email: value },
          function (data) {
            regState.emailValid = true;
            authmeSetFieldState(emailInput, emailMsg, "success", data.message);
            updateSubmitButton();
          },
          function (data) {
            regState.emailValid = false;
            authmeSetFieldState(emailInput, emailMsg, "error", data.message);
            updateSubmitButton();
          },
        );
      }, 500);
    });

    /* ── Custom Country Dropdown Initialization ── */
    if (window.authmeInitCountryDropdown) {
      window.authmeInitCountryDropdown(
        "authme-reg-country-dropdown",
        function (country) {
          // Re-validate mobile if it already has a value
          if (mobileInput.value) {
            validateMobileNumber();
          }
        },
      );
    }

    /* ── Mobile Number Validation ─────────── */
    function validateMobileNumber() {
      var countryCode = document.getElementById(
        "authme-reg-country-code",
      ).value;
      var mobileValue = mobileInput.value.trim();

      // Check if a country code is actually selected (not the placeholder)
      if (!countryCode) {
        regState.mobileValid = false;
        if (mobileValue) {
          authmeSetFieldState(
            mobileInput,
            mobileMsg,
            "error",
            "Please select a country code first.",
          );
        } else {
          authmeSetFieldState(mobileInput, mobileMsg, "", "");
        }
        updateSubmitButton();
        return;
      }

      if (!mobileValue) {
        regState.mobileValid = false;
        authmeSetFieldState(mobileInput, mobileMsg, "", "");
        updateSubmitButton();
        return;
      }

      var selectedCountry = PhoneCore.findCountry(countryCode);
      if (!selectedCountry) {
        regState.mobileValid = false;
        authmeSetFieldState(
          mobileInput,
          mobileMsg,
          "error",
          "Please select a valid country code.",
        );
        updateSubmitButton();
        return;
      }

      // Use PhoneCore.validate()
      var validation = PhoneCore.validate(mobileValue, selectedCountry);

      if (validation.valid) {
        // Formatting is good, now check uniqueness via AJAX
        clearTimeout(mobileDebounce);
        mobileDebounce = setTimeout(function () {
          authmeAjax(
            "authme_check_mobile",
            { mobile: countryCode + mobileValue },
            function (data) {
              regState.mobileValid = true;
              authmeSetFieldState(
                mobileInput,
                mobileMsg,
                "success",
                data.message,
              );
              updateSubmitButton();
            },
            function (data) {
              regState.mobileValid = false;
              authmeSetFieldState(
                mobileInput,
                mobileMsg,
                "error",
                data.message,
              );
              updateSubmitButton();
            },
          );
        }, 500);
      } else {
        regState.mobileValid = false;
        authmeSetFieldState(
          mobileInput,
          mobileMsg,
          "error",
          "Mobile number " +
            validation.error.toLowerCase() +
            " for " +
            selectedCountry.name +
            ".",
        );
        updateSubmitButton();
      }
    }

    mobileInput.addEventListener("input", function () {
      validateMobileNumber();
    });

    /* ── Password Validation + Strength ──── */
    passwordInput.addEventListener("input", function () {
      var value = this.value;
      regState.passwordValid = false;

      // Update strength indicator
      updateStrengthBar(value);

      if (!value) {
        authmeSetFieldState(passwordInput, passwordMsg, "", "");
        updateSubmitButton();
        return;
      }

      var result = authmeValidatePassword(value);
      if (result.valid) {
        regState.passwordValid = true;
        authmeSetFieldState(
          passwordInput,
          passwordMsg,
          "success",
          result.message,
        );
      } else {
        authmeSetFieldState(
          passwordInput,
          passwordMsg,
          "error",
          result.message,
        );
      }

      // Re-validate confirm password if it has a value
      if (confirmInput.value) {
        validateConfirmPassword();
      }

      updateSubmitButton();
    });

    /* ── Confirm Password Validation ─────── */
    confirmInput.addEventListener("input", function () {
      validateConfirmPassword();
      updateSubmitButton();
    });

    function validateConfirmPassword() {
      var value = confirmInput.value;
      regState.confirmValid = false;

      if (!value) {
        authmeSetFieldState(confirmInput, confirmMsg, "", "");
        return;
      }

      if (value !== passwordInput.value) {
        authmeSetFieldState(
          confirmInput,
          confirmMsg,
          "error",
          "Passwords do not match.",
        );
      } else {
        regState.confirmValid = true;
        authmeSetFieldState(
          confirmInput,
          confirmMsg,
          "success",
          "Passwords match.",
        );
      }
    }

    /* ── Strength Bar ────────────────────── */
    function updateStrengthBar(password) {
      if (!strengthEl) return;

      if (!password) {
        strengthEl.innerHTML = "";
        return;
      }

      var level = authmePasswordStrength(password);

      // Create the bar element if not already present
      var bar = strengthEl.querySelector(".authme-strength-bar");
      if (!bar) {
        bar = document.createElement("div");
        bar.className = "authme-strength-bar";
        strengthEl.innerHTML = "";
        strengthEl.appendChild(bar);
      }

      // Reset all level classes
      bar.className = "authme-strength-bar authme-strength-" + level;
    }

    /* ── Submit Button State ─────────────── */
    function updateSubmitButton() {
      submitBtn.disabled = !(
        regState.usernameValid &&
        regState.emailValid &&
        regState.mobileValid &&
        regState.passwordValid &&
        regState.confirmValid
      );
    }

    /* ── Form Submission (Send OTP) ──────── */
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      if (submitBtn.disabled) return;

      var username = usernameInput.value.trim();
      var email = emailInput.value.trim();
      var countryCode = document.getElementById(
        "authme-reg-country-code",
      ).value;
      var mobileNumber = mobileInput.value.trim();
      var password = passwordInput.value;

      // Build full international mobile number (e.g., +919876543210)
      var fullMobile = countryCode + mobileNumber;

      // Get region code from the selected option's data attribute
      // Get region info from selected country
      var selectedCountry = PhoneCore.findCountry(countryCode);
      var mobileRegion = selectedCountry ? selectedCountry.name : "";

      // Build user_data JSON
      var userData = JSON.stringify({
        username: username,
        email: email,
        mobile_number: fullMobile,
        mobile_region: mobileRegion,
        password: password,
      });

      // Disable button & show loading
      submitBtn.disabled = true;
      submitBtn.textContent = "Sending OTP…";

      // Send OTP for registration
      authmeAjax(
        "authme_send_otp",
        {
          email: email,
          purpose: "registration",
          user_data: userData,
        },
        function (data) {
          authmeToast("success", data.message);

          // Store context for OTP screen
          document.getElementById("authme-otp-email").value = email;
          document.getElementById("authme-otp-purpose").value = "registration";
          document.getElementById("authme-otp-user-data").value = userData;
          document.getElementById("authme-otp-user-id").value = "";
          document.getElementById("authme-otp-remember").value =
            document.getElementById("authme-reg-remember").checked
              ? "true"
              : "false";

          // Switch to OTP screen
          authmeShowScreen("authme-otp-screen");

          // Start the OTP timer
          if (typeof window.authmeStartOtpTimer === "function") {
            window.authmeStartOtpTimer();
          }

          // Focus the first OTP box
          var firstBox = document.querySelector(
            "#authme-otp-screen .authme-otp-box",
          );
          if (firstBox) firstBox.focus();

          // Reset button
          submitBtn.disabled = false;
          submitBtn.textContent = "Send OTP";
        },
        function (data) {
          authmeToast("error", data.message);
          submitBtn.disabled = false;
          submitBtn.textContent = "Send OTP";
        },
      );
    });
  });
})();
