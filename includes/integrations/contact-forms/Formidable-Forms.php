<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( get_option( 'jct_formidable' ) ) {

	/**
	 * Inject Turnstile field into Formidable Forms submit button.
	 */
	add_action( 'frm_submit_button_html', 'jct_field_formidable_form', 10, 2 );
	function jct_field_formidable_form( $button, $args ) {

		if ( ! isset( $args['form']->id ) || jct_form_disable( $args['form']->id, 'jct_formidable_disable' ) ) {
			return $button;
		}

		$form_id   = absint( $args['form']->id );
		$unique_id = wp_rand();

		ob_start();
		jct_field_show(
			'.frm_forms .frm_button_submit',
			'turnstileFormidableCallback',
			'formidable-form-' . $form_id,
			'-fmdble-' . esc_attr( $unique_id ),
			'jct-turnstile-formidable-' . $form_id
		);
		$widget = ob_get_clean();

		// Reset token after submit
		echo "<script>
			document.addEventListener('DOMContentLoaded', function () {
				let form = document.querySelector('.frm_forms');
				if (form) {
					form.addEventListener('submit', function () {
						let widget = document.getElementById('jct-turnstile-formidable-" . esc_js( $form_id ) . "');
						if (widget) {
							setTimeout(function () {
								turnstile.reset('#jct-turnstile-formidable-" . esc_js( $form_id ) . "');
							}, 1500);
						}
					});
				}
			});
		</script>";

		// Position setting
		$position = get_option( 'jct_formidable_pos', 'before' );
		return ( $position === 'after' ) ? $button . $widget : $widget . $button;
	}

	/**
	 * Validate Turnstile on Formidable form submission.
	 */
	add_action( 'frm_validate_entry', 'jct_formidable_check', 10, 2 );
	function jct_formidable_check( $errors, $values ) {
		if ( ! isset( $values['form_id'] ) || jct_form_disable( $values['form_id'], 'jct_formidable_disable' ) ) {
			return $errors;
		}

		$check   = jct_check();
		$success = $check['success'] ?? false;

		if ( ! $success ) {
			$errors['jct_error'] = jct_failed_message();
		}

		return $errors;
	}
}
