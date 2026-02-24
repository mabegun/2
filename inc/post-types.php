<?php
/**
 * Регистрация Custom Post Types
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Регистрация всех кастомных типов записей
 */
function prokb_register_post_types() {
    
    // Проекты
    register_post_type('prokb_project', array(
        'labels' => array(
            'name'               => __('Проекты', 'prokb'),
            'singular_name'      => __('Проект', 'prokb'),
            'add_new'            => __('Добавить проект', 'prokb'),
            'add_new_item'       => __('Добавить новый проект', 'prokb'),
            'edit_item'          => __('Редактировать проект', 'prokb'),
            'new_item'           => __('Новый проект', 'prokb'),
            'view_item'          => __('Просмотреть проект', 'prokb'),
            'search_items'       => __('Искать проекты', 'prokb'),
            'not_found'          => __('Проекты не найдены', 'prokb'),
            'not_found_in_trash' => __('В корзине проекты не найдены', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => true,
        'show_in_menu'        => false,
        'query_var'           => false,
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'supports'            => array('title', 'author', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Разделы проектов
    register_post_type('prokb_section', array(
        'labels' => array(
            'name'               => __('Разделы проектов', 'prokb'),
            'singular_name'      => __('Раздел', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Сотрудники
    register_post_type('prokb_employee', array(
        'labels' => array(
            'name'               => __('Сотрудники', 'prokb'),
            'singular_name'      => __('Сотрудник', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Задачи
    register_post_type('prokb_task', array(
        'labels' => array(
            'name'               => __('Задачи', 'prokb'),
            'singular_name'      => __('Задача', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'author', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Сообщения
    register_post_type('prokb_message', array(
        'labels' => array(
            'name'               => __('Сообщения', 'prokb'),
            'singular_name'      => __('Сообщение', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'content', 'author', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Файлы
    register_post_type('prokb_file', array(
        'labels' => array(
            'name'               => __('Файлы', 'prokb'),
            'singular_name'      => __('Файл', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Уведомления
    register_post_type('prokb_notification', array(
        'labels' => array(
            'name'               => __('Уведомления', 'prokb'),
            'singular_name'      => __('Уведомление', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Контактные лица проектов
    register_post_type('prokb_contact', array(
        'labels' => array(
            'name'               => __('Контактные лица', 'prokb'),
            'singular_name'      => __('Контактное лицо', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Блоки вводной информации
    register_post_type('prokb_intro_block', array(
        'labels' => array(
            'name'               => __('Блоки вводной информации', 'prokb'),
            'singular_name'      => __('Блок', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'content', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Изыскания (стандартные)
    register_post_type('prokb_standard_investigation', array(
        'labels' => array(
            'name'               => __('Стандартные изыскания', 'prokb'),
            'singular_name'      => __('Изыскание', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Изыскания проектов
    register_post_type('prokb_project_investigation', array(
        'labels' => array(
            'name'               => __('Изыскания проектов', 'prokb'),
            'singular_name'      => __('Изыскание проекта', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Этапы экспертизы
    register_post_type('prokb_expertise_stage', array(
        'labels' => array(
            'name'               => __('Этапы экспертизы', 'prokb'),
            'singular_name'      => __('Этап экспертизы', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Экспертиза проектов
    register_post_type('prokb_project_expertise', array(
        'labels' => array(
            'name'               => __('Экспертизы проектов', 'prokb'),
            'singular_name'      => __('Экспертиза проекта', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Замечания экспертизы
    register_post_type('prokb_expertise_remark', array(
        'labels' => array(
            'name'               => __('Замечания экспертизы', 'prokb'),
            'singular_name'      => __('Замечание', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'content', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Комментарии к сотрудникам
    register_post_type('prokb_employee_comment', array(
        'labels' => array(
            'name'               => __('Комментарии к сотрудникам', 'prokb'),
            'singular_name'      => __('Комментарий', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'content', 'author', 'custom-fields'),
        'show_in_rest'        => false,
    ));
    
    // Разделы проектирования (настраиваемые)
    register_post_type('prokb_design_section', array(
        'labels' => array(
            'name'               => __('Разделы проектирования', 'prokb'),
            'singular_name'      => __('Раздел', 'prokb'),
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'supports'            => array('title', 'custom-fields'),
        'show_in_rest'        => false,
    ));
}
add_action('init', 'prokb_register_post_types');
