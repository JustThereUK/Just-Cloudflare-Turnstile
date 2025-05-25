<?php
// Gravity Forms integration for Just Cloudflare Turnstile
namespace JCT\Integrations\Forms;

use JCT\Core\Whitelist;
use JCT\Core\Turnstile_Validator;
use function add_filter;
use function add_action;
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

class GravityForms {
    public static function init() {
        if (!class_exists('GFForms') || Whitelist::is_whitelisted()) {
            return;
        }
        $settings = get_option('jct_settings', []);
        if (empty($settings['enable_gravityforms'])) {
            return;
        }
        // Inject widget before submit button
        add_filter('gform_submit_button', [__CLASS__, 'inject_turnstile'], 10, 2);
        // Validate on submit
        add_filter('gform_validation', [__CLASS__, 'validate_turnstile'], 10, 1);
    }

    public static function inject_turnstile($button, $form) {
        $settings = get_option('jct_settings', []);
        $site_key = $settings['site_key'] ?? '';
        if (!$site_key) return $button;
        $widget = '';
        if (function_exists('wp_nonce_field')) {
            ob_start();
            wp_nonce_field('jct_turnstile_action', 'jct_turnstile_nonce');
            $widget .= ob_get_clean();
        }
        $widget .= '<div class="cf-turnstile" data-sitekey="' . esc_attr($site_key) . '" data-theme="' . esc_attr($settings['theme'] ?? 'auto') . '" data-size="' . esc_attr($settings['widget_size'] ?? 'normal') . '" data-appearance="' . esc_attr($settings['appearance'] ?? 'always') . '"></div>';
        return $widget . $button;
    }

    public static function validate_turnstile($validation_result) {
        if (!Turnstile_Validator::is_valid_submission()) {
            $validation_result['is_valid'] = false;
            $validation_result['form']['failed_validation'] = true;
            $validation_result['form']['validation_message'] = Turnstile_Validator::get_error_message('gravityforms');
        }
        return $validation_result;
    }
}

GravityForms::init();