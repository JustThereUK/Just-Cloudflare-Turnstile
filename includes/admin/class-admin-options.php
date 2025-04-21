<?phpnif (!defined('ABSPATH')) {u	exit;o} ,// Create custom plugin settings menu add_action('admin_menu', 'jct_create_menu');'function jct_create_menu() {
    add_submenu_page(
        'options-general.php', // Parent slug
        'Cloudflare Turnstile', // Page title
        'Cloudflare Turnstile', // Menu title
        'manage_options', // Capability
        'jct', // Menu slug
        'jct_settings_page' // Callback function
    );
}

// Keys Updated
add_action('update_option_jct_key', 'jct_keys_updated', 10);
add_action('update_option_jct_secret', 'jct_keys_updated', 10);
function jct_keys_updated() {
	update_option('jct_tested', 'no');
}

// Admin test form to check Turnstile response
function jct_admin_test() {
?>
	<form action="" method="POST" class="jct-settings">
		<?php
		if (!empty(get_option('jct_key')) && !empty(get_option('jct_secret'))) {
			$check = jct_check();
			$success = '';
			$error = '';
			if (isset($check['success'])) $success = $check['success'];
			if (isset($check['error_code'])) $error = $check['error_code'];
			if ($success != true) {
				echo '<div style="padding: 20px 20px 25px 20px; margin: 20px 0 28px 0; background: #fff; border-radius: 20px; max-width: 500px; border: 2px solid #d5d5d5;">';
				echo '<p style="font-weight: 600; font-size: 19px; margin-top: 0; margin-bottom: 0;">' . esc_html__('Almost done...', 'just-cloudflare-turnstile') . '</p>';
			}
			if (!isset($_POST['jct-turnstile-response'])) {
				echo '<p>'
					. '<span style="color: red; font-weight: bold;">' . esc_html__('API keys have been updated. Please test the Turnstile API response below.', 'just-cloudflare-turnstile') . '</span>'
					. '<br/>'
					. esc_html__('Turnstile will not be added to any forms until the test is successfully complete.', 'just-cloudflare-turnstile')
					. '</p>';
			} else {
				if ($success == true) {
					update_option('jct_tested', 'yes');
				} else {
					if ($error == "missing-input-response") {
						echo '<p style="font-weight: bold; color: red;">' . jct_failed_message() . '</p>';
					} else {
						echo '<p style="font-weight: bold; color: red;">' . esc_html__('Failed! There is an error with your API settings. Please check & update them.', 'just-cloudflare-turnstile') . '</p>';
					}
				}
				if ($error) {
					echo '<p style="font-weight: bold;">' . esc_html__('Error message:', 'just-cloudflare-turnstile') . " " . jct_error_message($error) . '</p>';
				}
			}
			if ($success != true) {
				echo '<div style="margin-left: 0px;">';
				echo jct_field_show('', '', 'admin-test', 'admin-test');
				echo '</div><div style="margin-bottom: -20px;"></div>';
				echo '<button type="submit" style="margin-top: 10px; padding: 7px 10px; background: #1c781c; color: #fff; font-size: 15px; font-weight: bold; border: 1px solid #176017; border-radius: 4px; cursor: pointer;">
				' . esc_html__('TEST RESPONSE', 'just-cloudflare-turnstile') . ' <span class="dashicons dashicons-arrow-right-alt"></span>
				</button>';
				echo '</div>';
			}
		}
		?>
	</form>
<?php
}

