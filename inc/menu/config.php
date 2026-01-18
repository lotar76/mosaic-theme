<?php

declare(strict_types=1);

/**
 * Конфигурация меню: регистрация и определение зон.
 *
 * Зоны меню:
 * - desktop_left:     Desktop (>=1920px) — левая часть хедера
 * - desktop_right:    Desktop (>=1920px) — правая часть хедера
 * - tablet_inline:    Tablet (1280-1919px) — видимые пункты рядом с hamburger
 * - tablet_dropdown:  Tablet (1280-1919px) — пункты в выпадающем меню
 * - mobile_offcanvas: Mobile (<=1279px) — полное меню в offcanvas
 * - footer:           Подвал сайта
 */

/**
 * Возвращает определения всех зон меню.
 *
 * @return array<string, array{label: string, description: string}>
 */
function mosaic_get_menu_zones(): array {
	return [
		'desktop_left' => [
			'label' => 'Desktop: слева',
			'description' => 'Левая часть шапки на больших экранах (≥1920px)',
		],
		'desktop_right' => [
			'label' => 'Desktop: справа',
			'description' => 'Правая часть шапки на больших экранах (≥1920px)',
		],
		'tablet_inline' => [
			'label' => 'Tablet: видимые',
			'description' => 'Видимые пункты на планшете (1280-1919px)',
		],
		'tablet_dropdown' => [
			'label' => 'Tablet: hamburger',
			'description' => 'Пункты в выпадающем меню на планшете',
		],
		'mobile_offcanvas' => [
			'label' => 'Mobile: offcanvas',
			'description' => 'Боковое меню на мобильных (≤1279px)',
		],
		'footer' => [
			'label' => 'Подвал',
			'description' => 'Меню в подвале сайта',
		],
	];
}

/**
 * Возвращает ключи всех зон.
 *
 * @return string[]
 */
function mosaic_get_menu_zone_keys(): array {
	return array_keys(mosaic_get_menu_zones());
}

/**
 * Регистрация nav menu location.
 */
add_action('after_setup_theme', static function (): void {
	register_nav_menus([
		'primary' => 'Главное меню',
	]);
});

/**
 * Добавляем пункт "Меню" в основное меню админки (рядом с другими настройками темы).
 * Это ссылка на стандартную страницу WordPress "Внешний вид → Меню".
 */
if (is_admin()) {
	add_action('admin_menu', static function (): void {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		add_menu_page(
			'Меню сайта',
			'Меню',
			'edit_theme_options',
			'nav-menus.php', // Стандартная страница WordPress
			'', // Нет callback — используем page slug
			'dashicons-menu',
			58 // Между "Баннер на главной" (57) и "Настройки" (59)
		);
	}, 10);
}

/**
 * Получить пункты меню с их зонами.
 *
 * @param string $location Menu location (default: 'primary')
 * @return array<int, array{id: int, title: string, url: string, target: string, classes: string[], zones: string[], order: int}>
 */
function mosaic_get_menu_items_with_zones(string $location = 'primary'): array {
	$locations = get_nav_menu_locations();

	if (!isset($locations[$location])) {
		return [];
	}

	$menuId = $locations[$location];
	$menuItems = wp_get_nav_menu_items($menuId);

	if (!$menuItems || !is_array($menuItems)) {
		return [];
	}

	$result = [];
	$zoneKeys = mosaic_get_menu_zone_keys();

	foreach ($menuItems as $index => $item) {
		// Получаем сохранённые зоны для этого пункта
		$savedZones = get_post_meta($item->ID, '_mosaic_menu_zones', true);
		$zones = is_array($savedZones) ? $savedZones : [];

		// Фильтруем только валидные зоны
		$zones = array_filter($zones, fn($zone) => in_array($zone, $zoneKeys, true));

		$result[] = [
			'id' => (int) $item->ID,
			'title' => (string) $item->title,
			'url' => (string) $item->url,
			'target' => (string) $item->target,
			'classes' => is_array($item->classes) ? array_filter($item->classes) : [],
			'zones' => array_values($zones),
			'order' => $index,
		];
	}

	return $result;
}

/**
 * Получить пункты меню для конкретной зоны.
 *
 * @param string $zone Zone key
 * @param string $location Menu location (default: 'primary')
 * @return array<int, array{id: int, title: string, url: string, target: string, classes: string[], order: int}>
 */
function mosaic_get_menu_items_for_zone(string $zone, string $location = 'primary'): array {
	$allItems = mosaic_get_menu_items_with_zones($location);

	$filtered = array_filter($allItems, function ($item) use ($zone): bool {
		return in_array($zone, $item['zones'], true);
	});

	// Убираем ключ 'zones' из результата — он больше не нужен
	return array_map(function ($item): array {
		unset($item['zones']);
		return $item;
	}, array_values($filtered));
}
