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
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
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
        $status = get_post_meta($project->ID, 'status', true);
        if ($status === 'in_work') {
            $in_progress++;
        }
        
        // Считаем разделы
        $sections = get_posts(array(
            'post_type'      => 'prokb_section',
            'posts_per_page' => -1,
            'meta_key'       => 'project_id',
            'meta_value'     => $project->ID,
        ));
        
        $total_sections += count($sections);
        
        foreach ($sections as $section) {
            $section_status = get_post_meta($section->ID, 'status', true);
            if ($section_status === 'completed') {
                $completed_sections++;
            }
        }
    }
    
    // Количество сотрудников
    $employees = get_users(array(
        'meta_key' => 'prokb_role',
    ));
    $total_employees = 0;
    foreach ($employees as $employee) {
        if (!get_user_meta($employee->ID, 'prokb_is_archived', true)) {
            $total_employees++;
        }
    }
    
    // Мои задачи
    $my_tasks = 0;
    if ($prokb_role === 'employee') {
        $tasks = get_posts(array(
            'post_type'      => 'prokb_task',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'   => 'assignee_id',
                    'value' => $user_id,
                ),
                array(
                    'key'     => 'status',
                    'value'   => 'completed',
                    'compare' => '!=',
                ),
            ),
        ));
        $my_tasks = count($tasks);
    }
    
    wp_send_json_success(array(
        'total_projects'     => $total_projects,
        'in_progress'        => $in_progress,
        'total_sections'     => $total_sections,
        'completed_sections' => $completed_sections,
        'total_employees'    => $total_employees,
        'my_tasks'           => $my_tasks,
    ));
}
add_action('wp_ajax_prokb_get_dashboard_stats', 'prokb_ajax_get_dashboard_stats');
