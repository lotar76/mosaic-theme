<?php

declare(strict_types=1);

/**
 * Каталог с вложенными URL:
 * - taxonomy: product_section (разделы, иерархическая)
 * - CPT: product (товары)
 *
 * URL раздела: /catalog/{slug}/ или /catalog/{parent}/{child}/
 * URL товара:  /catalog/{term_path}/{post_name}/
 */

/**
 * Получить путь терма (родители + сам терм) в виде slug/slug/slug
 */
if (!function_exists('mosaic_get_term_path')) {
	function mosaic_get_term_path(int $term_id, string $taxonomy): string
	{
		$term = get_term($term_id, $taxonomy);
		if (!$term || is_wp_error($term)) {
			return '';
		}

		$path = [];
		$current = $term;

		while ($current && !is_wp_error($current)) {
			array_unshift($path, $current->slug);

			if ((int) $current->parent > 0) {
				$current = get_term((int) $current->parent, $taxonomy);
			} else {
				break;
			}
		}

		return implode('/', $path);
	}
}

/**
 * Регистрируем query vars
 */
add_filter('query_vars', static function (array $vars): array {
	$vars[] = 'mosaic_catalog_root';
	$vars[] = 'contact'; // Для уведомлений после отправки формы
	return $vars;
});

/**
 * CPT + Taxonomy
 */
add_action('init', static function (): void {
	// Таксономия: product_section (иерархическая)
	register_taxonomy('product_section', ['product'], [
		'labels' => [
			'name' => 'Разделы каталога',
			'singular_name' => 'Раздел каталога',
		],
		'public' => true,
		'hierarchical' => true,
		'show_admin_column' => true,
		'show_in_rest' => true,
		'rewrite' => [
			'slug' => 'catalog',
			'with_front' => false,
			'hierarchical' => true, // даст /catalog/parent/child/
		],
	]);

	// CPT: product
	register_post_type('product', [
		'labels' => [
			'name' => 'Каталог',
			'singular_name' => 'Товар',
			'menu_name' => 'Каталог',
		],
		'public' => true,
		'menu_icon' => 'dashicons-screenoptions',
		'menu_position' => 56, // Позиция в админ-меню (перед "Баннер на главной" = 57)

		'supports' => ['title', 'thumbnail', 'page-attributes'],

		'has_archive' => false,
		'show_in_rest' => true,
		'rewrite' => [
			'slug' => 'catalog',
			'with_front' => false,
		],
		'taxonomies' => ['product_section'],
	]);
}, 0);

/**
 * Генерация ссылки товара: /catalog/{term_path}/{post_name}/
 */
add_filter('post_type_link', static function (string $permalink, WP_Post $post): string {
	if ($post->post_type !== 'product') {
		return $permalink;
	}

	$terms = get_the_terms($post->ID, 'product_section');
	if (empty($terms) || is_wp_error($terms)) {
		return home_url('/catalog/' . $post->post_name . '/');
	}

	// Берём первый терм (можно улучшить выбором "главного" терма)
	$term = reset($terms);
	$path = mosaic_get_term_path((int) $term->term_id, 'product_section');

	if ($path === '') {
		return home_url('/catalog/' . $post->post_name . '/');
	}

	return home_url('/catalog/' . $path . '/' . $post->post_name . '/');
}, 10, 2);

/**
 * Rewrite rules
 */
add_action('init', static function (): void {
	// /catalog/
	add_rewrite_rule(
		'^catalog/?$',
		'index.php?mosaic_catalog_root=1',
		'top'
	);

	// Товары: /catalog/{term_path}/{slug товара}/
	// Важно: может совпасть с /catalog/parent/child/ (архив терма),
	// но стоит 'bottom', чтобы правила таксономии сработали раньше.
	add_rewrite_rule(
		'^catalog/(.+)/([^/]+)/?$',
		'index.php?product=$matches[2]',
		'bottom'
	);
}, 20);

/**
 * Раннее определение товара по URL - модифицируем main query
 * Это нужно чтобы URL вида /catalog/category/product/?contact=success работали правильно
 * Используем pre_get_posts как рекомендует WordPress
 */
add_action('pre_get_posts', static function (WP_Query $query): void {
	// Только для главного запроса на фронтенде
	if (is_admin() || !$query->is_main_query()) {
		return;
	}

	// Получаем текущий URL path
	$requestUri = trim((string) ($_SERVER['REQUEST_URI'] ?? ''), '/');
	$requestUri = strtok($requestUri, '?'); // Убираем query string

	// strtok может вернуть false для пустой строки
	if ($requestUri === false || $requestUri === '') {
		return;
	}

	// Проверяем, начинается ли с catalog/
	if (strpos($requestUri, 'catalog/') !== 0) {
		return;
	}

	// Разбираем путь: catalog/{...}/{product_slug}
	$parts = explode('/', $requestUri);
	if (count($parts) < 3) {
		return; // Минимум: catalog / category / product
	}

	// Последний сегмент — потенциальный slug товара
	$productSlug = end($parts);
	if ($productSlug === '') {
		return;
	}

	// Ищем товар по slug
	$product = get_page_by_path($productSlug, OBJECT, 'product');
	if (!$product || $product->post_status !== 'publish') {
		return;
	}

	// Нашли товар — устанавливаем параметры запроса
	$query->set('post_type', 'product');
	$query->set('name', $productSlug);
	$query->set('p', $product->ID);
	$query->is_single = true;
	$query->is_singular = true;
	$query->is_404 = false;
});

