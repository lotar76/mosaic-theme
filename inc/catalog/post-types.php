<?php

declare(strict_types=1);

/**
 * Полноценный каталог:
 * - taxonomy: catalog_category (разделы)
 * - CPT: catalog_item (элементы)
 *
 * URL раздела: /catalog/{slug}/
 */
add_action('init', static function (): void {
	register_taxonomy('catalog_category', ['catalog_item'], [
		'labels' => [
			'name' => 'Разделы каталога',
			'singular_name' => 'Раздел каталога',
		],
		'public' => true,
		'hierarchical' => true,
		'show_admin_column' => true,
		'show_in_rest' => true,
		'rewrite' => [
			// ВАЖНО: как используется в теме (home_url('/catalog/' . $slug . '/'))
			'slug' => 'catalog',
			'with_front' => false,
			'hierarchical' => true,
		],
	]);

	register_post_type('catalog_item', [
		'labels' => [
			'name' => 'Каталог',
			'singular_name' => 'Элемент каталога',
		],
		'public' => true,
		'menu_icon' => 'dashicons-screenoptions',
		// UI делаем через метабоксы (заголовок/описание/галерея/характеристики/похожие),
		// поэтому убираем стандартные title/editor/excerpt, оставляем thumbnail.
		'supports' => ['thumbnail'],
		'has_archive' => 'catalog',
		'show_in_rest' => true,
		'rewrite' => [
			'slug' => 'catalog/item',
			'with_front' => false,
		],
		'taxonomies' => ['catalog_category'],
	]);
});

add_action('after_switch_theme', static function (): void {
	flush_rewrite_rules();
});


