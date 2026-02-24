<?php
/**
 * AJAX - Проекты
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить список проектов
 */
function prokb_ajax_get_projects() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    $status_filter = sanitize_text_field($_POST['status'] ?? '');
    
    $args = array(
        'post_type'      => 'prokb_project',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    
    // Фильтр по статусу
    if ($status_filter) {
        $args['meta_query'] = array(
            'relation' => 'OR',
            array('key' => 'project_status', 'value' => $status_filter),
            array('key' => 'status', 'value' => $status_filter),
        );
    }
    
    $projects = get_posts($args);
    
    // Сотрудники видят только проекты, где они назначены на разделы
    if ($prokb_role === 'employee') {
        $filtered = array();
        
        foreach ($projects as $project) {
            // Ищем разделы как дочерние посты
            $sections = get_posts(array(
                'post_type'      => 'prokb_section',
                'posts_per_page' => -1,
                'post_parent'    => $project->ID,
            ));
            
            // Fallback: ищем по мета-полю
            if (empty($sections)) {
                $sections = get_posts(array(
                    'post_type'      => 'prokb_section',
                    'posts_per_page' => -1,
                    'meta_key'       => 'project_id',
                    'meta_value'     => $project->ID,
                ));
            }
            
            $assigned = false;
            foreach ($sections as $section) {
                $assignee = get_post_meta($section->ID, 'section_assignee', true) ?: get_post_meta($section->ID, 'assignee_id', true);
                if ($assignee == $user_id) {
                    $assigned = true;
                    break;
                }
            }
            
            if ($assigned) {
                $filtered[] = $project;
            }
        }
        
        $projects = $filtered;
    }
    
    $result = array();
    foreach ($projects as $project) {
        $result[] = prokb_format_project($project);
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_projects', 'prokb_ajax_get_projects');

/**
 * Получить один проект
 */
function prokb_ajax_get_project() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    
    if (!$project_id) {
        wp_send_json_error(array('message' => 'ID проекта не указан'));
    }
    
    $project = get_post($project_id);
    
    if (!$project || $project->post_type !== 'prokb_project') {
        wp_send_json_error(array('message' => 'Проект не найден'));
    }
    
    wp_send_json_success(prokb_format_project($project, true));
}
add_action('wp_ajax_prokb_get_project', 'prokb_ajax_get_project');

/**
 * Создать проект
 */
function prokb_ajax_create_project() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для создания проекта'));
    }
    
    $name = sanitize_text_field($_POST['name'] ?? '');
    $code = sanitize_text_field($_POST['code'] ?? '');
    $address = sanitize_text_field($_POST['address'] ?? '');
    $type = sanitize_text_field($_POST['type'] ?? 'construction');
    $deadline = sanitize_text_field($_POST['deadline'] ?? '');
    $expertise = sanitize_text_field($_POST['expertise'] ?? 'none');
    $gip_id = intval($_POST['gip_id'] ?? 0);
    $client = sanitize_text_field($_POST['client'] ?? '');
    $description = sanitize_textarea_field($_POST['description'] ?? '');
    $sections = isset($_POST['sections']) ? json_decode(stripslashes($_POST['sections']), true) : array();
    
    if (empty($name)) {
        wp_send_json_error(array('message' => 'Название проекта обязательно'));
    }
    
    $project_id = wp_insert_post(array(
        'post_type'    => 'prokb_project',
        'post_title'   => $name,
        'post_status'  => 'publish',
        'post_author'  => $user_id,
    ));
    
    if (is_wp_error($project_id)) {
        wp_send_json_error(array('message' => 'Ошибка создания проекта'));
    }
    
    // Мета-поля проекта (новый формат) - используем статусы совместимые с JS
    update_post_meta($project_id, 'project_code', $code);
    update_post_meta($project_id, 'project_address', $address);
    update_post_meta($project_id, 'project_type', $type);
    update_post_meta($project_id, 'project_deadline', $deadline);
    update_post_meta($project_id, 'project_client', $client);
    update_post_meta($project_id, 'project_description', $description);
    update_post_meta($project_id, 'project_status', 'in_work'); // Совместимо с JS
    if ($gip_id) {
        update_post_meta($project_id, 'project_gip', $gip_id);
    }
    // Для совместимости со старым форматом
    update_post_meta($project_id, 'code', $code);
    update_post_meta($project_id, 'address', $address);
    update_post_meta($project_id, 'type', $type);
    update_post_meta($project_id, 'deadline', $deadline);
    update_post_meta($project_id, 'status', 'in_work'); // Совместимо с JS
    if ($gip_id) {
        update_post_meta($project_id, 'gip_id', $gip_id);
    }
    
    // Создаём разделы как дочерние посты
    if (!empty($sections)) {
        foreach ($sections as $section_code) {
            $section_id = wp_insert_post(array(
                'post_type'    => 'prokb_section',
                'post_title'   => $section_code . ' - ' . prokb_get_section_description($section_code),
                'post_status'  => 'publish',
                'post_parent'  => $project_id,
            ));
            
            update_post_meta($section_id, 'section_code', $section_code);
            update_post_meta($section_id, 'section_status', 'not_started');
            update_post_meta($section_id, 'section_progress', 0);
            // Для совместимости
            update_post_meta($section_id, 'project_id', $project_id);
            update_post_meta($section_id, 'status', 'not_started');
        }
    }
    
    wp_send_json_success(array(
        'message'    => 'Проект создан',
        'project_id' => $project_id,
    ));
}
add_action('wp_ajax_prokb_create_project', 'prokb_ajax_create_project');

/**
 * Обновить проект
 */
