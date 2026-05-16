<?php

/**
 * AuthMe Register Screen Template
 *
 * Rendered inside the overlay container.
 * Fields: Username, Email, Mobile Number (with country code), Password, Confirm Password.
 *
 * @package AuthMe
 */

if (! defined('ABSPATH')) {
    exit;
}
?>

<div id="authme-register-screen" class="authme-screen">

    <h2 class="authme-form-title">Create account</h2>
    <p class="authme-form-subtitle">Join us for an exclusive experience.</p>

    <form id="authme-register-form" class="authme-form" autocomplete="off" novalidate>

        <!-- Username Field -->
        <div class="authme-input-group">
            <input type="text" id="authme-reg-username" class="authme-input"
                placeholder="Username" aria-label="Username" required>
            <span id="authme-reg-username-msg" class="authme-field-msg"></span>
        </div>

        <!-- Email Field -->
        <div class="authme-input-group">
            <input type="email" id="authme-reg-email" class="authme-input"
                placeholder="Email Address" aria-label="Email Address" required>
            <span id="authme-reg-email-msg" class="authme-field-msg"></span>
        </div>

        <!-- Mobile Number Field -->
        <div class="authme-input-group">
            <div class="authme-mobile-wrapper">
                <div class="authme-custom-dropdown" id="authme-reg-country-dropdown">
                    <button type="button" class="authme-dropdown-trigger" id="authme-reg-country-trigger">
                        <span class="authme-selected-flag">🌐</span>
                        <span class="authme-selected-code">Code</span>
                        <svg class="authme-dropdown-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </button>
                    <div class="authme-dropdown-menu" id="authme-reg-country-menu" style="display:none;">
                        <div class="authme-dropdown-search">
                            <input type="text" placeholder="Search country..." id="authme-reg-country-search" autocomplete="off">
                        </div>
                        <ul class="authme-dropdown-list" id="authme-reg-country-list">
                            <!-- Populated by JS -->
                        </ul>
                    </div>
                    <input type="hidden" id="authme-reg-country-code" value="">
                </div>

                <input type="tel" id="authme-reg-mobile" class="authme-input authme-mobile-input"
                    placeholder="Mobile Number" aria-label="Mobile Number" required>
            </div>
            <span id="authme-reg-mobile-msg" class="authme-field-msg"></span>
        </div>


        <!-- Password Field -->
        <div class="authme-input-group">
            <div class="authme-password-wrapper">
                <input type="password" id="authme-reg-password" class="authme-input"
                    placeholder="Password" aria-label="Password" required>
                <button type="button" class="authme-toggle-password" data-target="authme-reg-password" aria-label="Toggle password visibility">
                    <svg class="authme-eye-off" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                    <svg class="authme-eye-on" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            <!-- Password strength indicator -->
            <div id="authme-reg-password-strength" class="authme-password-strength"></div>
            <span id="authme-reg-password-msg" class="authme-field-msg"></span>
        </div>

        <!-- Confirm Password Field -->
        <div class="authme-input-group">
            <div class="authme-password-wrapper">
                <input type="password" id="authme-reg-confirm-password" class="authme-input"
                    placeholder="Confirm Password" aria-label="Confirm Password" required>
                <button type="button" class="authme-toggle-password" data-target="authme-reg-confirm-password" aria-label="Toggle password visibility">
                    <svg class="authme-eye-off" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                    <svg class="authme-eye-on" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            <span id="authme-reg-confirm-password-msg" class="authme-field-msg"></span>
        </div>

        <!-- Remember Me -->
        <div class="authme-remember-me">
            <input type="checkbox" id="authme-reg-remember">
            <label for="authme-reg-remember">Remember me</label>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="authme-reg-submit-btn" class="authme-btn authme-btn-primary" disabled>Send OTP</button>

        <?php 
            $google_auth_tpl = AuthMe_Assets_Loader::dir('tpl_google_auth');
            if ($google_auth_tpl && file_exists($google_auth_tpl)) include $google_auth_tpl; 
        ?>

    </form>

    <!-- Switch to Login -->
    <p class="authme-switch-link">
        Already have an account?
        <span class="authme-link" data-screen="authme-login-screen">Login</span>
    </p>

</div>