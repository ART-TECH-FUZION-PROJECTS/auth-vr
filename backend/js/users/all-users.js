/**
 * AuthMe Admin — All Users Javascript
 *
 * Handles fetching travelers via AJAX and updating the UI.
 *
 * @package AuthMe
 */

(function () {
  "use strict";

  var state = {
    page: 1,
    search: "",
    data: [],
  };

  /**
   * Fetch traveler data from the backend.
   */
  function fetchData() {
    const tableBody = document.getElementById("authme-users-table-body");
    if (tableBody)
      tableBody.innerHTML =
        '<tr><td colspan="5" style="text-align:center;padding:60px;"><div class="authme-user-v-spinner" style="margin: 0 auto 15px;"></div><p style="color:var(--authme-grey-light-text);font-weight:500;">Loading travelers list...</p></td></tr>';

    const formData = new FormData();
    formData.append("action", "authme_admin_get_all_users");
    formData.append("nonce", authme_admin.nonce);
    formData.append("page", state.page);
    formData.append("search", state.search);

    fetch(authme_admin.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((result) => {
        if (result.success) {
          state.data = result.data.items;
          renderTable(result.data.items);
          updatePagination(result.data.total, result.data.pages);
        } else {
          renderError(result.data.message || "Failed to fetch users");
        }
      })
      .catch((err) => {
        console.error(err);
        renderError("Network error occurred while fetching users");
      });
  }

  /**
   * Render table rows dynamically.
   */
  function renderTable(users) {
    const tableBody = document.getElementById("authme-users-table-body");
    if (!tableBody) return;

    if (users.length === 0) {
      tableBody.innerHTML =
        '<tr><td colspan="5" style="text-align:center;padding:60px;"><p style="color:var(--authme-grey-light-text);font-weight:500;">No travelers found.</p></td></tr>';
      return;
    }

    tableBody.innerHTML = "";
    users.forEach((user, index) => {
      const row = document.createElement("tr");
      row.className = "authme-users-table-body-row";
      row.style.animation = "authmeFadeIn 0.3s ease forwards";
      row.style.animationDelay = index * 0.05 + "s";

      row.innerHTML = `
                <td class="authme-users-table-body-cell">
                    <div style="font-weight:700;color:var(--authme-secondary);background:var(--authme-bg);width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:10px;">${(state.page - 1) * 10 + index + 1}</div>
                </td>
                <td class="authme-users-table-body-cell">
                    <div class="authme-users-user-info">
                        <span class="authme-users-user-fullname" style="font-weight:600;color:var(--authme-secondary);font-size:0.95rem;">${user.fullname}</span>
                        <span class="authme-users-user-email" style="font-size:0.8rem;color:var(--authme-grey-light-text);">${user.email}</span>
                    </div>
                </td>
                <td class="authme-users-table-body-cell">
                    <span style="font-size:0.85rem;color:var(--authme-secondary);font-weight:500;">${user.phone}</span>
                </td>
                <td class="authme-users-table-body-cell">
                    <span style="font-size:0.85rem;color:var(--authme-grey-light-text);">${user.date}</span>
                </td>
                <td class="authme-users-table-body-cell authme-users-col-actions">
                    <a href="?page=authme-view-user&id=${user.id}" class="authme-users-action-btn">
                        <svg style="width:14px;height:14px;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        Edit Profile
                    </a>
                </td>
            `;
      tableBody.appendChild(row);
    });
  }

  /**
   * Update pagination UI.
   */
  function updatePagination(total, totalPages) {
    const text = document.getElementById("authme-users-pagination-text");
    const start = (state.page - 1) * 10 + 1;
    const end = Math.min(start + state.data.length - 1, total);

    if (text) {
      text.textContent =
        total > 0 ? `Showing ${start}-${end} of ${total}` : "No users found";
    }

    const numbers = document.getElementById("authme-users-page-numbers");
    if (numbers) {
      numbers.innerHTML = `<button class="authme-users-page-btn authme-users-page-btn-active">${state.page}</button>`;
    }

    const prev = document.getElementById("authme-users-btn-prev");
    const next = document.getElementById("authme-users-btn-next");

    if (prev) {
      prev.disabled = state.page <= 1;
      prev.classList.toggle("authme-users-page-btn-disabled", state.page <= 1);
    }
    if (next) {
      next.disabled = state.page >= totalPages;
      next.classList.toggle(
        "authme-users-page-btn-disabled",
        state.page >= totalPages,
      );
    }
  }

  /**
   * Show error message in table.
   */
  function renderError(msg) {
    const tableBody = document.getElementById("authme-users-table-body");
    if (tableBody) {
      tableBody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:60px;color:var(--authme-error);font-weight:600;">${msg}</td></tr>`;
    }
  }

  /* ── Initialization ────────────────────── */

  document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("authme-users-container");
    if (!container) return;

    fetchData();

    const searchInput = document.getElementById("authme-users-search-input");
    const clearBtn = document.getElementById("authme-users-search-clear-btn");
    let debounceTimer;

    if (searchInput) {
      searchInput.addEventListener("input", (e) => {
        const val = e.target.value.trim();
        if (clearBtn) clearBtn.style.display = val.length > 0 ? "flex" : "none";

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
          state.search = val;
          state.page = 1;
          fetchData();
        }, 500);
      });

      searchInput.addEventListener("focus", () => {
        document
          .getElementById("authme-users-search-bar-wrapper")
          ?.classList.add("authme-users-search-focused");
      });
      searchInput.addEventListener("blur", () => {
        document
          .getElementById("authme-users-search-bar-wrapper")
          ?.classList.remove("authme-users-search-focused");
      });
    }

    if (clearBtn) {
      clearBtn.addEventListener("click", () => {
        if (searchInput) {
          searchInput.value = "";
          state.search = "";
          state.page = 1;
          clearBtn.style.display = "none";
          fetchData();
          searchInput.focus();
        }
      });
    }

    document
      .getElementById("authme-users-refresh-btn")
      ?.addEventListener("click", () => {
        fetchData();
        if (window.authmeToast)
          window.authmeToast("success", "User list refreshed successfully");
      });

    document
      .getElementById("authme-users-export-btn")
      ?.addEventListener("click", () => {
        const exportUrl = `${authme_admin.ajax_url}?action=authme_admin_export_csv&type=users&_wpnonce=${authme_admin.nonce}`;
        window.location.href = exportUrl;
      });

    document
      .getElementById("authme-users-btn-prev")
      ?.addEventListener("click", () => {
        if (state.page > 1) {
          state.page--;
          fetchData();
        }
      });

    document
      .getElementById("authme-users-btn-next")
      ?.addEventListener("click", () => {
        state.page++;
        fetchData();
      });
  });
})();
