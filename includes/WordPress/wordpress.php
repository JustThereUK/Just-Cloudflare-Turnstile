<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared logic to display Turnstile field with unique ID.
 */
function jct_render_turnstile_field($selector, $callback, $action) {
    $unique_id = wp_rand();
    jct_field_show($selector, $callback, $action, '-' . $unique_id);
}

/**
 * Login Field
 */
function jct_field_login() {
    if (isset($_SESSION['jct_login_checked'])) {
        unset($_SESSION['jct_login_checked']);
    }

    if (get_option('jct_login_only')) {
        $current_path = wp_parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $login_path = wp_parse_url(wp_login_url(), PHP_URL_PATH);
        if ($current_path !== $login_path) {
            return;
        }
    }

    jct_render_turnstile_field('#wp-submit', 'turnstileWPCallback', 'wordpress-login');
}

/**
 * Register Field
 */
function jct_field_register() {
    jct_render_turnstile_field('#wp-submit', 'turnstileWPCallback', 'wordpress-register');
}

/**
 * Password Reset Field
 */
function jct_field_reset() {
    jct_render_turnstile_field('#wp-submit', 'turnstileWPCallback', 'wordpress-reset');
}

// Register all hooks if enabled
if (get_option('jct_login')) {
    add_action('login_form', 'jct_field_login');
    add_action('authenticate', 'jct_wp_login_check', 21, 1);
}

if (get_option('jct_register')) {
    add_action('register_form', 'jct_field_register');
    add_action('registration_errors', 'jct_wp_register_check', 10, 3);
}

if (get_option('jct_reset') && !is_admin()) {
    add_action('lostpassword_form', 'jct_field_reset');
    add_action('lostpassword_post', 'jct_wp_reset_check', 10, 1);
}

// WordPress Login Check
function jct_wp_login_check($user) {
    if (!isset($user->ID) || jct_skip_check()) {
        return $user;
    }

    if (!session_id()) {
        session_start();
    }

    if (isset($_SESSION['jct_login_checked']) && wp_verify_nonce(sanitize_text_field($_SESSION['jct_login_checked']), 'jct_login_check')) {
        return $user;
    }

    $check = jct_check();
    if (empty($check['success'])) {
        do_action('jct_wp_login_failed');
        return new WP_Error('jct_error', jct_failed_message());
    }

    $_SESSION['jct_login_checked'] = wp_create_nonce('jct_login_check');
    return $user;
}

// Clear login session after successful login
add_action('wp_login', function () {
    unset($_SESSION['jct_login_checked']);
}, 10, 2);

// WordPress Register Check
function jct_wp_register_check($errors, $login, $email) {
    if (jct_skip_check() || (is_user_logged_in() && current_user_can('manage_options'))) {
        return $errors;
    }

    $check = jct_check();
    if (empty($check['success'])) {
        $errors->add('jct_error', sprintf('<strong>%s</strong>: %s', __('ERROR', 'just-cloudflare-turnstile'), jct_failed_message()));
    }

    return $errors;
}

// Password Reset Check
function jct_wp_reset_check($validation_errors) {
    if (jct_skip_check()) return;

    $check = jct_check();
    if (empty($check['success'])) {
        $validation_errors->add('jct_error', jct_failed_message());
    }
}

// Comment Field & Validation (only if wpDiscuz not active)
if (get_option('jct_comment') && !cft_is_plugin_active('wpdiscuz/class.WpdiscuzCore.php')) {
    add_action('comment_form_after', function () {
        if (wp_doing_ajax()) {
            wp_print_scripts('jct');
            wp_print_styles('jct-css');
        }
    });

    add_action('comment_form_submit_button', 'jct_field_comment', 100, 2);
    add_action('pre_comment_on_post', 'jct_wp_comment_check');

    function jct_field_comment($submit_button, $args) {
        if (jct_whitelisted()) return $submit_button;

        do_action('jct_enqueue_scripts');

        $unique_id = wp_rand();
        $field_html = sprintf(
            '<span id="jct-turnstile-c-%d" class="jct-turnstile jct-turnstile-comments" data-action="wordpress-comment" data-callback="%s" data-sitekey="%s" data-theme="%s" data-language="%s" data-appearance="%s" data-size="%s"></span><br/>',
            $unique_id,
            get_option('jct_disable_button') ? 'turnstileCommentCallback' : '',
            esc_attr(get_option('jct_key')),
            esc_attr(get_option('jct_theme')),
            esc_attr(get_option('jct_language', 'auto')),
            esc_attr(get_option('jct_appearance', 'always')),
            esc_attr(get_option('jct_size', 'normal'))
        );

        $wrapper_start = $wrapper_end = '';
        if (get_option('jct_disable_button')) {
            $wrapper_start = '<span class="jct-turnstile-comment" style="pointer-events: none; opacity: 0.5;">';
            $wrapper_end = '</span>';
        }

        $script = <<<HTML
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.body.addEventListener("click", function (e) {
        if (e.target.matches(".comment-reply-link, #cancel-comment-reply-link")) {
            turnstile.reset(".comment-form .jct-turnstile");
        }
    });
    jQuery(document).ajaxComplete(function() {
        setTimeout(function() {
            turnstile.render("#jct-turnstile-c-$unique_id");
        }, 1000);
    });
});
</script>
HTML;

        return $field_html . $wrapper_start . $submit_button . $wrapper_end . jct_force_render("-c-$unique_id") . $script;
    }

    function jct_wp_comment_check($commentdata) {
        if (is_admin() || jct_whitelisted()) return $commentdata;

        $check = jct_check();
        if (empty($check['success'])) {
            wp_die('<p><strong>' . esc_html__('ERROR:', 'just-cloudflare-turnstile') . '</strong> ' . jct_failed_message() . '</p>', 'just-cloudflare-turnstile', ['response' => 403, 'back_link' => true]);
        }

        return $commentdata;
    }
}

/**
 * Utility: Determine whether to skip validation for various scenarios
 */
function jct_skip_check() {
    return
        (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) ||
        (defined('REST_REQUEST') && REST_REQUEST) ||
        (isset($_POST['edd_login_nonce']) && wp_verify_nonce(sanitize_text_field($_POST['edd_login_nonce']), 'edd-login-nonce')) ||
        (isset($_POST['edd_register_nonce']) && wp_verify_nonce(sanitize_text_field($_POST['edd_register_nonce']), 'edd-register-nonce')) ||
        (isset($_POST['woocommerce-register-nonce']) && wp_verify_nonce(sanitize_text_field($_POST['woocommerce-register-nonce']), 'woocommerce-register')) ||
        (isset($_POST['woocommerce-lost-password-nonce']));
}
