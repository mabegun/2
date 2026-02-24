<?php
/**
 * AJAX - Сообщения
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить сообщения раздела
 */
function prokb_ajax_get_messages() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    
    if (!$section_id) {
        wp_send_json_error(array('message' => 'ID раздела не указан'));
    }
    
    $messages = get_posts(array(
        'post_type'      => 'prokb_message',
        'posts_per_page' => -1,
        'meta_key'       => 'section_id',
        'meta_value'     => $section_id,
        'orderby'        => 'date',
        'order'          => 'ASC',
    ));
    
    $result = array();
    foreach ($messages as $msg) {
        $result[] = prokb_format_message($msg);
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_messages', 'prokb_ajax_get_messages');

/**
 * Отправить сообщение
 */
function prokb_ajax_send_message() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    
    $section_id = intval($_POST['section_id'] ?? 0);
    $content = sanitize_textarea_field($_POST['content'] ?? '');
    $is_critical = $_POST['is_critical'] === 'true';
    $parent_id = intval($_POST['parent_id'] ?? 0);
    
    if (!$section_id || empty($content)) {
        wp_send_json_error(array('message' => 'Обязательные поля не заполнены'));
    }
    
    $message_id = wp_insert_post(array(
        'post_type'    => 'prokb_message',
        'post_title'   => 'Сообщение',
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_author'  => $user_id,
    ));
    
    update_post_meta($message_id, 'section_id', $section_id);
    update_post_meta($message_id, 'is_critical', $is_critical);
    update_post_meta($message_id, 'is_resolved', false);
    if ($parent_id) {
        update_post_meta($message_id, 'parent_id', $parent_id);
    }
    
    wp_send_json_success(array(
        'message'    => 'Сообщение отправлено',
        'message_id' => $message_id,
    ));
}
add_action('wp_ajax_prokb_send_message', 'prokb_ajax_send_message');

/**
 * Отметить сообщение как решённое
 */
function prokb_ajax_resolve_message() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    $message_id = intval($_POST['message_id'] ?? 0);
    $resolved = $_POST['resolved'] === 'true' || $_POST['resolved'] === true;
    
    if (!$message_id) {
        wp_send_json_error(array('message' => 'ID сообщения не указан'));
    }
    
    update_post_meta($message_id, 'is_resolved', $resolved);
    
    wp_send_json_success(array('message' => 'Статус сообщения обновлён'));
}
add_action('wp_ajax_prokb_resolve_message', 'prokb_ajax_resolve_message');
