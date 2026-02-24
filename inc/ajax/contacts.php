<?php
/**
 * AJAX - Контактные лица
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Добавить контактное лицо
 */
function prokb_ajax_add_contact() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для добавления'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    $name = sanitize_text_field($_POST['name'] ?? '');
    $position = sanitize_text_field($_POST['position'] ?? '');
    $company = sanitize_text_field($_POST['company'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $notes = sanitize_textarea_field($_POST['notes'] ?? '');
    
    if (!$project_id || empty($name)) {
        wp_send_json_error(array('message' => 'Обязательные поля не заполнены'));
    }
    
    $contact_id = wp_insert_post(array(
        'post_type'    => 'prokb_contact',
        'post_title'   => $name,
        'post_status'  => 'publish',
    ));
    
    // Новый формат
    update_post_meta($contact_id, 'contact_project', $project_id);
    update_post_meta($contact_id, 'contact_position', $position);
    update_post_meta($contact_id, 'contact_company', $company);
    update_post_meta($contact_id, 'contact_phone', $phone);
    update_post_meta($contact_id, 'contact_email', $email);
    
    // Для совместимости
    update_post_meta($contact_id, 'project_id', $project_id);
    update_post_meta($contact_id, 'position', $position);
    update_post_meta($contact_id, 'company', $company);
    update_post_meta($contact_id, 'phone', $phone);
    update_post_meta($contact_id, 'email', $email);
    update_post_meta($contact_id, 'notes', $notes);
    
    wp_send_json_success(array(
        'message'   => 'Контакт добавлен',
        'contact_id' => $contact_id,
    ));
}
add_action('wp_ajax_prokb_add_contact', 'prokb_ajax_add_contact');

/**
 * Обновить контактное лицо
 */
function prokb_ajax_update_contact() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для редактирования'));
    }
    
    $contact_id = intval($_POST['contact_id'] ?? 0);
    
    if (!$contact_id) {
        wp_send_json_error(array('message' => 'ID контакта не указан'));
    }
    
    // Обновляем название
    if (isset($_POST['name'])) {
        wp_update_post(array(
            'ID'         => $contact_id,
            'post_title' => sanitize_text_field($_POST['name']),
        ));
    }
    
    // Обновляем поля
    $fields = array('position', 'company', 'phone', 'email', 'notes');
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = sanitize_text_field($_POST[$field]);
            update_post_meta($contact_id, 'contact_' . $field, $value);
            update_post_meta($contact_id, $field, $value);
        }
    }
    
    wp_send_json_success(array('message' => 'Контакт обновлён'));
}
add_action('wp_ajax_prokb_update_contact', 'prokb_ajax_update_contact');

/**
 * Удалить контактное лицо
 */
function prokb_ajax_delete_contact() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для удаления'));
    }
    
    $contact_id = intval($_POST['contact_id'] ?? 0);
    
    if (!$contact_id) {
        wp_send_json_error(array('message' => 'ID контакта не указан'));
    }
    
    wp_delete_post($contact_id, true);
    
    wp_send_json_success(array('message' => 'Контакт удалён'));
}
add_action('wp_ajax_prokb_delete_contact', 'prokb_ajax_delete_contact');

/**
 * Получить контакты проекта
 */
function prokb_ajax_get_project_contacts() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    
    if (!$project_id) {
        wp_send_json_error(array('message' => 'ID проекта не указан'));
    }
    
    // Ищем по новому мета-полю
    $contacts = get_posts(array(
        'post_type'      => 'prokb_contact',
        'posts_per_page' => -1,
        'meta_key'       => 'contact_project',
        'meta_value'     => $project_id,
    ));
    
    // Fallback: по старому мета-полю
    if (empty($contacts)) {
        $contacts = get_posts(array(
            'post_type'      => 'prokb_contact',
            'posts_per_page' => -1,
            'meta_key'       => 'project_id',
            'meta_value'     => $project_id,
        ));
    }
    
    $result = array();
    foreach ($contacts as $contact) {
        $result[] = array(
            'id'       => $contact->ID,
            'name'     => $contact->post_title,
            'position' => get_post_meta($contact->ID, 'contact_position', true) ?: get_post_meta($contact->ID, 'position', true),
            'company'  => get_post_meta($contact->ID, 'contact_company', true) ?: get_post_meta($contact->ID, 'company', true),
            'phone'    => get_post_meta($contact->ID, 'contact_phone', true) ?: get_post_meta($contact->ID, 'phone', true),
            'email'    => get_post_meta($contact->ID, 'contact_email', true) ?: get_post_meta($contact->ID, 'email', true),
            'notes'    => get_post_meta($contact->ID, 'notes', true),
        );
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_project_contacts', 'prokb_ajax_get_project_contacts');
