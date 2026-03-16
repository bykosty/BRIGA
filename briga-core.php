<?php
/**
 * Plugin Name: BRIGA Core V5
 * Description: BRIGA OS Bistro Régent — V5
 * Version: 1.5.0
 * Author: Constantin BARNA
 */
defined('ABSPATH') or die('Non.');
define('BRIGA_VERSION', '1.5.0');
define('BRIGA_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once BRIGA_PLUGIN_DIR . 'includes/class-briga-db.php';
require_once BRIGA_PLUGIN_DIR . 'includes/class-briga-pin.php';
require_once BRIGA_PLUGIN_DIR . 'includes/class-briga-api.php';
require_once plugin_dir_path(__FILE__).'includes/class-briga-tasks.php';
require_once plugin_dir_path(__FILE__).'includes/class-briga-caisse.php';
require_once plugin_dir_path(__FILE__).'includes/class-briga-stock.php';
require_once plugin_dir_path(__FILE__).'includes/class-briga-commandes.php';
require_once plugin_dir_path(__FILE__).'includes/class-briga-salle.php';
require_once BRIGA_PLUGIN_DIR . 'includes/class-briga-shortcode.php';

register_activation_hook(__FILE__, function() {
    Briga_DB::install();
});

add_action('wp_enqueue_scripts', function() {
    if (!is_page()) return;
    global $post;
    if (!has_shortcode($post->post_content ?? '', 'briga_os')) return;

    wp_enqueue_style('briga-style', plugins_url('assets/briga-style.css',__FILE__), array(), '1.5.0');
    wp_enqueue_script('briga-api',     plugins_url('assets/briga-api.js',__FILE__),    {'jquery'}, '1.5.0', true);
    wp_enqueue_script('briga-helpers', plugins_url('assets/briga-helpers.js',__FILE__), {'briga-api'}, '1.5.0', true);
    wp_enqueue_script('briga-dashboard', plugins_url('assets/briga-dashboard.js',__FILE__), {'briga-helpers'}, '1.5.0', true);
    wp_enqueue_script('briga-tasks', plugins_url('assets/briga-tasks.js',__FILE__), {'briga-helpers'}, '1.5.0', true);
    wp_enqueue_script('briga-caisse', plugins_url('assets/briga-caisse.js',__FILE__), {'briga-helpers'}, '1.5.0', true);
    wp_enqueue_script('briga-stock', plugins_url('assets/briga-stock.js',__FILE__), {'briga-helpers'}, '1.5.0', true);
    wp_enqueue_script('briga-commandes', plugins_url('assets/briga-commandes.js',__FILE__), {'briga-helpers'}, '1.5.0', true);
    wp_enqueue_script('briga-salle', plugins_url('assets/briga-salle.js',__FILE__), {'briga-helpers'}, '1.5.0', true);
    wp_enqueue_script('briga-app', plugins_url('assets/briga-app.js',__FILE__), array('briga-helpers','briga-dashboard','briga-tasks','briga-caisse','briga-stock','briga-commandes','briga-salle'), '1.5.0', true);
    wp_localize_script('briga-app','BRIGA',array('ajaxurl'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('briga_nonce'),'rest_url'=>get_rest_url(null,'briga/v1/'),'rest_nonce'=>wp_create_nonce('wp_rest')));
});
