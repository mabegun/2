<?php
/**
 * AJAX - Задачи
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить список задач
 */
function prokb_ajax_get_tasks() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    $filter = sanitize_text_field($_POST['filter'] ?? 'all');
    $status_filter = sanitize_text_field($_POST['status'] ?? '');
    
    $args = array(
        'post_type'      => 'prokb_task',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    
    // Фильтр по статусу
    if ($status_filter) {
        $args['meta_query'] = array(
            array(
                'key'   => 'status',
                'value' => $status_filter,
            ),
        );
    }
    
    $tasks = get_posts($args);
    $result = array();
    
    foreach ($tasks as $task) {
        $assignee_id = get_post_meta($task->ID, 'assignee_id', true);
        $author_id = $task->post_author;
        
        // Применяем фильтр
        if ($filter === 'my' && $assignee_id != $user_id) {
            continue;
        }
        if ($filter === 'created' && $author_id != $user_id) {
            continue;
        }
        
        $result[] = prokb_format_task($task);
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_tasks', 'prokb_ajax_get_tasks');

/**
 * Создать задачу
 */
function prokb_ajax_create_task() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    
    $title = sanitize_text_field($_POST['title'] ?? '');
    $description = sanitize_textarea_field($_POST['description'] ?? '');
    $project_id = intval($_POST['project_id'] ?? 0);
    $assignee_id = intval($_POST['assignee_id'] ?? 0);
    $deadline = sanitize_text_field($_POST['deadline'] ?? '');
    $priority = sanitize_text_field($_POST['priority'] ?? 'medium');
    
    if (empty($title)) {
        wp_send_json_error(array('message' => 'Название задачи обязательно'));
    }
    
    $task_id = wp_insert_post(array(
        'post_type'    => 'prokb_task',
        'post_title'   => $title,
        'post_content' => $description,
        'post_status'  => 'publish',
        'post_author'  => $user_id,
    ));
    
    if (is_wp_error($task_id)) {
        wp_send_json_error(array('message' => 'Ошибка создания задачи'));
    }
    
    update_post_meta($task_id, 'project_id', $project_id);
    update_post_meta($task_id, 'assignee_id', $assignee_id);
    update_post_meta($task_id, 'deadline', $deadline);
    update_post_meta($task_id, 'priority', $priority);
    update_post_meta($task_id, 'status', 'not_started');
    
    // Создаём уведомление исполнителю
    if ($assignee_id) {
        prokb_create_notification($assignee_id, 'task', 'Вам назначена новая задача: ' . $title, $task_id);
    }
    
    wp_send_json_success(array(
        'message' => 'Задача создана',
        'task_id' => $task_id,
    ));
}
add_action('wp_ajax_prokb_create_task', 'prokb_ajax_create_task');

/**
 * Обновить статус задачи
 */
function prokb_ajax_update_task_status() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $task_id = intval($_POST['task_id'] ?? 0);
    $status = sanitize_text_field($_POST['status'] ?? '');
    
    if (!$task_id || !$status) {
        wp_send_json_error(array('message' => 'Параметры не указаны'));
    }
    
    $valid_statuses = array('not_started', 'in_progress', 'completed');
    if (!in_array($status, $valid_statuses)) {
        wp_send_json_error(array('message' => 'Неверный статус'));
    }
    
    update_post_meta($task_id, 'status', $status);
    
    wp_send_json_success(array('message' => 'Статус обновлён'));
}
add_action('wp_ajax_prokb_update_task_status', 'prokb_ajax_update_task_status');
