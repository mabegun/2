<?php
/**
 * AJAX - Разделы проектов
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Обновить статус раздела
 */
function prokb_ajax_update_section_status() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    $status = sanitize_text_field($_POST['status'] ?? '');
    
    if (!$section_id || !$status) {
        wp_send_json_error(array('message' => 'Параметры не указаны'));
    }
    
    $valid_statuses = array('not_started', 'in_progress', 'completed', 'revision');
    if (!in_array($status, $valid_statuses)) {
        wp_send_json_error(array('message' => 'Неверный статус'));
    }
    
    update_post_meta($section_id, 'status', $status);
    
    if ($status === 'in_progress') {
        update_post_meta($section_id, 'started_at', current_time('mysql'));
    } elseif ($status === 'completed') {
        update_post_meta($section_id, 'completed_at', current_time('mysql'));
    }
    
    wp_send_json_success(array('message' => 'Статус обновлён'));
}
add_action('wp_ajax_prokb_update_section_status', 'prokb_ajax_update_section_status');

/**
 * Назначить исполнителя на раздел
 */
function prokb_ajax_assign_section() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для назначения'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    $assignee_id = intval($_POST['assignee_id'] ?? 0);
    
    if (!$section_id) {
        wp_send_json_error(array('message' => 'ID раздела не указан'));
    }
    
    update_post_meta($section_id, 'assignee_id', $assignee_id);
    
    wp_send_json_success(array('message' => 'Исполнитель назначен'));
}
add_action('wp_ajax_prokb_assign_section', 'prokb_ajax_assign_section');

/**
 * Обновить статус экспертизы раздела
 */
function prokb_ajax_update_section_expertise_status() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    $status = sanitize_text_field($_POST['status'] ?? '');
    
    if (!$section_id || !$status) {
        wp_send_json_error(array('message' => 'Параметры не указаны'));
    }
    
    $valid_statuses = array('uploaded_for_review', 'remarks_received', 'remarks_in_progress', 'accepted_by_expert');
    if (!in_array($status, $valid_statuses)) {
        wp_send_json_error(array('message' => 'Неверный статус'));
    }
    
    update_post_meta($section_id, 'expertise_status', $status);
    
    wp_send_json_success(array('message' => 'Статус экспертизы обновлён'));
}
add_action('wp_ajax_prokb_update_section_expertise_status', 'prokb_ajax_update_section_expertise_status');
