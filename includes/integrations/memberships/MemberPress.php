<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// MemberPress Login Field
if ( get_option( 'jct_login' ) ) {
    add_action( 'mepr-login-form-before-submit', 'jct_field_mepr_login' );
    function jct_field_mepr_login() {
        jct_field_show(
            '.mepr-submit',
            'turnstileMEPRCallback',
            'memberpress-login',
            '-mepr-login-' . wp_rand()
        );
    }
}

// MemberPress Register Field
if ( get_option( 'jct_mepr_register' ) ) {
    add_action( 'mepr-checkout-before-submit', 'jct_field_mepr_register', 10, 1 );
    function jct_field_mepr_register( $membership_ID ) {
        if ( jct_mepr_product_id_enabled( $membership_ID ) ) {
            jct_field_show(
                '.mepr-submit',
                'turnstileMEPRCallback',
                'memberpress-register',
                '-mepr-register-' . wp_rand()
            );
        }
    }
}

// Validate Turnstile on MemberPress Signup
if ( get_option( 'jct_mepr_register' ) ) {
    add_filter( 'mepr-validate-signup', 'jct_mepr_register_check', 20, 1 );
    function jct_mepr_register_check( $errors ) {
        $product_id = isset( $_POST['mepr_product_id'] ) ? sanitize_text_field( $_POST['mepr_product_id'] ) : '';

        // Skip if already validated
        if ( ! session_id() ) {
            session_start();
        }
        if ( isset( $_SESSION['jct_login_checked'] ) && wp_verify_nonce( sanitize_text_field( $_SESSION['jct_login_checked'] ), 'jct_login_check' ) ) {
            unset( $_SESSION['jct_login_checked'] );
            return $errors;
        }

        // Skip if whitelisted or product ID doesn't match
        if ( jct_whitelisted() || ( ! jct_mepr_product_id_enabled( $product_id ) ) ) {
            return $errors;
        }

        // Perform Turnstile check
        if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['jct-turnstile-response'] ) ) {
            $check = jct_check( $_POST['jct-turnstile-response'] );
            if ( empty( $check['success'] ) ) {
                $errors[] = jct_failed_message();
            } else {
                $_SESSION['jct_login_checked'] = wp_create_nonce( 'jct_login_check' );
            }
        } else {
            $errors[] = jct_failed_message();
        }

        return $errors;
    }
}

// Allow auto-login by removing additional login check
add_filter( 'mepr-auto-login', 'jct_mepr_allow_auto_login' );
function jct_mepr_allow_auto_login( $auto_login ) {
    if ( $auto_login ) {
        remove_action( 'authenticate', 'jct_wp_login_check', 21 );
    }
    return $auto_login;
}

/**
 * Check if a given MemberPress product ID is allowed (or all).
 *
 * @param string|int $product_id
 * @return bool
 */
function jct_mepr_product_id_enabled( $product_id ) {
    $product_id = (string) $product_id;
    $enabled_ids = get_option( 'jct_mepr_product_ids', '' );

    if ( empty( $enabled_ids ) ) {
        return true;
    }

    $allowed_ids = array_filter( array_map( 'trim', explode( "\n", str_replace( "\r", '', $enabled_ids ) ) ) );
    return in_array( $product_id, $allowed_ids, true );
}
