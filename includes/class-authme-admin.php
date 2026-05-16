<?php
/**
 * AuthMe Admin Panel
 *
 * Registers admin menu pages, enqueues admin assets,
 * and provides AJAX handlers for database management.
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AuthMe_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_admin_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Admin AJAX endpoints
        add_action( 'wp_ajax_authme_admin_check_db', array( $this, 'ajax_check_db_status' ) );
        add_action( 'wp_ajax_authme_admin_create_tables', array( $this, 'ajax_create_tables' ) );
        
        // Host Requests Admin AJAX endpoints
        add_action( 'wp_ajax_authme_admin_get_host_requests', array( $this, 'ajax_get_host_requests' ) );
        add_action( 'wp_ajax_authme_admin_get_single_host', array( $this, 'ajax_get_single_host_request' ) );
        add_action( 'wp_ajax_authme_admin_process_host', array( $this, 'ajax_process_host_request' ) );

        // All Users Admin AJAX endpoints
        add_action( 'wp_ajax_authme_admin_get_all_users', array( $this, 'ajax_get_all_users' ) );
        add_action( 'wp_ajax_authme_admin_get_user_details', array( $this, 'ajax_get_user_details' ) );
        add_action( 'wp_ajax_authme_admin_update_user', array( $this, 'ajax_update_user_details' ) );
        add_action( 'wp_ajax_authme_admin_check_username', array( $this, 'ajax_check_username_availability' ) );
        add_action( 'wp_ajax_authme_admin_export_csv', array( $this, 'ajax_export_csv' ) );
        
        // Admin Footer Injections
        add_action( 'admin_footer', array( $this, 'inject_admin_global_ui' ) );
    }

    /* ──────────────────────────────────────── */

    /**
     * Register admin menu and sub-menu pages.
     */
    public function register_admin_menus() {
        // Main menu: AuthMe
        add_menu_page(
            'AuthMe',                          // Page title
            'AuthMe',                          // Menu title
            'manage_options',                   // Capability
            'authme',                           // Menu slug
            array( $this, 'render_dashboard' ), // Callback
            'dashicons-lock',                   // Icon
            80                                  // Position
        );

        // Sub-menu: Dashboard (overrides the auto-generated first submenu "AuthMe")
        add_submenu_page(
            'authme',                           // Parent slug
            'AuthMe Dashboard',                 // Page title
            'Dashboard',                        // Menu title
            'manage_options',                   // Capability
            'authme',                           // Same slug as parent to override
            array( $this, 'render_dashboard' )  // Callback
        );

        // Sub-menu: Database
        add_submenu_page(
            'authme',                           // Parent slug
            'AuthMe Database',                  // Page title
            'Database',                         // Menu title
            'manage_options',                   // Capability
            'authme-database',                  // Menu slug
            array( $this, 'render_database' )   // Callback
        );

        // Sub-menu: Host Requests
        global $wpdb;
        $host_table = $wpdb->prefix . 'host_request';
        $pending_count = 0;
        if ( $wpdb->get_var("SHOW TABLES LIKE '{$host_table}'") === $host_table ) {
            $pending_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$host_table} WHERE status = 'pending'" );
        }
        
        $host_menu_title = 'Host Requests';
        if ( $pending_count > 0 ) {
            $host_menu_title .= ' <span class="update-plugins count-' . esc_attr( $pending_count ) . '"><span class="plugin-count">' . esc_html( $pending_count ) . '</span></span>';
        }

        add_submenu_page(
            'authme',                           // Parent slug
            'Host Requests',                    // Page title
            $host_menu_title,                   // Menu title
            'manage_options',                   // Capability
            'authme-host-requests',             // Menu slug
            array( $this, 'render_host_requests' ) // Callback
        );

        // Sub-menu: All Users
        add_submenu_page(
            'authme',                           // Parent slug
            'All Users',                        // Page title
            'All Users',                        // Menu title
            'manage_options',                   // Capability
            'authme-users',                     // Menu slug
            array( $this, 'render_all_users' )  // Callback
        );

        // Hidden Sub-menu: View Form
        add_submenu_page(
            null,                               // Parent slug null to hide it from menu
            'View Form',                        // Page title
            '',                                 // Menu title
            'manage_options',                   // Capability
            'authme-view-form',                 // Menu slug
            array( $this, 'render_view_form' )  // Callback
        );

        // Hidden Sub-menu: View User
        add_submenu_page(
            null,                               // Parent slug null to hide it from menu
            'View User',                        // Page title
            '',                                 // Menu title
            'manage_options',                   // Capability
            'authme-view-user',                 // Menu slug
            array( $this, 'render_view_user' )  // Callback
        );
    }

    /* ──────────────────────────────────────── */

    /**
     * Enqueue admin CSS and JS only on AuthMe admin pages.
     *
     * @param string $hook The current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on AuthMe pages
        if ( strpos( $hook, 'authme' ) === false ) {
            return;
        }

        // Enqueue media library for profile photo upload on View User page
        if ( strpos( $hook, 'authme-view-user' ) !== false ) {
            wp_enqueue_media();
        }

        // Global CSS
        $global_css_url = AuthMe_Assets_Loader::url('admin_global_css');
        wp_enqueue_style( 'authme-admin-global-css', $global_css_url, array(), AuthMe_Assets_Loader::version('admin_global_css') );

        // Global Toaster
        wp_enqueue_style( 'authme-toaster', AuthMe_Assets_Loader::url('css_toaster'), array(), AuthMe_Assets_Loader::version('css_toaster') );
        wp_enqueue_script( 'authme-toaster', AuthMe_Assets_Loader::url('js_toaster'), array(), AuthMe_Assets_Loader::version('js_toaster'), true );

        // Global Confirm Modal
        wp_enqueue_style( 'authme-confirm', AuthMe_Assets_Loader::url('css_confirm'), array(), AuthMe_Assets_Loader::version('css_confirm') );
        wp_enqueue_script( 'authme-confirm', AuthMe_Assets_Loader::url('js_confirm'), array(), AuthMe_Assets_Loader::version('js_confirm'), true );

        // Dynamically load page-specific CSS/JS if they exist (based on menu slug)
        $current_page = '';
        if ( strpos( $hook, 'authme-database' ) !== false ) {
            $current_page = 'database';
        } elseif ( strpos( $hook, 'authme-host-requests' ) !== false ) {
            $current_page = 'host-requests';
        } elseif ( strpos( $hook, 'authme-view-form' ) !== false ) {
            $current_page = 'view-form';
        } elseif ( strpos( $hook, 'authme-users' ) !== false ) {
            $current_page = 'all_users';
        } elseif ( strpos( $hook, 'authme-view-user' ) !== false ) {
            $current_page = 'view_user';
        } elseif ( strpos( $hook, 'toplevel_page_authme' ) !== false || strpos( $hook, 'authme_page_authme' ) !== false ) {
            $current_page = 'dashboard';
        }

        if ( ! empty( $current_page ) ) {
            // Normalize slug for loader key (host-requests -> host_requests)
            $loader_key = str_replace( '-', '_', $current_page );
            
            $css_key = 'admin_' . $loader_key . '_css';
            $js_key  = 'admin_' . $loader_key . '_js';

            // Enqueue CSS
            $css_url = AuthMe_Assets_Loader::url( $css_key );
            if ( $css_url ) {
                wp_enqueue_style(
                    'authme-admin-' . $current_page . '-css',
                    $css_url,
                    array( 'authme-admin-global-css' ),
                    AuthMe_Assets_Loader::version( $css_key )
                );
            }

            // Enqueue JS
            $js_url = AuthMe_Assets_Loader::url( $js_key );
            if ( $js_url ) {
                wp_enqueue_script(
                    'authme-admin-' . $current_page . '-js',
                    $js_url,
                    array( 'jquery', 'authme-toaster', 'authme-confirm' ),
                    AuthMe_Assets_Loader::version( $js_key ),
                    true
                );
                
                wp_localize_script( 'authme-admin-' . $current_page . '-js', 'authme_admin', array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'authme_admin_nonce' ),
                ) );
            }
        }


    }

    /* ──────────────────────────────────────── */

    /**
     * Render the Dashboard page.
     */
    public function render_dashboard() {
        $tpl = AuthMe_Assets_Loader::dir('admin_dashboard');
        if ($tpl && file_exists($tpl)) include $tpl;
    }

    /**
     * Render the Database management page.
     */
    public function render_database() {
        $tpl = AuthMe_Assets_Loader::dir('admin_database');
        if ($tpl && file_exists($tpl)) include $tpl;
    }

    /**
     * Render the Host Requests page.
     */
    public function render_host_requests() {
        $tpl = AuthMe_Assets_Loader::dir('admin_host_requests');
        if ($tpl && file_exists($tpl)) include $tpl;
    }

    /**
     * Render the View Form page.
     */
    public function render_view_form() {
        $tpl = AuthMe_Assets_Loader::dir('admin_view_form');
        if ($tpl && file_exists($tpl)) include $tpl;
    }

    /**
     * Render the All Users page.
     */
    public function render_all_users() {
        $tpl = AuthMe_Assets_Loader::dir('admin_all_users_tpl');
        if ($tpl && file_exists($tpl)) include $tpl;
    }

    /**
     * Render the View User page.
     */
    public function render_view_user() {
        $tpl = AuthMe_Assets_Loader::dir('admin_view_user_tpl');
        if ($tpl && file_exists($tpl)) include $tpl;
    }

    /* ──────────────────────────────────────── */

    /**
     * AJAX: Check database table status.
     */
    public function ajax_check_db_status() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
        }

        $db     = new AuthMe_DB();
        $status = $db->check_table_status();

        wp_send_json_success( $status );
    }

    /**
     * AJAX: Create or update database tables.
     */
    public function ajax_create_tables() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
        }

        $db = new AuthMe_DB();

        // Check current status before creating
        $status_before = $db->check_table_status();

        if ( $status_before['all_good'] ) {
            wp_send_json_success( array(
                'message' => 'All tables already exist with the correct structure.',
                'status'  => 'already_created',
            ) );
        }

        // Create / update tables
        $db->create_tables();

        // Verify after creation
        $status_after = $db->check_table_status();

        if ( $status_after['all_good'] ) {
            if ( ! $status_before['otp_table']['exists'] || ! $status_before['host_table']['exists'] ) {
                $msg = 'Create tables successfully';
            } else {
                $msg = 'Updated successfully';
            }

            wp_send_json_success( array(
                'message' => $msg,
                'status'  => 'created',
            ) );
        }

        wp_send_json_error( array( 'message' => 'Failed to create tables. Please try again.' ) );
    }

    /* ── Host Requests AJAX Endpoints ────────── */

    /**
     * Fetch list of host requests for the datatable.
     */
    public function ajax_get_host_requests() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'host_request';

        $status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'all';
        $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
        $page   = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
        $limit  = 10;
        $offset = ( $page - 1 ) * $limit;

        $where_clauses = array();
        $query_args    = array();

        if ( $status !== 'all' ) {
            $where_clauses[] = "status = %s";
            $query_args[]    = $status;
        }

        if ( ! empty( $search ) && strlen( $search ) >= 3 ) {
            $search_like = '%' . $wpdb->esc_like( $search ) . '%';
            $where_clauses[] = "(user_data LIKE %s OR id LIKE %s)";
            $query_args[]    = $search_like;
            $query_args[]    = $search_like;
        }

        $where_sql = '';
        if ( ! empty( $where_clauses ) ) {
            $where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );
        }

        // Total counts for tabs (ignoring search)
        $count_pending  = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'pending'" );
        $count_approved = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'approved'" );
        $count_rejected = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'rejected'" );
        $count_all      = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

        // Total items matching current filter/search
        $total_query_sql = "SELECT COUNT(*) FROM $table_name $where_sql";
        $total_items = empty( $query_args ) ? $wpdb->get_var( $total_query_sql ) : $wpdb->get_var( $wpdb->prepare( $total_query_sql, $query_args ) );

        // Fetch data
        $data_query_sql = "SELECT id, status, date, user_data FROM $table_name $where_sql ORDER BY date DESC LIMIT %d OFFSET %d";
        $data_args   = array_merge( $query_args, array( $limit, $offset ) );
        $results     = $wpdb->get_results( $wpdb->prepare( $data_query_sql, $data_args ) );

        $formatted_data = array();
        if ( $results ) {
            foreach ( $results as $row ) {
                $user_data = json_decode( $row->user_data, true );
                $formatted_data[] = array(
                    'id'     => $row->id,
                    'raw_id' => $row->id,
                    'email'  => isset( $user_data['email'] ) ? $user_data['email'] : 'N/A',
                    'phone'  => isset( $user_data['mobile'] ) ? $user_data['mobile'] : 'N/A',
                    'fullname'=> isset( $user_data['fullname'] ) ? $user_data['fullname'] : 'N/A',
                    'status' => $row->status,
                    'date'   => wp_date( 'F j, Y g:i A', strtotime( $row->date ) ),
                );
            }
        }

        wp_send_json_success( array(
            'items' => $formatted_data,
            'total' => $total_items,
            'pages' => ceil( $total_items / $limit ),
            'counts' => array(
                'all'      => $count_all,
                'pending'  => $count_pending,
                'approved' => $count_approved,
                'rejected' => $count_rejected,
            )
        ) );
    }

    /**
     * Fetch a single host request.
     */
    public function ajax_get_single_host_request() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
        }

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid ID.' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'host_request';
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );

        if ( ! $row ) {
            wp_send_json_error( array( 'message' => 'Request not found.' ) );
        }

        $user_data = json_decode( $row->user_data, true );

        wp_send_json_success( array(
            'id'       => $row->id,
            'raw_id'   => $row->id,
            'status'   => $row->status,
            'userData' => $user_data,
        ) );
    }

    /**
     * Process host request (Approve, Reject, Pending).
     */
    public function ajax_process_host_request() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
        }

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        $new_status = isset( $_POST['new_status'] ) ? sanitize_text_field( $_POST['new_status'] ) : '';

        if ( ! $id || ! in_array( $new_status, array( 'pending', 'approved', 'rejected' ) ) ) {
            wp_send_json_error( array( 'message' => 'Invalid data.' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'host_request';
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );

        if ( ! $row ) {
            wp_send_json_error( array( 'message' => 'Request not found.' ) );
        }

        // If it's already approved, blocking further action to avoid double user creation.
        if ( $row->status === 'approved' ) {
            wp_send_json_error( array( 'message' => 'This request is already approved.' ) );
        }

        $user_data = json_decode( $row->user_data, true );
        $email = $user_data['email'];
        $username = $user_data['username'];
        $mobile = isset( $user_data['mobile'] ) ? $user_data['mobile'] : '';

        if ( $new_status === 'approved' ) {
            // Check if user exists
            if ( email_exists( $email ) || username_exists( $username ) ) {
                wp_send_json_error( array( 'message' => 'A user with this email or username already exists.' ) );
            }

            // Create user
            $password = wp_generate_password( 12, false );
            $user_id = wp_create_user( $username, $password, $email );

            if ( is_wp_error( $user_id ) ) {
                wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
            }

            // Set Role and Meta
            $user = new WP_User( $user_id );
            $user->set_role( 'host' );

            wp_update_user( array(
                'ID' => $user_id,
                'display_name' => $username,
                'user_nicename' => $username,
            ) );

            update_user_meta( $user_id, 'mobile_number', $mobile );
            update_user_meta( $user_id, 'is_email_verified', '1' );
            update_user_meta( $user_id, 'is_number_verified', '0' );

            // Send Email
            $email_handler = new AuthMe_Email();
            if ( method_exists( $email_handler, 'send_host_approved_email' ) ) {
                $email_handler->send_host_approved_email( $email, $username, $password );
            }
            
            $wpdb->update( $table_name, array( 'status' => 'approved' ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
            wp_send_json_success( array( 'message' => 'Request Approved & User Created successfully.' ) );

        } elseif ( $new_status === 'rejected' ) {
            $wpdb->update( $table_name, array( 'status' => 'rejected' ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
            
            // Send Email
            $email_handler = new AuthMe_Email();
            if ( method_exists( $email_handler, 'send_host_rejected_email' ) ) {
                $email_handler->send_host_rejected_email( $email );
            }

            wp_send_json_success( array( 'message' => 'Request Rejected successfully.' ) );
        } else {
            // Pending
            $wpdb->update( $table_name, array( 'status' => 'pending' ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
            wp_send_json_success( array( 'message' => 'Status set to Pending.' ) );
        }
    }
    /* ── Users Management AJAX Endpoints ────────── */

    /**
     * Fetch list of travelers for the datatable.
     */
    public function ajax_get_all_users() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
        }

        $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
        $page   = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
        $limit  = 10;
        $offset = ( $page - 1 ) * $limit;

        // Note: The plugin uses 'traveller' (double 'l') in some places and 'traveler' in others.
        // We'll search for both or just use the one used in registration.
        $args = array(
            'role'    => 'traveller',
            'number'  => $limit,
            'offset'  => $offset,
            'search'  => ! empty( $search ) ? '*' . $search . '*' : '',
            'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
            'orderby' => 'user_registered',
            'order'   => 'DESC',
        );

        $user_query = new WP_User_Query( $args );
        $users = $user_query->get_results();
        $total_users = $user_query->get_total();

        $formatted_data = array();
        foreach ( $users as $user ) {
            $formatted_data[] = array(
                'id'       => $user->ID,
                'username' => $user->user_login,
                'email'    => $user->user_email,
                'phone'    => get_user_meta( $user->ID, 'mobile_number', true ) ?: 'N/A',
                'fullname' => $user->display_name ?: $user->user_login,
                'date'     => wp_date( 'F j, Y', strtotime( $user->user_registered ) ),
            );
        }

        wp_send_json_success( array(
            'items' => $formatted_data,
            'total' => $total_users,
            'pages' => ceil( $total_users / $limit ),
        ) );
    }

    /**
     * Fetch single user details.
     */
    public function ajax_get_user_details() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
        }

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid User ID.' ) );
        }

        $user = get_userdata( $id );
        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'User not found.' ) );
        }

        $avatar_url = get_user_meta( $user->ID, 'profile_pic', true );
        $avatar_id  = $avatar_url ? attachment_url_to_postid( $avatar_url ) : 0;
        
        if ( ! $avatar_url ) {
            $avatar_url = get_avatar_url( $user->ID );
        }

        $data = array(
            'id'         => $user->ID,
            'username'   => $user->user_login,
            'email'      => $user->user_email,
            'fullname'   => $user->display_name,
            'mobile'     => get_user_meta( $user->ID, 'mobile_number', true ) ?: '',
            'role'       => implode( ', ', $user->roles ),
            'avatar_url' => $avatar_url,
            'avatar_id'  => $avatar_id,
        );

        wp_send_json_success( $data );
    }

    /**
     * Update user details and password.
     */
    public function ajax_update_user_details() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
        }

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid User ID.' ) );
        }

        $email    = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
        $fullname = isset( $_POST['fullname'] ) ? sanitize_text_field( $_POST['fullname'] ) : '';
        $mobile   = isset( $_POST['mobile'] ) ? sanitize_text_field( $_POST['mobile'] ) : '';
        $username = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
        $password = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $avatar_id = isset( $_POST['avatar_id'] ) ? intval( $_POST['avatar_id'] ) : 0;

        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Invalid email address.' ) );
        }

        // Check if email is already taken by another user
        $existing_user = get_user_by( 'email', $email );
        if ( $existing_user && $existing_user->ID !== $id ) {
            wp_send_json_error( array( 'message' => 'This email is already in use by another account.' ) );
        }

        $userdata = array(
            'ID'           => $id,
            'user_email'   => $email,
            'display_name' => $fullname,
        );

        if ( ! empty( $password ) ) {
            $userdata['user_pass'] = $password;
        }

        $update_id = wp_update_user( $userdata );

        if ( is_wp_error( $update_id ) ) {
            wp_send_json_error( array( 'message' => $update_id->get_error_message() ) );
        }

        // Update Username directly via SQL if changed (wp_update_user doesn't allow it)
        $current_user = get_userdata( $id );
        if ( $username && $username !== $current_user->user_login ) {
            // Re-check uniqueness just in case
            $check_user = get_user_by( 'login', $username );
            if ( $check_user && $check_user->ID !== $id ) {
                wp_send_json_error( array( 'message' => 'This username is already taken.' ) );
            }

            global $wpdb;
            $wpdb->update(
                $wpdb->users,
                array( 'user_login' => $username, 'user_nicename' => sanitize_title( $username ) ),
                array( 'ID' => $id )
            );
            clean_user_cache( $id );
        }

        update_user_meta( $id, 'mobile_number', $mobile );
        
        if ( $avatar_id ) {
            $avatar_url = wp_get_attachment_url( $avatar_id );
            if ( $avatar_url ) {
                update_user_meta( $id, 'profile_pic', $avatar_url );
            }
        }

        wp_send_json_success( array( 'message' => 'User updated successfully.' ) );
    }

    /**
     * Check if username is available.
     */
    public function ajax_check_username_availability() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
        }

        $username = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
        $user_id  = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;

        if ( empty( $username ) ) {
            wp_send_json_error( array( 'message' => 'Username cannot be empty.' ) );
        }

        $user = get_user_by( 'login', $username );

        if ( $user && $user->ID !== $user_id ) {
            wp_send_json_error( array( 'message' => 'Username is already taken.', 'available' => false ) );
        }

        wp_send_json_success( array( 'message' => 'Username is available.', 'available' => true ) );
    }

    /**
     * AJAX handler for CSV Export.
     */
    public function ajax_export_csv() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }

        $type = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';
        
        if ( $type === 'users' ) {
            $this->export_users_csv();
        } elseif ( $type === 'hosts' ) {
            $this->export_hosts_csv();
        } else {
            wp_die( 'Invalid export type.' );
        }
    }

    /**
     * Export Travelers to CSV.
     */
    private function export_users_csv() {
        $args = array(
            'role'    => 'traveller',
            'orderby' => 'user_registered',
            'order'   => 'DESC',
            'number'  => -1, // Export all
        );
        $users = get_users( $args );

        $filename = 'authme-travelers-' . date('Y-m-d') . '.csv';
        
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        
        $output = fopen( 'php://output', 'w' );
        // Added S.No and User-ID as requested
        fputcsv( $output, array( 'S.No', 'User-ID', 'Username', 'Email', 'Full Name', 'Mobile', 'Joined Date' ) );

        $sno = 1;
        foreach ( $users as $user ) {
            $mobile = get_user_meta( $user->ID, 'mobile_number', true );
            // Force string formatting for Excel by adding a tab prefix to prevent scientific notation
            $mobile_formatted = $mobile ? "\t" . $mobile : 'N/A';
            
            fputcsv( $output, array(
                $sno++,
                $user->ID,
                $user->user_login,
                $user->user_email,
                $user->display_name,
                $mobile_formatted,
                get_date_from_gmt( $user->user_registered, 'Y-m-d H:i:s' ),
            ) );
        }
        
        fclose( $output );
        exit;
    }

    /**
     * Export Host Applications to CSV.
     */
    private function export_hosts_csv() {
        global $wpdb;
        $table_name = AuthMe_DB_Schema::host_request_table();
        $results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY date DESC" );

        $filename = 'authme-host-applications-' . date('Y-m-d') . '.csv';
        
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        
        $output = fopen( 'php://output', 'w' );
        // S.No, Application ID, Name, Username, Email, Phone, Status, Date Time, Aadhar Front, Aadhar Back, PAN Card
        fputcsv( $output, array( 'S.No', 'Application ID', 'Name', 'Username', 'Email', 'Phone', 'Status', 'Date Time', 'Aadhar Front', 'Aadhar Back', 'PAN Card' ) );

        $sno = 1;
        foreach ( $results as $row ) {
            $user_data = json_decode( $row->user_data, true ) ?: array();
            
            $name     = isset( $user_data['fullname'] ) ? $user_data['fullname'] : 'N/A';
            $username = isset( $user_data['username'] ) ? $user_data['username'] : 'N/A';
            $email    = isset( $user_data['email'] ) ? $user_data['email'] : 'N/A';
            $phone    = isset( $user_data['mobile'] ) ? $user_data['mobile'] : 'N/A';
            
            // Handle multiple documents (Aadhar Front, Aadhar Back, PAN)
            $docs = isset( $user_data['documents'] ) ? $user_data['documents'] : array();
            $doc1 = isset( $docs['aadharf']['url'] ) ? $docs['aadharf']['url'] : 'N/A';
            $doc2 = isset( $docs['aadharb']['url'] ) ? $docs['aadharb']['url'] : 'N/A';
            $doc3 = isset( $docs['pan']['url'] ) ? $docs['pan']['url'] : 'N/A';
            
            // Force string for phone
            $phone_formatted = ( $phone !== 'N/A' ) ? "\t" . $phone : 'N/A';

            fputcsv( $output, array(
                $sno++,
                $row->id,
                $name,
                $username,
                $email,
                $phone_formatted,
                ucfirst( $row->status ),
                $row->date,
                $doc1,
                $doc2,
                $doc3
            ) );
        }
        
        fclose( $output );
        exit;
    }

    /**
     * Inject global UI components (Toaster, Confirmation) into the admin footer.
     */
    public function inject_admin_global_ui() {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'authme' ) === false ) {
            return;
        }

        $toaster_tpl = AuthMe_Assets_Loader::dir('tpl_toaster');
        $confirm_tpl = AuthMe_Assets_Loader::dir('tpl_confirm');

        if ( $toaster_tpl && file_exists( $toaster_tpl ) ) {
            include $toaster_tpl;
        }
        if ( $confirm_tpl && file_exists( $confirm_tpl ) ) {
            include $confirm_tpl;
        }
    }
}

// Initialize admin
new AuthMe_Admin();
