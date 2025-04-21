<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Show admin notice if Turnstile is not currently displaying on forms.
 */
add_action('admin_notices', 'jct_admin_turnstile_error_notice');
function jct_admin_turnstile_error_notice() {
    // Only show notice outside the JCT settings page
    if (isset($_GET['page']) && $_GET['page'] === 'jct') {
        return;
    }

    $key    = get_option('jct_key');
    $secret = get_option('jct_secret');
    $tested = get_option('jct_tested');

    if (!empty($key) && !empty($secret) && $tested === 'no') {
        $settings_url = esc_url(admin_url('options-general.php?page=jct'));
        echo '<div class="notice notice-error is-dismissible">';
        echo wp_kses_post(
            sprintf(
                '<p>%s</p>',
                sprintf(
                    __('Cloudflare Turnstile is currently not showing on your forms. <a href="%s">Test API response on the settings page</a>.', 'just-cloudflare-turnstile'),
                    $settings_url
                )
            )
        );
        echo '</div>';
    }
}

/**
 * Returns the custom or default Turnstile failure message.
 *
 * @param string $default Optional fallback message.
 * @return string
 */
function jct_failed_message($default = '') {
    $custom = sanitize_text_field(get_option('jct_error_message'));
    $message = !empty($custom) ? $custom : $default;
    
    if (empty($message)) {
        $message = __('Please verify that you are human.', 'just-cloudflare-turnstile');
    }

    /**
     * Filter the Turnstile failure message.
     *
     * @param string $message Final error message.
     */
    return apply_filters('jct_failed_message', $message);
}

/**
 * Maps Turnstile error codes to readable messages.
 *
 * @param string $code Error code from API response.
 * @return string
 */
function jct_error_message($code) {
    $messages = array(
        'missing-input-secret'   => __('The secret parameter was not passed.', 'just-cloudflare-turnstile'),
        'invalid-input-secret'   => __('The secret parameter is invalid or missing.', 'just-cloudflare-turnstile'),
        'missing-input-response' => __('The response parameter was not passed.', 'just-cloudflare-turnstile'),
        'invalid-input-response' => __('The response parameter is invalid or expired.', 'just-cloudflare-turnstile'),
        'bad-request'            => __('The request was malformed or rejected.', 'just-cloudflare-turnstile'),
        'timeout-or-duplicate'   => __('This response has already been validated.', 'just-cloudflare-turnstile'),
        'internal-error'         => __('An internal error occurred. Please try again.', 'just-cloudflare-turnstile'),
    );

    /**
     * Filter Turnstile error messages.
     *
     * @param array $messages Keyed array of error codes and messages.
     */
    $messages = apply_filters('jct_turnstile_error_messages', $messages);

    return isset($messages[$code]) ? esc_html($messages[$code]) : esc_html__(
        'There was an error verifying Turnstile. Please check your keys or try again.',
        'just-cloudflare-turnstile'
    );
}
