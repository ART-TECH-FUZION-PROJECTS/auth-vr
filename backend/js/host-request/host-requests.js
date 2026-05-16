/**
 * AuthMe Admin — Host Requests Javascript
 *
 * Handles fetching host requests via AJAX and dynamically 
 * updating the UI according to the modern design.
 *
 * @package AuthMe
 */

(function () {
    'use strict';

    /* ── Global State ──────────────────────── */
    var amhState = {
        tab: 'pending',
        page: 1,
        searchQuery: '',
        data: [] // Latest current data response
    };

    var amhTabTitles = {
        pending: "Pending Applications",
        approved: "Approved Applications",
        rejected: "Rejected Applications"
    };

    var amhTabSubtitles = {
        pending: "Showing applications awaiting review",
        approved: "Showing approved host applications",
        rejected: "Showing rejected host applications"
    };

    /* ── Fetching Data ─────────────────────── */

    /**
     * Fetch host requests from the backend.
     */
    window.amhFetchData = function() {
        var tableBody = document.getElementById('amh-table-body-container');
        var mobileBody = document.getElementById('amh-mobile-body-container');
        
        if (tableBody) tableBody.innerHTML = '<tr><td colspan="5" class="amh-empty-state">Loading...</td></tr>';
        if (mobileBody) mobileBody.innerHTML = '<div class="amh-empty-state">Loading...</div>';

        var formData = new FormData();
        formData.append('action', 'authme_admin_get_host_requests');
        formData.append('nonce', authme_admin.nonce);
        formData.append('paged', amhState.page);
        formData.append('status', amhState.tab);

        // Note: The backend search currently might not be fully functional for `email` vs `fullname` search natively across JSON
        // However, if the backend supports `search`, we can append it. 
        // For now we will fetch the tab data and perform a frontend filter if searchQuery is active to match the HTML design logic.
        
        fetch(authme_admin.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
        .then(function(res) { return res.json(); })
        .then(function(result) {
            if (result.success) {
                amhState.data = result.data.items || [];
                amhUpdateCounts(result.data.counts);
                
                // If search is active, filter locally just like the HTML mockup
                var displayData = amhState.data;
                if (amhState.searchQuery.length >= 3) {
                    var s = amhState.searchQuery.toLowerCase();
                    displayData = displayData.filter(function(item) {
                        var e = (item.email || '').toLowerCase();
                        var p = (item.phone || '').toLowerCase();
                        var n = (item.fullname || '').toLowerCase();
                        return e.indexOf(s) > -1 || p.indexOf(s) > -1 || n.indexOf(s) > -1;
                    });
                }

                amhRenderTableRows(displayData);
                amhUpdatePagination(displayData.length, result.data.total, result.data.pages);
            } else {
                amhRenderError("Failed to fetch data.");
            }
        })
        .catch(function(e) {
            console.error(e);
            amhRenderError("Network error.");
        });
    };

    function amhUpdateCounts(counts) {
        if (!counts) return;
        var p = document.getElementById('amh-stat-pending-count');
        var a = document.getElementById('amh-stat-approved-count');
        var r = document.getElementById('amh-stat-rejected-count');
        
        if (p) p.textContent = counts.pending || 0;
        if (a) a.textContent = counts.approved || 0;
        if (r) r.textContent = counts.rejected || 0;
    }

    function amhRenderError(msg) {
        var tableBody = document.getElementById('amh-table-body-container');
        var mobileBody = document.getElementById('amh-mobile-body-container');
        
        if (tableBody) tableBody.innerHTML = '<tr><td colspan="5" class="amh-empty-state amh-error-state">' + msg + '</td></tr>';
        if (mobileBody) mobileBody.innerHTML = '<div class="amh-empty-state amh-error-state">' + msg + '</div>';
    }

    /* ── UI Rendering ──────────────────────── */

    function amhRenderTableRows(data) {
        var tableBody = document.getElementById('amh-table-body-container');
        var mobileBody = document.getElementById('amh-mobile-body-container');
        
        if (!tableBody || !mobileBody) return;

        tableBody.innerHTML = '';
        mobileBody.innerHTML = '';

        if (data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="amh-empty-state">No applications found.</td></tr>';
            mobileBody.innerHTML = '<div class="amh-empty-state">No applications found.</div>';
            return;
        }

        var amhStatusClass = '';
        var amhDotClass    = '';
        
        if (amhState.tab === 'pending') {
            amhStatusClass = 'amh-status-badge-pending';
            amhDotClass = 'amh-status-dot-pending';
        } else if (amhState.tab === 'approved') {
            amhStatusClass = 'amh-status-badge-approved';
            amhDotClass = 'amh-status-dot-approved';
        } else if (amhState.tab === 'rejected') {
            amhStatusClass = 'amh-status-badge-rejected';
            amhDotClass = 'amh-status-dot-rejected';
        }

        data.forEach(function (app, index) {
            var rawEmail = app.email || 'N/A';
            var rawPhone = app.phone || 'N/A';
            var statusLabel = amhState.tab.charAt(0).toUpperCase() + amhState.tab.slice(1);
            var serialNo = ((amhState.page - 1) * 10) + index + 1;
            
            // Build Desktop Row
            var row = document.createElement('tr');
            row.className = 'amh-table-body-row';
            row.style.animationDelay = (index * 0.05) + 's';
            row.innerHTML =
                '<td class="amh-table-body-cell"><div class="amh-cell-id-wrap">' + serialNo + '</div></td>' +
                '<td class="amh-table-body-cell">' +
                    '<div class="amh-applicant-info">' +
                        '<span class="amh-applicant-email">' + rawEmail + '</span>' +
                        '<span class="amh-applicant-phone">' +
                            '<svg class="amh-phone-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>' + 
                            rawPhone + 
                        '</span>' +
                    '</div>' +
                '</td>' +
                '<td class="amh-table-body-cell">' +
                    '<span class="amh-status-badge ' + amhStatusClass + '">' +
                        '<span class="amh-status-dot ' + amhDotClass + '"></span>' + statusLabel + 
                    '</span>' +
                '</td>' +
                '<td class="amh-table-body-cell">' +
                    '<span class="amh-date-cell">' +
                        '<svg class="amh-date-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>' + 
                        app.date + 
                    '</span>' +
                '</td>' +
                '<td class="amh-table-body-cell amh-actions-cell">' +
                    '<a href="?page=authme-view-form&id=' + app.id + '" class="amh-action-btn">' +
                        '<svg class="amh-action-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' +
                        'View Forms' +
                    '</a>' +
                '</td>';
            tableBody.appendChild(row);

            // Build Mobile Card
            var mCard = document.createElement('div');
            mCard.className = 'amh-t-datas';
            mCard.innerHTML = 
                '<div class="amh-t-data-entry"><div class="data-label">S.No</div><div class="data-entry">' + serialNo + '</div></div>' +
                '<div class="amh-t-data-entry"><div class="data-label">Email</div><div class="data-entry">' + rawEmail + '</div></div>' +
                '<div class="amh-t-data-entry"><div class="data-label">Mobile Number</div><div class="data-entry">' + rawPhone + '</div></div>' +
                '<div class="amh-t-data-entry"><div class="data-label">Date/Time</div><div class="data-entry">' + app.date + '</div></div>' +
                '<div class="amh-t-data-entry amh-t-data-action">' +
                    '<div class="data-status ' + amhState.tab + '">' + statusLabel + '</div>' +
                    '<a href="?page=authme-view-form&id=' + app.id + '" class="data-btn">' +
                        '<svg class="amh-action-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' +
                        'View' +
                    '</a>' +
                '</div>';
            mobileBody.appendChild(mCard);
        });
    }

    function amhUpdatePagination(visibleCount, totalCount, totalPages) {
        var paginationText = document.getElementById('amh-pagination-text');
        
        if (visibleCount > 0) {
            var start = ((amhState.page - 1) * 10) + 1; // Assuming 10 items per page limit from backend map
            var end = start + visibleCount - 1;
            paginationText.textContent = "Showing " + start + "-" + end + " of " + (amhState.searchQuery.length >= 3 ? visibleCount + ' (filtered)' : totalCount);
        } else {
            paginationText.textContent = "No results found";
        }

        // Extremely simple pagination behavior update for mock visuals
        var prevBtn = document.getElementById('amh-btn-prev');
        var nextBtn = document.getElementById('amh-btn-next');
        
        if (prevBtn) {
            if (amhState.page > 1) {
                prevBtn.disabled = false;
                prevBtn.classList.remove('amh-page-btn-disabled');
            } else {
                prevBtn.disabled = true;
                prevBtn.classList.add('amh-page-btn-disabled');
            }
        }
        
        if (nextBtn) {
            if (amhState.page < totalPages) {
                nextBtn.disabled = false;
                nextBtn.classList.remove('amh-page-btn-disabled');
            } else {
                nextBtn.disabled = true;
                nextBtn.classList.add('amh-page-btn-disabled');
            }
        }

        // Just render current page block
        var numbersBox = document.getElementById('amh-page-numbers');
        if (numbersBox) {
            numbersBox.innerHTML = '<button class="amh-page-btn amh-page-btn-active">' + amhState.page + '</button>';
        }
    }

    /* ── Event Handlers & Exports ──────────── */

    window.amhSwitchTab = function(tabName, clickedCard) {
        amhState.tab = tabName;
        amhState.page = 1;

        var allStatCards = document.querySelectorAll('.amh-stat-card');
        allStatCards.forEach(function (card) {
            card.classList.remove('amh-stat-active');
        });
        if (clickedCard) {
            clickedCard.classList.add('amh-stat-active');
        }

        var titleEl = document.getElementById('amh-table-section-title');
        var subtitleEl = document.getElementById('amh-table-section-subtitle');
        if (titleEl) titleEl.textContent = amhTabTitles[tabName] || 'Applications';
        if (subtitleEl) subtitleEl.textContent = amhTabSubtitles[tabName] || '';

        amhClearSearch(true); // Don't fetch yet, let the parent trigger fetch
        amhFetchData();
    };

    window.amhClearSearch = function(skipFetch) {
        var input = document.getElementById('amh-search-input');
        if (input) input.value = '';
        amhState.searchQuery = '';
        amhUpdateSearchClearBtn();
        amhUpdateSearchFocus();

        if (!skipFetch) {
            // Re-render local data without fetching if we already have it
            if (amhState.data) {
                amhRenderTableRows(amhState.data);
                // Fake a pagination reset text for the local filtered data reset
                var paginationText = document.getElementById('amh-pagination-text');
                if (paginationText && amhState.data.length > 0) {
                    var cnt = amhState.data.length;
                    paginationText.textContent = "Showing 1-" + cnt + " of " + cnt; 
                }
            } else {
                amhFetchData();
            }
            if (input) input.focus();
        }
    };

    window.amhExecuteRefresh = function() {
        amhClearSearch(true);
        amhState.page = 1;
        amhFetchData();
        if (window.authmeToast) {
            window.authmeToast('success', 'Refreshed successfully');
        }
    };



    function amhUpdateSearchClearBtn() {
        var input = document.getElementById('amh-search-input');
        var clearBtn = document.getElementById('amh-search-clear-btn');
        if (!input || !clearBtn) return;
        
        if (input.value.length > 0) {
            clearBtn.classList.add('amh-search-clear-visible');
        } else {
            clearBtn.classList.remove('amh-search-clear-visible');
        }
    }

    function amhUpdateSearchFocus() {
        var wrapper = document.getElementById('amh-search-bar-wrapper');
        if (wrapper) wrapper.classList.remove('amh-search-focused');
    }

    /* ── Listeners ─────────────────────────── */

    document.addEventListener('DOMContentLoaded', function () {
        if (!document.getElementById('amh-dashboard-container')) return;

        amhFetchData(); // First load

        var searchInput = document.getElementById('amh-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                amhState.searchQuery = e.target.value.trim();
                amhUpdateSearchClearBtn();

                if (amhState.searchQuery.length === 0 || amhState.searchQuery.length >= 3) {
                    // We trigger the local frontend filter rendering as built in backend data response
                    var displayData = amhState.data;
                    if (amhState.searchQuery.length >= 3) {
                        var s = amhState.searchQuery.toLowerCase();
                        displayData = displayData.filter(function(item) {
                            var email = (item.email || '').toLowerCase();
                            var phone = (item.phone || '').toLowerCase();
                            var name  = (item.fullname || '').toLowerCase();
                            return email.indexOf(s) > -1 || phone.indexOf(s) > -1 || name.indexOf(s) > -1;
                        });
                    }
                    amhRenderTableRows(displayData);
                    
                    var paginationText = document.getElementById('amh-pagination-text');
                    if (displayData.length > 0) {
                        paginationText.textContent = "Showing filtering results (" + displayData.length + ")";
                    } else {
                        paginationText.textContent = "No results found";
                    }
                }
            });

            searchInput.addEventListener('focus', function () {
                var wrapper = document.getElementById('amh-search-bar-wrapper');
                if (wrapper) wrapper.classList.add('amh-search-focused');
            });

            searchInput.addEventListener('blur', function () {
                amhUpdateSearchFocus();
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                amhClearSearch();
            }
        });

        // Export CSV Handler
        var exportBtn = document.getElementById('amh-export-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', function () {
                var exportUrl = authme_admin.ajax_url + '?action=authme_admin_export_csv&type=hosts&_wpnonce=' + authme_admin.nonce;
                window.location.href = exportUrl;
            });
        }

        // Pagination hooks
        var prevBtn = document.getElementById('amh-btn-prev');
        var nextBtn = document.getElementById('amh-btn-next');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                if (amhState.page > 1) {
                    amhState.page--;
                    amhFetchData();
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                if (!this.disabled) {
                    amhState.page++;
                    amhFetchData();
                }
            });
        }
    });

})();
