<?php
if (!defined('ABSPATH')) {
    exit;
}

if (get_option('jct_mailpoet')) {

    /**
     * Inject Turnstile field into MailPoet forms
     *
     * @param string $formHtml
     * @return string
     */
    function jct_field_mailpoet($formHtml) {
        if (jct_whitelisted()) {
            return $formHtml;
        }

        // Enqueue JS integration
        wp_enqueue_script(
            'jct-mailpoet',
            plugins_url('just-cloudflare-turnstile/js/integrations/mailpoet.js'),
            [],
            '1.0',
            true
        );

        $uniqueId = wp_rand();

        // Generate Turnstile field markup
        ob_start();
        jct_field_show('.mailpoet_submit', 'turnstileMailpoetCallback', 'mailpoet-' . $uniqueId, '-mailpoet');
        $turnstile = ob_get_clean();

        // Insert field before submit button
        $formHtml = preg_replace(
            '/(<input[^>]*class="mailpoet_submit"[^>]*>)/i',
            $turnstile . '$1',
            $formHtml
        );

        return $formHtml;
    }
    add_filter('mailpoet_form_widget_post_process', 'jct_field_mailpoet');

    /**
     * Validate Turnstile token before MailPoet subscription
     *
     * @param array $data
     * @param array $segmentIds
     * @param object $form
     * @throws \MailPoet\UnexpectedValueException
     */
    function jct_mailpoet_check($data, $segmentIds, $form) {
        if (jct_whitelisted()) {
            return;
        }

        $token = isset($_POST['data']['jct-turnstile-response'])
            ? sanitize_text_field($_POST['data']['jct-turnstile-response'])
            : '';

        if (empty($token)) {
            throw new \MailPoet\UnexpectedValueException(jct_failed_message());
        }

        $check = jct_check($token);
        if (empty($check['success']) || $check['success'] !== true) {
            throw new \MailPoet\UnexpectedValueException(jct_failed_message());
        }
    }
    add_action('mailpoet_subscription_before_subscribe', 'jct_mailpoet_check', 10, 3);
}
