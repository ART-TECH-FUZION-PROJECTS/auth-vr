/**
 * AuthMe Google Auth Frontend Logic
 *
 * Handles the Google OAuth2 flow using Google Identity Services.
 *
 * @package AuthMe
 */

document.addEventListener("DOMContentLoaded", function () {
  const googleBtns = document.querySelectorAll(".authme-google-btn");
  if (googleBtns.length === 0) return;

  const clientId =
    typeof authme_google_settings !== "undefined"
      ? authme_google_settings.client_id
      : "";
  let client;

  if (clientId) {
    // Load Google Identity Services SDK
    const script = document.createElement("script");
    script.src = "https://accounts.google.com/gsi/client";
    script.async = true;
    script.defer = true;
    script.onload = () => {
      client = google.accounts.oauth2.initTokenClient({
        client_id: clientId,
        scope:
          "openid profile email https://www.googleapis.com/auth/user.phonenumbers.read",
        callback: (response) => {
          if (response.access_token) {
            handleGoogleCallback(response.access_token);
          }
        },
      });
    };
    document.head.appendChild(script);
  }

  googleBtns.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      if (!clientId) {
        if (typeof window.authmeToast === "function") {
          window.authmeToast("error", "no client id found");
        }
        console.error("AuthMe Google Auth: Client ID is missing.");
        return;
      }

      // No e.preventDefault() here, just standard click handling
      if (client) {
        window.authmeActiveGoogleBtn = btn;
        client.requestAccessToken();
      } else {
        console.error("Google SDK not loaded yet.");
      }
    });
  });

  /**
   * Send the authorization code to the backend
   */
  function handleGoogleCallback(token) {
    // Show loading state on button
    const activeBtn =
      window.authmeActiveGoogleBtn ||
      document.querySelector(".authme-google-btn");
    const originalText = activeBtn.innerHTML;
    activeBtn.innerHTML =
      '<span class="authme-loader-small"></span> Authenticating...';
    activeBtn.disabled = true;

    const formData = new FormData();
    formData.append("action", "authme_google_auth_callback");
    formData.append("nonce", authme_ajax.nonce);
    formData.append("token", token);

    fetch(authme_ajax.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          if (typeof window.authmeToast === "function") {
            window.authmeToast(
              "success",
              data.data.message || "Authenticated successfully!",
            );
          }
          // Redirect or reload based on success
          setTimeout(() => {
            if (data.data.redirect_url) {
              window.location.href = data.data.redirect_url;
            } else {
              window.location.reload();
            }
          }, 1500);
        } else {
          if (typeof window.authmeToast === "function") {
            window.authmeToast(
              "error",
              data.data.message || "Authentication failed.",
            );
          }
          activeBtn.innerHTML = originalText;
          activeBtn.disabled = false;
        }
      })
      .catch((error) => {
        console.error("AJAX Error:", error);
        if (typeof window.authmeToast === "function") {
          window.authmeToast(
            "error",
            "An unexpected error occurred. Please try again.",
          );
        }
        activeBtn.innerHTML = originalText;
        activeBtn.disabled = false;
      });
  }
});