/**
 * Обработка корневого /catalog/ - загружаем шаблон page-catalog.php
 */
add_action('template_redirect', static function (): void {
	if ((string) get_query_var('mosaic_catalog_root') !== '1') {
		return;
	}

	$template = locate_template('page-catalog.php');
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
 * Обработка товаров в каталоге — проверяем URL и загружаем товар если нужно
 * Срабатывает как для 404, так и для случаев когда WP не нашел товар правильно
 */
add_action('template_redirect', static function (): void {
	// Получаем текущий URL path
	$requestUri = trim((string) ($_SERVER['REQUEST_URI'] ?? ''), '/');
	$requestUri = strtok($requestUri, '?'); // Убираем query string

	// strtok может вернуть false для пустой строки
	if ($requestUri === false || $requestUri === '') {
		return;
	}

	// Проверяем, начинается ли с catalog/
	if (strpos($requestUri, 'catalog/') !== 0) {
		return;
	}

	// Разбираем путь: catalog/{...}/{product_slug}
	$parts = explode('/', $requestUri);
	if (count($parts) < 3) {
		return; // Минимум: catalog / category / product
	}

	// Последний сегмент — потенциальный slug товара
	$productSlug = end($parts);
	if ($productSlug === '') {
		return;
	}

	// Проверяем, не загружен ли уже правильный товар
	global $wp_query, $post;
	if (
		!is_404() &&
		is_singular('product') &&
		isset($post) &&
		$post instanceof WP_Post &&
		$post->post_name === $productSlug
	) {
		// Товар уже правильно загружен
		return;
	}

	// Ищем товар по slug
	$product = get_page_by_path($productSlug, OBJECT, 'product');
	if (!$product || $product->post_status !== 'publish') {
		return;
	}

	// Нашли товар — загружаем шаблон
	$wp_query->is_404 = false;
	$wp_query->is_single = true;
	$wp_query->is_singular = true;
	$wp_query->queried_object = $product;
	$wp_query->queried_object_id = $product->ID;
	$wp_query->post = $product;
	$wp_query->posts = [$product];
	$wp_query->post_count = 1;
	$wp_query->found_posts = 1;

	$post = $product;
	setup_postdata($post);

	status_header(200);

	$template = locate_template('single-product.php');
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
 * Мета-бокс для редактирования slug товара
 */
add_action('add_meta_boxes', static function (): void {
	add_meta_box(
		'mosaic_product_slug',
		'URL (Slug)',
		'mosaic_product_slug_meta_box_render',
		'product',
		'side',    // Перенесено в правый сайдбар
		'high'
	);
});

/**
 * Рендер мета-бокса slug
 */
function mosaic_product_slug_meta_box_render(WP_Post $post): void {
	wp_nonce_field('mosaic_product_slug_save', 'mosaic_product_slug_nonce');
	$slug = $post->post_name;
	$isNew = $post->post_status === 'auto-draft';

	// Проверяем, является ли slug "плохим" (URL-encoded кириллица или черновик)
	$isBadSlug = preg_match('/^(d[0-9a-f]-[0-9a-f]{2}-|%d[0-9a-f]|chernovik)/i', $slug)
		|| strpos($slug, 'd0-') !== false
		|| strpos($slug, 'd1-') !== false;

	// Если slug плохой и есть заголовок — предлагаем сгенерированный
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
			Slug используется в URL товара. Автоматически генерируется из заголовка при первом сохранении.
		</p>
		<input
			type="text"
			id="mosaic_product_slug"
			name="mosaic_product_slug"
			value="<?= esc_attr($suggestedSlug !== '' ? $suggestedSlug : $slug); ?>"
			class="large-text"
			placeholder="<?= $isNew ? 'Будет сгенерирован из заголовка' : ''; ?>"
		>
		<?php if ($isBadSlug && $suggestedSlug !== '') : ?>
			<p class="description" style="margin-top: 4px; color: #d63638;">
				⚠️ Старый slug содержал некорректные символы. Предложен новый: <strong><?= esc_html($suggestedSlug); ?></strong>. Сохраните запись для применения.
			</p>
		<?php endif; ?>
		<?php
		$displaySlug = $suggestedSlug !== '' ? $suggestedSlug : $slug;
		if (!$isNew && $displaySlug !== '') :
			$terms = get_the_terms($post->ID, 'product_section');
			$termPath = '';
			if (!empty($terms) && !is_wp_error($terms)) {
				$term = reset($terms);
				$termPath = mosaic_get_term_path((int) $term->term_id, 'product_section');
			}
			$previewUrl = $termPath !== ''
				? home_url('/catalog/' . $termPath . '/' . $displaySlug . '/')
				: home_url('/catalog/' . $displaySlug . '/');
			?>
			<p class="description" style="margin-top: 8px;">
				<strong>URL:</strong> <code><?= esc_html($previewUrl); ?></code>
			</p>
		<?php endif; ?>
	</div>
	<script>
	(function() {
		const titleField = document.getElementById('title');
		const slugField = document.getElementById('mosaic_product_slug');
		if (!titleField || !slugField) return;

		// Транслитерация кириллицы
		const translitMap = {
			'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
			'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
			'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
			'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
			'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya',
			' ': '-'
		};

		function transliterate(text) {
			return text.toLowerCase().split('').map(char => {
				return translitMap[char] !== undefined ? translitMap[char] : char;
			}).join('')
			.replace(/[^a-z0-9-]/g, '')
			.replace(/-+/g, '-')
			.replace(/^-|-$/g, '');
		}

		// Автозаполнение только для новых записей (пустой slug)
		let userModified = slugField.value !== '';

		slugField.addEventListener('input', function() {
			userModified = true;
		});

		titleField.addEventListener('input', function() {
			if (!userModified) {
				slugField.value = transliterate(titleField.value);
			}
		});
	})();
	</script>
	<?php
}

/**
 * Транслитерация кириллицы в латиницу
 */
function mosaic_transliterate(string $text): string {
	$translitMap = [
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
		'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
		'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
		'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
		'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo',
		'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
		'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
		'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
		'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
	];

	return strtr($text, $translitMap);
}

/**
 * Генерация slug из текста с транслитерацией
 */
function mosaic_generate_slug(string $text): string {
	// Транслитерация кириллицы
	$slug = mosaic_transliterate($text);
	// Приводим к нижнему регистру
	$slug = mb_strtolower($slug, 'UTF-8');
	// Заменяем пробелы и спецсимволы на дефисы
	$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
	// Убираем дефисы в начале и конце
	$slug = trim($slug, '-');

	return $slug;
}

/**
 * Сохранение slug
 */
add_action('save_post_product', static function (int $postId): void {
	// Проверки безопасности
	if (!isset($_POST['mosaic_product_slug_nonce'])) {
		return;
	}
	if (!wp_verify_nonce($_POST['mosaic_product_slug_nonce'], 'mosaic_product_slug_save')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $postId)) {
		return;
	}

	$inputSlug = isset($_POST['mosaic_product_slug']) ? trim($_POST['mosaic_product_slug']) : '';

	// Генерируем slug с транслитерацией
	if ($inputSlug !== '') {
		$newSlug = mosaic_generate_slug($inputSlug);
	} else {
		// Если slug пустой, генерируем из заголовка
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

	// Проверяем уникальность slug
	$newSlug = mosaic_unique_product_slug($newSlug, $postId);

	// Обновляем post_name напрямую в БД чтобы избежать бесконечного цикла
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
 * Генерация уникального slug для товара
 */
function mosaic_unique_product_slug(string $slug, int $postId): string {
	global $wpdb;

	$originalSlug = $slug;
	$suffix = 2;

	// Проверяем, существует ли такой slug у другого товара
	while (true) {
		$existing = $wpdb->get_var($wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'product' AND ID != %d LIMIT 1",
			$slug,
			$postId
		));

		if (!$existing) {
			break;
		}

		$slug = $originalSlug . '-' . $suffix;
		$suffix++;

		// Защита от бесконечного цикла
		if ($suffix > 100) {
			$slug = $originalSlug . '-' . uniqid();
			break;
		}
	}

	return $slug;
}

/**
 * Добавляем фильтр по разделам каталога в админке
 */
add_action('restrict_manage_posts', static function (string $postType): void {
	if ($postType !== 'product') {
		return;
	}

	$taxonomy = 'product_section';
	$selected = isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : '';

	wp_dropdown_categories([
		'show_option_all' => 'Все разделы',
		'taxonomy' => $taxonomy,
		'name' => $taxonomy,
		'orderby' => 'name',
		'selected' => $selected,
		'hierarchical' => true,
		'show_count' => true,
		'hide_empty' => false,
		'value_field' => 'slug',
	]);
});

/**
 * Применяем фильтр по разделам
 */
add_filter('parse_query', static function (WP_Query $query): void {
	global $pagenow;

	if (!is_admin() || $pagenow !== 'edit.php') {
		return;
	}

	$postType = $query->get('post_type');
	if ($postType !== 'product') {
		return;
	}

	$taxonomy = 'product_section';
	if (!empty($_GET[$taxonomy])) {
		$query->query_vars['tax_query'] = [
			[
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => sanitize_text_field($_GET[$taxonomy]),
			],
		];
	}
});
