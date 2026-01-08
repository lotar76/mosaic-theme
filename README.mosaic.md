## Mosaic theme — контекст и карта проекта

Этот файл — чтобы держать контекст по кастомной логике темы и не раздувать хаотично `functions.php`.

---

### 0) Общая идея

В теме есть несколько “контентных подсистем”, которые управляются через админку:
- **Процесс работы** — репитер блоков (картинка/название/описание) + сортировка.
- **Новости** — список новостей (название, галерея, текст) + вывод на главной.
- **Каталог** — полноценная WP‑структура (taxonomy + CPT) с мета‑полями для разделов/товаров.
- **Глобальные настройки** — контакты/соцсети/адрес/график.

На фронте данные подтягиваются из этих источников, с фолбэками (чтобы главная не пустела).

---

### 1) Глобальные настройки сайта (опция)

Опция: `mosaic_site_settings` (array)

Поля:
- `phone` (string)
- `email` (string)
- `address` (string)
- `work_hours` (string)
- `socials` (array<string,string>): `telegram`, `whatsapp`, `vk`, `youtube`, `pinterest`

Используется:
- `header.php`, `footer.php`, `template-parts/social-icons.php`
- `front-page.php` (контактный блок)

---

### 2) Главная (баннер) (опция)

Опция: `mosaic_home_hero`
- `image_id` (int)
- `title` (string)
- `subtitle` (string)
- `button_text` (string)
- `button_url` (string)

---

### 3) Процесс работы (опция + админка)

Опция: `mosaic_work_process`

Структура:
- `blocks[]`:
  - `image_id` (int)
  - `image_url` (string) — fallback URL
  - `title` (string)
  - `description` (string)

Админка:
- страница: **таблица списка → редактирование одной карточкой**
- действия: save/delete/reorder с nonce
- сортировка: drag&drop в таблице (автосохранение порядка)

Фронт:
- `front-page.php` секция “Процесс работы” рендерит блоки из опции.

---

### 4) Новости (опция + админка)

Опция: `mosaic_news`

Структура:
- `items[]`:
  - `id` (int) — стабильный идентификатор
  - `title` (string)
  - `gallery_ids[]` (array<int>) — attachment IDs
  - `gallery_urls[]` (array<string>) — fallback URLs (для демо/сидов)
  - `content` (string) — HTML (через `wp_editor`, san: `wp_kses_post`)
  - `updated_at` (int)

Правило превью:
- для карточек/слайдера используем **первую** картинку галереи (`gallery_ids[0]`, иначе `gallery_urls[0]`).

Фронт:
- `front-page.php` секция “Новости и блог” рендерит новости из опции (с fallback на демо).

---

### 5) Каталог (полноценная WP‑структура)

#### 5.1 Разделы (taxonomy)

Taxonomy: `catalog_category`

Term meta:
- `mosaic_cat_image_id` (int) — картинка раздела (карточка)
- `mosaic_cat_interior_image_id` (int) — картинка “в интерьере”
- `mosaic_cat_video_url` (string) — mp4 URL

Hover-логика карточки раздела:
- если есть видео → hover показывает видео (когда готово)
- иначе если есть “в интерьере” → hover показывает interior image

#### 5.2 Товары (CPT)

CPT: `catalog_item`

Meta keys:
- `_mosaic_catalog_gallery_ids` (array<int>) — галерея товара
- `_mosaic_catalog_material` (string)
- `_mosaic_catalog_technique` (string)
- `_mosaic_catalog_size_color` (string)
- `_mosaic_catalog_related_ids` (array<int>) — похожие товары (исключая текущий)

UI товара:
- основной редактор WP отключён; заголовок/описание редактируются в метабоксе и сохраняются в `post_title`/`post_content`.
- “Похожие товары” — AJAX поиск по `catalog_item`, выбранные отображаются чипами.

Шаблоны:
- `taxonomy-catalog_category.php` — список товаров в разделе
- `single-catalog_item.php` — карточка товара (галерея/характеристики/описание/похожие)

---

### 6) Где что править (быстрая шпаргалка)

- Админка/опции/мета/handlers: `functions.php` (кандидат на вынос в `inc/`)
- Главная: `front-page.php`
- Страница каталога: `page-catalog.php`
- Раздел каталога: `taxonomy-catalog_category.php`
- Товар: `single-catalog_item.php`
- Hover‑логика каталога: `assets/css/main.css`, `assets/js/main.js`

---

### 7) Рекомендация (следующий шаг)

Вынести модули из `functions.php` в `inc/`:
- `inc/settings.php`
- `inc/admin/process.php`, `inc/admin/news.php`
- `inc/catalog/post-types.php`, `inc/catalog/term-meta.php`, `inc/catalog/item-meta.php`
- `inc/enqueue.php`

`functions.php` оставить как bootstrap (`require_once`).


