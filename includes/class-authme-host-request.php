<?php
/**
 * AuthMe Host Request Handler
 *
 * Handles AJAX requests for the "Become a Host" feature,
 * including real-time validation and final submission.
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AuthMe_Host_Request {

    /**
     * Database table name.
     *
     * @var string
     */
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'host_request';
    }

    /* ──────────────────────────────────────── */

    /**
     * AJAX handler: Upload a host document (identity verification).
     * Saves file to server and returns the URL.
     */
    public function ajax_upload_host_document() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        if ( empty( $_FILES['document'] ) ) {
            wp_send_json_error( array( 'message' => 'No file uploaded.' ) );
        }

        $file = $_FILES['document'];

        // Initial validation
        if ( ! in_array( $file['type'], array( 'image/jpeg', 'image/jpg' ) ) ) {
            wp_send_json_error( array( 'message' => 'Only JPEG images are allowed.' ) );
        }

        if ( $file['size'] > 1048576 ) { // 1MB
            wp_send_json_error( array( 'message' => 'File size exceeds 1MB limit.' ) );
        }

        // Use WordPress's wp_handle_upload for secure processing
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $upload_overrides = array( 'test_form' => false );
        $movefile = wp_handle_upload( $file, $upload_overrides );

        if ( $movefile && ! isset( $movefile['error'] ) ) {
            // Register the file in the Media Library (so it shows in Media > Library)
            if ( ! function_exists( 'wp_insert_attachment' ) ) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }

            $attachment = array(
                'guid'           => $movefile['url'], 
                'post_mime_type' => $movefile['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $movefile['file'] ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id = wp_insert_attachment( $attachment, $movefile['file'] );

            // Generate metadata for the attachment (for thumbnails, etc.)
            $attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            // Mark as temporary for later cleanup if not submitted
            update_post_meta( $attach_id, '_authme_host_temp', time() );

            wp_send_json_success( array(
                'url' => $movefile['url'],
                'attachment_id' => $attach_id,
                'message' => 'File uploaded and registered successfully.'
            ) );
        } else {
            wp_send_json_error( array( 'message' => $movefile['error'] ) );
        }
    }

    /* ──────────────────────────────────────── */

    /**
     * AJAX handler: Check if username is available.
     * Reuses validation logic similar to regular registration.
     */
    public function ajax_check_host_username() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';

        if ( empty( $username ) ) {
            wp_send_json_error( array( 'message' => 'Username is required.' ) );
        }

        if ( ! preg_match( '/^[a-zA-Z][a-zA-Z0-9]{3,13}$/', $username ) ) {
            wp_send_json_error( array( 'message' => 'Username must be 4–14 alphanumeric characters and start with a letter.' ) );
        }

        if ( username_exists( $username ) ) {
            wp_send_json_error( array( 'message' => 'Username is not available.' ) );
        }

        if ( $this->is_value_in_pending_request( 'username', $username ) ) {
            wp_send_json_error( array( 'message' => 'This username is currently under review in an existing application.' ) );
        }

        wp_send_json_success( array( 'message' => 'Username available.' ) );
    }

    /* ──────────────────────────────────────── */

    /**
     * AJAX handler: Check if email is available.
     */
    public function ajax_check_host_email() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Valid email is required.' ) );
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => 'Email already exists.' ) );
        }

        if ( $this->is_value_in_pending_request( 'email', $email ) ) {
            wp_send_json_error( array( 'message' => 'This email is currently under review in an existing application.' ) );
        }

        wp_send_json_success( array( 'message' => 'Email available.' ) );
    }

    /* ──────────────────────────────────────── */

    /**
     * AJAX handler: Check if mobile number is available.
     */
    public function ajax_check_host_mobile() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';

        if ( empty( $mobile ) ) {
            wp_send_json_error( array( 'message' => 'Mobile number is required.' ) );
        }

        global $wpdb;
        // Check if this mobile number already exists in wp_usermeta
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'mobile_number' AND meta_value = %s LIMIT 1",
            $mobile
        ) );

        if ( $exists ) {
            wp_send_json_error( array( 'message' => 'Mobile number is already registered.' ) );
        }

        if ( $this->is_value_in_pending_request( 'mobile', $mobile ) ) {
            wp_send_json_error( array( 'message' => 'This mobile number is currently under review in an existing application.' ) );
        }

        wp_send_json_success( array( 'message' => 'Mobile number available.' ) );
    }

    /* ──────────────────────────────────────── */

    /**
     * AJAX handler: Submit final host request.
     * Expects a JSON string of user_data containing all info and base64 images.
     */
    public function ajax_submit_host_request() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        // Extract raw JSON data
        $user_data = isset( $_POST['user_data'] ) ? wp_unslash( $_POST['user_data'] ) : '';

        if ( empty( $user_data ) ) {
            wp_send_json_error( array( 'message' => 'No data provided.' ) );
        }

        // Validate that it's actually valid JSON by decoding it
        $decoded = json_decode( $user_data, true );
        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
            wp_send_json_error( array( 'message' => 'Invalid data format.' ) );
        }

        // Perform basic validations on the extracted data
        $username = isset( $decoded['username'] ) ? sanitize_user( $decoded['username'] ) : '';
        $email    = isset( $decoded['email'] ) ? sanitize_email( $decoded['email'] ) : '';

        if ( empty( $username ) || username_exists( $username ) ) {
            wp_send_json_error( array( 'message' => 'Username is invalid or already taken.' ) );
        }

        if ( empty( $email ) || ! is_email( $email ) || email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => 'Email is invalid or already exists.' ) );
        }

        // Insert into host_request table
        global $wpdb;
        $inserted = $wpdb->insert(
            $this->table_name,
            array(
                'user_data' => $user_data, // Storing the raw JSON payload
                'status'    => 'pending',
                'date'      => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s' )
        );

        if ( ! $inserted ) {
            wp_send_json_error( array( 'message' => 'Failed to submit application. Please try again.' ) );
        }

        // Application submitted successfully! Solidify the attachments.
        $files = isset( $decoded['documents'] ) ? $decoded['documents'] : array();
        foreach ( $files as $file ) {
            if ( ! empty( $file['attachment_id'] ) ) {
                delete_post_meta( $file['attachment_id'], '_authme_host_temp' );
            }
        }

        // Fetch the freshly saved data straight from DB to ensure integrity
        $inserted_id = $wpdb->insert_id;
        $saved_row = $wpdb->get_row( $wpdb->prepare( "SELECT user_data FROM {$this->table_name} WHERE id = %d", $inserted_id ) );
        $saved_decoded = json_decode( $saved_row->user_data, true );

        // Send Email Notification to Admin using DB data
        if ( class_exists( 'AuthMe_Email' ) ) {
            $email_handler = new AuthMe_Email();
            $admin_email_data = array();
            foreach ( $saved_decoded as $key => $val ) {
                if ( is_string( $val ) || is_numeric( $val ) ) {
                    $admin_email_data[$key] = sanitize_text_field( $val );
                }
            }
            $email_handler->send_admin_host_request_notification( $admin_email_data );
        }

        wp_send_json_success( array( 'message' => 'Application submitted successfully.' ) );
    }

    /* ──────────────────────────────────────── */

    /**
     * AJAX handler: Delete a host document from the server.
     */
    public function ajax_delete_host_document() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $attach_id = isset( $_POST['attachment_id'] ) ? intval( $_POST['attachment_id'] ) : 0;
        if ( ! $attach_id ) {
            wp_send_json_error( array( 'message' => 'Invalid attachment ID.' ) );
        }

        // Verify it was a temporary host upload before deleting
        $is_temp = get_post_meta( $attach_id, '_authme_host_temp', true );
        if ( ! $is_temp ) {
            wp_send_json_error( array( 'message' => 'Unauthorized deletion or file already processed.' ) );
        }

        if ( wp_delete_attachment( $attach_id, true ) ) {
            wp_send_json_success( array( 'message' => 'File deleted successfully.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to delete file from server.' ) );
        }
    }

    /* ──────────────────────────────────────── */

    /**
     * Cleanup orphaned documents that were never submitted.
     * Called via cron twice daily.
     */
    public function cleanup_orphaned_documents() {
        $args = array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 50,
            'meta_query'     => array(
                array(
                    'key'     => '_authme_host_temp',
                    'compare' => 'EXISTS',
                ),
            ),
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                wp_delete_attachment( $post->ID, true );
            }
        }
    }

    /* ──────────────────────────────────────── */

    /**
     * Cleanup rejected host requests older than 7 days.
     * Keeps the database and Media Library clean from old rejected data.
     * Called via cron twice daily.
     */
    public function cleanup_rejected_requests() {
        global $wpdb;

        // 1. Fetch targeted rejected requests first
        $rejected_requests = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, user_data FROM {$this->table_name} WHERE status = 'rejected' AND date < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        ) );

        if ( ! empty( $rejected_requests ) ) {
            foreach ( $rejected_requests as $row ) {
                $decoded = json_decode( $row->user_data, true );
                if ( ! empty( $decoded['documents'] ) ) {
                    foreach ( $decoded['documents'] as $doc ) {
                        // Delete the actual file from Media Library
                        if ( ! empty( $doc['attachment_id'] ) ) {
                            wp_delete_attachment( $doc['attachment_id'], true );
                        }
                    }
                }

                // 2. Delete the row from the table
                $wpdb->delete( $this->table_name, array( 'id' => $row->id ), array( '%d' ) );
            }
        }
    }

    /* ──────────────────────────────────────── */

    /**
     * Helper to check if a specific key-value pair exists in any non-rejected host request JSON data.
     * 
     * @param string $field The JSON key (e.g. 'username', 'email')
     * @param string $value The value to check for.
     * @return bool
     */
    private function is_value_in_pending_request( $field, $value ) {
        global $wpdb;
        // Search the stringified JSON payload strictly for '"field":"value"'
        $search = '%"' . $field . '":"' . $wpdb->esc_like( $value ) . '"%';
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE status != 'rejected' AND user_data LIKE %s",
            $search
        ) );
        return $count > 0;
    }

}
