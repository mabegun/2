<?php
/**
 * AJAX - Файлы
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Загрузить файл
 */
function prokb_ajax_upload_file() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    $description = sanitize_text_field($_POST['description'] ?? '');
    
    if (!$section_id || empty($_FILES['file'])) {
        wp_send_json_error(array('message' => 'Файл не выбран'));
    }
    
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $upload = wp_handle_upload($_FILES['file'], array('test_form' => false));
    
    if (isset($upload['error'])) {
        wp_send_json_error(array('message' => 'Ошибка загрузки: ' . $upload['error']));
    }
    
    $file_id = wp_insert_post(array(
        'post_type'    => 'prokb_file',
        'post_title'   => $_FILES['file']['name'],
        'post_status'  => 'publish',
    ));
    
    update_post_meta($file_id, 'section_id', $section_id);
    update_post_meta($file_id, 'file_path', $upload['url']);
    update_post_meta($file_id, 'file_name', $_FILES['file']['name']);
    update_post_meta($file_id, 'file_type', $_FILES['file']['type']);
    update_post_meta($file_id, 'file_size', $_FILES['file']['size']);
    update_post_meta($file_id, 'description', $description);
    update_post_meta($file_id, 'is_current', true);
    
    wp_send_json_success(array(
        'message' => 'Файл загружен',
        'file_id' => $file_id,
        'url'     => $upload['url'],
        'name'    => $_FILES['file']['name'],
    ));
}
add_action('wp_ajax_prokb_upload_file', 'prokb_ajax_upload_file');

/**
 * Получить файлы раздела
 */
function prokb_ajax_get_files() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    
    if (!$section_id) {
        wp_send_json_error(array('message' => 'ID раздела не указан'));
    }
    
    $files = get_posts(array(
        'post_type'      => 'prokb_file',
        'posts_per_page' => -1,
        'meta_key'       => 'section_id',
        'meta_value'     => $section_id,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    
    $result = array();
    foreach ($files as $file) {
        $result[] = array(
            'id'          => $file->ID,
            'name'        => get_post_meta($file->ID, 'file_name', true),
            'path'        => get_post_meta($file->ID, 'file_path', true),
            'type'        => get_post_meta($file->ID, 'file_type', true),
            'size'        => get_post_meta($file->ID, 'file_size', true),
            'description' => get_post_meta($file->ID, 'description', true),
            'is_current'  => get_post_meta($file->ID, 'is_current', true),
            'date'        => get_the_date('d.m.Y H:i', $file),
            'author'      => prokb_get_user_data($file->post_author),
        );
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_files', 'prokb_ajax_get_files');

/**
 * Обновить статус файла (актуальный/неактуальный)
 */
function prokb_ajax_update_file_status() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $file_id = intval($_POST['file_id'] ?? 0);
    $is_current = $_POST['is_current'] === 'true' || $_POST['is_current'] === true;
    
    if (!$file_id) {
        wp_send_json_error(array('message' => 'ID файла не указан'));
    }
    
    update_post_meta($file_id, 'is_current', $is_current);
    
    wp_send_json_success(array('message' => 'Статус файла обновлён'));
}
add_action('wp_ajax_prokb_update_file_status', 'prokb_ajax_update_file_status');
