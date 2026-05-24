/**
 * AuthMe Admin — Database Management JavaScript
 *
 * Handles DB status checking, table creation via AJAX,
 * and renders the status UI matching the database.html design.
 *
 * Uses AuthMe global toaster.
 *
 * @package AuthMe
 */
(function () {
  "use strict";

  /* ── SVG Icons ───────────────────────── */
  var iconCheck =
    '<svg class="am-d-status-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
  var iconX =
    '<svg class="am-d-status-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

  /* ── Status Table Renderer ───────────── */

  /**
   * Render a single table card.
   */
  function renderTableCard(tableName, tableData, cardId) {
    var exists = tableData && tableData.exists;

    var html = '<article class="am-d-card" id="am-d-card-' + cardId + '">';

    // Header
    html +=
      '<div class="am-d-card-header" id="am-d-card-header-' + cardId + '">';
    html += '<div class="am-d-card-title" id="am-d-card-title-' + cardId + '">';
    html += tableData ? tableData.name : tableName;
    html += '<span class="am-d-badge">Table</span></div>';

    if (exists) {
      html += '<div class="am-d-status primary">' + iconCheck + " Exists</div>";
    } else {
      html += '<div class="am-d-status error">' + iconX + " Missing</div>";
    }
    html += "</div>"; // close header

    // Body
    html += '<div class="am-d-card-body" id="am-d-card-body-' + cardId + '">';
    if (exists && tableData.columns) {
      var cols = tableData.columns;
      for (var col in cols) {
        if (cols.hasOwnProperty(col)) {
          var colOk = cols[col];
          html += '<div class="am-d-field-row">';
          html += '<div class="am-d-field-info">';
          html += '<span class="am-d-field-arrow">↳</span>';
          html += '<span class="am-d-field-name">' + col + "</span>";
          html += "</div>";

          if (colOk) {
            html += '<div class="am-d-status">' + iconCheck + " OK</div>";
          } else {
            html +=
              '<div class="am-d-status error">' + iconX + " Missing</div>";
          }
          html += "</div>";
        }
      }
    } else if (!exists) {
      html +=
        '<div class="am-d-loading-state" style="padding: 20px;">Table missing. Cannot check columns.</div>';
    }
    html += "</div>"; // close body

    html += "</article>";
    return html;
  }

  /**
   * Render the database status data into HTML cards.
   *
   * @param {object} data - Status data from AJAX response.
   */
  function renderStatusUI(data) {
    var container = document.getElementById("am-d-status-container");
    var footerNote = document.getElementById("am-d-footer-note");
    if (!container) return;

    var html = "";

    // Table 1: OTP
    html += renderTableCard("wp_authme_otp_storage", data.otp_table, "otp");

    // Table 2: Host Request
    html += renderTableCard("wp_host_request", data.host_table, "host");

    container.innerHTML = html;

    // Footer note updates
    if (footerNote) {
      if (data.all_good) {
        footerNote.innerHTML =
          "✅ All tables & columns validated • AuthMe schema integrity";
        footerNote.style.color = "var(--authme-success)";
      } else {
        footerNote.innerHTML =
          "⚠️ Schema issues detected • Click Create / Update Tables to resolve";
        footerNote.style.color = "var(--authme-error)";
      }
    }
  }

  /* ── AJAX: Fetch DB Status ───────────── */

  function fetchDbStatus(isRefresh) {
    var container = document.getElementById("am-d-status-container");
    if (!container) return;

    if (!isRefresh) {
      container.innerHTML =
        '<div class="am-d-loading-state">Checking database status…</div>';
    }

    var formData = new FormData();
    formData.append("action", "authme_admin_check_db");
    formData.append("nonce", authme_admin.nonce);

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
          renderStatusUI(result.data);
          if (isRefresh && window.authmeShowToaster) {
            window.authmeShowToaster("Refresh", "Refresh successfully");
          }
        } else {
          container.innerHTML =
            '<div class="am-d-loading-state" style="color:var(--authme-error)">Failed to check database status.</div>';
        }
      })
      .catch(function () {
        container.innerHTML =
          '<div class="am-d-loading-state" style="color:var(--authme-error)">Network error. Please try again.</div>';
      });
  }

  /* ── AJAX: Create / Update Tables ────── */

  function createTables() {
    var btn = document.getElementById("am-d-btn-update");
    if (!btn) return;

    btn.disabled = true;
    var originalContent = btn.innerHTML;
    btn.innerHTML = "Creating…";

    var formData = new FormData();
    formData.append("action", "authme_admin_create_tables");
    formData.append("nonce", authme_admin.nonce);

    fetch(authme_admin.ajax_url, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (result) {
        btn.disabled = false;
        btn.innerHTML = originalContent;

        if (result.success) {
          if (window.authmeToast) {
            var msg = result.data.message || "Updated successfully";
            // The backend sends "Tables created successfully." or "Tables updated successfully."
            window.authmeToast("success", msg);
          }
          fetchDbStatus(false); // Silent refresh UI after success
        } else {
          if (window.authmeToast) {
            window.authmeToast(
              "error",
              result.data.message || "An error occurred.",
            );
          }
        }
      })
      .catch(function () {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        if (window.authmeToast) {
          window.authmeToast("error", "Network Error. Please try again.");
        }
      });
  }

  /* ── Init on DOM Ready ──────────────── */

  document.addEventListener("DOMContentLoaded", function () {
    // Only run on the database page
    if (!document.getElementById("am-d-dashboard")) return;

    // Auto-check status on load without trigger toaster msg
    fetchDbStatus(false);

    // Update btn
    var createBtn = document.getElementById("am-d-btn-update");
    if (createBtn) {
      createBtn.addEventListener("click", createTables);
    }

    // Refresh btn
    var refreshBtn = document.getElementById("am-d-btn-refresh");
    if (refreshBtn) {
      refreshBtn.addEventListener("click", function () {
        fetchDbStatus(true);
      });
    }
  });
})();
