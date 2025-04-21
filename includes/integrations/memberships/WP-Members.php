<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// WP-Members Register Check
if ( get_option( 'jct_register' ) ) {
    add_filter( 'wpmem_pre_validate_form', 'jct_wpmem_register_check', 10, 2 );

    /**
     * Validate Cloudflare Turnstile on WP-Members registration.
     *
     * @param array  $fields Submitted form fields.
     * @param string $tag    Form tag identifier (e.g., 'register').
     * @return array $fields Modified or unchanged fields.
     */
    function jct_wpmem_register_check( $fields, $tag ) {
        // Skip check for non-register forms
        if ( $tag !== 'register' ) {
            return $fields;
        }

        // Bypass for whitelisted users
        if ( jct_whitelisted() ) {
            return $fields;
        }

        // Check if already validated
        if ( ! session_id() ) {
            session_start();
        }
        if (
            isset( $_SESSION['jct_register_checked'] ) &&
            wp_verify_nonce( sanitize_text_field( $_SESSION['jct_register_checked'] ), 'jct_register_check' )
        ) {
            unset( $_SESSION['jct_register_checked'] );
            return $fields;
        }

        $error_message = jct_failed_message();

        // Ensure response exists
        if ( empty( $_POST['jct-turnstile-response'] ) ) {
            wp_die(
                '<p><strong>' . esc_html__( 'ERROR:', 'just-cloudflare-turnstile' ) . '</strong> ' . esc_html( $error_message ) . '</p>',
                'just-cloudflare-turnstile',
                array( 'response' => 403, 'back_link' => true )
            );
        }

        // Validate Turnstile
        $check   = jct_check( sanitize_text_field( $_POST['jct-turnstile-response'] ) );
        $success = $check['success'] ?? false;

        if ( ! $success ) {
            wp_die(
                '<p><strong>' . esc_html__( 'ERROR:', 'just-cloudflare-turnstile' ) . '</strong> ' . esc_html( $error_message ) . '</p>',
                'just-cloudflare-turnstile',
                array( 'response' => 403, 'back_link' => true )
            );
        }

        // Save session to skip future duplicate checks
        $_SESSION['jct_register_checked'] = wp_create_nonce( 'jct_register_check' );

        return $fields;
    }
}
