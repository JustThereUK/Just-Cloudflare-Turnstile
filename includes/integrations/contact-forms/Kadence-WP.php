<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( get_option( 'jct_kadence' ) ) {

	/**
	 * Enqueue Turnstile JS and inject form field for Kadence Forms.
	 */
	add_action( 'wp_enqueue_scripts', 'jct_enqueue_kadence_script' );
	function jct_enqueue_kadence_script() {
		if ( jct_whitelisted() || ! ( is_page() || is_single() ) ) {
			return;
		}

		global $post;
		if ( empty( $post ) ) {
			return;
		}

		$content = $post->post_content;
		if ( has_block( 'kadence/advanced-form', $content ) || has_block( 'kadence/form', $content ) ) {

			// Prepare unique ID for field container
			$unique_id = wp_rand();

			// Capture Turnstile field output
			ob_start();
			jct_field_show(
				'.kb-submit-field .kb-button',
				'turnstileKadenceCallback',
				'kadence-form-' . esc_attr( $unique_id ),
				'-kadence-' . esc_attr( $unique_id )
			);
			$field_html = ob_get_clean();

			// Clean up field HTML for inline injection
			$field_html = preg_replace( '/<br\s*\/?>/i', '', $field_html );
			$field_html = preg_replace( '/<div class="jct-turnstile-failed-text.*?<\/div>/is', '', $field_html );
			$field_html = trim( preg_replace( '/\s+/', ' ', $field_html ) );

			// Enqueue and localize JS
			wp_enqueue_script(
				'jct-kadence',
				plugins_url( 'just-cloudflare-turnstile/js/integrations/kadence.js' ),
				array(),
				'1.1',
				true
			);

			wp_localize_script( 'jct-kadence', 'jctVars', array(
				'sitekey' => sanitize_text_field( get_option( 'jct_key' ) ),
				'field'   => $field_html,
			) );
		}
	}

	/**
	 * Validate Turnstile on Kadence form submission.
	 */
	add_action( 'kadence_blocks_form_verify_nonce', 'jct_kadence_check', 10, 1 );
	function jct_kadence_check( $nonce ) {
		if ( jct_whitelisted() ) {
			return $nonce;
		}

		$response = isset( $_POST['jct-turnstile-response'] ) ? sanitize_text_field( $_POST['jct-turnstile-response'] ) : '';

		if ( empty( $response ) ) {
			wp_die( esc_html__( 'Please verify that you are human.', 'just-cloudflare-turnstile' ), 'just-cloudflare-turnstile', array( 'response' => 403 ) );
		}

		$check   = jct_check( $response );
		$success = $check['success'] ?? false;

		if ( ! $success ) {
			wp_die( esc_html__( 'Failed CAPTCHA validation. Please try again.', 'just-cloudflare-turnstile' ), 'just-cloudflare-turnstile', array( 'response' => 403 ) );
		}

		return $nonce;
	}
}
