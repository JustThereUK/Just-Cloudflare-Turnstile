<?php
namespace JCT\Integrations\Ecommerce;

use JCT\Core\Whitelist;
use function add_action;
use function get_option;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function __;
use function wp_nonce_field;
use function wc_add_notice;
use function sanitize_text_field;
use function wp_remote_post;
use function is_wp_error;
use function wp_remote_retrieve_body;
use function apply_filters;

defined('ABSPATH') || exit;

class WooCommerce {

    /**
     * Initialize integration.
     */
    public static function init() {
        if (!function_exists('is_woocommerce') || \JCT\Core\Whitelist::is_whitelisted()) {
            return;
        }

        $settings = \get_option('jct_settings', []);

        // Only add hooks for enabled forms
        if (!empty($settings['wc_checkout_form'])) {
            \add_action('woocommerce_after_checkout_billing_form', [__CLASS__, 'render_widget']);
            \add_action('woocommerce_after_order_notes', [__CLASS__, 'render_widget']);
            \add_action('woocommerce_checkout_process', [__CLASS__, 'validate_turnstile']);
            \add_action('woocommerce_after_checkout_validation', [__CLASS__, 'validate_turnstile'], 10, 2);
        }
        if (!empty($settings['wc_login_form'])) {
            \add_action('woocommerce_login_form', [__CLASS__, 'render_widget']);
            \add_filter('woocommerce_login_errors', [__CLASS__, 'catch_errors']);
        }
        if (!empty($settings['wc_register_form'])) {
            \add_action('woocommerce_register_form', [__CLASS__, 'render_widget']);
            \add_action('woocommerce_register_post', [__CLASS__, 'validate_generic'], 9);
        }
        if (!empty($settings['wc_lostpassword_form'])) {
            \add_action('woocommerce_lostpassword_form', [__CLASS__, 'render_widget']);
            \add_action('woocommerce_reset_password_validation', [__CLASS__, 'validate_generic']);
        }
    }

    /**
     * Output the Turnstile widget.
     */
    public static function render_widget() {
        $settings = \get_option('jct_settings', []);
        $site_key = $settings['site_key'] ?? '';

        if (!$site_key) {
            echo '<p class="jct-warning">' . \esc_html( __( 'Cloudflare Turnstile site key is missing. Please configure it in plugin settings.', 'just-cloudflare-turnstile' ) ) . '</p>';
            return;
        }

        // Add a nonce field for CSRF protection
        if (function_exists('wp_nonce_field')) {
            \wp_nonce_field('jct_turnstile_action', 'jct_turnstile_nonce');
        }

        echo '<div class="cf-turnstile" data-sitekey="' . \esc_attr($site_key) . '" data-theme="' . \esc_attr($settings['theme'] ?? 'auto') . '" data-size="' . \esc_attr($settings['widget_size'] ?? 'normal') . '" data-appearance="' . \esc_attr($settings['appearance'] ?? 'always') . '"></div>';
    }

    /**
     * Validate Turnstile on checkout.
     */
    public static function validate_turnstile() {
        if (!self::is_valid_submission()) {
            \wc_add_notice(self::get_error_message(), 'error');
        }
    }

    /**
     * Validate on registration and password reset.
     */
    public static function validate_generic() {
        if (!self::is_valid_submission()) {
            \wc_add_notice(self::get_error_message(), 'error');
        }
    }

    /**
     * Validate on login.
     */
    public static function catch_errors($error) {
        if (!self::is_valid_submission()) {
            $error->add('turnstile_error', self::get_error_message());
        }
        return $error;
    }

    /**
     * Server-side verification of Turnstile.
     */
    private static function is_valid_submission(): bool {
        // Verify nonce for CSRF protection
        if (!isset($_POST['jct_turnstile_nonce']) || (function_exists('wp_verify_nonce') && !\wp_verify_nonce($_POST['jct_turnstile_nonce'], 'jct_turnstile_action'))) {
            return false;
        }
        if (!isset($_POST['cf-turnstile-response'])) {
            return false;
        }

        $settings = \get_option('jct_settings', []);
        $secret = $settings['secret_key'] ?? '';
        $response = \sanitize_text_field($_POST['cf-turnstile-response']);
        $remoteip = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!$secret || !$response) {
            return false;
        }

        $verify = \wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret'   => $secret,
                'response' => $response,
                'remoteip' => $remoteip,
            ],
        ]);

        if (\is_wp_error($verify)) {
            if (defined('WP_DEBUG') && WP_DEBUG) error_log('Turnstile verification error: ' . $verify->get_error_message());
            return false;
        }

        $data = json_decode(\wp_remote_retrieve_body($verify), true);
        return !empty($data['success']);
    }

    /**
     * Return the error message.
     */
    private static function get_error_message(): string {
        $settings = \get_option('jct_settings', []);
        $message = !empty($settings['error_message']) ? $settings['error_message'] : __( 'Please complete the Turnstile challenge.', 'just-cloudflare-turnstile' );
        $message = \apply_filters('jct_woocommerce_turnstile_error_message', $message);
        return \esc_html($message);
    }
}
