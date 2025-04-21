<?php
if (!defined('ABSPATH')) exit;

/**
 * Show Cloudflare Turnstile widget
 */
function jct_field_show($button_id = '', $callback = '', $form_name = '', $unique_id = '', $class = '') {
	if (apply_filters('jct_widget_disable', false) || jct_whitelisted()) return;

	do_action('jct_enqueue_scripts');
	do_action('jct_before_field', esc_attr($unique_id));

	$key        = sanitize_text_field(get_option('jct_key'));
	$theme      = sanitize_text_field(get_option('jct_theme'));
	$language   = sanitize_text_field(get_option('jct_language')) ?: 'auto';
	$appearance = sanitize_text_field(get_option('jct_appearance', 'always'));
	$size       = sanitize_text_field(get_option('jct_size', 'normal'));

	$callback_attr = get_option('jct_disable_button') ? ' data-callback="' . esc_attr($callback) . '"' : '';
	$error_callback = get_option('jct_failure_message_enable') ? ' data-callback="jctCallback" data-error-callback="jctErrorCallback"' : '';

	echo sprintf(
		'<div id="jct-turnstile%s" class="jct-turnstile %s" %s data-sitekey="%s" data-theme="%s" data-language="%s" data-size="%s" data-retry="auto" data-retry-interval="1000" data-action="%s" %s data-appearance="%s"></div>',
		esc_attr($unique_id),
		esc_attr($class),
		$callback_attr,
		esc_attr($key),
		esc_attr($theme),
		esc_attr($language),
		esc_attr($size),
		esc_attr($form_name),
		$error_callback,
		esc_attr($appearance)
	);

	do_action('jct_after_field', esc_attr($unique_id), $button_id);
}

/**
 * Disable submit button styles
 */
add_action('jct_after_field', function($unique_id, $button_id) {
	if ($button_id && get_option('jct_disable_button')) {
		echo "<style>$button_id { pointer-events: none; opacity: 0.5; }</style>";
	}
}, 10, 2);

/**
 * Auto margin for Turnstile widgets
 */
add_action('jct_after_field', function($unique_id) {
	if (get_option('jct_appearance') === 'always' || !get_option('jct_appearance')) {
		echo '<br class="jct-turnstile-br jct-turnstile-br' . esc_attr($unique_id) . '">';
	} else {
		echo '<style>#jct-turnstile' . esc_html($unique_id) . ' iframe { margin-bottom: 15px; }</style>';
	}
}, 15);

/**
 * Styles if not on normal pages
 */
add_action('jct_after_field', function($unique_id) {
	if (defined('DOING_AJAX') || is_admin()) return;
	if ((!is_page() && !is_single() && !(function_exists('is_checkout') && is_checkout())) || strpos($_SERVER['PHP_SELF'], 'wp-login.php') !== false) {
		echo '<style>#jct-turnstile' . esc_html($unique_id) . ' { margin-left: -15px; }</style>';
	}
}, 20);

/**
 * Optional failed message display
 */
add_action('jct_after_field', function($unique_id) {
	if (function_exists('jct_is_block_based_checkout') && jct_is_block_based_checkout()) return;

	if (get_option('jct_failure_message_enable')) {
		$failed_message = get_option('jct_failure_message') ?: __('Failed to verify you are human. Please contact us if you are having issues.', 'just-cloudflare-turnstile');
		echo '<div class="jct-turnstile-failed-text jct-turnstile-failed-text' . esc_attr($unique_id) . '"></div>';
		echo "<script>
			function jctErrorCallback() {
				document.querySelector('.jct-turnstile-failed-text" . esc_js($unique_id) . "').innerHTML = '<p><i>" . esc_js($failed_message) . "</i></p>';
			}
			function jctCallback() {
				document.querySelector('.jct-turnstile-failed-text" . esc_js($unique_id) . "').innerHTML = '';
			}
		</script>";
	}
}, 5);

/**
 * Force Turnstile re-render
 */
add_action('jct_after_field', function($unique_id) {
	if (function_exists('jct_is_block_based_checkout') && jct_is_block_based_checkout()) return;
	$key = sanitize_text_field(get_option('jct_key'));
	echo "<script>document.addEventListener('DOMContentLoaded',function(){setTimeout(function(){var e=document.getElementById('jct-turnstile" . esc_js($unique_id) . "');if(e&&!e.innerHTML.trim()){turnstile.remove('#jct-turnstile" . esc_js($unique_id) . "');turnstile.render('#jct-turnstile" . esc_js($unique_id) . "',{sitekey:'" . esc_js($key) . "'});}},0);});</script>";
});

/**
 * Validate Turnstile token
 */
function jct_check($token = '') {
	if (jct_whitelisted() || apply_filters('jct_widget_disable', false)) return ['success' => true];

	if (empty($token) && isset($_POST['jct-turnstile-response'])) {
		$token = sanitize_text_field($_POST['jct-turnstile-response']);
	}

	$key    = sanitize_text_field(get_option('jct_key'));
	$secret = sanitize_text_field(get_option('jct_secret'));

	if (!$key || !$secret) return false;

	$response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
		'body' => ['secret' => $secret, 'response' => $token]
	]);

	$body    = json_decode(wp_remote_retrieve_body($response));
	$success = $body->success ?? false;
	$errors  = $body->{'error-codes'} ?? [];

	$result = ['success' => $success];
	if (!$success && !empty($errors)) {
		$result['error_code'] = $errors[0];
		if ($errors[0] === 'invalid-input-secret') {
			update_option('jct_tested', 'no');
		}
	}

	do_action('jct_after_check', $body, $result);
	return $result;
}

/**
 * Logging failed attempts (if enabled)
 */
add_action('jct_after_check', function($response, $results) {
	if (!get_option('jct_log_enable')) return;

	$log = get_option('jct_log') ?: [];
	$log[] = [
		'date'    => current_time('mysql'),
		'success' => $results['success'],
		'error'   => $results['error_code'] ?? '',
		'ip'      => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
		'page'    => esc_url_raw($_SERVER['REQUEST_URI'] ?? '')
	];
	if (count($log) > 50) array_shift($log);
	update_option('jct_log', $log);
}, 10, 2);

/**
 * Disable Turnstile for specific form IDs
 */
function jct_form_disable($id, $option) {
	$disabled_ids = explode(',', preg_replace('/\s+/', '', get_option($option, '')));
	return in_array($id, $disabled_ids);
}

/**
 * Simple Turnstile shortcode
 */
add_shortcode('simple-turnstile', function() {
	ob_start();
	jct_field_show();
	$content = ob_get_clean();
	return trim(preg_replace('/\s+/', ' ', $content));
});
