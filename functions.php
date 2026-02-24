<?php
/**
 * Проектное Бюро - Functions and definitions
 *
 * @package ProKB
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Константы темы
define('PROKB_VERSION', '2.0.0');
define('PROKB_THEME_DIR', get_template_directory());
define('PROKB_THEME_URI', get_template_directory_uri());

/**
 * Загрузка модулей
 */
$prokb_modules = array(
    // Основные модули
    'setup',
    'post-types',
    'user-meta',
    'helpers',
    // AJAX модули
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
    // Демо-данные
    'demo-data',
);

foreach ($prokb_modules as $module) {
    $file = PROKB_THEME_DIR . '/inc/' . $module . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
