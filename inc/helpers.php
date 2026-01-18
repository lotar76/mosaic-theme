<?php

declare(strict_types=1);

/**
 * Брейкпоинты темы (единый источник).
 */
function mosaic_get_breakpoints(): array {
	static $breakpoints = null;
	if (is_array($breakpoints)) {
		return $breakpoints;
	}

	/** @var array $loaded */
	$loaded = require get_template_directory() . '/inc/breakpoints.php';
	$breakpoints = $loaded;

	return $breakpoints;
}

/**
 * Категории каталога (legacy fallback для шаблонов).
 *
 * @return array<int, array{slug: string, title: string, image: string, video?: string}>
 */
function mosaic_get_catalog_categories(): array {
	return [
		[
			'slug' => 'modern',
			'title' => 'Современные панно',
			'image' => '/img/catalog/1.png',
		],
		[
			'slug' => 'classic',
			'title' => 'Классическое панно',
			'image' => '/img/catalog/2.png',
		],
		[
			'slug' => 'luxury',
			'title' => 'Люксовые панно',
			'image' => '/img/catalog/3.png',
		],
		[
			'slug' => 'ethnic',
			'title' => 'Этнические панно',
			'image' => '/img/catalog/4.png',
		],
	];
}

/**
 * Добавляет разделитель (черту) в админ-меню WP на указанной позиции.
 *
 * Важно: позиции в WP меню часто строковые и могут быть "десятичными" (например "58.0001").
 *
 * @param string $position
 * @param string $label Опциональная подпись рядом с разделителем
 */
function mosaic_add_admin_menu_separator(string $position, string $label = ''): void {
	global $menu;

	if (!is_array($menu)) {
		return;
	}

	$slug = 'separator-mosaic-' . preg_replace('~[^a-z0-9_-]+~i', '-', $position);

	if ($label !== '') {
		// Разделитель с подписью — используем структуру обычного пункта меню
		$menu[$position] = [
			$label,                        // 0: menu title
			'read',                        // 1: capability
			$slug,                         // 2: menu slug
			'',                            // 3: page title
			'menu-top mosaic-labeled-separator', // 4: classes
			$slug,                         // 5: id
			'none',                        // 6: icon
		];
	} else {
		$menu[$position] = ['', 'read', $slug, '', 'wp-menu-separator'];
	}
}

/**
 * Скрывает стандартные пункты меню WordPress.
 */
function mosaic_hide_default_admin_menu_items(): void {
	// Записи (Posts)
	remove_menu_page('edit.php');
	// Комментарии
	remove_menu_page('edit-comments.php');
	// Консоль (Dashboard) — оставляем только для суперадминов
	if (!is_super_admin()) {
		remove_menu_page('index.php');
	}
}


