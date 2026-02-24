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
    
    $valid_statuses = array('pending', 'not_started', 'in_progress', 'completed', 'revision');
    if (!in_array($status, $valid_statuses)) {
        wp_send_json_error(array('message' => 'Неверный статус'));
    }
    
    // Обновляем оба формата
    update_post_meta($section_id, 'section_status', $status);
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
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для назначения'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    $assignee_id = intval($_POST['assignee_id'] ?? 0);
    
    if (!$section_id) {
        wp_send_json_error(array('message' => 'ID раздела не указан'));
    }
    
    // Обновляем оба формата
    update_post_meta($section_id, 'section_assignee', $assignee_id);
    update_post_meta($section_id, 'assignee_id', $assignee_id);
    
    // Уведомление для исполнителя
    if ($assignee_id) {
        $section = get_post($section_id);
        $section_code = get_post_meta($section_id, 'section_code', true);
        $project_id = $section->post_parent ?: get_post_meta($section_id, 'project_id', true);
        $project = $project_id ? get_post($project_id) : null;
        
        prokb_create_notification(
            $assignee_id,
            'section_assigned',
            'Вам назначен раздел ' . $section_code . ($project ? ' в проекте "' . $project->post_title . '"' : ''),
            $section_id
        );
    }
    
    wp_send_json_success(array('message' => 'Исполнитель назначен'));
}
add_action('wp_ajax_prokb_assign_section', 'prokb_ajax_assign_section');

/**
 * Обновить дедлайн раздела
 */
function prokb_ajax_update_section_deadline() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    $deadline = sanitize_text_field($_POST['deadline'] ?? '');
    
    if (!$section_id) {
        wp_send_json_error(array('message' => 'ID раздела не указан'));
    }
    
    update_post_meta($section_id, 'section_deadline', $deadline);
    update_post_meta($section_id, 'deadline', $deadline);
    
    wp_send_json_success(array('message' => 'Дедлайн обновлён'));
}
add_action('wp_ajax_prokb_update_section_deadline', 'prokb_ajax_update_section_deadline');

/**
 * Обновить прогресс раздела
 */
function prokb_ajax_update_section_progress() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    $progress = intval($_POST['progress'] ?? 0);
    
    if (!$section_id) {
        wp_send_json_error(array('message' => 'ID раздела не указан'));
    }
    
    $progress = max(0, min(100, $progress));
    
    update_post_meta($section_id, 'section_progress', $progress);
    update_post_meta($section_id, 'progress', $progress);
    
    wp_send_json_success(array('message' => 'Прогресс обновлён', 'progress' => $progress));
}
add_action('wp_ajax_prokb_update_section_progress', 'prokb_ajax_update_section_progress');

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

/**
 * Получить разделы проекта
 */
function prokb_ajax_get_project_sections() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    
    if (!$project_id) {
        wp_send_json_error(array('message' => 'ID проекта не указан'));
    }
    
    // Ищем разделы как дочерние посты
    $sections = get_posts(array(
        'post_type'      => 'prokb_section',
        'posts_per_page' => -1,
        'post_parent'    => $project_id,
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ));
    
    // Fallback: ищем по мета-полю
    if (empty($sections)) {
        $sections = get_posts(array(
            'post_type'      => 'prokb_section',
            'posts_per_page' => -1,
            'meta_key'       => 'project_id',
            'meta_value'     => $project_id,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        ));
    }
    
    $result = array();
    foreach ($sections as $section) {
        $assignee_id = get_post_meta($section->ID, 'section_assignee', true) ?: get_post_meta($section->ID, 'assignee_id', true);
        $status = get_post_meta($section->ID, 'section_status', true) ?: get_post_meta($section->ID, 'status', true);
        
        $result[] = array(
            'id'              => $section->ID,
            'code'            => get_post_meta($section->ID, 'section_code', true),
            'title'           => $section->post_title,
            'description'     => get_post_meta($section->ID, 'section_description', true) ?: get_post_meta($section->ID, 'description', true),
            'status'          => $status ?: 'pending',
            'assignee_id'     => $assignee_id,
            'assignee'        => $assignee_id ? prokb_get_user_data($assignee_id) : null,
            'deadline'        => get_post_meta($section->ID, 'section_deadline', true) ?: get_post_meta($section->ID, 'deadline', true),
            'progress'        => get_post_meta($section->ID, 'section_progress', true) ?: get_post_meta($section->ID, 'progress', true) ?: 0,
            'expertise_status'=> get_post_meta($section->ID, 'expertise_status', true),
            'started_at'      => get_post_meta($section->ID, 'started_at', true),
            'completed_at'    => get_post_meta($section->ID, 'completed_at', true),
        );
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_project_sections', 'prokb_ajax_get_project_sections');

/**
 * Создать раздел вручную
 */
function prokb_ajax_create_section() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для создания раздела'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    $section_code = sanitize_text_field($_POST['code'] ?? '');
    $description = sanitize_textarea_field($_POST['description'] ?? '');
    
    if (!$project_id || empty($section_code)) {
        wp_send_json_error(array('message' => 'Обязательные поля не заполнены'));
    }
    
    $section_id = wp_insert_post(array(
        'post_type'    => 'prokb_section',
        'post_title'   => $section_code . ' - ' . ($description ?: prokb_get_section_description($section_code)),
        'post_status'  => 'publish',
        'post_parent'  => $project_id,
    ));
    
    if (is_wp_error($section_id)) {
        wp_send_json_error(array('message' => 'Ошибка создания раздела'));
    }
    
    update_post_meta($section_id, 'section_code', $section_code);
    update_post_meta($section_id, 'section_status', 'pending');
    update_post_meta($section_id, 'section_progress', 0);
    update_post_meta($section_id, 'section_description', $description);
    update_post_meta($section_id, 'project_id', $project_id);
    update_post_meta($section_id, 'status', 'pending');
    
    wp_send_json_success(array(
        'message'    => 'Раздел создан',
        'section_id' => $section_id,
    ));
}
add_action('wp_ajax_prokb_create_section', 'prokb_ajax_create_section');

/**
 * Удалить раздел
 */
function prokb_ajax_delete_section() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для удаления раздела'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    
    if (!$section_id) {
        wp_send_json_error(array('message' => 'ID раздела не указан'));
    }
    
    wp_delete_post($section_id, true);
    
    wp_send_json_success(array('message' => 'Раздел удалён'));
}
add_action('wp_ajax_prokb_delete_section', 'prokb_ajax_delete_section');
