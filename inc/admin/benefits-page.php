<?php

declare(strict_types=1);

/**
 * Страница "С нами комфортно работать" (Админка -> Benefits).
 * Управление контентом блока benefits на главной странице
 */

/**
 * @return array{
 *   title: string,
 *   items: array<int, array{
 *     title: string,
 *     text: string,
 *     image_id: int
 *   }>
 * }
 */
function mosaic_get_benefits_defaults(): array {
	return [
		'title' => 'С нами комфортно работать',
		'items' => [
			[
				'title' => 'Для дизайнеров интерьера',
				'text' => 'Подключаемся на этапе идеи, подбираем материалы и реализуем уникальные проекты',
				'image_id' => 0,
			],
			[
				'title' => 'Для бизнеса',
				'text' => 'Работаем как надежный партнер: соблюдаем сроки и несем ответственность. При соблюдении технологии монтажа предоставляем гарантию',
				'image_id' => 0,
			],
			[
				'title' => 'Индивидуальные проекты',
				'text' => 'Каждая работа создается специально под пространство и задачу',
				'image_id' => 0,
			],
			[
				'title' => 'Для частных интерьеров',
				'text' => 'Берем проект «под ключ» — от эскиза до готового изделия. Соблюдаем сроки и договоренности, сопровождаем на всех этапах реализации',
				'image_id' => 0,
			],
		],
	];
}

/**
 * @param mixed $value
 * @return array
 */
function mosaic_sanitize_benefits_option(mixed $value): array {
	$defaults = mosaic_get_benefits_defaults();

	if (!is_array($value)) {
		return $defaults;
	}

	// Заголовок секции
	$title = sanitize_text_field((string) ($value['title'] ?? ''));
	if ($title === '') {
		$title = $defaults['title'];
	}

	// Элементы (4 штуки)
	$items = [];
	$rawItems = $value['items'] ?? [];
	if (!is_array($rawItems)) {
		$rawItems = [];
	}

	for ($i = 0; $i < 4; $i++) {
		$item = $rawItems[$i] ?? [];
		if (!is_array($item)) {
			$item = [];
		}

		$itemTitle = sanitize_text_field((string) ($item['title'] ?? ''));
		$itemText = sanitize_textarea_field((string) ($item['text'] ?? ''));
		$itemImageId = absint($item['image_id'] ?? 0);

		// Если данных нет, используем defaults
		if ($itemTitle === '' && isset($defaults['items'][$i])) {
			$itemTitle = $defaults['items'][$i]['title'];
		}
		if ($itemText === '' && isset($defaults['items'][$i])) {
			$itemText = $defaults['items'][$i]['text'];
		}

		$items[] = [
			'title' => $itemTitle,
			'text' => $itemText,
			'image_id' => $itemImageId,
		];
	}

	return [
		'title' => $title,
		'items' => $items,
	];
}

/**
 * @return array
 */
function mosaic_get_benefits_data(): array {
	$data = get_option('mosaic_benefits', []);
	if (!is_array($data)) {
		$data = [];
	}
	return mosaic_sanitize_benefits_option($data);
}

// Регистрация страницы в меню
add_action('admin_menu', static function (): void {
	if (!current_user_can('edit_theme_options')) {
		return;
	}

	add_menu_page(
		'С нами комфортно работать',
		'С нами комфортно работать',
		'edit_theme_options',
		'mosaic-benefits',
		'mosaic_benefits_page_render',
		'dashicons-heart',
		58
	);
});

// Регистрация настроек
add_action('admin_init', static function (): void {
	register_setting(
		'mosaic_benefits_group',
		'mosaic_benefits',
		[
			'type' => 'array',
			'sanitize_callback' => 'mosaic_sanitize_benefits_option',
			'default' => mosaic_get_benefits_defaults(),
		]
	);
});

/**
 * Рендер страницы
 */
