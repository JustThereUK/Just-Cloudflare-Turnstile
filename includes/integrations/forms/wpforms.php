<?php
// WPForms integration for Just Cloudflare Turnstile
namespace JCT\Integrations\Forms;

use JCT\Core\Whitelist;
use function add_action;
use function get_option;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function __;
use function wp_nonce_field;
use function wpforms;
use function sanitize_text_field;
use function wp_remote_post;
use function is_wp_error;
use function wp_remote_retrieve_body;
use function apply_filters;

defined('ABSPATH') || exit;

class WPForms {
    public static function init() {
        if (!class_exists('WPForms') || Whitelist::is_whitelisted()) {
            return;
        }
        // Add Turnstile widget to WPForms frontend forms (after fields and before submit for compatibility)
        add_action('wpforms_display_after_fields', [__CLASS__, 'render_widget'], 10, 2);
        add_action('wpforms_display_submit_before', [__CLASS__, 'render_widget'], 10, 2);
        // Validate Turnstile on submit
        add_action('wpforms_process', [__CLASS__, 'validate_turnstile'], 9, 3);
    }

    public static function render_widget($fields, $form_data = null) {
        $settings = get_option('jct_settings', []);
        $site_key = $settings['site_key'] ?? '';
        // Debug marker for output
        echo '<!-- JCT: WPForms Turnstile widget output (render_widget called) -->';
        if (!$site_key) {
            echo '<p class="jct-warning">' . esc_html__('Cloudflare Turnstile site key is missing. Please configure it in plugin settings.', 'just-cloudflare-turnstile') . '</p>';
            return;
        }
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field('jct_turnstile_action', 'jct_turnstile_nonce');
        }
        // Add a visible marker for debugging
        echo '<div class="cf-turnstile jct-wpforms-marker" data-sitekey="' . esc_attr($site_key) . '" data-theme="' . esc_attr($settings['theme'] ?? 'auto') . '" data-size="' . esc_attr($settings['widget_size'] ?? 'normal') . '" data-appearance="' . esc_attr($settings['appearance'] ?? 'always') . '"></div>';
    }

    public static function validate_turnstile($fields, $entry, $form_data) {
        if (!self::is_valid_submission()) {
            wpforms()->process->errors[$form_data['id']]['footer'] = self::get_error_message();
        }
    }

    private static function is_valid_submission() {
        if (!isset($_POST['jct_turnstile_nonce']) || (function_exists('wp_verify_nonce') && !wp_verify_nonce($_POST['jct_turnstile_nonce'], 'jct_turnstile_action'))) {
            return false;
        }
        if (!isset($_POST['cf-turnstile-response'])) {
            return false;
        }
        $settings = get_option('jct_settings', []);
        $secret = $settings['secret_key'] ?? '';
        $response = sanitize_text_field($_POST['cf-turnstile-response']);
        $remoteip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$secret || !$response) {
            return false;
        }
        $verify = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret'   => $secret,
                'response' => $response,
                'remoteip' => $remoteip,
            ],
        ]);
        if (is_wp_error($verify)) {
            if (defined('WP_DEBUG') && WP_DEBUG) error_log('Turnstile verification error: ' . $verify->get_error_message());
            return false;
        }
        $data = json_decode(wp_remote_retrieve_body($verify), true);
        return !empty($data['success']);
    }

    private static function get_error_message() {
        $settings = get_option('jct_settings', []);
        $message = !empty($settings['error_message']) ? $settings['error_message'] : __('Please complete the Turnstile challenge.', 'just-cloudflare-turnstile');
        $message = apply_filters('jct_wpforms_turnstile_error_message', $message);
        return esc_html($message);
    }
}

WPForms::init();
