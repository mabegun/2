<?php
/**
 * Вспомогательные функции
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить данные пользователя
 */
function prokb_get_user_data($user_id) {
    $user = get_user_by('ID', $user_id);
    if (!$user) return null;
    
    $name = $user->display_name ?: $user->first_name ?: $user->user_login;
    $initials = '';
    $parts = explode(' ', $name);
    if (count($parts) >= 2) {
        $initials = mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1);
    } else {
        $initials = mb_substr($name, 0, 2);
    }
    $initials = mb_strtoupper($initials);
    
    $competencies = get_user_meta($user_id, 'prokb_competencies', true);
    $competencies_arr = $competencies ? json_decode($competencies, true) : array();
    
    return array(
        'id'             => $user->ID,
        'email'          => $user->user_email,
        'name'           => $name,
        'initials'       => $initials,
        'position'       => get_user_meta($user_id, 'prokb_position', true),
        'role'           => get_user_meta($user_id, 'prokb_role', true),
        'competencies'   => $competencies_arr,
        'phone'          => get_user_meta($user_id, 'prokb_phone', true),
        'avatar_color'   => get_user_meta($user_id, 'prokb_avatar_color', true),
        'is_archived'    => get_user_meta($user_id, 'prokb_is_archived', true),
        'archive_reason' => get_user_meta($user_id, 'prokb_archive_reason', true),
    );
}

/**
 * Форматирование проекта
 */
