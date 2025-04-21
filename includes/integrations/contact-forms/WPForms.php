<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( get_option( 'jct_wpforms' ) ) {

	/**
	 * Inject Turnstile into WPForms.
	 */
	if ( get_option( 'jct_wpforms_pos' ) === 'after' ) {
		add_action( 'wpforms_display_submit_after', 'jct_render_wpforms_field', 10, 1 );
	} else {
		add_action( 'wpforms_display_submit_before', 'jct_render_wpforms_field', 10, 1 );
	}

	/**
	 * Display Turnstile field in WPForms submit area.
	 *
	 * @param array $form_data WPForms form data.
	 */
	function jct_render_wpforms_field( $form_data ) {
		if ( jct_form_disable( $form_data['id'], 'jct_wpforms_disable' ) ) {
			return;
		}

		$unique_id = wp_rand();

		if ( get_option( 'jct_wpforms_pos' ) === 'after' ) {
			echo '<div style="margin-top: 10px;"></div>';
		}

		jct_field_show(
			'.wpforms-submit',
			'turnstileWPFCallback',
			'wpforms-' . esc_attr( $form_data['id'] ),
			'-wpf-' . esc_attr( $unique_id )
		);
	}

	/**
	 * Validate Turnstile on WPForms submission.
	 *
	 * @param array $entry Entry data.
	 * @param array $form_data Form data.
	 */
	add_action( 'wpforms_process_before', 'jct_validate_wpforms', 10, 2 );
	function jct_validate_wpforms( $entry, $form_data ) {
		if ( jct_whitelisted() || jct_form_disable( $form_data['id'], 'jct_wpforms_disable' ) ) {
			return;
		}

		$form_id = (int) $form_data['id'];
		$token   = isset( $_POST['jct-turnstile-response'] ) ? sanitize_text_field( $_POST['jct-turnstile-response'] ) : '';

		if ( empty( $token ) ) {
			wpforms()->process->errors[ $form_id ]['header'] = jct_failed_message();
			return;
		}

		$check   = jct_check( $token );
		$success = $check['success'] ?? false;

		if ( ! $success ) {
			wpforms()->process->errors[ $form_id ]['header'] = jct_failed_message();
		}
	}
}
