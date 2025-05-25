<?php
namespace JCT\Core;

defined('ABSPATH') || exit;

use function wp_verify_nonce;
use function get_option;
use function sanitize_text_field;
use function wp_remote_post;
use function is_wp_error;
use function wp_remote_retrieve_body;

class Turnstile_Validator {
    /**
     * Validate Turnstile challenge (nonce + token).
     * @param bool $require_nonce
     * @return bool
     */
    public static function is_valid_submission($require_nonce = true): bool {
        if ($require_nonce) {
            if (!isset($_POST['jct_turnstile_nonce']) || (function_exists('wp_verify_nonce') && !wp_verify_nonce($_POST['jct_turnstile_nonce'], 'jct_turnstile_action'))) {
                return false;
            }
        }
        if (!isset($_POST['cf-turnstile-response'])) {
            return false;
        }
        $settings = function_exists('get_option') ? get_option('jct_settings', []) : [];
        $secret = $settings['secret_key'] ?? '';
        $response = function_exists('sanitize_text_field') ? sanitize_text_field($_POST['cf-turnstile-response']) : $_POST['cf-turnstile-response'];
        $remoteip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$secret || !$response) {
            return false;
        }
        $verify = function_exists('wp_remote_post') ? wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret'   => $secret,
                'response' => $response,
                'remoteip' => $remoteip,
            ],
        ]) : false;
        if (!$verify || (function_exists('is_wp_error') && is_wp_error($verify))) {
            if (defined('WP_DEBUG') && WP_DEBUG && $verify && is_wp_error($verify)) error_log('Turnstile verification error: ' . $verify->get_error_message());
            return false;
        }
        $data = function_exists('wp_remote_retrieve_body') ? json_decode(wp_remote_retrieve_body($verify), true) : null;
        return !empty($data['success']);
    }

    /**
     * Validate a token directly (for integrations that only have the token, e.g. Fluent Forms filter).
     * @param string $token
     * @return bool
     */
    public static function validate_token($token): bool {
        $settings = function_exists('get_option') ? get_option('jct_settings', []) : [];
        $secret = $settings['secret_key'] ?? '';
        $remoteip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$secret || !$token) return false;
        $verify = function_exists('wp_remote_post') ? wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $remoteip,
            ],
        ]) : false;
        if (!$verify || (function_exists('is_wp_error') && is_wp_error($verify))) {
            if (defined('WP_DEBUG') && WP_DEBUG && $verify && is_wp_error($verify)) error_log('Turnstile verification error: ' . $verify->get_error_message());
            return false;
        }
        $data = function_exists('wp_remote_retrieve_body') ? json_decode(wp_remote_retrieve_body($verify), true) : null;
        return !empty($data['success']);
    }

    /**
     * Get the error message for Turnstile validation (shared utility).
     * @param string $context Optional context for filter hook.
     * @return string
     */
    public static function get_error_message($context = ''): string {
        $settings = function_exists('get_option') ? get_option('jct_settings', []) : [];
        $message = !empty($settings['error_message']) ? $settings['error_message'] : __('Please complete the Turnstile challenge.', 'just-cloudflare-turnstile');
        if ($context) {
            $filter = 'jct_' . $context . '_turnstile_error_message';
            $message = apply_filters($filter, $message);
        }
        return esc_html($message);
    }
}
