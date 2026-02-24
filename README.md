# Проектное Бюро v2.0

WordPress тема для управления проектами проектного бюро.

## Установка

1. Скопируйте папку темы в `/wp-content/themes/prokb-theme/`
2. Активируйте тему: **Внешний вид → Темы**
3. Готово!

## Демо-доступы

Любой пароль от 4 символов:

| Роль | Email |
|------|-------|
| Директор | ivanov@prokb.ru |
| ГИП | sidorova@prokb.ru |
| Сотрудник | petrov@prokb.ru |

## Структура

```
├── style.css          # Стили
├── functions.php      # Загрузчик модулей
├── index.php          # Основной шаблон
├── header.php         # Заголовок
├── footer.php         # Подвал
├── js/
│   └── app.js         # JavaScript
└── inc/
    ├── setup.php
    ├── post-types.php
    ├── user-meta.php
    ├── helpers.php
    ├── demo-data.php
    └── ajax/          # 14 файлов AJAX
```

## Требования

- WordPress 5.0+
- PHP 7.4+

## Лицензия

GPL v2
