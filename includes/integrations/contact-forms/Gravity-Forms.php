<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( get_option( 'jct_gravity' ) ) {

	/**
	 * Register shortcode: [gravity-simple-turnstile id="form_id"]
	 */
	add_shortcode( 'gravity-simple-turnstile', 'jct_gravity_shortcode' );
	function jct_gravity_shortcode( $atts ) {
		$form_id = isset( $atts['id'] ) ? absint( $atts['id'] ) : 0;
		if ( ! $form_id ) {
			return '';
		}

		ob_start();
		$unique_id = wp_rand();

		echo '<div class="gf-turnstile-container">';
		echo jct_field_show(
			'.gform_button',
			'turnstileGravityCallback',
			'gravity-form-' . esc_attr( $form_id ),
			'-gf-' . esc_attr( $unique_id ),
			'jct-turnstile-gf-' . esc_attr( $form_id )
		);
		echo '</div>';

		?>
		<style>
			.gf-turnstile-container { width: 100%; }
			.gform_footer.top_label { display: flex; flex-wrap: wrap; }
		</style>
		<script>
			document.addEventListener("DOMContentLoaded", function () {
				document.addEventListener('gform/post_render', function rerenderTurnstile(event) {
					if (event.detail.formId !== <?php echo esc_js( $form_id ); ?>) return;

					const target = document.getElementById('jct-turnstile-gf-<?php echo esc_js( $form_id ); ?>');
					if (target) {
						turnstile.remove('#jct-turnstile-gf-<?php echo esc_js( $form_id ); ?>');
						turnstile.render('#jct-turnstile-gf-<?php echo esc_js( $form_id ); ?>');
					}

					document.removeEventListener('gform/post_render', rerenderTurnstile);
				});
			});
		</script>
		<?php

		return trim( preg_replace( '/\s+/', ' ', ob_get_clean() ) );
	}

	/**
	 * Add Turnstile field before or after Gravity Forms submit button.
	 */
	add_action( 'gform_submit_button', 'jct_field_gravity_form', 10, 2 );
	function jct_field_gravity_form( $button, $form ) {
		$form_id = absint( $form['id'] );
		if ( jct_form_disable( $form_id, 'jct_gravity_disable' ) ) {
			return $button;
		}

		$tag = '[gravity-simple-turnstile id="' . esc_attr( $form_id ) . '"]';
		$position = get_option( 'jct_gravity_pos', 'before' );

		return $position === 'after' ? $button . do_shortcode( $tag ) : do_shortcode( $tag ) . $button;
	}

	/**
	 * Validate Turnstile response on Gravity Forms submission.
	 */
	add_action( 'gform_pre_submission', 'jct_gravity_check', 10 );
	function jct_gravity_check( $form ) {
		$form_id = absint( $form['id'] );

		if ( jct_whitelisted() || jct_form_disable( $form_id, 'jct_gravity_disable' ) ) {
			return $form;
		}

		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && ! empty( $_POST['jct-turnstile-response'] ) ) {
			$check   = jct_check();
			$success = $check['success'] ?? false;

			if ( ! $success ) {
				wp_die(
					'<p><strong>' . esc_html__( 'ERROR:', 'just-cloudflare-turnstile' ) . '</strong> ' . jct_failed_message() . '</p>',
					'just-cloudflare-turnstile',
					array( 'response' => 403, 'back_link' => true )
				);
			}
		} else {
			wp_die(
				'<p><strong>' . esc_html__( 'ERROR:', 'just-cloudflare-turnstile' ) . '</strong> ' . jct_failed_message() . '</p>',
				'just-cloudflare-turnstile',
				array( 'response' => 403, 'back_link' => true )
			);
		}

		return $form;
	}
}
