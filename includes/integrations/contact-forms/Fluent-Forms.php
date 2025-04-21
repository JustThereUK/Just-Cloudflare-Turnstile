<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( get_option( 'jct_fluent' ) ) {

	/**
	 * Attach Turnstile field to Fluent Forms.
	 */
	$render_hook = has_action( 'fluentform/render_item_submit_button' )
		? 'fluentform/render_item_submit_button'
		: 'fluentform_render_item_submit_button';

	add_action( $render_hook, 'jct_field_fluent_form', 10, 2 );

	/**
	 * Render the Turnstile field on Fluent Forms submit button.
	 *
	 * @param object $item
	 * @param object $form
	 */
	function jct_field_fluent_form( $item, $form ) {
		if ( ! isset( $form->id ) || jct_form_disable( $form->id, 'jct_fluent_disable' ) ) {
			return;
		}

		$unique_id = wp_rand();
		jct_field_show(
			'.fluentform .ff-btn-submit',
			'turnstileFluentCallback',
			'fluent-form-' . esc_attr( $form->id ),
			'-fluent-' . esc_attr( $unique_id ),
			'jct-turnstile-fluent-' . esc_attr( $form->id )
		);

		// Optional: Reset on click
		echo "<script>
			document.addEventListener('DOMContentLoaded', function () {
				const form = document.querySelector('.fluentform');
				if (form) {
					form.addEventListener('submit', function () {
						const widget = document.getElementById('jct-turnstile-fluent-" . esc_js( $form->id ) . "');
						if (widget) {
							setTimeout(function () {
								turnstile.reset('#jct-turnstile-fluent-" . esc_js( $form->id ) . "');
							}, 1500);
						}
					});
				}
			});
		</script>";
	}

	/**
	 * Validate Turnstile token during Fluent Forms submission.
	 */
	add_action( 'fluentform/before_insert_submission', 'jct_fluent_check', 10, 3 );

	/**
	 * Fluent Forms submission Turnstile check.
	 *
	 * @param array  $insertData
	 * @param array  $data
	 * @param object $form
	 */
	function jct_fluent_check( $insertData, $data, $form ) {
		if ( jct_whitelisted() || jct_form_disable( $form->id, 'jct_fluent_disable' ) ) {
			return;
		}

		$error_message = jct_failed_message();
		$response      = isset( $data['jct-turnstile-response'] ) ? sanitize_text_field( $data['jct-turnstile-response'] ) : '';

		if ( empty( $response ) ) {
			wp_die( esc_html( $error_message ), 'just-cloudflare-turnstile' );
		}

		$check   = jct_check( $response );
		$success = $check['success'] ?? false;

		if ( ! $success ) {
			wp_die( esc_html( $error_message ), 'just-cloudflare-turnstile' );
		}
	}
}
