<?php
/**
 * AJAX - Разделы проектирования (настройки)
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить разделы проектирования
 */
function prokb_ajax_get_design_sections() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $sections = get_posts(array(
        'post_type'      => 'prokb_design_section',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ));
    
    $result = array();
    foreach ($sections as $section) {
        $result[] = array(
            'id'          => $section->ID,
            'code'        => get_post_meta($section->ID, 'code', true),
            'name'        => $section->post_title,
            'description' => get_post_meta($section->ID, 'description', true),
        );
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_design_sections', 'prokb_ajax_get_design_sections');

/**
 * Сохранить раздел проектирования
 */
function prokb_ajax_save_design_section() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if ($prokb_role !== 'director') {
        wp_send_json_error(array('message' => 'Нет прав для редактирования'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    $code = sanitize_text_field($_POST['code'] ?? '');
    $name = sanitize_text_field($_POST['name'] ?? '');
    $description = sanitize_text_field($_POST['description'] ?? '');
    
    if (empty($code)) {
        wp_send_json_error(array('message' => 'Код раздела обязателен'));
    }
    
    if ($section_id) {
        // Обновление
        wp_update_post(array(
            'ID'         => $section_id,
            'post_title' => $name ?: $code,
        ));
        update_post_meta($section_id, 'code', $code);
        update_post_meta($section_id, 'description', $description);
    } else {
        // Создание
        $section_id = wp_insert_post(array(
            'post_type'    => 'prokb_design_section',
            'post_title'   => $name ?: $code,
            'post_status'  => 'publish',
        ));
        update_post_meta($section_id, 'code', $code);
        update_post_meta($section_id, 'description', $description);
    }
    
    wp_send_json_success(array(
        'message'    => 'Раздел сохранён',
        'section_id' => $section_id,
    ));
}
add_action('wp_ajax_prokb_save_design_section', 'prokb_ajax_save_design_section');

/**
 * Удалить раздел проектирования
 */
function prokb_ajax_delete_design_section() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if ($prokb_role !== 'director') {
        wp_send_json_error(array('message' => 'Нет прав для удаления'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    
    if (!$section_id) {
        wp_send_json_error(array('message' => 'ID раздела не указан'));
    }
    
    wp_delete_post($section_id, true);
    
    wp_send_json_success(array('message' => 'Раздел удалён'));
}
add_action('wp_ajax_prokb_delete_design_section', 'prokb_ajax_delete_design_section');
