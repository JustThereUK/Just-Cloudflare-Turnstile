<?php
namespace JCT\Core;

defined('ABSPATH') || exit;

class Turnstile_Loader {

    /**
     * Initialize the plugin.
     */
    public static function init() {
        self::load_core();
        self::load_admin();
        self::load_integrations();
    }

    /**
     * Load core functionality like script handler and whitelist checks.
     */
    private static function load_core() {
        require_once JCT_INCLUDES_PATH . 'core/class-turnstile-validator.php';
        require_once JCT_INCLUDES_PATH . 'core/class-script-handler.php';
        require_once JCT_INCLUDES_PATH . 'core/class-whitelist.php';

        \JCT\Core\Script_Handler::init();
        \JCT\Core\Whitelist::init();
    }

    /**
     * Load admin panel features like settings and onboarding.
     */
    private static function load_admin() {
        if (!\is_admin()) {
            return;
        }

        require_once JCT_INCLUDES_PATH . 'admin/class-admin-options.php';
        require_once JCT_INCLUDES_PATH . 'admin/class-settings-ui.php';
        require_once JCT_INCLUDES_PATH . 'admin/class-onboarding.php';

        if (class_exists('JCT\\Admin\\Admin_Options')) {
            \JCT\Admin\Admin_Options::init();
        }
        if (class_exists('JCT\\Admin\\Settings_UI')) {
            \JCT\Admin\Settings_UI::init();
        }
        if (class_exists('JCT\\Admin\\Onboarding')) {
            \JCT\Admin\Onboarding::init();
        }
    }

    /**
     * Load supported third-party integrations.
     */
    private static function load_integrations() {
        $settings = function_exists('get_option') ? \get_option('jct_settings', []) : [];
        // WordPress Core Forms
        if (!empty($settings['enable_wordpress'])) {
            require_once JCT_INCLUDES_PATH . 'integrations/wordpress/class-wp-core.php';
            if (class_exists('JCT\\Integrations\\WordPress\\WP_Core')) {
                \JCT\Integrations\WordPress\WP_Core::init();
            }
        }
        // WooCommerce Forms
        if (!empty($settings['enable_woocommerce']) && class_exists('WooCommerce')) {
            require_once JCT_INCLUDES_PATH . 'integrations/ecommerce/class-woocommerce.php';
            if (class_exists('JCT\\Integrations\\Ecommerce\\WooCommerce')) {
                \JCT\Integrations\Ecommerce\WooCommerce::init();
            }
        }
        // Elementor Forms
        if (!empty($settings['enable_elementor']) && defined('ELEMENTOR_VERSION')) {
            require_once JCT_INCLUDES_PATH . 'integrations/page-builder/class-elementor.php';
            if (class_exists('JCT\\Integrations\\PageBuilder\\Elementor')) {
                \JCT\Integrations\PageBuilder\Elementor::init();
            }
        }
        // WPForms
        if (!empty($settings['enable_wpforms']) && class_exists('WPForms')) {
            require_once JCT_INCLUDES_PATH . 'integrations/forms/wpforms.php';
        }
        // Fluent Forms
        if (!empty($settings['enable_fluentforms']) && (defined('FLUENTFORM') || class_exists('FluentForm'))) {
            require_once JCT_INCLUDES_PATH . 'integrations/forms/fluent-forms.php';
            if (class_exists('JCT\\Integrations\\Forms\\FluentForms')) {
                \JCT\Integrations\Forms\FluentForms::init();
            }
        }
        // Gravity Forms
        if (!empty($settings['enable_gravityforms']) && class_exists('GFForms')) {
            require_once JCT_INCLUDES_PATH . 'integrations/forms/gravity-forms.php';
        }
        // Contact Form 7
        if (!empty($settings['enable_cf7']) && defined('WPCF7_VERSION')) {
            require_once JCT_INCLUDES_PATH . 'integrations/forms/contact-form-7.php';
        }
        // Formidable Forms
        if (!empty($settings['enable_formidableforms']) && class_exists('FrmForm')) {
            require_once JCT_INCLUDES_PATH . 'integrations/forms/formidable-forms.php';
        }
        // Forminator Forms
        if (!empty($settings['enable_forminator']) && function_exists('forminator')) {
            require_once JCT_INCLUDES_PATH . 'integrations/forms/forminator-forms.php';
            if (class_exists('Just_Cloudflare_Turnstile_Forminator_Integration')) {
                Just_Cloudflare_Turnstile_Forminator_Integration::init();
            }
        }
        // Jetpack Forms
        if (!empty($settings['enable_jetpackforms']) && class_exists('Jetpack')) {
            require_once JCT_INCLUDES_PATH . 'integrations/forms/jetpack-forms.php';
        }
        // Kadence Forms
        if (!empty($settings['enable_kadenceforms']) && class_exists('Kadence_Blocks_Form')) {
            require_once JCT_INCLUDES_PATH . 'integrations/forms/kadence-forms.php';
        }
    }
}
