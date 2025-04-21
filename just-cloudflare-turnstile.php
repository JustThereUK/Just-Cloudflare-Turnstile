<?php
if (!defined('ABSPATH')) {
    exit;
}

class JCT_Turnstile {

    public function __construct() {
        add_action('jct_after_field', [$this, 'disable_button_styles'], 10, 2);
        add_action('jct_after_field', [$this, 'add_br_if_needed'], 15, 1);
        add_action('jct_after_field', [$this, 'admin_styles'], 20, 1);
        add_action('jct_after_field', [$this, 'add_failed_message'], 5, 1);
        add_action('jct_after_field', [$this, 'force_render'], 10, 1);

        add_action('jct_after_check', [$this, 'log_attempt'], 10, 2);

        add_shortcode('simple-turnstile', [$this, 'shortcode']);
    }

    public function render_field($button_id = '', $callback = '', $form_name = '', $unique_id = '', $class = '') {
        if (apply_filters('jct_widget_disable', false) || $this->is_whitelisted()) return;

        do_action("jct_enqueue_scripts");
        do_action("jct_before_field", esc_attr($unique_id));

        $key = sanitize_text_field(get_option('jct_key'));
        $theme = sanitize_text_field(get_option('jct_theme'));
        $language = sanitize_text_field(get_option('jct_language')) ?: 'auto';
        $appearance = sanitize_text_field(get_option('jct_appearance', 'always'));
        $size = sanitize_text_field(get_option('jct_size'), 'normal');

        ?>
        <div id="jct-turnstile<?php echo esc_attr($unique_id); ?>"
            class="jct-turnstile<?php echo $class ? ' ' . esc_attr($class) : ''; ?>"
            data-sitekey="<?php echo esc_attr($key); ?>"
            data-theme="<?php echo esc_attr($theme); ?>"
            data-language="<?php echo esc_attr($language); ?>"
            data-size="<?php echo esc_attr($size); ?>"
            data-retry="auto"
            data-retry-interval="1000"
            data-action="<?php echo esc_attr($form_name); ?>"
            data-appearance="<?php echo esc_attr($appearance); ?>"
            <?php if (get_option('jct_disable_button')) : ?>
                data-callback="<?php echo esc_attr($callback); ?>"
            <?php endif; ?>
            <?php if (get_option('jct_failure_message_enable')) : ?>
                data-callback="jctCallback"
                data-error-callback="jctErrorCallback"
            <?php endif; ?>>
        </div>
        <?php

        do_action("jct_after_field", esc_attr($unique_id), $button_id);
    }

    public function disable_button_styles($unique_id, $button_id) {
        if ($button_id && get_option('jct_disable_button')) {
            echo "<style>{$button_id} { pointer-events: none; opacity: 0.5; }</style>";
        }
    }

    public function add_br_if_needed($unique_id) {
        if (!get_option('jct_appearance') || get_option('jct_appearance') == 'always') {
            echo '<br class="jct-turnstile-br jct-turnstile-br' . esc_attr($unique_id) . '">';
        } else {
            echo '<style>#jct-turnstile' . esc_html($unique_id) . ' iframe { margin-bottom: 15px; }</style>';
        }
    }

    public function admin_styles($unique_id) {
        if (defined('DOING_AJAX') || is_admin()) return;

        $is_checkout = function_exists('is_checkout') && is_checkout();

        if ((!is_page() && !is_single() && !$is_checkout) || strpos($_SERVER['PHP_SELF'], 'wp-login.php') !== false) {
            echo '<style>#jct-turnstile' . esc_html($unique_id) . ' { margin-left: -15px; }</style>';
        }
    }

