<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register Turnstile field on Ultimate Member forms
if ( get_option( 'jct_um_login' ) ) {
    add_action( 'um_after_login_fields', 'jct_field_um_turnstile' );
    add_action( 'um_submit_form_errors_hook_login', 'jct_um_validate_turnstile', 20, 1 );
}

if ( get_option( 'jct_um_register' ) ) {
    add_action( 'um_after_register_fields', 'jct_field_um_turnstile' );
    add_action( 'um_submit_form_errors_hook__registration', 'jct_um_validate_turnstile', 20, 1 );
}

if ( get_option( 'jct_um_password' ) ) {
    add_action( 'um_after_password_reset_fields', 'jct_field_um_turnstile' );
    add_action( 'um_reset_password_errors_hook', 'jct_um_validate_turnstile', 20, 1 );
}

/**
 * Render the Turnstile field for Ultimate Member forms.
 */
function jct_field_um_turnstile() {
    jct_field_show(
        '#um-submit-btn',
        'turnstileUMCallback',
        'ultimate-member',
        '-um-' . wp_rand()
    );
}

/**
 * Validate Turnstile on Ultimate Member form submission.
 *
 * @param array $args Submitted form arguments.
 */
function jct_um_validate_turnstile( $args ) {
    if ( jct_whitelisted() ) {
        return;
    }

    if ( ! session_id() ) {
        session_start();
    }

    // Already verified during this session
    if ( isset( $_SESSION['jct_login_checked'] ) && wp_verify_nonce( sanitize_text_field( $_SESSION['jct_login_checked'] ), 'jct_login_check' ) ) {
        unset( $_SESSION['jct_login_checked'] );
        return;
    }

    $message = jct_failed_message();

    // Missing or invalid Turnstile response
    if ( empty( $_POST['jct-turnstile-response'] ) ) {
        UM()->form()->add_error( 'jct', $message );
        return;
    }

    $check   = jct_check( sanitize_text_field( $_POST['jct-turnstile-response'] ) );
    $success = $check['success'] ?? false;

    if ( ! $success ) {
        UM()->form()->add_error( 'jct', $message );
    } else {
        $_SESSION['jct_login_checked'] = wp_create_nonce( 'jct_login_check' );
    }
}

/**
 * Clear session flag on successful Ultimate Member login.
 */
add_action( 'um_user_login', 'jct_um_clear_login_session', 10, 1 );
function jct_um_clear_login_session( $args ) {
    if ( isset( $_SESSION['jct_login_checked'] ) ) {
        unset( $_SESSION['jct_login_checked'] );
    }
}
