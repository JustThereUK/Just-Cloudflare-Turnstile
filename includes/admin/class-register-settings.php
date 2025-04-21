
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register plugin settings securely
 */
add_action('admin_init', 'jct_register_settings');
function jct_register_settings() {
    foreach (jct_settings_list() as $setting) {
        register_setting('jct-settings-group', $setting, [
            'sanitize_callback' => 'jct_sanitize_setting',
        ]);
    }
}

/**
 * Sanitize all registered settings
 * Add specific sanitizers per field if needed
 */
function jct_sanitize_setting($value) {
    if (is_array($value)) {
        return array_map('sanitize_text_field', $value);
    }
    return sanitize_text_field($value);
}

/**
 * Remove inactive settings on uninstall toggle
 */
add_action('sanitize_option_jct_uninstall_remove', 'jct_delete_inactive_settings');
function jct_delete_inactive_settings($value) {
    $all = jct_settings_list(true);
    $active = jct_settings_list();
    foreach (array_diff($all, $active) as $option) {
        delete_option($option);
    }
    return $value;
}

/**
 * List all plugin options for registration or deletion
 *
 * @param bool $include_inactive Include all known settings (used for deletion).
 * @return array
 */
function jct_settings_list($include_inactive = false) {
    $core = [
        'jct_setup', 'jct_key', 'jct_secret', 'jct_theme', 'jct_disable_button',
        'jct_error_message', 'jct_defer_scripts', 'jct_language', 'jct_appearance', 'jct_size',
        'jct_failure_message_enable', 'jct_failure_message', 'jct_login', 'jct_login_only',
        'jct_register', 'jct_register_only', 'jct_reset', 'jct_comment', 'jct_ajax_comments',
        'jct_whitelist_users', 'jct_whitelist_ips', 'jct_whitelist_agents',
        'jct_log_enable', 'jct_log', 'jct_uninstall_remove'
    ];

    // Plugin integration-based options
    $integrations = [
        'woocommerce/woocommerce.php' => [
            'jct_woo_login', 'jct_woo_register', 'jct_woo_reset',
            'jct_woo_checkout', 'jct_guest_only', 'jct_woo_checkout_pos',
            'jct_selected_payment_methods', 'jct_woo_checkout_pay',
        ],
        'easy-digital-downloads/easy-digital-downloads.php' => [
            'jct_edd_checkout', 'jct_edd_guest_only', 'jct_edd_login', 'jct_edd_register',
        ],
        'paid-memberships-pro/paid-memberships-pro.php' => [
            'jct_pmp_checkout', 'jct_pmp_guest_only', 'jct_pmp_login', 'jct_pmp_register',
        ],
        'contact-form-7/wp-contact-form-7.php' => ['jct_cf7_all'],
        'wpforms-lite/wpforms.php' => ['jct_wpforms', 'jct_wpforms_pos', 'jct_wpforms_disable'],
        'wpforms/wpforms.php' => ['jct_wpforms', 'jct_wpforms_pos', 'jct_wpforms_disable'],
        'fluentform/fluentform.php' => ['jct_fluent', 'jct_fluent_disable'],
        'jetpack/jetpack.php' => ['jct_jetpack', 'jct_jetpack_disable'],
        'formidable/formidable.php' => ['jct_formidable', 'jct_formidable_pos', 'jct_formidable_disable'],
        'forminator/forminator.php' => ['jct_forminator', 'jct_forminator_pos', 'jct_forminator_disable'],
        'gravityforms/gravityforms.php' => ['jct_gravity', 'jct_gravity_pos', 'jct_gravity_disable'],
        'buddypress/bp-loader.php' => ['jct_bp_register'],
        'bbpress/bbpress.php' => ['jct_bbpress_create', 'jct_bbpress_reply', 'jct_bbpress_guest_only', 'jct_bbpress_align'],
        'elementor-pro/elementor-pro.php' => ['jct_elementor', 'jct_elementor_pos'],
        'pro-elements/pro-elements.php' => ['jct_elementor', 'jct_elementor_pos'],
        'mailpoet/mailpoet.php' => ['jct_mailpoet'],
        'kadence-blocks/kadence-blocks.php' => ['jct_kadence'],
        'ultimate-member/ultimate-member.php' => ['jct_um_login', 'jct_um_register', 'jct_um_password'],
        'memberpress/memberpress.php' => ['jct_mepr_login', 'jct_mepr_register', 'jct_mepr_product_ids'],
        'wp-user-frontend/wpuf.php' => ['jct_wpuf_register', 'jct_wpuf_forms'],
    ];

    foreach ($integrations as $plugin => $options) {
        if ($include_inactive || cft_is_plugin_active($plugin)) {
            $core = array_merge($core, $options);
        }
    }

    return array_unique($core);
}

/**
 * Check if a plugin is active (multi-site compatible)
 *
 * @param string $plugin Path to plugin.
 * @return bool
 */
if (!function_exists('cft_is_plugin_active')) {
    function cft_is_plugin_active($plugin) {
        return in_array($plugin, (array) get_option('active_plugins', []), true)
            || (function_exists('cft_is_plugin_active_for_network') && cft_is_plugin_active_for_network($plugin));
    }
}

/**
 * Check if plugin is active on multisite network
 *
 * @param string $plugin
 * @return bool
 */
if (!function_exists('cft_is_plugin_active_for_network')) {
    function cft_is_plugin_active_for_network($plugin) {
        return is_multisite() && isset(get_site_option('active_sitewide_plugins')[$plugin]);
    }
}