function prokb_format_project($project, $detailed = false) {
    $id = $project->ID;
    
    $status = get_post_meta($id, 'status', true) ?: 'in_work';
    $gip_id = get_post_meta($id, 'gip_id', true);
    
    // Разделы
    $sections = get_posts(array(
        'post_type'      => 'prokb_section',
        'posts_per_page' => -1,
        'meta_key'       => 'project_id',
        'meta_value'     => $id,
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ));
    
    $sections_data = array();
    $completed = 0;
    $in_progress = 0;
    
    foreach ($sections as $section) {
        $section_status = get_post_meta($section->ID, 'status', true) ?: 'not_started';
        $assignee_id = get_post_meta($section->ID, 'assignee_id', true);
        $expertise_status = get_post_meta($section->ID, 'expertise_status', true);
        
        if ($section_status === 'completed') $completed++;
        if ($section_status === 'in_progress') $in_progress++;
        
        $section_data = array(
            'id'              => $section->ID,
            'code'            => get_post_meta($section->ID, 'section_code', true) ?: $section->post_title,
            'description'     => get_post_meta($section->ID, 'description', true),
            'status'          => $section_status,
            'assignee_id'     => $assignee_id,
            'assignee'        => $assignee_id ? prokb_get_user_data($assignee_id) : null,
            'started_at'      => get_post_meta($section->ID, 'started_at', true),
            'completed_at'    => get_post_meta($section->ID, 'completed_at', true),
            'expertise_status'=> $expertise_status,
            'has_new_messages'=> false,
        );
        
        $sections_data[] = $section_data;
    }
    
    $total_sections = count($sections_data);
    $progress = $total_sections > 0 ? round(($completed / $total_sections) * 100) : 0;
    
    $data = array(
        'id'                => $id,
        'name'              => $project->post_title,
        'address'           => get_post_meta($id, 'address', true),
        'type'              => get_post_meta($id, 'type', true) ?: 'construction',
        'deadline'          => get_post_meta($id, 'deadline', true),
        'customer_contact'  => get_post_meta($id, 'customer_contact', true),
        'customer_phone'    => get_post_meta($id, 'customer_phone', true),
        'expertise'         => get_post_meta($id, 'expertise', true) ?: 'none',
        'status'            => $status,
        'gip_id'            => $gip_id,
        'gip'               => $gip_id ? prokb_get_user_data($gip_id) : null,
        'code'              => get_post_meta($id, 'code', true),
        'description'       => get_post_meta($id, 'description', true),
        'created_at'        => get_the_date('d.m.Y', $id),
        'sections'          => $sections_data,
        'sections_total'    => $total_sections,
        'sections_completed'=> $completed,
        'sections_in_progress' => $in_progress,
        'progress'          => $progress,
        'archived_at'       => get_post_meta($id, 'archived_at', true),
        'archive_reason'    => get_post_meta($id, 'archive_reason', true),
        'positive_conclusion_file' => get_post_meta($id, 'positive_conclusion_file', true),
        'positive_conclusion_name' => get_post_meta($id, 'positive_conclusion_name', true),
    );
    
    // Детальная информация
    if ($detailed) {
        // Контактные лица
        $contacts = get_posts(array(
            'post_type'      => 'prokb_contact',
            'posts_per_page' => -1,
            'meta_key'       => 'project_id',
            'meta_value'     => $id,
        ));
        
        $data['contact_persons'] = array();
        foreach ($contacts as $contact) {
            $data['contact_persons'][] = array(
                'id'       => $contact->ID,
                'name'     => $contact->post_title,
                'position' => get_post_meta($contact->ID, 'position', true),
                'company'  => get_post_meta($contact->ID, 'company', true),
                'phone'    => get_post_meta($contact->ID, 'phone', true),
                'email'    => get_post_meta($contact->ID, 'email', true),
                'notes'    => get_post_meta($contact->ID, 'notes', true),
            );
        }
        
        // Блоки вводной информации
        $intro_blocks = get_posts(array(
            'post_type'      => 'prokb_intro_block',
            'posts_per_page' => -1,
            'meta_key'       => 'project_id',
            'meta_value'     => $id,
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'order',
            'order'          => 'ASC',
        ));
        
        $data['intro_blocks'] = array();
        foreach ($intro_blocks as $block) {
            $data['intro_blocks'][] = array(
                'id'        => $block->ID,
                'type'      => get_post_meta($block->ID, 'type', true) ?: 'text',
                'title'     => $block->post_title,
                'content'   => $block->post_content,
                'file_name' => get_post_meta($block->ID, 'file_name', true),
                'file_path' => get_post_meta($block->ID, 'file_path', true),
                'order'     => get_post_meta($block->ID, 'order', true) ?: 0,
            );
        }
        
        // Изыскания
        $investigations = get_posts(array(
            'post_type'      => 'prokb_project_investigation',
            'posts_per_page' => -1,
            'meta_key'       => 'project_id',
            'meta_value'     => $id,
        ));
        
        $data['investigations'] = array();
        foreach ($investigations as $inv) {
            $standard_id = get_post_meta($inv->ID, 'standard_investigation_id', true);
            
            $data['investigations'][] = array(
                'id'                     => $inv->ID,
                'name'                   => $standard_id ? get_the_title($standard_id) : get_post_meta($inv->ID, 'custom_name', true),
                'standard_id'            => $standard_id,
                'is_custom'              => !$standard_id,
                'status'                 => get_post_meta($inv->ID, 'status', true) ?: 'not_started',
                'contractor_name'        => get_post_meta($inv->ID, 'contractor_name', true),
                'contractor_contact'     => get_post_meta($inv->ID, 'contractor_contact', true),
                'contractor_phone'       => get_post_meta($inv->ID, 'contractor_phone', true),
                'contractor_email'       => get_post_meta($inv->ID, 'contractor_email', true),
                'start_date'             => get_post_meta($inv->ID, 'start_date', true),
                'end_date'               => get_post_meta($inv->ID, 'end_date', true),
            );
        }
        
        // Экспертизы
        $expertises = get_posts(array(
            'post_type'      => 'prokb_project_expertise',
            'posts_per_page' => -1,
            'meta_key'       => 'project_id',
            'meta_value'     => $id,
        ));
        
        $data['expertises'] = array();
        foreach ($expertises as $exp) {
            $stage_id = get_post_meta($exp->ID, 'expertise_stage_id', true);
            
            // Замечания
            $remarks = get_posts(array(
                'post_type'      => 'prokb_expertise_remark',
                'posts_per_page' => -1,
                'meta_key'       => 'project_expertise_id',
                'meta_value'     => $exp->ID,
            ));
            
            $remarks_data = array();
            foreach ($remarks as $remark) {
                $section_id = get_post_meta($remark->ID, 'section_id', true);
                $remarks_data[] = array(
                    'id'          => $remark->ID,
                    'section_id'  => $section_id,
                    'section_code'=> $section_id ? get_post_meta($section_id, 'section_code', true) : '',
                    'content'     => $remark->post_content,
                    'file_name'   => get_post_meta($remark->ID, 'file_name', true),
                    'file_path'   => get_post_meta($remark->ID, 'file_path', true),
                    'is_resolved' => get_post_meta($remark->ID, 'is_resolved', true),
                    'resolved_at' => get_post_meta($remark->ID, 'resolved_at', true),
                );
            }
            
            $data['expertises'][] = array(
                'id'               => $exp->ID,
                'stage_id'         => $stage_id,
                'stage_name'       => $stage_id ? get_the_title($stage_id) : '',
                'expert_name'      => get_post_meta($exp->ID, 'expert_name', true),
                'expert_contact'   => get_post_meta($exp->ID, 'expert_contact', true),
                'expert_phone'     => get_post_meta($exp->ID, 'expert_phone', true),
                'expert_email'     => get_post_meta($exp->ID, 'expert_email', true),
                'start_date'       => get_post_meta($exp->ID, 'start_date', true),
                'end_date'         => get_post_meta($exp->ID, 'end_date', true),
                'remarks'          => $remarks_data,
            );
        }
    }
    
    return $data;
}

