<?php
/**
 * AuthMe Email Handler (Unified Controller)
 *
 * Sends all email notifications using WordPress wp_mail().
 * Uses config/mail-config.php for content and
 * mails/master-mail-template.php for layout.
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load configurations
require_once AUTHME_PLUGIN_DIR . 'config/mail-config.php';

class AuthMe_Email {

    /**
     * Send an email based on a specific template configuration.
     *
     * @param string $template_id  The ID of the template from mail-config.php.
     * @param string $to_email     Recipient email address.
     * @param array  $dynamic_data Optional dynamic data (otp_code, username, etc.).
     * @return bool                True if mail was sent successfully.
     */
    public function send_email( $template_id, $to_email, $dynamic_data = array() ) {

        $config = authme_get_email_config( $template_id );
        
        if ( ! $config ) {
            // Fallback or silently fail if template doesn't exist
            return false;
        }

        $subject = $config['subject'];

        /* Build the HTML email body from the master template */
        ob_start();
        $tpl = AuthMe_Assets_Loader::dir('tpl_master_mail');
        if ( $tpl && file_exists( $tpl ) ) {
            include $tpl;
        } else {
            // Emergency fallback if template is missing
            echo wp_kses_post( $config['message'] );
        }
        $body = ob_get_clean();

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        return wp_mail( $to_email, $subject, $body, $headers );
    }

    /* ── Backward Compatibility Wrappers (Optional but safer during refactoring) ── */

    public function send_otp_email( $to_email, $otp_code, $purpose = 'registration' ) {
        $template_id = 'registration_otp';
        if ( $purpose === 'password_reset' ) {
            $template_id = 'password_reset_otp';
        } elseif ( $purpose === 'host_request' ) {
            $template_id = 'host_request_otp';
        }

        return $this->send_email( $template_id, $to_email, array( 'otp_code' => $otp_code ) );
    }

    public function send_password_changed_email( $to_email, $user_name ) {
        return $this->send_email( 'password_changed', $to_email );
    }

    public function send_host_approved_email( $to_email, $username, $password ) {
        return $this->send_email( 'host_approved', $to_email, array(
            'username' => $username,
            'password' => $password,
        ) );
    }

    public function send_host_rejected_email( $to_email ) {
        return $this->send_email( 'host_rejected', $to_email );
    }

    public function send_admin_host_request_notification( $user_data ) {
        $admin_email = get_option( 'admin_email' );
        return $this->send_email( 'admin_host_request', $admin_email, array(
            'all_details' => $user_data,
        ) );
    }
}

