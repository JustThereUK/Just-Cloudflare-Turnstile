<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jct_woo_should_display($guest_only = false) {
	return ! $guest_only || ($guest_only && ! is_user_logged_in());
}

function jct_woo_render_field($selector, $callback, $action, $suffix, $extra_class = '') {
	$unique_id = wp_rand();
	jct_field_show($selector, $callback, "{$action}-{$unique_id}", "{$suffix}-{$unique_id}", $extra_class);
}

if (get_option('jct_woo_login')) {
	add_action('woocommerce_login_form', function() {
		if (get_option('jct_tested') === 'yes' || empty(get_option('jct_tested'))) {
			jct_woo_render_field('.woocommerce-form-login__submit', 'turnstileWooLoginCallback', 'woocommerce-login', 'woo-login', 'jct-woocommerce-login');
		}
	});

	if (!get_option('jct_login')) {
		add_action('authenticate', function($user) {
			if (
				is_wp_error($user) || 
				!isset($_POST['woocommerce-login-nonce']) || 
				defined('XMLRPC_REQUEST') || 
				defined('REST_REQUEST')
			) return $user;

			if (!session_id()) session_start();

			if (!empty($_SESSION['jct_login_checked']) && wp_verify_nonce(sanitize_text_field($_SESSION['jct_login_checked']), 'jct_login_check')) {
				return $user;
			}

			$check = jct_check();
			if (empty($check['success'])) {
				return new WP_Error('jct_error', jct_failed_message());
			}

			$_SESSION['jct_login_checked'] = wp_create_nonce('jct_login_check');
			return $user;
		}, 21, 1);

		add_action('wp_login', function() {
			unset($_SESSION['jct_login_checked']);
		}, 10, 2);
	}
}

if (get_option('jct_woo_register')) {
	add_action('woocommerce_register_form', function() {
		jct_woo_render_field('.woocommerce-form-register__submit', 'turnstileWooRegisterCallback', 'woocommerce-register', 'woo-register', 'jct-woocommerce-register');
	});

	if (!is_admin()) {
		add_action('woocommerce_register_post', function($username, $email, $validation_errors) {
			if (!is_checkout()) {
				$check = jct_check();
				if (empty($check['success'])) {
					$validation_errors->add('jct_error', jct_failed_message());
				}
			}
		}, 10, 3);
	}
}

if (get_option('jct_woo_reset')) {
	add_action('woocommerce_lostpassword_form', function() {
		jct_woo_render_field('.woocommerce-ResetPassword .button', 'turnstileWooResetCallback', 'woocommerce-reset', 'woo-reset', 'jct-woocommerce-reset');
	});

	add_action('lostpassword_post', function($errors) {
		if (isset($_POST['woocommerce-lost-password-nonce'])) {
			$check = jct_check();
			if (empty($check['success'])) {
				$errors->add('jct_error', jct_failed_message());
			}
		}
	}, 10, 1);
}

