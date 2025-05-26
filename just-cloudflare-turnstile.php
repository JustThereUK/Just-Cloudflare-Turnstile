<?php
/**
 * Plugin Name: Just Cloudflare Turnstile
 * Plugin URI: https://wordpress.org/plugins/just-cloudflare-turnstile
 * Description: Seamlessly integrate Cloudflare Turnstile with WordPress, WooCommerce, and Elementor forms.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.0
 * Author: Just There
 * Author URI: https://justthere.co.uk/
 * Support Us: https://justthere.co.uk/plugins/support-us/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: just-cloudflare-turnstile
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// Plugin Constants
define('JCT_VERSION', '1.0.0');
define('JCT_FILE', __FILE__);
define('JCT_PATH', plugin_dir_path(__FILE__));
define('JCT_URL', plugin_dir_url(__FILE__));
define('JCT_INCLUDES_PATH', JCT_PATH . 'includes/');
define('JCT_ASSETS_URL', JCT_URL . 'assets/');

// Autoload Core Loader
require_once JCT_INCLUDES_PATH . 'core/class-turnstile-loader.php';
require_once JCT_INCLUDES_PATH . 'admin/class-admin-options.php';
JCT\Admin\Admin_Options::init();
require_once JCT_INCLUDES_PATH . 'admin/class-settings-ui.php';
JCT\Admin\Settings_UI::init();

// Load plugin textdomain for translations
add_action('init', 'jct_load_textdomain');
function jct_load_textdomain() {
    load_plugin_textdomain('just-cloudflare-turnstile', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Initialize Plugin
add_action('plugins_loaded', 'jct_init_plugin');
function jct_init_plugin() {
    if (class_exists('JCT\\Core\\Turnstile_Loader')) {
        \JCT\Core\Turnstile_Loader::init();
    }
}

// Activation Hook
register_activation_hook(__FILE__, 'jct_activate_plugin');
function jct_activate_plugin() {
    set_transient('jct_do_activation_redirect', true, 30);
}

// Deactivation Hook
register_deactivation_hook(__FILE__, 'jct_deactivate_plugin');
function jct_deactivate_plugin() {
    // Placeholder: clean transient cache, if needed.
}

// Uninstall Hook (ensure uninstall.php is called)
register_uninstall_hook(__FILE__, 'jct_uninstall_plugin');
function jct_uninstall_plugin() {
    if (file_exists(JCT_PATH . 'uninstall.php')) {
        include JCT_PATH . 'uninstall.php';
    }
}
