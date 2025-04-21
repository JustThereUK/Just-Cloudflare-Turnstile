<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Determine if the current user/session should be whitelisted from Turnstile checks.
 *
 * @return bool
 */
function jct_whitelisted() {
    // Never whitelist on the settings page
    if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'jct') {
        return false;
    }

    // Apply external filters
    $is_whitelisted = apply_filters('jct_whitelisted', false);

    // Whitelist: Logged-in users
    if (!$is_whitelisted && get_option('jct_whitelist_users') && is_user_logged_in()) {
        return true;
    }

    // Whitelist: IP addresses
    if (!$is_whitelisted && ($ip_list = get_option('jct_whitelist_ips'))) {
        $whitelist_ips = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $ip_list))));
        $current_ip = jct_get_ip();

        if ($current_ip && in_array($current_ip, $whitelist_ips, true)) {
            return true;
        }
    }

    // Whitelist: User agents
    if (!$is_whitelisted && ($ua_list = get_option('jct_whitelist_agents')) && !empty($_SERVER['HTTP_USER_AGENT'])) {
        $whitelist_agents = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $ua_list))));
        $current_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);

        foreach ($whitelist_agents as $agent) {
            if (stripos($current_agent, $agent) !== false) {
                return true;
            }
        }
    }

    return (bool) $is_whitelisted;
}

/**
 * Get the real client IP address, taking into account proxies and headers.
 *
 * @return string|false
 */
function jct_get_ip() {
    $headers = array('HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');

    foreach ($headers as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);

            foreach ($ips as $ip) {
                $ip = sanitize_text_field(trim($ip));

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
    }

    return false;
}
