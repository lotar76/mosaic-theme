# Правила разработки

Руководство по разработке и поддержке темы Mosaic.

## Принципы разработки

### 1. Модульность

**Не раздувай `functions.php`** — новые подсистемы выноси в `inc/` и подключай через `require_once`.

```php
// functions.php — только bootstrap
require_once __DIR__ . '/inc/enqueue.php';
require_once __DIR__ . '/inc/settings.php';
require_once __DIR__ . '/inc/catalog/post-types.php';
```

**Деление по ответственности:**
- `inc/catalog/*` — CPT/tax/meta/handlers каталога
- `inc/admin/*` — страницы админки опций (таблица → карточка)
- `inc/settings.php` — контакты/соцсети/адрес/график
- `inc/enqueue.php` — стили/скрипты

### 2. Переиспользуемые блоки

**Правило "гладких блоков":**
- Блоки в `template-parts/sections/` — без внешних отступов (py)
- Spacing управляется на уровне layout через `space-y`

```php
<!-- ✅ Правильно -->
<div class="py-[80px] space-y-[80px]">
    <?php get_template_part('template-parts/sections/benefits'); ?>
    <?php get_template_part('template-parts/sections/portfolio'); ?>
</div>

<!-- ❌ Неправильно: двойные отступы -->
<div class="py-[80px]">...</div>
<div class="py-[80px]">...</div>
```

### 3. Архитектура принятия решений

**См. `.cursor/rules/workflow.mdc`**

Перед любой реализацией ОБЯЗАТЕЛЬНО:
1. **Визуализация** — ASCII-схема с расчётами
2. **Альтернативы** — минимум 2-3 варианта с ✅/❌
3. **Математика** — показать формулу/расчёты
4. **Обоснование** — почему выбран этот подход
5. **Проверка предположений** — edge cases

## Кодстайл

### PHP

```php
<?php
/**
 * Краткое описание файла
 */

declare(strict_types=1);

// Нейминг: mosaic_*
function mosaic_get_settings(): array {
    // ...
}

// Экранирование
echo esc_html($title);
echo esc_attr($class);
echo esc_url($link);

// HTML контент
echo wp_kses_post($content);
```

### Данные

**Опции:**
- Одна опция = один массив
- Обязательный sanitize

```php
$data = get_option('mosaic_settings', []);
$sanitized = mosaic_sanitize_settings($data);
update_option('mosaic_settings', $sanitized);
```

**Post/term meta:**
- Фиксированные meta keys
- Sanitize на сохранении

```php
update_post_meta($post_id, '_mosaic_gallery_ids', array_map('absint', $ids));
```

**Репитеры:**
- Лимит по количеству элементов (не раздувать `wp_options`)

```php
$items = array_slice($items, 0, 50); // макс 50 элементов
```

### Админка

**UX по умолчанию:** список (таблица) → редактирование (карточка)

```php
// Список
if (!isset($_GET['action']) || $_GET['action'] !== 'edit') {
    mosaic_render_list_table();
    return;
}

// Карточка редактирования
mosaic_render_edit_form($_GET['id']);
```

**Все действия только с nonce:**

```php
if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'mosaic_save')) {
    wp_die('Security check failed');
}
```

**Редиректы только в handlers:**

```php
// ❌ Неправильно
function mosaic_render_page() {
    if (isset($_POST['save'])) {
        wp_safe_redirect(...); // НЕЛЬЗЯ из рендера!
    }
}

// ✅ Правильно
function mosaic_handle_save() {
    // ... save logic ...
    wp_safe_redirect(...);
    exit;
}
```

**Inline JS не через heredoc если есть `$...`:**

```php
// ❌ Неправильно
$js = <<<JS
const data = $data;
JS;

// ✅ Правильно (nowdoc + str_replace)
$js = <<<'JS'
const data = __DATA__;
JS;
$js = str_replace('__DATA__', wp_json_encode($data), $js);
```

### Фронт

**Всегда делать fallback:**

```php
$items = mosaic_get_news_items();
if (empty($items)) {
    // Показать демо-контент или пустое состояние
    $items = mosaic_get_demo_items();
}
```

**Для галерей:** превью = первая картинка

```php
$thumb_id = $gallery_ids[0] ?? 0;
$thumb_url = wp_get_attachment_image_url($thumb_id, 'large');
```

