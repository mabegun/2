<?php
/**
 * AJAX - Экспертиза
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить этапы экспертизы
 */
function prokb_ajax_get_expertise_stages() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    $stages = get_posts(array(
        'post_type'      => 'prokb_expertise_stage',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ));
    
    $result = array();
    foreach ($stages as $stage) {
        $result[] = array(
            'id'   => $stage->ID,
            'name' => $stage->post_title,
        );
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_expertise_stages', 'prokb_ajax_get_expertise_stages');

/**
 * Добавить экспертизу к проекту
 */
function prokb_ajax_add_project_expertise() {
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
    $stage_id = intval($_POST['stage_id'] ?? 0);
    
    if (!$project_id) {
        wp_send_json_error(array('message' => 'ID проекта не указан'));
    }
    
    $exp_id = wp_insert_post(array(
        'post_type'    => 'prokb_project_expertise',
        'post_title'   => $stage_id ? get_the_title($stage_id) : 'Экспертиза',
        'post_status'  => 'publish',
    ));
    
    update_post_meta($exp_id, 'project_id', $project_id);
    
    if ($stage_id) {
        update_post_meta($exp_id, 'expertise_stage_id', $stage_id);
    }
    
    // Дополнительные поля
    $fields = array('expert_name', 'expert_contact', 'expert_phone', 'expert_email', 'start_date', 'end_date');
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($exp_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    wp_send_json_success(array(
        'message' => 'Экспертиза добавлена',
        'exp_id'  => $exp_id,
    ));
}
add_action('wp_ajax_prokb_add_project_expertise', 'prokb_ajax_add_project_expertise');

/**
 * Обновить экспертизу
 */
function prokb_ajax_update_project_expertise() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $exp_id = intval($_POST['exp_id'] ?? 0);
    
    if (!$exp_id) {
        wp_send_json_error(array('message' => 'ID экспертизы не указан'));
    }
    
    $fields = array('expert_name', 'expert_contact', 'expert_phone', 'expert_email', 'start_date', 'end_date', 'expertise_stage_id');
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($exp_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    wp_send_json_success(array('message' => 'Экспертиза обновлена'));
}
add_action('wp_ajax_prokb_update_project_expertise', 'prokb_ajax_update_project_expertise');

/**
 * Удалить экспертизу
 */
function prokb_ajax_delete_project_expertise() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $exp_id = intval($_POST['exp_id'] ?? 0);
    
    if (!$exp_id) {
        wp_send_json_error(array('message' => 'ID экспертизы не указан'));
    }
    
    // Удаляем связанные замечания
    $remarks = get_posts(array(
        'post_type'      => 'prokb_expertise_remark',
        'posts_per_page' => -1,
        'meta_key'       => 'project_expertise_id',
        'meta_value'     => $exp_id,
    ));
    
    foreach ($remarks as $remark) {
        wp_delete_post($remark->ID, true);
    }
    
    wp_delete_post($exp_id, true);
    
    wp_send_json_success(array('message' => 'Экспертиза удалена'));
}
add_action('wp_ajax_prokb_delete_project_expertise', 'prokb_ajax_delete_project_expertise');

/**
 * Добавить замечание экспертизы
 */
function prokb_ajax_add_expertise_remark() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для добавления'));
    }
    
    $expertise_id = intval($_POST['expertise_id'] ?? 0);
    $section_id = intval($_POST['section_id'] ?? 0);
    $content = sanitize_textarea_field($_POST['content'] ?? '');
    
    if (!$expertise_id || empty($content)) {
        wp_send_json_error(array('message' => 'Обязательные поля не заполнены'));
    }
    
    $remark_id = wp_insert_post(array(
        'post_type'    => 'prokb_expertise_remark',
        'post_title'   => 'Замечание',
        'post_content' => $content,
        'post_status'  => 'publish',
    ));
    
    update_post_meta($remark_id, 'project_expertise_id', $expertise_id);
    update_post_meta($remark_id, 'section_id', $section_id);
    update_post_meta($remark_id, 'is_resolved', false);
    
    // Обработка файла
    if (!empty($_FILES['file'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload = wp_handle_upload($_FILES['file'], array('test_form' => false));
        
        if (!isset($upload['error'])) {
            update_post_meta($remark_id, 'file_path', $upload['url']);
            update_post_meta($remark_id, 'file_name', $_FILES['file']['name']);
        }
    }
    
    wp_send_json_success(array(
        'message'  => 'Замечание добавлено',
        'remark_id' => $remark_id,
    ));
}
add_action('wp_ajax_prokb_add_expertise_remark', 'prokb_ajax_add_expertise_remark');

/**
 * Отметить замечание как исправленное
 */
function prokb_ajax_resolve_expertise_remark() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $remark_id = intval($_POST['remark_id'] ?? 0);
    $resolved = $_POST['resolved'] === 'true' || $_POST['resolved'] === true;
    
    if (!$remark_id) {
        wp_send_json_error(array('message' => 'ID замечания не указан'));
    }
    
    update_post_meta($remark_id, 'is_resolved', $resolved);
    update_post_meta($remark_id, 'resolved_at', $resolved ? current_time('mysql') : '');
    
    wp_send_json_success(array('message' => 'Статус замечания обновлён'));
}
add_action('wp_ajax_prokb_resolve_expertise_remark', 'prokb_ajax_resolve_expertise_remark');

/**
 * Загрузить положительное заключение
 */
function prokb_ajax_upload_positive_conclusion() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    if (!in_array($prokb_role, array('director', 'gip'))) {
        wp_send_json_error(array('message' => 'Нет прав для загрузки'));
    }
    
    $project_id = intval($_POST['project_id'] ?? 0);
    
    if (!$project_id) {
        wp_send_json_error(array('message' => 'ID проекта не указан'));
    }
    
    if (empty($_FILES['file'])) {
        wp_send_json_error(array('message' => 'Файл не загружен'));
    }
    
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $upload = wp_handle_upload($_FILES['file'], array('test_form' => false));
    
    if (isset($upload['error'])) {
        wp_send_json_error(array('message' => 'Ошибка загрузки файла'));
    }
    
    update_post_meta($project_id, 'positive_conclusion_file', $upload['url']);
    update_post_meta($project_id, 'positive_conclusion_name', $_FILES['file']['name']);
    update_post_meta($project_id, 'status', 'completed');
    
    wp_send_json_success(array(
        'message'  => 'Положительное заключение загружено',
        'file_url' => $upload['url'],
    ));
}
add_action('wp_ajax_prokb_upload_positive_conclusion', 'prokb_ajax_upload_positive_conclusion');