if (get_option('jct_woo_checkout')) {
	// Field display position
	$pos = get_option('jct_woo_checkout_pos', 'beforepay');
	$hook_map = array(
		'beforepay'    => ['woocommerce_review_order_before_payment', 'render_block_woocommerce/checkout-payment-block'],
		'afterpay'     => ['woocommerce_review_order_after_payment', 'render_block_woocommerce/checkout-payment-block'],
		'beforebilling'=> ['woocommerce_before_checkout_billing_form', 'render_block_woocommerce/checkout-contact-information-block'],
		'afterbilling' => ['woocommerce_after_checkout_billing_form', 'render_block_woocommerce/checkout-shipping-methods-block'],
		'beforesubmit' => ['woocommerce_review_order_before_submit', 'render_block_woocommerce/checkout-actions-block'],
	);

	if (isset($hook_map[$pos])) {
		add_action($hook_map[$pos][0], 'jct_field_checkout', 10);
		add_filter($hook_map[$pos][1], function($content) {
			ob_start();
			if ($GLOBALS['pos'] === 'beforepay' || $GLOBALS['pos'] === 'beforebilling') {
				jct_field_checkout();
			}
			echo $content;
			if ($GLOBALS['pos'] === 'afterpay') {
				jct_field_checkout();
			}
			return ob_get_clean();
		}, 999);
	}

	add_action('cfw_checkout_payment_method_tab', 'jct_field_checkout', 10);

	// Validate on checkout submit
	add_action('woocommerce_checkout_process', function() {
		if (!session_id()) session_start();

		// Skip based on method
		$method = $_POST['payment_method'] ?? '';
		$methods = get_option('jct_selected_payment_methods', []);
		if (in_array($method, $methods, true)) return;

		if (!empty($_SESSION['jct_checkout_checked']) && wp_verify_nonce(sanitize_text_field($_SESSION['jct_checkout_checked']), 'jct_checkout_check')) return;

		if (jct_woo_should_display(get_option('jct_guest_only'))) {
			$check = jct_check();
			if (empty($check['success'])) {
				wc_add_notice(jct_failed_message(), 'error');
			} else {
				$_SESSION['jct_checkout_checked'] = wp_create_nonce('jct_checkout_check');
			}
		}
	});

	// Checkout Block
	add_action('woocommerce_store_api_checkout_update_order_from_request', function($order, $request) {
		if ($request->get_method() !== 'POST') return;

		$method = sanitize_text_field($request->get_param('payment_method'));
		if (in_array($method, get_option('jct_selected_payment_methods', []), true)) return;

		if (!session_id()) session_start();
		if (!empty($_SESSION['jct_checkout_checked']) && wp_verify_nonce($_SESSION['jct_checkout_checked'], 'jct_checkout_check')) return;

		if (jct_woo_should_display(get_option('jct_guest_only'))) {
			$token = $request->get_param('extensions')['just-cloudflare-turnstile']['token'] ?? '';
			$check = jct_check($token);
			if (empty($check['success'])) {
				throw new \Exception(jct_failed_message());
			}
			$_SESSION['jct_checkout_checked'] = wp_create_nonce('jct_checkout_check');
		}
	}, 10, 2);

	// Clear session
	add_action('woocommerce_checkout_order_processed', 'jct_woo_checkout_clear', 10);
	add_action('woocommerce_store_api_checkout_order_processed', 'jct_woo_checkout_clear', 10);
	function jct_woo_checkout_clear() {
		unset($_SESSION['jct_checkout_checked']);
	}

	// Register endpoint for block checkout
	add_action('woocommerce_loaded', function() {
		woocommerce_store_api_register_endpoint_data([
			'endpoint'  => 'checkout',
			'namespace'=> 'just-cloudflare-turnstile',
			'schema_callback' => fn() => [
				'token' => [
					'description' => __('Turnstile token.', 'jct'),
					'type'        => 'string',
					'context'     => [],
				],
			],
		]);
	});
}

if (get_option('jct_woo_checkout_pay')) {
	add_action('woocommerce_pay_order_before_submit', 'jct_field_checkout', 10);
	add_action('woocommerce_before_pay_action', function($order) {
		$check = jct_check();
		if (empty($check['success'])) {
			wc_add_notice(jct_failed_message(), 'error');
		}
	}, 10);
}

function jct_field_checkout() {
	if (is_wc_endpoint_url('order-received')) return;

	if (jct_woo_should_display(get_option('jct_guest_only'))) {
		if (get_option('jct_woo_checkout_pos') === 'afterpay') echo "<br/>";
		jct_field_show('', '', 'woocommerce-checkout', '-woo-checkout');
	}
}

function jct_is_block_based_checkout() {
	if (!is_checkout()) return false;
	$page_id = wc_get_page_id('checkout');
	return has_block('woocommerce/checkout', get_post($page_id)->post_content ?? '');
}
