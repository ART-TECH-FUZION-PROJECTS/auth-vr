<?php
/**
 * AuthMe Core Handler
 *
 * This class centralizes all the hooks, filters, and helper functions
 * that were previously in the main authme.php file.
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AuthMe_Core {

    /**
     * Initialize all plugin hooks.
     */
    public function __construct() {
        // Enqueue Frontend Assets
        add_action('wp_enqueue_scripts', array('AuthMe_Assets_Loader', 'enqueue_frontend'));
        
        // Pass Google Client ID to JS
        add_action('wp_enqueue_scripts', array($this, 'pass_google_client_id'), 15);

        // Global UI Components (Toaster, Confirmation)
        add_action('wp_footer', array($this, 'inject_global_ui'));

        // Footer Injections (Overlay and Host Request Modal)
        add_action('wp_footer', array($this, 'inject_overlay_in_footer'));
        add_action('wp_footer', array($this, 'inject_host_request_modal'));

        // AJAX Actions
        add_action('init', array($this, 'register_ajax_actions'));

        // Virtual Page, Rewrite Rules, and Universal Logout
        add_action('init', array($this, 'register_rewrite_rules'));
        add_filter('query_vars', array($this, 'register_query_vars'));
        add_action('template_redirect', array($this, 'handle_virtual_page'));
        add_action('template_redirect', array($this, 'handle_universal_logout'));

        // UI & Access Restrictions
        add_action('after_setup_theme', array($this, 'disable_admin_bar'));
        add_filter('login_redirect', array($this, 'restrict_login_redirect'), 10, 3);
        add_action('admin_init', array($this, 'restrict_admin_access'));

        // Cron OTP Cleanup
        add_action('authme_otp_cleanup', array($this, 'run_otp_cleanup'));

        // Admin Panel Settings Link
        add_filter('plugin_action_links_' . AUTHME_PLUGIN_BASENAME, array($this, 'add_settings_link'));
    }

    /**
     * Fetch Google Client ID from DB and pass to JS
     */
    public function pass_google_client_id() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'admin_management';
        
        $suppress = $wpdb->suppress_errors();
        $google_client_id = $wpdb->get_var( $wpdb->prepare( "SELECT value FROM $table_name WHERE name = %s", 'auth_client_id' ) );
        $wpdb->suppress_errors( $suppress );

        wp_localize_script( 'authme-global', 'authme_google_settings', array(
            'client_id' => $google_client_id ? $google_client_id : ''
        ) );
    }

    /**
     * Activation Callback
     */
    public static function activate() {
        // Register roles
        if (! get_role('traveller')) {
            add_role('traveller', 'Traveller', array('read' => true));
        }
        if (! get_role('host')) {
            add_role('host', 'Host', array('read' => true, 'upload_files' => true));
        }

        // Flush rewrite rules
        add_rewrite_rule('^authme/?$', 'index.php?authme_page=1', 'top');
        flush_rewrite_rules();

        // Schedule Cron
        if (! wp_next_scheduled('authme_otp_cleanup')) {
            $timezone = wp_timezone();
            $date = new DateTime('today 03:00:00', $timezone);
            if ($date->getTimestamp() <= time()) {
                $date->modify('+1 day');
            }
            wp_schedule_event($date->getTimestamp(), 'daily', 'authme_otp_cleanup');
        }
    }

    /**
     * Deactivation Callback
     */
    public static function deactivate() {
        flush_rewrite_rules();
        $timestamp = wp_next_scheduled('authme_otp_cleanup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'authme_otp_cleanup');
        }
    }

    /**
     * Singleton instance for static access
     */
    private static $instance = null;
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * AJAX Endpoint Registration
     */
    public function register_ajax_actions() {
        $authme_auth = new AuthMe_Auth();
        $authme_otp  = new AuthMe_OTP();
        $authme_host = new AuthMe_Host_Request();

        $ajax_actions = array(
            'authme_check_username'    => array($authme_auth, 'ajax_check_username'),
            'authme_check_email'       => array($authme_auth, 'ajax_check_email'),
            'authme_check_mobile'      => array($authme_auth, 'ajax_check_mobile'),
            'authme_check_user_exists' => array($authme_auth, 'ajax_check_user_exists'),
            'authme_login_user'        => array($authme_auth, 'ajax_login_user'),
            'authme_register_user'     => array($authme_auth, 'ajax_register_user'),
            'authme_complete_login'    => array($authme_auth, 'ajax_complete_login'),
            'authme_forgot_check_user' => array($authme_auth, 'ajax_forgot_check_user'),
            'authme_reset_password'    => array($authme_auth, 'ajax_reset_password'),
            'authme_send_otp'          => array($authme_otp, 'ajax_send_otp'),
            'authme_verify_otp'        => array($authme_otp, 'ajax_verify_otp'),
            'authme_check_host_username'   => array($authme_host, 'ajax_check_host_username'),
            'authme_check_host_email'      => array($authme_host, 'ajax_check_host_email'),
            'authme_check_host_mobile'     => array($authme_host, 'ajax_check_host_mobile'),
            'authme_upload_host_document'  => array($authme_host, 'ajax_upload_host_document'),
            'authme_submit_host_request'   => array($authme_host, 'ajax_submit_host_request'),
            'authme_delete_host_document'  => array($authme_host, 'ajax_delete_host_document'),
            'authme_google_auth_callback'  => array($authme_auth, 'ajax_google_auth_callback'),
        );

        foreach ($ajax_actions as $action => $callback) {
            add_action('wp_ajax_' . $action, $callback);
            add_action('wp_ajax_nopriv_' . $action, $callback);
        }
    }

    /**
     * Footer injection: Global UI (Toaster, Confirmation)
     */
    public function inject_global_ui() {
        $toaster_tpl = AuthMe_Assets_Loader::dir('tpl_toaster');
        $confirm_tpl = AuthMe_Assets_Loader::dir('tpl_confirm');

        if ($toaster_tpl && file_exists($toaster_tpl)) include $toaster_tpl;
        if ($confirm_tpl && file_exists($confirm_tpl)) include $confirm_tpl;
    }

    /**
     * Footer injection: Overlay HTML
     */
    public function inject_overlay_in_footer() {
        if (is_user_logged_in()) return;

        $overlay_tpl = AuthMe_Assets_Loader::dir('tpl_overlay');

        if ($overlay_tpl && file_exists($overlay_tpl)) include $overlay_tpl;

        if (isset($_GET['authme_open']) && $_GET['authme_open'] === '1') {
            echo '<script>document.addEventListener("DOMContentLoaded",function(){if(typeof authmeOpenOverlay==="function"){authmeOpenOverlay();}});</script>';
        }
    }

    /**
     * Footer injection: Host Request Modal
     */
    public function inject_host_request_modal() {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            if (in_array('host', (array) $user->roles)) return;
        }

        if (isset($_GET['become-host'])) {
            $toaster_tpl = AuthMe_Assets_Loader::dir('tpl_toaster');
            $host_req_tpl = AuthMe_Assets_Loader::dir('tpl_host_request');

            if ($toaster_tpl && file_exists($toaster_tpl)) include $toaster_tpl;
            if ($host_req_tpl && file_exists($host_req_tpl)) include $host_req_tpl;
            echo '<script>document.addEventListener("DOMContentLoaded",function(){if(typeof authmeOpenHostModal==="function"){authmeOpenHostModal();}});</script>';
        }
    }

    /**
     * Rewrite Rules
     */
    public function register_rewrite_rules() {
        add_rewrite_rule('^authme/?$', 'index.php?authme_page=1', 'top');
    }

    public function register_query_vars($vars) {
        $vars[] = 'authme_page';
        return $vars;
    }

    public function handle_virtual_page() {
        if (! get_query_var('authme_page')) return;

        if (is_user_logged_in()) {
            wp_safe_redirect(home_url());
            exit;
        }
        wp_safe_redirect(home_url('?authme_open=1'));
        exit;
    }

    public function handle_universal_logout() {
        if (isset($_GET['authme_logout']) && $_GET['authme_logout'] === '1') {
            if (is_user_logged_in()) wp_logout();
            wp_safe_redirect(home_url());
            exit;
        }
    }

    /**
     * UI Restrictions
     */
    public function disable_admin_bar() {
        if (is_user_logged_in() && !current_user_can('administrator') && !is_admin()) {
            show_admin_bar(false);
        }
    }

    public function restrict_login_redirect($redirect_to, $request, $user) {
        if (isset($user->roles) && is_array($user->roles)) {
            if (!in_array('administrator', $user->roles)) {
                return home_url();
            }
        }
        return $redirect_to;
    }

    public function restrict_admin_access() {
        if (defined('DOING_AJAX') && DOING_AJAX) return;
        if (is_user_logged_in() && !current_user_can('administrator')) {
            wp_safe_redirect(home_url());
            exit;
        }
    }

    /**
     * Cron Job Logic
     */
    public function run_otp_cleanup() {
        (new AuthMe_OTP())->cleanup_expired_otps();
        $host_req = new AuthMe_Host_Request();
        $host_req->cleanup_orphaned_documents();
        $host_req->cleanup_rejected_requests();
    }

    /**
     * Plugin Settings Link
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=authme') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
