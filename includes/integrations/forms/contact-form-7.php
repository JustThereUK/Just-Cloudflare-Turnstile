<?php
// Contact Form 7 integration for Just Cloudflare Turnstile
namespace JCT\Integrations\Forms;

use JCT\Core\Whitelist;
use function add_action;
use function add_filter;
use function get_option;
use function esc_attr;
use function esc_html__;
use function wp_nonce_field;
use function sanitize_text_field;
use function wp_remote_post;
use function is_wp_error;
use function wp_remote_retrieve_body;
use function apply_filters;

defined('ABSPATH') || exit;

class ContactForm7 {
    public static function init() {
        if (!defined('WPCF7_VERSION') || Whitelist::is_whitelisted()) {
            return;
        }
        $settings = get_option('jct_settings', []);
        if (empty($settings['enable_cf7'])) {
            return;
        }
        // Inject widget before submit button
        add_filter('wpcf7_form_elements', [__CLASS__, 'inject_turnstile'], 20, 1);
        // Validate on submit
        add_action('wpcf7_validate', [__CLASS__, 'validate_turnstile'], 10, 2);
    }

    public static function inject_turnstile($content) {
        $settings = get_option('jct_settings', []);
        $site_key = $settings['site_key'] ?? '';
        if (!$site_key) return $content;
        $widget = '';
        if (function_exists('wp_nonce_field')) {
            ob_start();
            wp_nonce_field('jct_turnstile_action', 'jct_turnstile_nonce');
            $widget .= ob_get_clean();
        }
        $widget .= '<div class="cf-turnstile" data-sitekey="' . esc_attr($site_key) . '" data-theme="' . esc_attr($settings['theme'] ?? 'auto') . '" data-size="' . esc_attr($settings['widget_size'] ?? 'normal') . '" data-appearance="' . esc_attr($settings['appearance'] ?? 'always') . '"></div>';
        // Insert before submit button
        $content = preg_replace('/(<input[^>]+type=["\']submit["\'][^>]*>)/i', $widget . '$1', $content, 1);
        return $content;
    }

    public static function validate_turnstile($result, $tag) {
        if (!self::is_valid_submission()) {
            $result->invalidate('*', self::get_error_message());
        }
        return $result;
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
        $message = !empty($settings['error_message']) ? $settings['error_message'] : esc_html__('Please complete the Turnstile challenge.', 'just-cloudflare-turnstile');
        $message = apply_filters('jct_cf7_turnstile_error_message', $message);
        return esc_html($message);
    }
}

ContactForm7::init();
