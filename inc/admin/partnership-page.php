<?php

declare(strict_types=1);

/**
 * Страница "Партнерская программа" (Админка -> Партнерская программа).
 * Управление контентом страницы /partnership/
 */

/**
 * @return array{
 *   title: string,
 *   content: string
 * }
 */
function mosaic_get_partnership_page_defaults(): array {
	return [
		'title' => 'Партнерская программа',
		'content' => 'Присоединяйтесь к нашей партнерской программе и получайте выгодные условия сотрудничества.',
	];
}

/**
 * @param mixed $value
 * @return array
 */
function mosaic_sanitize_partnership_page_option(mixed $value): array {
	$defaults = mosaic_get_partnership_page_defaults();

	if (!is_array($value)) {
		return $defaults;
	}

	$title = sanitize_text_field((string) ($value['title'] ?? ''));
	$content = wp_kses_post((string) ($value['content'] ?? ''));

	return [
		'title' => $title !== '' ? $title : $defaults['title'],
		'content' => $content !== '' ? $content : $defaults['content'],
	];
}

/**
 * @return array
 */
function mosaic_get_partnership_page(): array {
	$opt = get_option('mosaic_partnership_page', mosaic_get_partnership_page_defaults());
	return mosaic_sanitize_partnership_page_option($opt);
}

if (is_admin()) {
	add_action('admin_menu', static function (): void {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		$hook = add_menu_page(
			'Партнеры',
			'Партнеры',
			'edit_theme_options',
			'mosaic-partnership-page',
			'mosaic_render_partnership_page_admin',
			'dashicons-businessman',
			57
		);

		add_action('admin_enqueue_scripts', static function (string $hookSuffix) use ($hook): void {
			if ($hookSuffix !== $hook) {
				return;
			}

			echo '<style>
				.mosaic-partnership-page-wrap { max-width: 1400px; }
				.mosaic-section { background: #fff; border: 1px solid #dcdcde; border-radius: 14px; margin-bottom: 24px; overflow: hidden; }
				.mosaic-section-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 18px; background: linear-gradient(180deg, #101010 0%, #0b0b0b 100%); color: #fff; }
				.mosaic-section-title { font-size: 16px; font-weight: 600; margin: 0; }
				.mosaic-section-body { padding: 18px; }
				.mosaic-field { margin-bottom: 14px; }
				.mosaic-label { display: block; font-weight: 600; margin-bottom: 6px; }
				.mosaic-input { width: 100%; border-radius: 8px; padding: 10px 12px; border: 1px solid #dcdcde; }
				.mosaic-muted { color: #7a7a7a; font-size: 12px; margin-top: 6px; }
			</style>';
		});
	});

	add_action('admin_init', static function (): void {
		$existing = get_option('mosaic_partnership_page', null);
		if ($existing === false) {
			add_option('mosaic_partnership_page', mosaic_get_partnership_page_defaults(), '', false);
		}

		register_setting(
			'mosaic_partnership_page_group',
			'mosaic_partnership_page',
			[
				'type' => 'array',
				'sanitize_callback' => 'mosaic_sanitize_partnership_page_option',
				'default' => [],
			]
		);
	});
}

add_action('admin_post_mosaic_save_partnership_page', static function (): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}
	check_admin_referer('mosaic_partnership_page_save', 'mosaic_partnership_page_nonce');

	$data = mosaic_sanitize_partnership_page_option([
		'title' => isset($_POST['title']) ? (string) $_POST['title'] : '',
		'content' => isset($_POST['content']) ? (string) $_POST['content'] : '',
	]);

	update_option('mosaic_partnership_page', $data, false);

	$redirect = add_query_arg(['page' => 'mosaic-partnership-page', 'updated' => '1'], admin_url('admin.php'));
	wp_safe_redirect($redirect);
	exit;
});

function mosaic_render_partnership_page_admin(): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}

	$data = mosaic_get_partnership_page();

	echo '<div class="wrap mosaic-partnership-page-wrap">';
	echo '<h1>Партнерская программа</h1>';
	echo '<p class="description">Настройка контента страницы /partnership/</p>';

	if (isset($_GET['updated']) && (string) $_GET['updated'] === '1') {
		echo '<div class="notice notice-success is-dismissible"><p>Сохранено.</p></div>';
	}

	echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
	echo '<input type="hidden" name="action" value="mosaic_save_partnership_page">';
	wp_nonce_field('mosaic_partnership_page_save', 'mosaic_partnership_page_nonce');

	// Content Section
	echo '<div class="mosaic-section">';
	echo '<div class="mosaic-section-header"><p class="mosaic-section-title">Контент страницы</p></div>';
	echo '<div class="mosaic-section-body">';

	echo '<p class="mosaic-field"><label class="mosaic-label">Заголовок</label>';
	echo '<input type="text" class="mosaic-input" name="title" value="' . esc_attr($data['title']) . '"></p>';

	echo '<p class="mosaic-field"><label class="mosaic-label">Содержание</label>';

	// Use WordPress editor
	wp_editor(
		$data['content'],
		'content',
		[
			'textarea_name' => 'content',
			'textarea_rows' => 15,
			'media_buttons' => true,
			'teeny' => false,
			'tinymce' => [
				'toolbar1' => 'formatselect,bold,italic,underline,bullist,numlist,link,unlink,blockquote,alignleft,aligncenter,alignright,undo,redo',
			],
		]
	);

	echo '</p>';

	echo '<p class="mosaic-muted">Текст будет отображаться на странице. Ниже текста автоматически добавится форма "Получить консультацию".</p>';

	echo '</div></div>';

	submit_button('Сохранить');
	echo '</form></div>';
}
