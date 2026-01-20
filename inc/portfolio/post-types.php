<?php

declare(strict_types=1);

/**
 * Портфолио:
 * - taxonomy: portfolio_category (разделы: Интерьеры, Коммерческое и т.д.)
 * - CPT: portfolio (проекты)
 *
 * URL раздела: /portfolio/{slug}/
 * URL проекта: /portfolio/{category}/{post_name}/
 */

/**
 * Регистрируем query var для /portfolio/
 */
add_filter('query_vars', static function (array $vars): array {
	$vars[] = 'mosaic_portfolio_root';
	return $vars;
});

/**
 * CPT + Taxonomy
 */
add_action('init', static function (): void {
	// Таксономия: portfolio_category
	register_taxonomy('portfolio_category', ['portfolio'], [
		'labels' => [
			'name' => 'Разделы портфолио',
			'singular_name' => 'Раздел портфолио',
			'menu_name' => 'Разделы',
			'all_items' => 'Все разделы',
			'edit_item' => 'Редактировать раздел',
			'view_item' => 'Просмотр раздела',
			'update_item' => 'Обновить раздел',
			'add_new_item' => 'Добавить раздел',
			'new_item_name' => 'Название раздела',
			'search_items' => 'Поиск разделов',
		],
		'public' => true,
		'hierarchical' => true,
		'show_admin_column' => true,
		'show_in_rest' => true,
		'rewrite' => [
			'slug' => 'portfolio',
			'with_front' => false,
			'hierarchical' => false,
		],
	]);

	// CPT: portfolio
	register_post_type('portfolio', [
		'labels' => [
			'name' => 'Портфолио',
			'singular_name' => 'Проект',
			'menu_name' => 'Портфолио',
			'add_new' => 'Добавить проект',
			'add_new_item' => 'Добавить новый проект',
			'edit_item' => 'Редактировать проект',
			'new_item' => 'Новый проект',
			'view_item' => 'Просмотр проекта',
			'search_items' => 'Поиск проектов',
			'not_found' => 'Проекты не найдены',
			'not_found_in_trash' => 'В корзине проектов нет',
		],
		'public' => true,
		'menu_icon' => 'dashicons-portfolio',
		'menu_position' => 56, // В блоке "Мозаика"

		'supports' => ['title', 'thumbnail'],

		'has_archive' => false,
		'show_in_rest' => true,
		'rewrite' => [
			'slug' => 'portfolio',
			'with_front' => false,
		],
		'taxonomies' => ['portfolio_category'],
	]);
}, 0);

/**
 * Генерация ссылки проекта: /portfolio/{post_name}/
 */
add_filter('post_type_link', static function (string $permalink, WP_Post $post): string {
	if ($post->post_type !== 'portfolio') {
		return $permalink;
	}

	return home_url('/portfolio/' . $post->post_name . '/');
}, 10, 2);

/**
 * Rewrite rules
 */
add_action('init', static function (): void {
	// /portfolio/
	add_rewrite_rule(
		'^portfolio/?$',
		'index.php?mosaic_portfolio_root=1',
		'top'
	);

	// Проекты: /portfolio/{slug}/
	add_rewrite_rule(
		'^portfolio/([^/]+)/?$',
		'index.php?portfolio=$matches[1]',
		'bottom'
	);
}, 20);

/**
 * Обработка корневого /portfolio/ - загружаем шаблон page-portfolio.php
 */
add_action('template_redirect', static function (): void {
	if ((string) get_query_var('mosaic_portfolio_root') !== '1') {
		return;
	}

	$template = locate_template('page-portfolio.php');
	if (!$template) {
		return;
	}

	status_header(200);

	global $wp_query;
	$wp_query->is_404 = false;
	$wp_query->is_page = true;

	load_template($template);
	exit;
}, 1);

/**
 * Обработка 404 для проектов портфолио
 */
add_action('template_redirect', static function (): void {
	if (!is_404()) {
		return;
	}

	$requestUri = trim((string) ($_SERVER['REQUEST_URI'] ?? ''), '/');
	$requestUri = strtok($requestUri, '?');

	if (strpos($requestUri, 'portfolio/') !== 0) {
		return;
	}

	$parts = explode('/', $requestUri);
	// Ожидаем: portfolio/{slug}
	if (count($parts) !== 2) {
		return;
	}

	$projectSlug = $parts[1];
	if ($projectSlug === '') {
		return;
	}

	$project = get_page_by_path($projectSlug, OBJECT, 'portfolio');
	if (!$project || $project->post_status !== 'publish') {
		return;
	}

	global $wp_query, $post;

	$wp_query->is_404 = false;
	$wp_query->is_single = true;
	$wp_query->is_singular = true;
	$wp_query->queried_object = $project;
	$wp_query->queried_object_id = $project->ID;
	$wp_query->post = $project;
	$wp_query->posts = [$project];
	$wp_query->post_count = 1;
	$wp_query->found_posts = 1;

	$post = $project;
	setup_postdata($post);

	status_header(200);

	$template = locate_template('single-portfolio.php');
	if (!$template) {
		$template = locate_template('single.php');
	}

	if ($template) {
		load_template($template);
		exit;
	}
}, 5);

