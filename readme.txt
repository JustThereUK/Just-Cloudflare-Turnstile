=== Just Cloudflare Turnstile – Modern CAPTCHA Alternative ===
Plugin Name: Just Cloudflare Turnstile – Modern CAPTCHA Alternative
Author: Just There
Contributors: carlbensy16
Plugin URI: https://wordpress.org/plugins/just-cloudflare-turnstile
Documentation URI: https://justthere.co.uk/plugins/just-cloudflare-turnstile/documentation
Support URI: https://justthere.co.uk/plugins/just-cloudflare-turnstile/support
Feature Request URI: https://justthere.co.uk/plugins/just-cloudflare-turnstile/feature-request
Donate link: https://justthere.co.uk/plugins/support-us/
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.0
Version: 1.0.0
Requires PHP: 7.0
License: GNU General Public License v3.0 or later
License URI: http://www.gnu.org/licenses/gpl.html

Cloudflare Turnstile CAPTCHA for WordPress, WooCommerce, Elementor, and all major forms. Lightweight and privacy-first.

== Description ==

**Stop spam, not your users.** Just Cloudflare Turnstile is the all-in-one, lightweight, and privacy-friendly CAPTCHA plugin for WordPress. Effortlessly add Cloudflare Turnstile to all major forms and builders with a single plugin—no bloat, no tracking, just results.

**Why choose Just Cloudflare Turnstile?**
- Ultra-lightweight and fast: minimal scripts, async loading, and no tracking.
- Privacy-first: GDPR-friendly, no user data sent to third parties.
- Seamless integration: works with all major WordPress forms and builders.
- Smart settings: only loads where needed, with advanced whitelisting and error handling.
- Actively maintained and supported.

**Supported Integrations (v1.0.0):**
- **WordPress Core**: Login, Registration, Password Reset, Comment Form
- **WooCommerce**: Checkout, Login, Register, Password Reset
- **Elementor Pro**: Forms & Popups
- **WPForms**: All forms
- **Fluent Forms**: All forms
- **Gravity Forms**: All forms
- **Formidable Forms**: All forms
- **Contact Form 7**: All forms
- **Forminator**: All forms
- **Jetpack Forms**: All forms
- **Kadence Forms**: All forms (Kadence Blocks)

**Key Features:**
- Conditional script loading for performance
- Widget size, theme, and appearance options
- Defer scripts and disable-submit logic
- Whitelist by IP, user agent, or logged-in users
- Custom error and fallback messages
- Modern admin UI with onboarding wizard
- Optional plugin badge (credit where it's due!)

**Privacy & Performance:**
- Fully GDPR-friendly, no cookies or tracking
- Lightweight JS, async/deferral support
- Optimized for caching, AJAX, and dynamic forms
- No impact on Core Web Vitals

== Installation ==

1. Upload the plugin to your WordPress site or install via the plugin directory.
2. Activate the plugin.
3. Go to **Settings → Cloudflare Turnstile**.
4. Enter your **Site Key** and **Secret Key** from [Cloudflare Dashboard](https://dash.cloudflare.com/).
5. Enable protection for your forms and enjoy a spam-free experience!

== Screenshots ==

1. Global settings (site key, theme, behavior)
2. Whitelist rules and toggle settings
3. Turnstile on WooCommerce checkout
4. Turnstile on Elementor form
5. Seamless login protection on WordPress core

== Frequently Asked Questions ==

= Do I need a Cloudflare account? =
Yes, you only need to register your site for a Turnstile site key and secret key. No other Cloudflare services are required.

= Does this plugin work with Elementor Free? =
Turnstile integration currently supports **Elementor Pro** forms only.

= Is it compatible with caching and optimization plugins? =
Yes, scripts are deferred and widgets re-render dynamically for maximum compatibility.

= Can I skip Turnstile for certain users? =
Yes — whitelist logged-in users, IP addresses, or user agents in the settings.

= How do I get support or report a bug? =
Visit the [Just There support page](https://justthere.co.uk/contact/) or open an issue on the plugin's repository if available.

= Will you add support for more form plugins? =
We plan to expand integrations based on user feedback. Let us know which plugins you need support for!

== Support ==

For help, feature requests, or bug reports, please visit our [support page](https://justthere.co.uk/contact/) or check our documentation.

== Changelog ==

= 1.0.0 =
* Initial release
* WordPress, WooCommerce, Elementor, WPForms, Gravity Forms, Fluent Forms, Formidable, Contact Form 7, Forminator, Jetpack, Kadence integrations
* Dynamic rendering and validation
* Onboarding wizard and admin UI

== Upgrade Notice ==

= 1.0.0 =
Stable launch with complete Turnstile protection for WordPress, WooCommerce, Elementor, and all major form plugins.

== Copyright ==
Just Duplicate is built with ❤️ by Just There.

== Disclaimer ==
This plugin is not affiliated with or endorsed by Cloudflare, Inc. “Cloudflare” and “Turnstile” are trademarks of Cloudflare, Inc.

== Credits ==
Cloudflare Turnstile is a product of [Cloudflare](https://www.cloudflare.com/products/turnstile/)
Plugin developed by [Just There](https://justthere.co.uk)

== External Services ==
This plugin connects to Cloudflare Turnstile for spam prevention. It sends the following data:
- Site key
- User IP and user-agent (via Cloudflare SDK)

Cloudflare Turnstile Terms:
https://developers.cloudflare.com/turnstile/

Privacy Policy:
https://www.cloudflare.com/privacypolicy/