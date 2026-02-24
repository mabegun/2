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
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
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
 * Удалить контактное лицо
 */
function prokb_ajax_delete_contact() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
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
