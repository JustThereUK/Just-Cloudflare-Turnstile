<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( get_option( 'jct_forminator' ) ) {

	/**
	 * Add Turnstile field to Forminator Forms.
	 */
	add_filter( 'forminator_render_form_submit_markup', 'jct_field_forminator_form', 10, 4 );
	function jct_field_forminator_form( $html, $form_id, $post_id, $nonce ) {
		$form_id = absint( $form_id );

		if ( jct_form_disable( $form_id, 'jct_forminator_disable' ) ) {
			return $html;
		}

		ob_start();

		// Ensure Turnstile script is loaded
		if ( ! wp_script_is( 'jct', 'enqueued' ) ) {
			wp_register_script(
				'jct',
				'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit',
				array(),
				null,
				true
			);
			wp_print_scripts( 'jct' );
		}

		// Add styling
		echo '<style>#jct-turnstile-fmntr-' . esc_attr( $form_id ) . ' { margin-left: 0 !important; }</style>';

		// Show the Turnstile field
		jct_field_show(
			'.forminator-button-submit',
			'turnstileForminatorCallback',
			'forminator-form-' . $form_id,
			'-fmntr-' . esc_attr( $form_id ),
			'jct-turnstile-fmntr-' . $form_id
		);

		?>
		<script>
			// Re-render Turnstile after AJAX refresh
			jQuery(document).ajaxComplete(function () {
				setTimeout(function () {
					const container = document.getElementById('jct-turnstile-fmntr-<?php echo esc_js( $form_id ); ?>');
					if (container && !container.innerHTML.trim()) {
						turnstile.remove('#jct-turnstile-fmntr-<?php echo esc_js( $form_id ); ?>');
						turnstile.render('#jct-turnstile-fmntr-<?php echo esc_js( $form_id ); ?>');
					}
				}, 1000);
			});

			// Re-render on submit
			jQuery(document).ready(function () {
				jQuery('.forminator-custom-form').on('submit', function () {
					const widget = document.getElementById('jct-turnstile-fmntr-<?php echo esc_js( $form_id ); ?>');
					if (widget) {
						setTimeout(function () {
							turnstile.remove('#jct-turnstile-fmntr-<?php echo esc_js( $form_id ); ?>');
							turnstile.render('#jct-turnstile-fmntr-<?php echo esc_js( $form_id ); ?>');
						}, 1000);
					}
				});
			});

			// Turnstile callback
			function turnstileForminatorCallback() {
				document.querySelectorAll('.forminator-button, .forminator-button-submit').forEach(function (btn) {
					btn.style.pointerEvents = 'auto';
					btn.style.opacity = '1';
				});
			}
		</script>
		<?php

		$jct = ob_get_clean();

		$position = get_option( 'jct_forminator_pos', 'before' );
		return ( $position === 'after' ) ? $html . $jct : $jct . $html;
	}

	/**
	 * Validate Forminator Forms submission via Turnstile.
	 */
	add_action( 'forminator_custom_form_submit_errors', 'jct_forminator_check', 10, 3 );
	function jct_forminator_check( $submit_errors, $form_id, $field_data_array ) {
		if ( jct_form_disable( $form_id, 'jct_forminator_disable' ) ) {
			return $submit_errors;
		}

		$check   = jct_check();
		$success = $check['success'] ?? false;

		if ( ! $success ) {
			$submit_errors[]['submit'] = jct_failed_message();
		}

		return $submit_errors;
	}
}
