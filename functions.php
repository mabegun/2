<?php
/**
 * Проектное Бюро - Основной файл темы
 * 
 * @package ProKB
 * @version 2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Константы темы
define('PROKB_THEME_DIR', get_template_directory());
define('PROKB_THEME_URI', get_template_directory_uri());
define('PROKB_VERSION', '2.0.0');

/**
 * Загрузка модулей темы
 */
function prokb_load_modules() {
    // Основные модули
    $prokb_modules = array(
        // Сначала регистрируем роли
        'roles',
        // Затем базовую настройку
        'setup',
        'post-types',
        'user-meta',
        'helpers',
        // AJAX обработчики
        'ajax/auth',
        'ajax/projects',
        'ajax/sections',
        'ajax/contacts',
        'ajax/intro-blocks',
        'ajax/investigations',
        'ajax/expertise',
        'ajax/employees',
        'ajax/tasks',
        'ajax/messages',
        'ajax/files',
        'ajax/notifications',
        'ajax/design-sections',
        'ajax/dashboard',
        // Демо-данные - загружаются последними
        'demo-data',
    );
    
    foreach ($prokb_modules as $module) {
        $file = PROKB_THEME_DIR . '/inc/' . $module . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// Загружаем модули после того как WordPress полностью инициализирован
add_action('after_setup_theme', 'prokb_load_modules', 5);

/**
 * Получение роли текущего пользователя
 */
function prokb_get_current_user_role() {
    return prokb_get_user_role(get_current_user_id());
}