// Show Settings Page
function jct_settings_page() {
?>
	<div class="jct-wrap wrap">

		<h1 style="font-weight: bold;"><?php echo esc_html__('Just Cloudflare Turnstile', 'just-cloudflare-turnstile'); ?></h1>

		<p style="margin-bottom: 0;"><?php echo esc_html__('Easily add the free CAPTCHA service called "Cloudflare Turnstile" to your WordPress forms to help prevent spam.', 'just-cloudflare-turnstile'); ?> <a href="https://www.cloudflare.com/en-gb/products/turnstile/" target="_blank"><?php echo esc_html__('Learn more.', 'just-cloudflare-turnstile'); ?></a>

		<div class="jct-admin-promo-top">

			<p>
				<a href="https://Just There.com/blog/how-to-add-cloudflare-turnstile-to-wordpress/?utm_campaign=simple-turnstile-plugin&utm_source=plugin-settings&utm_medium=guide" title="View our Turnstile plugin setup guide." target="_blank"><?php echo esc_html__('View setup guide', 'just-cloudflare-turnstile'); ?><span class="dashicons dashicons-external" style="margin-left: 2px; text-decoration: none;"></span></a> &nbsp;&#x2022;&nbsp; <?php echo esc_html__('Like this plugin?', 'just-cloudflare-turnstile'); ?> <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/reviews/#new-post" target="_blank" title="<?php echo esc_html__('Review on WordPress.org', 'just-cloudflare-turnstile'); ?>"><?php echo esc_html__('Please submit a review', 'just-cloudflare-turnstile'); ?></a> <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/reviews/#new-post" target="_blank" title="<?php echo esc_html__('Review on WordPress.org', 'just-cloudflare-turnstile'); ?>" style="text-decoration: none;">
					<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>
				</a>
			</p>

		</div>

		<?php
		if (empty(get_option('jct_tested')) || get_option('jct_tested') != 'yes') {
			echo jct_admin_test();
		}
		?>

		<form method="post" action="options.php" class="jct-settings">

			<?php settings_fields('jct-settings-group'); ?>
			<?php do_settings_sections('jct-settings-group'); ?>

			<hr style="margin: 20px 0 0 0;">

			<table class="form-table">

				<tr valign="top">
					<th scope="row" style="padding-bottom: 0;">

						<p style="font-size: 19px; margin-top: 0;"><?php echo esc_html__('API Key Settings:', 'just-cloudflare-turnstile'); ?></p>

						<?php
						if (get_option('jct_tested') == 'yes') {
							echo '<p style=" font-size: 15px; font-weight: bold; color: #1e8c1e;"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Success! Turnstile is working correctly with your API keys.', 'just-cloudflare-turnstile') . '</p>';
						} ?>

						<p style="margin-bottom: 2px;"><?php echo esc_html__('You can get your site key and secret key from here:', 'just-cloudflare-turnstile'); ?> <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank">https://dash.cloudflare.com/?to=/:account/turnstile</a></p>

					</th>
				</tr>

			</table>

			<table class="form-table">

				<tr valign="top">
					<th scope="row"><?php echo esc_html__('Site Key', 'just-cloudflare-turnstile'); ?></th>
					<td><input type="text" style="width: 240px;" name="jct_key" value="<?php echo esc_html(get_option('jct_key')); ?>" /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php echo esc_html__('Secret Key', 'just-cloudflare-turnstile'); ?></th>
					<td><input type="text" style="width: 240px;" name="jct_secret" value="<?php echo esc_html(get_option('jct_secret')); ?>" /></td>
				</tr>

			</table>

			<hr style="margin: 20px 0 10px 0;">

			<table class="form-table">

				<tr valign="top">
					<th scope="row" style="font-size: 19px; padding-bottom: 5px;"><?php echo esc_html__('General Settings:', 'just-cloudflare-turnstile'); ?></th>
				</tr>

				<tr valign="top">
					<th scope="row"><?php echo esc_html__('Theme', 'just-cloudflare-turnstile'); ?></th>
					<td>
						<select name="jct_theme">
							<option value="light" <?php if (!get_option('jct_theme') || get_option('jct_theme') == "light") { ?>selected<?php } ?>>
								<?php esc_html_e('Light', 'just-cloudflare-turnstile'); ?>
							</option>
							<option value="dark" <?php if (get_option('jct_theme') == "dark") { ?>selected<?php } ?>>
								<?php esc_html_e('Dark', 'just-cloudflare-turnstile'); ?>
							</option>
							<option value="auto" <?php if (get_option('jct_theme') == "auto") { ?>selected<?php } ?>>
								<?php esc_html_e('Auto', 'just-cloudflare-turnstile'); ?>
							</option>
						</select>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php echo esc_html__('Language', 'just-cloudflare-turnstile'); ?></th>
					<td>
						<select name="jct_language">
						<?php
						$languages = array(
							'auto'   => esc_html__( 'Auto Detect', 'just-cloudflare-turnstile' ),
							'ar-eg'  => esc_html__( 'Arabic (Egypt)', 'just-cloudflare-turnstile' ),
							'bg-bg'  => esc_html__( 'Bulgarian (Bulgaria)', 'just-cloudflare-turnstile' ),
							'zh-cn'  => esc_html__( 'Chinese (Simplified, China)', 'just-cloudflare-turnstile' ),
							'zh-tw'  => esc_html__( 'Chinese (Traditional, Taiwan)', 'just-cloudflare-turnstile' ),
							'hr-hr'  => esc_html__( 'Croatian (Croatia)', 'just-cloudflare-turnstile' ),
							'cs-cz'  => esc_html__( 'Czech (Czech Republic)', 'just-cloudflare-turnstile' ),
							'da-dk'  => esc_html__( 'Danish (Denmark)', 'just-cloudflare-turnstile' ),
							'nl-nl'  => esc_html__( 'Dutch (Netherlands)', 'just-cloudflare-turnstile' ),
							'en-us'  => esc_html__( 'English (United States)', 'just-cloudflare-turnstile' ),
							'fa-ir'  => esc_html__( 'Farsi (Iran)', 'just-cloudflare-turnstile' ),
							'fi-fi'  => esc_html__( 'Finnish (Finland)', 'just-cloudflare-turnstile' ),
							'fr-fr'  => esc_html__( 'French (France)', 'just-cloudflare-turnstile' ),
							'de-de'  => esc_html__( 'German (Germany)', 'just-cloudflare-turnstile' ),
							'el-gr'  => esc_html__( 'Greek (Greece)', 'just-cloudflare-turnstile' ),
							'he-il'  => esc_html__( 'Hebrew (Israel)', 'just-cloudflare-turnstile' ),
							'hi-in'  => esc_html__( 'Hindi (India)', 'just-cloudflare-turnstile' ),
							'hu-hu'  => esc_html__( 'Hungarian (Hungary)', 'just-cloudflare-turnstile' ),
							'id-id'  => esc_html__( 'Indonesian (Indonesia)', 'just-cloudflare-turnstile' ),
							'it-it'  => esc_html__( 'Italian (Italy)', 'just-cloudflare-turnstile' ),
							'ja-jp'  => esc_html__( 'Japanese (Japan)', 'just-cloudflare-turnstile' ),
							'tlh'    => esc_html__( 'Klingon (Qo’noS)', 'just-cloudflare-turnstile' ),
							'ko-kr'  => esc_html__( 'Korean (Korea)', 'just-cloudflare-turnstile' ),
							'lt-lt'  => esc_html__( 'Lithuanian (Lithuania)', 'just-cloudflare-turnstile' ),
							'ms-my'  => esc_html__( 'Malay (Malaysia)', 'just-cloudflare-turnstile' ),
							'nb-no'  => esc_html__( 'Norwegian Bokmål (Norway)', 'just-cloudflare-turnstile' ),
							'pl-pl'  => esc_html__( 'Polish (Poland)', 'just-cloudflare-turnstile' ),
							'pt-br'  => esc_html__( 'Portuguese (Brazil)', 'just-cloudflare-turnstile' ),
							'ro-ro'  => esc_html__( 'Romanian (Romania)', 'just-cloudflare-turnstile' ),
							'ru-ru'  => esc_html__( 'Russian (Russia)', 'just-cloudflare-turnstile' ),
							'sr-ba'  => esc_html__( 'Serbian (Bosnia and Herzegovina)', 'just-cloudflare-turnstile' ),
							'sk-sk'  => esc_html__( 'Slovak (Slovakia)', 'just-cloudflare-turnstile' ),
							'sl-si'  => esc_html__( 'Slovenian (Slovenia)', 'just-cloudflare-turnstile' ),
							'es-es'  => esc_html__( 'Spanish (Spain)', 'just-cloudflare-turnstile' ),
							'sv-se'  => esc_html__( 'Swedish (Sweden)', 'just-cloudflare-turnstile' ),
							'tl-ph'  => esc_html__( 'Tagalog (Philippines)', 'just-cloudflare-turnstile' ),
							'th-th'  => esc_html__( 'Thai (Thailand)', 'just-cloudflare-turnstile' ),
							'tr-tr'  => esc_html__( 'Turkish (Turkey)', 'just-cloudflare-turnstile' ),
							'uk-ua'  => esc_html__( 'Ukrainian (Ukraine)', 'just-cloudflare-turnstile' ),
							'vi-vn'  => esc_html__( 'Vietnamese (Vietnam)', 'just-cloudflare-turnstile' ),
						);
						$auto = $languages['auto'];
						unset($languages['auto']);
						asort($languages);
						$languages = array_merge(array('auto' => $auto), $languages);
						foreach ($languages as $code => $name) {
							$selected = '';
							if(get_option('jct_language') == $code) { $selected = 'selected'; }
							?>
								<option value="<?php echo esc_attr($code); ?>" <?php echo esc_attr($selected); ?>>
									<?php echo esc_html($name); ?>
								</option>
							<?php
						}
						?>
						</select>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<?php echo esc_html__('Disable Submit Button', 'just-cloudflare-turnstile'); ?>
					</th>
					<td><input type="checkbox" name="jct_disable_button" <?php if (get_option('jct_disable_button')) { ?>checked<?php } ?>>
						<i style="font-size: 10px;"><?php echo esc_html__('When enabled, the user will not be able to click submit until the Turnstile challenge is completed.', 'just-cloudflare-turnstile'); ?></i>
					</td>
				</tr>

			</table>

			<button type="button" class="jct-accordion" id="jct-accordion-whitelist"><?php echo esc_html__('Advanced Settings', 'just-cloudflare-turnstile'); ?></button>
			<div class="jct-panel">

				<p style="margin: 0 0 20px 0; padding-bottom: 20px; border-bottom: 1px solid #f3f3f3;">
					<?php echo esc_html__('These settings are for more advanced customisation. If you are not sure about these, they do not need to be changed.', 'just-cloudflare-turnstile'); ?>
				</p>

				<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

					<tr valign="top">
						<th scope="row"><?php echo esc_html__('Widget Size', 'just-cloudflare-turnstile'); ?></th>
						<td>
							<select name="jct_size" style="width: 100%;">
								<option value="normal" <?php if (!get_option('jct_size') || get_option('jct_size') == "normal") { ?>selected<?php } ?>>
									<?php esc_html_e('Normal (300px)', 'just-cloudflare-turnstile'); ?>
								</option>
								<option value="flexible" <?php if (get_option('jct_size') == "flexible") { ?>selected<?php } ?>>
									<?php esc_html_e('Flexible (100%)', 'just-cloudflare-turnstile'); ?>
								</option>
								<option value="compact" <?php if (get_option('jct_size') == "compact") { ?>selected<?php } ?>>
									<?php esc_html_e('Compact (150px)', 'just-cloudflare-turnstile'); ?>
								</option>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo esc_html__('Appearance Mode', 'just-cloudflare-turnstile'); ?></th>
						<td>
							<select name="jct_appearance" style="width: 100%;">
							<?php
							$appearances = array(
								'always' => esc_html__( 'Always', 'just-cloudflare-turnstile' ),
								// 'execute' => esc_html__( 'Execute', 'just-cloudflare-turnstile' ), // Not really needed
								'interaction-only' => esc_html__( 'Interaction Only', 'just-cloudflare-turnstile' ),
							);
							foreach ($appearances as $code => $name) {
								$selected = '';
								if(get_option('jct_appearance') == $code) { $selected = 'selected'; }
								?>
									<option value="<?php echo esc_attr($code); ?>" <?php echo esc_attr($selected); ?>>
										<?php echo esc_html($name); ?>
									</option>
								<?php
							}
							?>
							</select>
							<br/><br/>
							<div class="wcu-appearance-always" style="display: none;"><i style="font-size: 10px;"><?php echo esc_html__( 'Turnstile Widget is always displayed for all visitors.', 'just-cloudflare-turnstile' ); ?></i></div>
							<div class="wcu-appearance-execute" style="display: none;"><i style="font-size: 10px;"><?php echo esc_html__( 'Turnstile Widget is only displayed after the challenge begins.', 'just-cloudflare-turnstile' ); ?></i></div>
							<div class="wcu-appearance-interaction-only" style="display: none;"><i style="font-size: 10px;"><?php echo esc_html__( 'Turnstile Widget is only displayed in cases where an interaction is required. This essentially makes it "invisible" for most valid users.', 'just-cloudflare-turnstile' ); ?></i></div>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__('Defer Scripts', 'just-cloudflare-turnstile'); ?>
						</th>
						<td><input style="margin: 5px 0 20px 10px;" type="checkbox" name="jct_defer_scripts" <?php if (get_option('jct_defer_scripts', 1)) { ?>checked<?php } ?>>
						<i style="font-size: 10px;"><?php echo esc_html__('When enabled, the javascript files loaded by the plugin will be deferred. You can disable this if it causes any issues with your other optimisations.', 'just-cloudflare-turnstile'); ?></i>
					</td>

					<tr valign="top">
						<th scope="row"><?php echo esc_html__('Custom Error Message', 'just-cloudflare-turnstile'); ?></th>
						<td>
							<textarea type="text" style="width: 202px; margin-bottom: 5px;" name="jct_error_message"
							placeholder="<?php echo jct_failed_message(1); ?>"
							/><?php if(get_option('jct_error_message')) { echo esc_html(get_option('jct_error_message')); } ?></textarea>
							<br /><i style="font-size: 10px;"><?php echo esc_html__('Shown if the form is submitted without completing the Turnstile challenge. Leave blank to use the default message (localized):', 'just-cloudflare-turnstile') . ' "' . jct_failed_message(1) . '"'; ?></i>
						</td>
					</tr>

					<tr valign="top" style="border: 0;">
						<th scope="row">
							<?php echo esc_html__('Extra Failure Message', 'just-cloudflare-turnstile'); ?>
						</th>
						<td>
							<input type="checkbox" name="jct_failure_message_enable" <?php if (get_option('jct_failure_message_enable', 0)) { ?>checked<?php } ?>>
						</td>
					</tr>
					<tr valign="top" class="jct-failure-message" style="border: 0;">
						<th scope="row" style="padding-top: 0px;">
						<i style="font-size: 10px;">
							<?php echo esc_html__('HTML Markup Allowed.', 'just-cloudflare-turnstile'); ?>
						</i>
					</th>
						<td style="padding-top: 0px;">
							<textarea type="text" style="width: 202px; margin-bottom: 5px;" name="jct_failure_message" rows="3"
							placeholder="<?php echo esc_html__('Failed to verify you are human. Please contact us if you are having issues.', 'just-cloudflare-turnstile'); ?>"
							/><?php if(get_option('jct_failure_message')) { echo esc_html(get_option('jct_failure_message')); } ?></textarea>
							<i style="font-size: 10px;"><?php echo esc_html__('This will show a message below the Turnstile widget if they receive the "Failure!" response. Useful to give instructions in the *very rare* case a valid user is being flagged as spam.', 'just-cloudflare-turnstile'); ?></i>
							<br/><br/>
							<i style="font-size: 10px;"><?php echo esc_html__('Currently it is not possible to edit the actual "Failure!" message shown on the widget.', 'just-cloudflare-turnstile'); ?></i>
						</td>
					</tr>
					<script>
					jQuery(document).ready(function() {
						jQuery('.jct-failure-message').hide();
						jQuery('input[name="jct_failure_message_enable"]').change(function() {
							if(jQuery(this).is(":checked")) {
								jQuery('.jct-failure-message').show();
							} else {
								jQuery('.jct-failure-message').hide();
							}
						});
						jQuery('input[name="jct_failure_message_enable"]').trigger('change');						
					});
					</script>

				</tr>

				</table>

			</div>

			<button type="button" class="jct-accordion" id="jct-accordion-whitelist"><?php echo esc_html__('Whitelist Settings', 'just-cloudflare-turnstile'); ?></button>
			<div class="jct-panel">

				<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__('Logged In Users', 'just-cloudflare-turnstile'); ?>
						</th>
						<td><input style="margin-top: 5px;" type="checkbox" name="jct_whitelist_users" <?php if (get_option('jct_whitelist_users')) { ?>checked<?php } ?>>
							<i style="font-size: 10px;"><?php echo esc_html__('When enabled, logged in users will not see the Turnstile challenge.', 'just-cloudflare-turnstile'); ?></i>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php echo esc_html__('IP Addresses', 'just-cloudflare-turnstile'); ?></th>
						<td>
							<textarea style="width: 240px;" name="jct_whitelist_ips"><?php echo sanitize_textarea_field(get_option('jct_whitelist_ips')); ?></textarea>
							<br /><i style="font-size: 10px;"><?php echo esc_html__('One per line. Wildcards are not supported. All visitors with listed IP addresses will not see the Turnstile challenge. Warning: If an attacker knows one of the whitelisted IP addresses, they might be able to spoof that address to bypass Turnstile.', 'just-cloudflare-turnstile'); ?></i>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo esc_html__('User Agents', 'just-cloudflare-turnstile'); ?></th>
						<td>
							<textarea style="width: 240px;" name="jct_whitelist_agents"><?php echo sanitize_textarea_field(get_option('jct_whitelist_agents')); ?></textarea>
							<br /><i style="font-size: 10px;"><?php echo esc_html__('One per line.  All visitors with listed User Agents will not see the Turnstile challenge. Warning: If an attacker knows one of the whitelisted User Agents, they might be able to spoof that User Agent to bypass Turnstile.', 'just-cloudflare-turnstile'); ?></i>
						</td>
					</tr>

				</table>

			</div>

			<hr style="margin: 40px 0 10px 0;">

			<div class="jct-integrations">

			<table class="form-table" style="margin-bottom: -35px;">

				<tr valign="top">
					<th scope="row">
						<span style="font-size: 19px;"><?php echo esc_html__('Enable Turnstile on your forms:', 'just-cloudflare-turnstile'); ?></span>
						<p><?php echo esc_html__('Select the dropdown for each integration, and choose when specific forms you want to enable Turnstile on.', 'just-cloudflare-turnstile'); ?></p>
					</th>
				</tr>

			</table>

			<button type="button" class="jct-accordion" id="jct-accordion-wordpress"><?php echo esc_html__('Default WordPress Forms', 'just-cloudflare-turnstile'); ?></button>
			<div class="jct-panel">

				<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__('WordPress Login', 'just-cloudflare-turnstile'); ?> <a href="#" class="jct_toggle_login" style="font-size: 10px; text-decoration: none; color: #333;">&#9660;</a>
							<span id="jct_login_only_option" style="display: none;" title="<?php echo esc_html__('Enable this option to only enable on default WordPress login form at wp-login.php', 'just-cloudflare-turnstile'); ?>">
							<br/><br/>
								<label style="float: left; margin: -5px 10px 0px 0; font-weight: 600; font-size: 10px;" for="jct_login_only"><?php echo esc_html__('Only enable on default wp-login.php page', 'just-cloudflare-turnstile'); ?></label>
								<input style="float: left; transform: scale(0.75); margin-top: -7px; margin-left: -5px;"
								type="checkbox" name="jct_login_only" <?php if (get_option('jct_login_only')) { ?>checked<?php } ?>>
							</span>
						</th>
						<td><input type="checkbox" name="jct_login" <?php if (get_option('jct_login')) { ?>checked<?php } ?>></td>
					</tr>
					<script>
					jQuery(document).ready(function() {
						jQuery('.jct_toggle_login').click(function(e) {
							e.preventDefault();
							jQuery('#jct_login_only_option').toggle();
						});
					});
					</script>

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__('WordPress Register', 'just-cloudflare-turnstile'); ?> <a href="#" class="jct_toggle_register" style="font-size: 10px; text-decoration: none; color: #333;">&#9660;</a>
							<span id="jct_register_only_option" style="display: none;" title="<?php echo esc_html__('Enable this option to only enable on default WordPress register form at wp-login.php?action=register', 'just-cloudflare-turnstile'); ?>">
							<br/><br/>
								<label style="float: left; margin: -5px 10px 0px 0; font-weight: 600; font-size: 10px;" for="jct_register_only"><?php echo esc_html__('Only enable on default wp-login.php page', 'just-cloudflare-turnstile'); ?></label>
								<input style="float: left; transform: scale(0.75); margin-top: -7px; margin-left: -5px;"
								type="checkbox" name="jct_register_only" <?php if (get_option('jct_register_only')) { ?>checked<?php } ?>>
							</span>
						</th>
						<td><input type="checkbox" name="jct_register" <?php if (get_option('jct_register')) { ?>checked<?php } ?>></td>
					</tr>
					<script>
					jQuery(document).ready(function() {
						jQuery('.jct_toggle_register').click(function(e) {
							e.preventDefault();
							jQuery('#jct_register_only_option').toggle();
						});
					});
					</script>

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__('WordPress Reset Password', 'just-cloudflare-turnstile'); ?>
						</th>
						<td><input type="checkbox" name="jct_reset" <?php if (get_option('jct_reset')) { ?>checked<?php } ?>></td>
					</tr>

					<tr valign="top" style="border: 0;">
						<th scope="row">
							<?php echo esc_html__('WordPress Comment', 'just-cloudflare-turnstile'); ?> <a href="#" class="jct_toggle_comments" style="font-size: 10px; text-decoration: none; color: #333;">&#9660;</a>
							<span id="jct_ajax_comments_option" style="display: none;" title="<?php echo esc_html__('Enable this if you are using an AJAX based comments form plugin or theme.', 'just-cloudflare-turnstile'); ?>">
							<br/><br/>
								<label style="float: left; margin: -5px 10px 0px 0; font-weight: 600; font-size: 10px;" for="jct_ajax_comments"><?php echo esc_html__('AJAX comments form?', 'just-cloudflare-turnstile'); ?></label>
								<input style="float: left; transform: scale(0.75); margin-top: -7px; margin-left: -5px;"
								type="checkbox" name="jct_ajax_comments"
								<?php if(!cft_is_plugin_active('wpdiscuz/class.WpdiscuzCore.php') && !cft_is_plugin_active('wp-ajaxify-comments/wp-ajaxify-comments.php')) { ?>
								<?php if (get_option('jct_ajax_comments')) { ?>checked<?php } ?>>
								<?php } else { ?>checked disabled<?php } ?>
							</span>
						</th>
						<td>
							<input type="checkbox" name="jct_comment" <?php if (get_option('jct_comment')) { ?>checked<?php } ?>>
							<?php if (cft_is_plugin_active('jetpack/jetpack.php')) { ?>
								<br /><i style="font-size: 10px;"><?php echo esc_html__('Due to Jetpack limitations, this does NOT currently work with Jetpack comments form enabled.', 'just-cloudflare-turnstile'); ?></i>
							<?php } ?>
							<?php if (cft_is_plugin_active('wpdiscuz/class.WpdiscuzCore.php')) { ?>
								<i style="font-size: 11px;"><?php echo esc_html__('Compatible with wpDiscuz!', 'just-cloudflare-turnstile'); ?></i>
							<?php } ?>
						</td>
					</tr>
					<script>
						jQuery(document).ready(function() {
							jQuery('.jct_toggle_comments').click(function(e) {
								e.preventDefault();
								jQuery('#jct_ajax_comments_option').toggle();
							});
						});
					</script>

				</table>

			</div>

			<?php $not_installed = array(); ?>

			<?php // WooCommerce
			if (cft_is_plugin_active('woocommerce/woocommerce.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('WooCommerce Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('WooCommerce Login', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_woo_login" <?php if (get_option('jct_woo_login')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('WooCommerce Register', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_woo_register" <?php if (get_option('jct_woo_register')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('WooCommerce Reset Password', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_woo_reset" <?php if (get_option('jct_woo_reset')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('WooCommerce Checkout', 'just-cloudflare-turnstile'); ?>
								<br /><br />
								- <?php echo esc_html__('Guest Checkout Only', 'just-cloudflare-turnstile'); ?>
								<br /><br />
								- <?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?>
								<br/><br/>
							</th>
							<td>
								<input style="margin-top: 5px;" type="checkbox" name="jct_woo_checkout" <?php if (get_option('jct_woo_checkout')) { ?>checked<?php } ?>>
								<br /><br />
								<input style="margin-top: 5px;" type="checkbox" name="jct_guest_only" <?php if (get_option('jct_guest_only')) { ?>checked<?php } ?>>
								<br /><br />
								<select name="jct_woo_checkout_pos">
									<option value="beforepay" <?php if (!get_option('jct_woo_checkout_pos') || get_option('jct_woo_checkout_pos') == "beforepay") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Payment', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="afterpay" <?php if (get_option('jct_woo_checkout_pos') == "afterpay") { ?>selected<?php } ?>>
										<?php esc_html_e('After Payment', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="beforesubmit" <?php if (get_option('jct_woo_checkout_pos') == "beforesubmit") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Pay Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="beforebilling" <?php if (get_option('jct_woo_checkout_pos') == "beforebilling") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Billing', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="afterbilling" <?php if (get_option('jct_woo_checkout_pos') == "afterbilling") { ?>selected<?php } ?>>
										<?php esc_html_e('After Billing', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

						<tr valign="top" style="border-bottom: 1px solid #f3f3f3;">
							<th scope="row">
								<?php echo esc_html__('WooCommerce Pay for Order', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_woo_checkout_pay" <?php if (get_option('jct_woo_checkout_pay')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php if ( class_exists( 'WooCommerce' ) ) { ?>

						<?php $available_gateways = WC()->payment_gateways->get_available_payment_gateways(); ?>

						<?php if(!empty($available_gateways)) { ?>

							<br/>

							<p style="font-size: 15px; font-weight: 600;">
								<?php echo esc_html__('Payment Methods to Skip', 'just-cloudflare-turnstile'); ?> <a href="#" class="jct_toggle_skip_methods" style="font-size: 10px; text-decoration: none; color: #333;">&#9660;</a>
							</p>
							<script>
								jQuery(document).ready(function() {
									jQuery('.jct_toggle_skip_methods').click(function(e) {
										e.preventDefault();
										jQuery('#toggleContentSkipMethods').toggle();
									});
								});
							</script>

							<div id="toggleContentSkipMethods" style="display: none;"> <!-- Initially hidden -->
							
								<i style="font-size: 10px;">
									<?php echo esc_html__("If selected below, Turnstile check will not be run for that specific payment method.", 'just-cloudflare-turnstile'); ?>
									<br/>
									<?php echo esc_html__("Useful for 'Express Checkout' payment methods compatibility.", 'just-cloudflare-turnstile'); ?>
								</i>

								<?php
								$selected_payment_methods = get_option('jct_selected_payment_methods', array());
								if(!$selected_payment_methods) $selected_payment_methods = array();
								if(!empty($available_gateways)) { ?>
								<div style="margin-top: 10px; max-width: 200px;">
								<?php foreach ( $available_gateways as $gateway ) : ?>
								<p>
									<input type="checkbox" name="jct_selected_payment_methods[]" style="float: none; margin-top: 2px;"
									value="<?php echo esc_attr( $gateway->id ); ?>" <?php echo in_array( $gateway->id, $selected_payment_methods, true ) ? 'checked' : ''; ?> >
									<label><?php echo esc_html( $gateway->get_title() ); ?></label>
								</p>
								<?php endforeach; ?>
								</div>
								<?php } ?>

							</div>

					<?php } ?>

				<?php } ?>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">' . esc_html__('WooCommerce', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // EDD
			if (cft_is_plugin_active('easy-digital-downloads/easy-digital-downloads.php') || cft_is_plugin_active('easy-digital-downloads-pro/easy-digital-downloads.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Easy Digital Downloads', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('EDD Checkout', 'just-cloudflare-turnstile'); ?>
								<br /><br />
								- <?php echo esc_html__('Guest Checkout Only', 'just-cloudflare-turnstile'); ?>
							</th>
							<td>
								<input type="checkbox" name="jct_edd_checkout" <?php if (get_option('jct_edd_checkout')) { ?>checked<?php } ?>>
								<br /><br />
								<input type="checkbox" name="jct_edd_guest_only" <?php if (get_option('jct_edd_guest_only')) { ?>checked<?php } ?>>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('EDD Login', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_edd_login" <?php if (get_option('jct_edd_login')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('EDD Register', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_edd_register" <?php if (get_option('jct_edd_register')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/easy-digital-downloads/" target="_blank">' . esc_html__('Easy Digital Downloads', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Paid Memberships PRO
			if (cft_is_plugin_active('paid-memberships-pro/paid-memberships-pro.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Paid Memberships Pro', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Checkout / Registration', 'just-cloudflare-turnstile'); ?>
								<br /><br />
								- <?php echo esc_html__('Guest Checkout Only', 'just-cloudflare-turnstile'); ?>
							</th>
							<td>
								<input type="checkbox" name="jct_pmp_checkout" <?php if (get_option('jct_pmp_checkout')) { ?>checked<?php } ?>>
								<br /><br />
								<input type="checkbox" name="jct_pmp_guest_only" <?php if (get_option('jct_pmp_guest_only')) { ?>checked<?php } ?>>
							</td>
						</tr>

						<script>
						jQuery(document).ready(function(){
							jQuery("input[name='jct_login']").change(function(){
								if(jQuery("input[name='jct_login']").is(':checked')){
									jQuery('#jct_pmp_login').prop('checked', true);
								} else {
									jQuery('#jct_pmp_login').prop('checked', false);
								}
							});
						});
						</script>
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Login Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type='checkbox' name='jct_pmp_login' id='jct_pmp_login' <?php if (get_option('jct_login')) { ?>checked<?php } ?>
							title='<?php echo esc_html__('Edit via "WordPress Login" option in the "Default WordPress Forms" settings.', 'just-cloudflare-turnstile'); ?>' disabled></td>
						</tr>

						<!-- Lost Password -->
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Lost Password Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type='checkbox' name='jct_wpuf_reset' id='jct_wpuf_reset'
							title='<?php echo esc_html__('Currently Turnstile can not be implemented on the lost password form when PMP is installed.', 'just-cloudflare-turnstile'); ?>'
							disabled></td>
						</tr>
						<!-- Set name="jct_reset" to disabled and unchecked -->
						<script>
						jQuery(document).ready(function(){
							jQuery("input[name='jct_reset']").prop('disabled', true);
							jQuery("input[name='jct_reset']").prop('checked', false);
							jQuery("input[name='jct_reset']").attr('title', '<?php echo esc_html__('Currently Turnstile can not be implemented on the lost password form when PMP is installed.', 'just-cloudflare-turnstile'); ?>');
						});
						</script>
						<!-- Show X inside checkbox -->
						<style>
						#jct_wpuf_reset:after, input[name='jct_reset']:after {
							content: "X";
							color: #333;
							font-weight: bold;
							font-size: 15px;
							position: absolute;
							margin-left: -5px;
							margin-top: 7px;
						}
						</style>
						
					</table>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://en-gb.wordpress.org/plugins/paid-memberships-pro/" target="_blank">' . esc_html__('Paid Memberships PRO', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Contact Form 7
			if (cft_is_plugin_active('contact-form-7/wp-contact-form-7.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Contact Form 7', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all CF7 Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_cf7_all" <?php if (get_option('jct_cf7_all')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<br />

					<?php echo esc_html__('To add Turnstile to individual Contact Form 7 forms, simply add this shortcode to any of your forms (in the form editor):', 'just-cloudflare-turnstile'); ?>
					<br /><span style="color: red; font-size: 15px; font-weight: bold;">[cf7-simple-turnstile]</span>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">' . esc_html__('Contact Form 7', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // WPForms
			if (cft_is_plugin_active('wpforms-lite/wpforms.php') || cft_is_plugin_active('wpforms/wpforms.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('WPForms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all WPForms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_wpforms" <?php if (get_option('jct_wpforms')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added before/after the submit button, on ALL your forms created with WPForms.', 'just-cloudflare-turnstile'); ?>
					<?php echo esc_html__('Note: WPForms has an option to configure Turnstile on its own Settings page "CAPTCHA" tab. You should only enable it in one place, either here -OR- in those settings.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -15px;">

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_wpforms_pos">
									<option value="before" <?php if (!get_option('jct_wpforms_pos') || get_option('jct_wpforms_pos') == "before") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="after" <?php if (get_option('jct_wpforms_pos') == "after") { ?>selected<?php } ?>>
										<?php esc_html_e('After Button', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

					</table>

					<table class="form-table" style="margin-bottom: -10px;">
						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Disabled Form IDs', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<input type="text" name="jct_wpforms_disable" value="<?php echo esc_html(get_option('jct_wpforms_disable')); ?>" />
							</td>
						</tr>
					</table>
					<i style="font-size: 10px;">
						<?php echo sprintf(__('If you want to DISABLE the Turnstile widget on certain forms, enter the %s ID in this field.', 'just-cloudflare-turnstile'), 'WPForms Form'); ?>
						<?php echo esc_html__('Separate each ID with a comma, for example: 5,10', 'just-cloudflare-turnstile'); ?>
					</i>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/wpforms-lite/" target="_blank">' . esc_html__('WPForms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Gravity Forms
			if (cft_is_plugin_active('gravityforms/gravityforms.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Gravity Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Gravity Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_gravity" <?php if (get_option('jct_gravity')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added before/after the submit button, on ALL your forms created with Gravity Forms.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -15px;">

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_gravity_pos">
									<option value="before" <?php if (!get_option('jct_gravity_pos') || get_option('jct_gravity_pos') == "before") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="after" <?php if (get_option('jct_gravity_pos') == "after") { ?>selected<?php } ?>>
										<?php esc_html_e('After Button', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

					</table>

					<table class="form-table" style="margin-bottom: -10px;">
						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Disabled Form IDs', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<input type="text" name="jct_gravity_disable" value="<?php echo esc_html(get_option('jct_gravity_disable')); ?>" />
							</td>
						</tr>
					</table>
					<i style="font-size: 10px;">
						<?php echo sprintf(__('If you want to DISABLE the Turnstile widget on certain forms, enter the %s ID in this field.', 'just-cloudflare-turnstile'), 'Gravity Form'); ?>
						<?php echo esc_html__('Separate each ID with a comma, for example: 5,10', 'just-cloudflare-turnstile'); ?>
					</i>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://www.gravityforms.com/" target="_blank">' . esc_html__('Gravity Forms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Fluent Forms
			if (cft_is_plugin_active('fluentform/fluentform.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Fluent Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Fluent Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_fluent" <?php if (get_option('jct_fluent')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with Fluent Forms.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -10px;">
						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Disabled Form IDs', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<input type="text" name="jct_fluent_disable" value="<?php echo esc_html(get_option('jct_fluent_disable')); ?>" />
							</td>
						</tr>
					</table>
					<i style="font-size: 10px;">
						<?php echo sprintf(__('If you want to DISABLE the Turnstile widget on certain forms, enter the %s ID in this field.', 'just-cloudflare-turnstile'), 'Fluent Form'); ?>
						<?php echo esc_html__('Separate each ID with a comma, for example: 5,10', 'just-cloudflare-turnstile'); ?>
					</i>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/fluentform/" target="_blank">' . esc_html__('Fluent Forms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Jetpack Forms
			if (cft_is_plugin_active('jetpack/jetpack.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Jetpack Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Jetpack Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_jetpack" <?php if (get_option('jct_jetpack')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added after the submit button, on ALL your forms created with Jetpack Forms.', 'just-cloudflare-turnstile'); ?>
				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/jetpack/" target="_blank">' . esc_html__('Jetpack Forms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Formidable Forms
			if (cft_is_plugin_active('formidable/formidable.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Formidable Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Formidable Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_formidable" <?php if (get_option('jct_formidable')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with Formidable Forms.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -15px;">

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_formidable_pos">
									<option value="before" <?php if (!get_option('jct_formidable_pos') || get_option('jct_formidable_pos') == "before") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="after" <?php if (get_option('jct_formidable_pos') == "after") { ?>selected<?php } ?>>
										<?php esc_html_e('After Button', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

					</table>
				
					<table class="form-table" style="margin-bottom: -10px;">
						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Disabled Form IDs', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<input type="text" name="jct_formidable_disable" value="<?php echo esc_html(get_option('jct_formidable_disable')); ?>" />
							</td>
						</tr>
					</table>
					<i style="font-size: 10px;">
						<?php echo sprintf(__('If you want to DISABLE the Turnstile widget on certain forms, enter the %s ID in this field.', 'just-cloudflare-turnstile'), 'Formidable Form'); ?>
						<?php echo esc_html__('Separate each ID with a comma, for example: 5,10', 'just-cloudflare-turnstile'); ?>
					</i>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/formidable/" target="_blank">' . esc_html__('Formidable', 'just-cloudflare-turnstile') . '</a>');
			}
			?>
			
			<?php // Forminator Forms
			if (cft_is_plugin_active('forminator/forminator.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Forminator Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Forminator Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_forminator" <?php if (get_option('jct_forminator')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with Forminator Forms.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -15px;">

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_forminator_pos">
									<option value="before" <?php if (!get_option('jct_forminator_pos') || get_option('jct_forminator_pos') == "before") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="after" <?php if (get_option('jct_forminator_pos') == "after") { ?>selected<?php } ?>>
										<?php esc_html_e('After Button', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

					</table>

					<table class="form-table" style="margin-bottom: -10px;">
						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Disabled Form IDs', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<input type="text" name="jct_forminator_disable" value="<?php echo esc_html(get_option('jct_forminator_disable')); ?>" />
							</td>
						</tr>
					</table>
					<i style="font-size: 10px;">
						<?php echo sprintf(__('If you want to DISABLE the Turnstile widget on certain forms, enter the %s ID in this field.', 'just-cloudflare-turnstile'), 'Forminator Form'); ?>
						<?php echo esc_html__('Separate each ID with a comma, for example: 5,10', 'just-cloudflare-turnstile'); ?>
					</i>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/forminator/" target="_blank">' . esc_html__('Forminator', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // WS Form
			if (cft_is_plugin_active('ws-form/ws-form.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('WS Form', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<p>
						<?php echo esc_html__('Currently WS Form is not supported by this plugin, however their plugin does have its own Turnstile addon.', 'just-cloudflare-turnstile'); ?>
						<a href="https://wsform.com/knowledgebase/turnstile/" target="_blank"><?php echo esc_html__('Click here for more information.', 'just-cloudflare-turnstile'); ?></a>
					</p>

				</div>
			<?php
			}
			?>

			<?php // Ninja Forms
			if (cft_is_plugin_active('ninja-forms/ninja-forms.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Ninja Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<p>
						<?php echo esc_html__('Currently Ninja Forms is not supported by this plugin.', 'just-cloudflare-turnstile'); ?>
					</p>

				</div>
			<?php
			}
			?>

			<?php // Elementor Forms
			if ( cft_is_plugin_active('elementor-pro/elementor-pro.php') || cft_is_plugin_active('pro-elements/pro-elements.php') ) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Elementor Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Elementor Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_elementor" <?php if (get_option('jct_elementor')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with Elementor Pro Forms.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -15px;">

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_elementor_pos">
									<option value="before" <?php if (!get_option('jct_elementor_pos') || get_option('jct_elementor_pos') == "before") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="after" <?php if (get_option('jct_elementor_pos') == "after") { ?>selected<?php } ?>>
										<?php esc_html_e('After Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="afterform" <?php if (get_option('jct_elementor_pos') == "afterform") { ?>selected<?php } ?>>
										<?php esc_html_e('After Form', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

					</table>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://elementor.com/features/form-builder/" target="_blank">' . esc_html__('Elementor Forms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>
	
			<?php // Mailchimp for WordPress
			if (cft_is_plugin_active('mailchimp-for-wp/mailchimp-for-wp.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('MC4WP: Mailchimp for WordPress', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<?php echo esc_html__('To add Turnstile to Mailchimp for WordPress, simply add this shortcode to any of your forms (in the form editor):', 'just-cloudflare-turnstile'); ?>
					<br /><span style="color: red; font-size: 15px; font-weight: bold;">[mc4wp-simple-turnstile]</span>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/mailchimp-for-wp/" target="_blank">' . esc_html__('Mailchimp for WordPress', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // MailPoet
			if (cft_is_plugin_active('mailpoet/mailpoet.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('MailPoet', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all MailPoet Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_mailpoet" <?php if (get_option('jct_mailpoet')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with MailPoet.', 'just-cloudflare-turnstile'); ?>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/mailpoet/" target="_blank">' . esc_html__('MailPoet', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Kadence Forms
			if (cft_is_plugin_active('kadence-blocks/kadence-blocks.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Kadence Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Kadence Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_kadence" <?php if (get_option('jct_kadence')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with Kadence Forms.', 'just-cloudflare-turnstile'); ?>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/kadence-blocks/" target="_blank">' . esc_html__('Kadence Forms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // BuddyPress
			if (cft_is_plugin_active('buddypress/bp-loader.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('BuddyPress', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('BuddyPress Register', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_bp_register" <?php if (get_option('jct_bp_register')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/buddypress/" target="_blank">' . esc_html__('BuddyPress', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // bbPress
			if (cft_is_plugin_active('bbpress/bbpress.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('bbPress', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('bbPress Create Topic', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_bbpress_create" <?php if (get_option('jct_bbpress_create')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('bbPress Reply', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_bbpress_reply" <?php if (get_option('jct_bbpress_reply')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Alignment', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_bbpress_align">
									<option value="left" <?php if (!get_option('jct_bbpress_align') || get_option('jct_bbpress_align') == "left") { ?>selected<?php } ?>>
										<?php esc_html_e('Left', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="right" <?php if (get_option('jct_bbpress_align') == "right") { ?>selected<?php } ?>>
										<?php esc_html_e('Right', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Guest Users Only', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_bbpress_guest_only" <?php if (get_option('jct_bbpress_guest_only')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/bbpress/" target="_blank">' . esc_html__('bbPress', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Ultimate Member
			if (cft_is_plugin_active('ultimate-member/ultimate-member.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Ultimate Member', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('UM Login Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_um_login" <?php if (get_option('jct_um_login')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('UM Register Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_um_register" <?php if (get_option('jct_um_register')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('UM Password Reset Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_um_password" <?php if (get_option('jct_um_password')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/ultimate-member/" target="_blank">' . esc_html__('Ultimate Member', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // MemberPress
			if (cft_is_plugin_active('memberpress/memberpress.php')) { 

				if(get_option('jct_mepr_product_ids')) {
				  $LimitedToProductIDs = get_option('jct_mepr_product_ids');
				  $ProductsNeedingCaptcha = explode("\n", str_replace("\r", "", $LimitedToProductIDs));
				}
				?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('MemberPress', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<script>
						jQuery(document).ready(function(){
							jQuery("input[name='jct_login']").change(function(){
								if(jQuery("input[name='jct_login']").is(':checked')){
									jQuery('#jct_mepr_login').prop('checked', true);
								} else {
									jQuery('#jct_mepr_login').prop('checked', false);
								}
							});
						});
						</script>
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Login Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type='checkbox' name='jct_mepr_login' id='jct_mepr_login' <?php if (get_option('jct_login')) { ?>checked<?php } ?>
							title='<?php echo esc_html__('Edit via "WordPress Login" option in the "Default WordPress Forms" settings.', 'just-cloudflare-turnstile'); ?>' disabled></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Registration/Checkout Forms', 'just-cloudflare-turnstile'); 
								if(get_option('jct_mepr_product_ids')) {
									?>
								<br><span style="font-weight:400;font-size:12px;"><span style="color:#d1242f;"><?php echo esc_html__('Limited to:', 'just-cloudflare-turnstile'); ?></span> <?php echo implode(', ' , $ProductsNeedingCaptcha); ?></span>
								<?php
								}
								?>
							</th>
							<td><input type='checkbox' name='jct_mepr_register' id='jct_mepr_register' <?php if (get_option('jct_mepr_register')) { ?>checked<?php } ?>></td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('ONLY enable for these Membership IDs:', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<textarea style="width: 240px;" name="jct_mepr_product_ids"><?php echo sanitize_textarea_field(get_option('jct_mepr_product_ids')); ?></textarea>
								<br /><i style="font-size: 10px;"><?php echo esc_html__('(Optional) One per line. For Membership products that are not on this list, no Turnstile challenge will be loaded or enforced.', 'just-cloudflare-turnstile'); ?></i>
							</td>
						</tr>

					</table>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://memberpress.com/" target="_blank">' . esc_html__('MemberPress', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // WP-Members
			if (cft_is_plugin_active('wp-members/wp-members.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('WP-Members', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<p>
							<?php echo esc_html__('Turnstile is supported for WP-Members Login and Registration forms. Enable for these forms in the "Default WordPress Forms" settings.', 'just-cloudflare-turnstile'); ?>
						</p><br/>

					</table>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/wp-members/" target="_blank">' . esc_html__('WP-Members', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // WP User Frontend
			if (cft_is_plugin_active('wp-user-frontend/wpuf.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('WP User Frontend', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<script>
						jQuery(document).ready(function(){
							jQuery("input[name='jct_login']").change(function(){
								if(jQuery("input[name='jct_login']").is(':checked')){
									jQuery('#jct_wpuf_login').prop('checked', true);
								} else {
									jQuery('#jct_wpuf_login').prop('checked', false);
								}
							});
						});
						</script>
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Login Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type='checkbox' name='jct_wpuf_login' id='jct_wpuf_login' <?php if (get_option('jct_login')) { ?>checked<?php } ?>
							title='<?php echo esc_html__('Edit via "WordPress Login" option in the "Default WordPress Forms" settings.', 'just-cloudflare-turnstile'); ?>' disabled></td>
						</tr>

						<script>
						jQuery(document).ready(function(){
							jQuery("input[name='jct_reset']").change(function(){
								if(jQuery("input[name='jct_reset']").is(':checked')){
									jQuery('#jct_wpuf_reset').prop('checked', true);
								} else {
									jQuery('#jct_wpuf_reset').prop('checked', false);
								}
							});
						});
						</script>
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Reset Password Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type='checkbox' name='jct_wpuf_reset' id='jct_wpuf_reset' <?php if (get_option('jct_reset')) { ?>checked<?php } ?>
							title='<?php echo esc_html__('Edit via "WordPress Reset Password" option in the "Default WordPress Forms" settings.', 'just-cloudflare-turnstile'); ?>' disabled></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Register Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_wpuf_register" <?php if (get_option('jct_wpuf_register')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Post Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_wpuf_forms" <?php if (get_option('jct_wpuf_forms')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/wp-user-frontend/" target="_blank">' . esc_html__('WP User Frontend', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // wpDiscuz
			if (!cft_is_plugin_active('wpdiscuz/class.WpdiscuzCore.php')) {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/wpdiscuz/" target="_blank">' . esc_html__('wpDiscuz', 'just-cloudflare-turnstile') . '</a>');
			} ?>

			<?php
			// Output Custom Settings
			do_action('jct-settings-section');
			$not_installed = apply_filters('jct-settings-not-installed', $not_installed);
			?>

			<?php // List of plugins not installed
			if (!empty($not_installed)) { ?>
				<br />

				<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

					<tr valign="top">
						<th scope="row">
							<span style="font-size: 19px;"><?php echo esc_html__('Other Integrations', 'just-cloudflare-turnstile'); ?></span>
							<p>
								
								<?php echo esc_html__('You can also enable Turnstile on', 'just-cloudflare-turnstile') . " ";
								$last_plugin = end($not_installed);
								foreach ($not_installed as $not_plugin) {
									if ($not_plugin == $last_plugin && count($not_installed) > 1) echo 'and ';
									echo $not_plugin;
									if ($not_plugin != $last_plugin) {
										echo ', ';
									} else {
										echo '.';
									}
								}
								?>

								<?php echo esc_html__('Simply install the plugin and new settings will appear above.', 'just-cloudflare-turnstile'); ?>

							</p>
						</th>
					</tr>

				</table>

			<?php } ?>

			</div>

			<?php submit_button(); ?>

			<div style="font-size: 10px; margin-top: 15px;">
				<!-- Delete Options on Uninstall (Always keep this option last) -->
				<input type="checkbox" name="jct_uninstall_remove" <?php if (get_option('jct_uninstall_remove')) { ?>checked<?php } ?> style="transform: scale(0.7); margin: -2px 0 0 0;">
				<?php echo esc_html__('Delete all of this plugins saved options when the plugin is deleted via plugins page.', 'just-cloudflare-turnstile'); ?>
			</div>

			<div style="font-size: 10px; margin-top: 15px;">
				<!-- Enable Logging -->
				<input type="checkbox" name="jct_log_enable" <?php if (get_option('jct_log_enable')) { ?>checked<?php } ?> style="transform: scale(0.7); margin: -2px 0 0 0;">
				<?php echo esc_html__('Enable debug logging of Turnstile form submission events.', 'just-cloudflare-turnstile'); ?>
			</div>
			
		</form>

		<?php if(get_option('jct_log_enable')) { ?>
		<br/><button type="button" class="jct-accordion" id="jct-accordion-whitelist"><?php echo esc_html__('Turnstile Debug Log', 'just-cloudflare-turnstile'); ?></button>
			<div class="jct-panel">

				<?php
				$jct_log = get_option('jct_log');
				/* 	$jct_log[] = array(
					'date' => date('Y-m-d H:i:s'),
					'success' => $success,
					'error' => $errors,
					'ip' => $_SERVER['REMOTE_ADDR'],
					'page' => $_SERVER['REQUEST_URI'],
				);
				*/
				if ($jct_log) {
				echo '<div style="max-height: 200px; overflow: auto; border: 1px solid #ddd; padding: 0px;">';
					echo '<table>';
						echo '<tr valign="top">';
						echo '<td>';
						echo '<table class="widefat">';
						echo '<thead>';
						echo '<tr>';
						echo '<th>' . esc_html__('Date', 'just-cloudflare-turnstile') . '</th>';
						echo '<th>' . esc_html__('Success', 'just-cloudflare-turnstile') . '</th>';
						echo '<th>' . esc_html__('Response', 'just-cloudflare-turnstile') . '</th>';
						echo '<th>' . esc_html__('Info', 'just-cloudflare-turnstile') . '</th>';
						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
						$jct_log = array_reverse($jct_log);
						foreach ($jct_log as $log) {
							echo '<tr>';
							$log['date'] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['date']));
							echo '<td>' . esc_html($log['date']) . '</td>';
							echo '<td>' . ($log['success'] ? '<span style="color: green;">Yes</span>' : '<span style="color: red;">No</span>') . '</td>';
							echo '<td>';
							if(!$log['success']) {
								$error_val = $log['error'];
								echo esc_html($error_val);
							} else {
								echo '<span>' . esc_html__('Success', 'just-cloudflare-turnstile') . '</span>';
							}
							echo '</td>';
							echo '<td>';
							echo '<strong>' . esc_html__('IP:', 'just-cloudflare-turnstile') . '</strong> ' . esc_html($log['ip']) . '<br />';
							echo '<strong>' . esc_html__('URL:', 'just-cloudflare-turnstile') . '</strong> ' . esc_html($log['page']);
							echo '</td>';
						}
						echo '</tr>';
						echo '</tbody>';
						echo '</table>';
						echo '</td>';
						echo '</tr>';
					echo '</table>';
				echo '</div>';
				// Error code meanings
				echo '<div style="margin-top: 20px; font-size: 9px;">';
				echo '<strong><u>' . esc_html__('Error Codes', 'just-cloudflare-turnstile') . '</strong></u><br />';
				echo '- <strong>missing-input-response:</strong> ' . jct_error_message('missing-input-response') . esc_html__(' (Visitor submitted form when Turnstile was not successfully completed.)', 'just-cloudflare-turnstile') . '<br />';
				echo '- <strong>missing-input-secret:</strong> ' . jct_error_message('missing-input-secret') . '<br />';
				echo '- <strong>invalid-input-secret:</strong> ' . jct_error_message('invalid-input-secret') . '<br />';
				echo '- <strong>invalid-input-response:</strong> ' . jct_error_message('invalid-input-response') . '<br />';
				echo '- <strong>bad-request:</strong> ' . jct_error_message('bad-request') . '<br />';
				echo '- <strong>timeout-or-duplicate:</strong> ' . jct_error_message('timeout-or-duplicate') . '<br />';
				echo '- <strong>internal-error:</strong> ' . jct_error_message('internal-error') . '<br />';
				echo '</div>';
				} else {
					echo '<p>' . esc_html__('No events logged yet.', 'just-cloudflare-turnstile') . '</p>';
				}
				?>
			</div>
		<?php } else {
			if(get_option('jct_log')) {
				delete_option('jct_log');
			}
		}
		?>

		<div class="jct-admin-promo">

			<p style="font-size: 15px; font-weight: bold;"><?php echo esc_html__('100% free plugin developed by', 'just-cloudflare-turnstile'); ?> <a href="https://twitter.com/ElliotSowersby" target="_blank" title="@ElliotSowersby on Twitter"><span class="dashicons dashicons-twitter" style="margin-top: 5px; font-size: 15px; text-decoration: none;"></span></a> <a href="https://Just There.com/?utm_campaign=simple-turnstile-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank" title="Just There - WordPress Maintenance & Support"><span class="dashicons dashicons-admin-links" style="margin-top: 5px; font-size: 15px; text-decoration: none;"></span>Just There</a></p>

			<p style="font-size: 15px;">
				- <?php echo esc_html__('Like this plugin?', 'just-cloudflare-turnstile'); ?> <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/reviews/#new-post" target="_blank" title="<?php echo esc_html__('Review on WordPress.org', 'just-cloudflare-turnstile'); ?>"><?php echo esc_html__('Please submit a review', 'just-cloudflare-turnstile'); ?></a> <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/reviews/#new-post" target="_blank" title="<?php echo esc_html__('Review on WordPress.org', 'just-cloudflare-turnstile'); ?>" style="text-decoration: none;">
					<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>
				</a></p>

			<p style="font-size: 15px;">- <?php echo esc_html__('Need help? Have a suggestion?', 'just-cloudflare-turnstile'); ?> <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/#new-topic-0" target="_blank"><?php echo esc_html__('Create a support topic', 'just-cloudflare-turnstile'); ?><span class="dashicons dashicons-external" style="font-size: 15px; margin-top: 5px; text-decoration: none;"></span></a></p>

			<p style="font-size: 15px;">
				- <?php echo esc_html__('Want to support the plugin?', 'just-cloudflare-turnstile'); ?> <?php echo esc_html__('Feel free to', 'just-cloudflare-turnstile'); ?> <a href="https://www.paypal.com/donate/?hosted_button_id=RX28BBH7L5XDS" target="_blank"><?php echo esc_html__('Donate', 'just-cloudflare-turnstile'); ?><span class="dashicons dashicons-external" style="font-size: 15px; margin-top: 5px; text-decoration: none;"></span></a>
			</p>

			<p style="font-size: 12px;">
				<a href="https://translate.wordpress.org/projects/wp-plugins/just-cloudflare-turnstile/" target="_blank"><?php echo esc_html__('Translate into your language', 'just-cloudflare-turnstile'); ?><span class="dashicons dashicons-external" style="font-size: 15px; margin-top: 2px; text-decoration: none;"></span></a>
				<br />
				<a href="https://github.com/ElliotSowersby/just-cloudflare-turnstile" target="_blank"><?php echo esc_html__('View on GitHub', 'just-cloudflare-turnstile'); ?><span class="dashicons dashicons-external" style="font-size: 15px; margin-top: 2px; text-decoration: none;"></span></a>
			</p>

		</div>

		<div class="jct-admin-promo" style="margin-top: 15px;">

			<p style="font-size: 15px;">
				<a href="https://Just There.com/plugins/?utm_campaign=simple-turnstile-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank">
					<?php echo esc_html__( 'View more plugins by Just There', 'just-cloudflare-turnstile' ); ?><span class="dashicons dashicons-external"
					style="font-size: 15px; margin-top: 5px; text-decoration: none;"></span>
				</a>
			</p>

		</div>

<?php } ?> // Callback function
    );
}

// Keys Updated
add_action('update_option_jct_key', 'jct_keys_updated', 10);
add_action('update_option_jct_secret', 'jct_keys_updated', 10);
function jfunction jct_create_menu() {
    add_submenu_page(
        'options-general.php', // Parent slug
        'Cloudflare Turnstile', // Page title
        'Cloudflare Turnstile', // Menu title
        'manage_options', // Capability
        'jct', // Menu slug
        'jct_settings_page' // Callback function
    );
}

// Keys Updated
add_action('update_option_jct_key', 'jct_keys_updated', 10);
add_action('update_option_jct_secret', 'jct_keys_updated', 10);
function jct_keys_updated() {
	update_option('jct_tested', 'no');
}

// Admin test form to check Turnstile response
function jct_admin_test() {
?>
	<form action="" method="POST" class="jct-settings">
		<?php
		if (!empty(get_option('jct_key')) && !empty(get_option('jct_secret'))) {
			$check = jct_check();
			$success = '';
			$error = '';
			if (isset($check['success'])) $success = $check['success'];
			if (isset($check['error_code'])) $error = $check['error_code'];
			if ($success != true) {
				echo '<div style="padding: 20px 20px 25px 20px; margin: 20px 0 28px 0; background: #fff; border-radius: 20px; max-width: 500px; border: 2px solid #d5d5d5;">';
				echo '<p style="font-weight: 600; font-size: 19px; margin-top: 0; margin-bottom: 0;">' . esc_html__('Almost done...', 'just-cloudflare-turnstile') . '</p>';
			}
			if (!isset($_POST['jct-turnstile-response'])) {
				echo '<p>'
					. '<span style="color: red; font-weight: bold;">' . esc_html__('API keys have been updated. Please test the Turnstile API response below.', 'just-cloudflare-turnstile') . '</span>'
					. '<br/>'
					. esc_html__('Turnstile will not be added to any forms until the test is successfully complete.', 'just-cloudflare-turnstile')
					. '</p>';
			} else {
				if ($success == true) {
					update_option('jct_tested', 'yes');
				} else {
					if ($error == "missing-input-response") {
						echo '<p style="font-weight: bold; color: red;">' . jct_failed_message() . '</p>';
					} else {
						echo '<p style="font-weight: bold; color: red;">' . esc_html__('Failed! There is an error with your API settings. Please check & update them.', 'just-cloudflare-turnstile') . '</p>';
					}
				}
				if ($error) {
					echo '<p style="font-weight: bold;">' . esc_html__('Error message:', 'just-cloudflare-turnstile') . " " . jct_error_message($error) . '</p>';
				}
			}
			if ($success != true) {
				echo '<div style="margin-left: 0px;">';
				echo jct_field_show('', '', 'admin-test', 'admin-test');
				echo '</div><div style="margin-bottom: -20px;"></div>';
				echo '<button type="submit" style="margin-top: 10px; padding: 7px 10px; background: #1c781c; color: #fff; font-size: 15px; font-weight: bold; border: 1px solid #176017; border-radius: 4px; cursor: pointer;">
				' . esc_html__('TEST RESPONSE', 'just-cloudflare-turnstile') . ' <span class="dashicons dashicons-arrow-right-alt"></span>
				</button>';
				echo '</div>';
			}
		}
		?>
	</form>
<?php
}

// Show Settings Page
function jct_settings_page() {
?>
	<div class="jct-wrap wrap">

		<h1 style="font-weight: bold;"><?php echo esc_html__('Just Cloudflare Turnstile', 'just-cloudflare-turnstile'); ?></h1>

		<p style="margin-bottom: 0;"><?php echo esc_html__('Easily add the free CAPTCHA service called "Cloudflare Turnstile" to your WordPress forms to help prevent spam.', 'just-cloudflare-turnstile'); ?> <a href="https://www.cloudflare.com/en-gb/products/turnstile/" target="_blank"><?php echo esc_html__('Learn more.', 'just-cloudflare-turnstile'); ?></a>

		<div class="jct-admin-promo-top">

			<p>
				<a href="https://Just There.com/blog/how-to-add-cloudflare-turnstile-to-wordpress/?utm_campaign=simple-turnstile-plugin&utm_source=plugin-settings&utm_medium=guide" title="View our Turnstile plugin setup guide." target="_blank"><?php echo esc_html__('View setup guide', 'just-cloudflare-turnstile'); ?><span class="dashicons dashicons-external" style="margin-left: 2px; text-decoration: none;"></span></a> &nbsp;&#x2022;&nbsp; <?php echo esc_html__('Like this plugin?', 'just-cloudflare-turnstile'); ?> <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/reviews/#new-post" target="_blank" title="<?php echo esc_html__('Review on WordPress.org', 'just-cloudflare-turnstile'); ?>"><?php echo esc_html__('Please submit a review', 'just-cloudflare-turnstile'); ?></a> <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/reviews/#new-post" target="_blank" title="<?php echo esc_html__('Review on WordPress.org', 'just-cloudflare-turnstile'); ?>" style="text-decoration: none;">
					<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>
				</a>
			</p>

		</div>

		<?php
		if (empty(get_option('jct_tested')) || get_option('jct_tested') != 'yes') {
			echo jct_admin_test();
		}
		?>

		<form method="post" action="options.php" class="jct-settings">

			<?php settings_fields('jct-settings-group'); ?>
			<?php do_settings_sections('jct-settings-group'); ?>

			<hr style="margin: 20px 0 0 0;">

			<table class="form-table">

				<tr valign="top">
					<th scope="row" style="padding-bottom: 0;">

						<p style="font-size: 19px; margin-top: 0;"><?php echo esc_html__('API Key Settings:', 'just-cloudflare-turnstile'); ?></p>

						<?php
						if (get_option('jct_tested') == 'yes') {
							echo '<p style=" font-size: 15px; font-weight: bold; color: #1e8c1e;"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Success! Turnstile is working correctly with your API keys.', 'just-cloudflare-turnstile') . '</p>';
						} ?>

						<p style="margin-bottom: 2px;"><?php echo esc_html__('You can get your site key and secret key from here:', 'just-cloudflare-turnstile'); ?> <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank">https://dash.cloudflare.com/?to=/:account/turnstile</a></p>

					</th>
				</tr>

			</table>

			<table class="form-table">

				<tr valign="top">
					<th scope="row"><?php echo esc_html__('Site Key', 'just-cloudflare-turnstile'); ?></th>
					<td><input type="text" style="width: 240px;" name="jct_key" value="<?php echo esc_html(get_option('jct_key')); ?>" /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php echo esc_html__('Secret Key', 'just-cloudflare-turnstile'); ?></th>
					<td><input type="text" style="width: 240px;" name="jct_secret" value="<?php echo esc_html(get_option('jct_secret')); ?>" /></td>
				</tr>

			</table>

			<hr style="margin: 20px 0 10px 0;">

			<table class="form-table">

				<tr valign="top">
					<th scope="row" style="font-size: 19px; padding-bottom: 5px;"><?php echo esc_html__('General Settings:', 'just-cloudflare-turnstile'); ?></th>
				</tr>

				<tr valign="top">
					<th scope="row"><?php echo esc_html__('Theme', 'just-cloudflare-turnstile'); ?></th>
					<td>
						<select name="jct_theme">
							<option value="light" <?php if (!get_option('jct_theme') || get_option('jct_theme') == "light") { ?>selected<?php } ?>>
								<?php esc_html_e('Light', 'just-cloudflare-turnstile'); ?>
							</option>
							<option value="dark" <?php if (get_option('jct_theme') == "dark") { ?>selected<?php } ?>>
								<?php esc_html_e('Dark', 'just-cloudflare-turnstile'); ?>
							</option>
							<option value="auto" <?php if (get_option('jct_theme') == "auto") { ?>selected<?php } ?>>
								<?php esc_html_e('Auto', 'just-cloudflare-turnstile'); ?>
							</option>
						</select>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php echo esc_html__('Language', 'just-cloudflare-turnstile'); ?></th>
					<td>
						<select name="jct_language">
						<?php
						$languages = array(
							'auto'   => esc_html__( 'Auto Detect', 'just-cloudflare-turnstile' ),
							'ar-eg'  => esc_html__( 'Arabic (Egypt)', 'just-cloudflare-turnstile' ),
							'bg-bg'  => esc_html__( 'Bulgarian (Bulgaria)', 'just-cloudflare-turnstile' ),
							'zh-cn'  => esc_html__( 'Chinese (Simplified, China)', 'just-cloudflare-turnstile' ),
							'zh-tw'  => esc_html__( 'Chinese (Traditional, Taiwan)', 'just-cloudflare-turnstile' ),
							'hr-hr'  => esc_html__( 'Croatian (Croatia)', 'just-cloudflare-turnstile' ),
							'cs-cz'  => esc_html__( 'Czech (Czech Republic)', 'just-cloudflare-turnstile' ),
							'da-dk'  => esc_html__( 'Danish (Denmark)', 'just-cloudflare-turnstile' ),
							'nl-nl'  => esc_html__( 'Dutch (Netherlands)', 'just-cloudflare-turnstile' ),
							'en-us'  => esc_html__( 'English (United States)', 'just-cloudflare-turnstile' ),
							'fa-ir'  => esc_html__( 'Farsi (Iran)', 'just-cloudflare-turnstile' ),
							'fi-fi'  => esc_html__( 'Finnish (Finland)', 'just-cloudflare-turnstile' ),
							'fr-fr'  => esc_html__( 'French (France)', 'just-cloudflare-turnstile' ),
							'de-de'  => esc_html__( 'German (Germany)', 'just-cloudflare-turnstile' ),
							'el-gr'  => esc_html__( 'Greek (Greece)', 'just-cloudflare-turnstile' ),
							'he-il'  => esc_html__( 'Hebrew (Israel)', 'just-cloudflare-turnstile' ),
							'hi-in'  => esc_html__( 'Hindi (India)', 'just-cloudflare-turnstile' ),
							'hu-hu'  => esc_html__( 'Hungarian (Hungary)', 'just-cloudflare-turnstile' ),
							'id-id'  => esc_html__( 'Indonesian (Indonesia)', 'just-cloudflare-turnstile' ),
							'it-it'  => esc_html__( 'Italian (Italy)', 'just-cloudflare-turnstile' ),
							'ja-jp'  => esc_html__( 'Japanese (Japan)', 'just-cloudflare-turnstile' ),
							'tlh'    => esc_html__( 'Klingon (Qo’noS)', 'just-cloudflare-turnstile' ),
							'ko-kr'  => esc_html__( 'Korean (Korea)', 'just-cloudflare-turnstile' ),
							'lt-lt'  => esc_html__( 'Lithuanian (Lithuania)', 'just-cloudflare-turnstile' ),
							'ms-my'  => esc_html__( 'Malay (Malaysia)', 'just-cloudflare-turnstile' ),
							'nb-no'  => esc_html__( 'Norwegian Bokmål (Norway)', 'just-cloudflare-turnstile' ),
							'pl-pl'  => esc_html__( 'Polish (Poland)', 'just-cloudflare-turnstile' ),
							'pt-br'  => esc_html__( 'Portuguese (Brazil)', 'just-cloudflare-turnstile' ),
							'ro-ro'  => esc_html__( 'Romanian (Romania)', 'just-cloudflare-turnstile' ),
							'ru-ru'  => esc_html__( 'Russian (Russia)', 'just-cloudflare-turnstile' ),
							'sr-ba'  => esc_html__( 'Serbian (Bosnia and Herzegovina)', 'just-cloudflare-turnstile' ),
							'sk-sk'  => esc_html__( 'Slovak (Slovakia)', 'just-cloudflare-turnstile' ),
							'sl-si'  => esc_html__( 'Slovenian (Slovenia)', 'just-cloudflare-turnstile' ),
							'es-es'  => esc_html__( 'Spanish (Spain)', 'just-cloudflare-turnstile' ),
							'sv-se'  => esc_html__( 'Swedish (Sweden)', 'just-cloudflare-turnstile' ),
							'tl-ph'  => esc_html__( 'Tagalog (Philippines)', 'just-cloudflare-turnstile' ),
							'th-th'  => esc_html__( 'Thai (Thailand)', 'just-cloudflare-turnstile' ),
							'tr-tr'  => esc_html__( 'Turkish (Turkey)', 'just-cloudflare-turnstile' ),
							'uk-ua'  => esc_html__( 'Ukrainian (Ukraine)', 'just-cloudflare-turnstile' ),
							'vi-vn'  => esc_html__( 'Vietnamese (Vietnam)', 'just-cloudflare-turnstile' ),
						);
						$auto = $languages['auto'];
						unset($languages['auto']);
						asort($languages);
						$languages = array_merge(array('auto' => $auto), $languages);
						foreach ($languages as $code => $name) {
							$selected = '';
							if(get_option('jct_language') == $code) { $selected = 'selected'; }
							?>
								<option value="<?php echo esc_attr($code); ?>" <?php echo esc_attr($selected); ?>>
									<?php echo esc_html($name); ?>
								</option>
							<?php
						}
						?>
						</select>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<?php echo esc_html__('Disable Submit Button', 'just-cloudflare-turnstile'); ?>
					</th>
					<td><input type="checkbox" name="jct_disable_button" <?php if (get_option('jct_disable_button')) { ?>checked<?php } ?>>
						<i style="font-size: 10px;"><?php echo esc_html__('When enabled, the user will not be able to click submit until the Turnstile challenge is completed.', 'just-cloudflare-turnstile'); ?></i>
					</td>
				</tr>

			</table>

			<button type="button" class="jct-accordion" id="jct-accordion-whitelist"><?php echo esc_html__('Advanced Settings', 'just-cloudflare-turnstile'); ?></button>
			<div class="jct-panel">

				<p style="margin: 0 0 20px 0; padding-bottom: 20px; border-bottom: 1px solid #f3f3f3;">
					<?php echo esc_html__('These settings are for more advanced customisation. If you are not sure about these, they do not need to be changed.', 'just-cloudflare-turnstile'); ?>
				</p>

				<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

					<tr valign="top">
						<th scope="row"><?php echo esc_html__('Widget Size', 'just-cloudflare-turnstile'); ?></th>
						<td>
							<select name="jct_size" style="width: 100%;">
								<option value="normal" <?php if (!get_option('jct_size') || get_option('jct_size') == "normal") { ?>selected<?php } ?>>
									<?php esc_html_e('Normal (300px)', 'just-cloudflare-turnstile'); ?>
								</option>
								<option value="flexible" <?php if (get_option('jct_size') == "flexible") { ?>selected<?php } ?>>
									<?php esc_html_e('Flexible (100%)', 'just-cloudflare-turnstile'); ?>
								</option>
								<option value="compact" <?php if (get_option('jct_size') == "compact") { ?>selected<?php } ?>>
									<?php esc_html_e('Compact (150px)', 'just-cloudflare-turnstile'); ?>
								</option>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo esc_html__('Appearance Mode', 'just-cloudflare-turnstile'); ?></th>
						<td>
							<select name="jct_appearance" style="width: 100%;">
							<?php
							$appearances = array(
								'always' => esc_html__( 'Always', 'just-cloudflare-turnstile' ),
								// 'execute' => esc_html__( 'Execute', 'just-cloudflare-turnstile' ), // Not really needed
								'interaction-only' => esc_html__( 'Interaction Only', 'just-cloudflare-turnstile' ),
							);
							foreach ($appearances as $code => $name) {
								$selected = '';
								if(get_option('jct_appearance') == $code) { $selected = 'selected'; }
								?>
									<option value="<?php echo esc_attr($code); ?>" <?php echo esc_attr($selected); ?>>
										<?php echo esc_html($name); ?>
									</option>
								<?php
							}
							?>
							</select>
							<br/><br/>
							<div class="wcu-appearance-always" style="display: none;"><i style="font-size: 10px;"><?php echo esc_html__( 'Turnstile Widget is always displayed for all visitors.', 'just-cloudflare-turnstile' ); ?></i></div>
							<div class="wcu-appearance-execute" style="display: none;"><i style="font-size: 10px;"><?php echo esc_html__( 'Turnstile Widget is only displayed after the challenge begins.', 'just-cloudflare-turnstile' ); ?></i></div>
							<div class="wcu-appearance-interaction-only" style="display: none;"><i style="font-size: 10px;"><?php echo esc_html__( 'Turnstile Widget is only displayed in cases where an interaction is required. This essentially makes it "invisible" for most valid users.', 'just-cloudflare-turnstile' ); ?></i></div>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__('Defer Scripts', 'just-cloudflare-turnstile'); ?>
						</th>
						<td><input style="margin: 5px 0 20px 10px;" type="checkbox" name="jct_defer_scripts" <?php if (get_option('jct_defer_scripts', 1)) { ?>checked<?php } ?>>
						<i style="font-size: 10px;"><?php echo esc_html__('When enabled, the javascript files loaded by the plugin will be deferred. You can disable this if it causes any issues with your other optimisations.', 'just-cloudflare-turnstile'); ?></i>
					</td>

					<tr valign="top">
						<th scope="row"><?php echo esc_html__('Custom Error Message', 'just-cloudflare-turnstile'); ?></th>
						<td>
							<textarea type="text" style="width: 202px; margin-bottom: 5px;" name="jct_error_message"
							placeholder="<?php echo jct_failed_message(1); ?>"
							/><?php if(get_option('jct_error_message')) { echo esc_html(get_option('jct_error_message')); } ?></textarea>
							<br /><i style="font-size: 10px;"><?php echo esc_html__('Shown if the form is submitted without completing the Turnstile challenge. Leave blank to use the default message (localized):', 'just-cloudflare-turnstile') . ' "' . jct_failed_message(1) . '"'; ?></i>
						</td>
					</tr>

					<tr valign="top" style="border: 0;">
						<th scope="row">
							<?php echo esc_html__('Extra Failure Message', 'just-cloudflare-turnstile'); ?>
						</th>
						<td>
							<input type="checkbox" name="jct_failure_message_enable" <?php if (get_option('jct_failure_message_enable', 0)) { ?>checked<?php } ?>>
						</td>
					</tr>
					<tr valign="top" class="jct-failure-message" style="border: 0;">
						<th scope="row" style="padding-top: 0px;">
						<i style="font-size: 10px;">
							<?php echo esc_html__('HTML Markup Allowed.', 'just-cloudflare-turnstile'); ?>
						</i>
					</th>
						<td style="padding-top: 0px;">
							<textarea type="text" style="width: 202px; margin-bottom: 5px;" name="jct_failure_message" rows="3"
							placeholder="<?php echo esc_html__('Failed to verify you are human. Please contact us if you are having issues.', 'just-cloudflare-turnstile'); ?>"
							/><?php if(get_option('jct_failure_message')) { echo esc_html(get_option('jct_failure_message')); } ?></textarea>
							<i style="font-size: 10px;"><?php echo esc_html__('This will show a message below the Turnstile widget if they receive the "Failure!" response. Useful to give instructions in the *very rare* case a valid user is being flagged as spam.', 'just-cloudflare-turnstile'); ?></i>
							<br/><br/>
							<i style="font-size: 10px;"><?php echo esc_html__('Currently it is not possible to edit the actual "Failure!" message shown on the widget.', 'just-cloudflare-turnstile'); ?></i>
						</td>
					</tr>
					<script>
					jQuery(document).ready(function() {
						jQuery('.jct-failure-message').hide();
						jQuery('input[name="jct_failure_message_enable"]').change(function() {
							if(jQuery(this).is(":checked")) {
								jQuery('.jct-failure-message').show();
							} else {
								jQuery('.jct-failure-message').hide();
							}
						});
						jQuery('input[name="jct_failure_message_enable"]').trigger('change');						
					});
					</script>

				</tr>

				</table>

			</div>

			<button type="button" class="jct-accordion" id="jct-accordion-whitelist"><?php echo esc_html__('Whitelist Settings', 'just-cloudflare-turnstile'); ?></button>
			<div class="jct-panel">

				<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__('Logged In Users', 'just-cloudflare-turnstile'); ?>
						</th>
						<td><input style="margin-top: 5px;" type="checkbox" name="jct_whitelist_users" <?php if (get_option('jct_whitelist_users')) { ?>checked<?php } ?>>
							<i style="font-size: 10px;"><?php echo esc_html__('When enabled, logged in users will not see the Turnstile challenge.', 'just-cloudflare-turnstile'); ?></i>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php echo esc_html__('IP Addresses', 'just-cloudflare-turnstile'); ?></th>
						<td>
							<textarea style="width: 240px;" name="jct_whitelist_ips"><?php echo sanitize_textarea_field(get_option('jct_whitelist_ips')); ?></textarea>
							<br /><i style="font-size: 10px;"><?php echo esc_html__('One per line. Wildcards are not supported. All visitors with listed IP addresses will not see the Turnstile challenge. Warning: If an attacker knows one of the whitelisted IP addresses, they might be able to spoof that address to bypass Turnstile.', 'just-cloudflare-turnstile'); ?></i>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo esc_html__('User Agents', 'just-cloudflare-turnstile'); ?></th>
						<td>
							<textarea style="width: 240px;" name="jct_whitelist_agents"><?php echo sanitize_textarea_field(get_option('jct_whitelist_agents')); ?></textarea>
							<br /><i style="font-size: 10px;"><?php echo esc_html__('One per line.  All visitors with listed User Agents will not see the Turnstile challenge. Warning: If an attacker knows one of the whitelisted User Agents, they might be able to spoof that User Agent to bypass Turnstile.', 'just-cloudflare-turnstile'); ?></i>
						</td>
					</tr>

				</table>

			</div>

			<hr style="margin: 40px 0 10px 0;">

			<div class="jct-integrations">

			<table class="form-table" style="margin-bottom: -35px;">

				<tr valign="top">
					<th scope="row">
						<span style="font-size: 19px;"><?php echo esc_html__('Enable Turnstile on your forms:', 'just-cloudflare-turnstile'); ?></span>
						<p><?php echo esc_html__('Select the dropdown for each integration, and choose when specific forms you want to enable Turnstile on.', 'just-cloudflare-turnstile'); ?></p>
					</th>
				</tr>

			</table>

			<button type="button" class="jct-accordion" id="jct-accordion-wordpress"><?php echo esc_html__('Default WordPress Forms', 'just-cloudflare-turnstile'); ?></button>
			<div class="jct-panel">

				<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__('WordPress Login', 'just-cloudflare-turnstile'); ?> <a href="#" class="jct_toggle_login" style="font-size: 10px; text-decoration: none; color: #333;">&#9660;</a>
							<span id="jct_login_only_option" style="display: none;" title="<?php echo esc_html__('Enable this option to only enable on default WordPress login form at wp-login.php', 'just-cloudflare-turnstile'); ?>">
							<br/><br/>
								<label style="float: left; margin: -5px 10px 0px 0; font-weight: 600; font-size: 10px;" for="jct_login_only"><?php echo esc_html__('Only enable on default wp-login.php page', 'just-cloudflare-turnstile'); ?></label>
								<input style="float: left; transform: scale(0.75); margin-top: -7px; margin-left: -5px;"
								type="checkbox" name="jct_login_only" <?php if (get_option('jct_login_only')) { ?>checked<?php } ?>>
							</span>
						</th>
						<td><input type="checkbox" name="jct_login" <?php if (get_option('jct_login')) { ?>checked<?php } ?>></td>
					</tr>
					<script>
					jQuery(document).ready(function() {
						jQuery('.jct_toggle_login').click(function(e) {
							e.preventDefault();
							jQuery('#jct_login_only_option').toggle();
						});
					});
					</script>

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__('WordPress Register', 'just-cloudflare-turnstile'); ?> <a href="#" class="jct_toggle_register" style="font-size: 10px; text-decoration: none; color: #333;">&#9660;</a>
							<span id="jct_register_only_option" style="display: none;" title="<?php echo esc_html__('Enable this option to only enable on default WordPress register form at wp-login.php?action=register', 'just-cloudflare-turnstile'); ?>">
							<br/><br/>
								<label style="float: left; margin: -5px 10px 0px 0; font-weight: 600; font-size: 10px;" for="jct_register_only"><?php echo esc_html__('Only enable on default wp-login.php page', 'just-cloudflare-turnstile'); ?></label>
								<input style="float: left; transform: scale(0.75); margin-top: -7px; margin-left: -5px;"
								type="checkbox" name="jct_register_only" <?php if (get_option('jct_register_only')) { ?>checked<?php } ?>>
							</span>
						</th>
						<td><input type="checkbox" name="jct_register" <?php if (get_option('jct_register')) { ?>checked<?php } ?>></td>
					</tr>
					<script>
					jQuery(document).ready(function() {
						jQuery('.jct_toggle_register').click(function(e) {
							e.preventDefault();
							jQuery('#jct_register_only_option').toggle();
						});
					});
					</script>

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__('WordPress Reset Password', 'just-cloudflare-turnstile'); ?>
						</th>
						<td><input type="checkbox" name="jct_reset" <?php if (get_option('jct_reset')) { ?>checked<?php } ?>></td>
					</tr>

					<tr valign="top" style="border: 0;">
						<th scope="row">
							<?php echo esc_html__('WordPress Comment', 'just-cloudflare-turnstile'); ?> <a href="#" class="jct_toggle_comments" style="font-size: 10px; text-decoration: none; color: #333;">&#9660;</a>
							<span id="jct_ajax_comments_option" style="display: none;" title="<?php echo esc_html__('Enable this if you are using an AJAX based comments form plugin or theme.', 'just-cloudflare-turnstile'); ?>">
							<br/><br/>
								<label style="float: left; margin: -5px 10px 0px 0; font-weight: 600; font-size: 10px;" for="jct_ajax_comments"><?php echo esc_html__('AJAX comments form?', 'just-cloudflare-turnstile'); ?></label>
								<input style="float: left; transform: scale(0.75); margin-top: -7px; margin-left: -5px;"
								type="checkbox" name="jct_ajax_comments"
								<?php if(!cft_is_plugin_active('wpdiscuz/class.WpdiscuzCore.php') && !cft_is_plugin_active('wp-ajaxify-comments/wp-ajaxify-comments.php')) { ?>
								<?php if (get_option('jct_ajax_comments')) { ?>checked<?php } ?>>
								<?php } else { ?>checked disabled<?php } ?>
							</span>
						</th>
						<td>
							<input type="checkbox" name="jct_comment" <?php if (get_option('jct_comment')) { ?>checked<?php } ?>>
							<?php if (cft_is_plugin_active('jetpack/jetpack.php')) { ?>
								<br /><i style="font-size: 10px;"><?php echo esc_html__('Due to Jetpack limitations, this does NOT currently work with Jetpack comments form enabled.', 'just-cloudflare-turnstile'); ?></i>
							<?php } ?>
							<?php if (cft_is_plugin_active('wpdiscuz/class.WpdiscuzCore.php')) { ?>
								<i style="font-size: 11px;"><?php echo esc_html__('Compatible with wpDiscuz!', 'just-cloudflare-turnstile'); ?></i>
							<?php } ?>
						</td>
					</tr>
					<script>
						jQuery(document).ready(function() {
							jQuery('.jct_toggle_comments').click(function(e) {
								e.preventDefault();
								jQuery('#jct_ajax_comments_option').toggle();
							});
						});
					</script>

				</table>

			</div>

			<?php $not_installed = array(); ?>

			<?php // WooCommerce
			if (cft_is_plugin_active('woocommerce/woocommerce.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('WooCommerce Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('WooCommerce Login', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_woo_login" <?php if (get_option('jct_woo_login')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('WooCommerce Register', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_woo_register" <?php if (get_option('jct_woo_register')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('WooCommerce Reset Password', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_woo_reset" <?php if (get_option('jct_woo_reset')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('WooCommerce Checkout', 'just-cloudflare-turnstile'); ?>
								<br /><br />
								- <?php echo esc_html__('Guest Checkout Only', 'just-cloudflare-turnstile'); ?>
								<br /><br />
								- <?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?>
								<br/><br/>
							</th>
							<td>
								<input style="margin-top: 5px;" type="checkbox" name="jct_woo_checkout" <?php if (get_option('jct_woo_checkout')) { ?>checked<?php } ?>>
								<br /><br />
								<input style="margin-top: 5px;" type="checkbox" name="jct_guest_only" <?php if (get_option('jct_guest_only')) { ?>checked<?php } ?>>
								<br /><br />
								<select name="jct_woo_checkout_pos">
									<option value="beforepay" <?php if (!get_option('jct_woo_checkout_pos') || get_option('jct_woo_checkout_pos') == "beforepay") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Payment', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="afterpay" <?php if (get_option('jct_woo_checkout_pos') == "afterpay") { ?>selected<?php } ?>>
										<?php esc_html_e('After Payment', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="beforesubmit" <?php if (get_option('jct_woo_checkout_pos') == "beforesubmit") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Pay Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="beforebilling" <?php if (get_option('jct_woo_checkout_pos') == "beforebilling") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Billing', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="afterbilling" <?php if (get_option('jct_woo_checkout_pos') == "afterbilling") { ?>selected<?php } ?>>
										<?php esc_html_e('After Billing', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

						<tr valign="top" style="border-bottom: 1px solid #f3f3f3;">
							<th scope="row">
								<?php echo esc_html__('WooCommerce Pay for Order', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_woo_checkout_pay" <?php if (get_option('jct_woo_checkout_pay')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php if ( class_exists( 'WooCommerce' ) ) { ?>

						<?php $available_gateways = WC()->payment_gateways->get_available_payment_gateways(); ?>

						<?php if(!empty($available_gateways)) { ?>

							<br/>

							<p style="font-size: 15px; font-weight: 600;">
								<?php echo esc_html__('Payment Methods to Skip', 'just-cloudflare-turnstile'); ?> <a href="#" class="jct_toggle_skip_methods" style="font-size: 10px; text-decoration: none; color: #333;">&#9660;</a>
							</p>
							<script>
								jQuery(document).ready(function() {
									jQuery('.jct_toggle_skip_methods').click(function(e) {
										e.preventDefault();
										jQuery('#toggleContentSkipMethods').toggle();
									});
								});
							</script>

							<div id="toggleContentSkipMethods" style="display: none;"> <!-- Initially hidden -->
							
								<i style="font-size: 10px;">
									<?php echo esc_html__("If selected below, Turnstile check will not be run for that specific payment method.", 'just-cloudflare-turnstile'); ?>
									<br/>
									<?php echo esc_html__("Useful for 'Express Checkout' payment methods compatibility.", 'just-cloudflare-turnstile'); ?>
								</i>

								<?php
								$selected_payment_methods = get_option('jct_selected_payment_methods', array());
								if(!$selected_payment_methods) $selected_payment_methods = array();
								if(!empty($available_gateways)) { ?>
								<div style="margin-top: 10px; max-width: 200px;">
								<?php foreach ( $available_gateways as $gateway ) : ?>
								<p>
									<input type="checkbox" name="jct_selected_payment_methods[]" style="float: none; margin-top: 2px;"
									value="<?php echo esc_attr( $gateway->id ); ?>" <?php echo in_array( $gateway->id, $selected_payment_methods, true ) ? 'checked' : ''; ?> >
									<label><?php echo esc_html( $gateway->get_title() ); ?></label>
								</p>
								<?php endforeach; ?>
								</div>
								<?php } ?>

							</div>

					<?php } ?>

				<?php } ?>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">' . esc_html__('WooCommerce', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // EDD
			if (cft_is_plugin_active('easy-digital-downloads/easy-digital-downloads.php') || cft_is_plugin_active('easy-digital-downloads-pro/easy-digital-downloads.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Easy Digital Downloads', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('EDD Checkout', 'just-cloudflare-turnstile'); ?>
								<br /><br />
								- <?php echo esc_html__('Guest Checkout Only', 'just-cloudflare-turnstile'); ?>
							</th>
							<td>
								<input type="checkbox" name="jct_edd_checkout" <?php if (get_option('jct_edd_checkout')) { ?>checked<?php } ?>>
								<br /><br />
								<input type="checkbox" name="jct_edd_guest_only" <?php if (get_option('jct_edd_guest_only')) { ?>checked<?php } ?>>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('EDD Login', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_edd_login" <?php if (get_option('jct_edd_login')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('EDD Register', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_edd_register" <?php if (get_option('jct_edd_register')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/easy-digital-downloads/" target="_blank">' . esc_html__('Easy Digital Downloads', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Paid Memberships PRO
			if (cft_is_plugin_active('paid-memberships-pro/paid-memberships-pro.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Paid Memberships Pro', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Checkout / Registration', 'just-cloudflare-turnstile'); ?>
								<br /><br />
								- <?php echo esc_html__('Guest Checkout Only', 'just-cloudflare-turnstile'); ?>
							</th>
							<td>
								<input type="checkbox" name="jct_pmp_checkout" <?php if (get_option('jct_pmp_checkout')) { ?>checked<?php } ?>>
								<br /><br />
								<input type="checkbox" name="jct_pmp_guest_only" <?php if (get_option('jct_pmp_guest_only')) { ?>checked<?php } ?>>
							</td>
						</tr>

						<script>
						jQuery(document).ready(function(){
							jQuery("input[name='jct_login']").change(function(){
								if(jQuery("input[name='jct_login']").is(':checked')){
									jQuery('#jct_pmp_login').prop('checked', true);
								} else {
									jQuery('#jct_pmp_login').prop('checked', false);
								}
							});
						});
						</script>
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Login Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type='checkbox' name='jct_pmp_login' id='jct_pmp_login' <?php if (get_option('jct_login')) { ?>checked<?php } ?>
							title='<?php echo esc_html__('Edit via "WordPress Login" option in the "Default WordPress Forms" settings.', 'just-cloudflare-turnstile'); ?>' disabled></td>
						</tr>

						<!-- Lost Password -->
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Lost Password Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type='checkbox' name='jct_wpuf_reset' id='jct_wpuf_reset'
							title='<?php echo esc_html__('Currently Turnstile can not be implemented on the lost password form when PMP is installed.', 'just-cloudflare-turnstile'); ?>'
							disabled></td>
						</tr>
						<!-- Set name="jct_reset" to disabled and unchecked -->
						<script>
						jQuery(document).ready(function(){
							jQuery("input[name='jct_reset']").prop('disabled', true);
							jQuery("input[name='jct_reset']").prop('checked', false);
							jQuery("input[name='jct_reset']").attr('title', '<?php echo esc_html__('Currently Turnstile can not be implemented on the lost password form when PMP is installed.', 'just-cloudflare-turnstile'); ?>');
						});
						</script>
						<!-- Show X inside checkbox -->
						<style>
						#jct_wpuf_reset:after, input[name='jct_reset']:after {
							content: "X";
							color: #333;
							font-weight: bold;
							font-size: 15px;
							position: absolute;
							margin-left: -5px;
							margin-top: 7px;
						}
						</style>
						
					</table>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://en-gb.wordpress.org/plugins/paid-memberships-pro/" target="_blank">' . esc_html__('Paid Memberships PRO', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Contact Form 7
			if (cft_is_plugin_active('contact-form-7/wp-contact-form-7.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Contact Form 7', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all CF7 Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_cf7_all" <?php if (get_option('jct_cf7_all')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<br />

					<?php echo esc_html__('To add Turnstile to individual Contact Form 7 forms, simply add this shortcode to any of your forms (in the form editor):', 'just-cloudflare-turnstile'); ?>
					<br /><span style="color: red; font-size: 15px; font-weight: bold;">[cf7-simple-turnstile]</span>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">' . esc_html__('Contact Form 7', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // WPForms
			if (cft_is_plugin_active('wpforms-lite/wpforms.php') || cft_is_plugin_active('wpforms/wpforms.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('WPForms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all WPForms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_wpforms" <?php if (get_option('jct_wpforms')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added before/after the submit button, on ALL your forms created with WPForms.', 'just-cloudflare-turnstile'); ?>
					<?php echo esc_html__('Note: WPForms has an option to configure Turnstile on its own Settings page "CAPTCHA" tab. You should only enable it in one place, either here -OR- in those settings.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -15px;">

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_wpforms_pos">
									<option value="before" <?php if (!get_option('jct_wpforms_pos') || get_option('jct_wpforms_pos') == "before") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="after" <?php if (get_option('jct_wpforms_pos') == "after") { ?>selected<?php } ?>>
										<?php esc_html_e('After Button', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

					</table>

					<table class="form-table" style="margin-bottom: -10px;">
						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Disabled Form IDs', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<input type="text" name="jct_wpforms_disable" value="<?php echo esc_html(get_option('jct_wpforms_disable')); ?>" />
							</td>
						</tr>
					</table>
					<i style="font-size: 10px;">
						<?php echo sprintf(__('If you want to DISABLE the Turnstile widget on certain forms, enter the %s ID in this field.', 'just-cloudflare-turnstile'), 'WPForms Form'); ?>
						<?php echo esc_html__('Separate each ID with a comma, for example: 5,10', 'just-cloudflare-turnstile'); ?>
					</i>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/wpforms-lite/" target="_blank">' . esc_html__('WPForms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Gravity Forms
			if (cft_is_plugin_active('gravityforms/gravityforms.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Gravity Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Gravity Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_gravity" <?php if (get_option('jct_gravity')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added before/after the submit button, on ALL your forms created with Gravity Forms.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -15px;">

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_gravity_pos">
									<option value="before" <?php if (!get_option('jct_gravity_pos') || get_option('jct_gravity_pos') == "before") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="after" <?php if (get_option('jct_gravity_pos') == "after") { ?>selected<?php } ?>>
										<?php esc_html_e('After Button', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

					</table>

					<table class="form-table" style="margin-bottom: -10px;">
						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Disabled Form IDs', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<input type="text" name="jct_gravity_disable" value="<?php echo esc_html(get_option('jct_gravity_disable')); ?>" />
							</td>
						</tr>
					</table>
					<i style="font-size: 10px;">
						<?php echo sprintf(__('If you want to DISABLE the Turnstile widget on certain forms, enter the %s ID in this field.', 'just-cloudflare-turnstile'), 'Gravity Form'); ?>
						<?php echo esc_html__('Separate each ID with a comma, for example: 5,10', 'just-cloudflare-turnstile'); ?>
					</i>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://www.gravityforms.com/" target="_blank">' . esc_html__('Gravity Forms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Fluent Forms
			if (cft_is_plugin_active('fluentform/fluentform.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Fluent Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Fluent Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_fluent" <?php if (get_option('jct_fluent')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with Fluent Forms.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -10px;">
						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Disabled Form IDs', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<input type="text" name="jct_fluent_disable" value="<?php echo esc_html(get_option('jct_fluent_disable')); ?>" />
							</td>
						</tr>
					</table>
					<i style="font-size: 10px;">
						<?php echo sprintf(__('If you want to DISABLE the Turnstile widget on certain forms, enter the %s ID in this field.', 'just-cloudflare-turnstile'), 'Fluent Form'); ?>
						<?php echo esc_html__('Separate each ID with a comma, for example: 5,10', 'just-cloudflare-turnstile'); ?>
					</i>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/fluentform/" target="_blank">' . esc_html__('Fluent Forms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Jetpack Forms
			if (cft_is_plugin_active('jetpack/jetpack.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Jetpack Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Jetpack Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_jetpack" <?php if (get_option('jct_jetpack')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added after the submit button, on ALL your forms created with Jetpack Forms.', 'just-cloudflare-turnstile'); ?>
				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/jetpack/" target="_blank">' . esc_html__('Jetpack Forms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Formidable Forms
			if (cft_is_plugin_active('formidable/formidable.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Formidable Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Formidable Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_formidable" <?php if (get_option('jct_formidable')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with Formidable Forms.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -15px;">

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_formidable_pos">
									<option value="before" <?php if (!get_option('jct_formidable_pos') || get_option('jct_formidable_pos') == "before") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="after" <?php if (get_option('jct_formidable_pos') == "after") { ?>selected<?php } ?>>
										<?php esc_html_e('After Button', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

					</table>
				
					<table class="form-table" style="margin-bottom: -10px;">
						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Disabled Form IDs', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<input type="text" name="jct_formidable_disable" value="<?php echo esc_html(get_option('jct_formidable_disable')); ?>" />
							</td>
						</tr>
					</table>
					<i style="font-size: 10px;">
						<?php echo sprintf(__('If you want to DISABLE the Turnstile widget on certain forms, enter the %s ID in this field.', 'just-cloudflare-turnstile'), 'Formidable Form'); ?>
						<?php echo esc_html__('Separate each ID with a comma, for example: 5,10', 'just-cloudflare-turnstile'); ?>
					</i>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/formidable/" target="_blank">' . esc_html__('Formidable', 'just-cloudflare-turnstile') . '</a>');
			}
			?>
			
			<?php // Forminator Forms
			if (cft_is_plugin_active('forminator/forminator.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Forminator Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Forminator Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_forminator" <?php if (get_option('jct_forminator')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with Forminator Forms.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -15px;">

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_forminator_pos">
									<option value="before" <?php if (!get_option('jct_forminator_pos') || get_option('jct_forminator_pos') == "before") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="after" <?php if (get_option('jct_forminator_pos') == "after") { ?>selected<?php } ?>>
										<?php esc_html_e('After Button', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

					</table>

					<table class="form-table" style="margin-bottom: -10px;">
						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Disabled Form IDs', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<input type="text" name="jct_forminator_disable" value="<?php echo esc_html(get_option('jct_forminator_disable')); ?>" />
							</td>
						</tr>
					</table>
					<i style="font-size: 10px;">
						<?php echo sprintf(__('If you want to DISABLE the Turnstile widget on certain forms, enter the %s ID in this field.', 'just-cloudflare-turnstile'), 'Forminator Form'); ?>
						<?php echo esc_html__('Separate each ID with a comma, for example: 5,10', 'just-cloudflare-turnstile'); ?>
					</i>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/forminator/" target="_blank">' . esc_html__('Forminator', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // WS Form
			if (cft_is_plugin_active('ws-form/ws-form.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('WS Form', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<p>
						<?php echo esc_html__('Currently WS Form is not supported by this plugin, however their plugin does have its own Turnstile addon.', 'just-cloudflare-turnstile'); ?>
						<a href="https://wsform.com/knowledgebase/turnstile/" target="_blank"><?php echo esc_html__('Click here for more information.', 'just-cloudflare-turnstile'); ?></a>
					</p>

				</div>
			<?php
			}
			?>

			<?php // Ninja Forms
			if (cft_is_plugin_active('ninja-forms/ninja-forms.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Ninja Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<p>
						<?php echo esc_html__('Currently Ninja Forms is not supported by this plugin.', 'just-cloudflare-turnstile'); ?>
					</p>

				</div>
			<?php
			}
			?>

			<?php // Elementor Forms
			if ( cft_is_plugin_active('elementor-pro/elementor-pro.php') || cft_is_plugin_active('pro-elements/pro-elements.php') ) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Elementor Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Elementor Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_elementor" <?php if (get_option('jct_elementor')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with Elementor Pro Forms.', 'just-cloudflare-turnstile'); ?>

					<table class="form-table" style="margin-bottom: -15px;">

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Widget Location', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_elementor_pos">
									<option value="before" <?php if (!get_option('jct_elementor_pos') || get_option('jct_elementor_pos') == "before") { ?>selected<?php } ?>>
										<?php esc_html_e('Before Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="after" <?php if (get_option('jct_elementor_pos') == "after") { ?>selected<?php } ?>>
										<?php esc_html_e('After Button', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="afterform" <?php if (get_option('jct_elementor_pos') == "afterform") { ?>selected<?php } ?>>
										<?php esc_html_e('After Form', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

					</table>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://elementor.com/features/form-builder/" target="_blank">' . esc_html__('Elementor Forms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>
	
			<?php // Mailchimp for WordPress
			if (cft_is_plugin_active('mailchimp-for-wp/mailchimp-for-wp.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('MC4WP: Mailchimp for WordPress', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<?php echo esc_html__('To add Turnstile to Mailchimp for WordPress, simply add this shortcode to any of your forms (in the form editor):', 'just-cloudflare-turnstile'); ?>
					<br /><span style="color: red; font-size: 15px; font-weight: bold;">[mc4wp-simple-turnstile]</span>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/mailchimp-for-wp/" target="_blank">' . esc_html__('Mailchimp for WordPress', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // MailPoet
			if (cft_is_plugin_active('mailpoet/mailpoet.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('MailPoet', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all MailPoet Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_mailpoet" <?php if (get_option('jct_mailpoet')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with MailPoet.', 'just-cloudflare-turnstile'); ?>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/mailpoet/" target="_blank">' . esc_html__('MailPoet', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Kadence Forms
			if (cft_is_plugin_active('kadence-blocks/kadence-blocks.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Kadence Forms', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Enable on all Kadence Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_kadence" <?php if (get_option('jct_kadence')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

					<?php echo esc_html__('When enabled, Turnstile will be added above the submit button, on ALL your forms created with Kadence Forms.', 'just-cloudflare-turnstile'); ?>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/kadence-blocks/" target="_blank">' . esc_html__('Kadence Forms', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // BuddyPress
			if (cft_is_plugin_active('buddypress/bp-loader.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('BuddyPress', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('BuddyPress Register', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_bp_register" <?php if (get_option('jct_bp_register')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

				</div>
			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/buddypress/" target="_blank">' . esc_html__('BuddyPress', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // bbPress
			if (cft_is_plugin_active('bbpress/bbpress.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('bbPress', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('bbPress Create Topic', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_bbpress_create" <?php if (get_option('jct_bbpress_create')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('bbPress Reply', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_bbpress_reply" <?php if (get_option('jct_bbpress_reply')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php echo esc_html__('Alignment', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<select name="jct_bbpress_align">
									<option value="left" <?php if (!get_option('jct_bbpress_align') || get_option('jct_bbpress_align') == "left") { ?>selected<?php } ?>>
										<?php esc_html_e('Left', 'just-cloudflare-turnstile'); ?>
									</option>
									<option value="right" <?php if (get_option('jct_bbpress_align') == "right") { ?>selected<?php } ?>>
										<?php esc_html_e('Right', 'just-cloudflare-turnstile'); ?>
									</option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Guest Users Only', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_bbpress_guest_only" <?php if (get_option('jct_bbpress_guest_only')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/bbpress/" target="_blank">' . esc_html__('bbPress', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // Ultimate Member
			if (cft_is_plugin_active('ultimate-member/ultimate-member.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('Ultimate Member', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('UM Login Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_um_login" <?php if (get_option('jct_um_login')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('UM Register Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_um_register" <?php if (get_option('jct_um_register')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('UM Password Reset Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_um_password" <?php if (get_option('jct_um_password')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/ultimate-member/" target="_blank">' . esc_html__('Ultimate Member', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // MemberPress
			if (cft_is_plugin_active('memberpress/memberpress.php')) { 

				if(get_option('jct_mepr_product_ids')) {
				  $LimitedToProductIDs = get_option('jct_mepr_product_ids');
				  $ProductsNeedingCaptcha = explode("\n", str_replace("\r", "", $LimitedToProductIDs));
				}
				?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('MemberPress', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<script>
						jQuery(document).ready(function(){
							jQuery("input[name='jct_login']").change(function(){
								if(jQuery("input[name='jct_login']").is(':checked')){
									jQuery('#jct_mepr_login').prop('checked', true);
								} else {
									jQuery('#jct_mepr_login').prop('checked', false);
								}
							});
						});
						</script>
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Login Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type='checkbox' name='jct_mepr_login' id='jct_mepr_login' <?php if (get_option('jct_login')) { ?>checked<?php } ?>
							title='<?php echo esc_html__('Edit via "WordPress Login" option in the "Default WordPress Forms" settings.', 'just-cloudflare-turnstile'); ?>' disabled></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Registration/Checkout Forms', 'just-cloudflare-turnstile'); 
								if(get_option('jct_mepr_product_ids')) {
									?>
								<br><span style="font-weight:400;font-size:12px;"><span style="color:#d1242f;"><?php echo esc_html__('Limited to:', 'just-cloudflare-turnstile'); ?></span> <?php echo implode(', ' , $ProductsNeedingCaptcha); ?></span>
								<?php
								}
								?>
							</th>
							<td><input type='checkbox' name='jct_mepr_register' id='jct_mepr_register' <?php if (get_option('jct_mepr_register')) { ?>checked<?php } ?>></td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('ONLY enable for these Membership IDs:', 'just-cloudflare-turnstile'); ?></th>
							<td>
								<textarea style="width: 240px;" name="jct_mepr_product_ids"><?php echo sanitize_textarea_field(get_option('jct_mepr_product_ids')); ?></textarea>
								<br /><i style="font-size: 10px;"><?php echo esc_html__('(Optional) One per line. For Membership products that are not on this list, no Turnstile challenge will be loaded or enforced.', 'just-cloudflare-turnstile'); ?></i>
							</td>
						</tr>

					</table>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://memberpress.com/" target="_blank">' . esc_html__('MemberPress', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // WP-Members
			if (cft_is_plugin_active('wp-members/wp-members.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('WP-Members', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<p>
							<?php echo esc_html__('Turnstile is supported for WP-Members Login and Registration forms. Enable for these forms in the "Default WordPress Forms" settings.', 'just-cloudflare-turnstile'); ?>
						</p><br/>

					</table>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/wp-members/" target="_blank">' . esc_html__('WP-Members', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // WP User Frontend
			if (cft_is_plugin_active('wp-user-frontend/wpuf.php')) { ?>
				<button type="button" class="jct-accordion"><?php echo esc_html__('WP User Frontend', 'just-cloudflare-turnstile'); ?></button>
				<div class="jct-panel">

					<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

						<script>
						jQuery(document).ready(function(){
							jQuery("input[name='jct_login']").change(function(){
								if(jQuery("input[name='jct_login']").is(':checked')){
									jQuery('#jct_wpuf_login').prop('checked', true);
								} else {
									jQuery('#jct_wpuf_login').prop('checked', false);
								}
							});
						});
						</script>
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Login Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type='checkbox' name='jct_wpuf_login' id='jct_wpuf_login' <?php if (get_option('jct_login')) { ?>checked<?php } ?>
							title='<?php echo esc_html__('Edit via "WordPress Login" option in the "Default WordPress Forms" settings.', 'just-cloudflare-turnstile'); ?>' disabled></td>
						</tr>

						<script>
						jQuery(document).ready(function(){
							jQuery("input[name='jct_reset']").change(function(){
								if(jQuery("input[name='jct_reset']").is(':checked')){
									jQuery('#jct_wpuf_reset').prop('checked', true);
								} else {
									jQuery('#jct_wpuf_reset').prop('checked', false);
								}
							});
						});
						</script>
						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Reset Password Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type='checkbox' name='jct_wpuf_reset' id='jct_wpuf_reset' <?php if (get_option('jct_reset')) { ?>checked<?php } ?>
							title='<?php echo esc_html__('Edit via "WordPress Reset Password" option in the "Default WordPress Forms" settings.', 'just-cloudflare-turnstile'); ?>' disabled></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Register Form', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_wpuf_register" <?php if (get_option('jct_wpuf_register')) { ?>checked<?php } ?>></td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php echo esc_html__('Post Forms', 'just-cloudflare-turnstile'); ?>
							</th>
							<td><input type="checkbox" name="jct_wpuf_forms" <?php if (get_option('jct_wpuf_forms')) { ?>checked<?php } ?>></td>
						</tr>

					</table>

				</div>

			<?php
			} else {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/wp-user-frontend/" target="_blank">' . esc_html__('WP User Frontend', 'just-cloudflare-turnstile') . '</a>');
			}
			?>

			<?php // wpDiscuz
			if (!cft_is_plugin_active('wpdiscuz/class.WpdiscuzCore.php')) {
				array_push($not_installed, '<a href="https://wordpress.org/plugins/wpdiscuz/" target="_blank">' . esc_html__('wpDiscuz', 'just-cloudflare-turnstile') . '</a>');
			} ?>

			<?php
			// Output Custom Settings
			do_action('jct-settings-section');
			$not_installed = apply_filters('jct-settings-not-installed', $not_installed);
			?>

			<?php // List of plugins not installed
			if (!empty($not_installed)) { ?>
				<br />

				<table class="form-table" style="margin-top: -15px; margin-bottom: -10px;">

					<tr valign="top">
						<th scope="row">
							<span style="font-size: 19px;"><?php echo esc_html__('Other Integrations', 'just-cloudflare-turnstile'); ?></span>
							<p>
								
								<?php echo esc_html__('You can also enable Turnstile on', 'just-cloudflare-turnstile') . " ";
								$last_plugin = end($not_installed);
								foreach ($not_installed as $not_plugin) {
									if ($not_plugin == $last_plugin && count($not_installed) > 1) echo 'and ';
									echo $not_plugin;
									if ($not_plugin != $last_plugin) {
										echo ', ';
									} else {
										echo '.';
									}
								}
								?>

								<?php echo esc_html__('Simply install the plugin and new settings will appear above.', 'just-cloudflare-turnstile'); ?>

							</p>
						</th>
					</tr>

				</table>

			<?php } ?>

			</div>

			<?php submit_button(); ?>

			<div style="font-size: 10px; margin-top: 15px;">
				<!-- Delete Options on Uninstall (Always keep this option last) -->
				<input type="checkbox" name="jct_uninstall_remove" <?php if (get_option('jct_uninstall_remove')) { ?>checked<?php } ?> style="transform: scale(0.7); margin: -2px 0 0 0;">
				<?php echo esc_html__('Delete all of this plugins saved options when the plugin is deleted via plugins page.', 'just-cloudflare-turnstile'); ?>
			</div>

			<div style="font-size: 10px; margin-top: 15px;">
				<!-- Enable Logging -->
				<input type="checkbox" name="jct_log_enable" <?php if (get_option('jct_log_enable')) { ?>checked<?php } ?> style="transform: scale(0.7); margin: -2px 0 0 0;">
				<?php echo esc_html__('Enable debug logging of Turnstile form submission events.', 'just-cloudflare-turnstile'); ?>
			</div>
			
		</form>

		<?php if(get_option('jct_log_enable')) { ?>
		<br/><button type="button" class="jct-accordion" id="jct-accordion-whitelist"><?php echo esc_html__('Turnstile Debug Log', 'just-cloudflare-turnstile'); ?></button>
			<div class="jct-panel">

				<?php
				$jct_log = get_option('jct_log');
				/* 	$jct_log[] = array(
					'date' => date('Y-m-d H:i:s'),
					'success' => $success,
					'error' => $errors,
					'ip' => $_SERVER['REMOTE_ADDR'],
					'page' => $_SERVER['REQUEST_URI'],
				);
				*/
				if ($jct_log) {
				echo '<div style="max-height: 200px; overflow: auto; border: 1px solid #ddd; padding: 0px;">';
					echo '<table>';
						echo '<tr valign="top">';
						echo '<td>';
						echo '<table class="widefat">';
						echo '<thead>';
						echo '<tr>';
						echo '<th>' . esc_html__('Date', 'just-cloudflare-turnstile') . '</th>';
						echo '<th>' . esc_html__('Success', 'just-cloudflare-turnstile') . '</th>';
						echo '<th>' . esc_html__('Response', 'just-cloudflare-turnstile') . '</th>';
						echo '<th>' . esc_html__('Info', 'just-cloudflare-turnstile') . '</th>';
						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
						$jct_log = array_reverse($jct_log);
						foreach ($jct_log as $log) {
							echo '<tr>';
							$log['date'] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['date']));
							echo '<td>' . esc_html($log['date']) . '</td>';
							echo '<td>' . ($log['success'] ? '<span style="color: green;">Yes</span>' : '<span style="color: red;">No</span>') . '</td>';
							echo '<td>';
							if(!$log['success']) {
								$error_val = $log['error'];
								echo esc_html($error_val);
							} else {
								echo '<span>' . esc_html__('Success', 'just-cloudflare-turnstile') . '</span>';
							}
							echo '</td>';
							echo '<td>';
							echo '<strong>' . esc_html__('IP:', 'just-cloudflare-turnstile') . '</strong> ' . esc_html($log['ip']) . '<br />';
							echo '<strong>' . esc_html__('URL:', 'just-cloudflare-turnstile') . '</strong> ' . esc_html($log['page']);
							echo '</td>';
						}
						echo '</tr>';
						echo '</tbody>';
						echo '</table>';
						echo '</td>';
						echo '</tr>';
					echo '</table>';
				echo '</div>';
				// Error code meanings
				echo '<div style="margin-top: 20px; font-size: 9px;">';
				echo '<strong><u>' . esc_html__('Error Codes', 'just-cloudflare-turnstile') . '</strong></u><br />';
				echo '- <strong>missing-input-response:</strong> ' . jct_error_message('missing-input-response') . esc_html__(' (Visitor submitted form when Turnstile was not successfully completed.)', 'just-cloudflare-turnstile') . '<br />';
				echo '- <strong>missing-input-secret:</strong> ' . jct_error_message('missing-input-secret') . '<br />';
				echo '- <strong>invalid-input-secret:</strong> ' . jct_error_message('invalid-input-secret') . '<br />';
				echo '- <strong>invalid-input-response:</strong> ' . jct_error_message('invalid-input-response') . '<br />';
				echo '- <strong>bad-request:</strong> ' . jct_error_message('bad-request') . '<br />';
				echo '- <strong>timeout-or-duplicate:</strong> ' . jct_error_message('timeout-or-duplicate') . '<br />';
				echo '- <strong>internal-error:</strong> ' . jct_error_message('internal-error') . '<br />';
				echo '</div>';
				} else {
					echo '<p>' . esc_html__('No events logged yet.', 'just-cloudflare-turnstile') . '</p>';
				}
				?>
			</div>
		<?php } else {
			if(get_option('jct_log')) {
				delete_option('jct_log');
			}
		}
		?>

		<div class="jct-admin-promo">

			<p style="font-size: 15px; font-weight: bold;"><?php echo esc_html__('100% free plugin developed by', 'just-cloudflare-turnstile'); ?> <a href="https://twitter.com/ElliotSowersby" target="_blank" title="@ElliotSowersby on Twitter"><span class="dashicons dashicons-twitter" style="margin-top: 5px; font-size: 15px; text-decoration: none;"></span></a> <a href="https://Just There.com/?utm_campaign=simple-turnstile-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank" title="Just There - WordPress Maintenance & Support"><span class="dashicons dashicons-admin-links" style="margin-top: 5px; font-size: 15px; text-decoration: none;"></span>Just There</a></p>

			<p style="font-size: 15px;">
				- <?php echo esc_html__('Like this plugin?', 'just-cloudflare-turnstile'); ?> <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/reviews/#new-post" target="_blank" title="<?php echo esc_html__('Review on WordPress.org', 'just-cloudflare-turnstile'); ?>"><?php echo esc_html__('Please submit a review', 'just-cloudflare-turnstile'); ?></a> <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/reviews/#new-post" target="_blank" title="<?php echo esc_html__('Review on WordPress.org', 'just-cloudflare-turnstile'); ?>" style="text-decoration: none;">
					<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>
				</a></p>

			<p style="font-size: 15px;">- <?php echo esc_html__('Need help? Have a suggestion?', 'just-cloudflare-turnstile'); ?> <a href="https://wordpress.org/support/plugin/just-cloudflare-turnstile/#new-topic-0" target="_blank"><?php echo esc_html__('Create a support topic', 'just-cloudflare-turnstile'); ?><span class="dashicons dashicons-external" style="font-size: 15px; margin-top: 5px; text-decoration: none;"></span></a></p>

			<p style="font-size: 15px;">
				- <?php echo esc_html__('Want to support the plugin?', 'just-cloudflare-turnstile'); ?> <?php echo esc_html__('Feel free to', 'just-cloudflare-turnstile'); ?> <a href="https://www.paypal.com/donate/?hosted_button_id=RX28BBH7L5XDS" target="_blank"><?php echo esc_html__('Donate', 'just-cloudflare-turnstile'); ?><span class="dashicons dashicons-external" style="font-size: 15px; margin-top: 5px; text-decoration: none;"></span></a>
			</p>

			<p style="font-size: 12px;">
				<a href="https://translate.wordpress.org/projects/wp-plugins/just-cloudflare-turnstile/" target="_blank"><?php echo esc_html__('Translate into your language', 'just-cloudflare-turnstile'); ?><span class="dashicons dashicons-external" style="font-size: 15px; margin-top: 2px; text-decoration: none;"></span></a>
				<br />
				<a href="https://github.com/ElliotSowersby/just-cloudflare-turnstile" target="_blank"><?php echo esc_html__('View on GitHub', 'just-cloudflare-turnstile'); ?><span class="dashicons dashicons-external" style="font-size: 15px; margin-top: 2px; text-decoration: none;"></span></a>
			</p>

		</div>

		<div class="jct-admin-promo" style="margin-top: 15px;">

			<p style="font-size: 15px;">
				<a href="https://Just There.com/plugins/just-cloudflare-turnstile" target="_blank">
					<?php echo esc_html__( 'View more plugins by Just There', 'just-cloudflare-turnstile' ); ?><span class="dashicons dashicons-external"
					style="font-size: 15px; margin-top: 5px; text-decoration: none;"></span>
				</a>
			</p>

		</div>

<?php } ?>
