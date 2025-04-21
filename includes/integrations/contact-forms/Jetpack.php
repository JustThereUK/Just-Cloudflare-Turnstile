<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( get_option( 'jct_jetpack' ) ) {

	/**
	 * Inject Cloudflare Turnstile field into Jetpack contact forms.
	 */
	add_filter( 'jetpack_contact_form_html', 'jct_field_jetpack_form', 10, 1 );
	function jct_field_jetpack_form( $html ) {
		$unique_id = wp_rand();

		// Generate Turnstile HTML
		ob_start();
		jct_field_show(
			'.wp-block-jetpack-contact-form button',
			'',
			'jetpack-form',
			'-jetpack-' . esc_attr( $unique_id )
		);
		$jct_html = ob_get_clean();

		// Attempt to place before <button> or fallback before </form>
		$insert_before = '<button class="wp-block-button__link" style="" data-id-attr="placeholder" type="submit">';
		$position = strpos( $html, $insert_before );
		if ( $position === false ) {
			$position = strpos( $html, '</form>' );
		}

		if ( $position !== false ) {
			$html = substr_replace( $html, $jct_html, $position, 0 );
		}

		return $html;
	}

	/**
	 * Validate Cloudflare Turnstile on Jetpack contact form submission.
	 */
	add_filter( 'jetpack_contact_form_is_spam', 'jct_jetpack_check', 10, 1 );
	function jct_jetpack_check( $default ) {
		$check   = jct_check();
		$success = $check['success'] ?? false;

		if ( $success ) {
			return $default;
		}

		$error_message = jct_failed_message();

		// Add error message + repopulate form data
		add_filter( 'jetpack_contact_form_html', function( $html ) use ( $error_message ) {
			$error_html = '<div class="contact-form__error" style="color: red; margin-bottom: 1em;">' . esc_html( $error_message ) . '</div>';

			// Rebuild form DOM to retain posted values
			if ( class_exists( 'DOMDocument' ) ) {
				libxml_use_internal_errors( true );
				$dom = new DOMDocument();
				$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

				foreach ( $dom->getElementsByTagName( 'input' ) as $input ) {
					$name = $input->getAttribute( 'name' );
					if ( isset( $_POST[ $name ] ) ) {
						$input->setAttribute( 'value', esc_attr( $_POST[ $name ] ) );
					}
				}

				foreach ( $dom->getElementsByTagName( 'textarea' ) as $textarea ) {
					$name = $textarea->getAttribute( 'name' );
					if ( isset( $_POST[ $name ] ) ) {
						$textarea->nodeValue = esc_html( $_POST[ $name ] );
					}
				}

				$html = $error_html . $dom->saveHTML();
				libxml_clear_errors();
			} else {
				// Fallback if DOMDocument is not available
				$html = $error_html . $html;
			}

			return $html;
		} );

		return new WP_Error( 'captcha_failed', $error_message );
	}
}
