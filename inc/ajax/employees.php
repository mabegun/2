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
    $show_archived = $_POST['show_archived'] === 'true';
    
    // Только директор и ГИП могут видеть всех сотрудников
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для просмотра'));
    }
    
    // Получаем пользователей с ProKB ролями
    $args = array(
        'role__in' => array('prokb_director', 'prokb_gip', 'prokb_employee'),
    );
    
    $users = get_users($args);
    
    // Fallback: также ищем по мета-полю
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
        
        if (!$show_archived && $is_archived) {
            continue;
        }
        
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
    
    // Получаем разделы, назначенные сотруднику (как дочерние посты проектов)
    $sections = get_posts(array(
        'post_type'      => 'prokb_section',
        'posts_per_page' => -1,
        'meta_key'       => 'section_assignee',
        'meta_value'     => $employee_id,
    ));
    
    // Fallback: ищем по старому мета-полю
    if (empty($sections)) {
        $sections = get_posts(array(
            'post_type'      => 'prokb_section',
            'posts_per_page' => -1,
            'meta_key'       => 'assignee_id',
            'meta_value'     => $employee_id,
        ));
    }
    
    $project_ids = array();
    $sections_by_project = array();
    
    foreach ($sections as $section) {
        $project_id = $section->post_parent;
        if (!$project_id) {
            $project_id = get_post_meta($section->ID, 'project_id', true);
        }
        
        if ($project_id && !in_array($project_id, $project_ids)) {
            $project_ids[] = $project_id;
            $sections_by_project[$project_id] = array();
        }
        if ($project_id) {
            $sections_by_project[$project_id][] = array(
                'id'     => $section->ID,
                'code'   => get_post_meta($section->ID, 'section_code', true),
                'status' => get_post_meta($section->ID, 'section_status', true) ?: get_post_meta($section->ID, 'status', true),
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
                'status'   => get_post_meta($pid, 'project_status', true) ?: get_post_meta($pid, 'status', true),
                'sections' => $sections_by_project[$pid] ?? array(),
            );
        }
    }
    
    $user_data['projects'] = $projects;
    
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
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    // Только директор может создавать сотрудников
    if ($prokb_role !== 'director') {
        wp_send_json_error(array('message' => 'Нет прав для создания. Только директор может добавлять сотрудников.'));
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
    
    // Валидация роли
    if (!in_array($role, array('director', 'gip', 'employee'))) {
        $role = 'employee';
    }
    
    // Проверяем существование пользователя
    if (email_exists($email)) {
        wp_send_json_error(array('message' => 'Пользователь с таким email уже существует'));
    }
    
    // Генерируем пароль
    $password = wp_generate_password(12, true);
    
    // Создаём пользователя
    $new_user_id = wp_create_user($email, $password, $email);
    
    if (is_wp_error($new_user_id)) {
        wp_send_json_error(array('message' => 'Ошибка создания пользователя: ' . $new_user_id->get_error_message()));
    }
    
    // Обновляем данные пользователя
    wp_update_user(array(
        'ID'           => $new_user_id,
        'display_name' => $name,
        'first_name'   => $name,
    ));
    
    // Назначаем WordPress роль
    $new_user = get_user_by('ID', $new_user_id);
    switch ($role) {
        case 'director':
            $new_user->set_role('prokb_director');
            break;
        case 'gip':
            $new_user->set_role('prokb_gip');
            break;
        case 'employee':
        default:
            $new_user->set_role('prokb_employee');
            break;
    }
    
    // Сохраняем мета-поля для совместимости
    update_user_meta($new_user_id, 'prokb_position', $position);
    update_user_meta($new_user_id, 'prokb_role', $role);
    update_user_meta($new_user_id, 'prokb_phone', $phone);
    update_user_meta($new_user_id, 'prokb_competencies', json_encode($competencies));
    update_user_meta($new_user_id, 'prokb_avatar_color', sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
    
    // TODO: Отправить email с паролем новому сотруднику
    
    wp_send_json_success(array(
        'message'     => 'Сотрудник создан',
        'employee_id' => $new_user_id,
        'password'    => $password, // Временно возвращаем пароль для демо
    ));
}
add_action('wp_ajax_prokb_create_employee', 'prokb_ajax_create_employee');

/**
 * Обновить сотрудника
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
    $name = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $position = sanitize_text_field($_POST['position'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $role = sanitize_text_field($_POST['role'] ?? '');
    $competencies = isset($_POST['competencies']) ? json_decode(stripslashes($_POST['competencies']), true) : array();
    
    if (!$employee_id) {
        wp_send_json_error(array('message' => 'ID сотрудника не указан'));
    }
    
    $user = get_user_by('ID', $employee_id);
    if (!$user) {
        wp_send_json_error(array('message' => 'Пользователь не найден'));
    }
    
    // Обновляем данные
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
        $update_data['user_login'] = $email;
    }
    
    wp_update_user($update_data);
    
    // Обновляем роль если указана
    if (!empty($role) && in_array($role, array('director', 'gip', 'employee'))) {
        $user = get_user_by('ID', $employee_id);
        switch ($role) {
            case 'director':
                $user->set_role('prokb_director');
                break;
            case 'gip':
                $user->set_role('prokb_gip');
                break;
            case 'employee':
                $user->set_role('prokb_employee');
                break;
        }
        update_user_meta($employee_id, 'prokb_role', $role);
    }
    
    // Обновляем мета-поля
    update_user_meta($employee_id, 'prokb_position', $position);
    update_user_meta($employee_id, 'prokb_phone', $phone);
    update_user_meta($employee_id, 'prokb_competencies', json_encode($competencies));
    
    wp_send_json_success(array(
        'message' => 'Данные обновлены',
        'user'    => prokb_get_user_data($employee_id),
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
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
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
