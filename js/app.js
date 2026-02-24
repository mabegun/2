/**
 * Проектное Бюро - Frontend Application v2.0
 * 
 * @package ProKB
 */

(function() {
    'use strict';

    // ============================================================================
    // ГЛОБАЛЬНЫЕ ПЕРЕМЕННЫЕ
    // ============================================================================

    const App = {
        user: null,
        projects: [],
        employees: [],
        tasks: [],
        currentProject: null,
        currentSection: null,
        currentEmployee: null,
        designSections: [],
        standardInvestigations: [],
        expertiseStages: [],
        currentProjectStatus: 'in_work',

        elements: {},

        statusLabels: {
            not_started: { label: 'Не начато', class: 'status-not-started' },
            in_progress: { label: 'В работе', class: 'status-in-progress' },
            completed: { label: 'Готово', class: 'status-completed' },
            revision: { label: 'На доработке', class: 'status-revision' }
        },

        projectStatusLabels: {
            in_work: { label: 'В работе', class: 'status-in-progress' },
            in_expertise: { label: 'В экспертизе', class: 'status-expertise' },
            completed: { label: 'Выполнено', class: 'status-completed' },
            archived: { label: 'Архив', class: 'status-archived' }
        },

        expertiseSectionStatusLabels: {
            uploaded_for_review: { label: 'Загружен на проверку', class: 'status-in-progress' },
            remarks_received: { label: 'Замечания получены', class: 'status-revision' },
            remarks_in_progress: { label: 'Замечания в работе', class: 'status-expertise' },
            accepted_by_expert: { label: 'Принят экспертом', class: 'status-completed' }
        },

        investigationStatusLabels: {
            not_started: { label: 'Не начато', class: 'status-not-started' },
            in_progress: { label: 'В работе', class: 'status-in-progress' },
            completed: { label: 'Завершено', class: 'status-completed' }
        },

        priorityLabels: {
            low: { label: 'Низкий', class: 'priority-low' },
            medium: { label: 'Средний', class: 'priority-medium' },
            high: { label: 'Высокий', class: 'priority-high' },
            critical: { label: 'Критичный', class: 'priority-critical' }
        },

        projectTypes: {
            construction: 'Новое строительство',
            capital_repair: 'Капитальный ремонт',
            reconstruction: 'Реконструкция',
            modernization: 'Модернизация',
            demolition: 'Снос'
        }
    };

    // ============================================================================
    // ИНИЦИАЛИЗАЦИЯ
    // ============================================================================

    function init() {
        cacheElements();
        bindEvents();
        checkAuth();
    }

    function cacheElements() {
        App.elements = {
            // Страницы
            loginPage: document.getElementById('login-page'),
            app: document.getElementById('app'),
            pages: document.querySelectorAll('.page'),
            
            // Форма входа
            loginForm: document.getElementById('login-form'),
            loginEmail: document.getElementById('login-email'),
            loginPassword: document.getElementById('login-password'),
            loginError: document.getElementById('login-error'),
            loginBtnText: document.getElementById('login-btn-text'),
            loginBtnLoader: document.getElementById('login-btn-loader'),
            
            // Информация о пользователе
            userAvatar: document.getElementById('user-avatar'),
            userName: document.getElementById('user-name'),
            userPosition: document.getElementById('user-position'),
            welcomeText: document.getElementById('welcome-text'),
            
            // Навигация
            sidebarItems: document.querySelectorAll('.sidebar-item[data-nav]'),
            employeesNav: document.getElementById('employees-nav'),
            adminNav: document.getElementById('admin-nav'),
            newProjectBtn: document.getElementById('new-project-btn'),
            newEmployeeBtn: document.getElementById('new-employee-btn'),
            newTaskBtn: document.getElementById('new-task-btn'),
            logoutBtn: document.getElementById('logout-btn'),
            
            // Статистика
            statTotalProjects: document.getElementById('stat-total-projects'),
            statInProgress: document.getElementById('stat-in-progress'),
            statCompletedSections: document.getElementById('stat-completed-sections'),
            statEmployees: document.getElementById('stat-employees'),
            
            // Контейнеры
            dashboardProjects: document.getElementById('dashboard-projects'),
            dashboardTasks: document.getElementById('dashboard-tasks'),
            projectsList: document.getElementById('projects-list'),
            employeesList: document.getElementById('employees-list'),
            tasksList: document.getElementById('tasks-list'),
            designSectionsList: document.getElementById('design-sections-list'),
            
            // Вкладки статусов проектов
            projectStatusTabs: document.getElementById('project-status-tabs'),
            
            // Проект
            projectTitle: document.getElementById('project-title'),
            projectAddress: document.getElementById('project-address'),
            projectInfo: document.getElementById('project-info'),
            projectSections: document.getElementById('project-sections'),
            projectTabs: document.querySelectorAll('.project-tab'),
            projectTabContents: document.querySelectorAll('.project-tab-content'),
            backToProjects: document.getElementById('back-to-projects'),
            editProjectBtn: document.getElementById('edit-project-btn'),
            archiveProjectBtn: document.getElementById('archive-project-btn'),
            
            // Вводная информация
            introBlocks: document.getElementById('intro-blocks'),
            addIntroBlockBtn: document.getElementById('add-intro-block-btn'),
            
            // Контактные лица
            contactPersons: document.getElementById('contact-persons'),
            addContactBtn: document.getElementById('add-contact-btn'),
            
            // Изыскания
            investigationsList: document.getElementById('investigations-list'),
            addInvestigationBtn: document.getElementById('add-investigation-btn'),
            
            // Экспертиза
            expertiseContainer: document.getElementById('expertise-container'),
            addExpertiseBtn: document.getElementById('add-expertise-btn'),
            
            // Раздел
            sectionTitle: document.getElementById('section-title'),
            sectionProjectName: document.getElementById('section-project-name'),
            sectionInfo: document.getElementById('section-info'),
            sectionMessages: document.getElementById('section-messages'),
            backToProject: document.getElementById('back-to-project'),
            messageForm: document.getElementById('message-form'),
            messageContent: document.getElementById('message-content'),
            messageCritical: document.getElementById('message-critical'),
            
            // Профиль сотрудника
            employeeProfile: document.getElementById('employee-profile-page'),
            employeeProfileData: document.getElementById('employee-profile-data'),
            employeeComments: document.getElementById('employee-comments'),
            addEmployeeCommentBtn: document.getElementById('add-employee-comment-btn'),
            backToEmployees: document.getElementById('back-to-employees'),
            
            // Фильтры
            projectsSearch: document.getElementById('projects-search'),
            projectsFilterType: document.getElementById('projects-filter-type'),
            employeesSearch: document.getElementById('employees-search'),
            employeesShowArchived: document.getElementById('employees-show-archived'),
            tasksFilter: document.getElementById('tasks-filter'),
            tasksStatusFilter: document.getElementById('tasks-status-filter'),
            
            // Модальные окна
            newProjectModal: document.getElementById('new-project-modal'),
            editProjectModal: document.getElementById('edit-project-modal'),
            newEmployeeModal: document.getElementById('new-employee-modal'),
            newTaskModal: document.getElementById('new-task-modal'),
            addContactModal: document.getElementById('add-contact-modal'),
            addIntroBlockModal: document.getElementById('add-intro-block-modal'),
            addInvestigationModal: document.getElementById('add-investigation-modal'),
            addExpertiseModal: document.getElementById('add-expertise-modal'),
            addRemarkModal: document.getElementById('add-remark-modal'),
            employeeCommentModal: document.getElementById('employee-comment-modal'),
            
            newProjectForm: document.getElementById('new-project-form'),
            editProjectForm: document.getElementById('edit-project-form'),
            newEmployeeForm: document.getElementById('new-employee-form'),
            newTaskForm: document.getElementById('new-task-form'),
            addContactForm: document.getElementById('add-contact-form'),
            addIntroBlockForm: document.getElementById('add-intro-block-form'),
            addInvestigationForm: document.getElementById('add-investigation-form'),
            addExpertiseForm: document.getElementById('add-expertise-form'),
            addRemarkForm: document.getElementById('add-remark-form'),
            employeeCommentForm: document.getElementById('employee-comment-form'),
            
            // Мобильное меню
            sidebar: document.getElementById('sidebar'),
            overlay: document.getElementById('overlay'),
            mobileMenuBtn: document.getElementById('mobile-menu-btn'),
            
            // Toast
            toastContainer: document.getElementById('toast-container'),
            
            // Tasks badge
            tasksBadge: document.getElementById('tasks-badge')
        };
    }

    function bindEvents() {
        // Форма входа
        App.elements.loginForm.addEventListener('submit', handleLogin);
        
        // Выход
        App.elements.logoutBtn.addEventListener('click', handleLogout);
        
        // Навигация
        App.elements.sidebarItems.forEach(item => {
            item.addEventListener('click', () => showPage(item.dataset.nav));
        });
        
        // Кнопки создания
        if (App.elements.newProjectBtn) {
            App.elements.newProjectBtn.addEventListener('click', () => showModal('new-project-modal'));
        }
        if (App.elements.newEmployeeBtn) {
            App.elements.newEmployeeBtn.addEventListener('click', () => showModal('new-employee-modal'));
        }
        if (App.elements.newTaskBtn) {
            App.elements.newTaskBtn.addEventListener('click', () => openNewTaskModal());
        }
        
        // Формы создания
        if (App.elements.newProjectForm) {
            App.elements.newProjectForm.addEventListener('submit', handleCreateProject);
        }
        if (App.elements.editProjectForm) {
            App.elements.editProjectForm.addEventListener('submit', handleUpdateProject);
        }
        if (App.elements.newEmployeeForm) {
            App.elements.newEmployeeForm.addEventListener('submit', handleCreateEmployee);
        }
        if (App.elements.newTaskForm) {
            App.elements.newTaskForm.addEventListener('submit', handleCreateTask);
        }
        if (App.elements.addContactForm) {
            App.elements.addContactForm.addEventListener('submit', handleAddContact);
        }
        if (App.elements.addIntroBlockForm) {
            App.elements.addIntroBlockForm.addEventListener('submit', handleAddIntroBlock);
        }
        if (App.elements.addInvestigationForm) {
            App.elements.addInvestigationForm.addEventListener('submit', handleAddInvestigation);
        }
        if (App.elements.addExpertiseForm) {
            App.elements.addExpertiseForm.addEventListener('submit', handleAddExpertise);
        }
        if (App.elements.addRemarkForm) {
            App.elements.addRemarkForm.addEventListener('submit', handleAddRemark);
        }
        if (App.elements.employeeCommentForm) {
            App.elements.employeeCommentForm.addEventListener('submit', handleAddEmployeeComment);
        }
        
        // Сообщения
        if (App.elements.messageForm) {
            App.elements.messageForm.addEventListener('submit', handleSendMessage);
        }
        
        // Навигация назад
        if (App.elements.backToProjects) {
            App.elements.backToProjects.addEventListener('click', () => showPage('projects'));
        }
        if (App.elements.backToProject) {
            App.elements.backToProject.addEventListener('click', backToProject);
        }
        if (App.elements.backToEmployees) {
            App.elements.backToEmployees.addEventListener('click', () => showPage('employees'));
        }
        
        // Фильтры
        if (App.elements.projectsSearch) {
            App.elements.projectsSearch.addEventListener('input', debounce(renderProjects, 300));
        }
        if (App.elements.projectsFilterType) {
            App.elements.projectsFilterType.addEventListener('change', renderProjects);
        }
        if (App.elements.employeesSearch) {
            App.elements.employeesSearch.addEventListener('input', debounce(renderEmployees, 300));
        }
        if (App.elements.employeesShowArchived) {
            App.elements.employeesShowArchived.addEventListener('change', loadEmployees);
        }
        if (App.elements.tasksFilter) {
            App.elements.tasksFilter.addEventListener('change', loadTasks);
        }
        if (App.elements.tasksStatusFilter) {
            App.elements.tasksStatusFilter.addEventListener('change', loadTasks);
        }
        
        // Вкладки проекта
        if (App.elements.projectTabs) {
            App.elements.projectTabs.forEach(tab => {
                tab.addEventListener('click', () => switchProjectTab(tab.dataset.tab));
            });
        }
        
        // Вкладки статусов проектов
        if (App.elements.projectStatusTabs) {
            document.querySelectorAll('.status-tab').forEach(tab => {
                tab.addEventListener('click', () => switchProjectStatusTab(tab.dataset.status));
            });
        }
        
        // Модальные окна
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', closeModals);
        });
        
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModals();
            });
        });
        
        // Мобильное меню
        if (App.elements.mobileMenuBtn) {
            App.elements.mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        }
        if (App.elements.overlay) {
            App.elements.overlay.addEventListener('click', closeMobileMenu);
        }
        
        // Клавиша Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModals();
                closeMobileMenu();
            }
        });
    }

    // ============================================================================
    // АВТОРИЗАЦИЯ
    // ============================================================================

    function checkAuth() {
        apiRequest('check_auth')
            .then(response => {
                if (response.success && response.data.authenticated) {
                    App.user = response.data.user;
                    showApp();
                } else {
                    showLoginPage();
                }
            })
            .catch(() => showLoginPage());
    }

    function handleLogin(e) {
        e.preventDefault();
        
        const email = App.elements.loginEmail.value.trim();
        const password = App.elements.loginPassword.value;
        
        if (!email || !password) {
            showLoginError('Введите email и пароль');
            return;
        }
        
        setLoading(true);
        hideLoginError();
        
        apiRequest('login', { email, password })
            .then(response => {
                if (response.success) {
                    App.user = response.data.user;
                    showApp();
                    showToast('Добро пожаловать!', 'success');
                } else {
                    showLoginError(response.data.message || 'Ошибка авторизации');
                }
            })
            .catch(() => showLoginError('Ошибка соединения'))
            .finally(() => setLoading(false));
    }

    function handleLogout() {
        apiRequest('logout')
            .then(() => {
                App.user = null;
                showLoginPage();
                showToast('Вы вышли из системы', 'info');
            })
            .catch(() => showToast('Ошибка выхода', 'error'));
    }

    function showLoginPage() {
        App.elements.loginPage.classList.remove('hidden');
        App.elements.app.classList.add('hidden');
        App.elements.loginForm.reset();
    }

    function showApp() {
        App.elements.loginPage.classList.add('hidden');
        App.elements.app.classList.remove('hidden');
        
        App.elements.userAvatar.textContent = App.user.initials || App.user.name.charAt(0);
        App.elements.userName.textContent = App.user.name;
        App.elements.userPosition.textContent = App.user.position;
        App.elements.welcomeText.textContent = 'Добро пожаловать, ' + App.user.name;
        
        const isManagement = App.user.role === 'director' || App.user.role === 'gip';
        const isDirector = App.user.role === 'director';
        
        if (App.elements.employeesNav) {
            App.elements.employeesNav.classList.toggle('hidden', !isManagement);
        }
        if (App.elements.adminNav) {
            App.elements.adminNav.classList.toggle('hidden', !isDirector);
        }
        if (App.elements.newProjectBtn) {
            App.elements.newProjectBtn.classList.toggle('hidden', !isManagement);
        }
        if (App.elements.newEmployeeBtn) {
            App.elements.newEmployeeBtn.classList.toggle('hidden', !isDirector);
        }
        
        loadDashboardStats();
        loadDesignSections();
        loadStandardInvestigations();
        loadExpertiseStages();
        loadProjects();
        loadEmployees();
        loadTasks();
        
        showPage('dashboard');
    }

    function showLoginError(message) {
        App.elements.loginError.querySelector('span').textContent = message;
        App.elements.loginError.classList.remove('hidden');
    }

    function hideLoginError() {
        App.elements.loginError.classList.add('hidden');
    }

    function setLoading(loading) {
        App.elements.loginBtnText.classList.toggle('hidden', loading);
        App.elements.loginBtnLoader.classList.toggle('hidden', !loading);
    }

    // ============================================================================
    // НАВИГАЦИЯ
    // ============================================================================

    function showPage(pageName) {
        App.elements.pages.forEach(page => page.classList.remove('active'));
        
        const page = document.getElementById(pageName + '-page');
        if (page) page.classList.add('active');
        
        App.elements.sidebarItems.forEach(item => {
            const isActive = item.dataset.nav === pageName || 
                           (item.dataset.nav === 'projects' && (pageName === 'project' || pageName === 'section'));
            item.classList.toggle('active', isActive);
        });
        
        closeMobileMenu();
    }

    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('active');
    }

    function closeModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
    }

    function toggleMobileMenu() {
        App.elements.sidebar.classList.toggle('open');
        App.elements.overlay.classList.toggle('active');
    }

    function closeMobileMenu() {
        App.elements.sidebar.classList.remove('open');
        App.elements.overlay.classList.remove('active');
    }

    function switchProjectTab(tabName) {
        App.elements.projectTabs.forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabName);
        });
        
        App.elements.projectTabContents.forEach(content => {
            content.classList.toggle('active', content.id === 'tab-' + tabName);
        });
    }

    function switchProjectStatusTab(status) {
        document.querySelectorAll('.status-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.status === status);
        });
        
        App.currentProjectStatus = status;
        renderProjects();
    }

    // ============================================================================
    // ДАШБОРД
    // ============================================================================

    function loadDashboardStats() {
        apiRequest('get_dashboard_stats')
            .then(response => {
                if (response.success) {
                    const stats = response.data;
                    App.elements.statTotalProjects.textContent = stats.total_projects || 0;
                    App.elements.statInProgress.textContent = stats.in_progress || 0;
                    App.elements.statCompletedSections.innerHTML = 
                        `${stats.completed_sections || 0}<span class="text-lg text-slate-400">/${stats.total_sections || 0}</span>`;
                    App.elements.statEmployees.textContent = stats.total_employees || 0;
                    
                    if (stats.my_tasks > 0) {
                        App.elements.tasksBadge.textContent = stats.my_tasks;
                        App.elements.tasksBadge.classList.remove('hidden');
                    } else {
                        App.elements.tasksBadge.classList.add('hidden');
                    }
                }
            })
            .catch(() => {});
    }

    function renderDashboard() {
        const activeProjects = App.projects.filter(p => p.status !== 'archived');
        const projectsHtml = activeProjects.slice(0, 5).map(project => `
            <div class="p-4 rounded-xl border border-slate-100 hover:bg-slate-50 cursor-pointer transition-all" 
                 onclick="ProKBOpenProject(${project.id})">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-800 truncate">${escapeHtml(project.name)}</p>
                        <p class="text-sm text-slate-500 truncate">${escapeHtml(project.address || '')}</p>
                    </div>
                    <span class="badge ${App.projectStatusLabels[project.status]?.class || 'status-in-progress'} ml-2 flex-shrink-0">
                        ${App.projectStatusLabels[project.status]?.label || project.status}
                    </span>
                </div>
                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: ${project.progress}%"></div>
                </div>
                <p class="text-xs text-slate-400 mt-2">${project.sections_completed} из ${project.sections_total} разделов готово</p>
            </div>
        `).join('');
        
        App.elements.dashboardProjects.innerHTML = projectsHtml || 
            '<div class="empty-state"><p>Нет активных проектов</p></div>';
        
        const myTasks = App.tasks.filter(t => t.assignee_id == App.user.id && t.status !== 'completed').slice(0, 5);
        const tasksHtml = myTasks.map(task => `
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-sm font-medium text-slate-700">${escapeHtml(task.title)}</p>
                <div class="flex items-center justify-between mt-2">
                    <span class="badge ${App.statusLabels[task.status]?.class || ''}">${App.statusLabels[task.status]?.label || task.status}</span>
                    <span class="text-xs text-slate-400">${task.deadline ? 'до ' + formatDate(task.deadline) : ''}</span>
                </div>
            </div>
        `).join('');
        
        App.elements.dashboardTasks.innerHTML = tasksHtml || 
            '<div class="empty-state"><p>Нет назначенных задач</p></div>';
    }

    // ============================================================================
    // ПРОЕКТЫ
    // ============================================================================

    function loadProjects() {
        apiRequest('get_projects', { status: App.currentProjectStatus })
            .then(response => {
                if (response.success) {
                    App.projects = response.data;
                    renderProjects();
                    renderDashboard();
                    updateTaskSelects();
                }
            })
            .catch(() => showToast('Ошибка загрузки проектов', 'error'));
    }

    function renderProjects() {
        let filtered = App.projects.filter(p => p.status === App.currentProjectStatus);
        
        const search = App.elements.projectsSearch?.value.toLowerCase();
        if (search) {
            filtered = filtered.filter(p => 
                p.name.toLowerCase().includes(search) || 
                (p.address && p.address.toLowerCase().includes(search)) ||
                (p.code && p.code.toLowerCase().includes(search))
            );
        }
        
        const type = App.elements.projectsFilterType?.value;
        if (type) {
            filtered = filtered.filter(p => p.type === type);
        }
        
        const html = filtered.map(project => {
            const statusLabel = App.projectStatusLabels[project.status];
            
            return `
                <div onclick="ProKBOpenProject(${project.id})" class="card p-6 cursor-pointer">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                ${project.code ? `<span class="text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded">${escapeHtml(project.code)}</span>` : ''}
                                <h3 class="text-lg font-semibold text-slate-800">${escapeHtml(project.name)}</h3>
                            </div>
                            <p class="text-slate-500 text-sm">${escapeHtml(project.address || '')}</p>
                        </div>
                        <span class="badge ${statusLabel?.class || ''} flex-shrink-0 ml-2">${statusLabel?.label || project.status}</span>
                    </div>
                    <div class="flex items-center gap-4 text-sm text-slate-500 mb-4">
                        <span>${App.projectTypes[project.type] || project.type}</span>
                        ${project.gip ? `<span class="text-slate-300">|</span><span>ГИП: ${project.gip.name}</span>` : ''}
                    </div>
                    <div class="flex gap-2 mb-4">
                        <span class="badge status-completed">${project.sections_completed} готово</span>
                        <span class="badge status-in-progress">${project.sections_in_progress} в работе</span>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        ${project.sections.slice(0, 6).map(section => `
                            <span class="text-xs px-2 py-1 rounded-lg ${section.status === 'completed' ? 'bg-green-50 text-green-600' : section.status === 'in_progress' ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-500'}">${section.code}</span>
                        `).join('')}
                        ${project.sections.length > 6 ? `<span class="text-xs px-2 py-1 rounded-lg bg-slate-100 text-slate-500">+${project.sections.length - 6}</span>` : ''}
                    </div>
                </div>
            `;
        }).join('');
        
        App.elements.projectsList.innerHTML = html || 
            '<div class="empty-state"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg><p>Проекты не найдены</p></div>';
    }

    function openProject(projectId) {
        const project = App.projects.find(p => p.id === projectId);
        if (!project) return;
        
        App.currentProject = project;
        
        apiRequest('get_project', { project_id: projectId })
            .then(response => {
                if (response.success) {
                    App.currentProject = response.data;
                    renderProjectPage();
                    showPage('project');
                }
            })
            .catch(() => showToast('Ошибка загрузки проекта', 'error'));
    }

    function renderProjectPage() {
        const project = App.currentProject;
        const isManagement = App.user.role === 'director' || App.user.role === 'gip';
        const statusLabel = App.projectStatusLabels[project.status];
        
        App.elements.projectTitle.textContent = project.name;
        App.elements.projectAddress.textContent = project.address || '';
        
        // Кнопки управления
        if (App.elements.editProjectBtn) {
            App.elements.editProjectBtn.classList.toggle('hidden', !isManagement);
            App.elements.editProjectBtn.onclick = () => openEditProjectModal();
        }
        if (App.elements.archiveProjectBtn) {
            App.elements.archiveProjectBtn.classList.toggle('hidden', !isManagement);
            if (project.status === 'archived') {
                App.elements.archiveProjectBtn.textContent = 'Восстановить';
                App.elements.archiveProjectBtn.onclick = () => restoreProject();
            } else {
                App.elements.archiveProjectBtn.textContent = 'В архив';
                App.elements.archiveProjectBtn.onclick = () => archiveProject();
            }
        }
        
        // Информация о проекте
        renderProjectInfo();
        
        // Разделы
        renderProjectSections();
        
        // Вводная информация
        renderIntroBlocks();
        
        // Контактные лица
        renderContactPersons();
        
        // Изыскания
        renderInvestigations();
        
        // Экспертиза
        renderExpertise();
        
        // Сброс на первую вкладку
        switchProjectTab('sections');
    }

    function renderProjectInfo() {
        const project = App.currentProject;
        const isManagement = App.user.role === 'director' || App.user.role === 'gip';
        
        App.elements.projectInfo.innerHTML = `
            <div class="grid grid-cols-4 gap-6">
                <div>
                    <p class="text-sm text-slate-500 mb-1">Шифр проекта</p>
                    <p class="font-medium text-slate-800">${escapeHtml(project.code || '—')}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Тип работ</p>
                    <p class="font-medium text-slate-800">${App.projectTypes[project.type] || project.type}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Статус</p>
                    <p class="font-medium text-slate-800">${App.projectStatusLabels[project.status]?.label || project.status}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">ГИП</p>
                    <p class="font-medium text-slate-800">${project.gip ? project.gip.name : '—'}</p>
                </div>
            </div>
            ${project.description ? `<div class="mt-4 pt-4 border-t border-slate-100"><p class="text-sm text-slate-500 mb-1">Описание</p><p class="text-slate-700">${escapeHtml(project.description)}</p></div>` : ''}
        `;
    }

    function renderProjectSections() {
        const project = App.currentProject;
        
        App.elements.projectSections.innerHTML = project.sections.map(section => `
            <div onclick="ProKBOpenSection(${section.id})" class="card p-5 cursor-pointer">
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-4">
                        <div class="section-badge ${section.status === 'completed' ? 'bg-green-50 text-green-600' : section.status === 'in_progress' ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-500'}">
                            ${section.code}
                        </div>
                        <div>
                            <p class="font-medium text-slate-800">${section.description || section.code}</p>
                            ${section.assignee ? `<p class="text-sm text-slate-500 mt-1">Ответственный: ${section.assignee.name}</p>` : ''}
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="badge ${App.statusLabels[section.status]?.class || ''}">${App.statusLabels[section.status]?.label || section.status}</span>
                    </div>
                </div>
                ${project.status === 'in_expertise' && section.expertise_status ? `
                    <div class="mt-3 pt-3 border-t border-slate-100">
                        <span class="text-xs text-slate-500">Экспертиза: </span>
                        <span class="badge ${App.expertiseSectionStatusLabels[section.expertise_status]?.class || ''} text-xs">
                            ${App.expertiseSectionStatusLabels[section.expertise_status]?.label || section.expertise_status}
                        </span>
                    </div>
                ` : ''}
            </div>
        `).join('') || '<div class="empty-state"><p>Нет разделов</p></div>';
    }

    function renderIntroBlocks() {
        const project = App.currentProject;
        const isManagement = App.user.role === 'director' || App.user.role === 'gip';
        
        if (!App.elements.introBlocks) return;
        
        App.elements.introBlocks.innerHTML = project.intro_blocks && project.intro_blocks.length > 0 
            ? project.intro_blocks.map(block => `
                <div class="card p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-medium text-slate-800">${escapeHtml(block.title)}</h4>
                        ${isManagement ? `<button onclick="ProKBDeleteIntroBlock(${block.id})" class="text-red-500 hover:text-red-700 text-sm">Удалить</button>` : ''}
                    </div>
                    ${block.type === 'file' 
                        ? `<a href="${block.file_path}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            ${escapeHtml(block.file_name || 'Файл')}
                           </a>`
                        : `<p class="text-slate-600 whitespace-pre-wrap">${escapeHtml(block.content || '')}</p>`
                    }
                </div>
            `).join('')
            : '<div class="empty-state"><p>Нет вводной информации</p></div>';
        
        // Кнопка добавления
        if (App.elements.addIntroBlockBtn) {
            App.elements.addIntroBlockBtn.onclick = () => showModal('add-intro-block-modal');
        }
    }

    function renderContactPersons() {
        const project = App.currentProject;
        const isManagement = App.user.role === 'director' || App.user.role === 'gip';
        
        if (!App.elements.contactPersons) return;
        
        App.elements.contactPersons.innerHTML = project.contact_persons && project.contact_persons.length > 0
            ? project.contact_persons.map(contact => `
                <div class="card p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-medium text-slate-800">${escapeHtml(contact.name)}</h4>
                            ${contact.position ? `<p class="text-sm text-slate-500">${escapeHtml(contact.position)}</p>` : ''}
                            ${contact.company ? `<p class="text-sm text-slate-400">${escapeHtml(contact.company)}</p>` : ''}
                        </div>
                        ${isManagement ? `<button onclick="ProKBDeleteContact(${contact.id})" class="text-red-500 hover:text-red-700 text-sm">Удалить</button>` : ''}
                    </div>
                    <div class="mt-3 flex gap-4 text-sm">
                        ${contact.phone ? `<a href="tel:${contact.phone}" class="text-blue-600 hover:underline">${escapeHtml(contact.phone)}</a>` : ''}
                        ${contact.email ? `<a href="mailto:${contact.email}" class="text-blue-600 hover:underline">${escapeHtml(contact.email)}</a>` : ''}
                    </div>
                    ${contact.notes ? `<p class="mt-2 text-sm text-slate-500 italic">${escapeHtml(contact.notes)}</p>` : ''}
                </div>
            `).join('')
            : '<div class="empty-state"><p>Нет контактных лиц</p></div>';
        
        if (App.elements.addContactBtn) {
            App.elements.addContactBtn.onclick = () => showModal('add-contact-modal');
        }
    }

    function renderInvestigations() {
        const project = App.currentProject;
        const isManagement = App.user.role === 'director' || App.user.role === 'gip';
        
        if (!App.elements.investigationsList) return;
        
        // Показываем вкладку только если есть изыскания или права управления
        const investigationsTab = document.querySelector('[data-tab="investigations"]');
        if (investigationsTab) {
            const hasInvestigations = project.investigations && project.investigations.length > 0;
            investigationsTab.style.display = hasInvestigations || isManagement ? '' : 'none';
        }
        
        if (!project.investigations || project.investigations.length === 0) {
            App.elements.investigationsList.innerHTML = '<div class="empty-state"><p>Нет изысканий</p></div>';
        } else {
            App.elements.investigationsList.innerHTML = project.investigations.map(inv => `
                <div class="card p-5">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="font-semibold text-slate-800">${escapeHtml(inv.name)}</h4>
                            ${inv.is_custom ? '<span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded ml-2">Иное</span>' : ''}
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge ${App.investigationStatusLabels[inv.status]?.class || ''}">${App.investigationStatusLabels[inv.status]?.label || inv.status}</span>
                            ${isManagement ? `<button onclick="ProKBDeleteInvestigation(${inv.id})" class="text-red-500 hover:text-red-700 text-sm ml-2">Удалить</button>` : ''}
                        </div>
                    </div>
                    ${isManagement ? `
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <label class="block text-slate-500 mb-1">Подрядчик</label>
                                <input type="text" class="input-field" value="${escapeHtml(inv.contractor_name || '')}" 
                                    onchange="ProKBUpdateInvestigation(${inv.id}, 'contractor_name', this.value)">
                            </div>
                            <div>
                                <label class="block text-slate-500 mb-1">Контакт подрядчика</label>
                                <input type="text" class="input-field" value="${escapeHtml(inv.contractor_contact || '')}"
                                    onchange="ProKBUpdateInvestigation(${inv.id}, 'contractor_contact', this.value)">
                            </div>
                            <div>
                                <label class="block text-slate-500 mb-1">Телефон</label>
                                <input type="text" class="input-field" value="${escapeHtml(inv.contractor_phone || '')}"
                                    onchange="ProKBUpdateInvestigation(${inv.id}, 'contractor_phone', this.value)">
                            </div>
                            <div>
                                <label class="block text-slate-500 mb-1">Email</label>
                                <input type="email" class="input-field" value="${escapeHtml(inv.contractor_email || '')}"
                                    onchange="ProKBUpdateInvestigation(${inv.id}, 'contractor_email', this.value)">
                            </div>
                            <div>
                                <label class="block text-slate-500 mb-1">Дата начала</label>
                                <input type="date" class="input-field" value="${inv.start_date || ''}"
                                    onchange="ProKBUpdateInvestigation(${inv.id}, 'start_date', this.value)">
                            </div>
                            <div>
                                <label class="block text-slate-500 mb-1">Дата окончания</label>
                                <input type="date" class="input-field" value="${inv.end_date || ''}"
                                    onchange="ProKBUpdateInvestigation(${inv.id}, 'end_date', this.value)">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-slate-500 mb-1">Статус</label>
                                <select class="input-field bg-white" onchange="ProKBUpdateInvestigation(${inv.id}, 'status', this.value)">
                                    <option value="not_started" ${inv.status === 'not_started' ? 'selected' : ''}>Не начато</option>
                                    <option value="in_progress" ${inv.status === 'in_progress' ? 'selected' : ''}>В работе</option>
                                    <option value="completed" ${inv.status === 'completed' ? 'selected' : ''}>Завершено</option>
                                </select>
                            </div>
                        </div>
                    ` : `
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            ${inv.contractor_name ? `<div><p class="text-slate-500">Подрядчик:</p><p class="font-medium">${escapeHtml(inv.contractor_name)}</p></div>` : ''}
                            ${inv.contractor_phone ? `<div><p class="text-slate-500">Телефон:</p><p class="font-medium">${escapeHtml(inv.contractor_phone)}</p></div>` : ''}
                            ${inv.start_date ? `<div><p class="text-slate-500">Начало:</p><p class="font-medium">${formatDate(inv.start_date)}</p></div>` : ''}
                            ${inv.end_date ? `<div><p class="text-slate-500">Окончание:</p><p class="font-medium">${formatDate(inv.end_date)}</p></div>` : ''}
                        </div>
                    `}
                </div>
            `).join('');
        }
        
        if (App.elements.addInvestigationBtn) {
            App.elements.addInvestigationBtn.classList.toggle('hidden', !isManagement);
            App.elements.addInvestigationBtn.onclick = () => openAddInvestigationModal();
        }
    }

    function renderExpertise() {
        const project = App.currentProject;
        const isManagement = App.user.role === 'director' || App.user.role === 'gip';
        
        if (!App.elements.expertiseContainer) return;
        
        // Показываем вкладку только если проект в экспертизе
        const expertiseTab = document.querySelector('[data-tab="expertise"]');
        if (expertiseTab) {
            expertiseTab.style.display = project.status === 'in_expertise' ? '' : 'none';
        }
        
        if (project.status !== 'in_expertise') {
            App.elements.expertiseContainer.innerHTML = '<div class="empty-state"><p>Проект не находится в экспертизе</p></div>';
            return;
        }
        
        // Загрузка положительного заключения
        let positiveConclusionHtml = '';
        if (project.positive_conclusion_file) {
            positiveConclusionHtml = `
                <div class="card p-4 bg-green-50 border-green-200">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-medium text-green-800">Положительное заключение</p>
                            <a href="${project.positive_conclusion_file}" target="_blank" class="text-green-600 hover:underline text-sm">${escapeHtml(project.positive_conclusion_name || 'Скачать')}</a>
                        </div>
                    </div>
                </div>
            `;
        } else if (isManagement) {
            positiveConclusionHtml = `
                <div class="card p-4 border-2 border-dashed border-slate-200">
                    <p class="text-sm text-slate-500 mb-3">Загрузить положительное заключение:</p>
                    <input type="file" id="positive-conclusion-file" class="input-field" accept=".pdf,.doc,.docx">
                    <button onclick="ProKBUploadPositiveConclusion()" class="btn-primary mt-2">Загрузить</button>
                </div>
            `;
        }
        
        // Статусы разделов по экспертизе
        const sectionsExpertiseHtml = `
            <div class="card-static p-4 mb-6">
                <h4 class="font-semibold text-slate-800 mb-4">Статусы разделов в экспертизе</h4>
                <div class="grid gap-3">
                    ${project.sections.map(section => `
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="font-medium text-slate-700">${section.code}</span>
                                <span class="text-sm text-slate-500">${section.description || ''}</span>
                            </div>
                            ${isManagement ? `
                                <select class="input-field bg-white text-sm" style="max-width: 200px;"
                                    onchange="ProKBUpdateSectionExpertiseStatus(${section.id}, this.value)">
                                    <option value="">Не в экспертизе</option>
                                    <option value="uploaded_for_review" ${section.expertise_status === 'uploaded_for_review' ? 'selected' : ''}>Загружен на проверку</option>
                                    <option value="remarks_received" ${section.expertise_status === 'remarks_received' ? 'selected' : ''}>Замечания получены</option>
                                    <option value="remarks_in_progress" ${section.expertise_status === 'remarks_in_progress' ? 'selected' : ''}>Замечания в работе</option>
                                    <option value="accepted_by_expert" ${section.expertise_status === 'accepted_by_expert' ? 'selected' : ''}>Принят экспертом</option>
                                </select>
                            ` : `
                                <span class="badge ${App.expertiseSectionStatusLabels[section.expertise_status]?.class || 'bg-slate-100 text-slate-500'}">
                                    ${App.expertiseSectionStatusLabels[section.expertise_status]?.label || 'Не в экспертизе'}
                                </span>
                            `}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        // Этапы экспертизы
        const expertisesHtml = project.expertises && project.expertises.length > 0
            ? project.expertises.map(exp => `
                <div class="card p-5">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="font-semibold text-slate-800">${escapeHtml(exp.stage_name)}</h4>
                        ${isManagement ? `<button onclick="ProKBDeleteExpertise(${exp.id})" class="text-red-500 hover:text-red-700 text-sm">Удалить</button>` : ''}
                    </div>
                    ${isManagement ? `
                        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                            <div>
                                <label class="block text-slate-500 mb-1">Эксперт</label>
                                <input type="text" class="input-field" value="${escapeHtml(exp.expert_name || '')}"
                                    onchange="ProKBUpdateExpertise(${exp.id}, 'expert_name', this.value)">
                            </div>
                            <div>
                                <label class="block text-slate-500 mb-1">Контакт эксперта</label>
                                <input type="text" class="input-field" value="${escapeHtml(exp.expert_contact || '')}"
                                    onchange="ProKBUpdateExpertise(${exp.id}, 'expert_contact', this.value)">
                            </div>
                            <div>
                                <label class="block text-slate-500 mb-1">Телефон</label>
                                <input type="text" class="input-field" value="${escapeHtml(exp.expert_phone || '')}"
                                    onchange="ProKBUpdateExpertise(${exp.id}, 'expert_phone', this.value)">
                            </div>
                            <div>
                                <label class="block text-slate-500 mb-1">Email</label>
                                <input type="email" class="input-field" value="${escapeHtml(exp.expert_email || '')}"
                                    onchange="ProKBUpdateExpertise(${exp.id}, 'expert_email', this.value)">
                            </div>
                        </div>
                    ` : `
                        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                            ${exp.expert_name ? `<div><p class="text-slate-500">Эксперт:</p><p class="font-medium">${escapeHtml(exp.expert_name)}</p></div>` : ''}
                            ${exp.expert_phone ? `<div><p class="text-slate-500">Телефон:</p><p class="font-medium">${escapeHtml(exp.expert_phone)}</p></div>` : ''}
                        </div>
                    `}
                    
                    <div class="border-t border-slate-100 pt-4">
                        <div class="flex justify-between items-center mb-3">
                            <h5 class="font-medium text-slate-700">Замечания</h5>
                            ${isManagement ? `<button onclick="ProKBOpenAddRemarkModal(${exp.id})" class="text-blue-600 hover:text-blue-800 text-sm">+ Добавить замечание</button>` : ''}
                        </div>
                        ${exp.remarks && exp.remarks.length > 0 ? `
                            <div class="space-y-3">
                                ${exp.remarks.map(remark => `
                                    <div class="p-3 rounded-lg ${remark.is_resolved ? 'bg-green-50 border border-green-200' : 'bg-slate-50'}">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                ${remark.section_code ? `<span class="text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded mr-2">${remark.section_code}</span>` : ''}
                                                <span class="text-sm text-slate-700">${escapeHtml(remark.content || '')}</span>
                                                ${remark.file_name ? `<a href="${remark.file_path}" target="_blank" class="text-blue-600 hover:underline text-sm ml-2">${escapeHtml(remark.file_name)}</a>` : ''}
                                            </div>
                                            ${isManagement ? `
                                                <button onclick="ProKBResolveRemark(${remark.id}, ${!remark.is_resolved})" 
                                                    class="text-xs ${remark.is_resolved ? 'text-slate-500' : 'text-green-600 hover:text-green-800'}">
                                                    ${remark.is_resolved ? 'Восстановить' : 'Решено'}
                                                </button>
                                            ` : ''}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        ` : '<p class="text-sm text-slate-400">Нет замечаний</p>'}
                    </div>
                </div>
            `).join('')
            : '<div class="empty-state"><p>Нет этапов экспертизы</p></div>';
        
        App.elements.expertiseContainer.innerHTML = `
            ${positiveConclusionHtml}
            ${sectionsExpertiseHtml}
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-slate-800">Этапы экспертизы</h3>
                ${isManagement ? `<button onclick="ProKBOpenAddExpertiseModal()" class="btn-primary text-sm">+ Добавить этап</button>` : ''}
            </div>
            ${expertisesHtml}
        `;
    }

    function backToProject() {
        if (App.currentProject) {
            renderProjectPage();
            showPage('project');
        }
    }

    // ============================================================================
    // РАЗДЕЛЫ
    // ============================================================================

    function openSection(sectionId) {
        const section = App.currentProject.sections.find(s => s.id === sectionId);
        if (!section) return;
        
        App.currentSection = section;
        
        apiRequest('get_messages', { section_id: sectionId })
            .then(response => {
                if (response.success) {
                    App.currentSection.messages = response.data;
                    renderSectionPage();
                    showPage('section');
                }
            })
            .catch(() => {
                App.currentSection.messages = [];
                renderSectionPage();
                showPage('section');
            });
    }

    function renderSectionPage() {
        const section = App.currentSection;
        const project = App.currentProject;
        const isManagement = App.user.role === 'director' || App.user.role === 'gip';
        
        App.elements.sectionTitle.textContent = 'Раздел ' + section.code;
        App.elements.sectionProjectName.textContent = project.name;
        
        App.elements.sectionInfo.innerHTML = `
            <div class="space-y-5">
                <div>
                    <p class="text-sm text-slate-500 mb-2">Статус</p>
                    <select id="section-status-select" class="input-field w-full bg-white" ${!isManagement && section.assignee_id != App.user.id ? 'disabled' : ''}>
                        <option value="not_started" ${section.status === 'not_started' ? 'selected' : ''}>Не начато</option>
                        <option value="in_progress" ${section.status === 'in_progress' ? 'selected' : ''}>В работе</option>
                        <option value="completed" ${section.status === 'completed' ? 'selected' : ''}>Готово</option>
                        <option value="revision" ${section.status === 'revision' ? 'selected' : ''}>На доработке</option>
                    </select>
                </div>
                ${section.assignee ? `
                    <div>
                        <p class="text-sm text-slate-500 mb-2">Ответственный</p>
                        <div class="flex items-center gap-3">
                            <div class="avatar avatar-sm bg-slate-200 text-slate-600">${section.assignee.initials}</div>
                            <span class="text-sm text-slate-700">${section.assignee.name}</span>
                        </div>
                    </div>
                ` : ''}
                ${isManagement ? `
                    <div>
                        <label class="block text-sm text-slate-500 mb-2">Назначить ответственного</label>
                        <select id="section-assignee-select" class="input-field w-full bg-white">
                            <option value="">Выберите сотрудника</option>
                            ${App.employees.map(e => `<option value="${e.id}" ${section.assignee_id == e.id ? 'selected' : ''}>${e.name} (${e.position})</option>`).join('')}
                        </select>
                    </div>
                ` : ''}
                <div>
                    <p class="text-sm text-slate-500 mb-2">Описание</p>
                    <p class="text-sm text-slate-700 bg-slate-50 p-3 rounded-xl">${section.description || section.code}</p>
                </div>
            </div>
        `;
        
        const statusSelect = document.getElementById('section-status-select');
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                updateSectionStatus(section.id, this.value);
            });
        }
        
        const assigneeSelect = document.getElementById('section-assignee-select');
        if (assigneeSelect) {
            assigneeSelect.addEventListener('change', function() {
                assignSection(section.id, this.value);
            });
        }
        
        const messages = section.messages || [];
        App.elements.sectionMessages.innerHTML = messages.length ? messages.map(msg => `
            <div class="message-card ${msg.is_critical && !msg.is_resolved ? 'message-critical' : ''}">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <div class="avatar avatar-sm bg-slate-200 text-slate-600">${msg.author ? msg.author.initials : '?'}</div>
                        <div>
                            <p class="font-medium text-sm text-slate-800">${msg.author ? msg.author.name : 'Неизвестный'}</p>
                            <p class="text-xs text-slate-400">${msg.date}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        ${msg.is_critical && !msg.is_resolved ? '<span class="badge bg-red-500 text-white">КРИТИЧНО</span>' : ''}
                        ${msg.is_resolved ? '<span class="badge bg-green-500 text-white">Решено</span>' : ''}
                    </div>
                </div>
                <p class="text-sm text-slate-700">${escapeHtml(msg.content)}</p>
            </div>
        `).join('') : '<div class="text-center text-slate-400 py-8">Нет сообщений</div>';
    }

    function updateSectionStatus(sectionId, status) {
        apiRequest('update_section_status', { section_id: sectionId, status: status })
            .then(response => {
                if (response.success) {
                    showToast('Статус обновлён', 'success');
                    const section = App.currentProject.sections.find(s => s.id === sectionId);
                    if (section) section.status = status;
                }
            })
            .catch(() => showToast('Ошибка обновления статуса', 'error'));
    }

    function assignSection(sectionId, assigneeId) {
        apiRequest('assign_section', { section_id: sectionId, assignee_id: assigneeId })
            .then(response => {
                if (response.success) {
                    showToast('Ответственный назначен', 'success');
                    const section = App.currentProject.sections.find(s => s.id === sectionId);
                    if (section && assigneeId) {
                        section.assignee_id = assigneeId;
                        section.assignee = App.employees.find(e => e.id == assigneeId);
                    }
                }
            })
            .catch(() => showToast('Ошибка назначения', 'error'));
    }

    function handleSendMessage(e) {
        e.preventDefault();
        
        const content = App.elements.messageContent.value.trim();
        const isCritical = App.elements.messageCritical.checked;
        
        if (!content) return;
        
        apiRequest('send_message', {
            section_id: App.currentSection.id,
            content: content,
            is_critical: isCritical
        })
        .then(response => {
            if (response.success) {
                App.elements.messageContent.value = '';
                App.elements.messageCritical.checked = false;
                showToast('Сообщение отправлено', 'success');
                openSection(App.currentSection.id);
            }
        })
        .catch(() => showToast('Ошибка отправки сообщения', 'error'));
    }

    // ============================================================================
    // СОТРУДНИКИ
    // ============================================================================

    function loadEmployees() {
        const showArchived = App.elements.employeesShowArchived?.checked || false;
        
        apiRequest('get_employees', { show_archived: showArchived })
            .then(response => {
                if (response.success) {
                    App.employees = response.data;
                    renderEmployees();
                    updateTaskSelects();
                }
            })
            .catch(() => {});
    }

    function renderEmployees() {
        let filtered = [...App.employees];
        
        const search = App.elements.employeesSearch?.value.toLowerCase();
        if (search) {
            filtered = filtered.filter(e => 
                e.name.toLowerCase().includes(search) || 
                e.email.toLowerCase().includes(search) ||
                (e.position && e.position.toLowerCase().includes(search))
            );
        }
        
        const html = filtered.map(employee => {
            let activeCount = 0;
            let completedCount = 0;
            
            App.projects.forEach(project => {
                project.sections.forEach(section => {
                    if (section.assignee_id == employee.id) {
                        if (section.status === 'in_progress') activeCount++;
                        if (section.status === 'completed') completedCount++;
                    }
                });
            });
            
            return `
                <div onclick="ProKBOpenEmployeeProfile(${employee.id})" class="card p-5 cursor-pointer">
                    <div class="flex items-start gap-4">
                        <div class="avatar avatar-lg text-white" style="background: ${employee.avatar_color || '#64748b'}">
                            ${employee.initials}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-slate-800">${escapeHtml(employee.name)}</h3>
                            <p class="text-slate-500 text-sm">${escapeHtml(employee.position || '')}</p>
                            <p class="text-slate-400 text-xs mt-1">${escapeHtml(employee.email)}</p>
                            ${employee.is_archived ? '<span class="badge bg-slate-200 text-slate-600 mt-2">В архиве</span>' : ''}
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="flex gap-2">
                                <span class="badge status-in-progress">${activeCount} в работе</span>
                                <span class="badge status-completed">${completedCount} выполнено</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        App.elements.employeesList.innerHTML = html || 
            '<div class="empty-state"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg><p>Сотрудники не найдены</p></div>';
    }

    function openEmployeeProfile(employeeId) {
        apiRequest('get_employee_profile', { employee_id: employeeId })
            .then(response => {
                if (response.success) {
                    App.currentEmployee = response.data;
                    renderEmployeeProfile();
                    showPage('employee-profile');
                }
            })
            .catch(() => showToast('Ошибка загрузки профиля', 'error'));
    }

    function renderEmployeeProfile() {
        const data = App.currentEmployee;
        const employee = data.employee;
        const projects = data.projects;
        const comments = data.comments;
        const isManagement = App.user.role === 'director' || App.user.role === 'gip';
        const isDirector = App.user.role === 'director';
        
        document.getElementById('employee-profile-name').textContent = employee.name;
        document.getElementById('employee-profile-position').textContent = employee.position || '';
        document.getElementById('employee-profile-email').textContent = employee.email;
        document.getElementById('employee-profile-phone').textContent = employee.phone || '—';
        
        // Проекты
        const projectsHtml = projects.map(project => `
            <div class="p-4 rounded-lg border border-slate-100">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-medium text-slate-800">${escapeHtml(project.name)}</h4>
                    <span class="badge ${App.projectStatusLabels[project.status]?.class || ''}">${App.projectStatusLabels[project.status]?.label || project.status}</span>
                </div>
                <div class="flex gap-2 flex-wrap">
                    ${project.sections.map(s => `
                        <span class="text-xs px-2 py-1 rounded-lg ${s.status === 'completed' ? 'bg-green-50 text-green-600' : s.status === 'in_progress' ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-500'}">
                            ${s.code}
                        </span>
                    `).join('')}
                </div>
            </div>
        `).join('');
        
        document.getElementById('employee-projects').innerHTML = projectsHtml || '<div class="empty-state"><p>Нет участия в проектах</p></div>';
        
        // Комментарии (только для руководства)
        if (App.elements.employeeComments) {
            App.elements.employeeComments.classList.toggle('hidden', !isManagement);
            
            if (isManagement) {
                const commentsHtml = comments.map(comment => `
                    <div class="p-3 rounded-lg bg-slate-50">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-xs text-slate-400">${comment.date}</span>
                            <span class="text-xs text-slate-400">${comment.author?.name || ''}</span>
                        </div>
                        <p class="text-sm text-slate-700">${escapeHtml(comment.content)}</p>
                    </div>
                `).join('');
                
                document.getElementById('employee-comments-list').innerHTML = commentsHtml || '<p class="text-sm text-slate-400">Нет комментариев</p>';
            }
        }
        
        // Кнопки управления
        if (App.elements.addEmployeeCommentBtn) {
            App.elements.addEmployeeCommentBtn.classList.toggle('hidden', !isManagement);
        }
        
        const archiveBtn = document.getElementById('archive-employee-btn');
        if (archiveBtn) {
            archiveBtn.classList.toggle('hidden', !isDirector);
            if (employee.is_archived) {
                archiveBtn.textContent = 'Восстановить';
                archiveBtn.onclick = () => restoreEmployee(employee.id);
            } else {
                archiveBtn.textContent = 'В архив';
                archiveBtn.onclick = () => archiveEmployee(employee.id);
            }
        }
    }

    // ============================================================================
    // ЗАДАЧИ
    // ============================================================================

    function loadTasks() {
        const filter = App.elements.tasksFilter?.value;
        const statusFilter = App.elements.tasksStatusFilter?.value;
        
        apiRequest('get_tasks', { filter: filter, status: statusFilter })
            .then(response => {
                if (response.success) {
                    let tasks = response.data;
                    
                    if (statusFilter) {
                        tasks = tasks.filter(t => t.status === statusFilter);
                    }
                    
                    App.tasks = tasks;
                    renderTasks();
                    renderDashboard();
                }
            })
            .catch(() => showToast('Ошибка загрузки задач', 'error'));
    }

    function renderTasks() {
        const html = App.tasks.map(task => {
            const project = App.projects.find(p => p.id === task.project_id);
            
            return `
                <div class="card p-5">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-medium text-slate-800">${escapeHtml(task.title)}</h3>
                            ${project ? `<p class="text-sm text-slate-500 mt-1">${escapeHtml(project.name)}</p>` : ''}
                            ${task.description ? `<p class="text-sm text-slate-400 mt-2">${escapeHtml(task.description)}</p>` : ''}
                            <div class="flex items-center gap-4 mt-3 text-sm text-slate-400">
                                ${task.author ? `<span>Постановщик: ${task.author.name}</span>` : ''}
                                ${task.assignee ? `<span>Исполнитель: ${task.assignee.name}</span>` : ''}
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="badge ${App.priorityLabels[task.priority]?.class || ''}">${App.priorityLabels[task.priority]?.label || task.priority}</span>
                            <select onchange="ProKBUpdateTaskStatus(${task.id}, this.value)" class="input-field bg-white text-sm">
                                <option value="not_started" ${task.status === 'not_started' ? 'selected' : ''}>Новая</option>
                                <option value="in_progress" ${task.status === 'in_progress' ? 'selected' : ''}>В работе</option>
                                <option value="completed" ${task.status === 'completed' ? 'selected' : ''}>Выполнено</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        App.elements.tasksList.innerHTML = html || 
            '<div class="empty-state"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg><p>Задачи не найдены</p></div>';
    }

    // ============================================================================
    // АДМИН-ПАНЕЛЬ
    // ============================================================================

    function loadDesignSections() {
        apiRequest('get_design_sections')
            .then(response => {
                if (response.success) {
                    App.designSections = response.data;
                    renderDesignSections();
                    updateProjectSectionsCheckboxes();
                }
            })
            .catch(() => {});
    }

    function renderDesignSections() {
        if (!App.elements.designSectionsList) return;
        
        const isDirector = App.user.role === 'director';
        
        const html = App.designSections.map(section => `
            <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50">
                <div class="flex items-center gap-3">
                    <span class="font-mono font-bold text-slate-700">${escapeHtml(section.code)}</span>
                    <span class="text-slate-600">${escapeHtml(section.name)}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge ${section.is_active ? 'status-completed' : 'status-not-started'}">${section.is_active ? 'Активен' : 'Отключён'}</span>
                    ${isDirector ? `
                        <button onclick="ProKBEditDesignSection(${section.id})" class="text-blue-600 hover:text-blue-800 text-sm">Редактировать</button>
                        <button onclick="ProKBDeleteDesignSection(${section.id})" class="text-red-500 hover:text-red-700 text-sm">Удалить</button>
                    ` : ''}
                </div>
            </div>
        `).join('');
        
        App.elements.designSectionsList.innerHTML = html || '<div class="empty-state"><p>Нет разделов</p></div>';
    }

    function updateProjectSectionsCheckboxes() {
        const container = document.getElementById('project-sections-checkboxes');
        if (!container) return;
        
        container.innerHTML = App.designSections
            .filter(s => s.is_active)
            .map(section => `
                <label class="checkbox-label">
                    <input type="checkbox" name="sections[]" value="${section.code}"> 
                    ${section.code}
                </label>
            `).join('');
    }

    function loadStandardInvestigations() {
        apiRequest('get_standard_investigations')
            .then(response => {
                if (response.success) {
                    App.standardInvestigations = response.data;
                }
            })
            .catch(() => {});
    }

    function loadExpertiseStages() {
        apiRequest('get_expertise_stages')
            .then(response => {
                if (response.success) {
                    App.expertiseStages = response.data;
                }
            })
            .catch(() => {});
    }

    // ============================================================================
    // ОБРАБОТЧИКИ ФОРМ
    // ============================================================================

    function handleCreateProject(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        const sections = [];
        document.querySelectorAll('input[name="sections[]"]:checked').forEach(cb => {
            sections.push(cb.value);
        });
        
        apiRequest('create_project', {
            name: formData.get('name'),
            code: formData.get('code'),
            address: formData.get('address'),
            type: formData.get('type'),
            deadline: formData.get('deadline'),
            expertise: formData.get('expertise'),
            gip_id: formData.get('gip_id'),
            description: formData.get('description'),
            sections: JSON.stringify(sections)
        })
        .then(response => {
            if (response.success) {
                closeModals();
                form.reset();
                loadProjects();
                showToast('Проект создан', 'success');
            } else {
                showToast(response.data.message || 'Ошибка создания', 'error');
            }
        })
        .catch(() => showToast('Ошибка создания проекта', 'error'));
    }

    function handleUpdateProject(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        const sections = [];
        document.querySelectorAll('#edit-project-form input[name="sections[]"]:checked').forEach(cb => {
            sections.push(cb.value);
        });
        
        apiRequest('update_project', {
            project_id: App.currentProject.id,
            name: formData.get('name'),
            code: formData.get('code'),
            address: formData.get('address'),
            type: formData.get('type'),
            deadline: formData.get('deadline'),
            expertise: formData.get('expertise'),
            status: formData.get('status'),
            gip_id: formData.get('gip_id'),
            description: formData.get('description'),
            sections: JSON.stringify(sections)
        })
        .then(response => {
            if (response.success) {
                closeModals();
                openProject(App.currentProject.id);
                showToast('Проект обновлён', 'success');
            }
        })
        .catch(() => showToast('Ошибка обновления', 'error'));
    }

    function handleCreateEmployee(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        const competencies = [];
        form.querySelectorAll('input[name="competencies[]"]:checked').forEach(cb => {
            competencies.push(cb.value);
        });
        
        apiRequest('create_employee', {
            name: formData.get('name'),
            email: formData.get('email'),
            position: formData.get('position'),
            role: formData.get('role'),
            phone: formData.get('phone'),
            competencies: JSON.stringify(competencies)
        })
        .then(response => {
            if (response.success) {
                closeModals();
                form.reset();
                loadEmployees();
                showToast('Сотрудник создан', 'success');
            }
        })
        .catch(() => showToast('Ошибка создания', 'error'));
    }

    function handleCreateTask(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        apiRequest('create_task', {
            title: formData.get('title'),
            description: formData.get('description'),
            project_id: formData.get('project_id'),
            assignee_id: formData.get('assignee_id'),
            deadline: formData.get('deadline'),
            priority: formData.get('priority')
        })
        .then(response => {
            if (response.success) {
                closeModals();
                form.reset();
                loadTasks();
                showToast('Задача создана', 'success');
            }
        })
        .catch(() => showToast('Ошибка создания', 'error'));
    }

    function handleAddContact(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        apiRequest('add_contact', {
            project_id: App.currentProject.id,
            name: formData.get('name'),
            position: formData.get('position'),
            company: formData.get('company'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            notes: formData.get('notes')
        })
        .then(response => {
            if (response.success) {
                closeModals();
                form.reset();
                App.currentProject.contact_persons.push(response.data.contact);
                renderContactPersons();
                showToast('Контакт добавлен', 'success');
            }
        })
        .catch(() => showToast('Ошибка добавления', 'error'));
    }

    function handleAddIntroBlock(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        const data = new FormData();
        data.append('nonce', ProKB.nonce);
        data.append('action', 'prokb_add_intro_block');
        data.append('project_id', App.currentProject.id);
        data.append('type', formData.get('type'));
        data.append('title', formData.get('title'));
        data.append('content', formData.get('content'));
        
        if (formData.get('type') === 'file') {
            const fileInput = document.getElementById('intro-file-input');
            if (fileInput.files[0]) {
                data.append('file', fileInput.files[0]);
            }
        }
        
        fetch(ProKB.ajaxUrl, { method: 'POST', body: data })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    closeModals();
                    form.reset();
                    App.currentProject.intro_blocks.push(response.data.block);
                    renderIntroBlocks();
                    showToast('Блок добавлен', 'success');
                }
            })
            .catch(() => showToast('Ошибка добавления', 'error'));
    }

    function handleAddInvestigation(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        apiRequest('add_project_investigation', {
            project_id: App.currentProject.id,
            standard_id: formData.get('standard_id'),
            custom_name: formData.get('custom_name')
        })
        .then(response => {
            if (response.success) {
                closeModals();
                form.reset();
                App.currentProject.investigations.push(response.data.investigation);
                renderInvestigations();
                showToast('Изыскание добавлено', 'success');
            }
        })
        .catch(() => showToast('Ошибка добавления', 'error'));
    }

    function handleAddExpertise(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        apiRequest('add_project_expertise', {
            project_id: App.currentProject.id,
            stage_id: formData.get('stage_id')
        })
        .then(response => {
            if (response.success) {
                closeModals();
                form.reset();
                App.currentProject.expertises.push(response.data.expertise);
                renderExpertise();
                showToast('Этап экспертизы добавлен', 'success');
            }
        })
        .catch(() => showToast('Ошибка добавления', 'error'));
    }

    function handleAddRemark(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        const data = new FormData();
        data.append('nonce', ProKB.nonce);
        data.append('action', 'prokb_add_expertise_remark');
        data.append('expertise_id', formData.get('expertise_id'));
        data.append('section_id', formData.get('section_id'));
        data.append('content', formData.get('content'));
        
        const fileInput = document.getElementById('remark-file-input');
        if (fileInput && fileInput.files[0]) {
            data.append('file', fileInput.files[0]);
        }
        
        fetch(ProKB.ajaxUrl, { method: 'POST', body: data })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    closeModals();
                    form.reset();
                    openProject(App.currentProject.id);
                    showToast('Замечание добавлено', 'success');
                }
            })
            .catch(() => showToast('Ошибка добавления', 'error'));
    }

    function handleAddEmployeeComment(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        apiRequest('add_employee_comment', {
            employee_id: App.currentEmployee.employee.id,
            content: formData.get('content')
        })
        .then(response => {
            if (response.success) {
                closeModals();
                form.reset();
                openEmployeeProfile(App.currentEmployee.employee.id);
                showToast('Комментарий добавлен', 'success');
            }
        })
        .catch(() => showToast('Ошибка добавления', 'error'));
    }

    // ============================================================================
    // ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
    // ============================================================================

    function openEditProjectModal() {
        const project = App.currentProject;
        const form = App.elements.editProjectForm;
        
        form.querySelector('[name="name"]').value = project.name;
        form.querySelector('[name="code"]').value = project.code || '';
        form.querySelector('[name="address"]').value = project.address || '';
        form.querySelector('[name="type"]').value = project.type;
        form.querySelector('[name="deadline"]').value = project.deadline || '';
        form.querySelector('[name="expertise"]').value = project.expertise;
        form.querySelector('[name="status"]').value = project.status;
        form.querySelector('[name="description"]').value = project.description || '';
        
        // Устанавливаем ГИП
        const gipSelect = form.querySelector('[name="gip_id"]');
        if (gipSelect) {
            gipSelect.value = project.gip_id || '';
        }
        
        // Устанавливаем разделы
        const checkboxes = form.querySelectorAll('input[name="sections[]"]');
        checkboxes.forEach(cb => {
            cb.checked = project.sections.some(s => s.code === cb.value);
        });
        
        showModal('edit-project-modal');
    }

    function openNewTaskModal() {
        const projectSelect = document.getElementById('task-project-select');
        const assigneeSelect = document.getElementById('task-assignee-select');
        
        if (projectSelect) {
            projectSelect.innerHTML = '<option value="">Без проекта</option>' +
                App.projects.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join('');
        }
        
        if (assigneeSelect) {
            assigneeSelect.innerHTML = '<option value="">Выберите исполнителя</option>' +
                App.employees.map(e => `<option value="${e.id}">${escapeHtml(e.name)} (${escapeHtml(e.position || '')})</option>`).join('');
        }
        
        showModal('new-task-modal');
    }

    function openAddInvestigationModal() {
        const select = document.querySelector('#add-investigation-modal select[name="standard_id"]');
        if (select) {
            select.innerHTML = '<option value="">-- Выберите из списка --</option>' +
                App.standardInvestigations.map(i => `<option value="${i.id}">${escapeHtml(i.name)}</option>`).join('');
        }
        showModal('add-investigation-modal');
    }

    function openAddExpertiseModal() {
        const select = document.querySelector('#add-expertise-modal select[name="stage_id"]');
        if (select) {
            select.innerHTML = '<option value="">-- Выберите этап --</option>' +
                App.expertiseStages.map(s => `<option value="${s.id}">${escapeHtml(s.name)}</option>`).join('');
        }
        showModal('add-expertise-modal');
    }

    function updateTaskSelects() {
        const gipSelect = document.querySelector('#edit-project-form select[name="gip_id"]');
        if (gipSelect) {
            gipSelect.innerHTML = '<option value="">Выберите ГИПа</option>' +
                App.employees.filter(e => e.role === 'gip' || e.role === 'director')
                    .map(e => `<option value="${e.id}">${escapeHtml(e.name)}</option>`).join('');
        }
    }

    function archiveProject() {
        const reason = prompt('Причина перемещения в архив:');
        if (reason === null) return;
        
        apiRequest('archive_project', { project_id: App.currentProject.id, reason: reason })
            .then(response => {
                if (response.success) {
                    loadProjects();
                    showPage('projects');
                    showToast('Проект перемещён в архив', 'success');
                }
            })
            .catch(() => showToast('Ошибка архивации', 'error'));
    }

    function restoreProject() {
        apiRequest('restore_project', { project_id: App.currentProject.id })
            .then(response => {
                if (response.success) {
                    loadProjects();
                    showPage('projects');
                    showToast('Проект восстановлен', 'success');
                }
            })
            .catch(() => showToast('Ошибка восстановления', 'error'));
    }

    function archiveEmployee(employeeId) {
        const reason = prompt('Причина перемещения в архив:');
        if (reason === null) return;
        
        apiRequest('archive_employee', { employee_id: employeeId, reason: reason })
            .then(response => {
                if (response.success) {
                    loadEmployees();
                    showPage('employees');
                    showToast('Сотрудник перемещён в архив', 'success');
                }
            })
            .catch(() => showToast('Ошибка архивации', 'error'));
    }

    function restoreEmployee(employeeId) {
        apiRequest('restore_employee', { employee_id: employeeId })
            .then(response => {
                if (response.success) {
                    loadEmployees();
                    showPage('employees');
                    showToast('Сотрудник восстановлен', 'success');
                }
            })
            .catch(() => showToast('Ошибка восстановления', 'error'));
    }

    // ============================================================================
    // API ЗАПРОСЫ
    // ============================================================================

    function apiRequest(action, data = {}) {
        const formData = new FormData();
        formData.append('action', 'prokb_' + action);
        formData.append('nonce', ProKB.nonce);
        
        for (const key in data) {
            formData.append(key, data[key]);
        }
        
        return fetch(ProKB.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json());
    }

    // ============================================================================
    // УТИЛИТЫ
    // ============================================================================

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '—';
        const date = new Date(dateStr);
        return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function getDaysSince(dateStr) {
        if (!dateStr) return 0;
        const date = new Date(dateStr);
        const now = new Date();
        const diff = Math.floor((now - date) / (1000 * 60 * 60 * 24));
        return Math.max(0, diff);
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function showToast(message, type = 'info') {
        const container = App.elements.toastContainer;
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span>${escapeHtml(message)}</span>
            <button onclick="this.parentElement.remove()">×</button>
        `;
        container.appendChild(toast);
        
        setTimeout(() => toast.remove(), 4000);
    }

    // ============================================================================
    // ГЛОБАЛЬНЫЕ ФУНКЦИИ
    // ============================================================================

    window.ProKBOpenProject = openProject;
    window.ProKBOpenSection = openSection;
    window.ProKBOpenEmployeeProfile = openEmployeeProfile;
    window.ProKBUpdateTaskStatus = function(taskId, status) {
        apiRequest('update_task_status', { task_id: taskId, status: status })
            .then(() => {
                loadTasks();
                showToast('Статус обновлён', 'success');
            });
    };
    window.ProKBUpdateSectionExpertiseStatus = function(sectionId, status) {
        apiRequest('update_section_expertise_status', { section_id: sectionId, expertise_status: status })
            .then(() => {
                showToast('Статус экспертизы обновлён', 'success');
            });
    };
    window.ProKBUpdateInvestigation = function(invId, field, value) {
        const data = { investigation_id: invId };
        data[field] = value;
        apiRequest('update_project_investigation', data)
            .then(() => showToast('Изыскание обновлено', 'success'));
    };
    window.ProKBUpdateExpertise = function(expId, field, value) {
        const data = { expertise_id: expId };
        data[field] = value;
        apiRequest('update_project_expertise', data)
            .then(() => showToast('Экспертиза обновлена', 'success'));
    };
    window.ProKBDeleteContact = function(contactId) {
        if (!confirm('Удалить контактное лицо?')) return;
        apiRequest('delete_contact', { contact_id: contactId })
            .then(() => {
                App.currentProject.contact_persons = App.currentProject.contact_persons.filter(c => c.id !== contactId);
                renderContactPersons();
                showToast('Контакт удалён', 'success');
            });
    };
    window.ProKBDeleteIntroBlock = function(blockId) {
        if (!confirm('Удалить блок?')) return;
        apiRequest('delete_intro_block', { block_id: blockId })
            .then(() => {
                App.currentProject.intro_blocks = App.currentProject.intro_blocks.filter(b => b.id !== blockId);
                renderIntroBlocks();
                showToast('Блок удалён', 'success');
            });
    };
    window.ProKBDeleteInvestigation = function(invId) {
        if (!confirm('Удалить изыскание?')) return;
        apiRequest('delete_project_investigation', { investigation_id: invId })
            .then(() => {
                App.currentProject.investigations = App.currentProject.investigations.filter(i => i.id !== invId);
                renderInvestigations();
                showToast('Изыскание удалено', 'success');
            });
    };
    window.ProKBDeleteExpertise = function(expId) {
        if (!confirm('Удалить этап экспертизы?')) return;
        apiRequest('delete_project_expertise', { expertise_id: expId })
            .then(() => {
                App.currentProject.expertises = App.currentProject.expertises.filter(e => e.id !== expId);
                renderExpertise();
                showToast('Этап удалён', 'success');
            });
    };
    window.ProKBResolveRemark = function(remarkId, resolved) {
        apiRequest('resolve_expertise_remark', { remark_id: remarkId, resolved: resolved.toString() })
            .then(() => {
                openProject(App.currentProject.id);
                showToast(resolved ? 'Замечание отмечено как решённое' : 'Замечание восстановлено', 'success');
            });
    };
    window.ProKBOpenAddRemarkModal = function(expertiseId) {
        const form = App.elements.addRemarkForm;
        form.querySelector('[name="expertise_id"]').value = expertiseId;
        
        const sectionSelect = form.querySelector('[name="section_id"]');
        sectionSelect.innerHTML = '<option value="">-- Без привязки к разделу --</option>' +
            App.currentProject.sections.map(s => `<option value="${s.id}">${escapeHtml(s.code)} - ${escapeHtml(s.description || '')}</option>`).join('');
        
        showModal('add-remark-modal');
    };
    window.ProKBOpenAddExpertiseModal = openAddExpertiseModal;
    window.ProKBUploadPositiveConclusion = function() {
        const fileInput = document.getElementById('positive-conclusion-file');
        if (!fileInput.files[0]) {
            showToast('Выберите файл', 'error');
            return;
        }
        
        const data = new FormData();
        data.append('nonce', ProKB.nonce);
        data.append('action', 'prokb_upload_positive_conclusion');
        data.append('project_id', App.currentProject.id);
        data.append('file', fileInput.files[0]);
        
        fetch(ProKB.ajaxUrl, { method: 'POST', body: data })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    App.currentProject.positive_conclusion_file = response.data.file_url;
                    App.currentProject.positive_conclusion_name = response.data.file_name;
                    renderExpertise();
                    showToast('Файл загружен', 'success');
                }
            });
    };

    // ============================================================================
    // ЗАПУСК
    // ============================================================================

    document.addEventListener('DOMContentLoaded', init);

})();
