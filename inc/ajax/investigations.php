<?php
/**
 * AJAX - Изыскания
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить стандартные изыскания
 */
function prokb_ajax_get_standard_investigations() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    $investigations = get_posts(array(
        'post_type'      => 'prokb_standard_investigation',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ));
    
    $result = array();
    foreach ($investigations as $inv) {
        $result[] = array(
            'id'   => $inv->ID,
            'name' => $inv->post_title,
        );
    }
    
    // Если пусто, возвращаем стандартный список
    if (empty($result)) {
        $result = array(
            array('id' => 0, 'name' => 'Инженерно-геодезические изыскания'),
            array('id' => 0, 'name' => 'Инженерно-геологические изыскания'),
            array('id' => 0, 'name' => 'Инженерно-гидрометеорологические изыскания'),
            array('id' => 0, 'name' => 'Инженерно-экологические изыскания'),
            array('id' => 0, 'name' => 'Обследование строительных конструкций'),
        );
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_standard_investigations', 'prokb_ajax_get_standard_investigations');

/**
 * Добавить изыскание к проекту
 */
function prokb_ajax_add_project_investigation() {
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
    $standard_id = intval($_POST['standard_id'] ?? 0);
    $custom_name = sanitize_text_field($_POST['custom_name'] ?? '');
    
    if (!$project_id || (!$standard_id && empty($custom_name))) {
        wp_send_json_error(array('message' => 'Обязательные поля не заполнены'));
    }
    
    // Получаем название изыскания
    $inv_name = $custom_name;
    if ($standard_id) {
        $standard_post = get_post($standard_id);
        if ($standard_post && $standard_post->post_type === 'prokb_standard_investigation') {
            $inv_name = $standard_post->post_title;
        }
    }
    
    $inv_id = wp_insert_post(array(
        'post_type'    => 'prokb_project_investigation',
        'post_title'   => $inv_name,
        'post_status'  => 'publish',
    ));
    
    if (is_wp_error($inv_id)) {
        wp_send_json_error(array('message' => 'Ошибка создания изыскания'));
    }
    
    update_post_meta($inv_id, 'project_id', $project_id);
    
    if ($standard_id) {
        update_post_meta($inv_id, 'standard_investigation_id', $standard_id);
    } else {
        update_post_meta($inv_id, 'is_custom', true);
        update_post_meta($inv_id, 'custom_name', $custom_name);
    }
    
    // Дополнительные поля
    $fields = array(
        'status',
        'contractor_name',
        'contractor_contact',
        'contractor_phone',
        'contractor_email',
        'start_date',
        'end_date',
        'contract_number',
        'contract_date',
        'contract_file',
        'result_file',
        'description',
    );
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($inv_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    wp_send_json_success(array(
        'message' => 'Изыскание добавлено',
        'inv_id'  => $inv_id,
    ));
}
add_action('wp_ajax_prokb_add_project_investigation', 'prokb_ajax_add_project_investigation');

/**
 * Обновить изыскание
 */
function prokb_ajax_update_project_investigation() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $inv_id = intval($_POST['inv_id'] ?? 0);
    
    if (!$inv_id) {
        wp_send_json_error(array('message' => 'ID изыскания не указан'));
    }
    
    $fields = array(
        'status',
        'contractor_name',
        'contractor_contact',
        'contractor_phone',
        'contractor_email',
        'start_date',
        'end_date',
        'contract_number',
        'contract_date',
        'contract_file',
        'result_file',
        'description',
    );
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($inv_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    wp_send_json_success(array('message' => 'Изыскание обновлено'));
}
add_action('wp_ajax_prokb_update_project_investigation', 'prokb_ajax_update_project_investigation');

/**
 * Удалить изыскание
 */
function prokb_ajax_delete_project_investigation() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $inv_id = intval($_POST['inv_id'] ?? 0);
    
    if (!$inv_id) {
        wp_send_json_error(array('message' => 'ID изыскания не указан'));
    }
    
    wp_delete_post($inv_id, true);
    
    wp_send_json_success(array('message' => 'Изыскание удалено'));
}
add_action('wp_ajax_prokb_delete_project_investigation', 'prokb_ajax_delete_project_investigation');
