<?php
/**
 * Uninstall Just Cloudflare Turnstile Plugin
 *
 * This file is executed when the plugin is deleted via the WordPress admin.
 */

// Exit if accessed directly or not via WordPress uninstall process
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load settings registration file (to access all defined options)
require_once plugin_dir_path(__FILE__) . 'inc/admin/register-settings.php';

// Check if user has chosen to remove all data on uninstall
if (get_option('jct_uninstall_remove')) {

    // Get all plugin settings keys
    $settings = jct_settings_list();

    // Remove each setting from the database
    foreach ((array) $settings as $setting) {
        delete_option($setting);
    }

    // Remove other plugin-specific options
    delete_option('jct_tested');
    delete_option('jct_uninstall_remove');
    delete_option('jct_log'); // Optional: also delete debug log if it exists
}
