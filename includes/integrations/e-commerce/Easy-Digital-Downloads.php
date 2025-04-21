<?php
if (!defined('ABSPATH')) {
    exit;
}

// === Fields ===

// Login field
function jct_field_edd_login() {
    jct_field_show('#edd_login_submit', 'turnstileEDDLoginCallback', 'edd-login', '-edd-login');
}

// Register field
function jct_field_edd_register() {
    jct_field_show('#edd_register_form .edd-submit', 'turnstileEDDRegisterCallback', 'edd-register', '-edd-register');
}

// Checkout field
function jct_field_edd_checkout() {
    $guest_only = get_option('jct_edd_guest_only');
    if (!$guest_only || ($guest_only && !is_user_logged_in())) {
        jct_field_show('', '', 'edd-checkout', '-edd-checkout');
    }
}

if (get_option('jct_edd_checkout')) {
    add_action('edd_purchase_form_before_submit', 'jct_field_edd_checkout', 10);
    add_action('edd_pre_process_purchase', 'jct_edd_checkout_check');

    function jct_edd_checkout_check() {
        if (!session_id()) {
            session_start();
        }

        // Avoid rechecking if already validated
        if (isset($_SESSION['jct_edd_checkout_checked']) && wp_verify_nonce(sanitize_text_field($_SESSION['jct_edd_checkout_checked']), 'jct_edd_checkout')) {
            unset($_SESSION['jct_edd_checkout_checked']);
            return;
        }

        $guest_only = get_option('jct_edd_guest_only');

        if (!$guest_only || ($guest_only && !is_user_logged_in())) {
            if (!empty($_POST['edd-process-checkout-nonce']) && wp_verify_nonce(sanitize_text_field($_POST['edd-process-checkout-nonce']), 'edd-process-checkout')) {
                $check = jct_check();
                if (empty($check['success'])) {
                    edd_set_error('jct_error', jct_failed_message());
                    do_action('jct_turnstile_failed_edd_checkout', $check);
                } else {
                    $_SESSION['jct_edd_checkout_checked'] = wp_create_nonce('jct_edd_checkout');
                }
            }
        }
    }
}

if (get_option('jct_edd_login') && (empty(get_option('jct_tested')) || get_option('jct_tested') === 'yes')) {
    add_action('edd_login_fields_after', 'jct_field_edd_login');
    add_action('authenticate', 'jct_edd_login_check', 21, 1);

    function jct_edd_login_check($user) {
        if (!edd_is_checkout() && !jct_whitelisted()) {
            if (!empty($_POST['edd_login_nonce'])) {
                $check = jct_check();
                if (empty($check['success'])) {
                    wp_die('<p><strong>' . esc_html__('ERROR:', 'just-cloudflare-turnstile') . '</strong> ' . esc_html(jct_failed_message()) . '</p>', 'just-cloudflare-turnstile', array(
                        'response' => 403,
                        'back_link' => true,
                    ));
                }
            }
        }

        return $user;
    }
}

// Fallback login check for general logins triggered by EDD actions
add_filter('jct_wp_login_checks', 'jct_edd_default_login_check');
function jct_edd_default_login_check() {
    return function_exists('did_action') && (
        did_action('edd_purchase') ||
        did_action('edd_straight_to_gateway') ||
        did_action('edd_free_download_process')
    );
}

if (get_option('jct_edd_register')) {
    add_action('edd_register_form_fields_before_submit', 'jct_field_edd_register');
    add_action('edd_process_register_form', 'jct_edd_register_check');

    function jct_edd_register_check() {
        if (!edd_is_checkout() && !jct_whitelisted()) {
            $check = jct_check();
            if (empty($check['success'])) {
                edd_set_error('jct_error', jct_failed_message());
                do_action('jct_turnstile_failed_edd_register', $check);
            }
        }
    }
}
