<?php

declare(strict_types=1);

/**
 * Функции рендера меню по зонам.
 */

/**
 * Рендерит меню для указанной зоны.
 *
 * @param string $zone Zone key (desktop_left, tablet_dropdown, etc.)
 * @param array{
 *   tag?: string,
 *   class?: string,
 *   item_class?: string,
 *   link_class?: string,
 *   location?: string,
 *   fallback?: array<int, array{title: string, url: string}>
 * } $options Опции рендера
 */
function mosaic_render_menu_zone(string $zone, array $options = []): void {
	$tag = $options['tag'] ?? 'ul';
	$class = $options['class'] ?? '';
	$itemClass = $options['item_class'] ?? '';
	$linkClass = $options['link_class'] ?? 'hover:text-primary transition-colors';
	$location = $options['location'] ?? 'primary';
	$fallback = $options['fallback'] ?? [];

	$items = mosaic_get_menu_items_for_zone($zone, $location);

	// Если меню пустое и есть fallback — используем его
	if (empty($items) && !empty($fallback)) {
		$items = array_map(function ($item, $index): array {
			return [
				'id' => 0,
				'title' => $item['title'] ?? '',
				'url' => $item['url'] ?? '#',
				'target' => $item['target'] ?? '',
				'classes' => [],
				'order' => $index,
			];
		}, $fallback, array_keys($fallback));
	}

	if (empty($items)) {
		return;
	}

	$tagEscaped = tag_escape($tag);

	echo '<' . $tagEscaped;
	if ($class !== '') {
		echo ' class="' . esc_attr($class) . '"';
	}
	echo '>';

	foreach ($items as $item) {
		$itemClasses = $itemClass;
		if (!empty($item['classes'])) {
			$itemClasses .= ' ' . implode(' ', $item['classes']);
		}
		$itemClasses = trim($itemClasses);

		$target = $item['target'] === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';

		echo '<li';
		if ($itemClasses !== '') {
			echo ' class="' . esc_attr($itemClasses) . '"';
		}
		echo '>';

		echo '<a href="' . esc_url($item['url']) . '"';
		echo ' class="' . esc_attr($linkClass) . '"';
		echo ' aria-label="' . esc_attr($item['title']) . '"';
		echo $target;
		echo '>';
		echo esc_html($item['title']);
		echo '</a>';

		echo '</li>';
	}

	echo '</' . $tagEscaped . '>';
}

/**
 * Проверяет, есть ли пункты меню в указанной зоне.
 *
 * @param string $zone Zone key
 * @param string $location Menu location (default: 'primary')
 * @return bool
 */
function mosaic_has_menu_items_in_zone(string $zone, string $location = 'primary'): bool {
	$items = mosaic_get_menu_items_for_zone($zone, $location);
	return !empty($items);
}

/**
 * Возвращает количество пунктов меню в указанной зоне.
 *
 * @param string $zone Zone key
 * @param string $location Menu location (default: 'primary')
 * @return int
 */
function mosaic_count_menu_items_in_zone(string $zone, string $location = 'primary'): int {
	$items = mosaic_get_menu_items_for_zone($zone, $location);
	return count($items);
}

/**
 * Получить fallback-меню для пустого состояния.
 * Используется когда меню ещё не настроено в админке.
 *
 * @return array<string, array<int, array{title: string, url: string}>>
 */
function mosaic_get_menu_fallback(): array {
	return [
		'desktop_left' => [
			['title' => 'Каталог', 'url' => '/catalog'],
			['title' => 'Портфолио', 'url' => '/#portfolio'],
			['title' => 'О нас', 'url' => '/#about'],
			['title' => 'Новости', 'url' => '/#news'],
		],
		'desktop_right' => [
			['title' => 'Магазин', 'url' => '/shop'],
			['title' => 'Шоурум', 'url' => '/#showroom'],
			['title' => 'Контакты', 'url' => '/#contact-form'],
		],
		'tablet_inline' => [
			['title' => 'Каталог', 'url' => '/catalog'],
			['title' => 'Портфолио', 'url' => '/#portfolio'],
			['title' => 'Шоурум', 'url' => '/#showroom'],
		],
		'tablet_dropdown' => [
			['title' => 'О нас', 'url' => '/#about'],
			['title' => 'Новости', 'url' => '/#news'],
			['title' => 'Магазин', 'url' => '/shop'],
			['title' => 'Контакты', 'url' => '/#contact-form'],
		],
		'mobile_offcanvas' => [
			['title' => 'Каталог', 'url' => '/catalog'],
			['title' => 'Портфолио', 'url' => '/#portfolio'],
			['title' => 'О нас', 'url' => '/#about'],
			['title' => 'Новости', 'url' => '/#news'],
			['title' => 'Магазин', 'url' => '/shop'],
			['title' => 'Шоурум', 'url' => '/#showroom'],
			['title' => 'Контакты', 'url' => '/#contact-form'],
		],
		'footer' => [
			['title' => 'Каталог', 'url' => '/catalog'],
			['title' => 'О нас', 'url' => '/#about'],
			['title' => 'Контакты', 'url' => '/#contact-form'],
		],
	];
}

/**
 * Рендерит меню для указанной зоны с fallback.
 *
 * @param string $zone Zone key
 * @param array $options Опции рендера (см. mosaic_render_menu_zone)
 */
function mosaic_render_menu_zone_with_fallback(string $zone, array $options = []): void {
	$fallbacks = mosaic_get_menu_fallback();
	$options['fallback'] = $fallbacks[$zone] ?? [];

	mosaic_render_menu_zone($zone, $options);
}
