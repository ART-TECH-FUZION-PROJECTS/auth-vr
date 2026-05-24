<?php

/**
 * AuthMe Toaster Notification Template
 *
 * Simple centered toast notification shown at the
 * top of the viewport. Controlled by toaster.js.
 *
 * @package AuthMe
 */

if (! defined('ABSPATH')) {
    exit;
}
?>

<!-- AuthMe Toast Notification -->
<div id="authme-toaster" class="authme-toaster" style="display: none;">
    <span class="authme-toaster-message" id="authme-toaster-message"></span>
    <button class="authme-toaster-close" id="authme-toaster-close" aria-label="Close">&times;</button>
</div>