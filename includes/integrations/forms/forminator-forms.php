<?php
// Forminator Forms integration for Just Cloudflare Turnstile
namespace JCT\Integrations\Forms;

use JCT\Core\Whitelist;
use JCT\Core\Turnstile_Validator;
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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Just_Cloudflare_Turnstile_Forminator_Integration {
    /**
     * Init Forminator integration.
     */
    public static function init() {
        if ( ! function_exists( 'forminator' ) || Whitelist::is_whitelisted() ) {
            return;
        }
        $settings = \get_option( 'jct_settings', [] );
        if ( empty( $settings['enable_forminator'] ) ) {
            return;
        }
        // Inject widget by replacing submit markup (reliable for AJAX)
        \add_filter( 'forminator_render_form_submit_markup', [ __CLASS__, 'inject_turnstile_markup' ], 10, 4 );
        // Validate on submit
        \add_filter( 'forminator_custom_form_submit_errors', [ __CLASS__, 'validate_turnstile' ], 10, 3 );
    }

    /**
     * Inject Turnstile widget into Forminator form submit markup.
     *
     * @param string $html Submit button HTML.
     * @param int $form_id Form ID.
     * @param int $post_id Post ID.
     * @param string $nonce Nonce.
     * @return string Modified submit markup.
     */
    public static function inject_turnstile_markup( $html, $form_id, $post_id, $nonce ) {
        $settings = get_option( 'jct_settings', [] );
        $site_key = $settings['site_key'] ?? '';
        if ( ! $site_key ) return $html;

        $widget_id = 'cf-turnstile-fmntr-' . esc_attr( $form_id );
        ob_start();
        // Add nonce field for CSRF protection
        if ( function_exists( 'wp_nonce_field' ) ) {
            wp_nonce_field( 'jct_turnstile_action', 'jct_turnstile_nonce' );
        }
        echo '<div id="' . $widget_id . '" class="cf-turnstile" '
            . 'data-sitekey="' . esc_attr( $site_key ) . '" '
            . 'data-theme="' . esc_attr( $settings['theme'] ?? 'auto' ) . '" '
            . 'data-size="' . esc_attr( $settings['widget_size'] ?? 'normal' ) . '" '
            . 'data-callback="turnstileForminatorCallback"></div>';
        echo '<script>\njQuery(document).ajaxComplete(function() {\n    setTimeout(function() {\n        var el = document.getElementById("' . $widget_id . '");\n        if (el && el.innerHTML.trim() === "") {\n            if (typeof turnstile !== "undefined") {\n                turnstile.remove("#' . $widget_id . '");\n                turnstile.render("#' . $widget_id . '");\n            }\n        }\n    }, 1000);\n});\nfunction turnstileForminatorCallback() {\n    document.querySelectorAll(".forminator-button, .forminator-button-submit").forEach(function(el){el.disabled=false;});\n}\n</script>';
        return ob_get_clean() . $html;
    }

    /**
     * Validate Turnstile response on Formnator form submission.
     *
     * @param array $submit_errors Existing submission errors.
     * @param int $form_id Form ID.
     * @param array $field_data_array Submitted field data.
     * @return array Modified submission errors.
     */
    public static function validate_turnstile( $submit_errors, $form_id, $field_data_array ) {
        if ( ! Turnstile_Validator::is_valid_submission() ) {
            $submit_errors[] = Turnstile_Validator::get_error_message('forminator');
        }
        return $submit_errors;
    }
}

Just_Cloudflare_Turnstile_Forminator_Integration::init();