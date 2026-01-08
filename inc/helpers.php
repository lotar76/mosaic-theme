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
 */
function mosaic_add_admin_menu_separator(string $position): void {
	global $menu;

	if (!is_array($menu)) {
		return;
	}

	$slug = 'separator-mosaic-' . preg_replace('~[^a-z0-9_-]+~i', '-', $position);
	$menu[$position] = ['', 'read', $slug, '', 'wp-menu-separator'];
}


