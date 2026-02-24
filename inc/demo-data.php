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
    // Проверяем, есть ли уже проекты
    $existing_projects = get_posts(array(
        'post_type' => 'prokb_project',
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ));
    
    if (!empty($existing_projects)) {
        return; // Демо-данные уже созданы
    }
    
    // === 1. СОЗДАЁМ/ОБНОВЛЯЕМ ПОЛЬЗОВАТЕЛЕЙ ===
    
    // Директор
    $director_id = email_exists('ivanov@prokb.ru');
    if (!$director_id) {
        $director_id = wp_create_user('ivanov@prokb.ru', 'demo123', 'ivanov@prokb.ru');
    }
    if (!is_wp_error($director_id)) {
        wp_update_user(array(
            'ID'           => $director_id,
            'display_name' => 'Иванов Иван Иванович',
            'first_name'   => 'Иванов Иван Иванович',
        ));
        // Назначаем WordPress роль
        $user = get_user_by('ID', $director_id);
        $user->set_role('prokb_director');
        // Сохраняем мета-поле для совместимости
        update_user_meta($director_id, 'prokb_position', 'Директор');
        update_user_meta($director_id, 'prokb_role', 'director');
        update_user_meta($director_id, 'prokb_avatar_color', '#3b82f6');
    }
    
    // ГИП
    $gip_id = email_exists('sidorova@prokb.ru');
    if (!$gip_id) {
        $gip_id = wp_create_user('sidorova@prokb.ru', 'demo123', 'sidorova@prokb.ru');
    }
    if (!is_wp_error($gip_id)) {
        wp_update_user(array(
            'ID'           => $gip_id,
            'display_name' => 'Сидорова Анна Петровна',
            'first_name'   => 'Сидорова Анна Петровна',
        ));
        $user = get_user_by('ID', $gip_id);
        $user->set_role('prokb_gip');
        update_user_meta($gip_id, 'prokb_position', 'Главный инженер проекта');
        update_user_meta($gip_id, 'prokb_role', 'gip');
        update_user_meta($gip_id, 'prokb_competencies', json_encode(array('АР', 'КР', 'ОВ')));
        update_user_meta($gip_id, 'prokb_avatar_color', '#8b5cf6');
    }
    
    // Сотрудник 1 - Петров
    $employee_id = email_exists('petrov@prokb.ru');
    if (!$employee_id) {
        $employee_id = wp_create_user('petrov@prokb.ru', 'demo123', 'petrov@prokb.ru');
    }
    if (!is_wp_error($employee_id)) {
        wp_update_user(array(
            'ID'           => $employee_id,
            'display_name' => 'Петров Сергей Николаевич',
            'first_name'   => 'Петров Сергей Николаевич',
        ));
        $user = get_user_by('ID', $employee_id);
        $user->set_role('prokb_employee');
        update_user_meta($employee_id, 'prokb_position', 'Инженер ПГС');
        update_user_meta($employee_id, 'prokb_role', 'employee');
        update_user_meta($employee_id, 'prokb_competencies', json_encode(array('КР', 'КЖ')));
        update_user_meta($employee_id, 'prokb_avatar_color', '#10b981');
    }
    
    // Сотрудник 2 - Кузнецова
    $employee2_id = email_exists('kuznetsova@prokb.ru');
    if (!$employee2_id) {
        $employee2_id = wp_create_user('kuznetsova@prokb.ru', 'demo123', 'kuznetsova@prokb.ru');
    }
    if (!is_wp_error($employee2_id)) {
        wp_update_user(array(
            'ID'           => $employee2_id,
            'display_name' => 'Кузнецова Елена Викторовна',
            'first_name'   => 'Кузнецова Елена Викторовна',
        ));
        $user = get_user_by('ID', $employee2_id);
        $user->set_role('prokb_employee');
        update_user_meta($employee2_id, 'prokb_position', 'Инженер ОВиК');
        update_user_meta($employee2_id, 'prokb_role', 'employee');
        update_user_meta($employee2_id, 'prokb_competencies', json_encode(array('ОВ', 'ВК')));
        update_user_meta($employee2_id, 'prokb_avatar_color', '#f59e0b');
    }
    
    // === 2. СОЗДАЁМ ПРОЕКТЫ ===
    
    // Проект 1 - Жилой комплекс
    $project1_id = wp_insert_post(array(
        'post_type' => 'prokb_project',
        'post_title' => 'ЖК "Солнечный берег"',
        'post_status' => 'publish',
        'post_author' => $director_id,
    ));
    
    if (!is_wp_error($project1_id)) {
        update_post_meta($project1_id, 'project_status', 'in_work');
        update_post_meta($project1_id, 'project_gip', $gip_id);
        update_post_meta($project1_id, 'project_deadline', date('Y-m-d', strtotime('+3 months')));
        update_post_meta($project1_id, 'project_address', 'г. Москва, ул. Солнечная, д. 15');
        update_post_meta($project1_id, 'project_client', 'ООО "СтройИнвест"');
        update_post_meta($project1_id, 'project_code', 'ЖК-2024-001');
        update_post_meta($project1_id, 'project_description', 'Многоквартирный жилой дом переменной этажности с подземным паркингом. Проект включает разработку всех разделов проектной документации.');
    }
    
    // Проект 2 - Торговый центр
    $project2_id = wp_insert_post(array(
        'post_type' => 'prokb_project',
        'post_title' => 'ТЦ "Меридиан"',
        'post_status' => 'publish',
        'post_author' => $director_id,
    ));
    
    if (!is_wp_error($project2_id)) {
        update_post_meta($project2_id, 'project_status', 'in_work');
        update_post_meta($project2_id, 'project_gip', $gip_id);
        update_post_meta($project2_id, 'project_deadline', date('Y-m-d', strtotime('+5 months')));
        update_post_meta($project2_id, 'project_address', 'г. Москва, ш. Энтузиастов, д. 50');
        update_post_meta($project2_id, 'project_client', 'АО "ТоргЦентр"');
        update_post_meta($project2_id, 'project_code', 'ТЦ-2024-002');
        update_post_meta($project2_id, 'project_description', 'Торгово-развлекательный центр с киноцентром и фудкорт зоной.');
    }
    
    // === 3. СОЗДАЁМ РАЗДЕЛЫ ПРОЕКТА ===
    
    // Разделы для проекта 1
    $section1_id = wp_insert_post(array(
        'post_type' => 'prokb_section',
        'post_title' => 'АР - Архитектурные решения',
        'post_status' => 'publish',
        'post_parent' => $project1_id,
    ));
    if (!is_wp_error($section1_id)) {
        update_post_meta($section1_id, 'section_code', 'АР');
        update_post_meta($section1_id, 'section_status', 'in_progress');
        update_post_meta($section1_id, 'section_assignee', $employee_id);
        update_post_meta($section1_id, 'section_deadline', date('Y-m-d', strtotime('+1 month')));
        update_post_meta($section1_id, 'section_progress', 45);
    }
    
    $section2_id = wp_insert_post(array(
        'post_type' => 'prokb_section',
        'post_title' => 'КР - Конструктивные решения',
        'post_status' => 'publish',
        'post_parent' => $project1_id,
    ));
    if (!is_wp_error($section2_id)) {
        update_post_meta($section2_id, 'section_code', 'КР');
        update_post_meta($section2_id, 'section_status', 'in_progress');
        update_post_meta($section2_id, 'section_assignee', $employee_id);
        update_post_meta($section2_id, 'section_deadline', date('Y-m-d', strtotime('+2 months')));
        update_post_meta($section2_id, 'section_progress', 20);
    }
    
    $section3_id = wp_insert_post(array(
        'post_type' => 'prokb_section',
        'post_title' => 'ОВ - Отопление и вентиляция',
        'post_status' => 'publish',
        'post_parent' => $project1_id,
    ));
    if (!is_wp_error($section3_id)) {
        update_post_meta($section3_id, 'section_code', 'ОВ');
        update_post_meta($section3_id, 'section_status', 'pending');
        update_post_meta($section3_id, 'section_assignee', $employee2_id);
        update_post_meta($section3_id, 'section_deadline', date('Y-m-d', strtotime('+2.5 months')));
        update_post_meta($section3_id, 'section_progress', 0);
    }
    
    // Разделы для проекта 2
    $section4_id = wp_insert_post(array(
        'post_type' => 'prokb_section',
        'post_title' => 'АР - Архитектурные решения',
        'post_status' => 'publish',
        'post_parent' => $project2_id,
    ));
    if (!is_wp_error($section4_id)) {
        update_post_meta($section4_id, 'section_code', 'АР');
        update_post_meta($section4_id, 'section_status', 'in_progress');
        update_post_meta($section4_id, 'section_assignee', $gip_id);
        update_post_meta($section4_id, 'section_deadline', date('Y-m-d', strtotime('+3 months')));
        update_post_meta($section4_id, 'section_progress', 30);
    }
    
    $section5_id = wp_insert_post(array(
        'post_type' => 'prokb_section',
        'post_title' => 'ОВиК - Отопление, вентиляция и кондиционирование',
        'post_status' => 'publish',
        'post_parent' => $project2_id,
    ));
    if (!is_wp_error($section5_id)) {
        update_post_meta($section5_id, 'section_code', 'ОВиК');
        update_post_meta($section5_id, 'section_status', 'pending');
        update_post_meta($section5_id, 'section_assignee', $employee2_id);
        update_post_meta($section5_id, 'section_deadline', date('Y-m-d', strtotime('+4 months')));
        update_post_meta($section5_id, 'section_progress', 0);
    }
    
    // === 4. СОЗДАЁМ ЗАДАЧИ ===
    
    // Задачи для раздела АР проекта 1
    $task1_id = wp_insert_post(array(
        'post_type' => 'prokb_task',
        'post_title' => 'Разработка генплана',
        'post_status' => 'publish',
        'post_parent' => $section1_id,
    ));
    if (!is_wp_error($task1_id)) {
        update_post_meta($task1_id, 'task_status', 'completed');
        update_post_meta($task1_id, 'task_assignee', $employee_id);
        update_post_meta($task1_id, 'task_deadline', date('Y-m-d', strtotime('-1 week')));
        update_post_meta($task1_id, 'task_priority', 'high');
        update_post_meta($task1_id, 'task_description', 'Подготовка генерального плана участка с нанесением всех зданий и сооружений.');
    }
    
    $task2_id = wp_insert_post(array(
        'post_type' => 'prokb_task',
        'post_title' => 'Планы этажей',
        'post_status' => 'publish',
        'post_parent' => $section1_id,
    ));
    if (!is_wp_error($task2_id)) {
        update_post_meta($task2_id, 'task_status', 'in_progress');
        update_post_meta($task2_id, 'task_assignee', $employee_id);
        update_post_meta($task2_id, 'task_deadline', date('Y-m-d', strtotime('+2 weeks')));
        update_post_meta($task2_id, 'task_priority', 'high');
        update_post_meta($task2_id, 'task_description', 'Разработка поэтажных планов типовых и индивидуальных этажей.');
    }
    
    $task3_id = wp_insert_post(array(
        'post_type' => 'prokb_task',
        'post_title' => 'Фасады и разрезы',
        'post_status' => 'publish',
        'post_parent' => $section1_id,
    ));
    if (!is_wp_error($task3_id)) {
        update_post_meta($task3_id, 'task_status', 'pending');
        update_post_meta($task3_id, 'task_assignee', $employee_id);
        update_post_meta($task3_id, 'task_deadline', date('Y-m-d', strtotime('+3 weeks')));
        update_post_meta($task3_id, 'task_priority', 'medium');
        update_post_meta($task3_id, 'task_description', 'Выполнение чертежей фасадов и разрезов здания.');
    }
    
    // Задачи для раздела КР проекта 1
    $task4_id = wp_insert_post(array(
        'post_type' => 'prokb_task',
        'post_title' => 'Расчёт фундаментов',
        'post_status' => 'publish',
        'post_parent' => $section2_id,
    ));
    if (!is_wp_error($task4_id)) {
        update_post_meta($task4_id, 'task_status', 'in_progress');
        update_post_meta($task4_id, 'task_assignee', $employee_id);
        update_post_meta($task4_id, 'task_deadline', date('Y-m-d', strtotime('+1 month')));
        update_post_meta($task4_id, 'task_priority', 'high');
        update_post_meta($task4_id, 'task_description', 'Выполнение расчёта и конструирование фундаментов.');
    }
    
    // Задачи для Кузнецовой (ОВ)
    $task5_id = wp_insert_post(array(
        'post_type' => 'prokb_task',
        'post_title' => 'Расчёт теплопотерь',
        'post_status' => 'publish',
        'post_parent' => $section3_id,
    ));
    if (!is_wp_error($task5_id)) {
        update_post_meta($task5_id, 'task_status', 'pending');
        update_post_meta($task5_id, 'task_assignee', $employee2_id);
        update_post_meta($task5_id, 'task_deadline', date('Y-m-d', strtotime('+2 weeks')));
        update_post_meta($task5_id, 'task_priority', 'medium');
        update_post_meta($task5_id, 'task_description', 'Расчёт теплопотерь здания для подбора отопительного оборудования.');
    }
    
    // === 5. СОЗДАЁМ УВЕДОМЛЕНИЯ ===
    
    // Уведомление для сотрудника Петрова
    wp_insert_post(array(
        'post_type' => 'prokb_notification',
        'post_status' => 'publish',
        'post_title' => 'Новая задача: Разработка генплана',
        'post_author' => $employee_id,
        'meta_input' => array(
            'notification_type' => 'task_assigned',
            'notification_read' => false,
            'notification_date' => current_time('mysql'),
            'notification_link' => '',
        ),
    ));
    
    wp_insert_post(array(
        'post_type' => 'prokb_notification',
        'post_status' => 'publish',
        'post_title' => 'Дедлайн через 2 недели: Планы этажей',
        'post_author' => $employee_id,
        'meta_input' => array(
            'notification_type' => 'deadline_reminder',
            'notification_read' => false,
            'notification_date' => current_time('mysql'),
            'notification_link' => '',
        ),
    ));
    
    // Уведомление для Кузнецовой
    wp_insert_post(array(
        'post_type' => 'prokb_notification',
        'post_status' => 'publish',
        'post_title' => 'Вам назначен раздел ОВ',
        'post_author' => $employee2_id,
        'meta_input' => array(
            'notification_type' => 'section_assigned',
            'notification_read' => false,
            'notification_date' => current_time('mysql'),
            'notification_link' => '',
        ),
    ));
    
    // Уведомление для ГИПа
    wp_insert_post(array(
        'post_type' => 'prokb_notification',
        'post_status' => 'publish',
        'post_title' => 'Проект "ЖК Солнечный берег" требует внимания',
        'post_author' => $gip_id,
        'meta_input' => array(
            'notification_type' => 'project_update',
            'notification_read' => false,
            'notification_date' => current_time('mysql'),
            'notification_link' => '',
        ),
    ));
    
    // === 6. СОЗДАЁМ СООБЩЕНИЯ ===
    
    // Сообщение в чат проекта
    wp_insert_post(array(
        'post_type' => 'prokb_message',
        'post_status' => 'publish',
        'post_title' => 'Вопрос по генеральному плану',
        'post_author' => $employee_id,
        'post_parent' => $project1_id,
        'meta_input' => array(
            'message_content' => 'Добрый день! Есть вопрос по границам участка в генплане. Необходимо уточнить отступ от красной линии.',
            'message_date' => current_time('mysql'),
        ),
    ));
    
    wp_insert_post(array(
        'post_type' => 'prokb_message',
        'post_status' => 'publish',
        'post_title' => 'Ответ по генплану',
        'post_author' => $gip_id,
        'post_parent' => $project1_id,
        'meta_input' => array(
            'message_content' => 'Отступ от красной линии согласно градостроительным нормам - 5 метров. Уточните в техническом задании.',
            'message_date' => current_time('mysql'),
        ),
    ));
    
    // === 7. СОЗДАЁМ СТАНДАРТНЫЕ ИЗЫСКАНИЯ ===
    
    $standard_investigations = array(
        'Инженерно-геодезические изыскания',
        'Инженерно-геологические изыскания',
        'Инженерно-гидрометеорологические изыскания',
        'Инженерно-экологические изыскания',
        'Обследование строительных конструкций',
    );
    
    foreach ($standard_investigations as $inv_name) {
        // Проверяем, существует ли уже
        $existing = get_posts(array(
            'post_type' => 'prokb_standard_investigation',
            'title' => $inv_name,
            'posts_per_page' => 1,
        ));
        
        if (empty($existing)) {
            wp_insert_post(array(
                'post_type' => 'prokb_standard_investigation',
                'post_title' => $inv_name,
                'post_status' => 'publish',
            ));
        }
    }
    
    // === 8. СОЗДАЁМ КОНТАКТЫ ===
    
    wp_insert_post(array(
        'post_type' => 'prokb_contact',
        'post_status' => 'publish',
        'post_title' => 'Петренко Алексей Михайлович',
        'meta_input' => array(
            'contact_company' => 'ООО "СтройИнвест"',
            'contact_position' => 'Технический директор',
            'contact_phone' => '+7 (495) 123-45-67',
            'contact_email' => 'petrenko@stroyinvest.ru',
            'contact_project' => $project1_id,
        ),
    ));
    
    wp_insert_post(array(
        'post_type' => 'prokb_contact',
        'post_status' => 'publish',
        'post_title' => 'Смирнова Ольга Владимировна',
        'meta_input' => array(
            'contact_company' => 'АО "ТоргЦентр"',
            'contact_position' => 'Руководитель проекта',
            'contact_phone' => '+7 (495) 987-65-43',
            'contact_email' => 'smirnova@torgcenter.ru',
            'contact_project' => $project2_id,
        ),
    ));
}
add_action('init', 'prokb_create_demo_data', 10);

/**
 * Функция для принудительного пересоздания демо-данных
 * Вызывается через URL: ?prokb_reset_demo=1 (только для администраторов)
 */
function prokb_reset_demo_data() {
    if (!isset($_GET['prokb_reset_demo']) || !current_user_can('manage_options')) {
        return;
    }
    
    // Удаляем все записи кастомных типов
    $post_types = array(
        'prokb_project', 
        'prokb_section', 
        'prokb_task', 
        'prokb_message', 
        'prokb_file', 
        'prokb_notification', 
        'prokb_contact',
        'prokb_intro_block',
        'prokb_project_investigation',
        'prokb_project_expertise',
        'prokb_expertise_remark',
        'prokb_employee_comment',
    );
    
    foreach ($post_types as $post_type) {
        $posts = get_posts(array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));
        
        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }
    }
    
    // Перенаправляем
    wp_redirect(remove_query_arg('prokb_reset_demo'));
    exit;
}
add_action('template_redirect', 'prokb_reset_demo_data');
