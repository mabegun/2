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
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    $show_archived = isset($_POST['show_archived']) && $_POST['show_archived'] === 'true';
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для просмотра'));
    }
    
    $users = get_users(array('role__in' => array('prokb_director', 'prokb_gip', 'prokb_employee')));
    $meta_users = get_users(array('meta_key' => 'prokb_role'));
    $all_ids = array_unique(array_merge(
        array_map(function($u) { return $u->ID; }, $users),
        array_map(function($u) { return $u->ID; }, $meta_users)
    ));
    
    $result = array();
    foreach ($all_ids as $uid) {
        $user = get_user_by('ID', $uid);
        if (!$user) continue;
        
        $is_archived = get_user_meta($uid, 'prokb_is_archived', true);
        if (!$show_archived && $is_archived) continue;
        
        $result[] = prokb_get_user_data($uid);
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
    if (!$user_data) {
        wp_send_json_error(array('message' => 'Пользователь не найден'));
    }
    
    // Разделы сотрудника
    $sections = get_posts(array(
        'post_type' => 'prokb_section',
        'posts_per_page' => -1,
        'meta_key' => 'section_assignee',
        'meta_value' => $employee_id,
    ));
    
    if (empty($sections)) {
        $sections = get_posts(array(
            'post_type' => 'prokb_section',
            'posts_per_page' => -1,
            'meta_key' => 'assignee_id',
            'meta_value' => $employee_id,
        ));
    }
    
    $project_ids = array();
    $sections_by_project = array();
    
    foreach ($sections as $section) {
        $project_id = $section->post_parent ?: get_post_meta($section->ID, 'project_id', true);
        if ($project_id && !in_array($project_id, $project_ids)) {
            $project_ids[] = $project_id;
            $sections_by_project[$project_id] = array();
        }
        if ($project_id) {
            $sections_by_project[$project_id][] = array(
                'id' => $section->ID,
                'code' => get_post_meta($section->ID, 'section_code', true),
                'status' => get_post_meta($section->ID, 'section_status', true) ?: get_post_meta($section->ID, 'status', true),
            );
        }
    }
    
    $projects = array();
    foreach ($project_ids as $pid) {
        $project = get_post($pid);
        if ($project) {
            $projects[] = array(
                'id' => $pid,
                'name' => $project->post_title,
                'status' => get_post_meta($pid, 'project_status', true) ?: get_post_meta($pid, 'status', true),
                'sections' => $sections_by_project[$pid] ?? array(),
            );
        }
    }
    
    // Комментарии
    $comments = get_posts(array(
        'post_type' => 'prokb_employee_comment',
        'posts_per_page' => -1,
        'meta_key' => 'employee_id',
        'meta_value' => $employee_id,
        'orderby' => 'date',
        'order' => 'DESC',
    ));
    
    $comments_data = array();
    foreach ($comments as $comment) {
        $comments_data[] = array(
            'id' => $comment->ID,
            'content' => $comment->post_content,
            'author' => prokb_get_user_data($comment->post_author),
            'date' => get_the_date('d.m.Y', $comment),
        );
    }
    
    wp_send_json_success(array(
        'employee' => $user_data,
        'projects' => $projects,
        'comments' => $comments_data,
    ));
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
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if ($prokb_role !== 'director') {
        wp_send_json_error(array('message' => 'Только директор может добавлять сотрудников.'));
    }
    
    $name = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $position = sanitize_text_field($_POST['position'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $role = sanitize_text_field($_POST['role'] ?? 'employee');
    $comment = sanitize_textarea_field($_POST['comment'] ?? '');
    $competencies = isset($_POST['competencies']) ? json_decode(stripslashes($_POST['competencies']), true) : array();
    
    if (empty($name) || empty($email)) {
        wp_send_json_error(array('message' => 'Обязательные поля не заполнены'));
    }
    
    if (!in_array($role, array('director', 'gip', 'employee'))) {
        $role = 'employee';
    }
    
    if (email_exists($email)) {
        wp_send_json_error(array('message' => 'Пользователь с таким email уже существует'));
    }
    
    $password = wp_generate_password(12, true);
    $new_user_id = wp_create_user($email, $password, $email);
    
    if (is_wp_error($new_user_id)) {
        wp_send_json_error(array('message' => 'Ошибка создания: ' . $new_user_id->get_error_message()));
    }
    
    wp_update_user(array(
        'ID' => $new_user_id,
        'display_name' => $name,
        'first_name' => $name,
    ));
    
    $new_user = get_user_by('ID', $new_user_id);
    switch ($role) {
        case 'director': $new_user->set_role('prokb_director'); break;
        case 'gip': $new_user->set_role('prokb_gip'); break;
        default: $new_user->set_role('prokb_employee'); break;
    }
    
    update_user_meta($new_user_id, 'prokb_position', $position);
    update_user_meta($new_user_id, 'prokb_role', $role);
    update_user_meta($new_user_id, 'prokb_phone', $phone);
    update_user_meta($new_user_id, 'prokb_comment', $comment);
    update_user_meta($new_user_id, 'prokb_competencies', json_encode($competencies));
    update_user_meta($new_user_id, 'prokb_avatar_color', sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
    
    // Обработка аватарки
    if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload = wp_handle_upload($_FILES['avatar'], array('test_form' => false));
        if (!isset($upload['error'])) {
            update_user_meta($new_user_id, 'prokb_avatar_url', $upload['url']);
        }
    }
    
    wp_send_json_success(array(
        'message' => 'Сотрудник создан',
        'employee_id' => $new_user_id,
        'password' => $password,
        'employee' => prokb_get_user_data($new_user_id),
    ));
}
add_action('wp_ajax_prokb_create_employee', 'prokb_ajax_create_employee');

/**
 * Обновить свой профиль
 */
function prokb_ajax_update_profile() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $name = sanitize_text_field($_POST['name'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $position = sanitize_text_field($_POST['position'] ?? '');
    
    if (!empty($name)) {
        wp_update_user(array('ID' => $user_id, 'display_name' => $name, 'first_name' => $name));
    }
    
    update_user_meta($user_id, 'prokb_phone', $phone);
    update_user_meta($user_id, 'prokb_position', $position);
    
    // Аватарка
    if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload = wp_handle_upload($_FILES['avatar'], array('test_form' => false));
        if (!isset($upload['error'])) {
            update_user_meta($user_id, 'prokb_avatar_url', $upload['url']);
        }
    }
    
    wp_send_json_success(array(
        'message' => 'Профиль обновлён',
        'user' => prokb_get_user_data($user_id),
    ));
}
add_action('wp_ajax_prokb_update_profile', 'prokb_ajax_update_profile');

/**
 * Обновить сотрудника (директором)
 */
function prokb_ajax_update_employee() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $current_user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($current_user_id) : get_user_meta($current_user_id, 'prokb_role', true);
    
    if ($prokb_role !== 'director') {
        wp_send_json_error(array('message' => 'Нет прав для редактирования'));
    }
    
    $employee_id = intval($_POST['employee_id'] ?? 0);
    if (!$employee_id) {
        wp_send_json_error(array('message' => 'ID сотрудника не указан'));
    }
    
    $name = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $position = sanitize_text_field($_POST['position'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $role = sanitize_text_field($_POST['role'] ?? '');
    $comment = sanitize_textarea_field($_POST['comment'] ?? '');
    $competencies = isset($_POST['competencies']) ? json_decode(stripslashes($_POST['competencies']), true) : array();
    
    $user = get_user_by('ID', $employee_id);
    if (!$user) {
        wp_send_json_error(array('message' => 'Пользователь не найден'));
    }
    
    $update_data = array('ID' => $employee_id);
    if (!empty($name)) {
        $update_data['display_name'] = $name;
        $update_data['first_name'] = $name;
    }
    if (!empty($email) && $email !== $user->user_email) {
        if (email_exists($email) && email_exists($email) != $employee_id) {
            wp_send_json_error(array('message' => 'Email уже используется'));
        }
        $update_data['user_email'] = $email;
    }
    
    wp_update_user($update_data);
    
    if (!empty($role) && in_array($role, array('director', 'gip', 'employee'))) {
        $user = get_user_by('ID', $employee_id);
        switch ($role) {
            case 'director': $user->set_role('prokb_director'); break;
            case 'gip': $user->set_role('prokb_gip'); break;
            case 'employee': $user->set_role('prokb_employee'); break;
        }
        update_user_meta($employee_id, 'prokb_role', $role);
    }
    
    update_user_meta($employee_id, 'prokb_position', $position);
    update_user_meta($employee_id, 'prokb_phone', $phone);
    update_user_meta($employee_id, 'prokb_comment', $comment);
    update_user_meta($employee_id, 'prokb_competencies', json_encode($competencies));
    
    // Аватарка
    if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload = wp_handle_upload($_FILES['avatar'], array('test_form' => false));
        if (!isset($upload['error'])) {
            update_user_meta($employee_id, 'prokb_avatar_url', $upload['url']);
        }
    }
    
    wp_send_json_success(array(
        'message' => 'Данные обновлены',
        'user' => prokb_get_user_data($employee_id),
    ));
}
add_action('wp_ajax_prokb_update_employee', 'prokb_ajax_update_employee');

/**
 * Архивировать сотрудника
 */
function prokb_ajax_archive_employee() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if ($prokb_role !== 'director') {
        wp_send_json_error(array('message' => 'Нет прав'));
    }
    
    $employee_id = intval($_POST['employee_id'] ?? 0);
    $reason = sanitize_text_field($_POST['reason'] ?? '');
    
    if (!$employee_id) {
        wp_send_json_error(array('message' => 'ID не указан'));
    }
    
    update_user_meta($employee_id, 'prokb_is_archived', true);
    update_user_meta($employee_id, 'prokb_archive_reason', $reason);
    
    wp_send_json_success(array('message' => 'Сотрудник архивирован'));
}
add_action('wp_ajax_prokb_archive_employee', 'prokb_ajax_archive_employee');

/**
 * Восстановить сотрудника
 */
function prokb_ajax_restore_employee() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if ($prokb_role !== 'director') {
        wp_send_json_error(array('message' => 'Нет прав'));
    }
    
    $employee_id = intval($_POST['employee_id'] ?? 0);
    if (!$employee_id) {
        wp_send_json_error(array('message' => 'ID не указан'));
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
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав'));
    }
    
    $employee_id = intval($_POST['employee_id'] ?? 0);
    $content = sanitize_textarea_field($_POST['content'] ?? '');
    
    if (!$employee_id || empty($content)) {
        wp_send_json_error(array('message' => 'Обязательные поля не заполнены'));
    }
    
    $comment_id = wp_insert_post(array(
        'post_type' => 'prokb_employee_comment',
        'post_title' => 'Комментарий',
        'post_content' => $content,
        'post_status' => 'publish',
        'post_author' => $user_id,
    ));
    
    update_post_meta($comment_id, 'employee_id', $employee_id);
    
    wp_send_json_success(array(
        'message' => 'Комментарий добавлен',
        'comment' => array(
            'id' => $comment_id,
            'content' => $content,
            'author' => prokb_get_user_data($user_id),
            'date' => date('d.m.Y'),
        ),
    ));
}
add_action('wp_ajax_prokb_add_employee_comment', 'prokb_ajax_add_employee_comment');
