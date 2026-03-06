<?php
if (!defined('ABSPATH')) exit;

class BRIGA_Pin {

    public static function init() {
        add_action('wp_ajax_briga_pin_login',        [__CLASS__, 'pin_login']);
        add_action('wp_ajax_nopriv_briga_pin_login', [__CLASS__, 'pin_login']);
        add_action('wp_ajax_briga_pin_logout',        [__CLASS__, 'pin_logout']);
        add_action('wp_ajax_nopriv_briga_pin_logout', [__CLASS__, 'pin_logout']);
    }

    public static function pin_login() {
        check_ajax_referer('briga_nonce', 'nonce');
        $pin = isset($_POST['pin']) ? sanitize_text_field($_POST['pin']) : '';
        $hash = get_option('briga_pin_hash');
        if (!$hash) {
            $hash = wp_hash_password('1234');
            update_option('briga_pin_hash', $hash, false);
        }
        if ($pin && wp_check_password($pin, $hash)) {
            wp_send_json_success(['status' => 'ok', 'message' => 'PIN correct']);
        }
        wp_send_json_error(['status' => 'error', 'message' => 'PIN incorrect']);
    }

    public static function pin_logout() {
        check_ajax_referer('briga_nonce', 'nonce');
        wp_send_json_success(['status' => 'ok']);
    }
}
