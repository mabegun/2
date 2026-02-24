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
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    $filter = sanitize_text_field($_POST['filter'] ?? 'all');
    $status_filter = sanitize_text_field($_POST['status'] ?? '');
    $project_filter = intval($_POST['project_id'] ?? 0);
    
    $args = array(
        'post_type'      => 'prokb_task',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    
    // Фильтр по проекту
    if ($project_filter) {
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array('key' => 'task_project', 'value' => $project_filter),
            array('key' => 'project_id', 'value' => $project_filter),
        );
    }
    
    // Фильтр по статусу
    if ($status_filter) {
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array('key' => 'task_status', 'value' => $status_filter),
            array('key' => 'status', 'value' => $status_filter),
        );
    }
    
    $tasks = get_posts($args);
    $result = array();
    
    foreach ($tasks as $task) {
        $assignee_id = get_post_meta($task->ID, 'task_assignee', true) ?: get_post_meta($task->ID, 'assignee_id', true);
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
    $section_id = intval($_POST['section_id'] ?? 0);
    $assignee_id = intval($_POST['assignee_id'] ?? 0);
    $deadline = sanitize_text_field($_POST['deadline'] ?? '');
    $priority = sanitize_text_field($_POST['priority'] ?? 'medium');
    
    if (empty($title)) {
        wp_send_json_error(array('message' => 'Название задачи обязательно'));
    }
    
    // Определяем post_parent (раздел или проект)
    $post_parent = $section_id ?: $project_id;
    
    $task_id = wp_insert_post(array(
        'post_type'    => 'prokb_task',
        'post_title'   => $title,
        'post_content' => $description,
        'post_status'  => 'publish',
        'post_author'  => $user_id,
        'post_parent'  => $post_parent,
    ));
    
    if (is_wp_error($task_id)) {
        wp_send_json_error(array('message' => 'Ошибка создания задачи'));
    }
    
    // Мета-поля (новый формат)
    update_post_meta($task_id, 'task_description', $description);
    update_post_meta($task_id, 'task_project', $project_id);
    update_post_meta($task_id, 'task_assignee', $assignee_id);
    update_post_meta($task_id, 'task_deadline', $deadline);
    update_post_meta($task_id, 'task_priority', $priority);
    update_post_meta($task_id, 'task_status', 'pending');
    
    // Для совместимости
    update_post_meta($task_id, 'project_id', $project_id);
    update_post_meta($task_id, 'assignee_id', $assignee_id);
    update_post_meta($task_id, 'deadline', $deadline);
    update_post_meta($task_id, 'priority', $priority);
    update_post_meta($task_id, 'status', 'pending');
    
    // Уведомление исполнителю
    if ($assignee_id && $assignee_id != $user_id) {
        prokb_create_notification(
            $assignee_id,
            'task_assigned',
            'Вам назначена задача: ' . $title,
            $task_id
        );
    }
    
    wp_send_json_success(array(
        'message' => 'Задача создана',
        'task_id' => $task_id,
    ));
}
add_action('wp_ajax_prokb_create_task', 'prokb_ajax_create_task');

/**
 * Обновить задачу
 */
function prokb_ajax_update_task() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $task_id = intval($_POST['task_id'] ?? 0);
    
    if (!$task_id) {
        wp_send_json_error(array('message' => 'ID задачи не указан'));
    }
    
    $task = get_post($task_id);
    if (!$task || $task->post_type !== 'prokb_task') {
        wp_send_json_error(array('message' => 'Задача не найдена'));
    }
    
    // Обновляем название
    if (isset($_POST['title'])) {
        wp_update_post(array(
            'ID'         => $task_id,
            'post_title' => sanitize_text_field($_POST['title']),
        ));
    }
    
    // Обновляем описание
    if (isset($_POST['description'])) {
        $desc = sanitize_textarea_field($_POST['description']);
        update_post_meta($task_id, 'task_description', $desc);
        update_post_meta($task_id, 'description', $desc);
    }
    
    // Обновляем поля
    $fields = array(
        'status'    => 'task_status',
        'priority'  => 'task_priority',
        'deadline'  => 'task_deadline',
        'assignee'  => 'task_assignee',
    );
    
    foreach ($fields as $field => $new_key) {
        if (isset($_POST[$field])) {
            $value = sanitize_text_field($_POST[$field]);
            update_post_meta($task_id, $new_key, $value);
            update_post_meta($task_id, $field, $value);
        }
    }
    
    wp_send_json_success(array('message' => 'Задача обновлена'));
}
add_action('wp_ajax_prokb_update_task', 'prokb_ajax_update_task');

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
    
    $valid_statuses = array('pending', 'in_progress', 'completed', 'cancelled');
    if (!in_array($status, $valid_statuses)) {
        wp_send_json_error(array('message' => 'Неверный статус'));
    }
    
    // Обновляем оба формата
    update_post_meta($task_id, 'task_status', $status);
    update_post_meta($task_id, 'status', $status);
    
    if ($status === 'completed') {
        update_post_meta($task_id, 'completed_at', current_time('mysql'));
    }
    
    wp_send_json_success(array('message' => 'Статус обновлён'));
}
add_action('wp_ajax_prokb_update_task_status', 'prokb_ajax_update_task_status');

/**
 * Удалить задачу
 */
function prokb_ajax_delete_task() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    $task_id = intval($_POST['task_id'] ?? 0);
    
    if (!$task_id) {
        wp_send_json_error(array('message' => 'ID задачи не указан'));
    }
    
    $task = get_post($task_id);
    
    // Удалять может автор, директор или ГИП
    if ($task->post_author != $user_id && !in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для удаления'));
    }
    
    wp_delete_post($task_id, true);
    
    wp_send_json_success(array('message' => 'Задача удалена'));
}
add_action('wp_ajax_prokb_delete_task', 'prokb_ajax_delete_task');

/**
 * Получить задачи раздела
 */
function prokb_ajax_get_section_tasks() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $section_id = intval($_POST['section_id'] ?? 0);
    
    if (!$section_id) {
        wp_send_json_error(array('message' => 'ID раздела не указан'));
    }
    
    // Ищем задачи как дочерние посты
    $tasks = get_posts(array(
        'post_type'      => 'prokb_task',
        'posts_per_page' => -1,
        'post_parent'    => $section_id,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    
    $result = array();
    foreach ($tasks as $task) {
        $result[] = prokb_format_task($task);
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_section_tasks', 'prokb_ajax_get_section_tasks');