    public function add_failed_message($unique_id) {
        if (function_exists('jct_is_block_based_checkout') && jct_is_block_based_checkout()) return;

        if (get_option('jct_failure_message_enable')) {
            $msg = get_option('jct_failure_message') ?: esc_html__('Failed to verify you are human. Please contact us if you are having issues.', 'just-cloudflare-turnstile');
            ?>
            <div class="jct-turnstile-failed-text jct-turnstile-failed-text<?php echo esc_attr($unique_id); ?>"></div>
            <script>
            function jctErrorCallback() {
                document.querySelector('.jct-turnstile-failed-text<?php echo esc_html($unique_id); ?>').innerHTML =
                    '<p><i><?php echo wp_kses_post($msg); ?></i></p>';
            }
            function jctCallback() {
                document.querySelector('.jct-turnstile-failed-text<?php echo esc_html($unique_id); ?>').innerHTML = '';
            }
            </script>
            <?php
        }
    }

    public function force_render($unique_id = '') {
        if (function_exists('jct_is_block_based_checkout') && jct_is_block_based_checkout()) return;

        $key = sanitize_text_field(get_option('jct_key'));
        ?>
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(function () {
                var e = document.getElementById("jct-turnstile<?php echo esc_html($unique_id); ?>");
                if (e && !e.innerHTML.trim()) {
                    turnstile.remove("#jct-turnstile<?php echo esc_html($unique_id); ?>");
                    turnstile.render("#jct-turnstile<?php echo esc_html($unique_id); ?>", { sitekey: "<?php echo esc_html($key); ?>" });
                }
            }, 0);
        });
        </script>
        <?php
    }

    public function validate_response($postdata = '') {
        if ($this->is_whitelisted() || apply_filters('jct_widget_disable', false)) {
            return ['success' => true];
        }

        if (empty($postdata) && isset($_POST['jct-turnstile-response'])) {
            $postdata = sanitize_text_field($_POST['jct-turnstile-response']);
        }

        $secret = sanitize_text_field(get_option('jct_secret'));
        if (!$postdata || !$secret) {
            return ['success' => false];
        }

        $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret' => $secret,
                'response' => $postdata
            ]
        ]);

        $body = json_decode(wp_remote_retrieve_body($response));
        $results = ['success' => $body->success ?? false];

        if (!empty($body->{'error-codes'})) {
            $results['error_code'] = $body->{'error-codes'}[0];
            if ($results['error_code'] === 'invalid-input-secret') {
                update_option('jct_tested', 'no');
            }
        }

        do_action('jct_after_check', $body, $results);

        return $results;
    }

    public function log_attempt($response, $results) {
        if (!get_option('jct_log_enable')) return;

        $log = get_option('jct_log', []);
        $log[] = [
            'date' => current_time('mysql'),
            'success' => $results['success'],
            'error' => $results['error_code'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'page' => $_SERVER['REQUEST_URI']
        ];
        if (count($log) > 50) {
            array_shift($log);
        }
        update_option('jct_log', $log);
    }

    public function form_is_disabled($id, $option) {
        $disabled = get_option($option);
        if (!$disabled) return false;

        $ids = array_map('trim', explode(',', preg_replace('/\s+/', '', $disabled)));
        return in_array($id, $ids);
    }

    public function shortcode() {
        ob_start();
        $this->render_field();
        return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
    }

    public function is_whitelisted() {
        if (isset($_GET['page']) && $_GET['page'] == 'jct') return false;

        if (apply_filters('jct_whitelisted', false)) return true;
        if (get_option('jct_whitelist_users') && is_user_logged_in()) return true;

        $ip = $this->get_ip();
        $ips = array_map('trim', explode("\n", str_replace("\r", "", get_option('jct_whitelist_ips'))));
        if (in_array($ip, $ips)) return true;

        $agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
        $agents = array_map('trim', explode("\n", str_replace("\r", "", get_option('jct_whitelist_agents'))));
        foreach ($agents as $a) {
            if (strpos($agent, $a) !== false) return true;
        }

        return false;
    }

    public function get_ip() {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
                }
            }
        }
        return '';
    }
}

// Initialize the Turnstile class
$GLOBALS['jct_turnstile'] = new JCT_Turnstile();
