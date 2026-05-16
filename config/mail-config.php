<?php
/**
 * AuthMe Email Configurations
 *
 * Centralized configuration for all email templates sent by the plugin.
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get configuration for a specific email template.
 *
 * @param string $template_id The identifier for the email template.
 * @return array|null Returns the config array, or null if not found.
 */
function authme_get_email_config( $template_id ) {
    $site_name   = get_bloginfo( 'name' );
    $admin_email = get_option( 'admin_email' );

    $configs = array(
        'registration_otp' => array(
            'subject'        => 'Your Registration Verification Code — ' . $site_name,
            'title'          => 'Registration Verification Code',
            'message'        => 'Use the following one-time password to complete your registration. This code is valid for <strong>60 seconds</strong>.',
            'note'           => 'If you did not request this code, you can safely ignore this email.',
            'has_otp'        => true,
            'has_details'    => false,
            'has_admin_data' => false,
        ),
        'password_reset_otp' => array(
            'subject'        => 'Reset Password Verification code — ' . $site_name,
            'title'          => 'Verification Code',
            'message'        => 'Use the following one-time password to complete your reset password. This code is valid for <strong>60 seconds</strong>.',
            'note'           => 'Note: This code is requested to reset your password, If you have not requested kindly change your password to secure.',
            'has_otp'        => true,
            'has_details'    => false,
            'has_admin_data' => false,
        ),
        'host_request_otp' => array(
            'subject'        => 'Host Id Verification Code — ' . $site_name,
            'title'          => 'Verification Code',
            'message'        => 'Use the following one-time password to complete your registration. This code is valid for <strong>60 seconds</strong>.',
            'note'           => 'If you did not request this code, you can safely ignore this email.',
            'has_otp'        => true,
            'has_details'    => false,
            'has_admin_data' => false,
        ),
        'password_changed' => array(
            'subject'        => 'Reset Password Successfully — ' . $site_name,
            'title'          => 'Account password changed',
            'message'        => 'Your account password is changed successfully. Now your requested password is saved to database, you can login with your new password.',
            'note'           => 'Note: If you did not changed your password then reset your password now or contact to admin on this email - (' . $admin_email . ').',
            'has_otp'        => false,
            'has_details'    => false,
            'has_admin_data' => false,
        ),
        'host_approved' => array(
            'subject'        => 'Congratulations!! Your Host ID is Approved — ' . $site_name,
            'title'          => 'Application Approved',
            'message'        => 'Congratulations, your host account is approved! Now you can list your property. Here are your securely generated login credentials:',
            'note'           => 'Note: This is system generated password. Please securely login and change this auto-generated password immediately!',
            'has_otp'        => false,
            'has_details'    => true,
            'has_admin_data' => false,
        ),
        'host_rejected' => array(
            'subject'        => 'Regret!! Your host id unverified — ' . $site_name,
            'title'          => 'Application Rejected',
            'message'        => 'We are unable to verify your email because some documents are fake or missing.',
            'note'           => 'Note: This is an auto-generated mail, If you have any query Contact admin on this email - (' . $admin_email . ').',
            'has_otp'        => false,
            'has_details'    => false,
            'has_admin_data' => false,
        ),
        'admin_host_request' => array(
            'subject'        => 'New Host Request Submitted — ' . $site_name,
            'title'          => 'New Host Request',
            'message'        => 'A new user has submitted an application to become a host. Please review their details below.',
            'note'           => 'You can approve or reject this request from the AuthMe Host Requests dashboard.',
            'has_otp'        => false,
            'has_details'    => false,
            'has_admin_data' => false,
            'has_all_details'=> true,
        ),
        'google_register_success' => array(
            'subject'        => 'Welcome to ' . $site_name,
            'title'          => 'Account Created Successfully',
            'message'        => 'Your account has been created successfully via Google Authentication. Welcome to our community!',
            'note'           => 'You can now log in anytime using the "Continue with Google" button.',
            'has_otp'        => false,
            'has_details'    => false,
            'has_admin_data' => false,
            'has_all_details'=> true,
        ),
        'register_success' => array(
            'subject'        => 'Welcome to ' . $site_name,
            'title'          => 'Account Created Successfully',
            'message'        => 'Your account has been created successfully. Welcome to our community!',
            'note'           => 'You can now log in using your username/email and password.',
            'has_otp'        => false,
            'has_details'    => false,
            'has_admin_data' => false,
            'has_all_details'=> true,
        ),
        'google_login_alert' => array(
            'subject'        => 'New Login via Google Detected — ' . $site_name,
            'title'          => 'Security Alert',
            'message'        => 'We detected a new login to your account via Google. If this was you, you can safely ignore this email.',
            'note'           => 'If you did not log in, please secure your Google account immediately.',
            'has_otp'        => false,
            'has_details'    => false,
            'has_admin_data' => false,
        ),
        'login_alert' => array(
            'subject'        => 'New Login Detected — ' . $site_name,
            'title'          => 'Security Alert',
            'message'        => 'We detected a new login to your account using your password. If this was you, you can safely ignore this email.',
            'note'           => 'If you did not log in, please reset your password immediately.',
            'has_otp'        => false,
            'has_details'    => false,
            'has_admin_data' => false,
        ),
    );

    return isset( $configs[ $template_id ] ) ? $configs[ $template_id ] : null;
}
