<?php
namespace JCT\Core;

defined('ABSPATH') || exit;

class Script_Handler {

    /**
     * Initialize hooks.
     */
    public static function init() {
        // Frontend and login pages
        \add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_public_assets']);
        \add_action('login_enqueue_scripts', [__CLASS__, 'enqueue_public_assets']);

        // Admin settings panel
        \add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);

        // Config for frontend and login page
        \add_action('wp_head', [__CLASS__, 'output_config_script'], 1);
        \add_action('login_head', [__CLASS__, 'output_config_script'], 1);
    }

    /**
     * Enqueue public-facing assets (frontend and login).
     */
    public static function enqueue_public_assets() {
        $settings = self::get_settings();

        // Turnstile script URL
        $url = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
        if (!empty($settings['language']) && $settings['language'] !== 'auto') {
            $url .= '&hl=' . \esc_attr($settings['language']);
        }
        // Allow filtering of the Turnstile script URL
        $url = \apply_filters('jct_turnstile_script_url', $url, $settings);

        \wp_enqueue_script('jct-turnstile', $url, [], null, true);

        // Defer Turnstile script if enabled
        if (!empty($settings['defer_scripts'])) {
            \add_filter('script_loader_tag', function ($tag, $handle) {
                if ('jct-turnstile' === $handle) {
                    // Use async instead of defer for Turnstile script
                    $tag = str_replace(' src', ' async src', $tag);
                }
                return $tag;
            }, 10, 2);
        }

        // Load frontend JS/CSS
        \wp_enqueue_script('jct-public', JCT_ASSETS_URL . 'js/public.js', ['jquery'], JCT_VERSION, true);
        \wp_enqueue_style('jct-public', JCT_ASSETS_URL . 'css/public.css', [], JCT_VERSION);
    }

    /**
     * Enqueue admin panel assets (only on plugin settings page).
     */
    public static function enqueue_admin_assets($hook) {
        if (strpos($hook, 'just-cloudflare-turnstile') === false) {
            return;
        }
        $admin_css = JCT_PATH . 'assets/css/admin.css';
        $admin_js = JCT_PATH . 'assets/js/admin.js';
        $css_ver = file_exists($admin_css) ? filemtime($admin_css) : JCT_VERSION;
        $js_ver = file_exists($admin_js) ? filemtime($admin_js) : JCT_VERSION;
        \wp_enqueue_style('jct-admin', JCT_ASSETS_URL . 'css/admin.css', [], $css_ver);
        \wp_enqueue_script('jct-admin', JCT_ASSETS_URL . 'js/admin.js', ['jquery'], $js_ver, true);
    }

    /**
     * Output config script for frontend and login forms.
     */
    public static function output_config_script() {
        $settings = self::get_settings();
        $config = [
            'disable_submit' => !empty($settings['disable_submit']),
            'appearance'     => $settings['appearance'] ?? 'always',
            'size'           => $settings['widget_size'] ?? 'normal',
            'theme'          => $settings['theme'] ?? 'auto',
            'extra_message'  => $settings['extra_message'] ?? '',
        ];
        echo '<script type="text/javascript">window.JCTConfig = ' . \wp_json_encode($config) . ';</script>';
    }

    /**
     * Retrieve plugin settings.
     */
    private static function get_settings() {
        return \get_option('jct_settings', []);
    }
}
