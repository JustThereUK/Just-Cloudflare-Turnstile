<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Turnstile Shortcode for CF7.
 */
function jct_cf7_shortcode() {
	$id = wp_rand();
	ob_start();

	echo '<div class="cf7-jct-turnstile" style="margin-top: 0; margin-bottom: -15px;">';
	jct_field_show(
		'.wpcf7-submit',
		'turnstileCF7Callback',
		'contact-form-7',
		'-cf7-' . esc_attr( $id ),
		'jct-turnstile-cf7-' . esc_attr( $id )
	);
	echo '</div>';
	?>
	<script>
	document.addEventListener("DOMContentLoaded", function () {
		document.querySelectorAll('.wpcf7-form').forEach(function (form) {
			form.addEventListener('submit', function () {
				const widget = document.getElementById('jct-turnstile-cf7-<?php echo esc_js($id); ?>');
				if (widget) {
					setTimeout(function () {
						turnstile.reset('#jct-turnstile-cf7-<?php echo esc_js($id); ?>');
					}, 1500);
				}
			});
		});
	});
	</script>
	<?php
	return trim( preg_replace( '/\s+/', ' ', ob_get_clean() ) );
}
add_shortcode( 'cf7-simple-turnstile', 'jct_cf7_shortcode' );
add_filter( 'wpcf7_form_elements', 'do_shortcode' );

/**
 * Automatically insert Turnstile into all forms if enabled.
 */
if ( get_option( 'jct_cf7_all' ) ) {
	add_filter( 'wpcf7_form_elements', 'jct_field_cf7_auto', 10, 1 );
}

function jct_field_cf7_auto( $content ) {
	$jct_key = sanitize_text_field( get_option( 'jct_key' ) );
	if ( ! str_contains( $content, $jct_key ) ) {
		return preg_replace( '/(<input[^>]*type="submit")/i', jct_cf7_shortcode() . '<br/>$1', $content );
	}
	return $content;
}

/**
 * Validate CF7 Turnstile on form submission.
 */
add_filter( 'wpcf7_validate', 'jct_cf7_verify_turnstile', 20, 2 );

function jct_cf7_verify_turnstile( $result ) {
	if ( ! class_exists( 'WPCF7_Submission' ) ) {
		return $result;
	}

	$submission = WPCF7_Submission::get_instance();
	if ( empty( $submission ) ) {
		return $result;
	}

	$data     = $submission->get_posted_data();
	$form_id  = isset( $_POST['_wpcf7'] ) ? absint( $_POST['_wpcf7'] ) : 0;
	$cf7_html = do_shortcode( '[contact-form-7 id="' . $form_id . '"]' );

	// Skip if shortcode not in form and global insert disabled
	if ( ! get_option( 'jct_cf7_all' ) && ! str_contains( $cf7_html, sanitize_text_field( get_option( 'jct_key' ) ) ) ) {
		return $result;
	}

	if ( jct_whitelisted() ) {
		return $result;
	}

	if ( empty( $data['jct-turnstile-response'] ) ) {
		$result->invalidate( array( 'type' => 'captcha', 'name' => 'jct-turnstile' ), jct_failed_message() );
		return $result;
	}

	$check   = jct_check();
	$success = $check['success'] ?? false;

	if ( ! $success ) {
		$result->invalidate( array( 'type' => 'captcha', 'name' => 'jct-turnstile' ), jct_failed_message() );
	}

	return $result;
}

/**
 * Register the [cf7-simple-turnstile] tag.
 */
add_action( 'wpcf7_init', function () {
	wpcf7_add_form_tag( 'cf7-simple-turnstile', 'jct_cf7_shortcode' );
} );

/**
 * Add Turnstile Tag Generator to CF7 admin.
 */
add_action( 'wpcf7_admin_init', function () {
	if ( class_exists( 'WPCF7_TagGenerator' ) ) {
		$tag_generator = WPCF7_TagGenerator::get_instance();
		$tag_generator->add(
			'cf7-simple-turnstile',
			esc_html__( 'Cloudflare Turnstile', 'just-cloudflare-turnstile' ),
			'jct_cf7_tag_generator_button',
			array( 'version' => '2' )
		);
	}
}, 55 );

/**
 * Render the admin tag generator UI.
 */
function jct_cf7_tag_generator_button( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	?>
	<div class="insert-box">
		<input type="text" name="cf7-simple-turnstile" class="tag code" readonly="readonly" onfocus="this.select()" />
		<div class="submitbox">
			<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr__( 'Insert Tag', 'contact-form-7' ); ?>" />
		</div>
	</div>
	<?php
}
