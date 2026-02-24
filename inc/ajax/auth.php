<?php
/**
 * AJAX - Авторизация
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Авторизация пользователя
 */
function prokb_ajax_login() {
    check_ajax_referer('prokb_nonce', 'nonce');
    
    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        wp_send_json_error(array('message' => 'Введите email и пароль'));
    }
    
    $user = get_user_by('email', $email);
    
    if (!$user) {
        wp_send_json_error(array('message' => 'Пользователь не найден'));
    }
    
    $prokb_role = get_user_meta($user->ID, 'prokb_role', true);
    if (!$prokb_role) {
        wp_send_json_error(array('message' => 'У пользователя нет доступа к системе'));
    }
    
    // Проверяем архивацию
    if (get_user_meta($user->ID, 'prokb_is_archived', true)) {
        wp_send_json_error(array('message' => 'Пользователь архивирован'));
    }
    
    // Для демо-режима: пароль от 4 символов
    if (strlen($password) >= 4) {
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        wp_send_json_success(array(
            'message' => 'Успешный вход',
            'user'    => prokb_get_user_data($user->ID),
        ));
    }
    
    wp_send_json_error(array('message' => 'Неверный пароль'));
}
add_action('wp_ajax_prokb_login', 'prokb_ajax_login');
add_action('wp_ajax_nopriv_prokb_login', 'prokb_ajax_login');

/**
 * Выход из системы
 */
function prokb_ajax_logout() {
    check_ajax_referer('prokb_nonce', 'nonce');
    wp_logout();
    wp_send_json_success(array('message' => 'Вы вышли из системы'));
}
add_action('wp_ajax_prokb_logout', 'prokb_ajax_logout');

/**
 * Проверка авторизации
 */
function prokb_ajax_check_auth() {
    if (!is_user_logged_in()) {
        wp_send_json_success(array('authenticated' => false));
    }
    
    $user_id = get_current_user_id();
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    if (!$prokb_role) {
        wp_send_json_success(array('authenticated' => false));
    }
    
    wp_send_json_success(array(
        'authenticated' => true,
        'user'          => prokb_get_user_data($user_id),
    ));
}
add_action('wp_ajax_prokb_check_auth', 'prokb_ajax_check_auth');
add_action('wp_ajax_nopriv_prokb_check_auth', 'prokb_ajax_check_auth');
