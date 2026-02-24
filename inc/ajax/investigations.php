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
        'post_type' => 'prokb_standard_investigation',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
    
    $result = array();
    foreach ($investigations as $inv) {
        $result[] = array('id' => $inv->ID, 'name' => $inv->post_title);
    }
    
    // Если пусто - стандартный список
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
 * Добавить изыскание
 */
function prokb_ajax_add_project_investigation() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = function_exists('prokb_get_user_role') ? prokb_get_user_role($user_id) : get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    $standard_id = intval($_POST['standard_id'] ?? 0);
    $custom_name = sanitize_text_field($_POST['custom_name'] ?? '');
    
    if (!$project_id) {
        wp_send_json_error(array('message' => 'ID проекта не указан'));
    }
    
    if (!$standard_id && empty($custom_name)) {
        wp_send_json_error(array('message' => 'Выберите изыскание или введите название'));
    }
    
    // Название
    $inv_name = $custom_name;
    if ($standard_id) {
        $standard = get_post($standard_id);
        if ($standard && $standard->post_type === 'prokb_standard_investigation') {
            $inv_name = $standard->post_title;
        }
    }
    
    $inv_id = wp_insert_post(array(
        'post_type' => 'prokb_project_investigation',
        'post_title' => $inv_name,
        'post_status' => 'publish',
    ));
    
    if (is_wp_error($inv_id)) {
        wp_send_json_error(array('message' => 'Ошибка создания'));
    }
    
    update_post_meta($inv_id, 'project_id', $project_id);
    if ($standard_id) {
        update_post_meta($inv_id, 'standard_investigation_id', $standard_id);
    } else {
        update_post_meta($inv_id, 'is_custom', true);
        update_post_meta($inv_id, 'custom_name', $custom_name);
    }
    
    // Поля
    $fields = array('status', 'contractor_name', 'contractor_contact', 'contractor_phone', 'contractor_email', 'start_date', 'end_date', 'contract_number', 'contract_date', 'description');
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($inv_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    update_post_meta($inv_id, 'status', get_post_meta($inv_id, 'status', true) ?: 'not_started');
    
    // Файлы
    if (!empty($_FILES['contract_file']) && $_FILES['contract_file']['error'] === UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload = wp_handle_upload($_FILES['contract_file'], array('test_form' => false));
        if (!isset($upload['error'])) {
            update_post_meta($inv_id, 'contract_file', $upload['url']);
            update_post_meta($inv_id, 'contract_file_name', $_FILES['contract_file']['name']);
        }
    }
    
    if (!empty($_FILES['result_file']) && $_FILES['result_file']['error'] === UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload = wp_handle_upload($_FILES['result_file'], array('test_form' => false));
        if (!isset($upload['error'])) {
            update_post_meta($inv_id, 'result_file', $upload['url']);
            update_post_meta($inv_id, 'result_file_name', $_FILES['result_file']['name']);
        }
    }
    
    wp_send_json_success(array(
        'message' => 'Изыскание добавлено',
        'investigation' => array(
            'id' => $inv_id,
            'name' => $inv_name,
            'standard_id' => $standard_id,
            'is_custom' => !$standard_id,
            'status' => 'not_started',
        ),
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
        wp_send_json_error(array('message' => 'ID не указан'));
    }
    
    $fields = array('status', 'contractor_name', 'contractor_contact', 'contractor_phone', 'contractor_email', 'start_date', 'end_date', 'contract_number', 'contract_date', 'description');
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($inv_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    // Файлы
    if (!empty($_FILES['contract_file']) && $_FILES['contract_file']['error'] === UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload = wp_handle_upload($_FILES['contract_file'], array('test_form' => false));
        if (!isset($upload['error'])) {
            update_post_meta($inv_id, 'contract_file', $upload['url']);
            update_post_meta($inv_id, 'contract_file_name', $_FILES['contract_file']['name']);
        }
    }
    
    if (!empty($_FILES['result_file']) && $_FILES['result_file']['error'] === UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload = wp_handle_upload($_FILES['result_file'], array('test_form' => false));
        if (!isset($upload['error'])) {
            update_post_meta($inv_id, 'result_file', $upload['url']);
            update_post_meta($inv_id, 'result_file_name', $_FILES['result_file']['name']);
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
        wp_send_json_error(array('message' => 'ID не указан'));
    }
    
    wp_delete_post($inv_id, true);
    wp_send_json_success(array('message' => 'Изыскание удалено'));
}
add_action('wp_ajax_prokb_delete_project_investigation', 'prokb_ajax_delete_project_investigation');
