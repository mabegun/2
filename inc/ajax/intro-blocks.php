<?php
/**
 * AJAX - Блоки вводной информации
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Добавить блок вводной информации
 */
function prokb_ajax_add_intro_block() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для добавления'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    $title = sanitize_text_field($_POST['title'] ?? '');
    $type = sanitize_text_field($_POST['type'] ?? 'text');
    $content = sanitize_textarea_field($_POST['content'] ?? '');
    $order = intval($_POST['order'] ?? 0);
    
    if (!$project_id || empty($title)) {
        wp_send_json_error(array('message' => 'Обязательные поля не заполнены'));
    }
    
    $block_id = wp_insert_post(array(
        'post_type'    => 'prokb_intro_block',
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'publish',
    ));
    
    update_post_meta($block_id, 'project_id', $project_id);
    update_post_meta($block_id, 'type', $type);
    update_post_meta($block_id, 'order', $order);
    
    // Обработка файла
    if ($type === 'file' && !empty($_FILES['file'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload = wp_handle_upload($_FILES['file'], array('test_form' => false));
        
        if (!isset($upload['error'])) {
            update_post_meta($block_id, 'file_path', $upload['url']);
            update_post_meta($block_id, 'file_name', $_FILES['file']['name']);
        }
    }
    
    wp_send_json_success(array(
        'message'  => 'Блок добавлен',
        'block_id' => $block_id,
    ));
}
add_action('wp_ajax_prokb_add_intro_block', 'prokb_ajax_add_intro_block');

/**
 * Удалить блок вводной информации
 */
function prokb_ajax_delete_intro_block() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для удаления'));
    }
    
    $block_id = intval($_POST['block_id'] ?? 0);
    
    if (!$block_id) {
        wp_send_json_error(array('message' => 'ID блока не указан'));
    }
    
    wp_delete_post($block_id, true);
    
    wp_send_json_success(array('message' => 'Блок удалён'));
}
add_action('wp_ajax_prokb_delete_intro_block', 'prokb_ajax_delete_intro_block');
