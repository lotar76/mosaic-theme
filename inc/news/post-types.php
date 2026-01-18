<?php

declare(strict_types=1);

/**
 * Новости:
 * - CPT: news (новости)
 *
 * URL архива: /news/
 * URL новости: /news/{post_name}/
 */

/**
 * Регистрируем query var для /news/
 */
add_filter('query_vars', static function (array $vars): array {
	$vars[] = 'mosaic_news_root';
	return $vars;
});

/**
 * CPT: news
 */
add_action('init', static function (): void {
	register_post_type('news', [
		'labels' => [
			'name' => 'Новости',
			'singular_name' => 'Новость',
			'menu_name' => 'Новости',
			'add_new' => 'Добавить новость',
			'add_new_item' => 'Добавить новую новость',
			'edit_item' => 'Редактировать новость',
			'new_item' => 'Новая новость',
			'view_item' => 'Просмотр новости',
			'search_items' => 'Поиск новостей',
			'not_found' => 'Новости не найдены',
			'not_found_in_trash' => 'В корзине новостей нет',
		],
		'public' => true,
		'menu_icon' => 'dashicons-megaphone',
		'menu_position' => 57,

		'supports' => ['title', 'editor', 'thumbnail'],

		'has_archive' => false,
		'show_in_rest' => true,
		'rewrite' => [
			'slug' => 'news',
			'with_front' => false,
		],
	]);
}, 0);

/**
 * Генерация ссылки новости: /news/{post_name}/
 */
add_filter('post_type_link', static function (string $permalink, WP_Post $post): string {
	if ($post->post_type !== 'news') {
		return $permalink;
	}

	return home_url('/news/' . $post->post_name . '/');
}, 10, 2);

/**
 * Rewrite rules
 */
add_action('init', static function (): void {
	// /news/
	add_rewrite_rule(
		'^news/?$',
		'index.php?mosaic_news_root=1',
		'top'
	);

	// Новости: /news/{slug}/
	add_rewrite_rule(
		'^news/([^/]+)/?$',
		'index.php?news=$matches[1]',
		'bottom'
	);
}, 20);

/**
 * Обработка корневого /news/ - загружаем шаблон page-news.php
 */
add_action('template_redirect', static function (): void {
	if ((string) get_query_var('mosaic_news_root') !== '1') {
		return;
	}

	$template = locate_template('page-news.php');
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
 * Обработка 404 для новостей
 */
add_action('template_redirect', static function (): void {
	if (!is_404()) {
		return;
	}

	$requestUri = trim((string) ($_SERVER['REQUEST_URI'] ?? ''), '/');
	$requestUri = strtok($requestUri, '?');

	if (strpos($requestUri, 'news/') !== 0) {
		return;
	}

	$parts = explode('/', $requestUri);
	// Ожидаем: news/{slug}
	if (count($parts) !== 2) {
		return;
	}

	$newsSlug = $parts[1];
	if ($newsSlug === '') {
		return;
	}

	$newsPost = get_page_by_path($newsSlug, OBJECT, 'news');
	if (!$newsPost || $newsPost->post_status !== 'publish') {
		return;
	}

	global $wp_query, $post;

	$wp_query->is_404 = false;
	$wp_query->is_single = true;
	$wp_query->is_singular = true;
	$wp_query->queried_object = $newsPost;
	$wp_query->queried_object_id = $newsPost->ID;
	$wp_query->post = $newsPost;
	$wp_query->posts = [$newsPost];
	$wp_query->post_count = 1;
	$wp_query->found_posts = 1;

	$post = $newsPost;
	setup_postdata($post);

	status_header(200);

	$template = locate_template('single-news.php');
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
	if (get_option('mosaic_news_rewrite_flushed') !== '2') {
		flush_rewrite_rules(true);
		update_option('mosaic_news_rewrite_flushed', '2', false);
	}
}, 999);

/**
 * Мета-бокс для slug
 */
add_action('add_meta_boxes', static function (): void {
	add_meta_box(
		'mosaic_news_slug',
		'URL (Slug)',
		'mosaic_news_slug_meta_box_render',
		'news',
		'side',
		'high'
	);
});

/**
 * Рендер мета-бокса slug
 */
function mosaic_news_slug_meta_box_render(WP_Post $post): void {
	wp_nonce_field('mosaic_news_slug_save', 'mosaic_news_slug_nonce');
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
			Slug используется в URL новости.
		</p>
		<input
			type="text"
			id="mosaic_news_slug"
			name="mosaic_news_slug"
			value="<?= esc_attr($suggestedSlug !== '' ? $suggestedSlug : $slug); ?>"
			class="large-text"
			placeholder="<?= $isNew ? 'Будет сгенерирован из заголовка' : ''; ?>"
		>
		<?php if ($isBadSlug && $suggestedSlug !== '') : ?>
			<p class="description" style="margin-top: 4px; color: #d63638;">
				Предложен новый slug: <strong><?= esc_html($suggestedSlug); ?></strong>
			</p>
		<?php endif; ?>
		<?php
		$displaySlug = $suggestedSlug !== '' ? $suggestedSlug : $slug;
		if (!$isNew && $displaySlug !== '') :
			$previewUrl = home_url('/news/' . $displaySlug . '/');
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
add_action('save_post_news', static function (int $postId): void {
	if (!isset($_POST['mosaic_news_slug_nonce'])) {
		return;
	}
	if (!wp_verify_nonce($_POST['mosaic_news_slug_nonce'], 'mosaic_news_slug_save')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $postId)) {
		return;
	}

	$inputSlug = isset($_POST['mosaic_news_slug']) ? trim($_POST['mosaic_news_slug']) : '';

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

	$newSlug = mosaic_unique_news_slug($newSlug, $postId);

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
 * Генерация уникального slug для новости
 */
function mosaic_unique_news_slug(string $slug, int $postId): string {
	global $wpdb;

	$originalSlug = $slug;
	$suffix = 2;

	while (true) {
		$existing = $wpdb->get_var($wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'news' AND ID != %d LIMIT 1",
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
 * Получить новости для фронта
 *
 * @param int $limit Лимит (-1 = все)
 * @return array<int, array{id:int,title:string,url:string,image_url:string,date:string}>
 */
function mosaic_get_news_posts(int $limit = -1): array {
	$args = [
		'post_type' => 'news',
		'post_status' => 'publish',
		'posts_per_page' => $limit,
		'orderby' => 'date',
		'order' => 'DESC',
	];

	$query = new WP_Query($args);
	$newsItems = [];

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$postId = get_the_ID();

			// Получаем первое изображение из галереи или миниатюру
			$gallery = get_post_meta($postId, '_mosaic_news_gallery', true);
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

			$newsItems[] = [
				'id' => $postId,
				'title' => get_the_title(),
				'url' => get_permalink(),
				'image_url' => $imageUrl,
				'date' => get_the_date('d.m.Y'),
			];
		}
		wp_reset_postdata();
	}

	return $newsItems;
}
