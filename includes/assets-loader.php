<?php
/**
 * AuthMe — Centralized Assets Loader
 *
 * This file is the SINGLE source of truth for all file paths
 * (CSS, JS, templates, includes) used across the plugin.
 * Every other file references paths from here.
 *
 * Directory Structure (User Arranged):
 *   frontend/css/        — Frontend CSS files
 *   frontend/js/         — Frontend JS files
 *   frontend/template/   — Frontend PHP templates
 *   backend/css/         — Admin CSS files
 *   backend/js/          — Admin JS files
 *   backend/template/    — Admin PHP templates
 *   global-assets/css/   — Global CSS (variables, toaster)
 *   global-assets/js/    — Global JS (toaster, confirm)
 *   global-assets/template/ — Global templates
 *   includes/            — PHP class files
 *   mails/               — Email templates
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AuthMe_Assets_Loader {

    /* ================================================
     * 📁 PATH REGISTRY
     * ================================================
     * All plugin file paths are defined here.
     * Use AuthMe_Assets_Loader::get() to access them.
     * ================================================ */

    /**
     * Get all registered file paths.
     *
     * Each entry contains:
     *   'dir'  → Absolute server path (for file_exists / filemtime)
     *   'url'  → Public URL (for wp_enqueue)
     *
     * @return array Associative array of all file paths.
     */
    public static function get_paths() {

        $base_dir = AUTHME_PLUGIN_DIR;
        $base_url = AUTHME_PLUGIN_URL;

        return array(

            /* ── Global Assets ──────────────────── */
            'css_global'    => array(
                'dir' => $base_dir . 'global-assets/css/global.css',
                'url' => $base_url . 'global-assets/css/global.css',
            ),
            'js_global'     => array(
                'dir' => $base_dir . 'global-assets/js/global.js',
                'url' => $base_url . 'global-assets/js/global.js',
            ),

            /* ── Frontend Assets (CSS) ──────────── */
            'css_overlay'   => array(
                'dir' => $base_dir . 'frontend/css/overlay.css',
                'url' => $base_url . 'frontend/css/overlay.css',
            ),
            'css_login'     => array(
                'dir' => $base_dir . 'frontend/css/login.css',
                'url' => $base_url . 'frontend/css/login.css',
            ),
            'css_register'  => array(
                'dir' => $base_dir . 'frontend/css/register.css',
                'url' => $base_url . 'frontend/css/register.css',
            ),
            'css_forgot_password' => array(
                'dir' => $base_dir . 'frontend/css/forgot-password.css',
                'url' => $base_url . 'frontend/css/forgot-password.css',
            ),
            'css_new_password'    => array(
                'dir' => $base_dir . 'frontend/css/new-password.css',
                'url' => $base_url . 'frontend/css/new-password.css',
            ),
            'css_host_request' => array(
                'dir' => $base_dir . 'frontend/css/host-request.css',
                'url' => $base_url . 'frontend/css/host-request.css',
            ),
            'css_google_auth' => array(
                'dir' => $base_dir . 'components/google-auth/google-auth.css',
                'url' => $base_url . 'components/google-auth/google-auth.css',
            ),
            'css_otp'       => array(
                'dir' => $base_dir . 'frontend/css/otp.css',
                'url' => $base_url . 'frontend/css/otp.css',
            ),
            'css_toaster'   => array(
                'dir' => $base_dir . 'global-assets/css/toaster.css',
                'url' => $base_url . 'global-assets/css/toaster.css',
            ),
            'css_confirm'   => array(
                'dir' => $base_dir . 'global-assets/css/confirmation.css',
                'url' => $base_url . 'global-assets/css/confirmation.css',
            ),

            /* ── Frontend Assets (JS) ───────────── */
            'js_toaster'    => array(
                'dir' => $base_dir . 'global-assets/js/toaster.js',
                'url' => $base_url . 'global-assets/js/toaster.js',
            ),
            'js_overlay'    => array(
                'dir' => $base_dir . 'frontend/js/overlay.js',
                'url' => $base_url . 'frontend/js/overlay.js',
            ),
            'js_login'      => array(
                'dir' => $base_dir . 'frontend/js/login.js',
                'url' => $base_url . 'frontend/js/login.js',
            ),
            'js_phone_core' => array(
                'dir' => $base_dir . 'global-assets/js/am-phone-core.js',
                'url' => $base_url . 'global-assets/js/am-phone-core.js',
            ),
            'js_register'   => array(
                'dir' => $base_dir . 'frontend/js/register.js',
                'url' => $base_url . 'frontend/js/register.js',
            ),
            'js_forgot_password' => array(
                'dir' => $base_dir . 'frontend/js/forgot-password.js',
                'url' => $base_url . 'frontend/js/forgot-password.js',
            ),
            'js_new_password'    => array(
                'dir' => $base_dir . 'frontend/js/new-password.js',
                'url' => $base_url . 'frontend/js/new-password.js',
            ),
            'js_host_request' => array(
                'dir' => $base_dir . 'frontend/js/host-request.js',
                'url' => $base_url . 'frontend/js/host-request.js',
            ),
            'js_google_auth' => array(
                'dir' => $base_dir . 'components/google-auth/google-auth.js',
                'url' => $base_url . 'components/google-auth/google-auth.js',
            ),
            'js_otp'        => array(
                'dir' => $base_dir . 'frontend/js/otp.js',
                'url' => $base_url . 'frontend/js/otp.js',
            ),
            'js_confirm'    => array(
                'dir' => $base_dir . 'global-assets/js/confirmation.js',
                'url' => $base_url . 'global-assets/js/confirmation.js',
            ),

            /* ── Frontend Templates ─────────────── */
            'tpl_overlay'   => array(
                'dir' => $base_dir . 'frontend/template/overlay.php',
            ),
            'tpl_login'     => array(
                'dir' => $base_dir . 'frontend/template/login.php',
            ),
            'tpl_register'  => array(
                'dir' => $base_dir . 'frontend/template/register.php',
            ),
            'tpl_otp'       => array(
                'dir' => $base_dir . 'frontend/template/otp.php',
            ),
            'tpl_google_auth' => array(
                'dir' => $base_dir . 'components/google-auth/google-auth.php',
            ),
            'tpl_toaster'   => array(
                'dir' => $base_dir . 'global-assets/template/toaster.php',
            ),
            'tpl_forgot_password' => array(
                'dir' => $base_dir . 'frontend/template/forgot-password.php',
            ),
            'tpl_new_password'    => array(
                'dir' => $base_dir . 'frontend/template/new-password.php',
            ),
            'tpl_host_request'    => array(
                'dir' => $base_dir . 'frontend/template/host-request.php',
            ),
            'tpl_confirm' => array(
                'dir' => $base_dir . 'global-assets/template/confirmation.php',
            ),

            /* ── Mails ─────────────────────────── */
            'tpl_master_mail' => array(
                'dir' => $base_dir . 'mails/master-mail-template.php',
            ),

            /* ── Admin Assets ───────────────────── */
            'admin_global_css' => array(
                'dir' => $base_dir . 'global-assets/css/global.css',
                'url' => $base_url . 'global-assets/css/global.css',
            ),
            'admin_dashboard_css' => array(
                'dir' => $base_dir . 'backend/css/dashboard.css',
                'url' => $base_url . 'backend/css/dashboard.css',
            ),
            'admin_dashboard_js'  => array(
                'dir' => $base_dir . 'backend/js/dashboard.js',
                'url' => $base_url . 'backend/js/dashboard.js',
            ),
            'admin_host_requests_css' => array(
                'dir' => $base_dir . 'backend/css/host-requests.css',
                'url' => $base_url . 'backend/css/host-requests.css',
            ),
            'admin_host_requests_js'  => array(
                'dir' => $base_dir . 'backend/js/host-requests.js',
                'url' => $base_url . 'backend/js/host-requests.js',
            ),
            'admin_database_css' => array(
                'dir' => $base_dir . 'backend/css/database.css',
                'url' => $base_url . 'backend/css/database.css',
            ),
            'admin_database_js' => array(
                'dir' => $base_dir . 'backend/js/database.js',
                'url' => $base_url . 'backend/js/database.js',
            ),
            'admin_view_form_css' => array(
                'dir' => $base_dir . 'backend/css/view-form.css',
                'url' => $base_url . 'backend/css/view-form.css',
            ),
            'admin_view_form_js' => array(
                'dir' => $base_dir . 'backend/js/view-form.js',
                'url' => $base_url . 'backend/js/view-form.js',
            ),

            /* ── Admin Templates ────────────────── */
            'admin_dashboard' => array(
                'dir' => $base_dir . 'backend/template/dashboard.php',
            ),
            'admin_database'  => array(
                'dir' => $base_dir . 'backend/template/database.php',
            ),
            'admin_host_requests' => array(
                'dir' => $base_dir . 'backend/template/host-requests.php',
            ),
            'admin_view_form' => array(
                'dir' => $base_dir . 'backend/template/view-form.php',
            ),
            'tpl_confirm' => array(
                'dir' => $base_dir . 'global-assets/template/confirmation.php',
            ),


            /* ── Include (PHP Class) Files ──────── */
            'inc_db'        => array(
                'dir' => $base_dir . 'includes/class-authme-db.php',
            ),
            'inc_db_schema' => array(
                'dir' => $base_dir . 'includes/db-schema.php',
            ),
            'inc_auth'      => array(
                'dir' => $base_dir . 'includes/class-authme-auth.php',
            ),
            'inc_otp'       => array(
                'dir' => $base_dir . 'includes/class-authme-otp.php',
            ),
            'inc_host_request' => array(
                'dir' => $base_dir . 'includes/class-authme-host-request.php',
            ),
            'inc_email'     => array(
                'dir' => $base_dir . 'mails/mail-controller.php',
            ),
            'inc_assets'    => array(
                'dir' => $base_dir . 'includes/assets-loader.php',
            ),
        );
    }

    /* ================================================
     * 🔍 QUICK ACCESSORS
     * ================================================ */

    /**
     * Get the server path (dir) for a specific file key.
     *
     * @param string $key File key from the path registry.
     * @return string|null Absolute file path, or null if not found.
     */
    public static function dir( $key ) {
        $paths = self::get_paths();
        return isset( $paths[ $key ]['dir'] ) ? $paths[ $key ]['dir'] : null;
    }

    /**
     * Get the public URL for a specific file key.
     *
     * @param string $key File key from the path registry.
     * @return string|null Public URL, or null if not found.
     */
    public static function url( $key ) {
        $paths = self::get_paths();
        return isset( $paths[ $key ]['url'] ) ? $paths[ $key ]['url'] : null;
    }

    /**
     * Get the filemtime version for cache-busting.
     *
     * @param string $key File key from the path registry.
     * @return int|false File modification time, or false on failure.
     */
    public static function version( $key ) {
        $dir = self::dir( $key );
        return ( $dir && file_exists( $dir ) ) ? filemtime( $dir ) : AUTHME_VERSION;
    }

    /* ================================================
     * 🎨 ENQUEUE FRONTEND ASSETS
     * ================================================ */

    /**
     * Enqueue all frontend CSS and JS files.
     * Hooked to 'wp_enqueue_scripts'.
     */
    public static function enqueue_frontend() {

        /* ── CSS Files ───────────────────── */
        $css_files = array( 'global', 'overlay', 'login', 'register', 'otp', 'toaster', 'confirm', 'forgot_password', 'new_password', 'host_request', 'google_auth' );

        foreach ( $css_files as $name ) {
            $key = 'css_' . $name;
            $dir = self::dir( $key );
            $url = self::url( $key );

            if ( $dir && $url && file_exists( $dir ) ) {
                wp_enqueue_style(
                    'authme-' . $name,
                    $url,
                    array(),
                    self::version( $key )
                );
            }
        }

        /* ── JS Files with Dependencies ──── */
        $js_files = array(
            'global'   => array(),
            'toaster'  => array( 'authme-global' ),
            'confirm'  => array( 'authme-global' ),
            'overlay'  => array( 'authme-global' ),
            'login'    => array( 'authme-global', 'authme-toaster', 'authme-overlay' ),
            'phone_core' => array( 'authme-global' ),
            'register' => array( 'authme-global', 'authme-toaster', 'authme-overlay', 'authme-phone_core' ),
            'otp'             => array( 'authme-global', 'authme-toaster', 'authme-overlay' ),
            'forgot_password' => array( 'authme-global', 'authme-toaster', 'authme-overlay' ),
            'new_password'    => array( 'authme-global', 'authme-toaster', 'authme-overlay' ),
            'host_request'    => array( 'authme-global', 'authme-toaster', 'authme-confirm', 'authme-phone_core' ),
            'google_auth'     => array( 'authme-global', 'authme-toaster' ),
        );

        foreach ( $js_files as $name => $deps ) {
            $key = 'js_' . $name;
            $dir = self::dir( $key );
            $url = self::url( $key );

            if ( $dir && $url && file_exists( $dir ) ) {
                wp_enqueue_script(
                    'authme-' . $name,
                    $url,
                    $deps,
                    self::version( $key ),
                    true // Load in footer
                );
            }
        }

        /* ── Localize AJAX data ──────────── */
        wp_localize_script( 'authme-global', 'authme_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'authme_nonce' ),
        ) );
    }
}
