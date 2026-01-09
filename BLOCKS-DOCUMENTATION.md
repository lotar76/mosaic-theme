# Документация блоков темы Mosaic

Классификация всех блоков темы с указанием расположения и способа использования.

## ⚠️ Важное изменение: spacing подход v2

**Проблема v1:** Каждый блок оборачивался в `<div class="section-spacing">` с padding-top/bottom, что создавало двойные отступы между блоками (160px вместо 80px).

**Решение v2:** Все блоки помещаются в один контейнер с `space-y-[80px]`, который добавляет margin-top только между блоками (не в начале и конце).

```php
<!-- ✅ Правильно -->
<div class="py-[80px] space-y-[80px] min-[1280px]:py-[100px] min-[1280px]:space-y-[100px]">
    <?php get_template_part('template-parts/sections/block-1'); ?>
    <?php get_template_part('template-parts/sections/block-2'); ?>
</div>
```

**См.:** `.cursor/rules/workflow.mdc` для деталей процесса принятия архитектурных решений.

---

## Переиспользуемые блоки

Блоки из `template-parts/sections/` — **"гладкие"** (без внешних отступов). Используются в нескольких местах.

### 1. Catalog (Каталог)

**Файл:** `template-parts/sections/catalog.php`

**Где используется:**
- `front-page.php` — превью каталога (h2)
- `page-catalog.php` — полная страница (h1)

**Подключение:**
```php
<!-- В контейнере с space-y -->
<?php get_template_part('template-parts/sections/catalog', null, [
    'title_tag' => 'h2',
    'title' => 'Каталог'
]); ?>
```

**Параметры:**
- `title_tag` — h1 или h2
- `title` — текст заголовка
- `title_classes` — CSS классы (опционально)

---

### 2. Benefits (С нами комфортно работать)

**Файл:** `template-parts/sections/benefits.php`

**Где используется:**
- `front-page.php`
- `page-catalog.php`

**Подключение:**
```php
<!-- В контейнере с space-y -->
<?php get_template_part('template-parts/sections/benefits'); ?>
```

---

### 3. Contact Form (Давайте обсудим ваш проект)

**Файл:** `template-parts/sections/contact-form.php`

**Где используется:**
- `front-page.php`
- `page-catalog.php`

**Подключение:**
```php
<!-- В контейнере с space-y -->
<?php get_template_part('template-parts/sections/contact-form'); ?>
```

**Якорь:** `#contact-form`

**Отступы:** `py-[80px] min-[1280px]:py-[100px]` (внутренние вертикальные отступы)

---

### 4. Work Process (Процесс работы)

**Файл:** `template-parts/sections/work-process.php`

**Где используется:**
- `front-page.php`

**Подключение:**
```php
<!-- В контейнере с space-y -->
<?php get_template_part('template-parts/sections/work-process'); ?>
```

**Отступы:** `py-[80px] min-[1280px]:py-[100px]` (внутренние вертикальные отступы)

---

### 5. About Section (О нас: Компания + Основатели)

**Файл:** `template-parts/sections/about-section.php`

**Где используется:**
- `front-page.php`

**Описание:** Объединённый блок "О компании" + "Основатели" (между ними НЕТ отступа — это один логический блок)

**Подключение:**
```php
<!-- В контейнере с space-y -->
<?php get_template_part('template-parts/sections/about-section'); ?>
```

**Внутренние компоненты:**
- `about-company.php` — информация о студии (фото + текст)
- `founders.php` — Алексей и Светлана Исаевы

**Техническая деталь:**  
Оба компонента обёрнуты в `<div>`, чтобы `space-y` родителя видел один элемент вместо двух. Без обёртки `space-y` добавил бы margin-top между `<section>` элементами.

---

### 6. Portfolio (Портфолио)

**Файл:** `template-parts/sections/portfolio.php`

**Где используется:**
- `front-page.php`

**Подключение:**
```php
<!-- В контейнере с space-y -->
<?php get_template_part('template-parts/sections/portfolio'); ?>
```

---

### 7. Showroom (Шоурум)

**Файл:** `template-parts/sections/showroom.php`

**Где используется:**
- `front-page.php`

**Подключение:**
```php
<!-- В контейнере с space-y -->
<?php get_template_part('template-parts/sections/showroom'); ?>
```

---

### 8. News and Blog (Новости)

**Файл:** `template-parts/sections/news.php`

**Где используется:**
- `front-page.php`

**Подключение:**
```php
<!-- В контейнере с space-y -->
<?php get_template_part('template-parts/sections/news'); ?>
```

---

## Внутренние компоненты

Эти компоненты не вызываются напрямую, а используются внутри других блоков.

### About Company (О компании)

**Файл:** `template-parts/sections/about-company.php`

**Используется в:** `about-section.php`

**Описание:** Информация о студии (фото + текст)

---

### Founders (Основатели)

**Файл:** `template-parts/sections/founders.php`

**Используется в:** `about-section.php`

**Описание:** Алексей и Светлана Исаевы

---

## Уникальные блоки (только front-page.php)

Блоки с отступами внутри `<section>`. Не переиспользуются.

### 10. Hero (Баннер)

**Файл:** `front-page.php` (строки 40-83)

**Описание:** Главный баннер с изображением и CTA

