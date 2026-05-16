<?php

/**
 * Plugin Name: AuthMe
 * Plugin URI: https://arttechfuzion.com
 * Description: A comprehensive WordPress authentication plugin with OTP verification for secure registration and login.
 * Version: 2.1.8
 * Author: Art-Tech Fuzion
 * Author URI: https://arttechfuzion.com
 * Text Domain: authme
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

/* ──────────────────────────────────────────────
 * Constants
 * ────────────────────────────────────────────── */
define('AUTHME_VERSION', '2.1.8');
define('AUTHME_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AUTHME_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AUTHME_PLUGIN_BASENAME', plugin_basename(__FILE__));

/* ──────────────────────────────────────────────
 * Include Files
 * ────────────────────────────────────────────── */
require_once AUTHME_PLUGIN_DIR . 'includes/assets-loader.php';
require_once AUTHME_PLUGIN_DIR . 'includes/db-schema.php';
require_once AUTHME_PLUGIN_DIR . 'includes/class-authme-db.php';
require_once AUTHME_PLUGIN_DIR . 'includes/class-authme-email.php';
require_once AUTHME_PLUGIN_DIR . 'includes/class-authme-otp.php';
require_once AUTHME_PLUGIN_DIR . 'includes/class-authme-auth.php';
require_once AUTHME_PLUGIN_DIR . 'includes/class-authme-host-request.php';
require_once AUTHME_PLUGIN_DIR . 'includes/class-authme-core.php';

// Admin files (only on admin pages)
if (is_admin()) {
    require_once AUTHME_PLUGIN_DIR . 'includes/class-authme-admin.php';
}

/* ──────────────────────────────────────────────
 * Initialize Plugin
 * ────────────────────────────────────────────── */

// Hook activation/deactivation to the core class
register_activation_hook(__FILE__, array('AuthMe_Core', 'activate'));
register_deactivation_hook(__FILE__, array('AuthMe_Core', 'deactivate'));

// Start the core engine (registers hooks and actions)
AuthMe_Core::instance();