function mosaic_benefits_page_render(): void {
	if (!current_user_can('manage_options')) {
		return;
	}

	$data = mosaic_get_benefits_data();
	$title = $data['title'];
	$items = $data['items'];

	wp_enqueue_media();

	?>
	<div class="wrap">
		<h1><?= esc_html(get_admin_page_title()); ?></h1>
		<p class="description">Управление блоком "С нами комфортно работать". Блок содержит заголовок и 4 фиксированных элемента.</p>

		<form method="post" action="options.php">
			<?php
			settings_fields('mosaic_benefits_group');
			?>

			<style>
				.mosaic-benefits-admin { max-width: 1200px; }
				.mosaic-benefits-section { background: #fff; padding: 20px; margin-bottom: 20px; border: 1px solid #ccd0d4; border-radius: 4px; }
				.mosaic-benefits-item { background: #f9f9f9; padding: 20px; margin-bottom: 20px; border: 1px solid #dcdcde; border-radius: 4px; position: relative; }
				.mosaic-benefits-item-number { position: absolute; top: 10px; right: 10px; background: #2271b1; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
				.mosaic-benefits-field { margin-bottom: 15px; }
				.mosaic-benefits-field label { display: block; font-weight: 600; margin-bottom: 5px; }
				.mosaic-benefits-field input[type="text"] { width: 100%; max-width: 600px; }
				.mosaic-benefits-field textarea { width: 100%; max-width: 600px; height: 80px; }
				.mosaic-benefits-image-preview { margin-top: 10px; }
				.mosaic-benefits-image-preview img { max-width: 200px; height: auto; border: 1px solid #dcdcde; border-radius: 4px; }
				.mosaic-benefits-image-actions { margin-top: 10px; }
			</style>

			<div class="mosaic-benefits-admin">
				<!-- Заголовок секции -->
				<div class="mosaic-benefits-section">
					<h2>Заголовок секции</h2>
					<div class="mosaic-benefits-field">
						<label for="benefits_title">Заголовок</label>
						<input
							type="text"
							id="benefits_title"
							name="mosaic_benefits[title]"
							value="<?= esc_attr($title); ?>"
							class="regular-text"
						>
						<p class="description">Основной заголовок блока (например: "С нами комфортно работать")</p>
					</div>
				</div>

				<!-- 4 элемента -->
				<div class="mosaic-benefits-section">
					<h2>Элементы блока (4 шт.)</h2>
					<?php foreach ($items as $idx => $item) : ?>
						<?php
						$num = $idx + 1;
						$imageId = $item['image_id'];
						$imageUrl = $imageId > 0 ? wp_get_attachment_image_url($imageId, 'medium') : '';
						?>
						<div class="mosaic-benefits-item">
							<div class="mosaic-benefits-item-number"><?= $num; ?></div>

							<div class="mosaic-benefits-field">
								<label for="benefits_item_<?= $idx; ?>_title">Заголовок</label>
								<input
									type="text"
									id="benefits_item_<?= $idx; ?>_title"
									name="mosaic_benefits[items][<?= $idx; ?>][title]"
									value="<?= esc_attr($item['title']); ?>"
									class="regular-text"
								>
							</div>

							<div class="mosaic-benefits-field">
								<label for="benefits_item_<?= $idx; ?>_text">Текст</label>
								<textarea
									id="benefits_item_<?= $idx; ?>_text"
									name="mosaic_benefits[items][<?= $idx; ?>][text]"
									class="large-text"
								><?= esc_textarea($item['text']); ?></textarea>
							</div>

							<div class="mosaic-benefits-field">
								<label>Изображение</label>
								<input
									type="hidden"
									id="benefits_item_<?= $idx; ?>_image_id"
									name="mosaic_benefits[items][<?= $idx; ?>][image_id]"
									value="<?= esc_attr((string) $imageId); ?>"
								>
								<div class="mosaic-benefits-image-preview" id="benefits_item_<?= $idx; ?>_preview" style="<?= $imageUrl ? '' : 'display:none;'; ?>">
									<img src="<?= esc_url($imageUrl); ?>" alt="" id="benefits_item_<?= $idx; ?>_img">
								</div>
								<div class="mosaic-benefits-image-actions">
									<button type="button" class="button button-primary benefits-upload-image" data-index="<?= $idx; ?>">
										<?= $imageUrl ? 'Изменить изображение' : 'Загрузить изображение'; ?>
									</button>
									<button type="button" class="button benefits-remove-image" data-index="<?= $idx; ?>" style="<?= $imageUrl ? '' : 'display:none;'; ?>">
										Удалить
									</button>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<?php submit_button('Сохранить изменения'); ?>
			</div>
		</form>
	</div>

	<script>
	jQuery(document).ready(function($){
		var frames = {};

		// Upload image
		$('.benefits-upload-image').on('click', function(e){
			e.preventDefault();
			var index = $(this).data('index');

			if (!frames[index]) {
				frames[index] = wp.media({
				title: 'Выбрать изображение',
				button: { text: 'Использовать' },
				multiple: false,
				library: { type: 'image' }
			});

			frames[index].on('open', function(){
				var selection = frames[index].state().get('selection');
				var existingId = parseInt($('#benefits_item_' + index + '_image_id').val());
				if (existingId > 0) {
					var att = wp.media.attachment(existingId);
					att.fetch();
					selection.reset([att]);
				} else {
					selection.reset([]);
				}
			});

			frames[index].on('select', function(){
				var attachment = frames[index].state().get('selection').first().toJSON();
				$('#benefits_item_' + index + '_image_id').val(attachment.id);
				$('#benefits_item_' + index + '_img').attr('src', attachment.url);
				$('#benefits_item_' + index + '_preview').show();
				$('.benefits-upload-image[data-index="' + index + '"]').text('Изменить изображение');
				$('.benefits-remove-image[data-index="' + index + '"]').show();
			});
			}

			frames[index].open();
		});

		// Remove image
		$('.benefits-remove-image').on('click', function(e){
			e.preventDefault();
			var index = $(this).data('index');
			if (!confirm('Удалить изображение?')) return;

			$('#benefits_item_' + index + '_image_id').val('');
			$('#benefits_item_' + index + '_preview').hide();
			$('.benefits-upload-image[data-index="' + index + '"]').text('Загрузить изображение');
			$(this).hide();
		});
	});
	</script>
	<?php
}