/**
 * Flush rewrite rules при активации темы
 */
add_action('after_switch_theme', static function (): void {
	flush_rewrite_rules(true);
});

/**
 * Одноразовый flush rewrite rules при первом добавлении CPT
 */
add_action('init', static function (): void {
	if (get_option('mosaic_portfolio_rewrite_flushed') !== '2') {
		flush_rewrite_rules(true);
		update_option('mosaic_portfolio_rewrite_flushed', '2', false);
	}
}, 999);

/**
 * Мета-бокс для slug
 */
add_action('add_meta_boxes', static function (): void {
	add_meta_box(
		'mosaic_portfolio_slug',
		'URL (Slug)',
		'mosaic_portfolio_slug_meta_box_render',
		'portfolio',
		'side',
		'high'
	);
});

/**
 * Рендер мета-бокса slug
 */
function mosaic_portfolio_slug_meta_box_render(WP_Post $post): void {
	wp_nonce_field('mosaic_portfolio_slug_save', 'mosaic_portfolio_slug_nonce');
	$slug = $post->post_name;
	$isNew = $post->post_status === 'auto-draft';

	$isBadSlug = preg_match('/^(d[0-9a-f]-[0-9a-f]{2}-|%d[0-9a-f]|chernovik)/i', $slug)
		|| strpos($slug, 'd0-') !== false
		|| strpos($slug, 'd1-') !== false;

	$suggestedSlug = '';
	if ($isBadSlug && $post->post_title !== '') {
		$suggestedSlug = mosaic_generate_slug($post->post_title);
	}
	?>
	<style>
		.mosaic-slug-field code {
			word-break: break-all;
			display: inline-block;
			max-width: 100%;
		}
		.mosaic-slug-field .large-text {
			width: 100%;
			box-sizing: border-box;
		}
	</style>
	<div class="mosaic-slug-field">
		<p class="description" style="margin-bottom: 8px;">
			Slug используется в URL проекта.
		</p>
		<input
			type="text"
			id="mosaic_portfolio_slug"
			name="mosaic_portfolio_slug"
			value="<?= esc_attr($suggestedSlug !== '' ? $suggestedSlug : $slug); ?>"
			class="large-text"
			placeholder="<?= $isNew ? 'Будет сгенерирован из заголовка' : ''; ?>"
		>
		<?php if ($isBadSlug && $suggestedSlug !== '') : ?>
			<p class="description" style="margin-top: 4px; color: #d63638;">
				⚠️ Предложен новый slug: <strong><?= esc_html($suggestedSlug); ?></strong>
			</p>
		<?php endif; ?>
		<?php
		$displaySlug = $suggestedSlug !== '' ? $suggestedSlug : $slug;
		if (!$isNew && $displaySlug !== '') :
			$previewUrl = home_url('/portfolio/' . $displaySlug . '/');
			?>
			<p class="description" style="margin-top: 8px;">
				<strong>URL:</strong> <code><?= esc_html($previewUrl); ?></code>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Сохранение slug
 */
add_action('save_post_portfolio', static function (int $postId): void {
	if (!isset($_POST['mosaic_portfolio_slug_nonce'])) {
		return;
	}
	if (!wp_verify_nonce($_POST['mosaic_portfolio_slug_nonce'], 'mosaic_portfolio_slug_save')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $postId)) {
		return;
	}

	$inputSlug = isset($_POST['mosaic_portfolio_slug']) ? trim($_POST['mosaic_portfolio_slug']) : '';

	if ($inputSlug !== '') {
		$newSlug = mosaic_generate_slug($inputSlug);
	} else {
		$post = get_post($postId);
		if ($post && $post->post_title !== '') {
			$newSlug = mosaic_generate_slug($post->post_title);
		} else {
			return;
		}
	}

	if ($newSlug === '') {
		return;
	}

	$newSlug = mosaic_unique_portfolio_slug($newSlug, $postId);

	global $wpdb;
	$wpdb->update(
		$wpdb->posts,
		['post_name' => $newSlug],
		['ID' => $postId],
		['%s'],
		['%d']
	);

	clean_post_cache($postId);
});

