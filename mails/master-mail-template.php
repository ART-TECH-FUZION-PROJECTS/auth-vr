<?php
/**
 * AuthMe — Master Email Template
 *
 * Used for all email notifications. Driven by config/mail-config.php.
 *
 * Variables available:
 *   $config       — Array from authme_get_email_config()
 *   $dynamic_data — Array containing context-specific data (e.g., otp_code, username, etc.)
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$site_name = get_bloginfo( 'name' );
$site_url  = esc_url( home_url() );

// Fallback safety
if ( empty( $config ) || ! is_array( $config ) ) {
    $config = array(
        'title'          => 'Notification',
        'message'        => 'You have a new notification from ' . $site_name . '.',
        'note'           => '',
        'has_otp'        => false,
        'has_details'    => false,
        'has_admin_data' => false,
    );
}

$dynamic_data = isset( $dynamic_data ) && is_array( $dynamic_data ) ? $dynamic_data : array();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $config['title'] ); ?></title>
</head>
<body style="margin:0; padding:0; background-color:#FAFAFA; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; -webkit-font-smoothing:antialiased;">

    <!-- Wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#FAFAFA; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="500" cellpadding="0" cellspacing="0" style="max-width:500px; width:96%; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 12px 30px rgba(0,0,0,0.05);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#F15E74; padding:28px 20px; text-align:center;">
                            <h2 style="margin:0; color:#ffffff; font-size:1.2rem; font-weight:700; letter-spacing:0.5px;">
                                <?php echo esc_html( strtoupper( $site_name ) ); ?>
                            </h2>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px 14px; text-align:center;">
                            <!-- Title -->
                            <h1 style="margin:0 0 16px; color:#000000; font-size:1.2rem; font-weight:700;">
                                <?php echo esc_html( $config['title'] ); ?>
                            </h1>

                            <!-- Description -->
                            <p style="margin:0 0 32px; color:#5a6e7c; font-size:0.85rem; line-height:1.5;">
                                <?php echo wp_kses( $config['message'], array( 'strong' => array() ) ); ?>
                            </p>

                            <!-- OTP Block -->
                            <?php if ( ! empty( $config['has_otp'] ) && ! empty( $dynamic_data['otp_code'] ) ) : ?>
                            <div style="background-color:#FAFAFA; border:2px dashed #e2e8f0; border-radius:12px; padding:20px; margin:0 auto 32px; max-width:320px;">
                                <span style="color:#000000; font-size:36px; font-weight:700; letter-spacing:14px; margin-left:14px; display:inline-block;">
                                    <?php echo esc_html( $dynamic_data['otp_code'] ); ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <!-- Credentials Block -->
                            <?php if ( ! empty( $config['has_details'] ) && ! empty( $dynamic_data['username'] ) ) : ?>
                            <div style="background-color:#FAFAFA; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin:0 auto 32px; text-align:left; max-width:320px;">
                                <p style="margin:0 0 10px; font-size:0.9rem; color:#000;">
                                    <strong>Username:</strong> <?php echo esc_html( $dynamic_data['username'] ); ?>
                                </p>
                                <p style="margin:0; font-size:0.9rem; color:#000;">
                                    <strong>Password:</strong> <?php echo esc_html( $dynamic_data['password'] ); ?>
                                </p>
                            </div>
                            <?php endif; ?>

                            <!-- Admin Data Block -->
                            <?php if ( ! empty( $config['has_admin_data'] ) && ! empty( $dynamic_data['admin_data'] ) ) : ?>
                            <div style="background-color:#FAFAFA; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin:0 auto 32px; text-align:left; max-width:320px;">
                                <p style="margin:0 0 10px; font-size:0.9rem; color:#000;">
                                    <strong>Username:</strong> <?php echo esc_html( $dynamic_data['admin_data']['username'] ); ?>
                                </p>
                                <p style="margin:0 0 10px; font-size:0.9rem; color:#000;">
                                    <strong>Email:</strong> <?php echo esc_html( $dynamic_data['admin_data']['email'] ); ?>
                                </p>
                                <p style="margin:0; font-size:0.9rem; color:#000;">
                                    <strong>Mobile:</strong> <?php echo esc_html( $dynamic_data['admin_data']['mobile'] ); ?>
                                </p>
                            </div>
                            <?php endif; ?>

                            <!-- All Details Block -->
                            <?php if ( ! empty( $config['has_all_details'] ) && ! empty( $dynamic_data['all_details'] ) && is_array( $dynamic_data['all_details'] ) ) : ?>
                            <div style="background-color:#FAFAFA; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin:0 auto 32px; text-align:left; max-width:320px;">
                                <?php foreach ( $dynamic_data['all_details'] as $key => $value ) : ?>
                                    <?php if ( ! empty( $value ) ) : ?>
                                    <p style="margin:0 0 10px; font-size:0.9rem; color:#000;">
                                        <strong style="text-transform: capitalize;"><?php echo esc_html( str_replace( array('_', '-'), ' ', $key ) ); ?>:</strong> <?php echo esc_html( $value ); ?>
                                    </p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Note -->
                            <?php if ( ! empty( $config['note'] ) ) : ?>
                            <p style="margin:0; color:#5a6e7c; font-size:0.85rem;">
                                <?php echo esc_html( $config['note'] ); ?>
                            </p>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:24px 32px; background-color:#ffffff; border-top:1px solid #e9eef3; text-align:center;">
                            <p style="margin:0; color:#5a6e7c; font-size:0.8rem;">
                                &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
                                <a href="<?php echo $site_url; ?>" style="color:#5a6e7c; text-decoration:none;">
                                    <?php echo esc_html( strtoupper( $site_name ) ); ?>
                                </a>
                                — Powered by AuthMe
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
