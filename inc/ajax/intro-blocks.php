<?php
/**
 * AJAX - Вводная информация
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
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    $type = sanitize_text_field($_POST['type'] ?? 'text');
    $title = sanitize_text_field($_POST['title'] ?? '');
    $content = sanitize_textarea_field($_POST['content'] ?? '');
    
    if (!$project_id || empty($title)) {
        wp_send_json_error(array('message' => 'Обязательные поля не заполнены'));
    }
    
    $block_id = wp_insert_post(array(
        'post_type' => 'prokb_intro_block',
        'post_title' => $title,
        'post_content' => $content,
        'post_status' => 'publish',
    ));
    
    update_post_meta($block_id, 'project_id', $project_id);
    update_post_meta($block_id, 'type', $type);
    
    // Обработка файла
    $file_name = '';
    $file_path = '';
    
    if ($type === 'file' && !empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload = wp_handle_upload($_FILES['file'], array('test_form' => false));
        if (!isset($upload['error'])) {
            $file_path = $upload['url'];
            $file_name = $_FILES['file']['name'];
            update_post_meta($block_id, 'file_path', $file_path);
            update_post_meta($block_id, 'file_name', $file_name);
        }
    }
    
    wp_send_json_success(array(
        'message' => 'Блок добавлен',
        'block' => array(
            'id' => $block_id,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'file_path' => $file_path,
            'file_name' => $file_name,
        ),
    ));
}
add_action('wp_ajax_prokb_add_intro_block', 'prokb_ajax_add_intro_block');

/**
 * Удалить блок
 */
function prokb_ajax_delete_intro_block() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав'));
    }
    
    $block_id = intval($_POST['block_id'] ?? 0);
    if (!$block_id) {
        wp_send_json_error(array('message' => 'ID не указан'));
    }
    
    wp_delete_post($block_id, true);
    wp_send_json_success(array('message' => 'Блок удалён'));
}
add_action('wp_ajax_prokb_delete_intro_block', 'prokb_ajax_delete_intro_block');
