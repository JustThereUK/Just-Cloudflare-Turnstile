<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode for manual field placement
add_shortcode('mc4wp-simple-turnstile', 'jct_mc4wp_shortcode');
function jct_mc4wp_shortcode() {
    ob_start();
    jct_field_show(
        '.mc4wp-form-fields input[type=submit]',
        'turnstileMC4WPCallback',
        'mc4wp',
        '-mc4wp'
    );
    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

// Validate Turnstile token on MC4WP form submission
add_action('mc4wp_form_errors', 'jct_mc4wp_register_check', 10, 2);
function jct_mc4wp_register_check($errors, $form) {
    // Skip for whitelisted users
    if (jct_whitelisted()) {
        return $errors;
    }

    $form_id = isset($form->ID) ? absint($form->ID) : 0;
    if (!$form_id) {
        return $errors;
    }

    $post = get_post($form_id);
    if (!$post || strpos($post->post_content, '[mc4wp-simple-turnstile]') === false) {
        return $errors; // No Turnstile field present
    }

    $token = isset($_POST['jct-turnstile-response'])
        ? sanitize_text_field($_POST['jct-turnstile-response'])
        : '';

    if (empty($token)) {
        $errors[] = 'cf_turnstile_error';
        return $errors;
    }

    $check = jct_check($token);
    if (empty($check['success']) || $check['success'] !== true) {
        $errors[] = 'cf_turnstile_error';
    }

    return $errors;
}

// Display a user-friendly error message on failure
add_filter('mc4wp_form_messages', 'jct_mc4wp_error_message');
function jct_mc4wp_error_message($messages) {
    $messages['cf_turnstile_error'] = jct_failed_message();
    return $messages;
}
