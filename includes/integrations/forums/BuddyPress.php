<?php
if (!defined('ABSPATH')) {
    exit;
}

if (get_option('jct_bp_register')) {

    // Add Turnstile field to BuddyPress registration
    add_action('bp_before_registration_submit_buttons', 'jct_bp_render_field');
    function jct_bp_render_field() {
        jct_field_show('#buddypress #signup-form .submit', 'turnstileBPCallback', 'buddypress-register', '-bp-register');
    }

    // Validate Turnstile on registration
    add_action('bp_signup_validate', 'jct_bp_validate_turnstile');
    function jct_bp_validate_turnstile() {
        if (jct_whitelisted()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $token = isset($_POST['jct-turnstile-response']) ? sanitize_text_field($_POST['jct-turnstile-response']) : '';

        if (empty($token)) {
            jct_bp_die_error(__('No Turnstile token submitted.', 'just-cloudflare-turnstile'));
        }

        $check = jct_check();
        $success = isset($check['success']) ? $check['success'] : false;

        if (!$success) {
            do_action('jct_turnstile_failed_buddypress', $check); // Hook for logging/fallbacks
            jct_bp_die_error(jct_failed_message());
        }
    }

    /**
     * Helper: Show a styled error using wp_die for BuddyPress registration
     */
    function jct_bp_die_error($message) {
        wp_die(
            '<p><strong>' . esc_html__('ERROR:', 'just-cloudflare-turnstile') . '</strong> ' . esc_html($message) . '</p>',
            esc_html__('Registration Error', 'just-cloudflare-turnstile'),
            array(
                'response'  => 403,
                'back_link' => true,
            )
        );
    }
}
