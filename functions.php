<?php

declare(strict_types=1);

// Bootstrap темы: подключаем модули из inc/.

require_once __DIR__ . '/inc/telegram-config.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/enqueue.php';
require_once __DIR__ . '/inc/settings.php';

// Меню (зоны, админ-поля, рендер)
require_once __DIR__ . '/inc/menu/config.php';
require_once __DIR__ . '/inc/menu/admin-fields.php';
require_once __DIR__ . '/inc/menu/render.php';

// Каталог (CPT/tax/meta)
require_once __DIR__ . '/inc/catalog/post-types.php';
require_once __DIR__ . '/inc/catalog/term-meta.php';
require_once __DIR__ . '/inc/catalog/item-meta.php';

// Портфолио (CPT/tax/meta)
require_once __DIR__ . '/inc/portfolio/post-types.php';
require_once __DIR__ . '/inc/portfolio/item-meta.php';

// Новости (CPT/meta)
require_once __DIR__ . '/inc/news/post-types.php';
require_once __DIR__ . '/inc/news/item-meta.php';

// Админ-подсистемы, которые также нужны на фронте (get_* функции)
require_once __DIR__ . '/inc/admin/process.php';
require_once __DIR__ . '/inc/admin/about-home.php';
require_once __DIR__ . '/inc/admin/about-page.php';
require_once __DIR__ . '/inc/admin/showroom-page.php';
require_once __DIR__ . '/inc/admin/benefits-page.php';
require_once __DIR__ . '/inc/admin/partnership-page.php';

// Contact form handler
require_once __DIR__ . '/inc/contact-handler.php';

// 301 Редиректы (фронтенд + админ)
require_once __DIR__ . '/inc/redirects.php';
require_once __DIR__ . '/inc/admin/redirects.php';