<?php
/**
 * AJAX - Уведомления
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить уведомления пользователя
 */
function prokb_ajax_get_notifications() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $user_id = get_current_user_id();
    
    $notifications = get_posts(array(
        'post_type'      => 'prokb_notification',
        'posts_per_page' => 20,
        'meta_key'       => 'user_id',
        'meta_value'     => $user_id,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    
    $result = array();
    foreach ($notifications as $notif) {
        $result[] = array(
            'id'      => $notif->ID,
            'message' => $notif->post_title,
            'type'    => get_post_meta($notif->ID, 'type', true),
            'link'    => get_post_meta($notif->ID, 'link', true),
            'is_read' => get_post_meta($notif->ID, 'is_read', true),
            'date'    => get_the_date('d.m.Y H:i', $notif),
        );
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_prokb_get_notifications', 'prokb_ajax_get_notifications');

/**
 * Отметить уведомление как прочитанное
 */
function prokb_ajax_read_notification() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Не авторизован'));
    }
    
    $notif_id = intval($_POST['notification_id'] ?? 0);
    
    if (!$notif_id) {
        wp_send_json_error(array('message' => 'ID уведомления не указан'));
    }
    
    update_post_meta($notif_id, 'is_read', true);
    
    wp_send_json_success(array('message' => 'Уведомление прочитано'));
}
add_action('wp_ajax_prokb_read_notification', 'prokb_ajax_read_notification');