function prokb_ajax_update_project() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для редактирования проекта'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    
    if (!$project_id) {
        wp_send_json_error(array('message' => 'ID проекта не указан'));
    }
    
    $project = get_post($project_id);
    
    if (!$project || $project->post_type !== 'prokb_project') {
        wp_send_json_error(array('message' => 'Проект не найден'));
    }
    
    // Обновляем название
    if (isset($_POST['name'])) {
        wp_update_post(array(
            'ID'         => $project_id,
            'post_title' => sanitize_text_field($_POST['name']),
        ));
    }
    
    // Обновляем поля (оба формата для совместимости)
    $field_mapping = array(
        'code'     => array('project_code', 'code'),
        'address'  => array('project_address', 'address'),
        'type'     => array('project_type', 'type'),
        'deadline' => array('project_deadline', 'deadline'),
        'client'   => array('project_client', 'client'),
        'description' => array('project_description', 'description'),
        'status'   => array('project_status', 'status'),
        'gip_id'   => array('project_gip', 'gip_id'),
    );
    
    foreach ($field_mapping as $field => $keys) {
        if (isset($_POST[$field])) {
            $value = sanitize_text_field($_POST[$field]);
            update_post_meta($project_id, $keys[0], $value);
            update_post_meta($project_id, $keys[1], $value);
        }
    }
    
    // Обновляем разделы
    if (isset($_POST['sections'])) {
        $new_sections = json_decode(stripslashes($_POST['sections']), true);
        
        // Получаем текущие разделы (дочерние посты)
        $current_sections = get_posts(array(
            'post_type'      => 'prokb_section',
            'posts_per_page' => -1,
            'post_parent'    => $project_id,
        ));
        
        $current_codes = array();
        foreach ($current_sections as $section) {
            $code = get_post_meta($section->ID, 'section_code', true);
            $current_codes[$code] = $section->ID;
        }
        
        // Добавляем новые разделы
        foreach ($new_sections as $code) {
            if (!isset($current_codes[$code])) {
                $section_id = wp_insert_post(array(
                    'post_type'    => 'prokb_section',
                    'post_title'   => $code . ' - ' . prokb_get_section_description($code),
                    'post_status'  => 'publish',
                    'post_parent'  => $project_id,
                ));
                
                update_post_meta($section_id, 'section_code', $code);
                update_post_meta($section_id, 'section_status', 'not_started');
                update_post_meta($section_id, 'project_id', $project_id);
            }
        }
    }
    
    wp_send_json_success(array('message' => 'Проект обновлён'));
}
add_action('wp_ajax_prokb_update_project', 'prokb_ajax_update_project');

/**
 * Архивировать проект
 */
function prokb_ajax_archive_project() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для архивации проекта'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    $reason = sanitize_text_field($_POST['reason'] ?? '');
    
    if (!$project_id) {
        wp_send_json_error(array('message' => 'ID проекта не указан'));
    }
    
    // Обновляем оба формата
    update_post_meta($project_id, 'project_status', 'archived');
    update_post_meta($project_id, 'status', 'archived');
    update_post_meta($project_id, 'archived_at', current_time('mysql'));
    update_post_meta($project_id, 'archive_reason', $reason);
    
    wp_send_json_success(array('message' => 'Проект перемещён в архив'));
}
add_action('wp_ajax_prokb_archive_project', 'prokb_ajax_archive_project');

/**
 * Восстановить проект из архива
 */
function prokb_ajax_restore_project() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для восстановления проекта'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    
    if (!$project_id) {
        wp_send_json_error(array('message' => 'ID проекта не указан'));
    }
    
    // Обновляем оба формата
    update_post_meta($project_id, 'project_status', 'in_work');
    update_post_meta($project_id, 'status', 'in_work');
    delete_post_meta($project_id, 'archived_at');
    delete_post_meta($project_id, 'archive_reason');
    
    wp_send_json_success(array('message' => 'Проект восстановлен'));
}
add_action('wp_ajax_prokb_restore_project', 'prokb_ajax_restore_project');

/**
 * Удалить проект (полностью)
 */
function prokb_ajax_delete_project() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if ($prokb_role !== 'director') {
        wp_send_json_error(array('message' => 'Нет прав для удаления проекта'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    
    if (!$project_id) {
        wp_send_json_error(array('message' => 'ID проекта не указан'));
    }
    
    // Удаляем связанные разделы (дочерние посты)
    $sections = get_posts(array(
        'post_type'      => 'prokb_section',
        'posts_per_page' => -1,
        'post_parent'    => $project_id,
    ));
    
    // Fallback: разделы по мета-полю
    $meta_sections = get_posts(array(
        'post_type'      => 'prokb_section',
        'posts_per_page' => -1,
        'meta_key'       => 'project_id',
        'meta_value'     => $project_id,
    ));
    
    $all_sections = array_merge($sections, $meta_sections);
    $deleted_ids = array();
    
    foreach ($all_sections as $section) {
        if (!in_array($section->ID, $deleted_ids)) {
            wp_delete_post($section->ID, true);
            $deleted_ids[] = $section->ID;
        }
    }
    
    // Удаляем связанные контакты
    $contacts = get_posts(array(
        'post_type'      => 'prokb_contact',
        'posts_per_page' => -1,
        'meta_key'       => 'contact_project',
        'meta_value'     => $project_id,
    ));
    
    foreach ($contacts as $contact) {
        wp_delete_post($contact->ID, true);
    }
    
    // Удаляем сам проект
    wp_delete_post($project_id, true);
    
    wp_send_json_success(array('message' => 'Проект удалён'));
}
add_action('wp_ajax_prokb_delete_project', 'prokb_ajax_delete_project');
