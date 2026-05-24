<?php

/**
 * AuthMe Become a Host Modal
 *
 * This modal allows users to apply for host status.
 * Contains 4 steps: Personal Info, Document Upload, OTP Verification, Success.
 *
 * @package AuthMe
 */

if (! defined('ABSPATH')) {
    exit;
}
?>

<div class="authme-global-plugin-wrapper">
    <!-- Host Request Overlay Backdrop -->
    <div id="authme-host-backdrop" class="authme-host-backdrop" style="display:none;">

        <div id="authme-host-container" class="authme-host-container">

            <!-- Close Button -->
            <button type="button" id="authme-host-close" class="authme-host-close" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <!-- Dynamic Step Indicator -->
            <div id="authme-host-steps-tracker" class="authme-host-steps-tracker">
                <div class="host-step-dot active" data-step="1"></div>
                <div class="host-step-line"></div>
                <div class="host-step-dot" data-step="2"></div>
                <div class="host-step-line"></div>
                <div class="host-step-dot" data-step="3"></div>
            </div>

            <!-- ==========================================
                 STEP 1: Personal Information 
                 ========================================== -->
            <div id="authme-host-step-1" class="authme-host-screen authme-host-screen-active">
                <h2 class="authme-host-title">Become a Host</h2>
                <p class="authme-host-subtitle">Step 1: Personal Information</p>
                <p class="authme-host-subtitle authme-host-subtitle-2">(Keep your Aadhar card & Pan card ready to upload)</p>
                <form id="authme-host-info-form" class="authme-host-form" autocomplete="off" novalidate>

                    <div class="authme-host-input-group">
                        <input type="text" id="authme-host-username" class="authme-host-input" placeholder="Username" required>
                        <span id="authme-host-username-msg" class="authme-host-field-msg"></span>
                    </div>

                    <div class="authme-host-input-group">
                        <input type="text" id="authme-host-fullname" class="authme-host-input" placeholder="Full Name" required>
                        <span id="authme-host-fullname-msg" class="authme-host-field-msg"></span>
                    </div>

                    <div class="authme-host-input-group">
                        <input type="email" id="authme-host-email" class="authme-host-input" placeholder="Email Address" required>
                        <span id="authme-host-email-msg" class="authme-host-field-msg"></span>
                    </div>

                    <div class="authme-host-input-group">
                        <div class="authme-host-mobile-wrapper">
                            <div class="authme-custom-dropdown" id="authme-host-country-dropdown">
                                <button type="button" class="authme-dropdown-trigger" id="authme-host-country-trigger">
                                    <span class="authme-selected-flag">🌐</span>
                                    <span class="authme-selected-code">Code</span>
                                    <svg class="authme-dropdown-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 9l6 6 6-6" />
                                    </svg>
                                </button>
                                <div class="authme-dropdown-menu" id="authme-host-country-menu" style="display:none;">
                                    <div class="authme-dropdown-search">
                                        <input type="text" placeholder="Search country..." id="authme-host-country-search" autocomplete="off">
                                    </div>
                                    <ul class="authme-dropdown-list" id="authme-host-country-list">
                                        <!-- Populated by JS -->
                                    </ul>
                                </div>
                                <input type="hidden" id="authme-host-country-code" value="">
                            </div>
                            <input type="tel" id="authme-host-mobile" class="authme-host-input authme-host-mobile-input" placeholder="Mobile Number" required>
                        </div>
                        <span id="authme-host-mobile-msg" class="authme-host-field-msg"></span>
                    </div>

                    <button type="button" id="authme-host-next-btn" class="authme-host-btn authme-host-btn-primary" disabled>Next</button>
                </form>
            </div>

            <!-- ==========================================
                 STEP 2: Document Upload 
                 ========================================== -->
            <div id="authme-host-step-2" class="authme-host-screen">
                <h2 class="authme-host-title">Upload Documents</h2>
                <p class="authme-host-subtitle">Step 2: Verification identity</p>

                <form id="authme-host-upload-form" class="authme-host-form" novalidate>

                    <!-- Aadhar Front -->
                    <div class="authme-host-upload-box">
                        <label class="authme-host-upload-label">Aadhar Card (Front)</label>
                        <div class="authme-host-upload-area" id="authme-upload-area-aadharf">
                            <input type="file" id="authme-host-aadharf" accept=".jpg,.jpeg" class="authme-host-file-input">
                            <div class="authme-host-upload-placeholder">
                                <span>Click to upload JPEG (Max 1MB)</span>
                            </div>
                            <div class="authme-host-progress-wrap" style="display:none;">
                                <div class="authme-host-progress-bar"></div>
                            </div>
                            <div class="authme-host-upload-preview" style="display:none;">
                                <img src="" alt="Aadhar Front Preview">
                                <button type="button" class="authme-host-remove-file" aria-label="Remove file">×</button>
                            </div>
                        </div>
                        <span id="authme-host-aadharf-msg" class="authme-host-field-msg"></span>
                    </div>

                    <!-- Aadhar Back -->
                    <div class="authme-host-upload-box">
                        <label class="authme-host-upload-label">Aadhar Card (Back)</label>
                        <div class="authme-host-upload-area" id="authme-upload-area-aadharb">
                            <input type="file" id="authme-host-aadharb" accept=".jpg,.jpeg" class="authme-host-file-input">
                            <div class="authme-host-upload-placeholder">
                                <span>Click to upload JPEG (Max 1MB)</span>
                            </div>
                            <div class="authme-host-progress-wrap" style="display:none;">
                                <div class="authme-host-progress-bar"></div>
                            </div>
                            <div class="authme-host-upload-preview" style="display:none;">
                                <img src="" alt="Aadhar Back Preview">
                                <button type="button" class="authme-host-remove-file" aria-label="Remove file">×</button>
                            </div>
                        </div>
                        <span id="authme-host-aadharb-msg" class="authme-host-field-msg"></span>
                    </div>

                    <!-- PAN Front -->
                    <div class="authme-host-upload-box">
                        <label class="authme-host-upload-label">PAN Card (Front)</label>
                        <div class="authme-host-upload-area" id="authme-upload-area-pan">
                            <input type="file" id="authme-host-pan" accept=".jpg,.jpeg" class="authme-host-file-input">
                            <div class="authme-host-upload-placeholder">
                                <span>Click to upload JPEG (Max 1MB)</span>
                            </div>
                            <div class="authme-host-progress-wrap" style="display:none;">
                                <div class="authme-host-progress-bar"></div>
                            </div>
                            <div class="authme-host-upload-preview" style="display:none;">
                                <img src="" alt="PAN Card Preview">
                                <button type="button" class="authme-host-remove-file" aria-label="Remove file">×</button>
                            </div>
                        </div>
                        <span id="authme-host-pan-msg" class="authme-host-field-msg"></span>
                    </div>

                    <div class="authme-host-actions">
                        <button type="button" id="authme-host-prev-to-1" class="authme-host-btn authme-host-btn-secondary">Back</button>
                        <button type="button" id="authme-host-send-otp-btn" class="authme-host-btn authme-host-btn-primary" disabled>Send OTP</button>
                    </div>
                </form>
            </div>

            <!-- ==========================================
                 STEP 3: OTP Verification 
                 ========================================== -->
            <div id="authme-host-step-3" class="authme-host-screen">
                <h2 class="authme-host-title">Verification</h2>
                <p class="authme-host-subtitle">Step 3: Enter the 6-digit code sent to your email.</p>

                <form id="authme-host-otp-form" class="authme-host-form" novalidate>
                    <div class="authme-host-otp-container" id="authme-host-otp-boxes">
                        <input type="text" class="authme-host-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required autofocus>
                        <input type="text" class="authme-host-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" class="authme-host-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" class="authme-host-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" class="authme-host-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" class="authme-host-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    </div>

                    <button type="submit" id="authme-host-otp-submit-btn" class="authme-host-btn authme-host-btn-primary">Verify & Submit</button>

                    <!-- Dot Bounce Loader (Hidden by default) -->
                    <div id="authme-host-loader" class="authme-host-loader" style="display:none;">
                        <div class="host-dot"></div>
                        <div class="host-dot"></div>
                        <div class="host-dot"></div>
                    </div>
                </form>

                <p class="authme-host-switch-link">
                    Didn't receive the code?
                    <span id="authme-host-resend-btn" class="authme-host-link authme-host-link-disabled">Resend in <b id="authme-host-otp-timer">60</b>s</span>
                </p>

                <button type="button" id="authme-host-prev-to-2" class="authme-host-btn authme-host-btn-secondary authme-host-link-back">Back</button>
            </div>

            <!-- ==========================================
                 STEP 4: Success Message
                 ========================================== -->
            <div id="authme-host-step-4" class="authme-host-screen authme-host-success-screen">
                <div class="authme-host-success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h2 class="authme-host-title">Application Submitted!</h2>
                <p class="authme-host-subtitle">Your application is submitted, after review you will get email with login credentials.</p>
                <p class="authme-host-auto-close-msg">This window will close in <b id="authme-host-close-timer">15</b> seconds.</p>
            </div>

        </div>
    </div>
</div>