/**
 * Форматирование задачи
 */
function prokb_format_task($task) {
    $assignee_id = get_post_meta($task->ID, 'assignee_id', true);
    $project_id = get_post_meta($task->ID, 'project_id', true);
    
    return array(
        'id'          => $task->ID,
        'title'       => $task->post_title,
        'description' => $task->post_content,
        'project_id'  => $project_id,
        'assignee_id' => $assignee_id,
        'assignee'    => $assignee_id ? prokb_get_user_data($assignee_id) : null,
        'author'      => prokb_get_user_data($task->post_author),
        'deadline'    => get_post_meta($task->ID, 'deadline', true),
        'priority'    => get_post_meta($task->ID, 'priority', true) ?: 'medium',
        'status'      => get_post_meta($task->ID, 'status', true) ?: 'not_started',
        'created_at'  => get_the_date('d.m.Y H:i', $task),
    );
}

/**
 * Форматирование сообщения
 */
function prokb_format_message($message) {
    return array(
        'id'         => $message->ID,
        'content'    => $message->post_content,
        'author'     => prokb_get_user_data($message->post_author),
        'date'       => get_the_date('d.m.Y H:i', $message),
        'is_critical'=> get_post_meta($message->ID, 'is_critical', true),
        'is_resolved'=> get_post_meta($message->ID, 'is_resolved', true),
        'parent_id'  => get_post_meta($message->ID, 'parent_id', true),
    );
}

/**
 * Создать уведомление
 */
function prokb_create_notification($user_id, $type, $message, $link_id = 0) {
    $notification_id = wp_insert_post(array(
        'post_type'    => 'prokb_notification',
        'post_title'   => $message,
        'post_status'  => 'publish',
    ));
    
    update_post_meta($notification_id, 'user_id', $user_id);
    update_post_meta($notification_id, 'type', $type);
    update_post_meta($notification_id, 'link', $link_id);
    update_post_meta($notification_id, 'is_read', false);
    
    return $notification_id;
}

/**
 * Описание раздела
 */
function prokb_get_section_description($code) {
    $descriptions = array(
        'ГП'  => 'Генеральный план',
        'АР'  => 'Архитектурные решения',
        'КР'  => 'Конструктивные решения',
        'КЖ'  => 'Конструкции железобетонные',
        'ОВ'  => 'Отопление, вентиляция и кондиционирование',
        'ВК'  => 'Водоснабжение и канализация',
        'ЭОМ' => 'Электроснабжение и электрооборудование',
        'ЭС'  => 'Электроснабжение',
        'ТС'  => 'Теплоснабжение',
        'ГСВ' => 'Газоснабжение (внутреннее)',
        'ГСН' => 'Газоснабжение (наружное)',
        'СС'  => 'Слаботочные системы',
        'ПОС' => 'Проект организации строительства',
        'ПОД' => 'Проект организации дорожного движения',
        'ОДИ' => 'Охрана дорожного движения',
    );
    
    return isset($descriptions[$code]) ? $descriptions[$code] : $code;
}
