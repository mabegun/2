<?php
/**
 * Создание демо-данных
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Создание демо-данных при активации
 */
function prokb_create_demo_data() {
    // Проверяем, есть ли уже пользователи
    $users = get_users(array('meta_key' => 'prokb_role'));
    
    if (!empty($users)) {
        return; // Демо-данные уже созданы
    }
    
    // Создаём директора
    $director_id = wp_create_user('ivanov@prokb.ru', wp_generate_password(12), 'ivanov@prokb.ru');
    if (!is_wp_error($director_id)) {
        wp_update_user(array(
            'ID'           => $director_id,
            'display_name' => 'Иванов Иван Иванович',
            'first_name'   => 'Иванов Иван Иванович',
        ));
        update_user_meta($director_id, 'prokb_position', 'Директор');
        update_user_meta($director_id, 'prokb_role', 'director');
        update_user_meta($director_id, 'prokb_avatar_color', '#3b82f6');
    }
    
    // Создаём ГИПа
    $gip_id = wp_create_user('sidorova@prokb.ru', wp_generate_password(12), 'sidorova@prokb.ru');
    if (!is_wp_error($gip_id)) {
        wp_update_user(array(
            'ID'           => $gip_id,
            'display_name' => 'Сидорова Анна Петровна',
            'first_name'   => 'Сидорова Анна Петровна',
        ));
        update_user_meta($gip_id, 'prokb_position', 'Главный инженер проекта');
        update_user_meta($gip_id, 'prokb_role', 'gip');
        update_user_meta($gip_id, 'prokb_competencies', json_encode(array('АР', 'КР', 'ОВ')));
        update_user_meta($gip_id, 'prokb_avatar_color', '#8b5cf6');
    }
    
    // Создаём сотрудника
    $employee_id = wp_create_user('petrov@prokb.ru', wp_generate_password(12), 'petrov@prokb.ru');
    if (!is_wp_error($employee_id)) {
        wp_update_user(array(
            'ID'           => $employee_id,
            'display_name' => 'Петров Сергей Николаевич',
            'first_name'   => 'Петров Сергей Николаевич',
        ));
        update_user_meta($employee_id, 'prokb_position', 'Инженер ПГС');
        update_user_meta($employee_id, 'prokb_role', 'employee');
        update_user_meta($employee_id, 'prokb_competencies', json_encode(array('КР', 'КЖ')));
        update_user_meta($employee_id, 'prokb_avatar_color', '#10b981');
    }
    
    // Создаём ещё сотрудников
    $employee2_id = wp_create_user('kuznetsova@prokb.ru', wp_generate_password(12), 'kuznetsova@prokb.ru');
    if (!is_wp_error($employee2_id)) {
        wp_update_user(array(
            'ID'           => $employee2_id,
            'display_name' => 'Кузнецова Елена Викторовна',
            'first_name'   => 'Кузнецова Елена Викторовна',
        ));
        update_user_meta($employee2_id, 'prokb_position', 'Инженер ОВиК');
        update_user_meta($employee2_id, 'prokb_role', 'employee');
        update_user_meta($employee2_id, 'prokb_competencies', json_encode(array('ОВ', 'ВК')));
        update_user_meta($employee2_id, 'prokb_avatar_color', '#f59e0b');
    }
}
add_action('init', 'prokb_create_demo_data');
