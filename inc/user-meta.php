<?php
/**
 * Мета-поля для пользователей
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Регистрация мета-полей пользователей
 */
function prokb_add_user_meta_fields() {
    register_meta('user', 'prokb_position', array(
        'type'         => 'string',
        'description'  => 'Должность сотрудника',
        'single'       => true,
        'show_in_rest' => false,
    ));
    
    register_meta('user', 'prokb_role', array(
        'type'         => 'string',
        'description'  => 'Роль в системе (director, gip, employee)',
        'single'       => true,
        'show_in_rest' => false,
    ));
    
    register_meta('user', 'prokb_competencies', array(
        'type'         => 'string',
        'description'  => 'Компетенции сотрудника (JSON массив)',
        'single'       => true,
        'show_in_rest' => false,
    ));
    
    register_meta('user', 'prokb_phone', array(
        'type'         => 'string',
        'description'  => 'Телефон сотрудника',
        'single'       => true,
        'show_in_rest' => false,
    ));
    
    register_meta('user', 'prokb_avatar_color', array(
        'type'         => 'string',
        'description'  => 'Цвет аватара',
        'single'       => true,
        'show_in_rest' => false,
    ));
    
    register_meta('user', 'prokb_is_archived', array(
        'type'         => 'boolean',
        'description'  => 'Сотрудник в архиве',
        'single'       => true,
        'show_in_rest' => false,
    ));
    
    register_meta('user', 'prokb_archive_reason', array(
        'type'         => 'string',
        'description'  => 'Причина архивации',
        'single'       => true,
        'show_in_rest' => false,
    ));
}
add_action('init', 'prokb_add_user_meta_fields');
