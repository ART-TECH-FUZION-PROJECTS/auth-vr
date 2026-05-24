/**
 * AuthMe Admin — View Form JavaScript
 *
 * Handles fetching, populating, and updating status
 * for the decoupled single host request View Form.
 * Matches `view-form.html` design using `.amv-` classes.
 *
 * @package AuthMe
 */

(function () {
  "use strict";

  /* ── SVG Icons ───────────────────────── */
  var iconDoc =
    '<svg class="amv-icon-doc" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
  var iconExtLink =
    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 4px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>';

  var currentHostId = null;

  /* ── DOM Init ────────────────────────── */
  document.addEventListener("DOMContentLoaded", function () {
    var container = document.getElementById("amv-view-container");
    if (!container) return;

    currentHostId = parseInt(container.getAttribute("data-host-id"), 10);

    if (currentHostId) {
      loadApplicationData(currentHostId);
    } else {
      showError("Invalid Application ID. Please go back and try again.");
    }
  });

  /* ── Navigation ──────────────────────── */
  window.amvGoBack = function () {
    // Find if we have a referrer inside the plugin, or just navigate to host requests home
    if (
      document.referrer &&
      document.referrer.includes("page=authme-host-requests")
    ) {
      window.history.back();
    } else {
      window.location.href = "?page=authme-host-requests";
    }
  };

  /* ── Fetch Data ──────────────────────── */
  function loadApplicationData(id) {
    var formData = new FormData();
    formData.append("action", "authme_admin_get_single_host");
    formData.append("nonce", authme_admin.nonce);
    formData.append("id", id);

    fetch(authme_admin.ajax_url, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (result) {
        document.getElementById("amv-loading-state").style.display = "none";

        if (result.success) {
          populateViewForm(result.data);
        } else {
          showError(result.data.message || "Failed to load data.");
        }
      })
      .catch(function () {
        document.getElementById("amv-loading-state").style.display = "none";
        showError("Network error. Please try again.");
      });
  }

  function showError(msg) {
    var errEl = document.getElementById("amv-error-state");
    errEl.innerText = msg;
    errEl.style.display = "block";
  }

  /* ── Populate UI ─────────────────────── */
  function populateViewForm(data) {
    var basicData = data.userData || {};
    basicData.status = data.status; // Merge status from root object
    var dbId = data.raw_id;

    // Names & Usernames
    var name = basicData.fullname || basicData.username || "N/A";
    document.getElementById("amv-view-name").textContent = name;
    document.getElementById("amv-view-username").textContent =
      basicData.username ? "@" + basicData.username : "N/A";

    // Initials Avatar
    var initial = name.charAt(0).toUpperCase();
    document.getElementById("amv-view-initials").textContent = initial || "--";

    // Details
    document.getElementById("amv-view-id").textContent = dbId;
    document.getElementById("amv-view-email-detail").textContent =
      basicData.email || "N/A";
    document.getElementById("amv-view-phone").textContent =
      basicData.mobile || basicData.phone || "N/A";

    // Documents
    var docContainer = document.getElementById("amv-view-docs-list");
    docContainer.innerHTML = "";
    // 1. Try to find the documents object under 'documents' or 'files'
    var docsSource = basicData.documents || basicData.files || {};
    var hasDocs = false;

    /**
     * Robust lookup: Check for variations like 'aadharf', 'front', 'aadhar_front'
     */
    function getDocData(keys) {
      for (var i = 0; i < keys.length; i++) {
        if (docsSource[keys[i]]) return docsSource[keys[i]];
      }
      return null;
    }

    var docConfigs = [
      {
        keys: ["aadharf", "front", "aadhar_front"],
        label: "Aadhar Card (Front)",
      },
      { keys: ["aadharb", "back", "aadhar_back"], label: "Aadhar Card (Back)" },
      { keys: ["pan", "hand", "pan_card"], label: "PAN Card" },
    ];

    docConfigs.forEach(function (config) {
      var rawData = getDocData(config.keys);
      if (rawData) {
        hasDocs = true;

        // Handle both simple URL string and new object format { url, attachment_id }
        var url = typeof rawData === "object" ? rawData.url : rawData;

        if (url) {
          var docHtml =
            '<div class="amh-doc-item">' +
            '<div class="amh-doc-left">' +
            '<svg class="amh-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>' +
            '<span class="amh-doc-name">' +
            config.label +
            "</span>" +
            "</div>" +
            '<button class="amh-btn-view" onclick="amvViewDocument(\'' +
            url +
            '\')" aria-label="View Document">' +
            '<svg class="amh-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' +
            "</button>" +
            "</div>";

          docContainer.insertAdjacentHTML("beforeend", docHtml);
        }
      }
    });

    if (!hasDocs) {
      docContainer.innerHTML =
        '<p style="font-size: 0.875rem; color: var(--authme-grey-light-text); margin:0;">No documents uploaded.</p>';
    }

    // Status Badge styling
    var badge = document.getElementById("amv-view-status");
    badge.className = "amh-status-badge"; // Reset entirely
    badge.textContent =
      basicData.status.charAt(0).toUpperCase() + basicData.status.slice(1);

    if (basicData.status === "pending") {
      badge.style.backgroundColor = "var(--authme-light-yellow-bg)";
      badge.style.color = "var(--authme-warning)";
    } else if (basicData.status === "approved") {
      badge.style.backgroundColor = "var(--authme-light-green-bg)";
      badge.style.color = "var(--authme-success)";
    } else if (basicData.status === "rejected") {
      badge.style.backgroundColor = "var(--authme-light-red-bg)";
      badge.style.color = "var(--authme-error)";
    }

    // Action Buttons
    var actionWrap = document.getElementById("amv-view-actions");
    actionWrap.innerHTML = ""; // reset

    if (basicData.status === "pending") {
      actionWrap.innerHTML =
        '<button class="amh-btn-update" style="background-color: var(--authme-error); flex:1;" onclick="amvUpdateHostStatus(' +
        dbId +
        ", 'rejected')\">" +
        '<span class="amh-btn-text">Reject</span>' +
        "</button>" +
        '<button class="amh-btn-update" style="background-color: var(--authme-success); flex:1;" onclick="amvUpdateHostStatus(' +
        dbId +
        ", 'approved')\">" +
        '<span class="amh-btn-text">Approve</span>' +
        "</button>";
      actionWrap.style.gap = "1rem";
      actionWrap.style.flexDirection = "row";
    } else {
      var actionText =
        basicData.status === "approved"
          ? "Application Approved"
          : "Application Rejected";
      actionWrap.innerHTML =
        '<div style="font-weight: 500; color: var(--authme-grey-light-text);">' +
        actionText +
        "</div>";
    }

    // Show content area
    document.getElementById("amv-content-area").style.display = "flex";
  }

  /* ── Action Handler ──────────────────── */
  window.amvUpdateHostStatus = function (id, newStatus) {
    var actionLabel = newStatus === "approved" ? "Approve" : "Reject";

    if (window.authmeShowAdminConfirm) {
      window.authmeShowAdminConfirm(
        actionLabel + " Application",
        "Are you sure you want to " +
          actionLabel.toLowerCase() +
          " this application?",
        function () {
          executeStatusUpdate(id, newStatus);
        },
      );
    } else {
      if (
        confirm(
          "Are you sure you want to " +
            actionLabel.toLowerCase() +
            " this application?",
        )
      ) {
        executeStatusUpdate(id, newStatus);
      }
    }
  };

  function executeStatusUpdate(id, newStatus) {
    // Disable buttons temporarily
    var actionsWrap = document.getElementById("amv-view-actions");
    var originalHtml = actionsWrap.innerHTML;
    actionsWrap.innerHTML =
      '<div class="amv-action-completed">Processing...</div>';

    var formData = new FormData();
    formData.append("action", "authme_admin_process_host");
    formData.append("nonce", authme_admin.nonce);
    formData.append("id", id);
    formData.append("new_status", newStatus);

    fetch(authme_admin.ajax_url, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (result) {
        if (result.success) {
          if (window.authmeToast) {
            window.authmeToast(
              "success",
              "Application " + newStatus + " successfully.",
            );
          }
          // Reload the same ID to fetch updated data nicely
          document.getElementById("amv-content-area").style.display = "none";
          document.getElementById("amv-loading-state").style.display = "block";
          loadApplicationData(id);
        } else {
          actionsWrap.innerHTML = originalHtml; // Revert
          if (window.authmeToast) {
            window.authmeToast(
              "error",
              result.data.message || "Operation failed.",
            );
          }
        }
      })
      .catch(function () {
        actionsWrap.innerHTML = originalHtml; // Revert
        if (window.authmeToast) {
          window.authmeToast("error", "Network Error. Please try again.");
        }
      });
  }
})();

// --- Modal Document Viewer --- //
window.amvViewDocument = function (url) {
  var modal = document.getElementById("amh-doc-modal");
  var img = document.getElementById("amh-modal-image");
  if (modal && img) {
    img.src = url;
    modal.style.display = "flex";
  } else {
    window.open(url, "_blank");
  }
};
