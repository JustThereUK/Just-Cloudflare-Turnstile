<?php
namespace JCT\Integrations\WordPress;

use JCT\Core\Whitelist;

defined('ABSPATH') || exit;

class WP_Core {

    /**
     * Initialize hooks.
     */
    public static function init() {
        if (\JCT\Core\Whitelist::is_whitelisted()) {
            return;
        }

        // Add Turnstile widget
        \add_action('login_form', [__CLASS__, 'render_widget']);
        \add_action('register_form', [__CLASS__, 'render_widget']);
        \add_action('lostpassword_form', [__CLASS__, 'render_widget']);
        \add_action('comment_form_after_fields', [__CLASS__, 'render_widget']);
        \add_action('comment_form_logged_in_after', [__CLASS__, 'render_widget']);

        // Validate submission
        \add_filter('authenticate', [__CLASS__, 'validate_login'], 30, 3);
        \add_filter('registration_errors', [__CLASS__, 'validate_generic'], 30, 3);
        \add_action('reset_password_post', [__CLASS__, 'validate_reset'], 5);
        \add_filter('preprocess_comment', [__CLASS__, 'validate_comment']);
    }

    /**
     * Render the Turnstile widget HTML.
     */
    public static function render_widget() {
        $settings = \get_option('jct_settings', []);
        $site_key = $settings['site_key'] ?? '';

        if (!$site_key) {
            echo '<p class="jct-warning">' . \esc_html__( 'Cloudflare Turnstile site key is missing. Please configure it in plugin settings.', 'just-cloudflare-turnstile' ) . '</p>';
            return;
        }

        // Add a nonce field for CSRF protection
        wp_nonce_field('jct_turnstile_action', 'jct_turnstile_nonce');

        // Use current filter/action to determine context for unique ID
        global $wp_current_filter;
        $context = 'login';
        if (is_array($wp_current_filter)) {
            foreach ($wp_current_filter as $filter) {
                if (strpos($filter, 'register') !== false) {
                    $context = 'register';
                    break;
                } elseif (strpos($filter, 'lostpassword') !== false) {
                    $context = 'lostpassword';
                    break;
                } elseif (strpos($filter, 'comment') !== false) {
                    $context = 'comment';
                    break;
                }
            }
        }
        $unique_id = 'cf-turnstile-' . \esc_attr($context);

        // Always output the id attribute for the Turnstile div
        echo '<div id="' . $unique_id . '" class="cf-turnstile" data-sitekey="' . \esc_attr($site_key) . '" data-theme="' . \esc_attr($settings['theme'] ?? 'auto') . '" data-size="' . \esc_attr($settings['widget_size'] ?? 'normal') . '" data-appearance="' . \esc_attr($settings['appearance'] ?? 'always') . '"></div>';
    }

    /**
     * Validate Turnstile on login.
     */
    public static function validate_login($user, $username, $password) {
        if (!self::is_valid_submission()) {
            return new \WP_Error('turnstile_failed', self::get_error_message());
        }
        return $user;
    }

    /**
     * Validate on registration and lost password.
     */
    public static function validate_generic($errors) {
        if (!self::is_valid_submission()) {
            $errors->add('turnstile_failed', self::get_error_message());
        }
        return $errors;
    }

    /**
     * Validate on password reset.
     */
    public static function validate_reset($user) {
        if (!self::is_valid_submission()) {
            \wp_die(self::get_error_message(), 403);
        }
    }

    /**
     * Validate comment form.
     */
    public static function validate_comment($commentdata) {
        if (!self::is_valid_submission()) {
            \wp_die(self::get_error_message(), 403);
        }
        return $commentdata;
    }

    /**
     * Check if Turnstile challenge was passed.
     */
    private static function is_valid_submission(): bool {
        // Verify nonce for CSRF protection
        if (!isset($_POST['jct_turnstile_nonce']) || !wp_verify_nonce($_POST['jct_turnstile_nonce'], 'jct_turnstile_action')) {
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
            // Optionally log the error for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Turnstile verification error: ' . $verify->get_error_message());
            }
            return false;
        }

        $data = json_decode(\wp_remote_retrieve_body($verify), true);
        return !empty($data['success']);
    }

    /**
     * Return the custom or fallback error message.
     */
    private static function get_error_message(): string {
        $settings = \get_option('jct_settings', []);
        return !empty($settings['error_message']) ? \esc_html($settings['error_message']) : \esc_html__('Please complete the Turnstile challenge.', 'just-cloudflare-turnstile');
    }
}