**Отступы:** внутри блока (своя структура)

---

## Системные блоки

### Header (Шапка)

**Файл:** `header.php`

**Тип:** фиксированный (position: fixed)

---

### Footer (Подвал)

**Файл:** `footer.php`

**Тип:** обычный блок

---

## Вспомогательные компоненты

### Breadcrumbs (Хлебные крошки)

**Файл:** `template-parts/breadcrumbs.php`

**Использование:** "гладкий" блок, без обертки

```php
<?php get_template_part('template-parts/breadcrumbs'); ?>
```

---

### Social Icons (Иконки соцсетей)

**Файл:** `template-parts/social-icons.php`

**Использование:** inline компонент

```php
<?php get_template_part('template-parts/social-icons'); ?>
```

---

### Catalog Grid (Сетка каталога)

**Файл:** `template-parts/sections/catalog-grid.php`

**Тип:** внутренний компонент

⚠️ **Не использовать напрямую!** Вызывается автоматически из `catalog.php`.

---

## Управление spacing

### ✅ Правильный подход: `space-y` на контейнере

**Проблема старого подхода:**
```php
<!-- ❌ НЕПРАВИЛЬНО: двойные отступы -->
<div class="section-spacing">  <!-- padding-bottom: 80px -->
    <?php get_template_part('block-1'); ?>
</div>
<div class="section-spacing">  <!-- padding-top: 80px -->
    <?php get_template_part('block-2'); ?>
</div>
<!-- Результат: 160px между блоками! -->
```

**Правильное решение:**
```php
<!-- ✅ ПРАВИЛЬНО: одинарные отступы через gap -->
<div class="py-[80px] min-[1280px]:py-[100px] space-y-[80px] min-[1280px]:space-y-[100px]">
    <?php get_template_part('block-1'); ?>
    <?php get_template_part('block-2'); ?>
    <?php get_template_part('block-3'); ?>
</div>
<!-- Результат: 80px между блоками -->
```

### Схема работы

```
[Hero Section]
   ↓ py-[80px] (padding-top контейнера)
┌──────────────────────────────────────┐
│ [Block 1: Catalog]                   │
│    ↓ space-y-[80px]                  │
│ [Block 2: About Section]             │
│   ├─ About Company                   │
│   └─ Founders (БЕЗ отступа)          │
│    ↓ space-y-[80px]                  │
│ [Block 3: Benefits]                  │
│    ↓ space-y-[80px]                  │
│ [Block 4: Portfolio]                 │
│    ↓ space-y-[80px]                  │
│ [Block 5: Contact Form]              │
│    ↓ space-y-[80px]                  │
│ [Block 6: Showroom]                  │
│    ↓ space-y-[80px]                  │
│ [Block 7: Work Process]              │
│    ↓ space-y-[80px]                  │
│ [Block 8: News]                      │
└──────────────────────────────────────┘
   ↓ py-[80px] (padding-bottom контейнера)
[Footer]
```

### Значения

- **Mobile** (до 1279px): 80px
- **Desktop** (от 1280px): 100px

### Примеры использования

**front-page.php:**
```php
<main>
    <section><!-- Hero --></section>
    
    <div class="py-[80px] min-[1280px]:py-[100px] space-y-[80px] min-[1280px]:space-y-[100px]">
        <?php get_template_part('template-parts/sections/catalog'); ?>
        <?php get_template_part('template-parts/sections/about-section'); ?>
        <?php get_template_part('template-parts/sections/benefits'); ?>
        <?php get_template_part('template-parts/sections/portfolio'); ?>
        <?php get_template_part('template-parts/sections/contact-form'); ?>
        <?php get_template_part('template-parts/sections/showroom'); ?>
        <?php get_template_part('template-parts/sections/work-process'); ?>
        <?php get_template_part('template-parts/sections/news'); ?>
    </div>
</main>
```

**page-catalog.php:**
```php
<main>
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    
    <div class="py-[80px] min-[1280px]:py-[100px] space-y-[80px] min-[1280px]:space-y-[100px]">
        <?php get_template_part('template-parts/sections/catalog'); ?>
        <?php get_template_part('template-parts/sections/benefits'); ?>
        <?php get_template_part('template-parts/sections/contact-form'); ?>
    </div>
</main>
```

---

## Правило классификации

**Переиспользуемый блок** (используется в ≥2 местах):
- ✅ Без внешних отступов (гладкий)
- ✅ Помещается в контейнер с `space-y-[80px]`
- ✅ Файл в `template-parts/sections/`

**Уникальный блок** (только в одном месте):
- ✅ Отступы управляются так же через `space-y` родителя
- ❌ Не требует отдельного файла
- ❌ Код в основном файле (`front-page.php`)

**Архитектурное правило:**
- Spacing управляется на уровне контейнера через `space-y`
- Блоки остаются "гладкими" (без py)
- Избегаем двойных отступов (padding-bottom + padding-top)

---

## См. также

- `.cursor/rules/workflow.mdc` — правила архитектуры принятия решений
- `.cursor/rules/mosaic.mdc` — правила структуры темы

---

**Дата обновления:** 2026-01-09  
**Тема:** Mosaic WP  
**Версия spacing подхода:** v2 (space-y контейнер вместо .section-spacing обёрток)
