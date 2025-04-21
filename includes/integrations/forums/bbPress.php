<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Turnstile to bbPress forms based on settings
 */
if (get_option('jct_bbpress_create')) {
    add_action('bbp_theme_before_topic_form_submit_wrapper', function () {
        jct_bbpress_field_output('create', '#bbp_topic_submit', 'turnstileBBPressCreateCallback');
    });
    add_action('bbp_new_topic_pre_extras', 'jct_bbpress_validate_response');
}

if (get_option('jct_bbpress_reply')) {
    add_action('bbp_theme_before_reply_form_submit_wrapper', function () {
        jct_bbpress_field_output('reply', '#bbp_reply_submit', 'turnstileBBPressReplyCallback');
    });
    add_action('bbp_new_reply_pre_extras', 'jct_bbpress_validate_response');
}

/**
 * Output the Turnstile field for bbPress
 *
 * @param string $type - 'create' or 'reply'
 * @param string $selector - CSS selector for target
 * @param string $callback - Callback JS function name
 */
function jct_bbpress_field_output($type, $selector, $callback) {
    $guest_only = get_option('jct_bbpress_guest_only');
    $align = get_option('jct_bbpress_align', 'left');

    if (!$guest_only || ($guest_only && !is_user_logged_in())) {
        $suffix = ($type === 'create') ? '-bb-create' : '-bb-reply';
        jct_field_show($selector, $callback, "bbpress-$type", $suffix);

        if ($align === 'right') {
            echo '<style>#bbpress-forums .jct-turnstile { float: right; }</style>';
        }
    }
}

/**
 * Validate Turnstile response for bbPress topic/reply
 */
function jct_bbpress_validate_response() {
    if (!jct_whitelisted()) {
        $guest_only = get_option('jct_bbpress_guest_only');

        if (!$guest_only || ($guest_only && !is_user_logged_in())) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $token = isset($_POST['jct-turnstile-response']) ? sanitize_text_field($_POST['jct-turnstile-response']) : '';
                if (empty($token)) {
                    bbp_add_error('jct_bbpress_error', jct_failed_message());
                    return;
                }

                $check = jct_check();
                if (empty($check['success']) || $check['success'] !== true) {
                    bbp_add_error('jct_bbpress_error', jct_failed_message());
                }
            }
        }
    }
}
