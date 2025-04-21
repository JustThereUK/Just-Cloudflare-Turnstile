<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Output Turnstile field for WP User Frontend forms
 */
function jct_field_wpuf() {
    jct_field_show('.wpuf-form input[type="submit"]', 'turnstileWPUFCallback', 'wp-user-frontend', '-' . wp_rand());
}

/**
 * Login Integration
 */
if ( get_option('jct_login') ) {
    add_action('wpuf_login_form_bottom', 'jct_field_wpuf');
}

/**
 * Register Integration
 */
if ( get_option('jct_wpuf_register') ) {
    add_action('wpuf_reg_form_bottom', 'jct_field_wpuf');
    add_action('wpuf_process_registration_errors', 'jct_wpuf_check_register', 10, 1);

    function jct_wpuf_check_register($validation_error) {
        if ( jct_whitelisted() ) {
            return $validation_error;
        }

        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $token = sanitize_text_field($_POST['jct-turnstile-response'] ?? '');

            if ( empty($token) ) {
                $validation_error->add('jct_error', jct_failed_message());
                return $validation_error;
            }

            $check = jct_check($token);
            if ( empty($check['success']) || $check['success'] !== true ) {
                $validation_error->add('jct_error', jct_failed_message());
            }
        }

        return $validation_error;
    }
}

/**
 * Password Reset Integration
 */
if ( get_option('jct_reset') ) {
    remove_action('lostpassword_post', 'jct_wp_reset_check', 10);
    add_action('lostpassword_post', 'jct_wpuf_check_reset', 20);

    function jct_wpuf_check_reset() {
        if ( jct_whitelisted() ) return;

        $token = sanitize_text_field($_POST['jct-turnstile-response'] ?? '');
        if ( empty($token) ) {
            wp_die(
                '<p><strong>' . esc_html__('ERROR:', 'just-cloudflare-turnstile') . '</strong> ' . jct_failed_message() . '</p>',
                'just-cloudflare-turnstile',
                array('response' => 403, 'back_link' => true)
            );
        }

        $check = jct_check($token);
        if ( empty($check['success']) || $check['success'] !== true ) {
            wp_die(
                '<p><strong>' . esc_html__('ERROR:', 'just-cloudflare-turnstile') . '</strong> ' . jct_failed_message() . '</p>',
                'just-cloudflare-turnstile',
                array('response' => 403, 'back_link' => true)
            );
        }
    }
}

/**
 * WP User Frontend Post Form Integration
 */
if ( get_option('jct_wpuf_forms') ) {
    add_action('wpuf_add_post_form_bottom', 'jct_field_wpuf_form');
    add_action('wpuf_add_post_validate', 'jct_wpuf_check', 20);

    function jct_field_wpuf_form() {
        ?>
        <li class="wpuf-el post_content">
            <div class="wpuf-label"></div>
            <div class="wpuf-fields">
                <?php jct_field_show('.wpuf-form input[type="submit"]', 'turnstileWPUFCallback', 'wp-user-frontend', '-' . wp_rand()); ?>
            </div>
        </li>
        <?php
    }

    function jct_wpuf_check() {
        if ( jct_whitelisted() ) return;

        $token = sanitize_text_field($_POST['jct-turnstile-response'] ?? '');
        if ( empty($token) ) {
            return jct_failed_message();
        }

        $check = jct_check($token);
        if ( empty($check['success']) || $check['success'] !== true ) {
            return jct_failed_message();
        }

        return;
    }
}
