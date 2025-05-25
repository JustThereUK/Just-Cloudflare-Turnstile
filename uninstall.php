<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Just Cloudflare Turnstile
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

/**
 * Remove all plugin data from the database.
 */
function jct_remove_plugin_data() {
    // Remove plugin settings from options table
    delete_option('jct_settings');
    
    // Remove any transients or user meta if used
    // delete_transient('jct_some_transient');
    // delete_user_meta(0, 'jct_some_user_meta');

    // Multisite support: remove settings from all sites
    if (is_multisite()) {
        global $wpdb;
        $site_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach ($site_ids as $site_id) {
            switch_to_blog($site_id);
            delete_option('jct_settings');
            // delete_transient('jct_some_transient');
            // delete_user_meta(0, 'jct_some_user_meta');
            restore_current_blog();
        }
    }
}

jct_remove_plugin_data();
