<?php
/*
Plugin Name: BRIGA Core
Description: Briga - Restaurant Operating System
Version: 1.0.0
*/

if (!defined('ABSPATH')) exit;

define('BRIGA_CORE_PATH', plugin_dir_path(__FILE__));
define('BRIGA_CORE_URL', plugin_dir_url(__FILE__));

require_once BRIGA_CORE_PATH . 'includes/class-briga-pin.php';
require_once BRIGA_CORE_PATH . 'includes/class-briga-tasks.php';
require_once BRIGA_CORE_PATH . 'includes/class-briga-shortcode.php';

add_action('init', ['BRIGA_Pin', 'init']);
add_action('init', ['BRIGA_Tasks', 'init']);
add_action('init', ['BRIGA_Shortcode', 'init']);

add_action('wp_enqueue_scripts', function () {
    if (!is_singular()) return;
    $post = get_post();
    if (!$post) return;
    if (strpos($post->post_content, '[briga_app]') === false) return;

    wp_enqueue_style('briga-style', BRIGA_CORE_URL . 'assets/briga-style.css', [], '1.0.0');
    wp_enqueue_script('briga-app', BRIGA_CORE_URL . 'assets/briga-app.js', [], '1.0.0', true);
    wp_localize_script('briga-app', 'BRIGA', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('briga_nonce'),
    ]);
});
