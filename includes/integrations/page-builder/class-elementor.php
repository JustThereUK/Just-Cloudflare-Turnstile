<?php
namespace JCT\Integrations\PageBuilder;

use JCT\Core\Whitelist;

defined('ABSPATH') || exit;

use function add_action;
use function get_option;
use function esc_attr;
use function esc_html;
use function __;
use function wp_enqueue_script;
use function sanitize_text_field;
use function wp_remote_post;
use function is_wp_error;
use function wp_remote_retrieve_body;
use function is_admin;
use function error_log;

class Elementor {

    public static function init() {
        if (!defined('ELEMENTOR_VERSION')) {
            if (defined('WP_DEBUG') && WP_DEBUG) error_log('JCT: Elementor not detected.');
            return;
        }
        if (Whitelist::is_whitelisted()) {
            if (defined('WP_DEBUG') && WP_DEBUG) error_log('JCT: Whitelist is blocking Elementor integration.');
            if (!is_admin() && isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'elementor') !== false) {
                if (defined('WP_DEBUG') && WP_DEBUG) error_log('JCT: Fallback - forcing Elementor integration.');
            } else {
                return;
            }
        }
        if (defined('WP_DEBUG') && WP_DEBUG) error_log('JCT: Initializing Elementor integration.');
        add_action('elementor/frontend/init', [__CLASS__, 'register_widget_hooks']);
        add_action('elementor/widget/form/after_render', [__CLASS__, 'inject_widget_script'], 10, 2);
        add_action('elementor_pro/forms/validation', [__CLASS__, 'validate_turnstile'], 10, 2);
        add_action('elementor_pro/forms/pre_render', [__CLASS__, 'disable_submit_css']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts']);
        // Fallback: inject widget for all Elementor forms (free or pro)
        add_action('wp_footer', [__CLASS__, 'fallback_inject_widget'], 20);
    }

    public static function register_widget_hooks() {
        add_action('elementor_pro/forms/render_form_after_fields', [__CLASS__, 'render_widget'], 10, 1);
    }

    public static function render_widget($form) {
        if (defined('WP_DEBUG') && WP_DEBUG) error_log('JCT: render_widget called for Elementor form.');
        $settings = get_option('jct_settings', []);
        $site_key = $settings['site_key'] ?? '';

        if (!$site_key) {
            echo '<p class="jct-warning">' . esc_html__('Turnstile site key is missing.', 'just-cloudflare-turnstile') . '</p>';
            return;
        }

        // Add a nonce field for CSRF protection
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field('jct_turnstile_action', 'jct_turnstile_nonce');
        }

        echo '<div class="cf-turnstile" data-sitekey="' . esc_attr($site_key) . '" data-theme="' . esc_attr($settings['theme'] ?? 'auto') . '" data-size="' . esc_attr($settings['widget_size'] ?? 'normal') . '" data-appearance="' . esc_attr($settings['appearance'] ?? 'always') . '"></div>';
    }

    public static function inject_widget_script($widget, $args) {
        if ('form' !== $widget->get_name()) {
            return;
        }

        static $injected = false;
        if ($injected) return;
        $injected = true;

        add_action('wp_footer', function () {
            $settings = get_option('jct_settings', []);
            $site_key = esc_attr($settings["site_key"] ?? '');
            $theme = esc_attr($settings["theme"] ?? 'auto');

            if (!$site_key) return;

            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('.elementor-form-fields-wrapper').forEach(wrapper => {
                        if (!wrapper.querySelector('.jct-turnstile')) {
                            const container = document.createElement('div');
                            container.className = 'jct-turnstile-wrapper';
                            container.innerHTML = `<div class="jct-turnstile" data-sitekey="<?php echo $site_key; ?>" data-theme="<?php echo $theme; ?>"></div>`;
                            wrapper.appendChild(container);
                        }
                    });
                });
            </script>
            <?php
        });
    }

    /**
     * Fallback: Inject Turnstile widget before the submit button in Elementor forms
     */
    public static function fallback_inject_widget() {
        $settings = get_option('jct_settings', []);
        $site_key = esc_attr($settings['site_key'] ?? '');
        $theme = esc_attr($settings['theme'] ?? 'auto');
        $size = esc_attr($settings['widget_size'] ?? 'normal');
        $appearance = esc_attr($settings['appearance'] ?? 'always');
        if (!$site_key) return;
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.elementor-form-fields-wrapper').forEach(function(wrapper) {
                // Find the submit button group
                var submitGroup = wrapper.querySelector('.elementor-field-type-submit');
                if (!submitGroup) return;
                // Only insert if not already present
                if (!wrapper.querySelector('.cf-turnstile')) {
                    var container = document.createElement('div');
                    container.className = 'cf-turnstile';
                    container.setAttribute('data-sitekey', '<?php echo $site_key; ?>');
                    container.setAttribute('data-theme', '<?php echo $theme; ?>');
                    container.setAttribute('data-size', '<?php echo $size; ?>');
                    container.setAttribute('data-appearance', '<?php echo $appearance; ?>');
                    // Insert before the submit button group
                    submitGroup.parentNode.insertBefore(container, submitGroup);
                }
            });
        });
        </script>
        <?php
    }

    public static function validate_turnstile($record, $handler) {
        if (!self::is_valid_submission()) {
            $handler->add_error_message(self::get_error_message());
            $handler->add_error('__all__');
        }
    }

    public static function disable_submit_css() {
        echo "<style>.elementor-form button[type=submit] { pointer-events: none; opacity: 0.5; }</style>";
    }

    public static function enqueue_scripts() {
        wp_enqueue_script(
            'jct-elementor',
            JCT_ASSETS_URL . 'js/elementor.js',
            ['jquery', 'elementor-frontend'],
            JCT_VERSION,
            true
        );
    }

    private static function is_valid_submission(): bool {
        // Verify nonce for CSRF protection
        if (!isset($_POST['jct_turnstile_nonce']) || (function_exists('wp_verify_nonce') && !wp_verify_nonce($_POST['jct_turnstile_nonce'], 'jct_turnstile_action'))) {
            return false;
        }
        if (!isset($_POST['cf-turnstile-response'])) {
            return false;
        }

        $settings = get_option('jct_settings', []);
        $secret = $settings['secret_key'] ?? '';
        $response = sanitize_text_field($_POST['cf-turnstile-response']);
        $remoteip = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!$secret || !$response) {
            return false;
        }

        $verify = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret'   => $secret,
                'response' => $response,
                'remoteip' => $remoteip,
            ],
            'timeout' => 8,
        ]);

        if (is_wp_error($verify)) {
            if (defined('WP_DEBUG') && WP_DEBUG) error_log('Turnstile verification error: ' . $verify->get_error_message());
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($verify), true);
        return !empty($data['success']);
    }

    private static function get_error_message(): string {
        $settings = get_option('jct_settings', []);
        $message = !empty($settings['error_message']) ? $settings['error_message'] : __('Please complete the Turnstile challenge.', 'just-cloudflare-turnstile');
        /**
         * Filter the Turnstile error message for Elementor forms.
         *
         * @param string $message The error message to display.
         * @return string
         */
        $message = apply_filters('jct_elementor_turnstile_error_message', $message);
        return esc_html($message);
    }
}

Elementor::init();
