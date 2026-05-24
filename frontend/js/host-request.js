(function (window) {
  "use strict";

  /**
   * AuthMe — Become a Host Modal JavaScript
   *
   * Handles the multi-step "Become a Host" application flow:
   *   Step 1: Personal Information (username, fullname, email, mobile with country code)
   *   Step 2: Document Upload (Aadhar Front/Back, PAN Card - JPEG only, max 1MB)
   *   Step 3: OTP Verification (6-digit code sent to email)
   *   Step 4: Success Message (auto-close after 15 seconds)
   *
   * Flow: Personal Info → Documents → OTP Verification → Application Submitted
   *
   * @package AuthMe
   */

  /* ── DOM Elements ─────────────────────── */
  var backdrop = document.getElementById("authme-host-backdrop");
  var container = document.getElementById("authme-host-container");
  var closeBtn = document.getElementById("authme-host-close");

  // Step 1
  var usernameInput = document.getElementById("authme-host-username");
  var fullnameInput = document.getElementById("authme-host-fullname");
  var emailInput = document.getElementById("authme-host-email");
  var mobileInput = document.getElementById("authme-host-mobile");
  var countrySelect = document.getElementById("authme-host-country-code");
  var nextBtn = document.getElementById("authme-host-next-btn");

  // Step 2
  var aadharfInput = document.getElementById("authme-host-aadharf");
  var aadharbInput = document.getElementById("authme-host-aadharb");
  var panInput = document.getElementById("authme-host-pan");
  var sendOtpBtn = document.getElementById("authme-host-send-otp-btn");
  var prevTo1Btn = document.getElementById("authme-host-prev-to-1");

  // Step 3 (OTP)
  var otpVerifyBtn = document.getElementById("authme-host-otp-submit-btn");
  var prevTo2Btn = document.getElementById("authme-host-prev-to-2");
  var resendBtn = document.getElementById("authme-host-resend-btn");
  var otpTimerEl = document.getElementById("authme-host-otp-timer");
  var loader = document.getElementById("authme-host-loader");

  /* ── State ───────────────────────────── */
  var hostState = {
    usernameValid: false,
    fullnameValid: false,
    emailValid: false,
    mobileValid: false,

    files: {
      aadharf: null, // base64
      aadharb: null,
      pan: null,
    },

    email: "", // for OTP
    otpCode: "", // for OTP
  };

  var usernameDebounce, emailDebounce, mobileDebounce;
  var resendInterval;

  /* ── Initialization ──────────────────── */
  document.addEventListener("DOMContentLoaded", function () {
    if (!backdrop) return;

    // Custom Country Dropdown
    if (window.authmeInitCountryDropdown) {
      window.authmeInitCountryDropdown(
        "authme-host-country-dropdown",
        function (country) {
          if (mobileInput.value) validateMobile();
        },
      );
    }

    bindEvents();
  });

  function bindEvents() {
    if (closeBtn) {
      closeBtn.addEventListener("click", closeHostModal);
    }

    // Only close on backdrop explicitly clicked?
    // User requesting: "Clicking outside the modal overlay... must NOT close" -> Do nothing on backdrop click.

    // Navigation
    if (nextBtn)
      nextBtn.addEventListener("click", function () {
        goToStep(2);
      });
    if (prevTo1Btn)
      prevTo1Btn.addEventListener("click", function () {
        goToStep(1);
      });
    if (prevTo2Btn)
      prevTo2Btn.addEventListener("click", function () {
        goToStep(2);
      });
    if (sendOtpBtn) sendOtpBtn.addEventListener("click", handleSendOtp);
    if (otpVerifyBtn)
      otpVerifyBtn.addEventListener("click", function (e) {
        e.preventDefault();
        handleVerifySubmit();
      });

    // Validation - Step 1
    usernameInput.addEventListener("input", validateUsername);
    emailInput.addEventListener("input", validateEmail);
    fullnameInput.addEventListener("input", validateFullname);
    mobileInput.addEventListener("input", validateMobile);

    // Uploads - Step 2
    bindFileUpload("authme-host-aadharf", "aadharf");
    bindFileUpload("authme-host-aadharb", "aadharb");
    bindFileUpload("authme-host-pan", "pan");

    // OTP - Step 3
    bindOtpInputs();
    if (resendBtn) resendBtn.addEventListener("click", handleResendOtp);
  }

  /* ── Modal Controls ──────────────────── */
  function openHostModal() {
    if (!backdrop) return;
    backdrop.style.display = "flex";
    setTimeout(function () {
      backdrop.classList.add("authme-host-visible");
    }, 10);
    document.documentElement.classList.add("authme-html-locked");
    document.body.classList.add("authme-body-locked");
  }

  function closeHostModal() {
    if (!backdrop) return;
    backdrop.classList.remove("authme-host-visible");
    setTimeout(function () {
      backdrop.style.display = "none";
      document.documentElement.classList.remove("authme-html-locked");
      document.body.classList.remove("authme-body-locked");

      // Remove URL param
      if (window.history && window.history.replaceState) {
        var url = new URL(window.location.href);
        if (url.searchParams.has("become-host")) {
          url.searchParams.delete("become-host");
          window.history.replaceState({}, "", url.toString());
        }
      }
    }, 300);
  }

  /* ── Step Navigation ─────────────────── */
  function goToStep(step) {
    var screens = document.querySelectorAll(".authme-host-screen");
    screens.forEach(function (s) {
      s.classList.remove("authme-host-screen-active");
    });
    var target = document.getElementById("authme-host-step-" + step);
    if (target) {
      target.classList.add("authme-host-screen-active");
    }

    // Update Tracker Dots (Steps 1 to 3)
    if (step <= 3) {
      var dots = document.querySelectorAll(".host-step-dot");
      var lines = document.querySelectorAll(".host-step-line");
      dots.forEach(function (d) {
        d.classList.remove("active", "completed");
        var dStep = parseInt(d.dataset.step);
        if (dStep < step) d.classList.add("completed");
        else if (dStep === step) d.classList.add("active");
      });
      lines.forEach(function (l, idx) {
        l.classList.remove("active");
        if (idx < step - 1) l.classList.add("active");
      });
    }
  }

  /* ── Step 1 Validation ───────────────── */
  function checkStep1() {
    if (nextBtn) {
      nextBtn.disabled = !(
        hostState.usernameValid &&
        hostState.emailValid &&
        hostState.fullnameValid &&
        hostState.mobileValid
      );
    }
  }

  function setMsg(input, msgElId, type, message) {
    var msgEl = document.getElementById(msgElId);
    input.classList.remove(
      "authme-host-input-success",
      "authme-host-input-error",
    );
    if (msgEl) {
      msgEl.classList.remove(
        "authme-host-msg-success",
        "authme-host-msg-error",
      );
      msgEl.textContent = message;
    }

    if (type === "success") {
      input.classList.add("authme-host-input-success");
      if (msgEl) msgEl.classList.add("authme-host-msg-success");
    } else if (type === "error") {
      input.classList.add("authme-host-input-error");
      if (msgEl) msgEl.classList.add("authme-host-msg-error");
    }
  }

  function validateUsername() {
    var val = usernameInput.value.trim();
    hostState.usernameValid = false;
    checkStep1();

    if (!val) {
      setMsg(usernameInput, "authme-host-username-msg", "", "");
      return;
    }
    if (!/^[a-zA-Z]/.test(val)) {
      setMsg(
        usernameInput,
        "authme-host-username-msg",
        "error",
        "Must start with a letter.",
      );
      return;
    }
    if (!/^[a-zA-Z][a-zA-Z0-9]{3,13}$/.test(val)) {
      setMsg(
        usernameInput,
        "authme-host-username-msg",
        "error",
        "Must be 4–14 alphanumeric characters.",
      );
      return;
    }

    clearTimeout(usernameDebounce);
    usernameDebounce = setTimeout(function () {
      window.authmeAjax(
        "authme_check_host_username",
        { username: val },
        function (res) {
          hostState.usernameValid = true;
          setMsg(
            usernameInput,
            "authme-host-username-msg",
            "success",
            res.message,
          );
          checkStep1();
        },
        function (err) {
          hostState.usernameValid = false;
          setMsg(
            usernameInput,
            "authme-host-username-msg",
            "error",
            err.message,
          );
          checkStep1();
        },
      );
    }, 500);
  }

  function validateEmail() {
    var val = emailInput.value.trim();
    hostState.emailValid = false;
    checkStep1();

    if (!val) {
      setMsg(emailInput, "authme-host-email-msg", "", "");
      return;
    }
    if (!window.authmeIsValidEmail(val)) {
      setMsg(
        emailInput,
        "authme-host-email-msg",
        "error",
        "Invalid email address.",
      );
      return;
    }

    clearTimeout(emailDebounce);
    emailDebounce = setTimeout(function () {
      window.authmeAjax(
        "authme_check_host_email",
        { email: val },
        function (res) {
          hostState.emailValid = true;
          setMsg(emailInput, "authme-host-email-msg", "success", res.message);
          checkStep1();
        },
        function (err) {
          hostState.emailValid = false;
          setMsg(emailInput, "authme-host-email-msg", "error", err.message);
          checkStep1();
        },
      );
    }, 500);
  }

  function validateFullname() {
    var val = fullnameInput.value.trim();
    if (val.length >= 3) {
      hostState.fullnameValid = true;
      setMsg(fullnameInput, "authme-host-fullname-msg", "success", "");
    } else {
      hostState.fullnameValid = false;
      setMsg(
        fullnameInput,
        "authme-host-fullname-msg",
        "error",
        val ? "Too short" : "",
      );
    }
    checkStep1();
  }

  function validateMobile() {
    var val = mobileInput.value.trim();
    hostState.mobileValid = false;
    checkStep1();

    if (!val) {
      setMsg(mobileInput, "authme-host-mobile-msg", "", "");
      return;
    }

    var countryCode = document.getElementById("authme-host-country-code").value;
    var selectedCountry = PhoneCore.findCountry(countryCode);
    if (!selectedCountry) return;

    // Use PhoneCore.validate()
    var validation = PhoneCore.validate(val, selectedCountry);

    if (!validation.valid) {
      setMsg(
        mobileInput,
        "authme-host-mobile-msg",
        "error",
        "Mobile number " +
          validation.error.toLowerCase() +
          " for " +
          selectedCountry.name,
      );
      return;
    }

    // Full mobile formatting without space to check against usermeta (e.g. +919000000000)
    var fullMobile = countryCode + val;

    clearTimeout(mobileDebounce);
    mobileDebounce = setTimeout(function () {
      window.authmeAjax(
        "authme_check_host_mobile",
        { mobile: fullMobile },
        function (res) {
          hostState.mobileValid = true;
          setMsg(mobileInput, "authme-host-mobile-msg", "success", res.message);
          checkStep1();
        },
        function (err) {
          hostState.mobileValid = false;
          setMsg(mobileInput, "authme-host-mobile-msg", "error", err.message);
          checkStep1();
        },
      );
    }, 500);
  }

  /* ── Step 2 File Upload ──────────────── */
  function checkStep2() {
    if (sendOtpBtn) {
      sendOtpBtn.disabled = !(
        hostState.files.aadharf &&
        hostState.files.aadharb &&
        hostState.files.pan
      );
    }
  }

  function bindFileUpload(inputId, stateKey) {
    var input = document.getElementById(inputId);
    if (!input) return;

    var area = input.closest(".authme-host-upload-area");
    var progressWrap = area.querySelector(".authme-host-progress-wrap");
    var progressBar = area.querySelector(".authme-host-progress-bar");
    var preview = area.querySelector(".authme-host-upload-preview");
    var img = preview.querySelector("img");
    var removeBtn = preview.querySelector(".authme-host-remove-file");
    var msgElId = inputId + "-msg";
    var msgEl = document.getElementById(msgElId);

    input.addEventListener("change", function (e) {
      var file = e.target.files[0];
      if (!file) return;

      // Validate Type
      if (file.type !== "image/jpeg" && file.type !== "image/jpg") {
        showError("Only JPEG images are allowed.");
        input.value = "";
        return;
      }

      // Validate Size (1MB = 1048576 bytes)
      if (file.size > 1048576) {
        showError("File exceeds 1MB limit.");
        input.value = "";
        return;
      }

      // Clear errors
      if (msgEl) {
        msgEl.textContent = "";
        msgEl.classList.remove("authme-host-msg-error");
      }

      // Start Upload
      uploadFile(file);
    });

    function uploadFile(file) {
      var formData = new FormData();
      formData.append("action", "authme_upload_host_document");
      formData.append("nonce", authme_ajax.nonce);
      formData.append("document", file);

      var xhr = new XMLHttpRequest();

      // Show progress
      progressWrap.style.display = "block";
      progressBar.style.width = "0%";
      input.disabled = true; // Prevent double upload

      xhr.upload.addEventListener("progress", function (e) {
        if (e.lengthComputable) {
          var percent = Math.round((e.loaded / e.total) * 100);
          progressBar.style.width = percent + "%";
        }
      });

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          progressWrap.style.display = "none";
          input.disabled = false;

          if (xhr.status === 200) {
            try {
              var res = JSON.parse(xhr.responseText);
              if (res.success) {
                var url = res.data.url;
                var attachId = res.data.attachment_id;
                hostState.files[stateKey] = {
                  url: url,
                  attachment_id: attachId,
                };
                img.src = url;
                preview.style.display = "block";
                checkStep2();
              } else {
                showError(res.data.message || "Upload failed.");
              }
            } catch (e) {
              showError("Server error. Please try again.");
            }
          } else {
            showError("Network error.");
          }
        }
      };

      xhr.open("POST", authme_ajax.ajax_url, true);
      xhr.send(formData);
    }

    removeBtn.addEventListener("click", function (e) {
      e.stopPropagation();

      var fileData = hostState.files[stateKey];
      if (fileData && fileData.attachment_id) {
        // Call server to delete the file
        var delData = new FormData();
        delData.append("action", "authme_delete_host_document");
        delData.append("nonce", authme_ajax.nonce);
        delData.append("attachment_id", fileData.attachment_id);

        fetch(authme_ajax.ajax_url, {
          method: "POST",
          body: delData,
        });
      }

      input.value = "";
      img.src = "";
      preview.style.display = "none";
      hostState.files[stateKey] = null;
      checkStep2();
    });

    function showError(msg) {
      if (msgEl) {
        msgEl.textContent = msg;
        msgEl.classList.add("authme-host-msg-error");
      }
      hostState.files[stateKey] = null;
      checkStep2();
    }
  }

  /* ── Step 3 OTP ──────────────────────── */
  function handleSendOtp() {
    if (sendOtpBtn.disabled) return;

    hostState.email = emailInput.value.trim();
    var tempBtnText = sendOtpBtn.textContent;
    sendOtpBtn.textContent = "Sending...";
    sendOtpBtn.disabled = true;

    var data = {
      email: hostState.email,
      purpose: "host_request",
    };

    window.authmeAjax(
      "authme_send_otp",
      data,
      function (res) {
        sendOtpBtn.textContent = tempBtnText;
        sendOtpBtn.disabled = false;
        if (typeof window.authmeToast === "function")
          window.authmeToast("success", res.message);

        goToStep(3);
        startOtpTimer(res.expiry || 60);

        // Focus first OTP box
        var boxes = document.querySelectorAll(".authme-host-otp-box");
        if (boxes.length > 0) boxes[0].focus();
      },
      function (err) {
        sendOtpBtn.textContent = tempBtnText;
        sendOtpBtn.disabled = false;
        if (typeof window.authmeToast === "function")
          window.authmeToast("error", err.message);
      },
    );
  }

  function bindOtpInputs() {
    var boxes = document.querySelectorAll(".authme-host-otp-box");
    boxes.forEach(function (box, index) {
      box.addEventListener("input", function () {
        this.classList.toggle(
          "authme-host-otp-filled",
          this.value.length === 1,
        );
        // Auto-advance
        if (this.value.length === 1 && index < boxes.length - 1) {
          boxes[index + 1].focus();
        }
      });

      box.addEventListener("keydown", function (e) {
        // Auto-backspace
        if (e.key === "Backspace" && !this.value && index > 0) {
          boxes[index - 1].focus();
          boxes[index - 1].value = "";
          boxes[index - 1].classList.remove("authme-host-otp-filled");
        }
      });

      box.addEventListener("paste", function (e) {
        e.preventDefault();
        var pasted = (e.clipboardData || window.clipboardData).getData("text");
        var numbers = pasted.replace(/[^0-9]/g, "").slice(0, 6);

        for (var i = 0; i < numbers.length; i++) {
          if (index + i < boxes.length) {
            boxes[index + i].value = numbers[i];
            boxes[index + i].classList.add("authme-host-otp-filled");
          }
        }

        if (index + numbers.length < boxes.length) {
          boxes[index + numbers.length].focus();
        } else {
          boxes[boxes.length - 1].focus();
        }
      });
    });
  }

  function startOtpTimer(seconds) {
    clearInterval(resendInterval);
    resendBtn.classList.add("authme-host-link-disabled");

    var remaining = seconds;
    resendBtn.innerHTML =
      'Resend in <b id="authme-host-otp-timer">' + remaining + "</b>s";

    resendInterval = setInterval(function () {
      remaining--;
      var timerNode = document.getElementById("authme-host-otp-timer");
      if (timerNode) timerNode.textContent = remaining;

      if (remaining <= 0) {
        clearInterval(resendInterval);
        resendBtn.classList.remove("authme-host-link-disabled");
        resendBtn.textContent = "Resend OTP";
      }
    }, 1000);
  }

  function handleResendOtp() {
    if (resendBtn.classList.contains("authme-host-link-disabled")) return;

    resendBtn.classList.add("authme-host-link-disabled");
    resendBtn.textContent = "Sending...";

    var data = {
      email: hostState.email,
      purpose: "host_request",
    };

    window.authmeAjax(
      "authme_send_otp",
      data,
      function (res) {
        if (typeof window.authmeToast === "function")
          window.authmeToast("success", "OTP resent successfully.");

        // Clear boxes
        var boxes = document.querySelectorAll(".authme-host-otp-box");
        boxes.forEach(function (b) {
          b.value = "";
          b.classList.remove("authme-host-otp-filled");
        });
        boxes[0].focus();

        startOtpTimer(res.expiry || 60);
      },
      function (err) {
        resendBtn.classList.remove("authme-host-link-disabled");
        resendBtn.textContent = "Resend OTP";
        if (typeof window.authmeToast === "function")
          window.authmeToast("error", err.message || "Failed to resend OTP.");
      },
    );
  }

  function handleVerifySubmit() {
    var boxes = document.querySelectorAll(".authme-host-otp-box");
    var code = "";
    boxes.forEach(function (b) {
      code += b.value;
    });

    if (code.length < 6) {
      if (typeof window.authmeToast === "function")
        window.authmeToast("error", "Please enter the 6-digit code.");
      return;
    }

    otpVerifyBtn.style.display = "none";
    loader.style.display = "flex";

    var verifyData = {
      email: hostState.email,
      otp_code: code,
      purpose: "host_request",
    };

    window.authmeAjax(
      "authme_verify_otp",
      verifyData,
      function (res) {
        // If verified successfully, immediately submit the full payload
        submitFinalData();
      },
      function (err) {
        otpVerifyBtn.style.display = "block";
        loader.style.display = "none";
        if (typeof window.authmeToast === "function")
          window.authmeToast("error", err.message || "Invalid OTP code.");
        boxes.forEach(function (b) {
          b.value = "";
          b.classList.remove("authme-host-otp-filled");
        });
        boxes[0].focus();
      },
    );
  }

  function submitFinalData() {
    var countryCode = document.getElementById("authme-host-country-code").value;

    var fullPayload = {
      username: usernameInput.value.trim(),
      fullname: fullnameInput.value.trim(),
      email: hostState.email,
      mobile: countryCode + mobileInput.value.trim(), // Stored without space to match db standard
      documents: hostState.files,
    };

    var finalData = {
      user_data: JSON.stringify(fullPayload),
    };

    window.authmeAjax(
      "authme_submit_host_request",
      finalData,
      function (res) {
        loader.style.display = "none";
        goToStep(4);
        startCloseTimer();
      },
      function (err) {
        otpVerifyBtn.style.display = "block";
        loader.style.display = "none";
        if (typeof window.authmeToast === "function")
          window.authmeToast(
            "error",
            err.message || "Failed to submit request.",
          );
      },
    );
  }

  function startCloseTimer() {
    var closeTimerEl = document.getElementById("authme-host-close-timer");
    if (!closeTimerEl) return;

    var remaining = 15;
    closeTimerEl.textContent = remaining;

    var closeInt = setInterval(function () {
      remaining--;
      closeTimerEl.textContent = remaining;
      if (remaining <= 0) {
        clearInterval(closeInt);
        closeHostModal();
      }
      if (!backdrop.classList.contains("authme-host-visible")) {
        clearInterval(closeInt); // user closed manually
      }
    }, 1000);
  }

  // Expose open method globally
  window.authmeOpenHostModal = openHostModal;
})(window);
