<?php
/**
 * AuthMe Authentication Handler
 *
 * Handles user registration, login, and real-time field validation
 * via AJAX endpoints.
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



class AuthMe_Auth {

    /* ──────────────────────────────────────────────────
     * AJAX: Check username availability (Registration)
     * ────────────────────────────────────────────────── */
    public function ajax_check_username() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $username = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';

        if ( empty( $username ) ) {
            wp_send_json_error( array( 'message' => 'Username is required.' ) );
        }

        // Must start with an alphabetic character
        if ( ! preg_match( '/^[a-zA-Z]/', $username ) ) {
            wp_send_json_error( array( 'message' => 'Username must start with an alphabet character.' ) );
        }

        // Length: 4–14 characters, alphanumeric only
        if ( ! preg_match( '/^[a-zA-Z][a-zA-Z0-9]{3,13}$/', $username ) ) {
            wp_send_json_error( array( 'message' => 'Username must be 4–14 alphanumeric characters.' ) );
        }

        // Check uniqueness
        if ( username_exists( $username ) ) {
            wp_send_json_error( array( 'message' => 'Username not available.' ) );
        }

        wp_send_json_success( array( 'message' => 'Username available.' ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Check email availability (Registration)
     * ────────────────────────────────────────────────── */
    public function ajax_check_email() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => 'Email already registered.' ) );
        }

        wp_send_json_success( array( 'message' => 'Email available.' ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Check mobile availability (Registration)
     * ────────────────────────────────────────────────── */
    public function ajax_check_mobile() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( $_POST['mobile'] ) : '';

        if ( empty( $mobile ) ) {
            wp_send_json_error( array( 'message' => 'Please enter a valid mobile number.' ) );
        }

        // Check uniqueness in user meta
        $args = array(
            'meta_key'     => 'mobile_number',
            'meta_value'   => $mobile,
            'meta_compare' => '=',
            'number'       => 1,
            'fields'       => 'IDS'
        );
        $users = get_users( $args );

        if ( ! empty( $users ) ) {
            wp_send_json_error( array( 'message' => 'Mobile number already registered.' ) );
        }

        wp_send_json_success( array( 'message' => 'Mobile number available.' ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Check if user exists (Login — user lookup)
     * ────────────────────────────────────────────────── */
    public function ajax_check_user_exists() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $identifier = isset( $_POST['identifier'] ) ? sanitize_text_field( $_POST['identifier'] ) : '';

        if ( empty( $identifier ) ) {
            wp_send_json_error( array( 'message' => 'Please enter your username or email.' ) );
        }

        // Auto-detect: if it contains '@', treat as email
        if ( strpos( $identifier, '@' ) !== false ) {
            $user = get_user_by( 'email', sanitize_email( $identifier ) );
        } else {
            $user = get_user_by( 'login', $identifier );
        }

        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'User not found. Please check your credentials.' ) );
        }

        wp_send_json_success( array(
            'message' => 'User found.',
            'email'   => $user->user_email,
        ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Login user
     *
     * Validates credentials. If direct_login=true (login form),
     * sets auth cookie immediately and returns success.
     * ────────────────────────────────────────────────── */
    public function ajax_login_user() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $identifier   = isset( $_POST['identifier'] ) ? sanitize_text_field( $_POST['identifier'] ) : '';
        $password     = isset( $_POST['password'] )   ? $_POST['password'] : '';
        $remember     = isset( $_POST['remember'] ) && $_POST['remember'] === 'true';
        $direct_login = isset( $_POST['direct_login'] ) && $_POST['direct_login'] === 'true';

        if ( empty( $identifier ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => 'All fields are required.' ) );
        }

        // Determine user by email or username
        if ( strpos( $identifier, '@' ) !== false ) {
            $user = get_user_by( 'email', sanitize_email( $identifier ) );
        } else {
            $user = get_user_by( 'login', $identifier );
        }

        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'No account found. Please check your credentials.' ) );
        }

        // Restrict administrator login
        if ( in_array( 'administrator', (array) $user->roles ) ) {
            wp_send_json_error( array( 'message' => 'Administrator accounts cannot log in here.' ) );
        }

        // Verify password
        if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
            wp_send_json_error( array( 'message' => 'Incorrect password. Please try again.' ) );
        }

        if ( $direct_login ) {
            // Direct login mode — set auth cookie immediately, no OTP step
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID, $remember );
            do_action( 'wp_login', $user->user_login, $user );

            wp_send_json_success( array(
                'message' => 'Login successful! Welcome back, ' . esc_html( $user->display_name ) . '.',
            ) );
        }

        // Fallback: credentials valid — frontend will now trigger OTP send (legacy path)
        wp_send_json_success( array(
            'message'  => 'Credentials verified. Sending OTP…',
            'email'    => $user->user_email,
            'user_id'  => $user->ID,
            'remember' => $remember,
        ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Register user (called after OTP verification)
     * ────────────────────────────────────────────────── */
    public function ajax_register_user() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        // Expect raw user_data JSON string
        $user_data_raw = isset( $_POST['user_data'] ) ? wp_unslash( $_POST['user_data'] ) : '';

        if ( empty( $user_data_raw ) ) {
            wp_send_json_error( array( 'message' => 'User data is missing.' ) );
        }

        $user_data = json_decode( $user_data_raw, true );

        if ( ! $user_data || empty( $user_data['username'] ) || empty( $user_data['email'] ) || empty( $user_data['password'] ) ) {
            wp_send_json_error( array( 'message' => 'Invalid user data.' ) );
        }

        $username = sanitize_user( $user_data['username'] );
        $email    = sanitize_email( $user_data['email'] );
        $password = $user_data['password'];
        $mobile_number = isset( $user_data['mobile_number'] ) ? sanitize_text_field( $user_data['mobile_number'] ) : '';
        $mobile_region = isset( $user_data['mobile_region'] ) ? sanitize_text_field( $user_data['mobile_region'] ) : '';

        // Server-side mobile number validation (Lightweight check)
        if ( ! empty( $mobile_number ) ) {
            $clean_mobile = preg_replace( '/[^0-9]/', '', $mobile_number );
            if ( strlen( $clean_mobile ) < 7 || strlen( $clean_mobile ) > 15 ) {
                wp_send_json_error( array( 'message' => 'Invalid mobile number format.' ) );
            }
        }

        // Double-check uniqueness
        if ( username_exists( $username ) ) {
            wp_send_json_error( array( 'message' => 'Username not available.' ) );
        }
        if ( email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => 'Email already registered.' ) );
        }

        // Create the user
        $user_id = wp_insert_user( array(
            'user_login'    => $username,
            'user_nicename' => sanitize_title( $username ),
            'user_email'    => $email,
            'user_pass'     => $password,
            'role'          => 'traveller',
        ) );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
        }

        // Save mobile number to user meta
        if ( ! empty( $mobile_number ) ) {
            update_user_meta( $user_id, 'mobile_number', $mobile_number );
        }

        // Set verification flags in usermeta
        // Email is verified because registration requires OTP verification on the email
        update_user_meta( $user_id, 'is_email_verified', 1 );
        // Mobile number is NOT verified yet (no OTP sent to phone)
        update_user_meta( $user_id, 'is_number_verified', 0 );
        update_user_meta( $user_id, 'auth_method', 'normal_auth' );

        // Auto-login the newly registered user
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );

        wp_send_json_success( array(
            'message'  => 'Registration successful! Welcome aboard.',
        ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Check if user exists (Forgot Password — lookup)
     *
     * Accepts email or username. Returns the email address
     * so we can send an OTP to it.
     * ────────────────────────────────────────────────── */
    public function ajax_forgot_check_user() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $identifier = isset( $_POST['identifier'] ) ? sanitize_text_field( $_POST['identifier'] ) : '';

        if ( empty( $identifier ) ) {
            wp_send_json_error( array( 'message' => 'Please enter your email or username.' ) );
        }

        // Auto-detect: if it contains '@', treat as email; otherwise as username
        if ( strpos( $identifier, '@' ) !== false ) {
            $user = get_user_by( 'email', sanitize_email( $identifier ) );
        } else {
            $user = get_user_by( 'login', $identifier );
        }

        if ( ! $user ) {
            wp_send_json_error( array(
                'message' => 'No account found with this detail. Please create one.',
            ) );
        }

        wp_send_json_success( array(
            'email'   => $user->user_email,
            'user_id' => $user->ID,
        ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Reset password (called after OTP verification)
     *
     * Receives the user email and new password.
     * Updates the password in the database and sends
     * a "password changed" email notification.
     * ────────────────────────────────────────────────── */
    public function ajax_reset_password() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $email        = isset( $_POST['email'] )        ? sanitize_email( $_POST['email'] )       : '';
        $new_password = isset( $_POST['new_password'] ) ? wp_unslash( $_POST['new_password'] )    : '';

        if ( empty( $email ) || empty( $new_password ) ) {
            wp_send_json_error( array( 'message' => 'All fields are required.' ) );
        }

        // Password strength: minimum 8 chars
        if ( strlen( $new_password ) < 8 ) {
            wp_send_json_error( array( 'message' => 'Password must be at least 8 characters.' ) );
        }

        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'User not found.' ) );
        }

        // Update the password
        wp_set_password( $new_password, $user->ID );

        // Send "password changed" email notification
        $email_handler = new AuthMe_Email();
        $email_handler->send_password_changed_email( $email, $user->display_name );

        wp_send_json_success( array(
            'message' => 'Password reset successfully! You can now log in.',
        ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Google Auth Callback
     * ────────────────────────────────────────────────── */
    public function ajax_google_auth_callback() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $access_token = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';

        if ( empty( $access_token ) ) {
            wp_send_json_error( array( 'message' => 'Authentication token missing.' ) );
        }

        // 1. Fetch Basic User Info
        $userinfo_url = 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . $access_token;
        $response = wp_remote_get( $userinfo_url );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => 'Failed to fetch user data from Google API.' ) );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $data['email'] ) ) {
            wp_send_json_error( array( 'message' => 'Invalid data received from Google.' ) );
        }

        $email          = sanitize_email( $data['email'] );
        $name           = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : strstr($email, '@', true);
        $profile_pic    = isset( $data['picture'] ) ? esc_url_raw( $data['picture'] ) : '';
        $email_verified = !empty( $data['email_verified'] ) ? 1 : 0;

        // 2. Fetch Phone Number (People API)
        $people_url = 'https://people.googleapis.com/v1/people/me?personFields=phoneNumbers';
        $people_response = wp_remote_get( $people_url, array(
            'headers' => array( 'Authorization' => 'Bearer ' . $access_token )
        ) );

        $phone_number = '';
        $is_number_verified = 0;

        if ( ! is_wp_error( $people_response ) ) {
            $people_data = json_decode( wp_remote_retrieve_body( $people_response ), true );
            if ( ! empty( $people_data['phoneNumbers'] ) && is_array( $people_data['phoneNumbers'] ) ) {
                $best_phone = '';
                $best_phone_is_verified = 0;
                
                foreach ( $people_data['phoneNumbers'] as $phone ) {
                    if ( ! empty( $phone['value'] ) ) {
                        $is_verified = ! empty( $phone['metadata']['verified'] ) ? 1 : 0;
                        
                        // Extract canonicalForm as primary, fallback to value
                        if ( ! empty( $phone['canonicalForm'] ) ) {
                            $formatted_phone = sanitize_text_field( $phone['canonicalForm'] );
                        } else {
                            $formatted_phone = sanitize_text_field( $phone['value'] );
                        }
                        
                        // Format mobile number without space
                        $formatted_phone = str_replace( array(' ', '-', '(', ')'), '', $formatted_phone );

                        // Take this phone if:
                        // 1. We don't have a phone yet.
                        // 2. OR the current best phone is NOT verified, but THIS one IS verified.
                        if ( empty( $best_phone ) || ( $best_phone_is_verified === 0 && $is_verified === 1 ) ) {
                            $best_phone = $formatted_phone;
                            $best_phone_is_verified = $is_verified;
                        }
                    }
                }
                
                if ( ! empty( $best_phone ) ) {
                    $phone_number = $best_phone;
                    $is_number_verified = $best_phone_is_verified;
                }
            }
        }

        // 3. Process User (Login or Create)
        $user = get_user_by( 'email', $email );

        if ( $user ) {
            $existing_auth_method = get_user_meta( $user->ID, 'auth_method', true );

            if ( $existing_auth_method !== 'google_auth' ) {
                wp_send_json_error( array( 
                    'message' => 'Account already exists. Please login with password.'
                ) );
            }

            $has_phone = false;
            // Existing User: Update Profile Pic and Phone if fetched
            if ( ! empty( $profile_pic ) ) {
                update_user_meta( $user->ID, 'profile_pic', $profile_pic );
            }
            if ( ! empty( $phone_number ) ) {
                update_user_meta( $user->ID, 'mobile_number', $phone_number );
                update_user_meta( $user->ID, 'is_number_verified', $is_number_verified );
                $has_phone = true;
            } else {
                $existing_phone = get_user_meta( $user->ID, 'mobile_number', true );
                $has_phone = ! empty( $existing_phone );
            }

            // Redirect if phone is missing
            $redirect_url = '';
            if ( ! $has_phone ) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'admin_management';
                $profile_page_id = $wpdb->get_var( $wpdb->prepare( "SELECT page_id FROM $table_name WHERE name = %s", 'My Profile' ) );
                if ( $profile_page_id ) {
                    $redirect_url = get_permalink( $profile_page_id );
                }
            }

            // Set Auth Cookies
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID, true );
            do_action( 'wp_login', $user->user_login, $user );

            wp_send_json_success( array(
                'message'      => 'Welcome back, ' . esc_html( $user->display_name ) . '!',
                'redirect_url' => $redirect_url
            ) );
        } else {
            // New User: Create Account
            $base_username = strstr($email, '@', true);
            $username = $base_username;
            $counter = 1;
            while ( username_exists( $username ) ) {
                $username = $base_username . $counter;
                $counter++;
            }

            $user_id = wp_insert_user( array(
                'user_login'      => $username,
                'user_nicename'   => sanitize_title( $username ),
                'user_email'      => $email,
                'user_pass'       => wp_generate_password( 16, true ),
                'display_name'    => $name,
                'role'            => 'traveller',
                'user_registered' => current_time( 'mysql' ),
            ) );

            if ( is_wp_error( $user_id ) ) {
                wp_send_json_error( array( 'message' => 'Account creation failed: ' . $user_id->get_error_message() ) );
            }

            // Save Metadata
            update_user_meta( $user_id, 'auth_method', 'google_auth' );
            update_user_meta( $user_id, 'is_email_verified', $email_verified );
            update_user_meta( $user_id, 'is_number_verified', $is_number_verified );
            update_user_meta( $user_id, 'profile_pic', $profile_pic );
            
            $has_phone = false;
            if ( ! empty( $phone_number ) ) {
                update_user_meta( $user_id, 'mobile_number', $phone_number );
                $has_phone = true;
            }

            // Explicitly set traveller capability (as per PRD)
            update_user_meta( $user_id, 'wp_capabilities', array( 'traveller' => true ) );

            // Redirect if phone is missing
            $redirect_url = '';
            if ( ! $has_phone ) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'admin_management';
                $profile_page_id = $wpdb->get_var( $wpdb->prepare( "SELECT page_id FROM $table_name WHERE name = %s", 'My Profile' ) );
                if ( $profile_page_id ) {
                    $redirect_url = get_permalink( $profile_page_id );
                }
            }

            // Login
            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id, true );
            do_action( 'wp_login', $username, get_userdata( $user_id ) );

            wp_send_json_success( array(
                'message'      => 'Account created successfully! Welcome, ' . esc_html( $name ) . '.',
                'redirect_url' => $redirect_url
            ) );
        }
    }
}

