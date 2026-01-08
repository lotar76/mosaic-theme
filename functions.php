<?php

declare(strict_types=1);

// Bootstrap темы: подключаем модули из inc/.

require_once __DIR__ . '/inc/telegram-config.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/enqueue.php';
require_once __DIR__ . '/inc/settings.php';

// Каталог (CPT/tax/meta)
require_once __DIR__ . '/inc/catalog/post-types.php';
require_once __DIR__ . '/inc/catalog/term-meta.php';
require_once __DIR__ . '/inc/catalog/item-meta.php';

// Админ-подсистемы, которые также нужны на фронте (get_* функции)
require_once __DIR__ . '/inc/admin/process.php';
require_once __DIR__ . '/inc/admin/news.php';

// Contact form handler
require_once __DIR__ . '/inc/contact-handler.php';
