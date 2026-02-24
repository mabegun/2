<?php
/**
 * The main template file
 *
 * @package ProKB
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$is_logged_in = is_user_logged_in();
$current_user_id = get_current_user_id();
$prokb_role = null;
if ($is_logged_in) {
    $user = get_user_by('ID', $current_user_id);
    if (in_array('prokb_director', $user->roles)) {
        $prokb_role = 'director';
    } elseif (in_array('prokb_gip', $user->roles)) {
        $prokb_role = 'gip';
    } elseif (in_array('prokb_employee', $user->roles)) {
        $prokb_role = 'employee';
    } else {
        $prokb_role = get_user_meta($current_user_id, 'prokb_role', true);
    }
}
?>

<!-- Страница входа -->
<div id="login-page" class="login-container <?php echo $is_logged_in && $prokb_role ? 'hidden' : ''; ?>">
    <div class="login-box card-static p-8">
        <div class="text-center mb-8">
            <div class="login-logo">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Проектное бюро</h1>
            <p class="text-slate-500 mt-2">Система управления проектами</p>
        </div>

        <form id="login-form" class="space-y-5">
            <div class="form-group mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2" for="login-email">Email</label>
                <input type="email" id="login-email" class="input-field w-full" placeholder="email@prokb.ru" required>
            </div>
            <div class="form-group mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2" for="login-password">Пароль</label>
                <input type="password" id="login-password" class="input-field w-full" placeholder="••••••••" required>
            </div>
            <p id="login-error" class="error-message hidden mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Неверный email или пароль</span>
            </p>
            <button type="submit" class="btn-primary w-full text-center">
                <span id="login-btn-text">Войти</span>
                <span id="login-btn-loader" class="loader hidden"></span>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100">
            <p class="text-xs text-slate-400 text-center mb-3">Демо-доступы (любой пароль от 4 символов):</p>
            <div class="text-xs text-slate-500 text-center space-y-1 bg-slate-50 rounded-xl p-3">
                <p><span class="font-medium">Директор:</span> ivanov@prokb.ru</p>
                <p><span class="font-medium">ГИП:</span> sidorova@prokb.ru</p>
                <p><span class="font-medium">Сотрудник:</span> petrov@prokb.ru</p>
            </div>
        </div>
    </div>
</div>

<!-- Основное приложение -->
<div id="app" class="app-container <?php echo $is_logged_in && $prokb_role ? '' : 'hidden'; ?>">
    
    <!-- Сайдбар -->
    <aside class="sidebar" id="sidebar">
        <div class="p-5">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
                <div id="user-avatar" class="avatar bg-gradient-to-br from-slate-700 to-slate-500 text-white"></div>
                <div class="flex-1 min-w-0">
                    <p id="user-name" class="font-semibold text-slate-800 truncate"></p>
                    <p id="user-position" class="text-xs text-slate-500"></p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-3 py-2">
            <div class="mb-2 px-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Меню</div>
            
            <button data-nav="dashboard" class="sidebar-item active">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Дашборд
            </button>
            
            <button data-nav="projects" class="sidebar-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Проекты
            </button>
            
            <button data-nav="tasks" class="sidebar-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                Задачи
                <span id="tasks-badge" class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full hidden">0</span>
            </button>
            
            <button id="employees-nav" data-nav="employees" class="sidebar-item hidden">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Сотрудники
            </button>
            
            <button id="admin-nav" data-nav="admin" class="sidebar-item hidden">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Настройки
            </button>
        </nav>

        <div class="p-3 border-t border-slate-100">
            <button id="logout-btn" class="sidebar-item w-full text-left text-slate-500 hover:text-slate-700 hover:bg-slate-50">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Выйти
            </button>
        </div>
    </aside>

    <!-- Основной контент -->
    <main class="main-content">
        
        <!-- Дашборд -->
        <div id="dashboard-page" class="page active page-content">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-800">Дашборд</h1>
                <p id="welcome-text" class="text-slate-500 mt-1"></p>
            </div>

            <div class="grid grid-cols-4 gap-6 mb-8 stat-cards">
                <div class="stat-card">
                    <p class="text-sm text-slate-500 mb-1">Всего проектов</p>
                    <p id="stat-total-projects" class="text-3xl font-bold text-slate-800">0</p>
                </div>
                <div class="stat-card">
                    <p class="text-sm text-slate-500 mb-1">В работе</p>
                    <p id="stat-in-progress" class="text-3xl font-bold text-blue-600">0</p>
                </div>
                <div class="stat-card">
                    <p class="text-sm text-slate-500 mb-1">Выполнено разделов</p>
                    <p id="stat-completed-sections" class="text-3xl font-bold text-slate-800">0<span class="text-lg text-slate-400">/0</span></p>
                </div>
                <div class="stat-card">
                    <p class="text-sm text-slate-500 mb-1">Сотрудников</p>
                    <p id="stat-employees" class="text-3xl font-bold text-slate-800">0</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-6">
                <div class="col-span-2">
                    <div class="card-static p-6">
                        <h2 class="font-semibold text-slate-800 mb-5">Активные проекты</h2>
                        <div id="dashboard-projects" class="space-y-4"></div>
                    </div>
                </div>
                <div>
                    <div class="card-static p-6">
                        <h2 class="font-semibold text-slate-800 mb-5">Мои задачи</h2>
                        <div id="dashboard-tasks" class="space-y-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Проекты -->
        <div id="projects-page" class="page page-content">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Проекты</h1>
                    <p class="text-slate-500 mt-1">Управление проектами организации</p>
                </div>
                <button id="new-project-btn" class="btn-primary hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Новый проект
                </button>
            </div>

            <!-- Вкладки статусов -->
            <div id="project-status-tabs" class="flex gap-2 mb-6">
                <button class="status-tab active" data-status="in_work">В работе</button>
                <button class="status-tab" data-status="in_expertise">В экспертизе</button>
                <button class="status-tab" data-status="completed">Выполнено</button>
                <button class="status-tab" data-status="archived">Архив</button>
            </div>

            <div class="flex gap-4 mb-6">
                <input type="text" id="projects-search" placeholder="Поиск по проектам..." class="input-field flex-1">
                <select id="projects-filter-type" class="input-field bg-white" style="max-width: 200px;">
                    <option value="">Все типы</option>
                    <option value="construction">Новое строительство</option>
                    <option value="capital_repair">Капитальный ремонт</option>
                    <option value="reconstruction">Реконструкция</option>
                    <option value="modernization">Модернизация</option>
                    <option value="demolition">Снос</option>
                </select>
            </div>

            <div id="projects-list" class="grid gap-5"></div>
        </div>

        <!-- Карточка проекта -->
        <div id="project-page" class="page page-content">
            <div class="flex items-center justify-between mb-6">
                <button id="back-to-projects" class="back-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Назад к проектам
                </button>
                <div class="flex gap-2">
                    <button id="edit-project-btn" class="btn-secondary hidden">Редактировать</button>
                    <button id="archive-project-btn" class="btn-secondary hidden">В архив</button>
                </div>
            </div>

            <div class="mb-6">
                <h1 id="project-title" class="text-2xl font-bold text-slate-800"></h1>
                <p id="project-address" class="text-slate-500 mt-1"></p>
            </div>

            <div id="project-info" class="card-static p-6 mb-6"></div>

            <!-- Вкладки проекта -->
            <div class="flex gap-2 border-b border-slate-200 mb-6">
                <button class="project-tab active" data-tab="sections">Разделы</button>
                <button class="project-tab" data-tab="intro">Вводная информация</button>
                <button class="project-tab" data-tab="contacts">Контакты</button>
                <button class="project-tab" data-tab="investigations">Изыскания</button>
                <button class="project-tab" data-tab="expertise">Экспертиза</button>
            </div>

            <!-- Контент вкладок -->
            <div id="tab-sections" class="project-tab-content active">
                <div id="project-sections" class="grid gap-4"></div>
            </div>

            <div id="tab-intro" class="project-tab-content">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-slate-800">Вводная информация</h3>
                    <button id="add-intro-block-btn" class="btn-primary text-sm hidden">+ Добавить блок</button>
                </div>
                <div id="intro-blocks" class="grid gap-4"></div>
            </div>

            <div id="tab-contacts" class="project-tab-content">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-slate-800">Контактные лица</h3>
                    <button id="add-contact-btn" class="btn-primary text-sm hidden">+ Добавить контакт</button>
                </div>
                <div id="contact-persons" class="grid gap-4"></div>
            </div>

            <div id="tab-investigations" class="project-tab-content">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-slate-800">Изыскания</h3>
                    <button id="add-investigation-btn" class="btn-primary text-sm hidden">+ Добавить изыскание</button>
                </div>
                <div id="investigations-list" class="grid gap-4"></div>
            </div>

            <div id="tab-expertise" class="project-tab-content">
                <div id="expertise-container"></div>
            </div>
        </div>

        <!-- Раздел проекта -->
        <div id="section-page" class="page page-content">
            <button id="back-to-project" class="back-btn mb-6">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Назад к проекту
            </button>

            <div class="mb-8">
                <h1 id="section-title" class="text-2xl font-bold text-slate-800"></h1>
                <p id="section-project-name" class="text-slate-500 mt-1"></p>
            </div>

            <div class="grid grid-cols-3 gap-6">
                <div>
                    <div class="card-static p-6">
                        <h3 class="font-semibold text-slate-800 mb-5">Информация о разделе</h3>
                        <div id="section-info"></div>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="card-static overflow-hidden">
                        <div class="p-6 border-b border-slate-100">
                            <h3 class="font-semibold text-slate-800">Обсуждение раздела</h3>
                        </div>
                        <div id="section-messages" class="p-6 space-y-4 max-h-96 overflow-y-auto"></div>
                        <div class="p-6 border-t border-slate-100 bg-slate-50">
                            <form id="message-form" class="flex gap-3">
                                <textarea id="message-content" class="input-field flex-1 resize-none" rows="2" placeholder="Напишите сообщение..." required></textarea>
                                <div class="flex flex-col gap-2 justify-end">
                                    <label class="flex items-center gap-2 text-sm text-slate-500">
                                        <input type="checkbox" id="message-critical" class="w-4 h-4 rounded"> Критично
                                    </label>
                                    <button type="submit" class="btn-primary text-sm">Отправить</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Сотрудники -->
        <div id="employees-page" class="page page-content">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Сотрудники</h1>
                    <p class="text-slate-500 mt-1">Управление командой</p>
                </div>
                <button id="new-employee-btn" class="btn-primary hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Добавить сотрудника
                </button>
            </div>

            <div class="flex gap-4 mb-6">
                <input type="text" id="employees-search" placeholder="Поиск по сотрудникам..." class="input-field flex-1">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" id="employees-show-archived" class="w-4 h-4 rounded"> Показать архив
                </label>
            </div>

            <div id="employees-list" class="grid gap-4"></div>
        </div>

        <!-- Профиль сотрудника -->
        <div id="employee-profile-page" class="page page-content">
            <div class="flex items-center justify-between mb-6">
                <button id="back-to-employees" class="back-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Назад к сотрудникам
                </button>
                <button id="archive-employee-btn" class="btn-secondary hidden">В архив</button>
            </div>

            <div id="employee-profile-data" class="card-static p-6 mb-6">
                <div class="flex items-start gap-6">
                    <div id="employee-profile-avatar" class="avatar avatar-lg text-white" style="background: #64748b"></div>
                    <div class="flex-1">
                        <h1 id="employee-profile-name" class="text-2xl font-bold text-slate-800"></h1>
                        <p id="employee-profile-position" class="text-slate-500"></p>
                        <div class="flex gap-6 mt-4 text-sm">
                            <div>
                                <p class="text-slate-400">Email</p>
                                <p id="employee-profile-email" class="text-slate-700"></p>
                            </div>
                            <div>
                                <p class="text-slate-400">Телефон</p>
                                <p id="employee-profile-phone" class="text-slate-700"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-slate-800 mb-4">Участие в проектах</h3>
                    <div id="employee-projects" class="space-y-4"></div>
                </div>
                <div id="employee-comments">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-slate-800">Комментарии</h3>
                        <button id="add-employee-comment-btn" class="btn-primary text-sm hidden">+ Добавить</button>
                    </div>
                    <div id="employee-comments-list" class="space-y-3"></div>
                </div>
            </div>
        </div>

        <!-- Задачи -->
        <div id="tasks-page" class="page page-content">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Задачи</h1>
                    <p class="text-slate-500 mt-1">Управление задачами</p>
                </div>
                <button id="new-task-btn" class="btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Новая задача
                </button>
            </div>

            <div class="flex gap-4 mb-6">
                <select id="tasks-filter" class="input-field bg-white" style="max-width: 200px;">
                    <option value="all">Все задачи</option>
                    <option value="my">Мои задачи</option>
                    <option value="created">Я постановщик</option>
                </select>
                <select id="tasks-status-filter" class="input-field bg-white" style="max-width: 200px;">
                    <option value="">Все статусы</option>
                    <option value="not_started">Новые</option>
                    <option value="in_progress">В работе</option>
                    <option value="completed">Выполненные</option>
                </select>
            </div>

            <div id="tasks-list" class="grid gap-4"></div>
        </div>

        <!-- Админ-панель -->
        <div id="admin-page" class="page page-content">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-800">Настройки</h1>
                <p class="text-slate-500 mt-1">Управление разделами проектирования</p>
            </div>

            <div class="card-static p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-semibold text-slate-800">Разделы проектирования</h3>
                    <button class="btn-primary text-sm" onclick="ProKBAddDesignSection()">+ Добавить раздел</button>
                </div>
                <div id="design-sections-list" class="space-y-3"></div>
            </div>
        </div>

    </main>
</div>

<!-- Оверлей для мобильного меню -->
<div id="overlay" class="overlay"></div>

<!-- Кнопка мобильного меню -->
<button id="mobile-menu-btn" class="mobile-menu-btn">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<!-- Контейнер для уведомлений -->
<div id="toast-container" class="toast-container"></div>

<!-- ============================================ -->
<!-- МОДАЛЬНЫЕ ОКНА -->
<!-- ============================================ -->

<!-- Модальное окно нового проекта -->
<div id="new-project-modal" class="modal">
    <div class="modal-content w-full max-w-2xl max-h-screen overflow-y-auto">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white z-10">
            <h2 class="text-xl font-bold text-slate-800">Новый проект</h2>
            <button class="modal-close w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="new-project-form" class="p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Название проекта *</label>
                    <input type="text" name="name" class="input-field w-full" placeholder="Жилой дом по адресу..." required>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Шифр проекта</label>
                    <input type="text" name="code" class="input-field w-full" placeholder="2024-001">
                </div>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Адрес объекта</label>
                <input type="text" name="address" class="input-field w-full" placeholder="г. Москва, ул. ..., д. ...">
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Тип работ</label>
                    <select name="type" class="input-field w-full bg-white">
                        <option value="construction">Новое строительство</option>
                        <option value="capital_repair">Капитальный ремонт</option>
                        <option value="reconstruction">Реконструкция</option>
                        <option value="modernization">Модернизация</option>
                        <option value="demolition">Снос</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Срок выполнения</label>
                    <input type="date" name="deadline" class="input-field w-full">
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">ГИП</label>
                    <select name="gip_id" class="input-field w-full bg-white">
                        <option value="">Выберите ГИПа</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Экспертиза</label>
                <select name="expertise" class="input-field w-full bg-white">
                    <option value="none">Не требуется</option>
                    <option value="state">Государственная экспертиза</option>
                    <option value="non_state">Негосударственная экспертиза</option>
                </select>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Описание</label>
                <textarea name="description" class="input-field w-full" rows="2" placeholder="Краткое описание проекта..."></textarea>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Разделы проекта</label>
                <div class="grid grid-cols-4 gap-2" id="project-sections-checkboxes"></div>
            </div>
        </form>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3 sticky bottom-0 bg-white">
            <button type="button" class="btn-secondary modal-close">Отмена</button>
            <button type="submit" form="new-project-form" class="btn-primary">Создать проект</button>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования проекта -->
<div id="edit-project-modal" class="modal">
    <div class="modal-content w-full max-w-2xl max-h-screen overflow-y-auto">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white z-10">
            <h2 class="text-xl font-bold text-slate-800">Редактирование проекта</h2>
            <button class="modal-close w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="edit-project-form" class="p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Название проекта *</label>
                    <input type="text" name="name" class="input-field w-full" required>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Шифр проекта</label>
                    <input type="text" name="code" class="input-field w-full">
                </div>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Адрес объекта</label>
                <input type="text" name="address" class="input-field w-full">
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Тип работ</label>
                    <select name="type" class="input-field w-full bg-white">
                        <option value="construction">Новое строительство</option>
                        <option value="capital_repair">Капитальный ремонт</option>
                        <option value="reconstruction">Реконструкция</option>
                        <option value="modernization">Модернизация</option>
                        <option value="demolition">Снос</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Срок выполнения</label>
                    <input type="date" name="deadline" class="input-field w-full">
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Статус</label>
                    <select name="status" class="input-field w-full bg-white">
                        <option value="in_work">В работе</option>
                        <option value="in_expertise">В экспертизе</option>
                        <option value="completed">Выполнено</option>
                        <option value="archived">Архив</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">ГИП</label>
                    <select name="gip_id" class="input-field w-full bg-white">
                        <option value="">Выберите ГИПа</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Экспертиза</label>
                    <select name="expertise" class="input-field w-full bg-white">
                        <option value="none">Не требуется</option>
                        <option value="state">Государственная экспертиза</option>
                        <option value="non_state">Негосударственная экспертиза</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Описание</label>
                <textarea name="description" class="input-field w-full" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Разделы проекта</label>
                <div class="grid grid-cols-4 gap-2" id="edit-project-sections-checkboxes"></div>
            </div>
        </form>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3 sticky bottom-0 bg-white">
            <button type="button" class="btn-secondary modal-close">Отмена</button>
            <button type="submit" form="edit-project-form" class="btn-primary">Сохранить</button>
        </div>
    </div>
</div>

<!-- Модальное окно нового сотрудника -->
<div id="new-employee-modal" class="modal">
    <div class="modal-content w-full max-w-lg">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Новый сотрудник</h2>
            <button class="modal-close w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="new-employee-form" class="p-6 space-y-5">
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">ФИО *</label>
                <input type="text" name="name" class="input-field w-full" placeholder="Иванов Иван Иванович" required>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Email *</label>
                <input type="email" name="email" class="input-field w-full" placeholder="email@prokb.ru" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Должность</label>
                    <input type="text" name="position" class="input-field w-full" placeholder="Инженер ПГС">
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Телефон</label>
                    <input type="tel" name="phone" class="input-field w-full" placeholder="+7 (___) ___-__-__">
                </div>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Роль</label>
                <select name="role" class="input-field w-full bg-white">
                    <option value="employee">Сотрудник</option>
                    <option value="gip">ГИП</option>
                    <option value="director">Директор</option>
                </select>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Компетенции</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="checkbox-label"><input type="checkbox" name="competencies[]" value="ГП"> ГП</label>
                    <label class="checkbox-label"><input type="checkbox" name="competencies[]" value="АР"> АР</label>
                    <label class="checkbox-label"><input type="checkbox" name="competencies[]" value="КР"> КР</label>
                    <label class="checkbox-label"><input type="checkbox" name="competencies[]" value="КЖ"> КЖ</label>
                    <label class="checkbox-label"><input type="checkbox" name="competencies[]" value="ОВ"> ОВ</label>
                    <label class="checkbox-label"><input type="checkbox" name="competencies[]" value="ВК"> ВК</label>
                    <label class="checkbox-label"><input type="checkbox" name="competencies[]" value="ЭОМ"> ЭОМ</label>
                    <label class="checkbox-label"><input type="checkbox" name="competencies[]" value="ЭС"> ЭС</label>
                    <label class="checkbox-label"><input type="checkbox" name="competencies[]" value="ТС"> ТС</label>
                </div>
            </div>
        </form>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
            <button type="button" class="btn-secondary modal-close">Отмена</button>
            <button type="submit" form="new-employee-form" class="btn-primary">Добавить</button>
        </div>
    </div>
</div>

<!-- Модальное окно новой задачи -->
<div id="new-task-modal" class="modal">
    <div class="modal-content w-full max-w-lg">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Новая задача</h2>
            <button class="modal-close w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="new-task-form" class="p-6 space-y-5">
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Название задачи *</label>
                <input type="text" name="title" class="input-field w-full" placeholder="Подготовить смету" required>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Описание</label>
                <textarea name="description" class="input-field w-full" rows="3" placeholder="Описание задачи..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Проект</label>
                    <select name="project_id" class="input-field w-full bg-white" id="task-project-select">
                        <option value="">Без проекта</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Исполнитель</label>
                    <select name="assignee_id" class="input-field w-full bg-white" id="task-assignee-select">
                        <option value="">Выберите исполнителя</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Срок</label>
                    <input type="date" name="deadline" class="input-field w-full">
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Приоритет</label>
                    <select name="priority" class="input-field w-full bg-white">
                        <option value="low">Низкий</option>
                        <option value="medium" selected>Средний</option>
                        <option value="high">Высокий</option>
                        <option value="critical">Критичный</option>
                    </select>
                </div>
            </div>
        </form>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
            <button type="button" class="btn-secondary modal-close">Отмена</button>
            <button type="submit" form="new-task-form" class="btn-primary">Создать задачу</button>
        </div>
    </div>
</div>

<!-- Модальное окно добавления контакта -->
<div id="add-contact-modal" class="modal">
    <div class="modal-content w-full max-w-lg">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Добавить контактное лицо</h2>
            <button class="modal-close w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="add-contact-form" class="p-6 space-y-5">
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">ФИО *</label>
                <input type="text" name="name" class="input-field w-full" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Должность</label>
                    <input type="text" name="position" class="input-field w-full">
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Организация</label>
                    <input type="text" name="company" class="input-field w-full">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Телефон</label>
                    <input type="tel" name="phone" class="input-field w-full">
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                    <input type="email" name="email" class="input-field w-full">
                </div>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Примечание</label>
                <textarea name="notes" class="input-field w-full" rows="2"></textarea>
            </div>
        </form>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
            <button type="button" class="btn-secondary modal-close">Отмена</button>
            <button type="submit" form="add-contact-form" class="btn-primary">Добавить</button>
        </div>
    </div>
</div>

<!-- Модальное окно добавления блока вводной информации -->
<div id="add-intro-block-modal" class="modal">
    <div class="modal-content w-full max-w-lg">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Добавить блок информации</h2>
            <button class="modal-close w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="add-intro-block-form" class="p-6 space-y-5">
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Тип блока</label>
                <select name="type" class="input-field w-full bg-white" onchange="document.getElementById('file-field').classList.toggle('hidden', this.value !== 'file'); document.getElementById('text-field').classList.toggle('hidden', this.value === 'file')">
                    <option value="text">Текст</option>
                    <option value="file">Файл</option>
                </select>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Название *</label>
                <input type="text" name="title" class="input-field w-full" required>
            </div>
            <div id="text-field" class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Содержимое</label>
                <textarea name="content" class="input-field w-full" rows="4"></textarea>
            </div>
            <div id="file-field" class="form-group hidden">
                <label class="block text-sm font-medium text-slate-700 mb-2">Файл</label>
                <input type="file" id="intro-file-input" name="file" class="input-field w-full">
            </div>
        </form>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
            <button type="button" class="btn-secondary modal-close">Отмена</button>
            <button type="submit" form="add-intro-block-form" class="btn-primary">Добавить</button>
        </div>
    </div>
</div>

<!-- Модальное окно добавления изыскания -->
<div id="add-investigation-modal" class="modal">
    <div class="modal-content w-full max-w-lg">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Добавить изыскание</h2>
            <button class="modal-close w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="add-investigation-form" class="p-6 space-y-5">
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Выберите из списка</label>
                <select name="standard_id" class="input-field w-full bg-white">
                    <option value="">-- Выберите из списка --</option>
                </select>
            </div>
            <div class="text-center text-slate-400 my-3">— или —</div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Другое (введите название)</label>
                <input type="text" name="custom_name" class="input-field w-full" placeholder="Название нестандартного изыскания">
            </div>
        </form>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
            <button type="button" class="btn-secondary modal-close">Отмена</button>
            <button type="submit" form="add-investigation-form" class="btn-primary">Добавить</button>
        </div>
    </div>
</div>

<!-- Модальное окно добавления этапа экспертизы -->
<div id="add-expertise-modal" class="modal">
    <div class="modal-content w-full max-w-lg">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Добавить этап экспертизы</h2>
            <button class="modal-close w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="add-expertise-form" class="p-6 space-y-5">
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Этап экспертизы *</label>
                <select name="stage_id" class="input-field w-full bg-white" required>
                    <option value="">-- Выберите этап --</option>
                </select>
            </div>
        </form>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
            <button type="button" class="btn-secondary modal-close">Отмена</button>
            <button type="submit" form="add-expertise-form" class="btn-primary">Добавить</button>
        </div>
    </div>
</div>

<!-- Модальное окно добавления замечания -->
<div id="add-remark-modal" class="modal">
    <div class="modal-content w-full max-w-lg">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Добавить замечание</h2>
            <button class="modal-close w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="add-remark-form" class="p-6 space-y-5">
            <input type="hidden" name="expertise_id">
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Раздел (опционально)</label>
                <select name="section_id" class="input-field w-full bg-white">
                    <option value="">-- Без привязки к разделу --</option>
                </select>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Содержание замечания</label>
                <textarea name="content" class="input-field w-full" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Файл (опционально)</label>
                <input type="file" id="remark-file-input" name="file" class="input-field w-full">
            </div>
        </form>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
            <button type="button" class="btn-secondary modal-close">Отмена</button>
            <button type="submit" form="add-remark-form" class="btn-primary">Добавить</button>
        </div>
    </div>
</div>

<!-- Модальное окно комментария к сотруднику -->
<div id="employee-comment-modal" class="modal">
    <div class="modal-content w-full max-w-lg">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Добавить комментарий</h2>
            <button class="modal-close w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="employee-comment-form" class="p-6 space-y-5">
            <div class="form-group">
                <label class="block text-sm font-medium text-slate-700 mb-2">Комментарий *</label>
                <textarea name="content" class="input-field w-full" rows="4" placeholder="Введите комментарий..." required></textarea>
            </div>
            <p class="text-xs text-slate-400">Комментарий виден только директору и ГИПам</p>
        </form>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
            <button type="button" class="btn-secondary modal-close">Отмена</button>
            <button type="submit" form="employee-comment-form" class="btn-primary">Добавить</button>
        </div>
    </div>
</div>

<?php get_footer(); ?>
