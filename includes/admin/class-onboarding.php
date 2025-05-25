<?php
namespace JCT\Admin;

defined('ABSPATH') || exit;

class Onboarding {

    /**
     * Initialize onboarding logic.
     */
    public static function init() {
        \add_action('admin_init', [__CLASS__, 'maybe_redirect_to_setup']);
        \add_action('admin_menu', [__CLASS__, 'register_welcome_screen']);
    }

    /**
     * Trigger onboarding redirect on plugin activation.
     */
    public static function maybe_redirect_to_setup() {
        if (\get_transient('jct_do_activation_redirect')) {
            \delete_transient('jct_do_activation_redirect');

            if (!isset($_GET['activate-multi'])) {
                \wp_safe_redirect(\admin_url('options-general.php?page=jct-onboarding'));
                exit;
            }
        }
    }

    /**
     * Register onboarding page (not shown in menu).
     */
    public static function register_welcome_screen() {
        \add_submenu_page(
            null,
            \__('Welcome to Just Cloudflare Turnstile', 'just-cloudflare-turnstile'),
            '',
            'manage_options',
            'jct-onboarding',
            [__CLASS__, 'render_onboarding_page']
        );
    }

    /**
     * Render onboarding screen HTML.
     */
    public static function render_onboarding_page() {
        ?>
        <div class="wrap" id="jct-admin-app">
            <h1><?php \_e('Welcome to Just Cloudflare Turnstile 🎉', 'just-cloudflare-turnstile'); ?></h1>
            <p><?php \_e('Thank you for installing Just Cloudflare Turnstile! You’re now protected from spam bots with a modern, privacy-friendly solution powered by Cloudflare.', 'just-cloudflare-turnstile'); ?></p>

            <h2><?php \_e('Getting Started', 'just-cloudflare-turnstile'); ?></h2>
            <ol>
                <li><?php \_e('Go to <strong>Settings → Cloudflare Turnstile</strong> and enter your Site Key & Secret Key.', 'just-cloudflare-turnstile'); ?></li>
                <li><?php \_e('Enable Turnstile protection for WordPress, WooCommerce, or Elementor forms.', 'just-cloudflare-turnstile'); ?></li>
                <li><?php \_e('Customize the appearance and behavior to match your site.', 'just-cloudflare-turnstile'); ?></li>
            </ol>

            <h2><?php \_e('Useful Links', 'just-cloudflare-turnstile'); ?></h2>
            <ul>
                <li><a href="<?php echo \esc_url(\admin_url('options-general.php?page=just-cloudflare-turnstile')); ?>" class="button button-primary"><?php \_e('Go to Settings', 'just-cloudflare-turnstile'); ?></a></li>
                <li><a href="https://dash.cloudflare.com/" target="_blank" rel="noopener noreferrer"><?php \_e('Cloudflare Dashboard', 'just-cloudflare-turnstile'); ?></a></li>
            </ul>
        </div>
        <?php
    }
}
