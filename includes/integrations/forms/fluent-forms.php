<?php
namespace JCT\Integrations\Forms;

use JCT\Core\Whitelist;

defined('ABSPATH') || exit;

class FluentForms {
    public static function init() {
        error_log('[JCT] FluentForms::init() called.');
        if (!(defined('FLUENTFORM') || class_exists('FluentForm')) || Whitelist::is_whitelisted()) {
            return;
        }

        // Inject Turnstile widget before submit button (field-based)
        \add_filter('fluentform_rendering_field_data', [__CLASS__, 'inject_turnstile_field'], 10, 3);
        // Fallback: Render Turnstile above submit button for 100% placement control
        \add_action('fluentform/render_item_submit_button', [__CLASS__, 'render_turnstile_above_submit'], 5, 2);

        // Validate Turnstile on submission
        \add_action('fluentform_before_insert_submission', [__CLASS__, 'validate_turnstile'], 10, 3);
        \add_filter('fluentform_submit_validation', [__CLASS__, 'validate_turnstile_filter'], 10, 3);
    }

    public static function inject_turnstile_field($fields, $form, $form_id) {
        $settings = get_option('jct_settings', []);
        $site_key = $settings['site_key'] ?? '';
        $enabled = !empty($settings['enable_fluentforms']);
        if (!$enabled || !$site_key) return $fields;

        $turnstile_field = [
            'element' => 'custom_html',
            'settings' => [
                'label' => '',
                'html' => '<div class="cf-turnstile" data-sitekey="' . esc_attr($site_key) . '" 
                    data-theme="' . esc_attr($settings['theme'] ?? 'auto') . '" 
                    data-size="' . esc_attr($settings['widget_size'] ?? 'normal') . '" 
                    data-appearance="' . esc_attr($settings['appearance'] ?? 'always') . '"></div>'
            ]
        ];

        // Insert before the submit button
        foreach ($fields as $i => $field) {
            if (($field['element'] ?? '') === 'submit') {
                array_splice($fields, $i, 0, [$turnstile_field]);
                return $fields;
            }
        }
        // If no submit button found, append at end
        $fields[] = $turnstile_field;
        return $fields;
    }

    public static function validate_turnstile($insertData, $data, $form) {
        if (!self::is_valid_submission()) {
            \wp_die(self::get_error_message(), 403);
        }
    }

    public static function validate_turnstile_filter($errors, $formData, $form) {
        $token = $_POST['cf-turnstile-response'] ?? '';
        if (!self::validate_token($token)) {
            $errors['turnstile'] = \__('CAPTCHA validation failed. Please try again.', 'just-cloudflare-turnstile');
        }
        return $errors;
    }

    private static function is_valid_submission() {
        if (!isset($_POST['cf-turnstile-response'])) return false;

        $settings = \get_option('jct_settings', []);
        $secret = $settings['secret_key'] ?? '';
        $response = \sanitize_text_field($_POST['cf-turnstile-response']);
        $remoteip = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!$secret || !$response) return false;

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

    private static function validate_token($token) {
        $settings = \get_option('jct_settings', []);
        $secret = $settings['secret_key'] ?? '';
        $remoteip = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!$secret || !$token) return false;

        $verify = \wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret'   => $secret,
                'response' => $token,
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

    private static function get_error_message() {
        $settings = \get_option('jct_settings', []);
        $message = !empty($settings['error_message']) ? $settings['error_message'] : \__('Please complete the Turnstile challenge.', 'just-cloudflare-turnstile');
        return \apply_filters('jct_fluentforms_turnstile_error_message', \esc_html($message));
    }

    public static function render_turnstile_above_submit($item, $form) {
        $settings = get_option('jct_settings', []);
        $site_key = $settings['site_key'] ?? '';
        $enabled = !empty($settings['enable_fluentforms']);
        if (!$enabled || !$site_key) return;

        echo '<div class="cf-turnstile" data-sitekey="' . esc_attr($site_key) . '" 
            data-theme="' . esc_attr($settings['theme'] ?? 'auto') . '" 
            data-size="' . esc_attr($settings['widget_size'] ?? 'normal') . '" 
            data-appearance="' . esc_attr($settings['appearance'] ?? 'always') . '"></div>';
    }
}

FluentForms::init();
