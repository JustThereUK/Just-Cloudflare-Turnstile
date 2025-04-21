<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Inject Turnstile into Clean Login forms.
 */
add_action( 'cleanlogin_after_login_form', 'jct_clean_login_field_login' );
add_action( 'cleanlogin_after_register_form', 'jct_clean_login_field_register' );
add_action( 'cleanlogin_after_resetpassword_form', 'jct_clean_login_field_reset' );

/**
 * Turnstile field for login form.
 */
function jct_clean_login_field_login() {
    $unique_id = wp_rand();
    jct_field_show(
        '#cleanlogin_submit',
        'turnstileCleanLoginCallback',
        'clean-login-login',
        '-cl-login-' . esc_attr( $unique_id )
    );
}

/**
 * Turnstile field for registration form.
 */
function jct_clean_login_field_register() {
    $unique_id = wp_rand();
    jct_field_show(
        '#cleanlogin_submit',
        'turnstileCleanRegisterCallback',
        'clean-login-register',
        '-cl-register-' . esc_attr( $unique_id )
    );
}

/**
 * Turnstile field for reset password form.
 */
function jct_clean_login_field_reset() {
    $unique_id = wp_rand();
    jct_field_show(
        '#cleanlogin_submit',
        'turnstileCleanResetCallback',
        'clean-login-reset',
        '-cl-reset-' . esc_attr( $unique_id )
    );
}
