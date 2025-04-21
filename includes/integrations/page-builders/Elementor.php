<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!get_option('jct_elementor')) {
    return;
}

// Add Turnstile to Elementor Login & Form Widgets
add_filter('elementor/widget/render_content', 'jct_elementor_add_turnstile', 10, 2);
function jct_elementor_add_turnstile($content, $widget) {
    $allowed_widgets = ['form', 'login'];
    $widget_name = $widget->get_name();

    if (!in_array($widget_name, $allowed_widgets, true)) {
        return $content;
    }

    static $processed = [];
    $widget_id = $widget->get_id();

    if (in_array($widget_id, $processed, true)) {
        return $content;
    }
    $processed[] = $widget_id;

    $position = get_option('jct_elementor_pos', 'before'); // Default to before
    $unique_id = wp_rand();

    ob_start();
    echo '<div class="elementor-turnstile-field" style="margin-top:10px;width:100%;">';
    jct_field_show('', 'turnstileElementorCallback', 'elementor-' . $unique_id, '-elementor-' . $unique_id);
    echo '</div>';
    $turnstile_html = ob_get_clean();

    $submit_btn_pattern = '/(<button[^>]*type="submit"[^>]*>.*?<\/button>)/is';
    preg_match($submit_btn_pattern, $content, $matches);
    $submit_btn = $matches[0] ?? '';

    // Inject Turnstile field based on position
    if ($submit_btn) {
        if ($position === 'after') {
            $content = str_replace($submit_btn, $submit_btn . $turnstile_html, $content);
        } else {
            $content = str_replace($submit_btn, $turnstile_html . $submit_btn, $content);
        }
    } else {
        // Fallback: inject before form closing tag
        $content = str_replace('</form>', $turnstile_html . '</form>', $content);
    }

    return $content;
}

// Reset Turnstile after submission
add_action('elementor-pro/forms/pre_render', 'jct_elementor_enqueue_script', 10, 2);
function jct_elementor_enqueue_script($instance, $form) {
    if (!wp_script_is('jct', 'enqueued')) {
        $defer = get_option('jct_defer_scripts', 1) ? ['strategy' => 'defer'] : [];
        wp_enqueue_script('jct', 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit', [], null, $defer);
    }

    ?>
    <script>
    jQuery(document).ready(function () {
        jQuery(".elementor-form").on("submit", function () {
            const $form = jQuery(this);
            setTimeout(() => {
                const $el = $form.find(".jct-turnstile");
                if ($el.length) {
                    const newId = "jct-turnstile-elementor-" + Date.now();
                    $el.attr("id", newId);
                    turnstile.reset("#" + newId);
                }
            }, 2000);
        });
    });
    </script>
    <?php if (get_option('jct_disable_button')) : ?>
    <style>
        .elementor-form[name="<?php echo esc_attr($instance['form_name']); ?>"] button[type=submit] {
            pointer-events: none;
            opacity: 0.5;
        }
    </style>
    <?php endif;
}

// Validation for Elementor Forms
add_action('elementor_pro/forms/validation', 'jct_elementor_validate_submission', 10, 2);
function jct_elementor_validate_submission($record, $ajax_handler) {
    if (jct_whitelisted()) {
        return;
    }

    $error = jct_failed_message();
    $token = $_POST['jct-turnstile-response'] ?? '';

    if (empty($token)) {
        $ajax_handler->add_error_message($error);
        $ajax_handler->is_success = false;
        return;
    }

    $check = jct_check($token);
    if (empty($check['success']) || $check['success'] !== true) {
        $ajax_handler->add_error_message($error);
        $ajax_handler->is_success = false;
    }
}
