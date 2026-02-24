<?php
/**
 * AJAX - Дашборд
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить статистику для дашборда
 */
function prokb_ajax_get_dashboard_stats() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    // Общее количество проектов
    $projects = get_posts(array(
        'post_type'      => 'prokb_project',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ));
    $total_projects = count($projects);
    
    // Проекты в работе
    $in_progress = 0;
    $total_sections = 0;
    $completed_sections = 0;
    
    foreach ($projects as $project) {
        $status = get_post_meta($project->ID, 'project_status', true) ?: get_post_meta($project->ID, 'status', true);
        if ($status === 'active' || $status === 'in_work') {
            $in_progress++;
        }
        
        // Считаем разделы (дочерние посты)
        $sections = get_posts(array(
            'post_type'      => 'prokb_section',
            'posts_per_page' => -1,
            'post_parent'    => $project->ID,
        ));
        
        // Fallback: по мета-полю
        if (empty($sections)) {
            $sections = get_posts(array(
                'post_type'      => 'prokb_section',
                'posts_per_page' => -1,
                'meta_key'       => 'project_id',
                'meta_value'     => $project->ID,
            ));
        }
        
        $total_sections += count($sections);
        
        foreach ($sections as $section) {
            $section_status = get_post_meta($section->ID, 'section_status', true) ?: get_post_meta($section->ID, 'status', true);
            if ($section_status === 'completed') {
                $completed_sections++;
            }
        }
    }
    
    // Количество сотрудников (с WordPress ролями)
    $director_users = get_users(array('role' => 'prokb_director'));
    $gip_users = get_users(array('role' => 'prokb_gip'));
    $employee_users = get_users(array('role' => 'prokb_employee'));
    
    $total_employees = count($director_users) + count($gip_users) + count($employee_users);
    
    // Fallback: считаем по мета-полю
    if ($total_employees === 0) {
        $meta_users = get_users(array('meta_key' => 'prokb_role'));
        foreach ($meta_users as $employee) {
            if (!get_user_meta($employee->ID, 'prokb_is_archived', true)) {
                $total_employees++;
            }
        }
    }
    
    // Мои задачи
    $my_tasks = 0;
    $tasks = get_posts(array(
        'post_type'      => 'prokb_task',
        'posts_per_page' => -1,
    ));
    
    foreach ($tasks as $task) {
        $assignee = get_post_meta($task->ID, 'task_assignee', true) ?: get_post_meta($task->ID, 'assignee_id', true);
        $status = get_post_meta($task->ID, 'task_status', true) ?: get_post_meta($task->ID, 'status', true);
        
        if ($assignee == $user_id && $status !== 'completed') {
            $my_tasks++;
        }
    }
    
    // Непрочитанные уведомления
    $unread_notifications = 0;
    $notifications = get_posts(array(
        'post_type'      => 'prokb_notification',
        'posts_per_page' => -1,
        'author'         => $user_id,
    ));
    
    foreach ($notifications as $notif) {
        $is_read = get_post_meta($notif->ID, 'notification_read', true) ?: get_post_meta($notif->ID, 'is_read', true);
        if (!$is_read) {
            $unread_notifications++;
        }
    }
    
    wp_send_json_success(array(
        'total_projects'       => $total_projects,
        'in_progress'          => $in_progress,
        'total_sections'       => $total_sections,
        'completed_sections'   => $completed_sections,
        'total_employees'      => $total_employees,
        'my_tasks'             => $my_tasks,
        'unread_notifications' => $unread_notifications,
    ));
}
add_action('wp_ajax_prokb_get_dashboard_stats', 'prokb_ajax_get_dashboard_stats');

/**
 * Получить последние активности
 */
function prokb_ajax_get_recent_activity() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    $activities = array();
    
    // Последние проекты
    $projects = get_posts(array(
        'post_type'      => 'prokb_project',
        'posts_per_page' => 5,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    
    foreach ($projects as $project) {
        $activities[] = array(
            'type'    => 'project',
            'title'   => $project->post_title,
            'date'    => get_the_date('d.m.Y', $project),
            'id'      => $project->ID,
        );
    }
    
    // Последние задачи пользователя
    $tasks = get_posts(array(
        'post_type'      => 'prokb_task',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    
    foreach ($tasks as $task) {
        $assignee = get_post_meta($task->ID, 'task_assignee', true) ?: get_post_meta($task->ID, 'assignee_id', true);
        
        // Показываем только назначенные пользователю задачи (для сотрудников)
        if ($prokb_role === 'employee' && $assignee != $user_id) {
            continue;
        }
        
        $activities[] = array(
            'type'    => 'task',
            'title'   => $task->post_title,
            'date'    => get_the_date('d.m.Y', $task),
            'id'      => $task->ID,
            'status'  => get_post_meta($task->ID, 'task_status', true) ?: get_post_meta($task->ID, 'status', true),
        );
    }
    
    // Сортируем по дате (упрощённо - по ID)
    usort($activities, function($a, $b) {
        return $b['id'] - $a['id'];
    });
    
    wp_send_json_success(array_slice($activities, 0, 10));
}
add_action('wp_ajax_prokb_get_recent_activity', 'prokb_ajax_get_recent_activity');
