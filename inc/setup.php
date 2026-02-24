<?php
/**
 * Настройка темы
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Настройка темы после активации
 */
function prokb_setup() {
    load_theme_textdomain('prokb', PROKB_THEME_DIR . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'prokb'),
    ));
}
add_action('after_setup_theme', 'prokb_setup');

/**
 * Подключение стилей и скриптов
 */
function prokb_enqueue_assets() {
    wp_enqueue_style('prokb-style', PROKB_THEME_URI . '/style.css', array(), PROKB_VERSION);
    wp_enqueue_script('prokb-app', PROKB_THEME_URI . '/js/app.js', array(), PROKB_VERSION, true);
    
    wp_localize_script('prokb-app', 'prokbData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('prokb_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'prokb_enqueue_assets');