## Git workflow

### Коммиты

**Формат:** `type: описание`

**Types:**
- `feat:` — новая функциональность
- `fix:` — исправление бага
- `refactor:` — рефакторинг без изменения функциональности
- `docs:` — только документация
- `style:` — форматирование, пробелы
- `perf:` — оптимизация производительности
- `test:` — добавление тестов
- `chore:` — обновление зависимостей, конфигов

**Примеры:**
```bash
git commit -m "feat: добавлена адаптивная галерея для товаров"
git commit -m "fix: исправлен двойной отступ между блоками"
git commit -m "refactor: вынесен каталог в отдельные модули"
git commit -m "docs: обновлена документация API"
```

### Ветки

**main** — production-ready код

Для фич:
```bash
git checkout -b feature/catalog-filters
# ... разработка ...
git commit -m "feat: добавлены фильтры каталога"
git push origin feature/catalog-filters
# ... PR/merge ...
```

### Push на продакшн

```bash
# Проверка перед push
git status
git log --oneline -5

# Push в origin
git push origin main
```

## Тестирование

### Ручное тестирование

**Чеклист перед коммитом:**
- [ ] Главная страница отображается корректно
- [ ] Каталог работает (категории + товары)
- [ ] Контактная форма отправляется
- [ ] Админка работает (новости, процесс, настройки)
- [ ] Адаптивность на мобильных (Chrome DevTools)
- [ ] Нет ошибок в консоли браузера
- [ ] Нет PHP ошибок в `debug.log`

### Проверка производительности

```php
// Включить Query Monitor (плагин) для отладки
// Проверить количество SQL запросов
// Проверить время загрузки страницы
```

## Debugging

### Включение debug режима

```php
// wp-config.php (только на локалке!)
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
define('SCRIPT_DEBUG', true);
```

### Логирование

```php
// В любом месте кода
error_log('DEBUG: ' . print_r($data, true));

// Проверить логи
tail -f wp-content/debug.log
```

### Профилирование

```php
// Замер времени выполнения
$start = microtime(true);
// ... код ...
$time = microtime(true) - $start;
error_log("Execution time: {$time}s");
```

## Документация

### При добавлении нового функционала:

1. **Обновить README.md** — краткое описание
2. **Обновить docs/** — детальная документация
3. **Комментарии в коде** — PHPDoc для функций
4. **BLOCKS-DOCUMENTATION.md** — если новый блок

### PHPDoc формат

```php
/**
 * Получает настройки сайта
 *
 * @return array{
 *   phone: string,
 *   email: string,
 *   address: string,
 *   work_hours: string,
 *   socials: array<string,string>
 * }
 */
function mosaic_get_site_settings(): array {
    // ...
}
```

## Оптимизация

### CSS/JS

```php
// Минимизация на проде
if (!defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG) {
    $suffix = '.min';
} else {
    $suffix = '';
}

wp_enqueue_script('mosaic-main', get_template_directory_uri() . "/assets/js/main{$suffix}.js");
```

### Database queries

```php
// ❌ Неправильно: N+1 запросов
foreach ($posts as $post) {
    $meta = get_post_meta($post->ID, '_key', true);
}

// ✅ Правильно: один запрос
update_post_meta_cache(wp_list_pluck($posts, 'ID'));
foreach ($posts as $post) {
    $meta = get_post_meta($post->ID, '_key', true);
}
```

### Кеширование

```php
// Transients для тяжёлых запросов
$data = get_transient('mosaic_catalog_stats');
if (false === $data) {
    $data = mosaic_calculate_stats(); // тяжёлый запрос
    set_transient('mosaic_catalog_stats', $data, HOUR_IN_SECONDS);
}
```

## Полезные ресурсы

### Внутренние
- [Архитектура темы](architecture.md)
- [Блоки и компоненты](blocks.md)
- [Workflow правила](../.cursor/rules/workflow.mdc)
- [Mosaic правила](../.cursor/rules/mosaic.mdc)

### Внешние
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [WordPress Theme Handbook](https://developer.wordpress.org/themes/)
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Swiper.js API](https://swiperjs.com/swiper-api)

---

**Помни:** Код пишется один раз, читается — много раз. Делай его понятным! 🎯

