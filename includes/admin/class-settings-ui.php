<?php
namespace JCT\Admin;

use function add_action;
use function add_options_page;
use function current_user_can;
use function apply_filters;
use function get_option;
use function settings_fields;
use function wp_nonce_field;
use function checked;
use function selected;
use function esc_attr;
use function esc_attr__;
use function esc_html__;
use function esc_textarea;
use function submit_button;
use function in_array;
use function defined;
use function __;
use function settings_errors;

defined('ABSPATH') || exit;

class Settings_UI {
    /**
     * Initialize admin menu and page rendering.
     */
    public static function init() {
        \add_action('admin_menu', [__CLASS__, 'register_menu']);
    }

    /**
     * Register the plugin settings page.
     */
    public static function register_menu() {
        \add_options_page(
            \__('Just Cloudflare Turnstile', 'just-cloudflare-turnstile'),
            \__('Cloudflare Turnstile', 'just-cloudflare-turnstile'),
            'manage_options',
            'just-cloudflare-turnstile',
            [__CLASS__, 'render_page']
        );
    }

    /**
     * Render the settings page.
     */
    public static function render_page() {
        if (!\current_user_can('manage_options')) {
            return;
        }

        // Remove default success notice WordPress adds automatically
        add_filter('get_settings_errors', function ($errors) {
            return array_filter($errors, function ($error) {
                return $error['code'] !== 'settings_updated';
            });
        });

        $settings = Admin_Options::get_settings();
        $active_plugins = \apply_filters('active_plugins', \get_option('active_plugins', []));

        // Enqueue Turnstile API script only on this admin page
        add_action('admin_footer', function() use ($settings) {
            $site_key = esc_attr($settings['site_key'] ?? '');
            if (!$site_key) return;
            ?>
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=jctAdminTurnstileReady&render=explicit" async defer></script>
            <script>
            window.jctAdminTurnstileReady = function() {
                var el = document.getElementById('jct-turnstile-test-widget');
                if (el && typeof turnstile !== 'undefined' && !el.dataset.rendered) {
                    turnstile.render(el, {
                        sitekey: '<?php echo $site_key; ?>',
                        theme: '<?php echo esc_attr($settings['theme'] ?? 'auto'); ?>',
                        size: '<?php echo esc_attr($settings['widget_size'] ?? 'normal'); ?>',
                        appearance: '<?php echo esc_attr($settings['appearance'] ?? 'always'); ?>',
                        callback: function(token) {
                            document.getElementById('jct-turnstile-test-success').style.display = 'block';
                        },
                        'expired-callback': function() {
                            document.getElementById('jct-turnstile-test-success').style.display = 'none';
                        },
                        'error-callback': function() {
                            document.getElementById('jct-turnstile-test-success').style.display = 'none';
                        }
                    });
                    el.dataset.rendered = 'true';
                }
            };
            </script>
            <?php
        });
        ?>
        <div class="wrap" id="jct-admin-app">
            <div class="jct-settings-intro">
                <h1 class="jct-admin-title">Just Cloudflare Turnstile</h1>
                <p>Seamlessly integrate Cloudflare’s free Turnstile CAPTCHA into your WordPress forms to enhance security and reduce spam – without compromising user experience.</p>
                <div class="jct-intro-links">
                    <a href="https://justthere.co.uk/plugins/just-cloudflare-turnstile/documentation/" target="_blank" rel="noopener">View Plugin Documentation</a>
                    <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/reviews/#new-post" target="_blank" rel="noopener">Consider Leaving Us a Review</a>
                    <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/" target="_blank" rel="noopener">Get Support</a>
                    <a href="https://justthere.co.uk/plugins/support-us/" target="_blank" rel="noopener">Buy us a coffee</a>
                </div>
            </div>
            <form method="post" action="options.php" autocomplete="off" novalidate>
                <?php \settings_fields('jct_settings_group'); ?>
                <?php \wp_nonce_field('jct_settings_save', 'jct_settings_nonce'); ?>

                <!-- Site Keys (no accordion, now at top) -->
                <div class="jct-card">
                  <h2>Cloudflare Turnstile Site Key & Secret Key</h2>
                  <div class="jct-section-content">
                    <p style="margin-bottom:18px;font-size:1.08em;max-width:100%;">
                      You can obtain your Site Key and Secret Key by visiting the following Cloudflare Turnstile dashboard link:<br>
                      <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" rel="noopener" style="color:var(--jt-accent);font-weight:600;">https://dash.cloudflare.com/?to=/:account/turnstile</a>
                    </p>
                    <table class="form-table">
                        <tr>
                            <th><label for="site_key">Site Key</label></th>
                            <td><input type="text" id="site_key" name="jct_settings[site_key]" value="<?php echo \esc_attr($settings['site_key'] ?? ''); ?>" class="regular-text" required autocomplete="off" />
                            </td>
                        </tr>
                        <tr>
                            <th><label for="secret_key">Secret Key</label></th>
                            <td><input type="text" id="secret_key" name="jct_settings[secret_key]" value="<?php echo \esc_attr($settings['secret_key'] ?? ''); ?>" class="regular-text" required autocomplete="off" />
                            </td>
                        </tr>
                    </table>

                    <!-- Test Cloudflare Turnstile Response Section (moved here) -->
                    <table class="form-table" style="margin-top:28px;">
                      <tr>
                        <th scope="row" style="vertical-align:top;"><label style="margin-bottom:10px; margin-top:0;">Test Cloudflare Turnstile Response</label></th>
                        <td>
                          <div id="jct-turnstile-test-widget" class="cf-turnstile" data-sitekey="<?php echo esc_attr($settings['site_key'] ?? ''); ?>" data-theme="<?php echo esc_attr($settings['theme'] ?? 'auto'); ?>" data-size="<?php echo esc_attr($settings['widget_size'] ?? 'normal'); ?>" data-appearance="<?php echo esc_attr($settings['appearance'] ?? 'always'); ?>"></div>
                          <div id="jct-turnstile-test-success" style="display:none;">
                              <?php echo esc_html__('Success! Your API keys are valid and Turnstile is functioning correctly.', 'just-cloudflare-turnstile'); ?>
                          </div>
                          <?php if (empty($settings['site_key'])): ?>
                              <div class="jct-warning"><?php echo esc_html__('Enter your Site Key above to test Turnstile.', 'just-cloudflare-turnstile'); ?></div>
                          <?php endif; ?>
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>

                <!-- Display Settings -->
                <div class="jct-card">
                  <h2>Display Settings</h2>
                  <div class="jct-section-content">
                    <table class="form-table">
                        <tr>
                            <th><label for="theme"><?php echo \__('Theme', 'just-cloudflare-turnstile'); ?></label></th>
                            <td>
                                <select id="theme" name="jct_settings[theme]">
                                    <option value="auto" <?php selected($settings['theme'] ?? '', 'auto'); ?>><?php echo \__('Auto', 'just-cloudflare-turnstile'); ?></option>
                                    <option value="light" <?php selected($settings['theme'] ?? '', 'light'); ?>><?php echo \__('Light', 'just-cloudflare-turnstile'); ?></option>
                                    <option value="dark" <?php selected($settings['theme'] ?? '', 'dark'); ?>><?php echo \__('Dark', 'just-cloudflare-turnstile'); ?></option>
                                </select>
                                <div class="description" style="margin-top:6px;color:#555;font-size:0.97em;">
                                    <?php echo __('Select the visual style for the Turnstile widget to match your site\'s design.', 'just-cloudflare-turnstile'); ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="widget_size"><?php echo \__('Widget Size', 'just-cloudflare-turnstile'); ?></label></th>
                            <td>
                                <select id="widget_size" name="jct_settings[widget_size]">
                                    <option value="normal" <?php selected($settings['widget_size'] ?? '', 'normal'); ?>><?php echo \__('Normal', 'just-cloudflare-turnstile'); ?></option>
                                    <option value="small" <?php selected($settings['widget_size'] ?? '', 'small'); ?>><?php echo \__('Small', 'just-cloudflare-turnstile'); ?></option>
                                    <option value="medium" <?php selected($settings['widget_size'] ?? '', 'medium'); ?>><?php echo \__('Medium', 'just-cloudflare-turnstile'); ?></option>
                                    <option value="large" <?php selected($settings['widget_size'] ?? '', 'large'); ?>><?php echo \__('Large', 'just-cloudflare-turnstile'); ?></option>
                                </select>
                                <div class="description" style="margin-top:6px;color:#555;font-size:0.97em;">
                                    <?php echo __('Define the display size of the Turnstile widget (e.g., normal, small, medium or large) to best fit your form layout.', 'just-cloudflare-turnstile'); ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="appearance"><?php echo __('Appearance Mode', 'just-cloudflare-turnstile'); ?></label></th>
                            <td>
                                <select id="appearance" name="jct_settings[appearance]">
                                    <option value="always" <?php selected($settings['appearance'] ?? '', 'always'); ?>><?php echo __('Always', 'just-cloudflare-turnstile'); ?></option>
                                    <option value="interaction-only" <?php selected($settings['appearance'] ?? '', 'interaction-only'); ?>><?php echo __('Interaction Only', 'just-cloudflare-turnstile'); ?></option>
                                </select>
                                <div class="description" style="margin-top:6px;color:#555;font-size:0.97em;">
                                    <?php echo __('Control how the Turnstile widget is rendered visually (e.g., always visible or context-aware).', 'just-cloudflare-turnstile'); ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="language"><?php echo __('Language', 'just-cloudflare-turnstile'); ?></label></th>
                            <td>
                                <select id="language" name="jct_settings[language]">
                                    <option value="auto" <?php selected($settings['language'] ?? '', 'auto'); ?>><?php echo __('Auto (Detect)', 'just-cloudflare-turnstile'); ?></option>
                                    <option value="en" <?php selected($settings['language'] ?? '', 'en'); ?>>English</option>
                                    <option value="es" <?php selected($settings['language'] ?? '', 'es'); ?>>Español (Spanish)</option>
                                    <option value="fr" <?php selected($settings['language'] ?? '', 'fr'); ?>>Français (French)</option>
                                    <option value="de" <?php selected($settings['language'] ?? '', 'de'); ?>>Deutsch (German)</option>
                                    <option value="it" <?php selected($settings['language'] ?? '', 'it'); ?>>Italiano (Italian)</option>
                                    <option value="pt" <?php selected($settings['language'] ?? '', 'pt'); ?>>Português (Portuguese)</option>
                                    <option value="ru" <?php selected($settings['language'] ?? '', 'ru'); ?>>Русский (Russian)</option>
                                    <option value="zh-CN" <?php selected($settings['language'] ?? '', 'zh-CN'); ?>>简体中文 (Chinese Simplified)</option>
                                    <option value="zh-TW" <?php selected($settings['language'] ?? '', 'zh-TW'); ?>>繁體中文 (Chinese Traditional)</option>
                                    <option value="ja" <?php selected($settings['language'] ?? '', 'ja'); ?>>日本語 (Japanese)</option>
                                    <option value="ko" <?php selected($settings['language'] ?? '', 'ko'); ?>>한국어 (Korean)</option>
                                    <option value="ar" <?php selected($settings['language'] ?? '', 'ar'); ?>>العربية (Arabic)</option>
                                    <option value="tr" <?php selected($settings['language'] ?? '', 'tr'); ?>>Türkçe (Turkish)</option>
                                    <option value="pl" <?php selected($settings['language'] ?? '', 'pl'); ?>>Polski (Polish)</option>
                                    <option value="nl" <?php selected($settings['language'] ?? '', 'nl'); ?>>Nederlands (Dutch)</option>
                                    <option value="sv" <?php selected($settings['language'] ?? '', 'sv'); ?>>Svenska (Swedish)</option>
                                    <option value="fi" <?php selected($settings['language'] ?? '', 'fi'); ?>>Suomi (Finnish)</option>
                                    <option value="da" <?php selected($settings['language'] ?? '', 'da'); ?>>Dansk (Danish)</option>
                                    <option value="no" <?php selected($settings['language'] ?? '', 'no'); ?>>Norsk (Norwegian)</option>
                                    <option value="cs" <?php selected($settings['language'] ?? '', 'cs'); ?>>Čeština (Czech)</option>
                                    <option value="hu" <?php selected($settings['language'] ?? '', 'hu'); ?>>Magyar (Hungarian)</option>
                                    <option value="el" <?php selected($settings['language'] ?? '', 'el'); ?>>Ελληνικά (Greek)</option>
                                    <option value="he" <?php selected($settings['language'] ?? '', 'he'); ?>>עברית (Hebrew)</option>
                                    <option value="uk" <?php selected($settings['language'] ?? '', 'uk'); ?>>Українська (Ukrainian)</option>
                                    <option value="ro" <?php selected($settings['language'] ?? '', 'ro'); ?>>Română (Romanian)</option>
                                    <option value="bg" <?php selected($settings['language'] ?? '', 'bg'); ?>>Български (Bulgarian)</option>
                                    <option value="id" <?php selected($settings['language'] ?? '', 'id'); ?>>Bahasa Indonesia</option>
                                    <option value="th" <?php selected($settings['language'] ?? '', 'th'); ?>>ไทย (Thai)</option>
                                    <option value="vi" <?php selected($settings['language'] ?? '', 'vi'); ?>>Tiếng Việt (Vietnamese)</option>
                                </select>
                                <div class="description" style="margin-top:6px;color:#555;font-size:0.97em;">
                                    <?php echo __('Choose the language for the Turnstile interface.', 'just-cloudflare-turnstile'); ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="defer_scripts"><?php echo __('Defer Turnstile Script', 'just-cloudflare-turnstile'); ?></label></th>
                            <td><input type="checkbox" id="defer_scripts" name="jct_settings[defer_scripts]" value="1" <?php checked(!empty($settings['defer_scripts'])); ?> /> <span class="description"><?php echo __('When enabled, the plugin\'s JavaScript files will be deferred to improve page load performance. Disable this option if it conflicts with other performance or optimization plugins.', 'just-cloudflare-turnstile'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="disable_submit"><?php echo __('Disable Submit Button', 'just-cloudflare-turnstile'); ?></label></th>
                            <td><input type="checkbox" id="disable_submit" name="jct_settings[disable_submit]" value="1" <?php checked(!empty($settings['disable_submit'])); ?> />
                                <span class="description">When enabled, the form’s submit button will remain inactive until the Turnstile challenge is successfully completed, ensuring proper verification before submission.</span>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="error_message"><?php echo __('Custom Error Message', 'just-cloudflare-turnstile'); ?></label></th>
                            <td><input type="text" id="error_message" name="jct_settings[error_message]" value="<?php echo esc_attr($settings['error_message'] ?? ''); ?>" class="regular-text" />
                                <div class="description" style="margin-top:6px;color:#555;font-size:0.97em;">
                                    <?php echo __('This message is displayed if the form is submitted without completing the Turnstile challenge. Leave blank to use the default localized message: “Please verify that you are human.”', 'just-cloudflare-turnstile'); ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="extra_message"><?php echo __('Extra Failure Message', 'just-cloudflare-turnstile'); ?></label></th>
                            <td><input type="text" id="extra_message" name="jct_settings[extra_message]" value="<?php echo esc_attr($settings['extra_message'] ?? ''); ?>" class="regular-text" />
                                <div class="description" style="margin-top:6px;color:#555;font-size:0.97em;">
                                    <?php echo __('This message appears below the Turnstile widget when a user receives a “Failure!” response. It’s helpful for providing additional guidance in the rare event that a valid user is mistakenly flagged as spam.', 'just-cloudflare-turnstile'); ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                  </div>
                </div>

                <!-- Whitelist -->
                <div class="jct-card">
                    <h2>Whitelist Settings</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="whitelist_loggedin"><?php echo __('Skip for Logged-in Users', 'just-cloudflare-turnstile'); ?></label></th>
                                <td><input type="checkbox" id="whitelist_loggedin" name="jct_settings[whitelist_loggedin]" value="1" <?php checked(!empty($settings['whitelist_loggedin'])); ?> />
                                    <span class="description" style="margin-left:8px;">When enabled, users who are logged in to your site will be exempt from the Turnstile challenge.</span>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="whitelist_ips"><?php echo __('IP Address Whitelist', 'just-cloudflare-turnstile'); ?></label></th>
                                <td>
                                    <textarea id="whitelist_ips" name="jct_settings[whitelist_ips]" rows="2" class="large-text code"><?php echo esc_textarea($settings['whitelist_ips'] ?? ''); ?></textarea><br />
                                    <span class="description">Enter one IP address per line. Visitors from these IP addresses will bypass the Turnstile verification.<br />
                                    <span style='color:#b91c1c;font-weight:600;'>⚠️ Caution: IP spoofing is possible. If an attacker obtains a whitelisted IP address, they may be able to bypass the challenge.</span></span>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="whitelist_user_agents"><?php echo __('User Agent Whitelist', 'just-cloudflare-turnstile'); ?></label></th>
                                <td>
                                    <textarea id="whitelist_user_agents" name="jct_settings[whitelist_user_agents]" rows="2" class="large-text code"><?php echo esc_textarea($settings['whitelist_user_agents'] ?? ''); ?></textarea><br />
                                    <span class="description">Enter one User Agent per line. Visitors using a listed User Agent will bypass the Turnstile challenge.<br />
                                    <span style='color:#b91c1c;font-weight:600;'>⚠️ Caution: User Agents can be spoofed. Only use this option if you understand the risks.</span></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- WordPress Integration -->
                <div class="jct-card">
                    <h2>WordPress Integration</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="enable_wordpress">Enable for WordPress Core Forms</label></th>
                                <td><input type="checkbox" id="enable_wordpress" name="jct_settings[enable_wordpress]" value="1" <?php checked(!empty($settings['enable_wordpress'])); ?> />
                                    <span class="description">Enable Turnstile on WordPress login, registration, password reset, and comment forms. (Select exact forms separately below.)</span>
                                </td>
                            </tr>
                        </table>
                        <table class="form-table">
                            <tr>
                                <th><label for="wp_login_form">Login Form</label></th>
                                <td><input type="checkbox" id="wp_login_form" name="jct_settings[wp_login_form]" value="1" <?php checked(!empty($settings['wp_login_form'])); ?> />
                                    <span class="description">Enable Turnstile on WordPress login form.</span>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="wp_register_form">Registration Form</label></th>
                                <td><input type="checkbox" id="wp_register_form" name="jct_settings[wp_register_form]" value="1" <?php checked(!empty($settings['wp_register_form'])); ?> />
                                    <span class="description">Enable Turnstile on WordPress registration form.</span>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="wp_lostpassword_form">Password Reset Form</label></th>
                                <td><input type="checkbox" id="wp_lostpassword_form" name="jct_settings[wp_lostpassword_form]" value="1" <?php checked(!empty($settings['wp_lostpassword_form'])); ?> />
                                    <span class="description">Enable Turnstile on WordPress password reset form.</span>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="wp_comments_form">Comments Form</label></th>
                                <td><input type="checkbox" id="wp_comments_form" name="jct_settings[wp_comments_form]" value="1" <?php checked(!empty($settings['wp_comments_form'])); ?> />
                                    <span class="description">Enable Turnstile on WordPress comments form.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <!-- WooCommerce Integration -->
                <?php $is_wc_active = in_array('woocommerce/woocommerce.php', $active_plugins, true); ?>
                <?php if ($is_wc_active): ?>
                <div class="jct-card">
                    <h2>WooCommerce Integration</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="enable_woocommerce">Enable for WooCommerce Forms</label></th>
                                <td><input type="checkbox" id="enable_woocommerce" name="jct_settings[enable_woocommerce]" value="1" <?php checked(!empty($settings['enable_woocommerce'])); ?> />
                                    <span class="description">Enable Turnstile on WooCommerce checkout, login, registration, and password reset forms. (Select exact forms separately below.)</span>
                                </td>
                            </tr>
                        </table>
                        <table class="form-table">
                            <tr>
                                <th><label for="wc_checkout_form">Checkout Form</label></th>
                                <td><input type="checkbox" id="wc_checkout_form" name="jct_settings[wc_checkout_form]" value="1" <?php checked(!empty($settings['wc_checkout_form'])); ?> />
                                    <span class="description">Enable Turnstile on WooCommerce checkout form.</span>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="wc_login_form">Login Form</label></th>
                                <td><input type="checkbox" id="wc_login_form" name="jct_settings[wc_login_form]" value="1" <?php checked(!empty($settings['wc_login_form'])); ?> />
                                    <span class="description">Enable Turnstile on WooCommerce login form.</span>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="wc_register_form">Registration Form</label></th>
                                <td><input type="checkbox" id="wc_register_form" name="jct_settings[wc_register_form]" value="1" <?php checked(!empty($settings['wc_register_form'])); ?> />
                                    <span class="description">Enable Turnstile on WooCommerce registration form.</span>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="wc_lostpassword_form">Password Reset Form</label></th>
                                <td><input type="checkbox" id="wc_lostpassword_form" name="jct_settings[wc_lostpassword_form]" value="1" <?php checked(!empty($settings['wc_lostpassword_form'])); ?> />
                                    <span class="description">Enable Turnstile on WooCommerce password reset form.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Elementor Integration -->
                <?php if (defined('ELEMENTOR_VERSION')) : ?>
                <div class="jct-card">
                    <h2>Elementor Integration</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="enable_elementor">Enable for Elementor Forms</label></th>
                                <td><input type="checkbox" id="enable_elementor" name="jct_settings[enable_elementor]" value="1" <?php checked(!empty($settings['enable_elementor'])); ?> />
                                    <span class="description">Enable Turnstile on all Elementor forms and popups.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- WPForms Integration -->
                <?php if (class_exists('WPForms')) : ?>
                <div class="jct-card">
                    <h2>WPForms Integration</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="enable_wpforms">Enable for WPForms</label></th>
                                <td><input type="checkbox" id="enable_wpforms" name="jct_settings[enable_wpforms]" value="1" <?php checked(!empty($settings['enable_wpforms'])); ?> />
                                    <span class="description">Enable Turnstile on all WPForms forms.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Fluent Forms Integration -->
                <?php if (defined('FLUENTFORM') || class_exists('FluentForm')) : ?>
                <div class="jct-card">
                    <h2>Fluent Forms Integration</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="enable_fluentforms">Enable for Fluent Forms</label></th>
                                <td><input type="checkbox" id="enable_fluentforms" name="jct_settings[enable_fluentforms]" value="1" <?php checked(!empty($settings['enable_fluentforms'])); ?> />
                                    <span class="description">Enable Turnstile on all Fluent Forms forms.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Gravity Forms Integration -->
                <?php if (class_exists('GFForms')) : ?>
                <div class="jct-card">
                    <h2>Gravity Forms Integration</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="enable_gravityforms">Enable for Gravity Forms</label></th>
                                <td><input type="checkbox" id="enable_gravityforms" name="jct_settings[enable_gravityforms]" value="1" <?php checked(!empty($settings['enable_gravityforms'])); ?> />
                                    <span class="description">Enable Turnstile on all Gravity Forms forms.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Formidable Forms Integration -->
                <?php if (class_exists('FrmForm')) : ?>
                <div class="jct-card">
                    <h2>Formidable Forms Integration</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="enable_formidableforms">Enable for Formidable Forms</label></th>
                                <td><input type="checkbox" id="enable_formidableforms" name="jct_settings[enable_formidableforms]" value="1" <?php checked(!empty($settings['enable_formidableforms'])); ?> />
                                    <span class="description">Enable Turnstile on all Formidable Forms forms.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Contact Form 7 Integration Toggle -->
                <?php if (in_array('contact-form-7/wp-contact-form-7.php', $active_plugins, true) || defined('WPCF7_VERSION')): ?>
                <div class="jct-card">
                    <h2>Contact Form 7 Integration</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="enable_cf7"><?php echo __('Enable for Contact Form 7', 'just-cloudflare-turnstile'); ?></label></th>
                                <td><input type="checkbox" id="enable_cf7" name="jct_settings[enable_cf7]" value="1" <?php checked(!empty($settings['enable_cf7'])); ?> />
                                <span class="description"><?php echo __('Enable Turnstile on all Contact Form 7 forms..', 'just-cloudflare-turnstile'); ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Forminator Integration -->
                <?php if (class_exists('Forminator')) : ?>
                <div class="jct-card">
                    <h2>Forminator Integration</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="enable_forminator">Enable for Forminator Forms</label></th>
                                <td><input type="checkbox" id="enable_forminator" name="jct_settings[enable_forminator]" value="1" <?php checked(!empty($settings['enable_forminator'])); ?> />
                                    <span class="description">Enable Turnstile on all Forminator forms.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Jetpack Forms Integration -->
                <?php if (class_exists('Jetpack')) : ?>
                <div class="jct-card">
                    <h2>Jetpack Forms Integration</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="enable_jetpackforms">Enable for Jetpack Forms</label></th>
                                <td><input type="checkbox" id="enable_jetpackforms" name="jct_settings[enable_jetpackforms]" value="1" <?php checked(!empty($settings['enable_jetpackforms'])); ?> />
                                    <span class="description">Enable Turnstile on all Jetpack Forms.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Kadence Forms Integration -->
                <?php if (class_exists('Kadence_Blocks_Form')) : ?>
                <div class="jct-card">
                    <h2>Kadence Forms Integration</h2>
                    <div class="jct-section-content">
                        <table class="form-table">
                            <tr>
                                <th><label for="enable_kadenceforms">Enable for Kadence Forms</label></th>
                                <td><input type="checkbox" id="enable_kadenceforms" name="jct_settings[enable_kadenceforms]" value="1" <?php checked(!empty($settings['enable_kadenceforms'])); ?> />
                                    <span class="description">Enable Turnstile on all Kadence Forms (Kadence Blocks).</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Support Us Section -->
                <div class="jct-settings-intro" style="margin-top: 0; margin-bottom: 24px;">
                    <h2 style="font-size:1.3em; font-weight:700; margin-bottom:6px;">Support Active Development</h2>
                    <p style="margin-bottom:8px; font-size:1.05em; max-width:100%;">
                        If you find Just Cloudflare Turnstile useful, please consider buying us a coffee! Your support helps us maintain and actively develop this plugin for the WordPress community.
                    </p>
                    <a href="https://justthere.co.uk/plugins/support-us/" target="_blank" rel="noopener" style="color:var(--jt-accent);font-weight:600;">☕ Buy us a coffee</a>
                </div>
                <div class="jct-save-row">
                    <?php submit_button(__('Save Settings', 'just-cloudflare-turnstile'), 'primary', 'submit', false, ['style' => 'min-width:160px;font-size:17px;']); ?>
                </div>
            </form>
        </div>
    <?php
    }
}
