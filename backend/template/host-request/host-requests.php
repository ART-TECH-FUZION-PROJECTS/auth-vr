<?php

/**
 * AuthMe Admin — Host Requests Page
 *
 * @package AuthMe
 */

if (! defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <!-- This hidden h2 and the empty notice container catch WordPress admin notices before they get moved into our custom header. -->
    <h2 class="authme-admin-notice-placeholder"></h2>

    <div class="authme-global-plugin-wrapper">
        <div class="amh-main-container" id="amh-dashboard-container">
            <div class="amh-header-section">
                <div class="amh-header-top">
                    <div class="amh-header-left">
                        <div class="amh-header-icon-box">
                            <svg class="amh-header-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="amh-header-text-group">
                            <h1 class="amh-page-title">Host Applications</h1>
                            <p class="amh-page-subtitle">Manage and review incoming host applications</p>
                        </div>
                    </div>
                </div>

                <div class="amh-stats-row" id="amh-stats-container">
                    <div class="amh-stat-card amh-stat-pending amh-stat-active" data-amh-stat-tab="pending" onclick="amhSwitchTab('pending', this)">
                        <div class="amh-stat-icon-wrap amh-stat-icon-pending">
                            <svg class="amh-stat-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="amh-stat-info">
                            <span class="amh-stat-label">Pending</span>
                            <span class="amh-stat-value" id="amh-stat-pending-count">-</span>
                        </div>
                    </div>

                    <div class="amh-stat-card amh-stat-approved" data-amh-stat-tab="approved" onclick="amhSwitchTab('approved', this)">
                        <div class="amh-stat-icon-wrap amh-stat-icon-approved">
                            <svg class="amh-stat-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div class="amh-stat-info">
                            <span class="amh-stat-label">Approved</span>
                            <span class="amh-stat-value" id="amh-stat-approved-count">-</span>
                        </div>
                    </div>

                    <div class="amh-stat-card amh-stat-rejected" data-amh-stat-tab="rejected" onclick="amhSwitchTab('rejected', this)">
                        <div class="amh-stat-icon-wrap amh-stat-icon-rejected">
                            <svg class="amh-stat-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <div class="amh-stat-info">
                            <span class="amh-stat-label">Rejected</span>
                            <span class="amh-stat-value" id="amh-stat-rejected-count">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="amh-search-section">
                <div class="amh-search-bar-wrapper" id="amh-search-bar-wrapper">
                    <span class="amh-search-icon">
                        <svg class="amh-search-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input type="text" class="amh-search-input" placeholder="Search by name, email, or phone (min 3 chars)..." id="amh-search-input" autocomplete="off">
                    <button class="amh-search-clear-btn" id="amh-search-clear-btn" onclick="amhClearSearch()" aria-label="Clear Search">
                        <svg class="amh-search-clear-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="amh-table-section">
                <div class="amh-table-header-bar">
                    <div class="amh-table-title-group">
                        <span class="amh-table-section-title" id="amh-table-section-title">Pending Applications</span>
                        <span class="amh-table-section-subtitle" id="amh-table-section-subtitle">Showing applications awaiting review</span>
                    </div>
                    <div class="amh-table-actions-group">
                        <button class="amh-filter-btn" id="amh-export-btn">
                            <svg class="amh-filter-btn-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Export CSV
                        </button>
                        <button class="amh-filter-btn" onclick="amhExecuteRefresh()">
                            <svg class="amh-filter-btn-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>

                <div class="amh-table-scroll">
                    <table class="amh-data-table">
                        <thead class="amh-table-header">
                            <tr class="amh-table-header-row">
                                <th class="amh-table-header-cell amh-col-id">ID</th>
                                <th class="amh-table-header-cell amh-col-applicant">Applicant Info</th>
                                <th class="amh-table-header-cell amh-col-status">Status</th>
                                <th class="amh-table-header-cell amh-col-date">Date Submitted</th>
                                <th class="amh-table-header-cell amh-col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="amh-table-body" id="amh-table-body-container">
                            <!-- Dynamic Rows populated by JS -->
                        </tbody>
                    </table>

                    <div class="amh-table-mobile" id="amh-mobile-body-container">
                        <!-- Mobile Cards populated by JS -->
                    </div>
                </div>

                <!-- Pagination -->
                <div class="amh-pagination-bar">
                    <span class="amh-pagination-text" id="amh-pagination-text">Showing 0-0 of 0</span>
                    <div class="amh-pagination-controls" id="amh-pagination-controls">
                        <button class="amh-page-btn amh-page-btn-disabled" disabled id="amh-btn-prev">
                            <svg class="amh-page-btn-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        <div id="amh-page-numbers">
                            <button class="amh-page-btn amh-page-btn-active">1</button>
                        </div>
                        <button class="amh-page-btn amh-page-btn-disabled" disabled id="amh-btn-next">
                            <svg class="amh-page-btn-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>