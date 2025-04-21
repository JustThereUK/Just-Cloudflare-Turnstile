<?php
if (!defined('ABSPATH')) {
    exit;
}

if (get_option('jct_comment')) {

    add_action('wpdiscuz_before_comments', 'jct_wpdiscuz_enqueue_scripts');
    function jct_wpdiscuz_enqueue_scripts() {
        do_action('jct_enqueue_scripts');
    }

    add_action('wpdiscuz_submit_button_before', 'jct_wpdiscuz_render_field', 10, 3);
    function jct_wpdiscuz_render_field($currentUser, $uniqueId, $isMainForm) {
        $uniqueId = sanitize_key($uniqueId);
        $site_key = sanitize_text_field(get_option('jct_key'));
        $appearance = esc_attr(get_option('jct_appearance', 'always'));
        $size = esc_attr(get_option('jct_size', 'normal'));
        $disable_button = get_option('jct_disable_button');

        $field_id = "jct-turnstile-wpd-{$uniqueId}";
        $button_id = "wpd-field-submit-{$uniqueId}";

        ?>
        <script>
        jQuery(document).ready(function () {
            const targetSelector = '<?php echo ($uniqueId === "0_0") ? ".wpd_main_comm_form" : "#wpd-comm-<?php echo esc_js($uniqueId); ?>"; ?> .wpd-form-col-right .wc-field-submit';

            const fieldHtml = `
                <div id="<?php echo esc_js($field_id); ?>" class="wpdiscuz-jct" style="margin: 10px 0; display: inline-flex;"></div>
                <div style="clear: both;"></div>
            `;

            jQuery(targetSelector).before(fieldHtml);

            try {
                turnstile.remove('#<?php echo esc_js($field_id); ?>');
                turnstile.render('#<?php echo esc_js($field_id); ?>', {
                    sitekey: '<?php echo esc_js($site_key); ?>',
                    appearance: '<?php echo esc_js($appearance); ?>',
                    size: '<?php echo esc_js($size); ?>',
                    action: 'wpdiscuz-comment',
                    <?php if ($disable_button): ?>
                    callback: function (token) {
                        const btn = document.getElementById('<?php echo esc_js($button_id); ?>');
                        if (btn) {
                            btn.style.pointerEvents = 'auto';
                            btn.style.opacity = '1';
                        }
                    },
                    <?php endif; ?>
                });
            } catch (e) {
                console.error("Turnstile render error:", e);
            }

            // Reset Turnstile on click
            jQuery('#<?php echo esc_js($button_id); ?>').on('click', function () {
                const widget = document.getElementById('<?php echo esc_js($field_id); ?>');
                if (widget) {
                    setTimeout(() => {
                        turnstile.reset('#<?php echo esc_js($field_id); ?>');
                    }, 2000);
                }
            });

            <?php if ($disable_button): ?>
            document.getElementById('<?php echo esc_js($button_id); ?>').style.pointerEvents = 'none';
            document.getElementById('<?php echo esc_js($button_id); ?>').style.opacity = '0.5';
            <?php endif; ?>
        });
        </script>
        <?php
    }

    add_action('wpdiscuz_before_comment_post', 'jct_wpdiscuz_validate_turnstile');
    function jct_wpdiscuz_validate_turnstile() {
        if (jct_whitelisted()) {
            return;
        }

        $check = jct_check();
        $success = isset($check['success']) && $check['success'];

        if (!$success) {
            do_action('jct_turnstile_failed_wpdiscuz', $check);
            wp_die('<strong>' . esc_html__('ERROR:', 'just-cloudflare-turnstile') . '</strong> ' . esc_html(jct_failed_message()), 403);
        }
    }
}
