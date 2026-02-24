<?php
/**
 * Кастомные роли пользователей
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Регистрация кастомных ролей при активации темы
 */
function prokb_register_roles() {
    // Удаляем стандартные роли если нужно (опционально)
    // remove_role('subscriber');
    // remove_role('contributor');
    
    // Роль: Директор
    // Полный доступ ко всему
    add_role('prokb_director', 'Директор', array(
        'read'                   => true,
        'read_private_posts'     => true,
        'read_private_pages'     => true,
        'edit_posts'             => true,
        'edit_others_posts'      => true,
        'edit_private_posts'     => true,
        'edit_published_posts'   => true,
        'delete_posts'           => true,
        'delete_others_posts'    => true,
        'delete_private_posts'   => true,
        'delete_published_posts' => true,
        'publish_posts'          => true,
        'upload_files'           => true,
        'edit_files'             => true,
        'delete_files'           => true,
        'manage_categories'      => true,
        'edit_categories'        => true,
        'delete_categories'      => true,
        'manage_options'         => true, // Доступ к настройкам
        'list_users'             => true,
        'edit_users'             => true,
        'delete_users'           => true,
        'create_users'           => true,
        'promote_users'          => true,
        'remove_users'           => true,
    ));
    
    // Роль: ГИП (Главный инженер проекта)
    // Управление проектами, разделами, задачами, но не пользователями
    add_role('prokb_gip', 'ГИП', array(
        'read'                   => true,
        'read_private_posts'     => true,
        'read_private_pages'     => true,
        'edit_posts'             => true,
        'edit_others_posts'      => true,
        'edit_private_posts'     => true,
        'edit_published_posts'   => true,
        'delete_posts'           => true,
        'delete_others_posts'    => true,
        'delete_private_posts'   => true,
        'delete_published_posts' => true,
        'publish_posts'          => true,
        'upload_files'           => true,
        'edit_files'             => true,
        'delete_files'           => true,
        'manage_categories'      => true,
        'edit_categories'        => true,
        'delete_categories'      => true,
    ));
    
    // Роль: Сотрудник
    // Работа только со своими задачами и разделами
    add_role('prokb_employee', 'Сотрудник', array(
        'read'                   => true,
        'read_private_posts'     => true,
        'edit_posts'             => true,
        'edit_published_posts'   => true,
        'delete_posts'           => true,
        'delete_published_posts' => true,
        'publish_posts'          => true,
        'upload_files'           => true,
        'edit_files'             => true,
    ));
}
add_action('after_switch_theme', 'prokb_register_roles');

/**
 * Также регистрируем роли при init (для случаев когда тема уже активирована)
 */
function prokb_ensure_roles_exist() {
    static $roles_checked = false;
    
    if ($roles_checked) {
        return;
    }
    
    $director_role = get_role('prokb_director');
    
    if (!$director_role) {
        prokb_register_roles();
    }
    
    $roles_checked = true;
}
add_action('init', 'prokb_ensure_roles_exist', 1);

/**
 * Удаление ролей при деактивации темы
 */
function prokb_remove_roles() {
    remove_role('prokb_director');
    remove_role('prokb_gip');
    remove_role('prokb_employee');
}
add_action('switch_theme', 'prokb_remove_roles');

/**
 * Получение роли ProKB для пользователя
 */
function prokb_get_user_role($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        return false;
    }
    
    // Проверяем WordPress роли
    if (in_array('prokb_director', $user->roles)) {
        return 'director';
    }
    if (in_array('prokb_gip', $user->roles)) {
        return 'gip';
    }
    if (in_array('prokb_employee', $user->roles)) {
        return 'employee';
    }
    
    // Fallback на мета-поле (для совместимости)
    return get_user_meta($user_id, 'prokb_role', true);
}

/**
 * Синхронизация мета-поля с WordPress ролью
 */
function prokb_sync_user_role($user_id) {
    $prokb_role = get_user_meta($user_id, 'prokb_role', true);
    
    if (!$prokb_role) {
        return;
    }
    
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        return;
    }
    
    // Удаляем старые ProKB роли
    $user->remove_role('prokb_director');
    $user->remove_role('prokb_gip');
    $user->remove_role('prokb_employee');
    
    // Добавляем новую роль
    switch ($prokb_role) {
        case 'director':
            $user->add_role('prokb_director');
            break;
        case 'gip':
            $user->add_role('prokb_gip');
            break;
        case 'employee':
            $user->add_role('prokb_employee');
            break;
    }
}
add_action('personal_options_update', 'prokb_sync_user_role');
add_action('edit_user_profile_update', 'prokb_sync_user_role');

/**
 * Хук для синхронизации при обновлении мета-поля
 */
function prokb_on_role_meta_update($meta_id, $user_id, $meta_key, $meta_value) {
    if ($meta_key === 'prokb_role') {
        prokb_sync_user_role($user_id);
    }
}
add_action('update_user_meta', 'prokb_on_role_meta_update', 10, 4);
add_action('add_user_meta', 'prokb_on_role_meta_update', 10, 4);
