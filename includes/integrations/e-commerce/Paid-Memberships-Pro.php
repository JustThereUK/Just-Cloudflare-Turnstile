<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Login Field
function jct_field_pmp_login( $output, $args ) {
    if ( function_exists( 'pmpro_getOption' ) ) {
        $login_page_id     = pmpro_getOption( 'login_page_id' );
        $current_page_id   = get_the_ID();

        if ( $current_page_id == $login_page_id && isset( $args['form_id'] ) && $args['form_id'] === 'loginform' ) {
            ob_start();
            jct_field_show( '#wp-submit', 'turnstilePMPLoginCallback', 'pmp-login', '-pmp-login' );
            $turnstile_html = ob_get_clean();
            return $output . $turnstile_html;
        }
    }
    return $output;
}

// Register Field
function jct_field_pmp_register() {
    jct_field_show( '#pmp_register_form .pmp-submit', 'turnstilePMPRegisterCallback', 'pmp-register', '-pmp-register' );
}

// Checkout Field
function jct_field_pmp_checkout() {
    $guest_only = get_option( 'jct_pmp_guest_only' );
    if ( ! $guest_only || ( $guest_only && ! is_user_logged_in() ) ) {
        jct_field_show( '', '', 'pmp-checkout', '-pmp-checkout' );
    }
}

// Paid Memberships Pro Checkout Validation
if ( get_option( 'jct_pmp_checkout' ) ) {
    add_action( 'pmpro_checkout_before_submit_button', 'jct_field_pmp_checkout', 10 );
    add_filter( 'pmpro_registration_checks', 'jct_pmp_checkout_check' );

    function jct_pmp_checkout_check() {
        $guest_only = get_option( 'jct_pmp_guest_only' );
        if ( ! $guest_only || ( $guest_only && ! is_user_logged_in() ) ) {
            $check = jct_check();
            if ( empty( $check['success'] ) ) {
                pmpro_setMessage( jct_failed_message(), 'pmpro_error' );
                do_action( 'jct_turnstile_failed_pmp_checkout', $check );
                return false;
            }
        }
        return true;
    }
}

// Paid Memberships Pro Login Validation
if ( get_option( 'jct_login' ) && ( empty( get_option( 'jct_tested' ) ) || get_option( 'jct_tested' ) === 'yes' ) ) {
    add_filter( 'login_form_middle', 'jct_field_pmp_login', 10, 2 );

    // Hooked into custom login failure detection
    add_action( 'jct_wp_login_failed', 'jct_pmp_error', 21 );
    function jct_pmp_error() {
        if ( isset( $_POST['pmpro_login_form_used'] ) ) {
            wp_die(
                '<p><strong>' . esc_html__( 'ERROR:', 'just-cloudflare-turnstile' ) . '</strong> ' . esc_html( jct_failed_message() ) . '</p>',
                'just-cloudflare-turnstile',
                array(
                    'response'  => 403,
                    'back_link' => true,
                )
            );
        }
    }
}

// Remove default reset check as PMP handles it differently
remove_action( 'lostpassword_post', 'jct_wp_reset_check', 10 );
