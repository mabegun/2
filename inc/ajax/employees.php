<?php
/**
 * AJAX - Сотрудники
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить список сотрудников
 */
function prokb_ajax_get_employees() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    $show_archived = $_POST['show_archived'] === 'true';
    
    // Только директор и ГИП могут видеть всех сотрудников
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для просмотра'));
    }
    
    $args = array(
        'meta_key' => 'prokb_role',
    );
    
    $users = get_users($args);
    $result = array();
    
    foreach ($users as $user) {
        $is_archived = get_user_meta($user->ID, 'prokb_is_archived', true);
        
        if (!$show_archived && $is_archived) {
            continue;
        }
        
        $result[] = prokb_get_user_data($user->ID);
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_employees', 'prokb_ajax_get_employees');

/**
 * Получить профиль сотрудника
 */
function prokb_ajax_get_employee_profile() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $employee_id = intval($_POST['employee_id'] ?? 0);
    
    if (!$employee_id) {
        wp_send_json_error(array('message' => 'ID сотрудника не указан'));
    }
    
    $user_data = prokb_get_user_data($employee_id);
    
    // Получаем проекты, где сотрудник назначен на разделы
    $sections = get_posts(array(
        'post_type'      => 'prokb_section',
        'posts_per_page' => -1,
        'meta_key'       => 'assignee_id',
        'meta_value'     => $employee_id,
    ));
    
    $project_ids = array();
    $sections_by_project = array();
    
    foreach ($sections as $section) {
        $project_id = get_post_meta($section->ID, 'project_id', true);
        if ($project_id && !in_array($project_id, $project_ids)) {
            $project_ids[] = $project_id;
            $sections_by_project[$project_id] = array();
        }
        if ($project_id) {
            $sections_by_project[$project_id][] = array(
                'id'     => $section->ID,
                'code'   => get_post_meta($section->ID, 'section_code', true),
                'status' => get_post_meta($section->ID, 'status', true),
            );
        }
    }
    
    $projects = array();
    foreach ($project_ids as $pid) {
        $project = get_post($pid);
        if ($project) {
            $projects[] = array(
                'id'       => $pid,
                'name'     => $project->post_title,
                'status'   => get_post_meta($pid, 'status', true),
                'sections' => $sections_by_project[$pid] ?? array(),
            );
        }
    }
    
    $user_data['projects'] = $projects;
    
    // Получаем комментарии к сотруднику
    $comments = get_posts(array(
        'post_type'      => 'prokb_employee_comment',
        'posts_per_page' => -1,
        'meta_key'       => 'employee_id',
        'meta_value'     => $employee_id,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    
    $user_data['comments'] = array();
    foreach ($comments as $comment) {
        $user_data['comments'][] = array(
            'id'      => $comment->ID,
            'content' => $comment->post_content,
            'author'  => prokb_get_user_data($comment->post_author),
            'date'    => get_the_date('d.m.Y', $comment),
        );
    }
    
    wp_send_json_success($user_data);
}
add_action('wp_ajax_prokb_get_employee_profile', 'prokb_ajax_get_employee_profile');

/**
 * Создать сотрудника
 */
function prokb_ajax_create_employee() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    if ($prokb_role !== 'director') {
        wp_send_json_error(array('message' => 'Нет прав для создания'));
    }
    
    $name = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $position = sanitize_text_field($_POST['position'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $role = sanitize_text_field($_POST['role'] ?? 'employee');
    $competencies = isset($_POST['competencies']) ? json_decode(stripslashes($_POST['competencies']), true) : array();
    
    if (empty($name) || empty($email)) {
        wp_send_json_error(array('message' => 'Обязательные поля не заполнены'));
    }
    
    // Проверяем существование пользователя
    if (email_exists($email)) {
        wp_send_json_error(array('message' => 'Пользователь с таким email уже существует'));
    }
    
    // Создаём пользователя
    $new_user_id = wp_create_user($email, wp_generate_password(12), $email);
    
    if (is_wp_error($new_user_id)) {
        wp_send_json_error(array('message' => 'Ошибка создания пользователя'));
    }
    
    wp_update_user(array(
        'ID'           => $new_user_id,
        'display_name' => $name,
        'first_name'   => $name,
    ));
    
    update_user_meta($new_user_id, 'prokb_position', $position);
    update_user_meta($new_user_id, 'prokb_role', $role);
    update_user_meta($new_user_id, 'prokb_phone', $phone);
    update_user_meta($new_user_id, 'prokb_competencies', json_encode($competencies));
    update_user_meta($new_user_id, 'prokb_avatar_color', sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
    
    wp_send_json_success(array(
        'message'    => 'Сотрудник создан',
        'employee_id' => $new_user_id,
    ));
}
add_action('wp_ajax_prokb_create_employee', 'prokb_ajax_create_employee');

/**
 * Архивировать сотрудника
 */
function prokb_ajax_archive_employee() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    if ($prokb_role !== 'director') {
        wp_send_json_error(array('message' => 'Нет прав для архивации'));
    }
    
    $employee_id = intval($_POST['employee_id'] ?? 0);
    $reason = sanitize_text_field($_POST['reason'] ?? '');
    
    if (!$employee_id) {
        wp_send_json_error(array('message' => 'ID сотрудника не указан'));
    }
    
    update_user_meta($employee_id, 'prokb_is_archived', true);
    update_user_meta($employee_id, 'prokb_archive_reason', $reason);
    
    wp_send_json_success(array('message' => 'Сотрудник архивирован'));
}
add_action('wp_ajax_prokb_archive_employee', 'prokb_ajax_archive_employee');

/**
 * Восстановить сотрудника из архива
 */
function prokb_ajax_restore_employee() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    if ($prokb_role !== 'director') {
        wp_send_json_error(array('message' => 'Нет прав для восстановления'));
    }
    
    $employee_id = intval($_POST['employee_id'] ?? 0);
    
    if (!$employee_id) {
        wp_send_json_error(array('message' => 'ID сотрудника не указан'));
    }
    
    update_user_meta($employee_id, 'prokb_is_archived', false);
    delete_user_meta($employee_id, 'prokb_archive_reason');
    
    wp_send_json_success(array('message' => 'Сотрудник восстановлен'));
}
add_action('wp_ajax_prokb_restore_employee', 'prokb_ajax_restore_employee');

/**
 * Добавить комментарий к сотруднику
 */
function prokb_ajax_add_employee_comment() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для добавления'));
    }
    
    $employee_id = intval($_POST['employee_id'] ?? 0);
    $content = sanitize_textarea_field($_POST['content'] ?? '');
    
    if (!$employee_id || empty($content)) {
        wp_send_json_error(array('message' => 'Обязательные поля не заполнены'));
    }
    
    $comment_id = wp_insert_post(array(
        'post_type'    => 'prokb_employee_comment',
        'post_title'   => 'Комментарий',
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_author'  => $user_id,
    ));
    
    update_post_meta($comment_id, 'employee_id', $employee_id);
    
    wp_send_json_success(array(
        'message'    => 'Комментарий добавлен',
        'comment_id' => $comment_id,
    ));
}
add_action('wp_ajax_prokb_add_employee_comment', 'prokb_ajax_add_employee_comment');
