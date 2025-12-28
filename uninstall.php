<?php
/**
 * Uninstall Plugin
 *
 * Removes settings when the plugin is deleted.
 * Does NOT remove:
 * - Order Meta (History)
 * - Uploaded Receipts (Proof of purchase)
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete Plugin Options
delete_option('woocommerce_wocommerce_yape_peru_settings');
