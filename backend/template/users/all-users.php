<?php
/**
 * AuthMe Admin — All Travelers Page
 *
 * @package AuthMe
 */

if (! defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h2 class="authme-admin-notice-placeholder"></h2>

    <div class="authme-global-plugin-wrapper">
        <div class="authme-users-main-container" id="authme-users-container">
            <div class="authme-users-header-section">
                <div class="authme-users-header-top">
                    <div class="authme-users-header-left">
                        <div class="authme-users-header-icon-box">
                            <svg class="authme-users-header-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="authme-users-header-text-group">
                            <h1 class="authme-users-page-title">All Travelers</h1>
                            <p class="authme-users-page-subtitle">Manage and view all registered travelers</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="authme-users-search-section">
                <div class="authme-users-search-bar-wrapper" id="authme-users-search-bar-wrapper">
                    <span class="authme-users-search-icon">
                        <svg class="authme-users-search-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input type="text" class="authme-users-search-input" placeholder="Search by name, email, or username..." id="authme-users-search-input" autocomplete="off">
                    <button class="authme-users-search-clear-btn" id="authme-users-search-clear-btn" style="display:none;" aria-label="Clear Search">
                        <svg class="authme-users-search-clear-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="authme-users-table-section">
                <div class="authme-users-table-header-bar">
                    <div class="authme-users-table-title-group">
                        <span class="authme-users-table-section-title">Travelers List</span>
                        <span class="authme-users-table-section-subtitle">Showing all accounts with traveler role</span>
                    </div>
                    <div class="authme-users-table-actions-group">
                        <button class="authme-users-filter-btn" id="authme-users-export-btn" >
                            <svg class="authme-users-filter-btn-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Export CSV
                        </button>
                        <button class="authme-users-filter-btn" id="authme-users-refresh-btn">
                            <svg class="authme-users-filter-btn-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>

                <div class="authme-users-table-scroll">
                    <table class="authme-users-data-table">
                        <thead class="authme-users-table-header">
                            <tr class="authme-users-table-header-row">
                                <th class="authme-users-table-header-cell authme-users-col-id">ID</th>
                                <th class="authme-users-table-header-cell authme-users-col-info">User Info</th>
                                <th class="authme-users-table-header-cell authme-users-col-phone">Mobile</th>
                                <th class="authme-users-table-header-cell authme-users-col-date">Joined Date</th>
                                <th class="authme-users-table-header-cell authme-users-col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="authme-users-table-body" id="authme-users-table-body">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="authme-users-pagination-bar">
                    <span class="authme-users-pagination-text" id="authme-users-pagination-text">Showing 0-0 of 0</span>
                    <div class="authme-users-pagination-controls" id="authme-users-pagination-controls">
                        <button class="authme-users-page-btn authme-users-page-btn-disabled" disabled id="authme-users-btn-prev">
                            <svg class="authme-users-page-btn-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        <div id="authme-users-page-numbers">
                            <!-- Page numbers -->
                        </div>
                        <button class="authme-users-page-btn authme-users-page-btn-disabled" disabled id="authme-users-btn-next">
                            <svg class="authme-users-page-btn-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