/**
 * Генерация уникального slug для проекта
 */
function mosaic_unique_portfolio_slug(string $slug, int $postId): string {
	global $wpdb;

	$originalSlug = $slug;
	$suffix = 2;

	while (true) {
		$existing = $wpdb->get_var($wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'portfolio' AND ID != %d LIMIT 1",
			$slug,
			$postId
		));

		if (!$existing) {
			break;
		}

		$slug = $originalSlug . '-' . $suffix;
		$suffix++;

		if ($suffix > 100) {
			$slug = $originalSlug . '-' . uniqid();
			break;
		}
	}

	return $slug;
}

/**
 * Сид начальных разделов портфолио
 */
add_action('admin_init', static function (): void {
	if (!current_user_can('manage_options')) {
		return;
	}
	if (get_option('mosaic_portfolio_seeded_v1', null) === '1') {
		return;
	}

	$existing = get_terms([
		'taxonomy' => 'portfolio_category',
		'hide_empty' => false,
	]);
	if (!is_wp_error($existing) && is_array($existing) && count($existing) > 0) {
		add_option('mosaic_portfolio_seeded_v1', '1', '', false);
		return;
	}

	$categories = [
		['slug' => 'interiors', 'title' => 'Интерьеры'],
		['slug' => 'commercial', 'title' => 'Коммерческое'],
	];

	foreach ($categories as $cat) {
		wp_insert_term($cat['title'], 'portfolio_category', ['slug' => $cat['slug']]);
	}

	add_option('mosaic_portfolio_seeded_v1', '1', '', false);
});

/**
 * Получить проекты портфолио для фронта
 *
 * @param string $category Slug категории (пусто = все)
 * @param int $limit Лимит (-1 = все)
 * @return array<int, array{id:int,title:string,url:string,image_url:string,category:string,category_slug:string,pdf_file_url:string}>
 */
function mosaic_get_portfolio_projects(string $category = '', int $limit = -1): array {
	$args = [
		'post_type' => 'portfolio',
		'post_status' => 'publish',
		'posts_per_page' => $limit,
		'orderby' => 'date',
		'order' => 'DESC',
	];

	if ($category !== '') {
		$args['tax_query'] = [
			[
				'taxonomy' => 'portfolio_category',
				'field' => 'slug',
				'terms' => $category,
			],
		];
	}

	$query = new WP_Query($args);
	$projects = [];

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$postId = get_the_ID();

			$terms = get_the_terms($postId, 'portfolio_category');
			$categoryName = '';
			$categorySlug = '';
			if (!empty($terms) && !is_wp_error($terms)) {
				$term = reset($terms);
				$categoryName = $term->name;
				$categorySlug = $term->slug;
			}

			// Получаем первое изображение из галереи или миниатюру
			$gallery = get_post_meta($postId, '_mosaic_portfolio_gallery', true);
			$imageUrl = '';

			if (is_array($gallery) && count($gallery) > 0) {
				$firstImageId = (int) $gallery[0];
				$imageUrl = (string) wp_get_attachment_image_url($firstImageId, 'large');
			}

			if ($imageUrl === '') {
				$thumbnailId = get_post_thumbnail_id($postId);
				if ($thumbnailId) {
					$imageUrl = (string) wp_get_attachment_image_url($thumbnailId, 'large');
				}
			}

			// Получаем PDF файл
			$pdfFileId = (int) get_post_meta($postId, '_mosaic_portfolio_pdf_file', true);
			$pdfFileUrl = '';
			if ($pdfFileId > 0) {
				$pdfFileUrl = (string) wp_get_attachment_url($pdfFileId);
			}

			$projects[] = [
				'id' => $postId,
				'title' => get_the_title(),
				'url' => get_permalink(),
				'image_url' => $imageUrl,
				'category' => $categoryName,
				'category_slug' => $categorySlug,
				'pdf_file_url' => $pdfFileUrl,
			];
		}
		wp_reset_postdata();
	}

	return $projects;
}

/**
 * Получить категории портфолио
 *
 * @return array<int, array{slug:string,name:string,count:int}>
 */
function mosaic_get_portfolio_categories(): array {
	$terms = get_terms([
		'taxonomy' => 'portfolio_category',
		'hide_empty' => false,
	]);

	if (is_wp_error($terms) || !is_array($terms)) {
		return [];
	}

	$categories = [];
	foreach ($terms as $term) {
		if (!($term instanceof WP_Term)) {
			continue;
		}
		$categories[] = [
			'slug' => $term->slug,
			'name' => $term->name,
			'count' => (int) $term->count,
		];
	}

	return $categories;
}
